#!/usr/bin/env python3
"""Allocator and request-lifecycle regression helpers for the test target."""
import argparse
import base64
import datetime
import hashlib
import json
from pathlib import Path
import subprocess
import sys
import time
import uuid


TESTS = Path(__file__).resolve().parent
EXTENSION_ARGS = []


def command(args, data=None, timeout=30):
    args = list(args)
    if EXTENSION_ARGS and args[:1] == ["docker"]:
        if "--entrypoint" in args:
            entry = args.index("--entrypoint")
            if args[entry + 1] in ("php", "php-fpm"):
                start = entry + 3
                if len(args) > start and args[start] == "-n":
                    args[start + 1:start + 1] = EXTENSION_ARGS
        elif "exec" in args and "php" in args:
            start = args.index("php") + 1
            if len(args) > start and args[start] == "-n":
                args[start + 1:start + 1] = EXTENSION_ARGS
    result = subprocess.run(
        args, input=data, stdout=subprocess.PIPE, stderr=subprocess.PIPE,
        text=True, encoding="utf-8", errors="replace", timeout=timeout,
    )
    return {"exit": result.returncode, "stdout": result.stdout, "stderr": result.stderr}


def container_options():
    return [
        "--network", "none", "--cap-drop", "ALL", "--security-opt", "no-new-privileges",
        "--user", "1000:1000", "--read-only", "--pids-limit", "64", "--memory", "256m",
        "--cpus", "2", "--tmpfs", "/tmp:rw,noexec,nosuid,nodev,size=128m",
        "--mount", "type=bind,source=" + str(TESTS) + ",target=/tests,readonly",
    ]


def record(report, name, ok, evidence):
    report["checks"].append({"name": name, "pass": bool(ok), "evidence": evidence})


def cli_suite(report, image, expected):
    prefix = ["docker", "run", "--rm"] + container_options() + ["--entrypoint", "php", image, "-n"]
    version = command(prefix + ["-r", "echo PHP_VERSION, PHP_EOL, PHP_SAPI, PHP_EOL;"])
    record(report, "PHP CLI executes", version["exit"] == 0, version)
    control = command(prefix + ["/tests/control.php"])
    record(report, "Parser, array copy/growth, error handler and exception controls", control["exit"] == 0, control)
    normal = command(prefix + ["-d", "memory_limit=8M", "/tests/oom.php", "control"])
    record(report, "Below-limit allocation and normal shutdown", normal["exit"] == 0 and
           "CONTROL_OK" in normal["stdout"] and "SHUTDOWN_MARKER" in normal["stdout"], normal)
    expected_exit = 1 if expected == "patched" else 255
    shutdown_expected = expected == "baseline"
    for mode, allocation in (("oom", "one large allocation"), ("many", "many small allocations and array growth")):
        oom = command(prefix + ["-d", "memory_limit=8M", "-d", "display_errors=0", "-d", "log_errors=1",
                                "/tests/oom.php", mode])
        reached_oom = "PROBE_STARTED" in oom["stdout"] and "Allowed memory size" in (oom["stderr"] + oom["stdout"])
        ok = reached_oom and oom["exit"] == expected_exit and (
            ("SHUTDOWN_MARKER" in oom["stdout"]) == shutdown_expected
        ) and (expected != "patched" or "DESTRUCTOR_MARKER" not in oom["stdout"])
        record(report, "Forced PHP memory_limit OOM: " + allocation, ok,
               dict(oom, expected_exit=expected_exit, shutdown_expected=shutdown_expected,
                    note="PHP allocator OOM only; this does not force a kernel/cgroup OOM or prove an RCE exploit."))


def fastcgi(name, action, body=b"", extra=None, cookie=None):
    params = {
        "GATEWAY_INTERFACE": "CGI/1.1", "SERVER_PROTOCOL": "HTTP/1.1",
        "REQUEST_METHOD": "POST" if body else "GET", "SCRIPT_FILENAME": "/tests/request.php",
        "SCRIPT_NAME": "/request.php", "REQUEST_URI": "/request.php?action=" + action,
        "QUERY_STRING": "action=" + action, "DOCUMENT_ROOT": "/tests", "SERVER_NAME": "localhost",
        "SERVER_PORT": "80", "REMOTE_ADDR": "127.0.0.1", "REMOTE_PORT": "12345",
        "CONTENT_LENGTH": str(len(body)),
    }
    if cookie:
        params["HTTP_COOKIE"] = "PHPSESSID=" + cookie
    if extra:
        params.update(extra)
    request = json.dumps({"params": params, "body_base64": base64.b64encode(body).decode("ascii")})
    output = command(["docker", "exec", "-i", name, "php", "-n", "/tests/fastcgi-client.php"], request)
    if output["exit"] != 0:
        raise RuntimeError(json.dumps(output))
    response = json.loads(output["stdout"])
    headers, separator, content = response["stdout"].partition("\r\n\r\n")
    if not separator:
        raise RuntimeError("FastCGI response has no CGI header separator: " + json.dumps(response))
    response["headers"] = headers
    response["body"] = json.loads(content)
    if response["end"]["application_status"] != 0 or response["end"]["protocol_status"] != 0:
        raise RuntimeError("FastCGI END_REQUEST failure: " + json.dumps(response))
    return response


def multipart(fields, payload=None):
    boundary = "phpmultipart" + uuid.uuid4().hex
    chunks = []
    for key, value in fields:
        chunks.append(("--" + boundary + '\r\nContent-Disposition: form-data; name="' + key +
                       '"\r\n\r\n' + value + "\r\n").encode("ascii"))
    if payload is not None:
        chunks.append(("--" + boundary + '\r\nContent-Disposition: form-data; name="file"; '
                       'filename="control.bin"\r\nContent-Type: application/octet-stream\r\n\r\n').encode("ascii"))
        chunks.extend([payload, b"\r\n"])
    chunks.append(("--" + boundary + "--\r\n").encode("ascii"))
    return b"".join(chunks), "multipart/form-data; boundary=" + boundary


def multipart_upload_limit(report, name):
    boundary = "published-upload-limit"
    parts = [f'--{boundary}\r\nContent-Disposition: form-data; name="file{i}"; '
             f'filename="control.txt"\r\n\r\nordinary\r\n' for i in range(3)]
    parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="control"\r\n\r\nok\r\n')
    parts.append(f'--{boundary}--\r\n')
    limited_uploads = fastcgi(name, "fields", "".join(parts).encode("ascii"), {
        "CONTENT_TYPE": "multipart/form-data; boundary=" + boundary,
        "PHP_VALUE": "max_file_uploads = 1\nmax_multipart_body_parts = 10\ndisplay_errors = 0",
    })
    upload_logs = command(["docker", "logs", name])
    warning_count = (upload_logs["stdout"] + upload_logs["stderr"]).count(
        "Maximum number of allowable file uploads has been exceeded")
    record(report, "Excess file uploads produce one warning and preserve ordinary fields",
           warning_count == 1 and limited_uploads["body"].get("post") == {"control": "ok"},
           {"warning_count": warning_count, "response": limited_uploads})


def published_input_controls(report, name):
    """Exercise published request-input regressions through the built FPM SAPI."""
    # CVE-2024-8925: the CGI payload from tests/basic/GHSA-9pqp-7h25-4f32.phpt.
    # https://github.com/php/php-src/commit/19b49258d0c5a61398d395d8afde1123e8d161e0
    boundaries = []
    for length in (5115, 5116, 5117):
        boundary = "A" * length
        value = "BBB\r\n--" + boundary[:-1] + "CCC"
        body = ("--" + boundary + '\r\nContent-Disposition: form-data; name="koko"\r\n\r\n'
                + value + "\r\n--" + boundary + "--\r\n").encode("ascii")
        response = fastcgi(name, "fields", body,
                           {"CONTENT_TYPE": "multipart/form-data; boundary=" + boundary})
        fields = response["body"].get("post", {})
        actual = fields.get("koko") if isinstance(fields, dict) else None
        error = response["body"].get("early_error") or {}
        valid = actual == value if length < 5117 else (
            fields in ([], {}) and "Boundary too large" in error.get("message", ""))
        boundaries.append({"boundary_length": length, "pass": valid, "error": error,
                           "field_length": len(actual) if isinstance(actual, str) else None})
    record(report, "Published multipart boundary-buffer cases through FPM",
           all(case["pass"] for case in boundaries), boundaries)

    # The existing fixtures preserve the published CVE-2022-31629 and
    # CVE-2024-2756 payloads. Add CVE-2020-7070's encoded-cookie-name case:
    # https://github.com/php/php-src/commit/6559fe912661ca5ce5f0eeeb591d928451428ed0
    fixtures = json.loads((TESTS / "legacy" / "cookie-controls.json").read_text(encoding="utf-8"))
    fixtures.append({
        "source": "tests/basic/bug79699.phpt",
        "cookie": "__%48ost-evil=evil; __Host-evil=good; %66oo=baz;foo=bar",
        "expected": {"__%48ost-evil": "evil", "__Host-evil": "good", "%66oo": "baz", "foo": "bar"},
    })
    cookies = []
    for fixture in fixtures:
        response = fastcgi(name, "cookies", extra={"HTTP_COOKIE": fixture["cookie"]})
        cookies.append({"source": fixture["source"],
                        "pass": response["body"].get("cookies") == fixture["expected"],
                        "actual": response["body"].get("cookies"), "expected": fixture["expected"]})
    record(report, "Published cookie-prefix cases through FPM",
           all(case["pass"] for case in cookies), cookies)


def fpm_suite(report, image, expected):
    name = "php-regression-" + uuid.uuid4().hex
    started = False
    try:
        start = command(["docker", "run", "-d", "--name", name] + container_options() + [
            "--entrypoint", "php-fpm", image, "-n", "-F", "-y", "/tests/fpm.conf",
            "-d", "display_errors=0", "-d", "log_errors=1", "-d", "error_reporting=-1",
            "-d", "error_log=/proc/self/fd/2", "-d", "memory_limit=128M",
            "-d", "max_execution_time=10", "-d", "post_max_size=2M", "-d", "upload_max_filesize=1M",
            "-d", "session.save_handler=files", "-d", "session.save_path=/tmp",
            "-d", "session.upload_progress.enabled=1", "-d", "session.upload_progress.cleanup=0",
            "-d", "session.upload_progress.freq=1", "-d", "session.upload_progress.min_freq=0",
        ])
        started = start["exit"] == 0
        if not started:
            record(report, "Isolated FPM starts", False, start)
            return
        last_error = None
        for _ in range(30):
            try:
                ping = fastcgi(name, "ping")
                break
            except (RuntimeError, ValueError) as error:
                last_error = str(error)
                time.sleep(0.2)
        else:
            record(report, "Isolated FPM accepts FastCGI", False, last_error)
            return
        pid = ping["body"]["pid"]
        record(report, "Single-worker FPM starts", True, ping)

        lifecycle = []
        lifecycle_error = None
        try:
            for _ in range(10):
                producer = fastcgi(name, "produce", extra={
                    "HTTP_AUTHORIZATION": "Basic " + base64.b64encode(b"regression-user:test-only").decode("ascii")
                })
                consumer = fastcgi(name, "diagnostic", extra={"PHP_VALUE": "session.sid_length = 0"})
                lifecycle.append({"producer": producer, "consumer": consumer})
        except (RuntimeError, ValueError) as error:
            lifecycle_error = str(error)
        logs = command(["docker", "logs", name])
        observed = "\n".join([logs["stdout"], logs["stderr"]] + [
            pair["consumer"]["stderr"] + json.dumps(pair["consumer"]["body"].get("early_error"))
            for pair in lifecycle
        ])
        same_worker = len(lifecycle) == 10 and all(
            pair["producer"]["body"]["pid"] == pid and pair["consumer"]["body"]["pid"] == pid
            for pair in lifecycle
        )
        clean_state = all(pair["consumer"]["body"].get("stale_callbacks") == "" and
                          pair["consumer"]["body"].get("auth_user") is None for pair in lifecycle)
        auth_exercised = all(pair["producer"]["body"].get("auth_user") == "regression-user" for pair in lifecycle)
        diagnostics = observed.count("session.sid_length")
        record(report, "Repeated request cleanup followed by early INI diagnostics on same FPM worker",
               same_worker and clean_state and auth_exercised and diagnostics >= 10 and lifecycle_error is None,
               {"pid": pid, "pairs": lifecycle, "error": lifecycle_error, "same_worker": same_worker,
                "auth_producer_exercised": auth_exercised, "diagnostic_observations": diagnostics,
                "note": "Lifecycle smoke test, not a complete reproducer for every stale-state producer or RCE path."})

        session_id = "phpregression" + uuid.uuid4().hex
        first = fastcgi(name, "session", cookie=session_id)
        second = fastcgi(name, "session", cookie=session_id)
        record(report, "Normal sessions persist between requests", first["body"].get("counter") == 1 and
               second["body"].get("counter") == 2, {"first": first, "second": second})

        payload = (b"ordinary-upload\x00payload\r\n" * 4096)
        body, content_type = multipart([
            ("PHP_SESSION_UPLOAD_PROGRESS", "probe"), ("plain", "value"),
            ("nested[x]", "one"), ("nested[y]", "two"),
        ], payload)
        upload = fastcgi(name, "upload", body, {"CONTENT_TYPE": content_type}, session_id)
        uploaded = upload["body"].get("file") or {}
        expected_progress = expected == "baseline"
        upload_ok = uploaded.get("error") == 0 and uploaded.get("is_uploaded_file") is True and \
            uploaded.get("size") == len(payload) and uploaded.get("sha256") == hashlib.sha256(payload).hexdigest()
        post_ok = upload["body"].get("post") == {
            "PHP_SESSION_UPLOAD_PROGRESS": "probe", "plain": "value", "nested": {"x": "one", "y": "two"}
        }
        record(report, "Ordinary multipart file upload, nested fields and session compatibility",
               upload_ok and post_ok and upload["body"].get("counter") == 2, upload)
        record(report, "Upload-progress follows expected EOL source behavior despite enabled INI",
               upload["body"].get("progress_present") is expected_progress and
               upload["body"].get("progress_ini_enabled") == "1",
               {"expected_progress_present": expected_progress, "response": upload,
                "note": "The source backport intentionally disables upload-progress tracking."})

        body, content_type = multipart([("field" + str(i), str(i)) for i in range(5)])
        fields = fastcgi(name, "fields", body, {"CONTENT_TYPE": content_type,
                         "PHP_VALUE": "max_multipart_body_parts = 3"})
        # PHP 8.0.30 already has the part-count limit in its original source.
        # The custom PHP 7.1/7.2 backports introduce it on those branches.
        baseline_has_limit = tuple(int(n) for n in ping["body"]["php"].split(".")[:2]) >= (8, 0)
        count = 3 if expected == "patched" or baseline_has_limit else 5
        record(report, "Multipart body-part limit reduces amplification", fields["body"].get("post") == {
            "field" + str(i): str(i) for i in range(count)
        }, {"expected_fields": count, "response": fields})
        if expected == "patched":
            published_input_controls(report, name)
            multipart_upload_limit(report, name)
    except (RuntimeError, ValueError, subprocess.TimeoutExpired) as error:
        record(report, "FPM remaining test execution", False, str(error))
    finally:
        if started:
            report["fpm_logs"] = command(["docker", "logs", name])
            cleanup = command(["docker", "rm", "-f", name])
            record(report, "Disposable FPM container removed", cleanup["exit"] == 0, cleanup)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--image", required=True, help="Local test image reference or immutable image ID")
    parser.add_argument("--expect", choices=["patched", "baseline"], default="patched")
    parser.add_argument("--suite", choices=["cli", "fpm", "all"], default="all")
    parser.add_argument("--output", type=Path, help="Optional local JSON evidence file")
    options = parser.parse_args()
    if options.image.startswith("-") or any(c.isspace() for c in options.image):
        parser.error("Image reference must be one non-option argument")
    report = {
        "image": options.image, "expected_behavior": options.expect,
        "utc": datetime.datetime.now(datetime.timezone.utc).isoformat(), "checks": [],
        "scope": "Focused mechanism and compatibility tests for EOL PHP source backports; not comprehensive security maintenance.",
    }
    inspect = command(["docker", "image", "inspect", options.image, "--format", "{{.Id}}"])
    report["image_id"] = inspect["stdout"].strip()
    if inspect["exit"] != 0:
        record(report, "Local image exists", False, inspect)
    else:
        # An immutable ID also prevents a local tag changing between tests.
        image = report["image_id"]
        try:
            if options.suite in ("cli", "all"):
                cli_suite(report, image, options.expect)
            if options.suite in ("fpm", "all"):
                fpm_suite(report, image, options.expect)
        except (OSError, RuntimeError, ValueError, subprocess.TimeoutExpired) as error:
            record(report, "Test orchestration", False, str(error))
    report["pass"] = bool(report["checks"]) and all(check["pass"] for check in report["checks"])
    rendered = json.dumps(report, indent=2, ensure_ascii=False) + "\n"
    if options.output:
        options.output.parent.mkdir(parents=True, exist_ok=True)
        options.output.write_text(rendered, encoding="utf-8")
    sys.stdout.write(rendered)
    return 0 if report["pass"] else 1


if __name__ == "__main__":
    sys.exit(main())
