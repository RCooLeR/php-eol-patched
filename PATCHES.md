# Included security changes

These builds contain selected published PHP security fixes and native backports. They are not a complete collection of fixes released after each branch reached end of life. Upgrade applications to a supported official PHP release as soon as possible.

The table describes the patch contents. It does not claim that every affected code path, architecture or application has been tested.

- **B**: backported in this repository.
- **U**: already present in that PHP release.
- **M**: upstream EOL mitigation, with branch adaptations and related defensive changes.

| CVE | Component | 7.1.33 | 7.2.34 | 7.3.33 | 7.4.33 | 8.0.30 | 8.1.34 | Published change |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| [CVE-2026-109618](https://github.com/php/php-src/security/advisories/GHSA-w8hw-w9fx-j79w) | Allocation failure and request cleanup | M | M | M | M | M | M | EOL changes below |
| [CVE-2026-109619](https://github.com/php/php-src/security/advisories/GHSA-6wmf-4rv5-p3rp) | Stale request state | M | M | M | M | M | M | EOL changes below |
| [CVE-2026-6722](https://github.com/php/php-src/security/advisories/GHSA-85c2-q967-79q5) | SOAP value ownership | B | B | B | B | B | B | [aee3b3ac](https://github.com/php/php-src/commit/aee3b3ac9b816b0def1c462695b483b49a83148e) |
| [CVE-2026-91765](https://github.com/php/php-src/security/advisories/GHSA-rgrp-mwpx-f6rm) | SOAP recursion | B | B | B | B | B | B | [3655b79c](https://github.com/php/php-src/commit/3655b79c7bfa90db004d75a3b282ed0f538f4836) |
| [CVE-2025-14181](https://github.com/php/php-src/security/advisories/GHSA-cj93-vc83-wgqv) | SOAP response length | B | B | B | B | B | B | [b11bd1d9](https://github.com/php/php-src/commit/b11bd1d9390bb203684e3d4aa5e1104ea842de6f) |
| [CVE-2026-6103](https://github.com/php/php-src/security/advisories/GHSA-j3wh-g957-2m85) | Phar TAR entries | B | B | B | B | B | B | [0994e2e8](https://github.com/php/php-src/commit/0994e2e887cd993866b5e4d7dba2fade5caeefec) |
| [CVE-2023-3824](https://github.com/php/php-src/security/advisories/GHSA-jqcx-ccgc-xwhv) | Phar directory reads | B | B | B | B | U | U | [80316123](https://github.com/php/php-src/commit/80316123f3e9dcce8ac419bd9dd43546e2ccb5ef) |
| [CVE-2026-91766](https://github.com/php/php-src/security/advisories/GHSA-fpwc-w8rq-cr92) | HTTP redirect credentials | B | B | B | B | B | B | [5af9465c](https://github.com/php/php-src/commit/5af9465ca890be679033d5fb0dc89b6ea071326b) |
| [CVE-2026-93682](https://github.com/php/php-src/security/advisories/GHSA-7875-c8px-7q5f) | Empty HTTP redirect location | B | B | B | B | B | B | [231f29fd](https://github.com/php/php-src/commit/231f29fdfd94a571b36c5f772367329c3af67ca7), [follow-up](https://github.com/php/php-src/pull/23521) |
| [CVE-2025-1217](https://github.com/php/php-src/security/advisories/GHSA-v8xr-gpvj-cx9g) | Folded HTTP headers | B | B | B | B | B | U | [d20b4c97](https://github.com/php/php-src/commit/d20b4c97a9f883b62b65b82d939c5af9a2028ef1) |
| [CVE-2025-1734](https://github.com/php/php-src/security/advisories/GHSA-pcmh-g36c-qc44) | Invalid HTTP headers | B | B | B | B | B | U | [0548c4c1](https://github.com/php/php-src/commit/0548c4c1756724a89ef8310709419b08aadb2b3b) |
| [CVE-2025-1861](https://github.com/php/php-src/security/advisories/GHSA-52jp-hrpf-2jff) | HTTP redirect truncation | B | B | B | B | B | U | [ac1a054b](https://github.com/php/php-src/commit/ac1a054bb3eb5994a199e8b18cca28cbabf5943e) |
| [CVE-2026-91767](https://github.com/php/php-src/security/advisories/GHSA-xr7j-rvgx-xq5p) | TLS wildcard bounds | B | B | B | B | B | B | [78cc82b3](https://github.com/php/php-src/commit/78cc82b37a340840c6fbff676f513eb4e3d96533) |
| [CVE-2026-91769](https://github.com/php/php-src/security/advisories/GHSA-vvx9-73fr-5jjx) | TLS SAN/CN verification | B | B | B | B | B | B | [0bb308eb](https://github.com/php/php-src/commit/0bb308eb4cf699e707efbcf97ffb70e46a176be7) |
| [CVE-2026-92842](https://github.com/php/php-src/security/advisories/GHSA-88hq-2827-7pg6) | Standard stream filters | B | B | B | B | B | B | [b4e3397e](https://github.com/php/php-src/commit/b4e3397ec8f4f647e6e019dbf79870fc2f14ba78) |
| [CVE-2026-91768](https://github.com/php/php-src/security/advisories/GHSA-62xp-839h-2637) | FastCGI IPv6 client matching | B | B | B | B | B | B | [dcdfcf86](https://github.com/php/php-src/commit/dcdfcf86fcf7f42fa98dd3d000f310ebdad41965) |
| [CVE-2024-8929](https://github.com/php/php-src/security/advisories/GHSA-h35g-vwh6-m678) | mysqlnd packet reads | B | B | B | B | B | U | [2f5aa9f9](https://github.com/php/php-src/commit/2f5aa9f9d150ca56e356f3ca9acf9d530108cb08), [32f905f1](https://github.com/php/php-src/commit/32f905f1d689aaa8eacd6331a18c0dd45972c3c1) |
| [CVE-2025-1218](https://github.com/php/php-src/security/advisories/GHSA-r6x9-5r99-36j7) | mysqlnd packet reads | B | B | B | B | B | B | [114dbb74](https://github.com/php/php-src/commit/114dbb74368e6a6cd6e48ddb4a76dbe8728b84e2) |
| [CVE-2021-21703](https://www.php.net/ChangeLog-7.php#7.3.31) | FPM scoreboard | B | B | U | U | U | U | [cb2021e5](https://github.com/php/php-src/commit/cb2021e5f69da5e2868130a05bb53db0f9f89e4b) |

## EOL mitigations

The first two rows use the published EOL approach: [PHP 7.4](https://github.com/php/php-src/commit/712e60f9edfd03f0960fc4fc3c2c375f42b90aa2), [PHP 8.0](https://github.com/php/php-src/commit/82f8f31b16a035c1b67cc253e79f7cc288f4cc20) and [PHP 8.1](https://github.com/php/php-src/commit/1d7dec178a094e6e75b229ce22754045f8c6070a). PHP 7.1–7.3 use local adaptations of the 7.4 change, not official releases for those branches.

Handled allocation failure exits the process immediately, bypassing ordinary shutdown callbacks and destructors. In FPM, the master replaces the affected worker. Session upload progress is disabled in source. Ordinary sessions and uploads remain available. These changes do not implement the modern deferred out-of-memory recovery model.

The core patches also include related bounds and early-state changes based on the [modern PHP fix](https://github.com/php/php-src/commit/506e87fa7dcdd306975e5b8f8265ee03527aacbf), with native adaptations. PHP 8.1 additionally includes changes in EXIF, mbstring/libmbfl, OPcache and zlib. The libmbfl modification limits initial allocation to the input length; it was applied to this distribution on 2026-10-22. These supplemental changes are not separate CVE claims.

## Backport sources and compatibility

The older-branch ports use published PHP changes and [Freexian's PHP 7.3 maintenance patches](https://deb.freexian.com/extended-lts/pool/main/p/php7.3/php7.3_7.3.31-1~deb10u16.debian.tar.xz), archive SHA-256 `1eedbd1095e7be93d310c2cbfd13acbcf521b2433ef0ddb5e40abd2dffa633bb`. The [ELA-1848-1 advisory](https://www.freexian.com/lts/extended/updates/ela-1848-1-php7.3/) describes an earlier release of that maintenance series.

The series includes necessary published prerequisites: [307/308 request-method preservation](https://github.com/php/php-src/commit/1ede3137c9abe19da1fafef66d8e4038c63516b4), [chunked redirect cleanup](https://github.com/php/php-src/commit/1c157d3fa2e102ccf375ec4c1ddca8770208dae7), [long header handling](https://github.com/php/php-src/commit/51e2015af3fc4757a666736aae7899a43f76ae6d) and [mysqlnd result ownership](https://github.com/php/php-src/commit/f365d0e00ed93b1c33e984ff3b4cc8677cbca193), where absent. Patch headers identify additional test and compatibility follow-ups.

Expected behavior changes include rejection of malformed MySQL packets, invalid HTTP headers, malformed Phar entries and excessive SOAP recursion. Cross-origin HTTP redirects remove credentials. TLS verification no longer falls back to a certificate's common name after a service-identity SAN mismatch. Applications depending on the previous behavior need changes.

mysqli and PDO MySQL are included as interfaces to the patched mysqlnd driver; their production source has no separate patch in this series. Unrelated optional extensions are omitted. System libraries and any extensions added later need their own maintenance.
