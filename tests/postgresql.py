"""Disposable PostgreSQL service for CLI and production FPM controls."""
import json
import os
import secrets
import subprocess
import time
import urllib.parse
import uuid

import core

DEFAULT_IMAGE = "postgres:18-bookworm@sha256:bbf83594798e144bff365a9c191c5d779f181fb3135902064ab8ff11918020fc"


def command(args, data=None, env=None, timeout=30):
    result = subprocess.run(args, input=data, stdout=subprocess.PIPE, stderr=subprocess.PIPE,
                            timeout=timeout, env=env)
    return {"exit": result.returncode, "stdout": result.stdout.decode("utf-8", "replace"),
            "stderr": result.stderr.decode("utf-8", "replace")}


def suite(report, image, server_image, runtime_request, container_options, test_image=None, version=None):
    token = uuid.uuid4().hex
    network, server, client = ("php-postgresql-" + kind + "-" + token
                               for kind in ("network", "server", "client"))
    regression = "php-postgresql-phpt-" + token
    created_network = False
    config = {"database": "php_controls", "username": "php_" + token,
              "password": secrets.token_hex(32)}
    try:
        inspected = command(["docker", "image", "inspect", server_image, "--format", "{{.Id}}"])
        if inspected["exit"]:
            raise RuntimeError("PostgreSQL test image is missing; docker pull " + server_image)
        server_image = inspected["stdout"].strip()
        report["postgresql_image"] = server_image
        created = command(["docker", "network", "create", "--internal", network])
        created_network = created["exit"] == 0
        if not created_network:
            raise RuntimeError(json.dumps(created))
        environment = dict(os.environ, POSTGRES_PASSWORD=config["password"])
        started = command(["docker", "run", "-d", "--name", server,
                           "--network", network, "--network-alias", "postgresql",
                           "--user", "postgres", "--read-only", "--cap-drop", "ALL",
                           "--security-opt", "no-new-privileges", "--pids-limit", "128",
                           "--memory", "512m", "--cpus", "2",
                           "--tmpfs", "/tmp:rw,noexec,nosuid,nodev,size=16m",
                           "--tmpfs", "/var/lib/postgresql:rw,noexec,nosuid,nodev,size=256m,mode=1777",
                           "--tmpfs", "/var/run/postgresql:rw,noexec,nosuid,nodev,size=16m,mode=1777",
                           "--mount", "type=bind,source=" + str(core.TESTS) + ",target=/tests,readonly",
                           "--env", "PGDATA=/var/lib/postgresql/data",
                           "--env", "POSTGRES_USER=" + config["username"],
                           "--env", "POSTGRES_DB=" + config["database"],
                           "--env", "POSTGRES_PASSWORD",
                           "--env", "POSTGRES_HOST_AUTH_METHOD=scram-sha-256",
                           "--entrypoint", "/bin/bash", server_image, "/tests/postgresql-server.sh"],
                          env=environment)
        if started["exit"]:
            raise RuntimeError(json.dumps(started))
        for _ in range(100):
            ready = command(["docker", "exec", server, "pg_isready", "-h", "127.0.0.1",
                             "-U", config["username"], "-d", config["database"]])
            if ready["exit"] == 0:
                break
            state = command(["docker", "inspect", server, "--format", "{{.State.Running}}"])
            if state["stdout"].strip() == "false":
                raise RuntimeError("PostgreSQL test server exited")
            time.sleep(0.2)
        else:
            raise RuntimeError("PostgreSQL test server was not ready")
        certificate = command(["docker", "exec", server, "cat", "/tmp/postgresql-server.crt"])
        if certificate["exit"]:
            raise RuntimeError("Cannot read PostgreSQL test certificate")
        config["certificate"] = certificate["stdout"]
        authentication = command(["docker", "exec", server, "psql", "-U", config["username"],
                                  "-d", config["database"], "-tAc",
                                  "SELECT rolpassword LIKE 'SCRAM-SHA-256$%' FROM pg_authid "
                                  "WHERE rolname = current_user"])
        core.record(report, "PostgreSQL uses SCRAM credentials", authentication["exit"] == 0
                    and authentication["stdout"].strip() == "t", authentication)
        options = container_options()
        options[options.index("--network") + 1] = network
        started = command(["docker", "run", "-d", "--name", client] + options + [image])
        if started["exit"]:
            raise RuntimeError(json.dumps(started))
        cli = command(["docker", "exec", "-i", client, "/usr/local/bin/php", "/tests/postgresql.php"],
                      json.dumps(config).encode())
        values = dict(urllib.parse.parse_qsl(cli["stdout"]))
        core.record(report, "PostgreSQL CLI native and PDO controls", valid(values, "cli")
                    and cli["exit"] == 0 and not cli["stderr"], dict(cli, values=values))
        body = urllib.parse.urlencode(config).encode()
        for _ in range(40):
            try:
                values, errors = runtime_request(client, "/tests/postgresql.php", body)
                break
            except (RuntimeError, ValueError):
                time.sleep(0.25)
        else:
            raise RuntimeError("PostgreSQL FPM control request unavailable")
        core.record(report, "PostgreSQL FPM native and PDO controls", valid(values, "fpm-fcgi")
                    and not errors, {"values": values, "stderr": errors})
        if test_image:
            options = container_options("2g", True)
            options[options.index("--network") + 1] = network
            published = command(["docker", "run", "--rm", "-i", "--name", regression] + options
                                + ["--user", "33:33", "--entrypoint", "/usr/local/bin/php", test_image,
                                   "/tests/postgresql-phpt.php", version], json.dumps(config).encode(), timeout=1200)
            published = {key: value.replace(config["password"], "[redacted]")
                         if isinstance(value, str) else value for key, value in published.items()}
            core.record(report, "Published PostgreSQL PHPT regressions", published["exit"] == 0, published)
    except (OSError, RuntimeError, ValueError, subprocess.TimeoutExpired) as error:
        core.record(report, "PostgreSQL integration", False,
                    str(error).replace(config["password"], "[redacted]"))
    finally:
        for name in (regression, client, server):
            exists = command(["docker", "container", "inspect", name, "--format", "{{.Id}}"])
            if exists["exit"] == 0:
                cleanup = command(["docker", "rm", "-f", "-v", name])
                kind = "server" if name == server else "PHPT" if name == regression else "client"
                core.record(report, "PostgreSQL test container removed: " + kind,
                            cleanup["exit"] == 0, cleanup)
        if created_network:
            cleanup = command(["docker", "network", "rm", network])
            core.record(report, "PostgreSQL test network removed", cleanup["exit"] == 0, cleanup)


def valid(values, sapi):
    checks = {name: value for name, value in values.items() if name.startswith("check:")}
    return (values.get("pass") == "yes" and values.get("sapi") == sapi
            and values.get("libpq_version") == "16.15"
            and values.get("pdo_client_version") == "16.15"
            and len(checks) == 18 and all(value == "ok" for value in checks.values()))
