# Tests

The runtime checks use the image's default FPM configuration, verify installed
extensions and pinned PECL versions, and exercise local extension operations.
PostgreSQL controls also use a disposable server to exercise real database
connections through CLI and the runtime's production FPM configuration.
The regression suite runs against a matching `test` build target, which adds
test modules and build tools without adding them to the minimal runtime image.

```sh
docker pull postgres:18-bookworm@sha256:bbf83594798e144bff365a9c191c5d779f181fb3135902064ab8ff11918020fc
python tests/run.py --image php-eol-patched:7.1.33 \
  --test-image php-eol-patched:7.1.33-test --version 7.1.33
```

Use an explicitly built local tag or an immutable image ID. Omit `--test-image`
to run only the runtime checks. Add `--output /tmp/php-tests.json` to save a
report outside the repository, including the observed extension versions.
The PostgreSQL service image must be available locally. `--postgres-image`
overrides the pinned default with another compatible official PostgreSQL image.
The report records its resolved image ID.

This branch builds the shell-free `:VERSION` images and uses `--variant minimal`
(the default). The shared test runner also retains a Debian profile for the
[`debian` branch](https://github.com/RCooLeR/php-eol-patched/blob/debian/tests/README.md),
but this branch does not build `:VERSION-debian` runtime images. CI builds and
tests only the minimal images and their matching regression targets.

The test target must contain the matching patched source at `/usr/src/php`,
headers, a C compiler, Bash, and test-only `pcntl` and `posix` modules. Modules
may be installed in PHP's extension directory or `/test-support`. The suite
loads application extensions and test helpers explicitly. MySQL cases use a
local protocol fixture and do not need a database server. Redis checks cover
client construction and compiled serializer constants; Memcached checks cover
local client setup and serializer options. Neither check connects to a server.

The Python runner requires Python 3 and Docker. Minimal-profile containers have
no external network, no published ports, a read-only root filesystem and bounded
writable tmpfs mounts. PHP containers drop all Linux capabilities. Test files
are mounted read-only. PostgreSQL uses a dedicated
internal Docker network and a tmpfs database; other checks use no network.
The runner removes only its own containers, anonymous volumes and network.

The PostgreSQL suite generates temporary credentials and a temporary server
certificate. It verifies SCRAM credentials, rejects an incorrect password and
uses `sslmode=verify-full` with the generated certificate as its trust anchor.
Reports include the client request times and certificate validity interval to
help diagnose clock-related TLS failures without relaxing certificate checks.
Native controls cover parameterized queries, prepared statements, literal
escaping, `pg_insert`, `pg_select`, `pg_update`, `pg_delete` and transactions.
PDO controls run native and emulated prepares, integer/string/null binding,
quoting, commit and rollback. The reported client version must match libpq
16.15. These controls run through both CLI and FPM on all eight PHP releases.
When `--test-image` is supplied, the same disposable server also runs each
release's `postgresql-tests.list` PHPT cases against the patched source. Their
connection settings are supplied only to the test process environment, and
skipped, warned, malformed or failed cases fail the suite.

To run the in-container regressions separately:

```sh
docker run --rm --network none --user 33:33 \
  --entrypoint /bin/bash php-eol-patched:7.1.33-test \
  /tests/test-target.sh 7.1.33
```

PHPT failures, warnings and malformed tests fail the run. Skips also fail,
except for the exact unavailable AES-WRAP-PAD prerequisite on PHP 7.1–8.0;
that regression must execute on PHP 8.1. An allowed skip is reported separately
and does not count as a passing regression. The
multi-gigabyte SOAP case is excluded by default; its bounded control remains
in the normal suite. Set `SOAP_RUN_RESOURCE_HEAVY_TESTS=1` only when running the
test target separately with sufficient memory.

PHP 5.6 and 7.0 use their published native fixtures. The default lists omit
32-bit-only and multi-gigabyte cases. Published multipart boundary and cookie
payloads run through FPM because the images do not include CGI. PHP 5.6 also checks its
native SOAP response-size limit with the published oversized chunk header.

Version overrides adapt handshake bytes, diagnostics and helpers to the older
APIs while retaining the published rejection checks. PHP 5.6 and 7.0 HTTP fixtures read
request headers before replying so that closing the server does not interrupt
the client's request writes.
The circular-link fixture uses the built-in extension name `Phar` for older
runners that compare extension names case-sensitively; its archives and
assertions are unchanged.
The PHP 5.6/7.0 lists also omit three published cases whose prerequisites are
absent: AES-WRAP-PAD is unavailable through their OpenSSL call path, SQLite's
defensive mode requires SQLite 3.26 or newer, and the upstream tar symlink test
is unconditionally skipped for PHP bug 65332. These omissions are not passing
tests. The published FPM status payload runs through the shared FastCGI probe
because the older FPM test helpers have incompatible response APIs.
