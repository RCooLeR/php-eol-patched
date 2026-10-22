# Third-party notices

The root MIT license covers this project's original build scripts, configuration and documentation. It does not replace the licenses of PHP, imported patches, upstream tests or linked libraries. Their copyrights and license terms remain in force.

## PHP and Zend

PHP and Zend code is used under the Modified BSD License, also named PHP License version 4 and Zend Engine License version 3. [PHP's licensing page](https://www.php.net/license/) permits this choice for earlier releases under the later-version clauses of their original licenses. The applicable text is in [LICENSES/PHP-BSD-3-Clause.txt](LICENSES/PHP-BSD-3-Clause.txt).

Original release licenses and copyright notices are also retained under `LICENSES/php-VERSION/`. `SOURCE-NOTICES.txt` preserves license and copyright comments from the selected runtime components, including embedded notices that have no separate license file. `LICENSES/SOURCES.json` records their origins and checksums. Individual files with different licenses retain those terms.

This product includes PHP software, freely available from <https://www.php.net/software/>. This product includes the Zend Engine, freely available at <https://www.zend.com/>. The Zend Engine is freely available at <https://www.zend.com/>.

This project is independent of the PHP Group, Zend Technologies and Freexian. Their names identify the original software and patch sources; they do not imply endorsement.

## Patches and tests

The security changes and regression tests derive from the PHP project and its contributors. [PATCHES.md](PATCHES.md) links the upstream advisories and changes. Native backports and compatibility adjustments do not transfer ownership of the original code to this project.

Some older-branch adaptations use Freexian's published Debian maintenance patches from [php7.3_7.3.31-1~deb10u16.debian.tar.xz](https://deb.freexian.com/extended-lts/pool/main/p/php7.3/php7.3_7.3.31-1~deb10u16.debian.tar.xz). Its `debian/copyright` identifies PHP code as PHP-3.01 and Debian packaging as Expat/MIT, copyright Ondřej Surý. The packaging notice is retained in [LICENSES/Debian-packaging-MIT.txt](LICENSES/Debian-packaging-MIT.txt). Upstream author and commit references remain the attribution for the imported fixes.

## Other bundled and linked code

PHP includes code with additional terms, including timelib (MIT), PCRE/PCRE2 (BSD), FPM (BSD), and libarchive-derived Phar routines (BSD). The PHP 8.1 build also includes libmbfl (LGPL 2.1), Boost context assembly and OPcache's bundled disassembler notices. These notices are preserved with the corresponding version under `LICENSES/`.

The PHP 8.1 libmbfl allocation-bound change is a local adaptation of the published PHP fix, applied on 2026-10-22. libmbfl is linked into PHP. The source-export build target supplies the patched source and build inputs so recipients can rebuild PHP with a modified library. The LGPL permits modification and reverse engineering for debugging those modifications; this project adds no restriction on either. See the build and source-export instructions in [README.md](README.md).

Linked system libraries keep their own licenses. Runtime images retain their package copyright files. When redistributing an image, preserve these notices and provide the corresponding source and rebuild materials required by any copyleft components. The source export for PHP does not replace the source obligations of the operating-system libraries.
