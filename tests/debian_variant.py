"""Debian FPM image controls, separate from the minimal runtime profile."""
import json
import subprocess
import time
import uuid

import core


def process_controls(report, values, environment_probe):
    expected = {"master_uid": "0", "master_gid": "0", "worker_uid": "33", "worker_gid": "33"}
    actual = {key: values.get(key) for key in expected}
    core.record(report, "FPM root master uses www-data workers",
                actual == expected and values.get("worker_pid") not in (None, "", "1"),
                {"actual": actual, "expected": expected, "worker_pid": values.get("worker_pid")})
    core.record(report, "FPM workers inherit container environment",
                values.get("environment_probe") == environment_probe,
                {"actual": values.get("environment_probe"), "expected": environment_probe})


def arbitrary_user_suite(report, image, version, runtime_request, container_options):
    name = "php-fpm-user-test-" + uuid.uuid4().hex
    started = False
    environment_probe = "unprivileged-" + uuid.uuid4().hex
    try:
        start = core.command(["docker", "run", "-d", "--name", name] + container_options() +
                             ["--user", "1000:1000", "--env", "PHP_EOL_FPM_PROBE=" + environment_probe,
                              image])
        started = start["exit"] == 0
        if not started:
            raise RuntimeError(json.dumps(start))
        last_error = None
        for _ in range(40):
            try:
                values, errors = runtime_request(name)
                break
            except (RuntimeError, ValueError) as error:
                last_error = str(error)
                state = core.command(["docker", "inspect", name, "--format", "{{.State.Running}}"])
                if state["exit"] == 0 and state["stdout"].strip() == "false":
                    raise RuntimeError("FPM with explicit UID 1000 exited")
                time.sleep(0.25)
        else:
            raise RuntimeError("FPM with explicit UID 1000 unavailable: " + str(last_error))
        expected = {"version": version, "sapi": "fpm-fcgi", "sample": "ok",
                    "master_uid": "1000", "master_gid": "1000", "worker_uid": "1000",
                    "worker_gid": "1000", "environment_probe": environment_probe,
                    "memory_limit": "128M", "post_max_size": "8M"}
        actual = {key: values.get(key) for key in expected}
        core.record(report, "FPM supports explicit UID 1000 without capabilities",
                    actual == expected and not errors,
                    {"actual": actual, "expected": expected, "stderr": errors})
    except (RuntimeError, ValueError, subprocess.TimeoutExpired) as error:
        core.record(report, "FPM explicit-user request", False, str(error))
    finally:
        if started:
            report["fpm_arbitrary_user_logs"] = core.command(["docker", "logs", name])
            cleanup = core.command(["docker", "rm", "-f", name])
            core.record(report, "FPM explicit-user container removed", cleanup["exit"] == 0, cleanup)


def suite(report, image, version, runtime_request, container_options):
    inspected = core.command(["docker", "image", "inspect", image, "--format", "{{json .Config}}"])
    if inspected["exit"]:
        raise RuntimeError("Cannot inspect FPM image configuration")
    config = json.loads(inspected["stdout"])
    expected = {"Entrypoint": ["docker-php-entrypoint"], "Cmd": ["php-fpm"],
                "WorkingDir": "/var/www/html", "StopSignal": "SIGQUIT"}
    actual = {key: config.get(key) for key in expected}
    core.record(report, "FPM official entrypoint and default command", actual == expected,
                {"actual": actual, "expected": expected})
    tools = core.command(["docker", "run", "--rm"] + container_options() +
                         [image, "sh", "/tests/debian-tools.sh", version])
    core.record(report, "Debian shell, development tools and command forwarding", tools["exit"] == 0, tools)
    option = core.command(["docker", "run", "--rm"] + container_options() + [image, "-v"])
    core.record(report, "FPM entrypoint forwards leading options to php-fpm", option["exit"] == 0 and
                option["stdout"].startswith("PHP " + version + " (fpm-fcgi)") and
                not option["stderr"], option)
    arbitrary_user_suite(report, image, version, runtime_request, container_options)
    # Build helpers must be tested in a disposable writable container. It has
    # neither network access nor Linux capabilities; the application checks above
    # retain a read-only root filesystem.
    name = "php-debian-extension-test-" + uuid.uuid4().hex
    build_options = container_options("1g", True)
    build_options.remove("--read-only")
    try:
        extension = core.command(["docker", "run", "--rm", "--name", name] + build_options +
                                 [image, "sh", "/tests/debian-extension.sh", version], timeout=600)
        core.record(report, "Debian patched source lifecycle and native extension installation",
                    extension["exit"] == 0, extension)
    except subprocess.TimeoutExpired as error:
        core.record(report, "Debian patched source lifecycle and native extension installation",
                    False, str(error))
    finally:
        exists = core.command(["docker", "container", "inspect", name, "--format", "{{.Id}}"])
        if exists["exit"] == 0:
            cleanup = core.command(["docker", "rm", "-f", name])
            core.record(report, "Debian extension-build container removed", cleanup["exit"] == 0, cleanup)
