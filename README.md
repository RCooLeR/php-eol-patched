# php-eol-patched

Minimal PHP CLI/FPM images with selected published security backports for PHP 7.1–8.1.

**These PHP versions are unsupported. Upgrade your application to a [supported official PHP release](https://www.php.net/supported-versions.php) as soon as possible.** These patches are a temporary migration aid, not continued PHP support or a guarantee of security. The images also depend on old system libraries. No application compatibility or production-safety guarantee is provided.

This is an independent project, not an official PHP distribution. [PATCHES.md](PATCHES.md) lists the included fixes, upstream sources and remaining limits. Two entries are EOL mitigations with different failure behavior from modern PHP.

## Images

| Build target | Local image | PHP |
| --- | --- | --- |
| `php71` | `php-eol-patched:7.1.33` | 7.1.33 |
| `php72` | `php-eol-patched:7.2.34` | 7.2.34 |
| `php73` | `php-eol-patched:7.3.33` | 7.3.33 |
| `php74` | `php-eol-patched:7.4.33` | 7.4.33 |
| `php80` | `php-eol-patched:8.0.30` | 8.0.30 |
| `php81` | `php-eol-patched:8.1.34` | 8.1.34 |

Only Linux amd64 is supported. Builds start from pinned official PHP images, apply the patches once, and compile the selected modules. The runtime starts from `scratch` and contains PHP, its required shared libraries, CA certificates, configuration and license notices.

All versions include the patched OpenSSL, Phar, session, SOAP and mysqlnd components, plus the PHP engine and standard functions. mysqli and PDO MySQL provide access to mysqlnd; PDO, libxml and hash supply required dependencies. PHP's mandatory core modules remain present.

PHP 8.1 additionally includes patched EXIF, mbstring, OPcache and zlib. JSON is built into PHP 8.0/8.1; it is omitted from the PHP 7 images. Other optional extensions, PECL packages, Composer, Node.js, mail tools, shells, package managers and compilers are not in the runtime. Test-only modules and tools remain in the test build stage.

## Build and run

Use Docker with Buildx from the repository root:

```sh
docker buildx bake --load php71
docker run --rm php-eol-patched:7.1.33 php -v
```

Replace `php71` with another target, or omit the target to build all six. `BUILD_JOBS` controls compiler parallelism:

```sh
docker buildx bake --load --set php71.args.BUILD_JOBS=4 php71
```

The default command runs FPM on port 9000 as UID/GID `33:33`. Mount application files at paths matching the web server's `SCRIPT_FILENAME`. Writable cache, log and upload directories must permit that UID. PHP CLI commands must be explicit because the runtime has no shell:

```sh
docker run --rm php-eol-patched:7.1.33 php -m
docker run --rm php-eol-patched:7.1.33 php --ini
```

[compose.example.yaml](compose.example.yaml) shows a single PHP 8.1 service. Set its application bind mount before use. Publish FPM only to a trusted web server; the example binds the host port to loopback. FPM is not an HTTP server.

The image uses PHP's `php.ini-production`, with UTC as the timezone and session upload progress disabled. The default memory limit is `128M` and POST limit is `8M`. Mount additional INI files under `/usr/local/etc/php/conf.d/` or replace `/usr/local/etc/php-fpm.d/www.conf` for application-specific settings. Upload progress is disabled in the patched source and cannot be restored through INI settings.

## Test

The test stage contains the same PHP build plus the compiler, matching source and test-only modules. It uses local fake servers; no database or credentials are needed.

```sh
docker buildx bake --load php71
docker buildx bake --load --set php71.target=test --set php71.tags=php-eol-patched:7.1.33-test php71
python3 tests/run.py --image php-eol-patched:7.1.33 --test-image php-eol-patched:7.1.33-test --version 7.1.33
```

See [tests/README.md](tests/README.md) for the selected regressions and resource limits. Passing them does not establish coverage of every vulnerability or application.

## Source and modified builds

Export the complete patched PHP source and build inputs:

```sh
docker buildx bake --allow fs.write=/tmp/php-eol-source \
  --set php81.target=source --set php81.output=type=local,dest=/tmp/php-eol-source php81
mkdir -p /tmp/php-eol-modified
tar -xJf /tmp/php-eol-source/php-8.1.34-patched.tar.xz -C /tmp/php-eol-modified
```

The export's `build/` directory contains the Dockerfile, scripts, configuration, patches, licenses and this documentation. To rebuild after editing the exported source, including libmbfl:

```sh
cd /tmp/php-eol-source/build
docker buildx bake --load php81 \
  --allow fs.read=/tmp/php-eol-modified \
  --set php81.args.SOURCE_STAGE=supplied-source \
  --set php81.contexts.php-source=/tmp/php-eol-modified \
  --set php81.tags=php-eol-patched:8.1.34-modified
```

This explicit mode builds the supplied source instead of applying the repository's patch series. Its contents and behavior are the responsibility of the person changing it; retest the resulting image. Use the corresponding target and archive name for another PHP version.

Runtime images contain module lists, configure arguments, source and patch checksums, and linked package versions under `/usr/share/php-eol-patched/`. They retain system-library copyright files and full common-license texts. System-library source obligations are separate from the PHP source export.

## License

Original project files use the [MIT license](LICENSE). PHP, upstream patches, tests and libraries retain their own licenses. See [THIRD_PARTY_NOTICES.md](THIRD_PARTY_NOTICES.md) and [LICENSES/](LICENSES/), particularly the LGPL terms for PHP 8.1's libmbfl. Preserve the notices and required corresponding source when redistributing binaries.
