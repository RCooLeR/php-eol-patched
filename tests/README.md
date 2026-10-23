# Tests

The runtime checks use the image's default FPM configuration, verify installed
extensions and pinned PECL versions, and exercise local extension operations.
The regression suite runs against a matching `test` build target, which adds
test modules and build tools without adding them to the runtime image.

```sh
python tests/run.py --image php-eol-patched:7.1.33 \
  --test-image php-eol-patched:7.1.33-test --version 7.1.33
```

Use an explicitly built local tag or an immutable image ID. Omit `--test-image`
to run only the runtime checks. Add `--output /tmp/php-tests.json` to save a
report outside the repository, including the observed extension versions.

The test target must contain the matching patched source at `/usr/src/php`,
headers, a C compiler, Bash, and test-only `pcntl` and `posix` modules. Modules
may be installed in PHP's extension directory or `/test-support`. The suite
loads application extensions and test helpers explicitly. MySQL cases use a
local protocol fixture and do not need a database server. Redis checks cover
client construction and compiled serializer constants; Memcached checks cover
local client setup and serializer options. Neither check connects to a server.

The Python runner requires Python 3 and Docker. Containers have no external
network, no published ports, a read-only root filesystem and a bounded writable
`/tmp`. Test files are mounted read-only. The runner removes only the containers
it creates.

To run the in-container regressions separately:

```sh
docker run --rm --network none --user 33:33 \
  --entrypoint /bin/bash php-eol-patched:7.1.33-test \
  /tests/test-target.sh 7.1.33
```

PHPT failures, skipped tests, warnings and malformed tests fail the run. The
multi-gigabyte SOAP case is excluded by default; its bounded control remains
in the normal suite. Set `SOAP_RUN_RESOURCE_HEAVY_TESTS=1` only when running the
test target separately with sufficient memory.

PHP 5.6 and 7.0 use their published native fixtures. The default lists omit
32-bit-only and multi-gigabyte cases. Published multipart boundary and cookie
payloads run through FPM because the images do not include CGI. PHP 5.6 also checks its
native SOAP response-size limit with the published oversized chunk header.

Version overrides adapt handshake bytes, diagnostics and helpers to the older
APIs while retaining the published rejection checks. PHP 5.6 HTTP fixtures read
request headers before replying so that closing the server does not interrupt
the client's request writes.
The PHP 5.6/7.0 lists also omit three published cases whose prerequisites are
absent: AES-WRAP-PAD is unavailable through their OpenSSL call path, SQLite's
defensive mode requires SQLite 3.26 or newer, and the upstream tar symlink test
is unconditionally skipped for PHP bug 65332. These omissions are not passing
tests. The published FPM status payload runs through the shared FastCGI probe
because the older FPM test helpers have incompatible response APIs.
