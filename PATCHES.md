# Included security changes

These builds contain selected published PHP security fixes and native backports. They are not a complete collection of fixes released after each branch reached end of life. Upgrade applications to a supported official PHP release as soon as possible.

The table describes the patch contents. It does not claim that every affected code path, architecture or application has been tested.

- **B**: backported in this repository.
- **U**: already present in that PHP release.
- **M**: upstream EOL mitigation, with branch adaptations and related defensive changes.

| CVE | Component | 5.6.40 | 7.0.33 | 7.1.33 | 7.2.34 | 7.3.33 | 7.4.33 | 8.0.30 | 8.1.34 | Published change |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| [CVE-2026-109618](https://github.com/php/php-src/security/advisories/GHSA-w8hw-w9fx-j79w) | Allocation failure and request cleanup | M | M | M | M | M | M | M | M | EOL changes below |
| [CVE-2026-109619](https://github.com/php/php-src/security/advisories/GHSA-6wmf-4rv5-p3rp) | Stale request state | M | M | M | M | M | M | M | M | EOL changes below |
| [CVE-2026-6722](https://github.com/php/php-src/security/advisories/GHSA-85c2-q967-79q5) | SOAP value ownership | B | B | B | B | B | B | B | B | [aee3b3ac](https://github.com/php/php-src/commit/aee3b3ac9b816b0def1c462695b483b49a83148e) |
| [CVE-2026-91765](https://github.com/php/php-src/security/advisories/GHSA-rgrp-mwpx-f6rm) | SOAP recursion | B | B | B | B | B | B | B | B | [3655b79c](https://github.com/php/php-src/commit/3655b79c7bfa90db004d75a3b282ed0f538f4836) |
| [CVE-2025-14181](https://github.com/php/php-src/security/advisories/GHSA-cj93-vc83-wgqv) | SOAP response length | B | B | B | B | B | B | B | B | [b11bd1d9](https://github.com/php/php-src/commit/b11bd1d9390bb203684e3d4aa5e1104ea842de6f) |
| [CVE-2026-6103](https://github.com/php/php-src/security/advisories/GHSA-j3wh-g957-2m85) | Phar TAR entries | B | B | B | B | B | B | B | B | [0994e2e8](https://github.com/php/php-src/commit/0994e2e887cd993866b5e4d7dba2fade5caeefec) |
| [CVE-2023-3824](https://github.com/php/php-src/security/advisories/GHSA-jqcx-ccgc-xwhv) | Phar directory reads | B | B | B | B | B | B | U | U | [80316123](https://github.com/php/php-src/commit/80316123f3e9dcce8ac419bd9dd43546e2ccb5ef) |
| [CVE-2026-91766](https://github.com/php/php-src/security/advisories/GHSA-fpwc-w8rq-cr92) | HTTP redirect credentials | B | B | B | B | B | B | B | B | [5af9465c](https://github.com/php/php-src/commit/5af9465ca890be679033d5fb0dc89b6ea071326b) |
| [CVE-2026-93682](https://github.com/php/php-src/security/advisories/GHSA-7875-c8px-7q5f) | Empty HTTP redirect location | B | B | B | B | B | B | B | B | [231f29fd](https://github.com/php/php-src/commit/231f29fdfd94a571b36c5f772367329c3af67ca7), [follow-up](https://github.com/php/php-src/pull/23521) |
| [CVE-2025-1217](https://github.com/php/php-src/security/advisories/GHSA-v8xr-gpvj-cx9g) | Folded HTTP headers | B | B | B | B | B | B | B | U | [d20b4c97](https://github.com/php/php-src/commit/d20b4c97a9f883b62b65b82d939c5af9a2028ef1) |
| [CVE-2025-1734](https://github.com/php/php-src/security/advisories/GHSA-pcmh-g36c-qc44) | Invalid HTTP headers | B | B | B | B | B | B | B | U | [0548c4c1](https://github.com/php/php-src/commit/0548c4c1756724a89ef8310709419b08aadb2b3b) |
| [CVE-2025-1861](https://github.com/php/php-src/security/advisories/GHSA-52jp-hrpf-2jff) | HTTP redirect truncation | B | B | B | B | B | B | B | U | [ac1a054b](https://github.com/php/php-src/commit/ac1a054bb3eb5994a199e8b18cca28cbabf5943e) |
| [CVE-2026-91767](https://github.com/php/php-src/security/advisories/GHSA-xr7j-rvgx-xq5p) | TLS wildcard bounds | B | B | B | B | B | B | B | B | [78cc82b3](https://github.com/php/php-src/commit/78cc82b37a340840c6fbff676f513eb4e3d96533) |
| [CVE-2026-91769](https://github.com/php/php-src/security/advisories/GHSA-vvx9-73fr-5jjx) | TLS SAN/CN verification | B | B | B | B | B | B | B | B | [0bb308eb](https://github.com/php/php-src/commit/0bb308eb4cf699e707efbcf97ffb70e46a176be7) |
| [CVE-2026-92842](https://github.com/php/php-src/security/advisories/GHSA-88hq-2827-7pg6) | Standard stream filters | B | B | B | B | B | B | B | B | [b4e3397e](https://github.com/php/php-src/commit/b4e3397ec8f4f647e6e019dbf79870fc2f14ba78) |
| [CVE-2026-91768](https://github.com/php/php-src/security/advisories/GHSA-62xp-839h-2637) | FastCGI IPv6 client matching | B | B | B | B | B | B | B | B | [dcdfcf86](https://github.com/php/php-src/commit/dcdfcf86fcf7f42fa98dd3d000f310ebdad41965) |
| [CVE-2024-8929](https://github.com/php/php-src/security/advisories/GHSA-h35g-vwh6-m678) | mysqlnd packet reads | B | B | B | B | B | B | B | U | [2f5aa9f9](https://github.com/php/php-src/commit/2f5aa9f9d150ca56e356f3ca9acf9d530108cb08), [32f905f1](https://github.com/php/php-src/commit/32f905f1d689aaa8eacd6331a18c0dd45972c3c1) |
| [CVE-2025-1218](https://github.com/php/php-src/security/advisories/GHSA-r6x9-5r99-36j7) | mysqlnd packet reads | B | B | B | B | B | B | B | B | [114dbb74](https://github.com/php/php-src/commit/114dbb74368e6a6cd6e48ddb4a76dbe8728b84e2) |
| [CVE-2021-21703](https://www.php.net/ChangeLog-7.php#7.3.31) | FPM scoreboard | B | B | B | B | U | U | U | U | [cb2021e5](https://github.com/php/php-src/commit/cb2021e5f69da5e2868130a05bb53db0f9f89e4b) |

## PostgreSQL

Both pgsql and PDO PostgreSQL are enabled in every image. These fixes cover selected published extension defects; they do not establish complete PostgreSQL-related CVE coverage.

| CVE | Component | 5.6.40 | 7.0.33 | 7.1.33 | 7.2.34 | 7.3.33 | 7.4.33 | 8.0.30 | 8.1.34 | Published change |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| CVE-2022-31625 | pgsql parameter cleanup | B | B | B | B | B | U | U | U | [55f6895f](https://github.com/php/php-src/commit/55f6895f4b4c677272fd4ee1113acdbd99c4b5ab) |
| [CVE-2025-1735](https://github.com/php/php-src/security/advisories/GHSA-hrwm-9436-5mv3) | pgsql and PDO PostgreSQL escaping failures | B | B | B | B | B | B | B | U | [9376aeef](https://github.com/php/php-src/commit/9376aeef9f8ff81f2705b8016237ec3e30bdee44) |
| [CVE-2026-17543](https://github.com/php/php-src/security/advisories/GHSA-7qpv-r5mr-78m4) | pgsql string literal escaping | B | B | B | B | B | B | B | B | [ab048bd8](https://github.com/php/php-src/commit/ab048bd83b578119cf81b456526d50498421d617) |

[CVE-2025-14180](https://github.com/php/php-src/security/advisories/GHSA-8xr5-qppj-gvwj) is already fixed in PHP 8.1.34. The older branches retain their integer-returning PDO quoter ABI, which already propagates quote failure; they do not need the newer zend_string-returning fix. The published invalid-byte regression is included for all eight versions.

Each version's `postgresql/` patch directory records upstream commits, native maintenance sources, adaptations and before/after source hashes. Tests exercise the published failure inputs and ordinary queries through both interfaces, including SCRAM authentication and certificate-verified TLS in CLI and FPM.

The extensions use [libpq 16.15](https://www.postgresql.org/docs/16/release-16-15.html), built from the pinned upstream source in [extensions/libpq.tsv](extensions/libpq.tsv). This includes the client-library repairs for [CVE-2025-1094](https://www.postgresql.org/support/security/CVE-2025-1094/) and [CVE-2026-6477](https://www.postgresql.org/support/security/CVE-2026-6477/). Substituting an older libpq can reintroduce client-library defects. Other linked libraries, including OpenSSL, retain the limits of the base image.

## Additional GD, OpenSSL and Phar fixes

The following fixes were already included in the PHP 5.6 and 7.0 maintenance series and are also backported to PHP 7.1–8.1:

| CVE | Component | Published change | Verification scope |
| --- | --- | --- | --- |
| CVE-2026-9672 | GD GIF decoder | [fcd691b3](https://github.com/php/php-src/commit/fcd691b377d02285740744bee17c0f298be227d5) | Exact published changes, compilation and ordinary GIF decoding; no CVE-specific fixture was supplied upstream. |
| CVE-2026-14355 | OpenSSL AES-WRAP-PAD allocation | [cbc04891](https://github.com/php/php-src/commit/cbc0489126a7682796aad1e5fb4e51de74af162c) | Published round-trip regression executes on PHP 8.1. The cipher is unavailable in the older builds; those skips are not passing exploit tests. |
| CVE-2026-7260 | Phar circular symbolic links | [2e0fa0a4](https://github.com/php/php-src/commit/2e0fa0a44441d74bf8cc4e1ce1c8af9cd4209f52) | Published archive fixtures and ordinary nested symbolic links. |

The Phar backports also retain the filename separator after resolving a nested link, following the [published native compatibility repair](https://repo.tuxcare.com/security/errata/els_alt_php/debian12/CLSA-2026-1786517269.html). This repair applies to all eight versions. Per-version `coverage/` directories contain the patch provenance and source hashes.

## EOL mitigations

The first two rows use the published EOL approach: [PHP 7.4](https://github.com/php/php-src/commit/712e60f9edfd03f0960fc4fc3c2c375f42b90aa2), [PHP 8.0](https://github.com/php/php-src/commit/82f8f31b16a035c1b67cc253e79f7cc288f4cc20) and [PHP 8.1](https://github.com/php/php-src/commit/1d7dec178a094e6e75b229ce22754045f8c6070a). PHP 7.1–7.3 use local adaptations of the 7.4 change, not official releases for those branches.

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

## Additional PHP 5.6 and 7.0 CVE inventory

The original maintenance series includes **53 additional CVE IDs for PHP 5.6.40** and **59 for PHP 7.0.33**, beyond the 19 CVEs in the main table. The PostgreSQL additions are listed separately above. These counts describe included patch contents in enabled components, not the number of regression tests or complete post-EOL coverage.

PHP 5.6 release-note evidence is in the [frozen published NEWS](https://github.com/shivammathur/php-src-backports/blob/989262e3e9dcca7642b644a75d209c5b7740e294/NEWS). Later changes use the [native PHP 5.6 maintenance archive](https://deb.freexian.com/extended-lts/pool/main/p/php5/php5_5.6.40%2Bdfsg-0%2Bdeb8u23.debian.tar.xz) and published ports. PHP 7.0 IDs follow the selected patch filenames and headers from the [PHP 7.0 maintenance archive](https://deb.freexian.com/extended-lts/pool/main/p/php7.0/php7.0_7.0.33-0+deb9u26.debian.tar.xz). The [PHP 5.6 provenance](patches/5.6.40/provenance.json) and [PHP 7.0 provenance](patches/7.0.33/provenance.json) record the selected sources; patch headers retain upstream links.

| Component | PHP 5.6.40 additional IDs | PHP 7.0.33 additional IDs |
| --- | --- | --- |
| FPM | CVE-2019-11043, CVE-2026-6735 | CVE-2019-11043, CVE-2026-6735 |
| SOAP | CVE-2021-21702, CVE-2023-3247, CVE-2026-7261, CVE-2026-7262 | CVE-2021-21702, CVE-2023-3247, CVE-2025-6491, CVE-2026-7261, CVE-2026-7262 |
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
| Streams / HTTP / DNS | CVE-2020-7066, CVE-2024-11233, CVE-2024-11234, CVE-2025-1220, CVE-2025-1736 | CVE-2019-9022, CVE-2020-7066, CVE-2024-11233, CVE-2024-11234, CVE-2025-1220, CVE-2025-1736 |
| Standard string routines | CVE-2020-7059, CVE-2020-7067 | CVE-2020-7059, CVE-2020-7067, CVE-2026-7568 |
| Core / enabled extensions | CVE-2026-7258 | CVE-2026-7258 |
| OpenSSL | CVE-2026-14355 | CVE-2026-14355 |

PHP 5.6's native HTTP additions include [CVE-2025-1219](https://github.com/php/php-src/commit/b6004a043c16b211d462218fbb3f72db68ec2b18) and [CVE-2025-1736](https://github.com/php/php-src/commit/41d49abbd99dab06cdae4834db664435f8177174). Its additional recent ports are [CVE-2025-1220](https://github.com/php/php-src/commit/cac8f7f1cf4939f55f06b68120040f057682d89c), [CVE-2026-7260](https://github.com/php/php-src/commit/2e0fa0a44441d74bf8cc4e1ce1c8af9cd4209f52), [CVE-2026-9672](https://github.com/php/php-src/commit/fcd691b377d02285740744bee17c0f298be227d5), and [CVE-2026-7258](https://github.com/php/php-src/commit/8bdee7e4c6f4e92f9e89841d53855d9ffbdbfe54).

Inventory limits and exclusions:

- Windows-oriented CVE-2019-11044 and CVE-2021-21706 are excluded from Linux runtime counts. PHP 5.6 retains the ZIP source change for the latter, but it is not counted here.
- Disabled components are excluded: LDAP CVE-2024-8932; CGI CVE-2024-4577, CVE-2024-8926 and CVE-2024-8927; PDO Firebird CVE-2021-21704.
- PHP 5.6 receives no additional patch for the reported CVE-2025-6491 and CVE-2026-7568 vectors. Its native `str_repeat` limit and included concatenation bound reject the respective strings exceeding `INT_MAX` before SOAP or metaphone runs. This describes those published reproductions, not a broader review of those functions.
- Older PHP 7.0 patches named only by a PHP bug number are mapped to the explicit CVE attached to the same bug number in the frozen PHP 5.6 NEWS. Test-only patches, prerequisite-only references and the contextual CVE-2012-1823 mention are not counted as additional fixes.
- PHP 5.6's AES-WRAP-PAD NEWS entry has no CVE number. Its [published backport](https://github.com/shivammathur/php-src-backports/commit/5c2cc46b45cf286666671df14edf25fc23676cd8) cites [the same upstream change](https://github.com/php/php-src/commit/cbc0489126a7682796aad1e5fb4e51de74af162c) as the PHP 7.0 CVE-2026-14355 patch, which supplies that precise mapping.
- Published GHSA fixes without a CVE assignment in these sources are not assigned invented CVE numbers. Fixes already present in official PHP 5.6.40 or 7.0.33 are not counted as new backports.
