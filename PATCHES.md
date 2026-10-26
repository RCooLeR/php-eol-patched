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

## CVEs with missing fixes

| CVE | Not fixed in | Fixed in |
| --- | --- | --- |
| CVE-2023-3247 | **7.1–7.4**; candidate backports failed the compressed-request failure test | Published fix included in 5.6, 7.0, 8.0, 8.1; limitations below |
| CVE-2024-2408 | **5.6–7.1, 8.1** | **7.2–8.0 via OpenSSL**, according to Debian's fixed-package criteria |
| CVE-2025-1220 | **7.1–8.0; persistent-socket gap (see below)** | No complete fix claimed |

These remaining gaps require a compatible backport, a dependency repair or a
published follow-up that covers the remaining path. The two critical EOL
mitigations are listed separately below and are not complete fixes.

## Core, SOAP, Phar, HTTP, TLS and FPM

| CVE | Status | PHP builds |
| --- | --- | --- |
| [CVE-2026-109618](https://github.com/php/php-src/security/advisories/GHSA-w8hw-w9fx-j79w) | **Mitigated, not fully fixed** | All eight builds |
| [CVE-2026-109619](https://github.com/php/php-src/security/advisories/GHSA-6wmf-4rv5-p3rp) | **Mitigated, not fully fixed** | All eight builds |
| [CVE-2026-6722](https://github.com/php/php-src/security/advisories/GHSA-85c2-q967-79q5) | **Fixed** | All eight builds |
| [CVE-2026-91765](https://github.com/php/php-src/security/advisories/GHSA-rgrp-mwpx-f6rm) | **Fixed** | All eight builds |
| [CVE-2025-14181](https://github.com/php/php-src/security/advisories/GHSA-cj93-vc83-wgqv) | **Fixed** | All eight builds |
| [CVE-2026-6103](https://github.com/php/php-src/security/advisories/GHSA-j3wh-g957-2m85) | **Fixed** | All eight builds |
| [CVE-2023-3824](https://github.com/php/php-src/security/advisories/GHSA-jqcx-ccgc-xwhv) | **Fixed** | All eight builds |
| [CVE-2026-91766](https://github.com/php/php-src/security/advisories/GHSA-fpwc-w8rq-cr92) | **Fixed** | All eight builds |
| [CVE-2026-93682](https://github.com/php/php-src/security/advisories/GHSA-7875-c8px-7q5f) | **Fixed** | All eight builds |
| [CVE-2025-1217](https://github.com/php/php-src/security/advisories/GHSA-v8xr-gpvj-cx9g) | **Fixed** | All eight builds |
| [CVE-2025-1734](https://github.com/php/php-src/security/advisories/GHSA-pcmh-g36c-qc44) | **Fixed** | All eight builds |
| [CVE-2025-1861](https://github.com/php/php-src/security/advisories/GHSA-52jp-hrpf-2jff) | **Fixed** | All eight builds |
| [CVE-2026-91767](https://github.com/php/php-src/security/advisories/GHSA-xr7j-rvgx-xq5p) | **Fixed** | All eight builds |
| [CVE-2026-91769](https://github.com/php/php-src/security/advisories/GHSA-vvx9-73fr-5jjx) | **Fixed** | All eight builds |
| [CVE-2026-92842](https://github.com/php/php-src/security/advisories/GHSA-88hq-2827-7pg6) | **Fixed** | All eight builds |
| [CVE-2026-91768](https://github.com/php/php-src/security/advisories/GHSA-62xp-839h-2637) | **Fixed** | All eight builds |
| [CVE-2024-8929](https://github.com/php/php-src/security/advisories/GHSA-h35g-vwh6-m678) | **Fixed** | All eight builds |
| [CVE-2025-1218](https://github.com/php/php-src/security/advisories/GHSA-r6x9-5r99-36j7) | **Fixed** | All eight builds |
| [CVE-2021-21703](https://www.php.net/ChangeLog-7.php#7.3.31) | **Fixed** | All eight builds |

## PostgreSQL

Both pgsql and PDO PostgreSQL are enabled in every image. These fixes cover selected published extension defects; they do not establish complete PostgreSQL-related CVE coverage.

| CVE | Status | PHP builds |
| --- | --- | --- |
| CVE-2022-31625 | **Fixed** | All eight builds |
| [CVE-2025-1735](https://github.com/php/php-src/security/advisories/GHSA-hrwm-9436-5mv3) | **Fixed** | All eight builds |
| [CVE-2026-17543](https://github.com/php/php-src/security/advisories/GHSA-7qpv-r5mr-78m4) | **Fixed** | All eight builds |

[CVE-2025-14180](https://github.com/php/php-src/security/advisories/GHSA-8xr5-qppj-gvwj) is already fixed in PHP 8.1.34. The older branches retain their integer-returning PDO quoter ABI, which already propagates quote failure; they do not need the newer zend_string-returning fix. The published invalid-byte regression is included for all eight versions.

Each version's `postgresql/` patch directory records upstream commits, native maintenance sources, adaptations and before/after source hashes. Tests exercise the published failure inputs and ordinary queries through both interfaces, including SCRAM authentication and certificate-verified TLS in CLI and FPM.

The extensions use [libpq 16.15](https://www.postgresql.org/docs/16/release-16-15.html), built from the pinned upstream source in [extensions/libpq.tsv](extensions/libpq.tsv). This includes the client-library repairs for [CVE-2025-1094](https://www.postgresql.org/support/security/CVE-2025-1094/) and [CVE-2026-6477](https://www.postgresql.org/support/security/CVE-2026-6477/). Substituting an older libpq can reintroduce client-library defects. Other linked libraries, including OpenSSL, retain the limits of the base image.

## Additional GD, OpenSSL and Phar fixes

The following fixes were already included in the PHP 5.6 and 7.0 maintenance series and are also backported to PHP 7.1–8.1:

| CVE | Status | PHP builds |
| --- | --- | --- |
| CVE-2026-9672 | **Fixed** | All eight builds |
| CVE-2026-14355 | **Fixed** | All eight builds |
| CVE-2026-7260 | **Fixed** | All eight builds |

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

| CVE | Status | PHP builds |
| --- | --- | --- |
| CVE-2016-10166 | **Fixed** | All eight builds |
| CVE-2019-9641 | **Fixed** | All eight builds |
| CVE-2020-7059 | **Fixed** | 5.6–7.4; not affected: 8.0, 8.1 |
| CVE-2020-7060 | **Fixed** | All eight builds |
| CVE-2022-31631 | **Fixed** | All eight builds |
| CVE-2022-37454 | **Fixed (see note)** | 7.2–8.1; not affected: 5.6–7.1 |

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

| CVE | Status | PHP builds |
| --- | --- | --- |
| CVE-2019-6977 | **Fixed** | 7.0 |
| CVE-2019-9637 | **Fixed** | 5.6, 7.0 |
| CVE-2019-9638 | **Fixed** | 5.6, 7.0 |
| CVE-2019-9639 | **Fixed** | 5.6, 7.0 |
| CVE-2019-9640 | **Fixed** | 5.6, 7.0 |
| CVE-2019-9675 | **Fixed** | 5.6, 7.0 |
| CVE-2021-21702 | **Fixed** | 5.6–7.2 |
| CVE-2023-0662 | **Fixed** | All eight builds |
| CVE-2025-14178 | **Fixed** | 7.2–8.1; not affected: 5.6–7.1 |
| CVE-2019-11048 | **Fixed** | 5.6, 7.0, 7.1 |
| CVE-2020-7068 | **Fixed** | 7.1 |
| CVE-2022-31626 | **Fixed** | 5.6–7.3 |
| CVE-2023-0568 | **Fixed** | 5.6–7.4 |
| CVE-2023-3823 | **Fixed** | 5.6–7.4 |
| CVE-2024-11233 | **Fixed** | All eight builds |
| CVE-2024-11234 | **Fixed** | All eight builds |
| CVE-2024-9026 | **Fixed** | 7.4–8.1; not affected: 5.6–7.3 |
| CVE-2026-6735 | **Fixed** | All eight builds |
| CVE-2026-7259 | **Fixed** | 8.0, 8.1; not affected: 5.6–7.4 |
| CVE-2026-7262 | **Fixed** | All eight builds |

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

| CVE | Status | PHP builds |
| --- | --- | --- |
| CVE-2026-7258 | **Fixed (published repair; see scope below)** | All eight builds |
| CVE-2019-11045 | **Fixed** | 7.1 |
| CVE-2019-11046 | **Fixed** | 7.1 |
| CVE-2019-11047 | **Fixed** | 7.1 |
| CVE-2019-11050 | **Fixed** | 7.1 |
| CVE-2020-7063 | **Fixed** | 7.1 |
| CVE-2020-7064 | **Fixed** | 7.1 |
| CVE-2020-7066 | **Fixed** | 7.1 |
| CVE-2020-7069 | **Fixed** | 7.1 |
| CVE-2020-7070 | **Fixed** | 7.1 |
| CVE-2020-7071 | **Fixed** | 7.1, 7.2 |
| CVE-2021-21705 | **Fixed** | 7.1, 7.2 |
| CVE-2021-21707 | **Fixed** | 7.1, 7.2 |
| CVE-2022-31628 | **Fixed** | 7.1–7.3 |
| CVE-2022-31629 | **Fixed** | 7.1–7.3 |
| CVE-2022-4900 | **Fixed** | 7.4 |
| CVE-2023-0567 | **Fixed** | 7.1–7.4 |
| CVE-2024-2756 | **Fixed** | 7.1–8.0 |
| CVE-2024-3096 | **Fixed** | 7.1–8.0 |
| CVE-2024-5458 | **Fixed** | 7.1–8.0 |
| CVE-2024-8925 | **Fixed** | 7.1–8.0 |
| CVE-2025-1219 | **Fixed** | 7.1–8.0 |
| CVE-2025-1736 | **Fixed** | 7.1–8.0 |
| CVE-2025-6491 | **Fixed** | 7.1–8.0 |
| CVE-2026-7261 | **Fixed** | 7.1–8.1 |
| CVE-2026-7568 | **Fixed** | 7.1–8.1 |

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

| CVE | Status | PHP builds |
| --- | --- | --- |
| CVE-2019-19246 | **Not affected** | 5.6–7.1 |
| CVE-2020-7065 | **Not affected** | 5.6–7.1 |
| CVE-2020-7067 | **Not affected** | 7.1; EBCDIC support is disabled |
| CVE-2022-31630 | **Not affected** | 5.6–7.3 |
| CVE-2024-2757 | **Not affected** | 5.6–8.0 |
| CVE-2026-12184 | **Not affected** | All eight builds |
| CVE-2026-17544 | **Not affected** | All eight builds |
| CVE-2020-7069 | **Not affected** | 5.6, 7.0; the affected AEAD API is absent |
| CVE-2025-14177 | **Not affected** | 5.6–8.0; the affected chunked-reader implementation is absent |
| CVE-2026-6104 | **Not affected** | All eight builds; the affected encoding-name lookup is absent |
| CVE-2026-7263 | **Not affected** | All eight builds; the affected modern DOM implementation is absent |
| CVE-2024-9026 | **Not affected** | 5.6–7.3; the affected split flush-marker parser is absent |
| CVE-2026-7259 | **Not affected** | 5.6–7.4; the affected cached mbfl-encoding pointer is absent |

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

| Component | Fixed in PHP 5.6.40 | Fixed in PHP 7.0.33 |
| --- | --- | --- |
| FPM | CVE-2019-11043, CVE-2026-6735 | CVE-2019-11043, CVE-2026-6735 |
| SOAP | CVE-2021-21702, CVE-2026-7261, CVE-2026-7262 | CVE-2021-21702, CVE-2025-6491, CVE-2026-7261, CVE-2026-7262 |
| mysqlnd | CVE-2022-31626 | CVE-2022-31626 |
| Phar | CVE-2020-7063, CVE-2020-7068, CVE-2022-31628, CVE-2026-7260 | CVE-2019-9021, CVE-2020-7063, CVE-2020-7068, CVE-2022-31628, CVE-2026-7260 |
| EXIF | CVE-2019-11034, CVE-2019-11035, CVE-2019-11036, CVE-2019-11040, CVE-2019-11041, CVE-2019-11042, CVE-2019-11047, CVE-2019-11050, CVE-2020-7064 | CVE-2019-11034, CVE-2019-11035, CVE-2019-11036, CVE-2019-11040, CVE-2019-11041, CVE-2019-11042, CVE-2019-11047, CVE-2019-11050, CVE-2020-7064 |
| GD | CVE-2019-11038, CVE-2026-9672 | CVE-2019-11038, CVE-2026-9672 |
| mbstring / Oniguruma | CVE-2019-13224, CVE-2020-7060 | CVE-2019-13224, CVE-2019-9023, CVE-2020-7060 |
| Fileinfo | — | CVE-2019-18218 |
| Bcmath | CVE-2019-11046 | CVE-2019-11046 |
| Iconv | CVE-2019-11039 | CVE-2019-11039 |
| SPL | CVE-2019-11045 | CVE-2019-11045 |
| PDO SQLite | CVE-2022-31631 | CVE-2022-31631 |
| XML / Libxml | CVE-2021-21707, CVE-2023-0568, CVE-2023-3823, CVE-2025-1219 | CVE-2021-21707, CVE-2023-0568, CVE-2023-3823, CVE-2025-1219 |
| URL validation | CVE-2020-7071, CVE-2021-21705, CVE-2024-5458 | CVE-2020-7071, CVE-2021-21705, CVE-2024-5458 |
| Passwords | CVE-2023-0567, CVE-2024-3096 | CVE-2023-0567, CVE-2024-3096 |
| Cookies / request input | CVE-2020-7070, CVE-2022-31629, CVE-2024-2756, CVE-2024-8925 | CVE-2020-7070, CVE-2022-31629, CVE-2024-2756, CVE-2024-8925 |
| Multipart / session | CVE-2019-11048, CVE-2020-7062, CVE-2023-0662 | CVE-2019-11048, CVE-2020-7062, CVE-2023-0662 |
| Streams / HTTP / DNS | CVE-2020-7066, CVE-2024-11233, CVE-2024-11234, CVE-2025-1736 | CVE-2019-9022, CVE-2020-7066, CVE-2024-11233, CVE-2024-11234, CVE-2025-1736 |
| Standard string routines | CVE-2020-7059, CVE-2020-7067 | CVE-2020-7059, CVE-2020-7067, CVE-2026-7568 |
| Core / enabled extensions | CVE-2026-7258 | CVE-2026-7258 |
| OpenSSL | CVE-2026-14355 | CVE-2026-14355 |

PHP 5.6's native HTTP additions include [CVE-2025-1219](https://github.com/php/php-src/commit/b6004a043c16b211d462218fbb3f72db68ec2b18) and [CVE-2025-1736](https://github.com/php/php-src/commit/41d49abbd99dab06cdae4834db664435f8177174). Its additional recent ports are [CVE-2026-7260](https://github.com/php/php-src/commit/2e0fa0a44441d74bf8cc4e1ce1c8af9cd4209f52), [CVE-2026-9672](https://github.com/php/php-src/commit/fcd691b377d02285740744bee17c0f298be227d5), and [CVE-2026-7258](https://github.com/php/php-src/commit/8bdee7e4c6f4e92f9e89841d53855d9ffbdbfe54).

Inventory limits and exclusions:

- Windows-oriented CVE-2019-11044 and CVE-2021-21706 are excluded from Linux runtime counts. PHP 5.6 retains the ZIP source change for the latter, but it is not counted here.
- Disabled components are excluded: LDAP CVE-2024-8932; CGI CVE-2024-4577, CVE-2024-8926 and CVE-2024-8927; PDO Firebird CVE-2021-21704.
- PHP 5.6 receives no additional patch for the reported CVE-2025-6491 and CVE-2026-7568 vectors. Its native `str_repeat` limit and included concatenation bound reject the respective strings exceeding `INT_MAX` before SOAP or metaphone runs. This describes those published reproductions, not a broader review of those functions.
- Older PHP 7.0 patches named only by a PHP bug number are mapped to the explicit CVE attached to the same bug number in the frozen PHP 5.6 NEWS. Test-only patches, prerequisite-only references and the contextual CVE-2012-1823 mention are not counted as additional fixes.
- PHP 5.6's AES-WRAP-PAD NEWS entry has no CVE number. Its [published backport](https://github.com/shivammathur/php-src-backports/commit/5c2cc46b45cf286666671df14edf25fc23676cd8) cites [the same upstream change](https://github.com/php/php-src/commit/cbc0489126a7682796aad1e5fb4e51de74af162c) as the PHP 7.0 CVE-2026-14355 patch, which supplies that precise mapping.
- Published GHSA fixes without a CVE assignment in these sources are not assigned invented CVE numbers. Fixes already present in official PHP 5.6.40 or 7.0.33 are not counted as new backports.
