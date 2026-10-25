# Extension map

The `minimal` branch enables the extensions below in all eight no-shell PHP CLI/FPM images. The optional installation sections in [Dockerfile](Dockerfile) are enabled by default and have start/end comments. [docker-bake.hcl](docker-bake.hcl) selects the base image and release. The [`debian` branch](https://github.com/RCooLeR/php-eol-patched/tree/debian) offers the same module set for each release with a shell and extension build tools.

A check mark means the extension is included. It does not mean that the extension or its system libraries have complete security backport coverage; see [PATCHES.md](PATCHES.md).

## PHP extensions

| Extension | 5.6.40 | 7.0.33 | 7.1.33 | 7.2.34 | 7.3.33 | 7.4.33 | 8.0.30 | 8.1.34 |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `Core` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `date` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `hash` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `libxml` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `mysqlnd` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `mysqli` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `PDO` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `pdo_mysql` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `pgsql` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `pdo_pgsql` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `openssl` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `pcre` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `Phar` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `Reflection` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `session` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `soap` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `SPL` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `standard` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `bcmath` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `ctype` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `curl` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `dom` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `exif` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `fileinfo` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `filter` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `gd` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `iconv` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `intl` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `json` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `mbstring` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `pdo_sqlite` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `SimpleXML` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `sockets` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `sqlite3` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `tokenizer` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `xml` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `xmlreader` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `xmlwriter` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `Zend OPcache` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `zip` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `zlib` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `ereg` | ✓ | — | — | — | — | — | — | — |
| `apc` compatibility aliases | ✓ | — | — | — | — | — | — | — |

`ereg` is part of PHP 5.6 and was removed in PHP 7. The PHP 5.6 `apc` module supplies APCu 4's compatibility aliases; it is not a separate APC package. JSON is built into PHP 8; earlier versions load it as an extension. GD includes JPEG, PNG and FreeType support. mysqli and PDO MySQL use mysqlnd. SQLite uses the system library.

pgsql and PDO PostgreSQL use libpq 16.15, built from the checksum-pinned upstream source in [extensions/libpq.tsv](extensions/libpq.tsv). The client supports SCRAM authentication and TLS through each base image's OpenSSL library. PostgreSQL server binaries and client command-line tools are not included in the runtime. The source export includes the original PostgreSQL archive; the runtime includes its license and libpq dependency manifest.

## PECL extensions

Versions and download checksums are pinned in [extensions/pecl.tsv](extensions/pecl.tsv).

| Extension | 5.6.40 | 7.0.33 | 7.1.33 | 7.2.34 | 7.3.33 | 7.4.33 | 8.0.30 | 8.1.34 |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `redis` | 4.3.0 | 5.3.7 | 5.3.7 | 6.0.2 | 6.0.2 | 6.3.0 | 6.3.0 | 6.3.0 |
| `apcu` | 4.0.11 | 5.1.28 | 5.1.28 | 5.1.28 | 5.1.28 | 5.1.28 | 5.1.28 | 5.1.28 |
| `imagick` | 3.8.1 | 3.8.1 | 3.8.1 | 3.8.1 | 3.8.1 | 3.8.1 | 3.8.1 | 3.8.1 |
| `memcached` | 2.2.0 | 3.4.0 | 3.4.0 | 3.4.0 | 3.4.0 | 3.4.0 | 3.4.0 | 3.4.0 |
| `igbinary` | 2.0.8 | 3.2.16 | 3.2.16 | 3.2.16 | 3.2.16 | 3.2.16 | 3.2.16 | 3.2.16 |
| `msgpack` | 0.5.7 (beta) | 3.0.1 | 3.0.1 | 3.0.1 | 3.0.1 | 3.0.1 | 3.0.1 | 3.0.1 |

PHP 5.6 uses older compatible PECL releases. MessagePack 0.5.7 is marked beta by its publisher. These versions are included for compatibility and are not a claim of ongoing upstream security support.

Redis and Memcached are client extensions; their servers are not included. The build enables their available igbinary/MessagePack serializers. Redis 4.3.0 does not provide its newer JSON/MessagePack serializer options. APCu is enabled for FPM; PHP's default disables its CLI cache. Imagick uses the distribution's ImageMagick libraries and policy. External format delegates such as Ghostscript are not included.

## Inspect an image

```sh
docker buildx bake --load
docker run --rm php-eol-patched:5.6.40 php -m
docker run --rm php-eol-patched:8.1.34 php --ri redis
```

Replace the tag with any PHP release in the tables. Each image also contains its module list, PECL pins and libpq manifest under `/usr/share/php-eol-patched/`. Tests compare loaded modules and dependency versions with this build profile and exercise extension functions through FPM. PostgreSQL controls connect to a disposable server using SCRAM and verified TLS, exercising both native pgsql and PDO with native and emulated prepared statements.

These images contain PHP and the libraries/data needed by these extensions. Composer, Node.js, database servers, mail tools, shells, package managers and compilers are not included. Additional extensions must be compiled in a build stage; see [CUSTOMIZING.md](CUSTOMIZING.md).

`pcntl`, `posix`, `gmp`, `ftp`, `mcrypt`, `sodium` and Xdebug are not enabled by this default profile. `pcntl` and `posix` are installed in the test stage only. [CUSTOMIZING.md](CUSTOMIZING.md) explains how to add application-specific modules to a Debian variant, including keeping `pcntl` restricted to CLI workers and Xdebug restricted to development.
