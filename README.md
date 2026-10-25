# php-eol-patched

PHP CLI/FPM images with selected published security backports for PHP 5.6–8.1.

**These PHP versions are unsupported. Upgrade your application to a [supported official PHP release](https://www.php.net/supported-versions.php) as soon as possible.** These patches are a temporary migration aid, not continued PHP support or a guarantee of security. The images also depend on old system libraries. No application compatibility or production-safety guarantee is provided.

This is an independent project, not an official PHP distribution. [PATCHES.md](PATCHES.md) lists the included fixes, upstream sources and remaining limits. Two entries are EOL mitigations with different failure behavior from modern PHP.

## Images

| Minimal target / local image | Debian target / local image |
| --- | --- |
| `php56` / `php-eol-patched:5.6.40` | `php56-debian` / `php-eol-patched:5.6.40-debian` |
| `php70` / `php-eol-patched:7.0.33` | `php70-debian` / `php-eol-patched:7.0.33-debian` |
| `php71` / `php-eol-patched:7.1.33` | `php71-debian` / `php-eol-patched:7.1.33-debian` |
| `php72` / `php-eol-patched:7.2.34` | `php72-debian` / `php-eol-patched:7.2.34-debian` |
| `php73` / `php-eol-patched:7.3.33` | `php73-debian` / `php-eol-patched:7.3.33-debian` |
| `php74` / `php-eol-patched:7.4.33` | `php74-debian` / `php-eol-patched:7.4.33-debian` |
| `php80` / `php-eol-patched:8.0.30` | `php80-debian` / `php-eol-patched:8.0.30-debian` |
| `php81` / `php-eol-patched:8.1.34` | `php81-debian` / `php-eol-patched:8.1.34-debian` |

Only Linux amd64 is supported. Builds start from pinned official PHP images, apply the patches once, and compile the selected modules. The tags above are built locally; these instructions do not pull a published project image or publish images.

The original `:VERSION` tags are minimal runtimes built from `scratch`. They contain PHP CLI/FPM, required shared libraries, CA certificates, configuration and license notices. The additional `:VERSION-debian` tags retain the matching Debian base, a shell, `apt`, `phpize`, `php-config`, PECL and the official image's `docker-php-*` helpers and entrypoint. Use them for application Dockerfiles that install packages or extensions. Both variants use the same patched PHP build and extension set; the Debian variant does not reproduce the official image's module defaults.

All targets use the shared Dockerfile. They include OpenSSL, Phar, session, SOAP and mysqlnd, plus mysqli and PDO MySQL. PostgreSQL is available through pgsql and PDO PostgreSQL, linked to source-built libpq 16.15. Application extensions such as GD, intl, curl, ZIP, bcmath, mbstring, XML, OPcache and the pinned PECL packages are enabled in every image. [EXTENSIONS.md](EXTENSIONS.md) gives the complete per-version map.

The optional extension installation sections are clearly marked after the core build. They are enabled by default. Removing a shared extension's section may require adjusting extensions that depend on it. Extensions already built into the patched core remain enabled, including mbstring, EXIF and zlib on PHP 8.1. Optional extensions and linked system libraries need their own security maintenance; the patch table describes only the included PHP fixes.

Composer, Node.js, mail tools and database servers are not included. The minimal runtime also excludes shells, package managers and compilers. Test-only modules and tools remain in the test stage. See [CUSTOMIZING.md](CUSTOMIZING.md) for extension installation, Composer, application configuration and UID examples.

## Build and run

Use Docker with Buildx from the repository root:

```sh
docker buildx bake --load php71
docker run --rm php-eol-patched:7.1.33 php -v
```

Replace `php71` with another target, or omit the target to build all eight. `BUILD_JOBS` controls compiler parallelism:

```sh
docker buildx bake --load --set php71.args.BUILD_JOBS=4 php71
```

Build an extensible Debian image or either group explicitly:

```sh
docker buildx bake --load php71-debian
docker buildx bake --load debian  # All eight Debian variants
docker buildx bake --load all  # All sixteen images
docker run --rm php-eol-patched:7.1.33-debian sh -c 'php -v && phpize --version'
```

The default group remains the eight minimal images. The minimal image runs FPM on port 9000 as UID/GID `33:33`. The Debian variant starts a root master with `www-data` workers (`33:33`) and `clear_env = no`, as expected by application Dockerfiles that pass container environment variables to PHP. The minimal variant keeps `clear_env = yes`. Mount application files at paths matching the web server's `SCRIPT_FILENAME`. Writable cache, log and upload directories must permit the worker UID. PHP CLI commands must be explicit in the minimal image because it has no shell:

```sh
docker run --rm php-eol-patched:7.1.33 php -m
docker run --rm php-eol-patched:7.1.33 php --ini
```

[compose.example.yaml](compose.example.yaml) shows a single PHP 8.1 service. Set its application bind mount before use. Publish FPM only to a trusted web server; the example binds the host port to loopback. FPM is not an HTTP server.

The image uses PHP's `php.ini-production`, with UTC as the timezone and session upload progress disabled. The default memory limit is `128M` and POST limit is `8M`. Mount additional INI files under `/usr/local/etc/php/conf.d/` or replace `/usr/local/etc/php-fpm.d/www.conf` for application-specific settings. Upload progress is disabled in the patched source and cannot be restored through INI settings.

## Test

The test stage contains the same PHP build plus the compiler, matching source and test-only modules. Tests use local fake servers and a disposable PostgreSQL server on an isolated Docker network. Database credentials are generated for each run; no existing database is used.

```sh
docker buildx bake --load php71
docker buildx bake --load --set php71.target=test --set php71.tags=php-eol-patched:7.1.33-test php71
docker pull "$(python3 -B -c "import sys; sys.path.insert(0, 'tests'); import postgresql; print(postgresql.DEFAULT_IMAGE)")"
python3 tests/run.py --image php-eol-patched:7.1.33 --test-image php-eol-patched:7.1.33-test --version 7.1.33
```

See [tests/README.md](tests/README.md) for the selected regressions and resource limits. Passing them does not establish coverage of every vulnerability or application.

## Source and modified builds

Export the complete patched PHP source, PECL and libpq source archives, and build inputs:

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

Original project files use the [MIT license](LICENSE). PHP, upstream patches, tests and libraries retain their own licenses. See [THIRD_PARTY_NOTICES.md](THIRD_PARTY_NOTICES.md) and [LICENSES/](LICENSES/), including the LGPL terms for libmbfl. Preserve the notices and required corresponding source when redistributing binaries.
