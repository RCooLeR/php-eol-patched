# Tests

The runtime checks use the image's default FPM configuration and a normal POST
request. The regression suite runs against a matching `test` build target. It
adds test modules and build tools without adding them to the runtime image.

```sh
python tests/run.py --image php-eol-patched:7.1.33 \
  --test-image php-eol-patched:7.1.33-test --version 7.1.33
```

Use an explicitly built local tag or an immutable image ID. Omit `--test-image`
to run only the runtime checks. Add `--output /tmp/php-tests.json` to save a
report outside the repository.

The test target must contain the matching patched source at `/usr/src/php`,
headers, a C compiler, Bash, and shared `pcntl`, `posix`, `dom`, and (on PHP 7)
`json` modules. Modules may be installed in PHP's extension directory or
`/test-support`. The suite loads them explicitly. MySQL cases use a local
protocol fixture and do not need a database server.

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
