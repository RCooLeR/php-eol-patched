# Third-party notices

The root MIT license covers this project's original build scripts, configuration and documentation. It does not replace the licenses of PHP, imported patches, upstream tests or linked libraries. Their copyrights and license terms remain in force.

## PHP and Zend

PHP and Zend code is used under the Modified BSD License, also named PHP License version 4 and Zend Engine License version 3. [PHP's licensing page](https://www.php.net/license/) permits this choice for earlier releases under the later-version clauses of their original licenses. The applicable text is in [LICENSES/PHP-BSD-3-Clause.txt](LICENSES/PHP-BSD-3-Clause.txt).

Original release licenses and copyright notices are retained for all eight versions under `LICENSES/php-VERSION/`. `SOURCE-NOTICES.txt` and, where present, `EXTENSION-SOURCE-NOTICES.txt` preserve license and copyright comments from the enabled components, including embedded notices that have no separate license file. Each version's `POSTGRESQL-SOURCE-NOTICES.txt` separately preserves the original notice comments from `ext/pgsql` and `ext/pdo_pgsql`. `LICENSES/SOURCES.json` records their origins and checksums. Individual files with different licenses retain those terms.

This product includes PHP software, freely available from <https://www.php.net/software/>. This product includes the Zend Engine, freely available at <https://www.zend.com/>. The Zend Engine is freely available at <https://www.zend.com/>.

This project is independent of the PHP Group, Zend Technologies and Freexian. Their names identify the original software and patch sources; they do not imply endorsement.

## Patches and tests

The security changes and regression tests derive from the PHP project and its contributors. [PATCHES.md](PATCHES.md) links the upstream advisories and changes. Native backports and compatibility adjustments do not transfer ownership of the original code to this project.

Some older-branch adaptations use Freexian's published Debian maintenance patches from [php7.3_7.3.31-1~deb10u16.debian.tar.xz](https://deb.freexian.com/extended-lts/pool/main/p/php7.3/php7.3_7.3.31-1~deb10u16.debian.tar.xz). Its `debian/copyright` identifies PHP code as PHP-3.01 and Debian packaging as Expat/MIT, copyright Ondřej Surý. The packaging notice is retained in [LICENSES/Debian-packaging-MIT.txt](LICENSES/Debian-packaging-MIT.txt). Upstream author and commit references remain the attribution for the imported fixes.

PHP 5.6 and 7.0 also use the public maintenance sources identified in [PATCHES.md](PATCHES.md) and their per-version provenance files. PHP 5.6 includes selected changes from [shivammathur/php-src-backports](https://github.com/shivammathur/php-src-backports). Original authors and published commit references remain in the patch headers.

## PECL extensions

Redis, APCu, Imagick, Memcached, igbinary and MessagePack retain their package licenses under `LICENSES/pecl/PACKAGE-VERSION/`. [extensions/pecl.tsv](extensions/pecl.tsv) records the exact release, official source URL, checksum and PHP compatibility requirements. The source-export target includes their original archives and unpacked source. Their license terms are separate from this project's MIT license.

## Other bundled and linked code

libpq 16.15 is built from the PostgreSQL source under the PostgreSQL License. Its original copyright and permission notice is retained in [LICENSES/libpq-16.15/COPYRIGHT](LICENSES/libpq-16.15/COPYRIGHT). Additional BSD and ISC notices for the linked portability routines are in [LICENSES/libpq-16.15/SOURCE-NOTICES.txt](LICENSES/libpq-16.15/SOURCE-NOTICES.txt). [extensions/libpq.tsv](extensions/libpq.tsv) pins the upstream archive and checksum, also recorded in `LICENSES/SOURCES.json`. The source-export target includes the original PostgreSQL archive, its `COPYRIGHT` notice and the scripts used to build the client library.

PHP includes code with additional terms, including timelib (MIT), PCRE/PCRE2 (BSD), FPM (BSD), libarchive-derived Phar routines (BSD), GD, bcmath, fileinfo and mbstring's libmbfl (LGPL 2.1). Where present, the bundled Oniguruma, libzip, Boost context assembly and OPcache disassembler notices are retained. These notices are preserved with the corresponding version under `LICENSES/`.

The PHP 8.1 libmbfl allocation-bound change is a local adaptation of the published PHP fix, applied on 2026-10-22. PHP 5.6 and 7.0 include the published Big5 conversion bounds fix, applied to this distribution on 2026-10-23. Modified library files carry dated notices. The source-export build target supplies the patched source and build inputs so recipients can rebuild PHP or its mbstring extension with a modified library. The LGPL permits modification and reverse engineering for debugging those modifications; this project adds no restriction on either. See the build and source-export instructions in [README.md](README.md).

Linked system libraries keep their own licenses. Runtime images retain their package copyright files. When redistributing an image, preserve these notices and provide the corresponding source and rebuild materials required by any copyleft components. The source export for PHP does not replace the source obligations of the operating-system libraries. Published PHP, PECL and libpq sources use a `<release>-source-<full-commit>-<run-id>-<attempt>` image tag matching the runtime's build suffix; `<release>-source` is a moving alias. `/usr/share/php-eol-patched/runtime-packages.tsv` records the linked Debian runtime packages, not every package in the extensible Debian image. Use its dpkg database for the complete installed-package inventory. Debian package sources must be obtained separately from the matching Debian repositories, archives or snapshots; they are not included in this source-export image.
