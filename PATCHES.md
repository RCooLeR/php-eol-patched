# CVE fix status

This page states which CVEs are fixed in this repository's PHP images. The same
status applies to the minimal and Debian variants. Short version numbers refer
to the pinned releases listed in [README.md](README.md).

**Fixed** includes repository backports, fixes already in the official PHP
release, and dependency fixes identified by verified package versions.
**Not fixed** means the fix is missing from the listed builds.
**Not affected** means the reported issue does not apply to the listed version or build.
**Mitigated** does not mean fully fixed. Versions not listed for a CVE are not
assessed by that row. This is selected CVE coverage, not complete EOL support.

For low- and medium-severity CVEs, a backport that breaks normal application
behavior is omitted and its gap documented. Compatibility takes priority during
the migration period; passing repository tests cannot guarantee every application.

Scores are the published CVSS base scores, with the version shown beside each
number and a link to its source. The assigning CVE Numbering Authority's (CNA)
newest published CVSS version is preferred. CISA-ADP, NVD or vendor scores fill
gaps; CNA and NVD ratings can differ. Scores describe the original vulnerability, not the
remaining risk after this repository's fixes or mitigations. Checked 2026-10-27.

## CVEs with missing fixes

| CVE | CVSS | Short description | Not fixed in | Fixed in |
| --- | --- | --- | --- | --- |
| CVE-2023-3247 | [2.6 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2023/3xxx/CVE-2023-3247.json) | SOAP Digest authentication fails to check randomness errors | **7.1–7.4**; candidate backports failed the compressed-request failure test | Published fix included in 5.6, 7.0, 8.0, 8.1; limitations below |
| CVE-2024-2408 | [5.9 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/2xxx/CVE-2024-2408.json) | RSA PKCS#1 decryption exposes the Marvin timing attack | **5.6–7.1, 8.1** | **7.2–8.0 via OpenSSL**, according to Debian's fixed-package criteria |
| CVE-2025-1220 | [3.7 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2025/1xxx/CVE-2025-1220.json) | Network functions truncate NUL-containing hostnames, potentially bypassing access checks | **7.1–8.0; persistent-socket gap (see below)** | No complete fix claimed |

These remaining gaps require a compatible backport, a dependency repair or a
published follow-up that covers the remaining path. The two critical EOL
mitigations are listed separately below and are not complete fixes.

## Core, SOAP, Phar, HTTP, TLS and FPM

| CVE | CVSS | Short description | Status | PHP builds |
| --- | --- | --- | --- | --- |
| [CVE-2026-109618](https://github.com/php/php-src/security/advisories/GHSA-w8hw-w9fx-j79w) | [9.2 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/109xxx/CVE-2026-109618.json) | PHP out-of-memory cleanup corruption permits remote code execution | **Mitigated, not fully fixed** | All eight builds |
| [CVE-2026-109619](https://github.com/php/php-src/security/advisories/GHSA-6wmf-4rv5-p3rp) | [9.2 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/109xxx/CVE-2026-109619.json) | PHP stale global request state permits remote code execution | **Mitigated, not fully fixed** | All eight builds |
| [CVE-2026-6722](https://github.com/php/php-src/security/advisories/GHSA-85c2-q967-79q5) | [9.5 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/6xxx/CVE-2026-6722.json) | SOAP duplicate Apache map keys cause exploitable use-after-free | **Fixed** | All eight builds |
| [CVE-2026-91765](https://github.com/php/php-src/security/advisories/GHSA-rgrp-mwpx-f6rm) | [7.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/91xxx/CVE-2026-91765.json) | SOAP deeply nested XML exhausts the stack through unbounded recursion | **Fixed** | All eight builds |
| [CVE-2025-14181](https://github.com/php/php-src/security/advisories/GHSA-cj93-vc83-wgqv) | [6.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2025/14xxx/CVE-2025-14181.json) | SOAP HTTP response size overflow causes heap buffer corruption | **Fixed** | All eight builds |
| [CVE-2026-6103](https://github.com/php/php-src/security/advisories/GHSA-j3wh-g957-2m85) | [4.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/6xxx/CVE-2026-6103.json) | Phar TAR size overflow allows forged archive entry injection | **Fixed** | All eight builds |
| [CVE-2023-3824](https://github.com/php/php-src/security/advisories/GHSA-jqcx-ccgc-xwhv) | [9.4 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2023/3xxx/CVE-2023-3824.json) | Phar directory parsing overflows a stack buffer | **Fixed** | All eight builds |
| [CVE-2026-91766](https://github.com/php/php-src/security/advisories/GHSA-fpwc-w8rq-cr92) | [5.9 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/91xxx/CVE-2026-91766.json) | HTTP stream redirects forward credentials to different origins | **Fixed** | All eight builds |
| [CVE-2026-93682](https://github.com/php/php-src/security/advisories/GHSA-7875-c8px-7q5f) | [5.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/93xxx/CVE-2026-93682.json) | HTTP redirects with empty Location headers read beyond heap buffers | **Fixed** | All eight builds |
| [CVE-2025-1217](https://github.com/php/php-src/security/advisories/GHSA-v8xr-gpvj-cx9g) | [6.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2025/1xxx/CVE-2025-1217.json) | HTTP stream parser misinterprets folded response headers | **Fixed** | All eight builds |
| [CVE-2025-1734](https://github.com/php/php-src/security/advisories/GHSA-pcmh-g36c-qc44) | [6.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2025/1xxx/CVE-2025-1734.json) | HTTP stream parser accepts malformed headers lacking colons | **Fixed** | All eight builds |
| [CVE-2025-1861](https://github.com/php/php-src/security/advisories/GHSA-52jp-hrpf-2jff) | [6.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2025/1xxx/CVE-2025-1861.json) | HTTP stream redirects truncate long URLs to incorrect destinations | **Fixed** | All eight builds |
| [CVE-2026-91767](https://github.com/php/php-src/security/advisories/GHSA-xr7j-rvgx-xq5p) | [6.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/91xxx/CVE-2026-91767.json) | OpenSSL wildcard certificate matching underflows lengths, causing heap buffer overreads | **Fixed** | All eight builds |
| [CVE-2026-91769](https://github.com/php/php-src/security/advisories/GHSA-vvx9-73fr-5jjx) | [4.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/91xxx/CVE-2026-91769.json) | OpenSSL hostname verification accepts matching CN despite conflicting SAN entries | **Fixed** | All eight builds |
| [CVE-2026-92842](https://github.com/php/php-src/security/advisories/GHSA-88hq-2827-7pg6) | [5.9 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/92xxx/CVE-2026-92842.json) | Conversion stream filters expose heap data through NUL-containing line separators | **Fixed** | All eight builds |
| [CVE-2026-91768](https://github.com/php/php-src/security/advisories/GHSA-62xp-839h-2637) | [6.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/91xxx/CVE-2026-91768.json) | FastCGI IPv6 allowlist checks compare incomplete addresses, permitting unauthorized clients | **Fixed** | All eight builds |
| [CVE-2024-8929](https://github.com/php/php-src/security/advisories/GHSA-h35g-vwh6-m678) | [5.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/8xxx/CVE-2024-8929.json) | A hostile MySQL server can disclose client heap data | **Fixed** | All eight builds |
| [CVE-2025-1218](https://github.com/php/php-src/security/advisories/GHSA-r6x9-5r99-36j7) | [3.4 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2025/1xxx/CVE-2025-1218.json) | mysqlnd reads beyond truncated server packet buffers | **Fixed** | All eight builds |
| [CVE-2021-21703](https://www.php.net/ChangeLog-7.php#7.3.31) | [7.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2021/21xxx/CVE-2021-21703.json) | FPM workers can corrupt root-process memory and escalate privileges | **Fixed** | All eight builds |

## PostgreSQL

Both pgsql and PDO PostgreSQL are enabled in every image. These fixes cover selected published extension defects; they do not establish complete PostgreSQL-related CVE coverage.

| CVE | CVSS | Short description | Status | PHP builds |
| --- | --- | --- | --- | --- |
| CVE-2022-31625 | [8.1 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2022/31xxx/CVE-2022-31625.json) | PostgreSQL parameter cleanup frees uninitialized pointers | **Fixed** | All eight builds |
| [CVE-2025-1735](https://github.com/php/php-src/security/advisories/GHSA-hrwm-9436-5mv3) | [5.9 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2025/1xxx/CVE-2025-1735.json) | PostgreSQL escaping ignores quoting errors and can crash | **Fixed** | All eight builds |
| [CVE-2026-17543](https://github.com/php/php-src/security/advisories/GHSA-7qpv-r5mr-78m4) | [8.1 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/17xxx/CVE-2026-17543.json) | PostgreSQL backslash escaping permits SQL injection through crafted parameters | **Fixed** | All eight builds |
| CVE-2025-14180 | [8.2 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2025/14xxx/CVE-2025-14180.json) | PDO PostgreSQL quoting errors trigger a NULL pointer dereference | **Fixed / not affected** | 8.1 fixed; 5.6–8.0 use the older unaffected PDO quoter ABI |
| CVE-2025-1094 | [8.1 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2025/1xxx/CVE-2025-1094.json) | PostgreSQL libpq quoting permits SQL injection through invalid encoded text | **Fixed via libpq** | All eight builds |
| CVE-2026-6477 | [8.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/6xxx/CVE-2026-6477.json) | PostgreSQL libpq large-object responses can overflow client stack buffers | **Fixed via libpq** | All eight builds |

[CVE-2025-14180](https://github.com/php/php-src/security/advisories/GHSA-8xr5-qppj-gvwj) is already fixed in PHP 8.1.34. The older branches retain their integer-returning PDO quoter ABI, which already propagates quote failure; they do not need the newer zend_string-returning fix. The published invalid-byte regression is included for all eight versions.

Each version's `postgresql/` patch directory records upstream commits, native maintenance sources, adaptations and before/after source hashes. Tests exercise the published failure inputs and ordinary queries through both interfaces, including SCRAM authentication and certificate-verified TLS in CLI and FPM.

The extensions use [libpq 16.15](https://www.postgresql.org/docs/16/release-16-15.html), built from the pinned upstream source in [extensions/libpq.tsv](extensions/libpq.tsv). This includes the client-library repairs for [CVE-2025-1094](https://www.postgresql.org/support/security/CVE-2025-1094/) and [CVE-2026-6477](https://www.postgresql.org/support/security/CVE-2026-6477/). Substituting an older libpq can reintroduce client-library defects. Other linked libraries, including OpenSSL, retain the limits of the base image.

## Additional GD, OpenSSL and Phar fixes

The following fixes were already included in the PHP 5.6 and 7.0 maintenance series and are also backported to PHP 7.1–8.1:

| CVE | CVSS | Short description | Status | PHP builds |
| --- | --- | --- | --- | --- |
| CVE-2026-9672 | [8.1 (v3.1; Red Hat)](https://access.redhat.com/security/cve/cve-2026-9672) | GD GIF decoding permits buffer overflow and application crashes | **Fixed** | All eight builds |
| CVE-2026-14355 | [5.6 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/14xxx/CVE-2026-14355.json) | OpenSSL AES-WRAP-PAD encryption underallocates output buffers, corrupting heap memory | **Fixed** | All eight builds |
| CVE-2026-7260 | [5.4 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/7xxx/CVE-2026-7260.json) | Phar circular symbolic links exhaust the stack through unbounded recursion | **Fixed** | All eight builds |

<details>
<summary>Test coverage and limitations</summary>

- **CVE-2026-9672**: Exact published changes, compilation and ordinary GIF decoding; no CVE-specific fixture was supplied upstream.
- **CVE-2026-14355**: Published round-trip regression executes on PHP 8.1. The cipher is unavailable in the older builds; those skips are not passing exploit tests.
- **CVE-2026-7260**: Published archive fixtures and ordinary nested symbolic links.

</details>

The Phar backports also retain the filename separator after resolving a nested link, following the [published native compatibility repair](https://repo.tuxcare.com/security/errata/els_alt_php/debian12/CLSA-2026-1786517269.html). This repair applies to all eight versions. Per-version `coverage/` directories contain the patch provenance and source hashes.

## GD, EXIF, string, SQLite and SHA-3 coverage

Per-version `coverage/` directories record the patches, published sources and
source checksums. Not-affected builds are identified explicitly below.

| CVE | CVSS | Short description | Status | PHP builds |
| --- | --- | --- | --- | --- |
| CVE-2016-10166 | [9.8 (v3.0)](https://nvd.nist.gov/vuln/detail/CVE-2016-10166) | GD interpolation allocation suffers an integer underflow | **Fixed** | All eight builds |
| CVE-2019-9641 | [9.8 (v3.1)](https://nvd.nist.gov/vuln/detail/CVE-2019-9641) | EXIF TIFF directory parsing reads uninitialized memory | **Fixed** | All eight builds |
| CVE-2020-7059 | [6.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7059.json) | `fgetss()` reads beyond the tag-stripping buffer | **Fixed** | 5.6–7.4; not affected: 8.0, 8.1 |
| CVE-2020-7060 | [6.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7060.json) | mbstring BIG5 conversion reads beyond its buffer | **Fixed** | All eight builds |
| CVE-2022-31631 | [9.1 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2022/31xxx/CVE-2022-31631.json) | Oversized PDO SQLite strings bypass quoting, permitting SQL injection | **Fixed** | All eight builds |
| CVE-2022-37454 | [9.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2022/37xxx/CVE-2022-37454.json) | SHA-3/Keccak integer overflow causes buffer overflow | **Fixed (see note)** | 7.2–8.1; not affected: 5.6–7.1 |

The EXIF fixes were already included in the PHP 5.6 and 7.0 maintenance series.
The [original report](https://bugs.php.net/bug.php?id=77509) concerns 32-bit
integer overflow; these images support amd64 only. No duplicate patch is added.

**SHA-3 limitation:** PHP 7.2 and 7.3 receive the native Keccak fix in both generic implementations.
Their PHP wrappers already truncate very large input lengths before the reported
native overflow condition. That separate truncation can produce incorrect SHA-3
digests and is **not fixed here**. PHP 7.1 uses an earlier, different implementation.

Regression tests cover GD allocation failure and overflow, stateful tag boundaries,
CP950 conversion, SQLite argument-width limits and ordinary operations. The
published multi-gigabyte SQLite test and native Keccak vector run separately;
see [test instructions](tests/README.md). The upstream GD huge-image fixture is
rejected by an earlier constructor check on PHP 7.0, so the default suite uses
an exact-source allocator harness instead. These checks do not establish complete
CVE coverage for the extensions or their linked libraries.

## Additional fixed CVEs

| CVE | CVSS | Short description | Status | PHP builds |
| --- | --- | --- | --- | --- |
| CVE-2019-6977 | [8.8 (v3.0)](https://nvd.nist.gov/vuln/detail/CVE-2019-6977) | GD image color matching heap buffer overflow with crafted images | **Fixed** | 7.0 |
| CVE-2019-9637 | [7.5 (v3.0)](https://nvd.nist.gov/vuln/detail/CVE-2019-9637) | Cross-filesystem rename temporarily exposes files with incorrect permissions | **Fixed** | 5.6, 7.0 |
| CVE-2019-9638 | [7.5 (v3.1)](https://nvd.nist.gov/vuln/detail/CVE-2019-9638) | EXIF MakerNote offset handling causes uninitialized memory reads | **Fixed** | 5.6, 7.0 |
| CVE-2019-9639 | [7.5 (v3.1)](https://nvd.nist.gov/vuln/detail/CVE-2019-9639) | EXIF MakerNote length handling causes uninitialized memory reads | **Fixed** | 5.6, 7.0 |
| CVE-2019-9640 | [7.5 (v3.1)](https://nvd.nist.gov/vuln/detail/CVE-2019-9640) | EXIF SOF marker processing performs invalid memory reads | **Fixed** | 5.6, 7.0 |
| CVE-2019-9675 | [8.1 (v3.0)](https://nvd.nist.gov/vuln/detail/CVE-2019-9675) | PHAR tar long-link buffer overflow; vendor considers practical attacks usually impossible | **Fixed** | 5.6, 7.0 |
| CVE-2021-21702 | [5.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2021/21xxx/CVE-2021-21702.json) | Malformed SOAP responses cause a NULL-pointer crash | **Fixed** | 5.6–7.2 |
| CVE-2023-0662 | [7.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2023/0xxx/CVE-2023-0662.json) | Excessive multipart fields exhaust CPU or log storage | **Fixed** | All eight builds |
| CVE-2025-14178 | [6.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2025/14xxx/CVE-2025-14178.json) | `array_merge()` element-count overflow causes heap buffer corruption | **Fixed** | 7.2–8.1; not affected: 5.6–7.1 |
| CVE-2019-11048 | [5.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11048.json) | Upload memory exhaustion leaves temporary files, potentially exhausting disk space | **Fixed** | 5.6, 7.0, 7.1 |
| CVE-2020-7068 | [4.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7068.json) | Phar ZIP parsing accesses freed memory | **Fixed** | 7.1 |
| CVE-2022-31626 | [7.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2022/31xxx/CVE-2022-31626.json) | Oversized MySQL passwords overflow the mysqlnd/PDO buffer | **Fixed** | 5.6–7.3 |
| CVE-2023-0568 | [7.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2023/0xxx/CVE-2023-0568.json) | Path resolution writes one byte beyond its buffer | **Fixed** | 5.6–7.4 |
| CVE-2023-3823 | [8.6 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2023/3xxx/CVE-2023-3823.json) | Shared libxml state unexpectedly enables external entity loading | **Fixed** | 5.6–7.4 |
| CVE-2024-11233 | [4.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/11xxx/CVE-2024-11233.json) | Quoted-printable stream decoding reads beyond its buffer | **Fixed** | All eight builds |
| CVE-2024-11234 | [4.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/11xxx/CVE-2024-11234.json) | HTTP proxy streams permit CRLF injection and request smuggling | **Fixed** | All eight builds |
| CVE-2024-9026 | [3.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/9xxx/CVE-2024-9026.json) | FPM worker output can alter or remove log content | **Fixed** | 7.4–8.1; not affected: 5.6–7.3 |
| CVE-2026-6735 | [7.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/6xxx/CVE-2026-6735.json) | PHP-FPM status page renders attacker-controlled data as executable JavaScript | **Fixed** | All eight builds |
| CVE-2026-7259 | [2.1 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/7xxx/CVE-2026-7259.json) | mbstring regex encoding mismatches trigger NULL pointer dereferences | **Fixed** | 8.0, 8.1; not affected: 5.6–7.4 |
| CVE-2026-7262 | [2.9 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/7xxx/CVE-2026-7262.json) | SOAP Apache map decoding dereferences NULL when values are missing | **Fixed** | All eight builds |

<details>
<summary>Final-pass sources and verification</summary>

The PHP 7.1/7.2 quoted-printable backports include the published
[bug 74267 prerequisite](https://github.com/php/php-src/commit/12c59f6660706321f9d42c55421ff6864439c8b7),
which fixes the flush-path crash seen in the earlier attempt. The HTTP header
backports include the published parser prerequisites. Provenance files record
the complete sequence and native context adjustments.

The [upload-length fix](https://github.com/php/php-src/commit/3c8582ca4b8e84e5647220b647914876d2c3b124)
has a separate PHP 7.1 test that streams a form field larger than 2 GiB after an
uploaded file, then checks the field and temporary-file cleanup. It is outside
the default CI memory limit; see [test instructions](tests/README.md).

The [mysqlnd fix](https://github.com/php/php-src/commit/58006537fc5f133ae8549efe5118cde418b3ace9)
is checked using the actual authentication writer with guarded buffers and
ordinary PDO MySQL controls. The [path fix](https://github.com/php/php-src/commit/ec10b28d64decbc54aa1e585dce580f0bd7a5953)
reserves the extra byte in all five published locations; maximum-length working
directory and ordinary open_basedir tests check compatibility.

The [libxml fix](https://github.com/php/php-src/commit/c283c3ab0ba45d21b2b8745c1f9c7cbfe771c975)
uses the native maintenance backport and its follow-up. Published DOM, SimpleXML
and XMLReader regressions use a test-only helper to alter library globals.
Ordinary controls preserve explicit entity options, validation and the caller's
global state. The helper is not included in runtime images.

The [Phar ZIP fix](https://github.com/php/php-src/commit/7355ab81763a3d6a04ac11660e6a16d58838d187)
retains the published binary archive fixture.
The [FPM logging fix](https://github.com/php/php-src/commit/4580b8b3e1495df45a894ab928b23067159eedb8)
checks both published split-marker cases and ordinary log bytes.
The [SOAP map fix](https://github.com/php/php-src/commit/9992b81bcd0a4b94e8a2249e50cc37646725df93)
checks missing values and an ordinary typed map.
The [mbstring fix](https://github.com/php/php-src/commit/79a054eae016c56409432e69aebc8ca908a88838)
checks unsupported encoding rejection and preservation of ordinary regex state.

PHP 7.4/8.0/8.1 use the complete published branch-native CVE-2026-7258 changes,
apart from an already-applied unsigned-character check. High-bit URL and string
controls run on these Linux/glibc images. The reported platform-dependent crash
is not reproduced here; no NetBSD validation is claimed. PHP 7.1–7.3 use ports of
the applicable published changes, with older-code limitations described below.

</details>

## Further published fixes

These are the additional branches covered by the published backports. Existing
PHP 5.6/7.0 coverage is listed separately below; missing branches are listed at the top.

| CVE | CVSS | Short description | Status | PHP builds |
| --- | --- | --- | --- | --- |
| CVE-2026-7258 | [6.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/7xxx/CVE-2026-7258.json) | URL decoding passes signed characters to ctype, causing NetBSD buffer overreads | **Fixed (published repair; see scope below)** | All eight builds |
| CVE-2019-11045 | [3.7 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11045.json) | DirectoryIterator truncates paths at embedded NUL bytes | **Fixed** | 7.1 |
| CVE-2019-11046 | [3.7 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11046.json) | BCMath non-ASCII numeric input triggers out-of-bounds reads | **Fixed** | 7.1 |
| CVE-2019-11047 | [4.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11047.json) | EXIF image parsing heap over-read exposes memory or crashes | **Fixed** | 7.1 |
| CVE-2019-11050 | [4.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11050.json) | EXIF parsing use-after-free allows invalid memory reads | **Fixed** | 7.1 |
| CVE-2020-7063 | [5.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7063.json) | Phar archives discard restrictive file permissions | **Fixed** | 7.1 |
| CVE-2020-7064 | [6.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7064.json) | EXIF parsing reads uninitialized memory | **Fixed** | 7.1 |
| CVE-2020-7066 | [5.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7066.json) | `get_headers()` truncates URLs at NUL bytes | **Fixed** | 7.1 |
| CVE-2020-7069 | [5.4 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7069.json) | OpenSSL AES-CCM incorrectly shortens the initialization vector | **Fixed** | 7.1 |
| CVE-2020-7070 | [4.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7070.json) | Cookie-name decoding bypasses secure cookie prefixes | **Fixed** | 7.1 |
| CVE-2020-7071 | [5.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7071.json) | URL validation accepts invalid password components | **Fixed** | 7.1, 7.2 |
| CVE-2021-21705 | [4.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2021/21xxx/CVE-2021-21705.json) | Malformed URL password fields bypass validation | **Fixed** | 7.1, 7.2 |
| CVE-2021-21707 | [5.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2021/21xxx/CVE-2021-21707.json) | XML filenames permit encoded-NUL path truncation | **Fixed** | 7.1, 7.2 |
| CVE-2022-31628 | [2.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2022/31xxx/CVE-2022-31628.json) | Phar repeatedly decompresses self-reproducing gzip archives | **Fixed** | 7.1–7.3 |
| CVE-2022-31629 | [6.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2022/31xxx/CVE-2022-31629.json) | Cookie-name normalization permits forged secure-prefix cookies | **Fixed** | 7.1–7.3 |
| CVE-2022-4900 | [6.2 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2022/4xxx/CVE-2022-4900.json) | Excessive CLI-server worker counts overflow a heap buffer | **Fixed** | 7.4 |
| CVE-2023-0567 | [7.7 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2023/0xxx/CVE-2023-0567.json) | Malformed bcrypt hashes can accept an incorrect password | **Fixed** | 7.1–7.4 |
| CVE-2024-2756 | [6.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/2xxx/CVE-2024-2756.json) | Incomplete cookie-prefix repair still permits forged secure cookies | **Fixed** | 7.1–8.0 |
| CVE-2024-3096 | [6.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/3xxx/CVE-2024-3096.json) | `password_verify()` accepts an empty password in a NUL case | **Fixed** | 7.1–8.0 |
| CVE-2024-5458 | [5.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/5xxx/CVE-2024-5458.json) | URL filtering accepts invalid user information | **Fixed** | 7.1–8.0 |
| CVE-2024-8925 | [3.1 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/8xxx/CVE-2024-8925.json) | Malformed multipart data suppresses legitimate request fields | **Fixed** | 7.1–8.0 |
| CVE-2025-1219 | [6.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2025/1xxx/CVE-2025-1219.json) | libxml redirects use incorrect Content-Type headers for charset detection | **Fixed** | 7.1–8.0 |
| CVE-2025-1736 | [6.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2025/1xxx/CVE-2025-1736.json) | HTTP stream header validation mishandles line endings and omits headers | **Fixed** | 7.1–8.0 |
| CVE-2025-6491 | [5.9 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2025/6xxx/CVE-2025-6491.json) | SOAP parsing dereferences NULL for oversized XML namespace prefixes | **Fixed** | 7.1–8.0 |
| CVE-2026-7261 | [6.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/7xxx/CVE-2026-7261.json) | SoapServer session persistence mishandles faults, leaving freed object references | **Fixed** | 7.1–8.1 |
| CVE-2026-7568 | [6.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/7xxx/CVE-2026-7568.json) | `metaphone()` input position overflow causes out-of-bounds memory reads | **Fixed** | 7.1–8.1 |

<details>
<summary>Sources, verification and skipped fixes</summary>

Each version's `coverage/provenance.json` records the published commits, native
maintenance patches, compatibility adjustments and checksums. Builds enforce
zero-fuzz application and source checksums. Published regressions exercise the
malformed inputs, with ordinary password, URL, archive, upload and SOAP controls.
Cookie and multipart payloads run through FPM, since these images do not include CGI.
The SOAP QName and metaphone regressions exceed 2 GiB and run separately using
[the documented large-test command](tests/README.md).

- **CVE-2023-3247:** Additional PHP 7.1–7.4 backports are skipped after local
  testing. The native published backports, with the
  [hash-header prerequisite](https://github.com/php/php-src/commit/32c7c433ac1983c4497349051681a4f361d3d33e),
  built and passed ordinary Digest, Basic and no-auth exchanges, blocked
  authenticated requests when random generation failed, and allowed client reuse
  after entropy was restored. However, repeated failures with compressed requests
  retained request buffers until PHP request shutdown. The candidates were removed
  under this repository's policy of skipping medium-severity backports that
  introduce a compatibility regression. No published cleanup follow-up was found
  in this limited check. The
  [published patch](https://github.com/php/php-src/commit/ac4254ad764c70cb1f05c9270d8d12689fc3aeb6)
  does repair the randomness-error check; this is separate from the observation that
  current SOAP HTTP Digest code in all eight builds still hashes and sends only
  eight hexadecimal nonce characters, limiting the effective nonce to at most
  32 bits. Existing upstream/native changes remain in place. Upstream lists its releases
  as patched in [the advisory](https://github.com/php/php-src/security/advisories/GHSA-76gg-c692-v2mw).
  The nonce-length limitation is this repository's observation, not a separate
  upstream CVE or a change to upstream's fix classification.
- **CVE-2024-2408:** The fix depends on OpenSSL, not a PHP source patch. Exact
  library package records and loaded-library hashes were checked in both image
  variants. PHP 7.2–8.0 meet [Debian's published fixed-package criteria](https://security-tracker.debian.org/tracker/CVE-2024-2408);
  this classification does not claim an independent timing-attack proof.
  PHP 5.6–7.1 and 8.1 retain dependency gaps; the PHP 8.1 release number alone
  does not fix its Bookworm OpenSSL dependency. See the package table below and
  [PHP's guidance](https://www.php.net/ChangeLog-8.php#8.1.29).
- **CVE-2025-1220:** Additional backports are skipped. The
  [published hostname fix](https://github.com/php/php-src/commit/cac8f7f1cf4939f55f06b68120040f057682d89c)
  still permits NUL-truncated persistent-socket reuse before hostname validation;
  a local regression reproduced this on PHP 7.1. Existing PHP 5.6/7.0 maintenance
  patches remain, but complete coverage is not claimed. The ordinary new-connection
  tests alone do not verify this reuse path. The available native
  [7.4](https://github.com/remicollet/php-src-security/commit/d407d8a8735ebf43bee3e6b49fb013b8aa4b6bfc)
  and [8.0](https://github.com/remicollet/php-src-security/commit/36150278addd8686a9899559241296094bd57282)
  patches are also omitted here: they leave the same reuse path unchanged.
- **CVE-2026-7258:** PHP 7.1–7.3 include ports of the applicable published
  unsigned-character conversions, including the reported URL-decoding path.
  Adaptations preserve older formatting parsers, URL user information and FPM
  path allowlists. The published change is not an exhaustive cleanup of older
  code: tag parsing still has a signed-character case conversion outside its
  hunks, including in the PHP 7.4 native patch. This separate observation does
  not change upstream's classification of the reported URL-decoding fix.
  Linux/glibc compatibility controls do not reproduce the NetBSD crash or
  establish behavior on untested platforms and locales.
  Native published sources include
  [7.4](https://github.com/remicollet/php-src-security/commit/b44c4c80d8a76d7df32985e4bbadb12c985d6a22),
  [8.0](https://github.com/remicollet/php-src-security/commit/30de2f8492a06ebcf1659307f1f049e2799c7f21) and
  [8.1](https://github.com/remicollet/php-src-security/commit/07e5a9263314fd9a2a4479661353704e00dd8d0c).

The OpenSSL package classification applies to RSA PKCS#1 v1.5 private decryption:

| PHP builds | Linked library package in both variants | CVE-2024-2408 status |
| --- | --- | --- |
| 5.6–7.1 (Stretch) | `libssl1.1 1.1.0l-1~deb9u6` | **Not fixed** |
| 7.2–7.4 (Buster) | `libssl1.1 1.1.1n-0+deb10u6` | **Fixed via OpenSSL**; exceeds Debian's `1.1.1n-0+deb10u4` threshold |
| 8.0 (Bullseye) | `libssl1.1 1.1.1w-0+deb11u8` | **Fixed via OpenSSL**; exceeds Debian's `1.1.1n-0+deb11u4` threshold |
| 8.1 (Bookworm) | `libssl3 3.0.22-1~deb12u1` | **Not fixed**; Debian reports the required support is absent |

Images record package versions in `/usr/share/php-eol-patched/runtime-packages.tsv`.
Replacing linked libraries requires reassessing this status.

Published sources for the added fixes:

| CVE | Published source |
| --- | --- |
| CVE-2019-11045 | [a5a15965](https://github.com/php/php-src/commit/a5a15965da23c8e97657278fc8dfbf1dfb20c016) |
| CVE-2019-11046 | [eb23c600](https://github.com/php/php-src/commit/eb23c6008753b1cdc5359dead3a096dce46c9018) |
| CVE-2019-11047 | [d348cfb9](https://github.com/php/php-src/commit/d348cfb96f2543565691010ade5e0346338be5a7) |
| CVE-2019-11050 | [c14eb8de](https://github.com/php/php-src/commit/c14eb8de974fc8a4d74f3515424c293bc7a40fba) |
| CVE-2020-7063 | [e5c95234](https://github.com/php/php-src/commit/e5c95234d87fcb8f6b7569a96a89d1e1544749a6) |
| CVE-2020-7064 | [41f66e2a](https://github.com/php/php-src/commit/41f66e2a2cfd611e35be5ac3bf747f0b56161216), [8577fa58](https://github.com/php/php-src/commit/8577fa5891220dac40d42b2f745fa159dcd871ad), [2c081b7e](https://github.com/php/php-src/commit/2c081b7e269d0f63cd9d60a40997f18b5cf793be), [ad05ad4d](https://github.com/php/php-src/commit/ad05ad4dbafc29dd23828760d4bfa2be12ccbb1c), [b42b6d0f](https://github.com/php/php-src/commit/b42b6d0ff774fdced1155cb0c721d91914d619f5) |
| CVE-2020-7066 | [0d139c5b](https://github.com/php/php-src/commit/0d139c5b94a5f485a66901919e51faddb0371c43) |
| CVE-2020-7069 | [0216630e](https://github.com/php/php-src/commit/0216630ea2815a5789a24279a1211ac398d4de79) |
| CVE-2020-7070 | [6559fe91](https://github.com/php/php-src/commit/6559fe912661ca5ce5f0eeeb591d928451428ed0) |
| CVE-2020-7071 | [2d3d7241](https://github.com/php/php-src/commit/2d3d72412a6734e19a38ed10f385227a6238e4a6), [4a89e726](https://github.com/php/php-src/commit/4a89e726bd4d0571991dc22a9a1ad4509e8fe347), [9c673083](https://github.com/php/php-src/commit/9c673083cd46ee2a954a62156acbe4b6e657c048), [5174de7c](https://github.com/php/php-src/commit/5174de7cd33c3d4fa591c9c93859ff9989b07e8c) |
| CVE-2021-21705 | [a5538c62](https://github.com/php/php-src/commit/a5538c62293fa782fcc382d0635cfc0c8b9190e3), [19001378](https://github.com/php/php-src/commit/190013787bbc424c240413d914e3a038f974ccef) |
| CVE-2021-21707 | [f15f8fc5](https://github.com/php/php-src/commit/f15f8fc573eb38c3c73e23e0930063a6f6409ed4) |
| CVE-2022-31628 | [404e8bdb](https://github.com/php/php-src/commit/404e8bdb68350931176a5bdc86fc417b34fb583d), [432bf196](https://github.com/php/php-src/commit/432bf196d59bcb661fcf9cb7029cea9b43f490af) |
| CVE-2022-31629 | [0611be4e](https://github.com/php/php-src/commit/0611be4e82887cee0de6c4cbae320d34eec946ca) |
| CVE-2022-4900 | [789a37f1](https://github.com/php/php-src/commit/789a37f14405e2d1a05a76c9fb4ed2d49d4580d5) |
| CVE-2023-0567 | [c840f715](https://github.com/php/php-src/commit/c840f71524067aa474c00c3eacfb83bd860bfc8a), [a92acbad](https://github.com/php/php-src/commit/a92acbad873a05470af1a47cb785a18eadd827b5) |
| CVE-2024-2756 | [093c08af](https://github.com/php/php-src/commit/093c08af25fb323efa0c8e6154aa9fdeae3d3b53) |
| CVE-2024-3096 | [0ba5229a](https://github.com/php/php-src/commit/0ba5229a3f7572846e91c8f5382e87785f543826) |
| CVE-2024-5458 | [4066610b](https://github.com/php/php-src/commit/4066610b47e22c24cbee91be434a94357056a479), [08be64e4](https://github.com/php/php-src/commit/08be64e40197fc12dca5f802d16748d9c3cb4cb4), [76362f95](https://github.com/php/php-src/commit/76362f9526afbd5565003d981f9507aaf62337f2), [87a7b8da](https://github.com/php/php-src/commit/87a7b8dab75e221a1fcd74cf34dc650f7253c12c), [b919ad03](https://github.com/php/php-src/commit/b919ad0323dbc3e1e1ac0f6ba8ec2ad380579918), [7e0e3cc8](https://github.com/php/php-src/commit/7e0e3cc820c493301409a0ce2b6ef95e0ab06b0c) |
| CVE-2024-8925 | [19b49258](https://github.com/php/php-src/commit/19b49258d0c5a61398d395d8afde1123e8d161e0) |
| CVE-2025-1219 | [f901bec4](https://github.com/php/php-src/commit/f901bec494ae921f36e1066e4380b92888757f0f), [b6004a04](https://github.com/php/php-src/commit/b6004a043c16b211d462218fbb3f72db68ec2b18) |
| CVE-2025-1736 | [41d49abb](https://github.com/php/php-src/commit/41d49abbd99dab06cdae4834db664435f8177174) |
| CVE-2025-6491 | [9cb3d8d2](https://github.com/php/php-src/commit/9cb3d8d200f0c822b17bda35a2a67a97b039d3e1) |
| CVE-2026-7261 | [b9f81dea](https://github.com/php/php-src/commit/b9f81deadfda4df332fd75e20c03b3812038cd3f) |
| CVE-2026-7568 | [dd77cfef](https://github.com/php/php-src/commit/dd77cfefa7fb457ddbfa2adab0f2bf2b0d1731c1) |

</details>

## CVEs that do not affect the checked builds

| CVE | CVSS | Short description | Status | PHP builds |
| --- | --- | --- | --- | --- |
| CVE-2019-19246 | [7.5 (v3.1)](https://nvd.nist.gov/vuln/detail/CVE-2019-19246) | Oniguruma case-insensitive matching reads beyond heap buffers | **Not affected** | 5.6–7.1 |
| CVE-2020-7065 | [7.4 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7065.json) | UTF-32LE lowercasing overwrites a stack buffer | **Not affected** | 5.6–7.1 |
| CVE-2020-7067 | [7.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7067.json) | EBCDIC `urldecode()` uses invalid signed array indexes | **Not affected** | 7.1; EBCDIC support is disabled |
| CVE-2022-31630 | [6.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2022/31xxx/CVE-2022-31630.json) | GD font loading permits out-of-bounds reads | **Not affected** | 5.6–7.3 |
| CVE-2024-2757 | [7.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/2xxx/CVE-2024-2757.json) | `mb_encode_mimeheader()` loops indefinitely on crafted input | **Not affected** | 5.6–8.0 |
| CVE-2026-12184 | [8.2 (v4.0)](https://github.com/php/php-src/security/advisories/GHSA-mhmq-mmqj-2v39) | HTTP stream TLS setup failure crashes PHP-FPM and its workers | **Not affected** | All eight builds |
| CVE-2026-17544 | [8.1 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/17xxx/CVE-2026-17544.json) | BCMath `bccomp()` writes beyond bounds with crafted operands and scale | **Not affected** | All eight builds |
| CVE-2020-7069 | [5.4 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7069.json) | OpenSSL AES-CCM incorrectly shortens the initialization vector | **Not affected** | 5.6, 7.0; the affected AEAD API is absent |
| CVE-2025-14177 | [6.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2025/14xxx/CVE-2025-14177.json) | `getimagesize()` exposes uninitialized heap data when reading image chunks | **Not affected** | 5.6–8.0; the affected chunked-reader implementation is absent |
| CVE-2026-6104 | [6.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/6xxx/CVE-2026-6104.json) | mbstring encoding names containing NUL trigger global buffer overreads | **Not affected** | All eight builds; the affected encoding-name lookup is absent |
| CVE-2026-7263 | [6.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/7xxx/CVE-2026-7263.json) | DOM XML canonicalization creates circular lists, causing infinite loops | **Not affected** | All eight builds; the affected modern DOM implementation is absent |
| CVE-2024-9026 | [3.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/9xxx/CVE-2024-9026.json) | FPM worker output can alter or remove log content | **Not affected** | 5.6–7.3; the affected split flush-marker parser is absent |
| CVE-2026-7259 | [2.1 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/7xxx/CVE-2026-7259.json) | mbstring regex encoding mismatches trigger NULL pointer dereferences | **Not affected** | 5.6–7.4; the affected cached mbfl-encoding pointer is absent |

<details>
<summary>Published sources and compatibility details</summary>

The new nonapplicability checks compare the actual patched sources with the
reported paths: [CVE-2020-7069](https://bugs.php.net/bug.php?id=79601),
[CVE-2025-14177](https://github.com/php/php-src/security/advisories/GHSA-3237-qqm7-mfv7),
[CVE-2026-6104](https://github.com/php/php-src/security/advisories/GHSA-74r9-qxhc-fx53),
and [CVE-2026-7263](https://github.com/php/php-src/security/advisories/GHSA-4jhr-8w89-j733).

The PHP 7.3 maintenance archive linked below supplies older native variants.
PHP 7.4 uses its native `return;` after the array-size error because it lacks
`RETURN_THROWS()`. The older FPM patches cover HTML/XML escaping and retain their
JSON behavior. Per-version `coverage/` directories record provenance and hashes.

| CVE | Source or applicability evidence |
| --- | --- |
| [CVE-2026-109618](https://github.com/php/php-src/security/advisories/GHSA-w8hw-w9fx-j79w) | EOL changes below |
| [CVE-2026-109619](https://github.com/php/php-src/security/advisories/GHSA-6wmf-4rv5-p3rp) | EOL changes below |
| [CVE-2026-6722](https://github.com/php/php-src/security/advisories/GHSA-85c2-q967-79q5) | [aee3b3ac](https://github.com/php/php-src/commit/aee3b3ac9b816b0def1c462695b483b49a83148e) |
| [CVE-2026-91765](https://github.com/php/php-src/security/advisories/GHSA-rgrp-mwpx-f6rm) | [3655b79c](https://github.com/php/php-src/commit/3655b79c7bfa90db004d75a3b282ed0f538f4836) |
| [CVE-2025-14181](https://github.com/php/php-src/security/advisories/GHSA-cj93-vc83-wgqv) | [b11bd1d9](https://github.com/php/php-src/commit/b11bd1d9390bb203684e3d4aa5e1104ea842de6f) |
| [CVE-2026-6103](https://github.com/php/php-src/security/advisories/GHSA-j3wh-g957-2m85) | [0994e2e8](https://github.com/php/php-src/commit/0994e2e887cd993866b5e4d7dba2fade5caeefec) |
| [CVE-2023-3824](https://github.com/php/php-src/security/advisories/GHSA-jqcx-ccgc-xwhv) | [80316123](https://github.com/php/php-src/commit/80316123f3e9dcce8ac419bd9dd43546e2ccb5ef) |
| [CVE-2026-91766](https://github.com/php/php-src/security/advisories/GHSA-fpwc-w8rq-cr92) | [5af9465c](https://github.com/php/php-src/commit/5af9465ca890be679033d5fb0dc89b6ea071326b) |
| [CVE-2026-93682](https://github.com/php/php-src/security/advisories/GHSA-7875-c8px-7q5f) | [231f29fd](https://github.com/php/php-src/commit/231f29fdfd94a571b36c5f772367329c3af67ca7), [follow-up](https://github.com/php/php-src/pull/23521) |
| [CVE-2025-1217](https://github.com/php/php-src/security/advisories/GHSA-v8xr-gpvj-cx9g) | [d20b4c97](https://github.com/php/php-src/commit/d20b4c97a9f883b62b65b82d939c5af9a2028ef1) |
| [CVE-2025-1734](https://github.com/php/php-src/security/advisories/GHSA-pcmh-g36c-qc44) | [0548c4c1](https://github.com/php/php-src/commit/0548c4c1756724a89ef8310709419b08aadb2b3b) |
| [CVE-2025-1861](https://github.com/php/php-src/security/advisories/GHSA-52jp-hrpf-2jff) | [ac1a054b](https://github.com/php/php-src/commit/ac1a054bb3eb5994a199e8b18cca28cbabf5943e) |
| [CVE-2026-91767](https://github.com/php/php-src/security/advisories/GHSA-xr7j-rvgx-xq5p) | [78cc82b3](https://github.com/php/php-src/commit/78cc82b37a340840c6fbff676f513eb4e3d96533) |
| [CVE-2026-91769](https://github.com/php/php-src/security/advisories/GHSA-vvx9-73fr-5jjx) | [0bb308eb](https://github.com/php/php-src/commit/0bb308eb4cf699e707efbcf97ffb70e46a176be7) |
| [CVE-2026-92842](https://github.com/php/php-src/security/advisories/GHSA-88hq-2827-7pg6) | [b4e3397e](https://github.com/php/php-src/commit/b4e3397ec8f4f647e6e019dbf79870fc2f14ba78) |
| [CVE-2026-91768](https://github.com/php/php-src/security/advisories/GHSA-62xp-839h-2637) | [dcdfcf86](https://github.com/php/php-src/commit/dcdfcf86fcf7f42fa98dd3d000f310ebdad41965) |
| [CVE-2024-8929](https://github.com/php/php-src/security/advisories/GHSA-h35g-vwh6-m678) | [2f5aa9f9](https://github.com/php/php-src/commit/2f5aa9f9d150ca56e356f3ca9acf9d530108cb08), [32f905f1](https://github.com/php/php-src/commit/32f905f1d689aaa8eacd6331a18c0dd45972c3c1) |
| [CVE-2025-1218](https://github.com/php/php-src/security/advisories/GHSA-r6x9-5r99-36j7) | [114dbb74](https://github.com/php/php-src/commit/114dbb74368e6a6cd6e48ddb4a76dbe8728b84e2) |
| [CVE-2021-21703](https://www.php.net/ChangeLog-7.php#7.3.31) | [cb2021e5](https://github.com/php/php-src/commit/cb2021e5f69da5e2868130a05bb53db0f9f89e4b) |
| CVE-2022-31625 | [55f6895f](https://github.com/php/php-src/commit/55f6895f4b4c677272fd4ee1113acdbd99c4b5ab) |
| [CVE-2025-1735](https://github.com/php/php-src/security/advisories/GHSA-hrwm-9436-5mv3) | [9376aeef](https://github.com/php/php-src/commit/9376aeef9f8ff81f2705b8016237ec3e30bdee44) |
| [CVE-2026-17543](https://github.com/php/php-src/security/advisories/GHSA-7qpv-r5mr-78m4) | [ab048bd8](https://github.com/php/php-src/commit/ab048bd83b578119cf81b456526d50498421d617) |
| CVE-2016-10166 | [a918020](https://github.com/php/php-src/commit/a918020c03880e12ac9f38e11a4a3789491a5f85) |
| CVE-2019-9641 | [5e824a8](https://github.com/php/php-src/commit/5e824a88d073d282c4f358f186cb87ddc284f83d) |
| CVE-2020-7059 | [0f79b1b](https://github.com/php/php-src/commit/0f79b1bf301f455967676b5129240140c5c45b09), [follow-up](https://github.com/php/php-src/commit/2dc170e25d86a725fefd4c08f2bd8378820b28f5) |
| CVE-2020-7060 | [2bcbc95](https://github.com/php/php-src/commit/2bcbc95f033c31b00595ed39f79c3a99b4ed0501) |
| CVE-2022-31631 | [921b681](https://github.com/php/php-src/commit/921b6813da3237a83e908998483f46ae3d8bacba) |
| CVE-2022-37454 | [248f647](https://github.com/php/php-src/commit/248f647724e385bfb8d83aa5b5a5ca3c4ee2c7fd) |
| CVE-2026-9672 | [fcd691b3](https://github.com/php/php-src/commit/fcd691b377d02285740744bee17c0f298be227d5) |
| CVE-2026-14355 | [cbc04891](https://github.com/php/php-src/commit/cbc0489126a7682796aad1e5fb4e51de74af162c) |
| CVE-2026-7260 | [2e0fa0a4](https://github.com/php/php-src/commit/2e0fa0a44441d74bf8cc4e1ce1c8af9cd4209f52) |
| CVE-2019-6977 | [567c9f5](https://github.com/php/php-src/commit/567c9f58425c37260864f276b6d3f434eecf4b49) |
| CVE-2021-21702 | [3c939e3](https://github.com/php/php-src/commit/3c939e3f69955d087e0bb671868f7267dfb2a502) |
| CVE-2023-0662 | [e45850c](https://github.com/php/php-src/commit/e45850c195dcd5534394cf357a3f776d4916b655), [part limit](https://github.com/php/php-src/commit/716de0cff539f46294ef70fe75d548cd66766370) |
| CVE-2024-11233 | [a6c84cd](https://github.com/php/php-src/commit/a6c84cd7efd7eaaaefd4463412508df570d35358) |
| CVE-2024-11234 | [cf6700e](https://github.com/php/php-src/commit/cf6700e86d6357420a7c8386da63d48fec55f633) |
| CVE-2025-14178 | [8b80115](https://github.com/php/php-src/commit/8b801151bd54b36aae4593ed6cfc096e8122b415) |
| CVE-2026-6735 | [13e72d1](https://github.com/php/php-src/commit/13e72d1c97f72892421b04e8c5c05bc9bb193457) |
| CVE-2019-9637 | The existing [rename fix](https://github.com/php/php-src/commit/e3133e4db70476fb7adfdedb738483e2255ce0e1) creates with restrictive permissions and restores ownership before mode. |
| CVE-2019-9638, CVE-2019-9639 | [Bug77563](https://bugs.php.net/bug.php?id=77563) bounds MakerNote data and resets its length; [bug77659](https://bugs.php.net/bug.php?id=77659) identifies the shared fix. |
| CVE-2019-9640 | Existing [bug77540](https://bugs.php.net/bug.php?id=77540) bounds the SOF segment before parsing. |
| CVE-2019-9675 | The existing [TAR link fix](https://github.com/php/php-src/commit/e0f5d62bd6690169998474b62f92a8c5ddf0e699) rejects oversized link names. |
| CVE-2019-19246 | [Upstream bug78559](https://bugs.php.net/bug.php?id=78559) identifies PHP 7.3 as affected. These builds bundle Oniguruma 5.9.5/5.9.6; no speculative port was added. |
| CVE-2020-7065 | Vulnerable full case mapping was [introduced in PHP 7.3](https://www.php.net/manual/en/migration73.new-features.php); these trees use the older simple mapping. |
| CVE-2020-7067 | [Bug79465](https://bugs.php.net/bug.php?id=79465) requires EBCDIC. This Linux build leaves `CHARSET_EBCDIC` undefined. |
| CVE-2022-31630 | [Bug81739](https://bugs.php.net/bug.php?id=81739) dates introduction to 7.4; earlier trees retain the post-byte-swap font-size guard. |
| CVE-2024-2757 | These versions use the legacy MIME encoder, not the loop changed by [3394efc](https://github.com/php/php-src/commit/3394efc63e52a017995f92d8da4ef28224247bb3). |
| CVE-2026-12184 | The [reported peer-name reset path](https://github.com/php/php-src/security/advisories/GHSA-mhmq-mmqj-2v39) is absent. |
| CVE-2026-17544 | Faulty fractional trimming was [introduced in 8.4.3](https://github.com/php/php-src/commit/063c3c852236ecbe45ab23c0fb271b6292ce82c3); these versions retain count-bounded copying. |

</details>

## EOL mitigations

CVE-2026-109618 and CVE-2026-109619 use the published EOL approach: [PHP 7.4](https://github.com/php/php-src/commit/712e60f9edfd03f0960fc4fc3c2c375f42b90aa2), [PHP 8.0](https://github.com/php/php-src/commit/82f8f31b16a035c1b67cc253e79f7cc288f4cc20) and [PHP 8.1](https://github.com/php/php-src/commit/1d7dec178a094e6e75b229ce22754045f8c6070a). PHP 7.1–7.3 use local adaptations of the 7.4 change, not official releases for those branches.

Handled allocation failure exits the process immediately, bypassing ordinary shutdown callbacks and destructors. In FPM, the master replaces the affected worker. Session upload progress is disabled in source. Ordinary sessions and uploads remain available. These changes do not implement the modern deferred out-of-memory recovery model.

PHP 5.6 retains its native request cleanup and multipart-part limit. It does not contain PHP 7-specific zend_string or early-RINIT guards, or the newer actual-byte multipart POST bound.

The PHP 7.1-8.1 core patches also include related bounds and early-state changes based on the [modern PHP fix](https://github.com/php/php-src/commit/506e87fa7dcdd306975e5b8f8265ee03527aacbf), with native adaptations. PHP 8.1 additionally includes changes in EXIF, mbstring/libmbfl, OPcache and zlib. The libmbfl modification limits initial allocation to the input length; it was applied to this distribution on 2026-10-22. These supplemental changes are not separate CVE claims.

## Backport sources and compatibility

The older-branch ports use published PHP changes and [Freexian's PHP 7.3 maintenance patches](https://deb.freexian.com/extended-lts/pool/main/p/php7.3/php7.3_7.3.31-1~deb10u16.debian.tar.xz), archive SHA-256 `1eedbd1095e7be93d310c2cbfd13acbcf521b2433ef0ddb5e40abd2dffa633bb`. The [ELA-1848-1 advisory](https://www.freexian.com/lts/extended/updates/ela-1848-1-php7.3/) describes an earlier release of that maintenance series.

The series includes necessary published prerequisites: [307/308 request-method preservation](https://github.com/php/php-src/commit/1ede3137c9abe19da1fafef66d8e4038c63516b4), [chunked redirect cleanup](https://github.com/php/php-src/commit/1c157d3fa2e102ccf375ec4c1ddca8770208dae7), [long header handling](https://github.com/php/php-src/commit/51e2015af3fc4757a666736aae7899a43f76ae6d) and [mysqlnd result ownership](https://github.com/php/php-src/commit/f365d0e00ed93b1c33e984ff3b4cc8677cbca193), where absent. Patch headers identify additional test and compatibility follow-ups.

Expected behavior changes include rejection of malformed MySQL packets, invalid HTTP headers, malformed Phar entries and excessive SOAP recursion. Cross-origin HTTP redirects remove credentials. TLS verification no longer falls back to a certificate's common name after a service-identity SAN mismatch. Applications depending on the previous behavior need changes.

mysqli and PDO MySQL are included as interfaces to the patched mysqlnd driver. The older maintenance series also includes applicable historical fixes in enabled extensions. [EXTENSIONS.md](EXTENSIONS.md) lists every enabled extension. Adding an extension does not imply complete CVE coverage for it, its PECL release or its linked system libraries.

## PHP 5.6 and 7.0 sources

PHP 5.6 starts with the enabled-component changes in [shivammathur/php-src-backports at 989262e3](https://github.com/shivammathur/php-src-backports/commit/989262e3e9dcca7642b644a75d209c5b7740e294), followed by [Freexian's native PHP 5.6 patches](https://deb.freexian.com/extended-lts/pool/main/p/php5/php5_5.6.40%2Bdfsg-0%2Bdeb8u23.debian.tar.xz) and the native ports identified in each patch header. The archive SHA-256 is `a9bb07558803ef70ea0f860cfdefd2006ad35fc99735dd2ef111bc6d10b9952e`.

PHP 7.0 uses the enabled-component patches from [Freexian's PHP 7.0 maintenance series](https://deb.freexian.com/extended-lts/pool/main/p/php7.0/php7.0_7.0.33-0+deb9u26.debian.tar.xz), SHA-256 `3db890cadb4debb154e9fb85aea6b10389cab36d7b52a46d7be47e5e4d4a7d08`, plus the native CVE-2025-1218 port and its published ownership prerequisite. Packaging changes and extensions not built here are excluded.

Both versions keep their native mysqlnd allocation and connection ownership contracts. PHP 5.6 SOAP uses a separate owned map for decoded values because its serialization map contains XML-node pointers. These adaptations require regression tests beyond checking whether a patch applies.

Known mysqlnd limit: the inherited statement-metadata EOF error path clears the statement structure. On PHP 5.6 this can leave null error/status pointers for its caller and terminate the PHP process after a malformed database response. The published ownership prerequisite retains this path; no separate published repair was identified. The included packet bounds fixes do not establish complete protection from a hostile database server.

The ordered series, source hashes and provenance are under `patches/5.6.40/` and `patches/7.0.33/`. They also contain older published security and compatibility changes needed by those branches. The table above covers the selected CVEs common to this project's review; it is not a complete advisory inventory for either branch.

## Other CVEs fixed in PHP 5.6 and 7.0

The original maintenance series includes **53 additional CVE IDs for PHP 5.6.40** and **59 for PHP 7.0.33**, beyond the 19 CVEs in the core/SOAP/Phar/HTTP/TLS/FPM table. The PostgreSQL additions are listed separately above. These counts describe included patch contents in enabled components, not the number of regression tests or complete post-EOL coverage.

PHP 5.6 release-note evidence is in the [frozen published NEWS](https://github.com/shivammathur/php-src-backports/blob/989262e3e9dcca7642b644a75d209c5b7740e294/NEWS). Later changes use the [native PHP 5.6 maintenance archive](https://deb.freexian.com/extended-lts/pool/main/p/php5/php5_5.6.40%2Bdfsg-0%2Bdeb8u23.debian.tar.xz) and published ports. PHP 7.0 IDs follow the selected patch filenames and headers from the [PHP 7.0 maintenance archive](https://deb.freexian.com/extended-lts/pool/main/p/php7.0/php7.0_7.0.33-0+deb9u26.debian.tar.xz). The [PHP 5.6 provenance](patches/5.6.40/provenance.json) and [PHP 7.0 provenance](patches/7.0.33/provenance.json) record the selected sources; patch headers retain upstream links.

| CVE | CVSS | Short description | PHP 5.6.40 | PHP 7.0.33 |
| --- | --- | --- | --- | --- |
| CVE-2019-11043 | [8.7 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11043.json) | PHP-FPM buffer underflow permits remote code execution in certain configurations | **Fixed** | **Fixed** |
| CVE-2026-6735 | [7.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/6xxx/CVE-2026-6735.json) | PHP-FPM status page renders attacker-controlled data as executable JavaScript | **Fixed** | **Fixed** |
| CVE-2021-21702 | [5.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2021/21xxx/CVE-2021-21702.json) | Malformed SOAP responses cause a NULL-pointer crash | **Fixed** | **Fixed** |
| CVE-2025-6491 | [5.9 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2025/6xxx/CVE-2025-6491.json) | SOAP parsing dereferences NULL for oversized XML namespace prefixes | — | **Fixed** |
| CVE-2026-7261 | [6.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/7xxx/CVE-2026-7261.json) | SoapServer session persistence mishandles faults, leaving freed object references | **Fixed** | **Fixed** |
| CVE-2026-7262 | [2.9 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/7xxx/CVE-2026-7262.json) | SOAP Apache map decoding dereferences NULL when values are missing | **Fixed** | **Fixed** |
| CVE-2022-31626 | [7.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2022/31xxx/CVE-2022-31626.json) | Oversized MySQL passwords overflow the mysqlnd/PDO buffer | **Fixed** | **Fixed** |
| CVE-2019-9021 | [9.8 (v3.0)](https://nvd.nist.gov/vuln/detail/CVE-2019-9021) | PHAR filename parsing reads beyond heap buffers | — | **Fixed** |
| CVE-2020-7063 | [5.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7063.json) | Phar archives discard restrictive file permissions | **Fixed** | **Fixed** |
| CVE-2020-7068 | [4.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7068.json) | Phar ZIP parsing accesses freed memory | **Fixed** | **Fixed** |
| CVE-2022-31628 | [2.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2022/31xxx/CVE-2022-31628.json) | Phar repeatedly decompresses self-reproducing gzip archives | **Fixed** | **Fixed** |
| CVE-2026-7260 | [5.4 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/7xxx/CVE-2026-7260.json) | Phar circular symbolic links exhaust the stack through unbounded recursion | **Fixed** | **Fixed** |
| CVE-2019-11034 | [4.8 (v3.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11034.json) | EXIF tag parsing heap over-read exposes memory or crashes | **Fixed** | **Fixed** |
| CVE-2019-11035 | [4.8 (v3.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11035.json) | EXIF value parsing heap over-read exposes memory or crashes | **Fixed** | **Fixed** |
| CVE-2019-11036 | [4.8 (v3.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11036.json) | EXIF tag parsing heap over-read exposes memory or crashes | **Fixed** | **Fixed** |
| CVE-2019-11040 | [4.8 (v3.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11040.json) | EXIF image metadata parsing reads beyond heap buffers | **Fixed** | **Fixed** |
| CVE-2019-11041 | [4.8 (v3.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11041.json) | EXIF thumbnail scanning reads beyond allocated buffers | **Fixed** | **Fixed** |
| CVE-2019-11042 | [4.8 (v3.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11042.json) | EXIF user-comment parsing reads beyond allocated buffers | **Fixed** | **Fixed** |
| CVE-2019-11047 | [4.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11047.json) | EXIF image parsing heap over-read exposes memory or crashes | **Fixed** | **Fixed** |
| CVE-2019-11050 | [4.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11050.json) | EXIF parsing use-after-free allows invalid memory reads | **Fixed** | **Fixed** |
| CVE-2020-7064 | [6.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7064.json) | EXIF parsing reads uninitialized memory | **Fixed** | **Fixed** |
| CVE-2019-11038 | [3.1 (v3.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11038.json) | GD XBM decoding uses uninitialized values, exposing stack data | **Fixed** | **Fixed** |
| CVE-2026-9672 | [8.1 (v3.1; Red Hat)](https://access.redhat.com/security/cve/cve-2026-9672) | GD GIF decoding permits buffer overflow and application crashes | **Fixed** | **Fixed** |
| CVE-2019-13224 | [9.8 (v3.1)](https://nvd.nist.gov/vuln/detail/CVE-2019-13224) | Oniguruma `onig_new_deluxe()` use-after-free with crafted multibyte regular expressions | **Fixed** | **Fixed** |
| CVE-2019-9023 | [9.8 (v3.0)](https://nvd.nist.gov/vuln/detail/CVE-2019-9023) | Mbstring regular expressions over-read heap buffers on invalid multibyte data | — | **Fixed** |
| CVE-2020-7060 | [6.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7060.json) | mbstring BIG5 conversion reads beyond its buffer | **Fixed** | **Fixed** |
| CVE-2019-18218 | [7.8 (v3.1)](https://nvd.nist.gov/vuln/detail/CVE-2019-18218) | Libmagic CDF vector parsing causes a heap buffer overflow | — | **Fixed** |
| CVE-2019-11046 | [3.7 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11046.json) | BCMath non-ASCII numeric input triggers out-of-bounds reads | **Fixed** | **Fixed** |
| CVE-2019-11039 | [4.2 (v3.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11039.json) | Iconv MIME header integer overflow causes out-of-bounds reads | **Fixed** | **Fixed** |
| CVE-2019-11045 | [3.7 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11045.json) | DirectoryIterator truncates paths at embedded NUL bytes | **Fixed** | **Fixed** |
| CVE-2022-31631 | [9.1 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2022/31xxx/CVE-2022-31631.json) | Oversized PDO SQLite strings bypass quoting, permitting SQL injection | **Fixed** | **Fixed** |
| CVE-2021-21707 | [5.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2021/21xxx/CVE-2021-21707.json) | XML filenames permit encoded-NUL path truncation | **Fixed** | **Fixed** |
| CVE-2023-0568 | [7.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2023/0xxx/CVE-2023-0568.json) | Path resolution writes one byte beyond its buffer | **Fixed** | **Fixed** |
| CVE-2023-3823 | [8.6 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2023/3xxx/CVE-2023-3823.json) | Shared libxml state unexpectedly enables external entity loading | **Fixed** | **Fixed** |
| CVE-2025-1219 | [6.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2025/1xxx/CVE-2025-1219.json) | libxml redirects use incorrect Content-Type headers for charset detection | **Fixed** | **Fixed** |
| CVE-2020-7071 | [5.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7071.json) | URL validation accepts invalid password components | **Fixed** | **Fixed** |
| CVE-2021-21705 | [4.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2021/21xxx/CVE-2021-21705.json) | Malformed URL password fields bypass validation | **Fixed** | **Fixed** |
| CVE-2024-5458 | [5.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/5xxx/CVE-2024-5458.json) | URL filtering accepts invalid user information | **Fixed** | **Fixed** |
| CVE-2023-0567 | [7.7 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2023/0xxx/CVE-2023-0567.json) | Malformed bcrypt hashes can accept an incorrect password | **Fixed** | **Fixed** |
| CVE-2024-3096 | [6.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/3xxx/CVE-2024-3096.json) | `password_verify()` accepts an empty password in a NUL case | **Fixed** | **Fixed** |
| CVE-2020-7070 | [4.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7070.json) | Cookie-name decoding bypasses secure cookie prefixes | **Fixed** | **Fixed** |
| CVE-2022-31629 | [6.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2022/31xxx/CVE-2022-31629.json) | Cookie-name normalization permits forged secure-prefix cookies | **Fixed** | **Fixed** |
| CVE-2024-2756 | [6.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/2xxx/CVE-2024-2756.json) | Incomplete cookie-prefix repair still permits forged secure cookies | **Fixed** | **Fixed** |
| CVE-2024-8925 | [3.1 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/8xxx/CVE-2024-8925.json) | Malformed multipart data suppresses legitimate request fields | **Fixed** | **Fixed** |
| CVE-2019-11048 | [5.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11048.json) | Upload memory exhaustion leaves temporary files, potentially exhausting disk space | **Fixed** | **Fixed** |
| CVE-2020-7062 | [7.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7062.json) | Failed uploads crash session progress cleanup | **Fixed** | **Fixed** |
| CVE-2023-0662 | [7.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2023/0xxx/CVE-2023-0662.json) | Excessive multipart fields exhaust CPU or log storage | **Fixed** | **Fixed** |
| CVE-2019-9022 | [7.5 (v3.0)](https://nvd.nist.gov/vuln/detail/CVE-2019-9022) | DNS CAA/ANY response parsing reads beyond allocated buffers | — | **Fixed** |
| CVE-2020-7066 | [5.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7066.json) | `get_headers()` truncates URLs at NUL bytes | **Fixed** | **Fixed** |
| CVE-2024-11233 | [4.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/11xxx/CVE-2024-11233.json) | Quoted-printable stream decoding reads beyond its buffer | **Fixed** | **Fixed** |
| CVE-2024-11234 | [4.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/11xxx/CVE-2024-11234.json) | HTTP proxy streams permit CRLF injection and request smuggling | **Fixed** | **Fixed** |
| CVE-2025-1736 | [6.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2025/1xxx/CVE-2025-1736.json) | HTTP stream header validation mishandles line endings and omits headers | **Fixed** | **Fixed** |
| CVE-2020-7059 | [6.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7059.json) | `fgetss()` reads beyond the tag-stripping buffer | **Fixed** | **Fixed** |
| CVE-2020-7067 | [7.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2020/7xxx/CVE-2020-7067.json) | EBCDIC `urldecode()` uses invalid signed array indexes | **Fixed** | **Fixed** |
| CVE-2026-7568 | [6.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/7xxx/CVE-2026-7568.json) | `metaphone()` input position overflow causes out-of-bounds memory reads | — | **Fixed** |
| CVE-2026-7258 | [6.3 (v4.0)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/7xxx/CVE-2026-7258.json) | URL decoding passes signed characters to ctype, causing NetBSD buffer overreads | **Fixed** | **Fixed** |
| CVE-2026-14355 | [5.6 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2026/14xxx/CVE-2026-14355.json) | OpenSSL AES-WRAP-PAD encryption underallocates output buffers, corrupting heap memory | **Fixed** | **Fixed** |

PHP 5.6's native HTTP additions include [CVE-2025-1219](https://github.com/php/php-src/commit/b6004a043c16b211d462218fbb3f72db68ec2b18) and [CVE-2025-1736](https://github.com/php/php-src/commit/41d49abbd99dab06cdae4834db664435f8177174). Its additional recent ports are [CVE-2026-7260](https://github.com/php/php-src/commit/2e0fa0a44441d74bf8cc4e1ce1c8af9cd4209f52), [CVE-2026-9672](https://github.com/php/php-src/commit/fcd691b377d02285740744bee17c0f298be227d5), and [CVE-2026-7258](https://github.com/php/php-src/commit/8bdee7e4c6f4e92f9e89841d53855d9ffbdbfe54).

### Inventory limits and exclusions

| CVE | CVSS | Short description | Inventory treatment |
| --- | --- | --- | --- |
| CVE-2012-1823 | [9.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2012/1xxx/CVE-2012-1823.json) | PHP-CGI query-string argument injection enables remote code execution | Contextual prerequisite reference; not counted as an additional fix |
| CVE-2019-11044 | [3.7 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2019/11xxx/CVE-2019-11044.json) | Windows `link()` truncates paths at embedded NUL bytes | Windows issue; excluded from Linux runtime counts |
| CVE-2021-21706 | [5.3 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2021/21xxx/CVE-2021-21706.json) | Windows ZIP extraction writes outside the destination directory | Windows issue; excluded from Linux runtime counts |
| CVE-2021-21704 | [5.0 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2021/21xxx/CVE-2021-21704.json) | Malformed Firebird responses cause crashes or memory corruption | PDO Firebird is disabled |
| CVE-2024-8932 | [9.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/8xxx/CVE-2024-8932.json) | Oversized `ldap_escape()` input causes an out-of-bounds write on 32-bit systems | LDAP is disabled |
| CVE-2024-4577 | [9.8 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/4xxx/CVE-2024-4577.json) | Windows PHP-CGI permits command-line argument injection | CGI is disabled |
| CVE-2024-8926 | [8.1 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/8xxx/CVE-2024-8926.json) | Windows code pages bypass the CGI argument-injection repair | CGI is disabled |
| CVE-2024-8927 | [7.5 (v3.1)](https://raw.githubusercontent.com/CVEProject/cvelistV5/main/cves/2024/8xxx/CVE-2024-8927.json) | Environment-variable collision bypasses CGI redirect protection | CGI is disabled |

- Windows-oriented CVE-2019-11044 and CVE-2021-21706 are excluded from Linux runtime counts. PHP 5.6 retains the ZIP source change for the latter, but it is not counted here.
- Disabled components are excluded: LDAP CVE-2024-8932; CGI CVE-2024-4577, CVE-2024-8926 and CVE-2024-8927; PDO Firebird CVE-2021-21704.
- PHP 5.6 receives no additional patch for the reported CVE-2025-6491 and CVE-2026-7568 vectors. Its native `str_repeat` limit and included concatenation bound reject the respective strings exceeding `INT_MAX` before SOAP or metaphone runs. This describes those published reproductions, not a broader review of those functions.
- Older PHP 7.0 patches named only by a PHP bug number are mapped to the explicit CVE attached to the same bug number in the frozen PHP 5.6 NEWS. Test-only patches, prerequisite-only references and the contextual CVE-2012-1823 mention are not counted as additional fixes.
- PHP 5.6's AES-WRAP-PAD NEWS entry has no CVE number. Its [published backport](https://github.com/shivammathur/php-src-backports/commit/5c2cc46b45cf286666671df14edf25fc23676cd8) cites [the same upstream change](https://github.com/php/php-src/commit/cbc0489126a7682796aad1e5fb4e51de74af162c) as the PHP 7.0 CVE-2026-14355 patch, which supplies that precise mapping.
- Published GHSA fixes without a CVE assignment in these sources are not assigned invented CVE numbers. Fixes already present in official PHP 5.6.40 or 7.0.33 are not counted as new backports.
