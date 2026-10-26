# Tests

Check ordinary application behavior alongside the published failure cases. If a
low- or medium-severity backport introduces a compatibility regression, omit it
and record the remaining gap in [PATCHES.md](../PATCHES.md). Applications still
need their own tests and staging checks before adopting an image.

The further published-CVE tests cover DirectoryIterator paths, BCMath and EXIF
parsing, Phar permissions and compressed archives, AES-CCM vectors, password and
URL validation, XML redirects and session-persistent SOAP
objects. Native fixture overrides preserve the rejection checks while adapting
older PHP syntax or diagnostics. XML fixtures retain the full expected document
and the original published whitespace expectations; PHP 8.0 HTTP fixtures expect its
native HTTP/1.1 request line. PHP 7.4 also checks immediate rejection of an
overflowing CLI worker count and ordinary operation with two workers.

Published cookie-prefix and multipart-boundary payloads run through FPM on every
branch. They retain ordinary cookies and fields as controls.

The QName and metaphone cases need large inputs. After building a matching test
image, run this separately for each PHP 7.1–8.1 release, one at a time:

```sh
docker run --rm --network none --memory 8g --memory-swap 8g --cpus 2 \
  --user 33:33 --entrypoint /bin/bash php-eol-patched:7.1.33-test \
  /tests/batch2-large.sh 7.1.33
```

These tests are excluded from the default 2 GiB test container and default CI.
The command requires successful execution; skips fail. PHP 8.1 runs the newly
backported metaphone regression; its QName fix is already in the base release.

Additional coverage lists select the published GD color-match, SOAP null-name,
quoted-printable, proxy CR/LF and array-size regressions where their patches are
included. Ordinary array merging, recursive merging and filter decoding provide
compatibility controls. The FPM status probe checks HTML and XML escaping plus
an ordinary concurrent response on every release. FPM tests send three uploads against a one-upload
limit and require one warning while preserving an ordinary form field.
The PHP 8.0 proxy-test override changes only the diagnostic capitalization
(`Failed to open stream`); the rejection payload and assertions are preserved.

The final-pass tests add Phar ZIP parsing, SOAP Apache maps, mbregex encoding
rejection/state preservation, and exact FPM worker-log bytes across split flush
markers. PHP 7.1/7.2 quoted-printable tests include the published flush-path
prerequisite; their HTTP tests include the published header-parser prerequisites.
PHP 7.1–7.4/8.0/8.1 also check high-bit URL inputs and ordinary string operations.
Those Linux/glibc checks do not reproduce the platform-dependent NetBSD crash.
The PHP 7.1–7.3 ctype ports additionally check positional formatting, padding,
tag allowlists, URL user information, date offsets, day suffixes and interval
whitespace against ordinary and high-bit inputs. These controls preserve the
existing Linux behavior; they do not prove other platforms or locales safe.

The PHP 7.1–7.3 ctype ports passed the required local suites for both image
variants: 446 focused PHPT passes, three permitted AES-WRAP-PAD skips, and 8,538
explicit compatibility checks. A separate comparison of 1,863 upstream tests
against the same builds before these ports found no changed results or skip reasons.
That broader selection has one existing SQLite column-metadata failure per PHP
version (`ext/pdo_sqlite/tests/bug_42589.phpt`), 229 skips and six expected
failures in total. These baseline limitations remain; the result is not a claim
that every upstream test passes. The rejected PHP 7.1–7.4 SOAP candidates
retained memory when randomness failed during repeated compressed requests
and are not included.

PHP 7.1–7.3 compile the actual mysqlnd authentication writer with guarded reusable
and allocated buffers, checking twelve payload lengths and the packet header.
PHP 7.1–7.4 check maximum-length working-directory XML parsing and ordinary
open_basedir restrictions. Published libxml global-state cases use a small
test-only extension; positive controls verify explicit parser options and global
state restoration. The helper is compiled only inside the test container.
Resetting libxml parser defaults also restores whitespace in the HTML redirect
fixtures on PHP 7.1–7.4; serialized HTML can therefore differ from the earlier
builds even when the document content is unchanged.

The PHP 7.1 upload regression streams a field larger than 2 GiB after an uploaded
file and verifies both the response and temporary-file cleanup. Run it separately
with sufficient memory; it is excluded from the default 2 GiB CI container:

```sh
docker run --rm --network none --memory 8g --memory-swap 8g --cpus 2 \
  --user 33:33 --entrypoint /bin/bash php-eol-patched:7.1.33-test \
  /tests/published-upload-large.sh
```

The quoted-printable PHPT checks the published input and output. A normal build
is not a memory-sanitizer proof against every invalid read; source inspection
confirms the bound check precedes the affected dereference. No broad sanitizer
or full extension audit is implied by these tests.

The runtime checks use the image's default FPM configuration, verify installed
extensions and pinned PECL versions, and exercise local extension operations.
PostgreSQL controls also use a disposable server to exercise real database
connections through CLI and the runtime's production FPM configuration.
The regression suite runs against a matching `test` build target, which adds
matching source, test modules and regression tools. These test-only modules are
not enabled in the Debian runtime image.

```sh
docker pull postgres:18-bookworm@sha256:bbf83594798e144bff365a9c191c5d779f181fb3135902064ab8ff11918020fc
python tests/run.py --variant debian --image php-eol-patched:7.1.33-debian \
  --test-image php-eol-patched:7.1.33-test --version 7.1.33
```

Use an explicitly built local tag or an immutable image ID. Omit `--test-image`
to run only the runtime checks. Add `--output /tmp/php-tests.json` to save a
report outside the repository, including the observed extension versions.
The PostgreSQL service image must be available locally. `--postgres-image`
overrides the pinned default with another compatible official PostgreSQL image.
The report records its resolved image ID.

Pass `--variant debian` for every runtime image built by this branch. The runner also retains the minimal profile for the [minimal branch](https://github.com/RCooLeR/php-eol-patched/tree/minimal); it is not the default image type here. Both variants use the same source-regression `test` target for each release.

The Debian profile checks the official entrypoint's command and option forwarding,
shell, package manager, development tools, PHP headers, `phpize`, `php-config`,
and PECL. Requests must run in UID/GID 33 workers under a root master and
inherit container environment variables. A second container exercises ordinary
FPM requests with explicit `--user 1000:1000` and no Linux capabilities.
The default master must stop its workers cleanly on the image's stop signal.

A separate disposable, writable container verifies the packaged patched source
archive and extracted file checksums, deletes and re-extracts it, and builds
and enables `posix` using `docker-php-ext-install`. It then re-extracts and checks
the source again. This container has no network access or Linux capabilities;
it cannot fetch a replacement source or build dependencies. Build output is
captured in the report. `posix` is installed only in this disposable container.

The test target must contain the matching patched source at `/usr/src/php`,
headers, a C compiler, Bash, and test-only `pcntl` and `posix` modules. Modules
may be installed in PHP's extension directory or `/test-support`. The suite
loads application extensions and test helpers explicitly. MySQL cases use a
local protocol fixture and do not need a database server. Redis checks cover
client construction and compiled serializer constants; Memcached checks cover
local client setup and serializer options. Neither check connects to a server.

The Python runner requires Python 3 and Docker. Containers have no external
network, no published ports, a read-only root filesystem and bounded writable
tmpfs mounts, except for the Debian profile's disposable extension-build container.
The root FPM master receives only `SETUID`, `SETGID` and `KILL` capabilities;
other PHP containers drop all capabilities. Test files are mounted read-only. PostgreSQL uses a dedicated
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

The additional critical-CVE checks run in the default test target. They include
the published PHP 7.1 tag-stripping and CP950 fixtures, ordinary image resizing
and SQLite quote round-trips, and native tests extracted from the actual patched
source. The GD harness checks failed first/later weight allocations and size
overflow. The tag-stripping harness places the input immediately after an
inaccessible page, so a preceding-byte read fails even when PHP output would
otherwise look correct. The SQLite harness checks its signed-int argument limit
without allocating a large string. Both Keccak implementations compile and run
a known-answer digest vector on the supported amd64 platform.

Run the resource-heavy regressions separately after building the matching test
image. Use this command for PHP 7.1.33, 7.2.34, 7.3.33 and 7.4.33, one at a time:

```sh
docker run --rm --network none --memory 8g --memory-swap 8g --cpus 2 \
  --user 33:33 --entrypoint /bin/bash php-eol-patched:7.2.34-test \
  /tests/critical-large.sh 7.2.34
```

The SQLite fixture allocates 2 GiB and must reject the quote. PHP 7.2/7.3 also
run the published 4 GiB SHA-3 vector directly through the patched native Keccak
implementation. Their PHP wrapper's separate large-input truncation remains;
the native test does not claim to repair that behavior. These large tests are
not part of the default 2 GiB regression container or the default CI run.

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
tests. On all eight versions, the published FPM status payload runs through the
shared FastCGI probe, with HTML and XML escaping checks and an ordinary response
control. It is not duplicated in the PHPT lists because the older FPM test
helpers have incompatible response APIs.
