#!/usr/bin/env python3
"""Run focused PHP regressions against explicitly supplied local images."""
import argparse
import datetime
import json
from pathlib import Path
import struct
import subprocess
import sys
import time
import urllib.parse
import uuid

sys.dont_write_bytecode = True
import core

TESTS = Path(__file__).resolve().parent
VERSIONS = ("7.1.33", "7.2.34", "7.3.33", "7.4.33", "8.0.30", "8.1.34")


def command(args, data=None, timeout=30):
    return subprocess.run(args, input=data, stdout=subprocess.PIPE,
                          stderr=subprocess.PIPE, timeout=timeout)


def decoded(result):
    return {"exit": result.returncode,
            "stdout": result.stdout.decode("utf-8", "replace"),
            "stderr": result.stderr.decode("utf-8", "replace")}


def image_id(image):
    result = command(["docker", "image", "inspect", image, "--format", "{{.Id}}"])
    if result.returncode:
        raise RuntimeError(result.stderr.decode("utf-8", "replace"))
    return result.stdout.decode().strip()


def container_options(memory="512m", executable_tmp=False):
    permissions = "rw,nosuid,nodev" + (",exec" if executable_tmp else ",noexec")
    return ["--network", "none", "--read-only", "--cap-drop", "ALL",
            "--security-opt", "no-new-privileges", "--pids-limit", "128",
            "--memory", memory, "--cpus", "2",
            "--tmpfs", "/tmp:" + permissions + ",size=256m",
            "--mount", "type=bind,source=" + str(TESTS) + ",target=/tests,readonly"]


def fcgi_record(kind, content):
    return struct.pack("!BBHHBB", 1, kind, 1, len(content), 0, 0) + content


def fcgi_length(length):
    return bytes([length]) if length < 128 else struct.pack("!I", length | 0x80000000)


def runtime_request(container):
    body = b"sample=ok"
    params = {
        "GATEWAY_INTERFACE": "CGI/1.1", "SERVER_PROTOCOL": "HTTP/1.1",
        "REQUEST_METHOD": "POST", "SCRIPT_FILENAME": "/tests/runtime-probe.php",
        "SCRIPT_NAME": "/runtime-probe.php", "REQUEST_URI": "/runtime-probe.php",
        "DOCUMENT_ROOT": "/tests", "SERVER_NAME": "localhost", "SERVER_PORT": "80",
        "REMOTE_ADDR": "127.0.0.1", "CONTENT_TYPE": "application/x-www-form-urlencoded",
        "CONTENT_LENGTH": str(len(body)),
    }
    encoded = b""
    for name, value in params.items():
        name, value = name.encode(), value.encode()
        encoded += fcgi_length(len(name)) + fcgi_length(len(value)) + name + value
    request = (fcgi_record(1, struct.pack("!HB5x", 1, 0)) + fcgi_record(4, encoded) +
               fcgi_record(4, b"") + fcgi_record(5, body) + fcgi_record(5, b""))
    result = command(["docker", "exec", "-i", container, "/usr/local/bin/php", "-n",
                      "/tests/runtime-client.php"], request)
    if result.returncode:
        raise RuntimeError(result.stderr.decode("utf-8", "replace"))
    data = result.stdout
    stdout, stderr, ended = b"", b"", False
    while data:
        if len(data) < 8:
            raise RuntimeError("Truncated FastCGI header")
        version, kind, request_id, length, padding, _ = struct.unpack("!BBHHBB", data[:8])
        if version != 1 or request_id != 1 or len(data) < 8 + length + padding:
            raise RuntimeError("Invalid FastCGI record")
        content, data = data[8:8 + length], data[8 + length + padding:]
        if kind == 6:
            stdout += content
        elif kind == 7:
            stderr += content
        elif kind == 3:
            application, protocol = struct.unpack("!IB3x", content)
            if application or protocol:
                raise RuntimeError("FastCGI request failed")
            ended = True
    if not ended:
        raise RuntimeError("Missing FastCGI END_REQUEST")
    headers, separator, payload = stdout.partition(b"\r\n\r\n")
    if not separator or b"Status: 500" in headers:
        raise RuntimeError(stdout.decode("utf-8", "replace") + stderr.decode("utf-8", "replace"))
    return dict(urllib.parse.parse_qsl(payload.decode("utf-8"))), stderr.decode("utf-8", "replace")


def runtime_suite(report, image, version):
    inspect = command(["docker", "image", "inspect", image, "--format", "{{.Config.User}}"])
    core.record(report, "Runtime default user", inspect.returncode == 0 and
                inspect.stdout.decode().strip() == "33:33", decoded(inspect))
    cli = command(["docker", "run", "--rm"] + container_options() +
                  ["--entrypoint", "/usr/local/bin/php", image, "-n", "/tests/control.php"])
    core.record(report, "Runtime CLI controls", cli.returncode == 0, decoded(cli))
    name = "php-runtime-test-" + uuid.uuid4().hex
    started = False
    try:
        start = command(["docker", "run", "-d", "--name", name] + container_options() + [image])
        started = start.returncode == 0
        if not started:
            raise RuntimeError(json.dumps(decoded(start)))
        last_error = None
        for _ in range(40):
            try:
                values, errors = runtime_request(name)
                break
            except (RuntimeError, ValueError) as error:
                last_error = str(error)
                state = command(["docker", "inspect", name, "--format", "{{.State.Running}}"])
                if state.returncode == 0 and state.stdout.strip() == b"false":
                    logs = decoded(command(["docker", "logs", name]))
                    raise RuntimeError("Runtime FPM exited: " + json.dumps(logs))
                time.sleep(0.25)
        else:
            raise RuntimeError("Runtime FPM unavailable: " + str(last_error))
        expected = {"version": version, "sapi": "fpm-fcgi", "memory_limit": "128M",
                    "post_max_size": "8M", "sample": "ok", "shell_present": "no",
                    "source_present": "no"}
        if version.startswith("8.1."):
            expected["cached_types"] = "ok"
        ok = all(values.get(key) == value for key, value in expected.items())
        ok = ok and values.get("upload_progress") in ("", "0") and not errors
        core.record(report, "Runtime FPM request and production defaults", ok,
                    {"values": values, "stderr": errors, "expected": expected})
        modules = {module.lower() for module in values.get("modules", "").split(",")}
        required = {"cgi-fcgi", "core", "date", "hash", "libxml", "openssl", "pcre", "phar", "reflection",
                    "session", "spl", "standard", "soap", "mysqlnd", "mysqli", "pdo", "pdo_mysql"}
        if version.startswith("8."):
            required.add("json")
        if version.startswith("8.1."):
            required.update(("exif", "mbstring", "zlib", "zend opcache"))
        core.record(report, "Runtime extension set", modules == required,
                    {"loaded": sorted(modules), "missing": sorted(required - modules),
                     "unexpected": sorted(modules - required)})
        if version.startswith("8.1."):
            repeated, errors = runtime_request(name)
            core.record(report, "Cached PHP 8.1 union types and enum serialization",
                        repeated.get("cached_types") == "ok" and not errors, repeated)
    except (RuntimeError, ValueError, subprocess.TimeoutExpired) as error:
        core.record(report, "Runtime FPM verification", False, str(error))
    finally:
        if started:
            report["runtime_logs"] = decoded(command(["docker", "logs", name]))
            cleanup = command(["docker", "rm", "-f", name])
            core.record(report, "Runtime test container removed", cleanup.returncode == 0, decoded(cleanup))


def test_suite(report, image, version):
    name = "php-phpt-test-" + uuid.uuid4().hex
    try:
        result = command(["docker", "run", "--rm", "--name", name] + container_options("2g", True) +
                         ["--user", "33:33", "--entrypoint", "/bin/bash", image,
                          "/tests/test-target.sh", version], timeout=1200)
    finally:
        # A timed-out Docker client can leave its container running.
        command(["docker", "rm", "-f", name])
    core.record(report, "PHPT, native bounds and FPM controls", result.returncode == 0, decoded(result))
    if result.returncode:
        return
    # These shared modules are confined to the test target.
    extension_probe = command(["docker", "run", "--rm"] + container_options() +
                         ["--entrypoint", "/bin/bash", image, "-c",
                          'source /tests/extensions.sh; printf "%s\\n" "${php_args[@]:1}"'])
    if extension_probe.returncode:
        raise RuntimeError(json.dumps(decoded(extension_probe)))
    core.EXTENSION_ARGS = [line for line in extension_probe.stdout.decode().splitlines() if line]
    core.cli_suite(report, image, "patched")
    core.fpm_suite(report, image, "patched")
    soap_suite(report, image)


def soap_suite(report, image):
    name = "php-soap-test-" + uuid.uuid4().hex
    started = False
    try:
        result = core.command(["docker", "run", "-d", "--name", name] + core.container_options() + [
            "--entrypoint", "php-fpm", image, "-n", "-F", "-y", "/tests/fpm.conf",
            "-d", "memory_limit=128M", "-d", "soap.wsdl_cache_enabled=0",
            "-d", "log_errors=1", "-d", "error_log=/proc/self/fd/2"])
        started = result["exit"] == 0
        if not started:
            raise RuntimeError(json.dumps(result))
        last_error = None
        for _ in range(40):
            try:
                first = core.fastcgi(name, "ping")
                break
            except (RuntimeError, ValueError) as error:
                last_error = str(error)
                time.sleep(0.25)
        else:
            raise RuntimeError("SOAP test FPM unavailable: " + str(last_error))
        responses = [core.fastcgi(name, "soap-normal", extra={
            "SCRIPT_FILENAME": "/tests/soap-normal-request.php"}) for _ in range(10)]
        ok = all(response["body"].get("pass") and response["body"].get("soap") and
                 response["body"].get("pid") == first["body"]["pid"] for response in responses)
        core.record(report, "Repeated SOAP identity and serialization on one FPM worker", ok, responses)
    except (RuntimeError, ValueError, subprocess.TimeoutExpired) as error:
        core.record(report, "Repeated SOAP FPM requests", False, str(error))
    finally:
        if started:
            report["soap_logs"] = core.command(["docker", "logs", name])
            cleanup = core.command(["docker", "rm", "-f", name])
            core.record(report, "SOAP test container removed", cleanup["exit"] == 0, cleanup)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--image", required=True, help="Runtime image tag or ID")
    parser.add_argument("--test-image", help="Matching Dockerfile test target tag or ID")
    parser.add_argument("--version", required=True, choices=VERSIONS)
    parser.add_argument("--output", type=Path, help="Optional JSON report outside the source tree")
    options = parser.parse_args()
    report = {"version": options.version,
              "utc": datetime.datetime.now(datetime.timezone.utc).isoformat(), "checks": []}
    try:
        runtime = image_id(options.image)
        report["runtime_image"] = runtime
        runtime_suite(report, runtime, options.version)
        if options.test_image:
            test_image = image_id(options.test_image)
            report["test_image"] = test_image
            test_suite(report, test_image, options.version)
    except (OSError, RuntimeError, ValueError, subprocess.TimeoutExpired) as error:
        core.record(report, "Test orchestration", False, str(error))
    report["pass"] = bool(report["checks"]) and all(check["pass"] for check in report["checks"])
    if options.output:
        options.output.parent.mkdir(parents=True, exist_ok=True)
        options.output.write_text(json.dumps(report, indent=2) + "\n", encoding="utf-8")
    for check in report["checks"]:
        print(("PASS " if check["pass"] else "FAIL ") + check["name"])
        if not check["pass"]:
            print(json.dumps(check["evidence"], indent=2))
    return 0 if report["pass"] else 1


if __name__ == "__main__":
    sys.exit(main())
