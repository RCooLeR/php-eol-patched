# php-eol-patched

PHP CLI/FPM images with selected published security backports for PHP 5.6–8.1.

**These PHP versions are unsupported. Upgrade your application to a [supported official PHP release](https://www.php.net/supported-versions.php) as soon as possible.** These patches are a temporary migration aid, not continued PHP support or a guarantee of security. The images also depend on old system libraries. No application compatibility or production-safety guarantee is provided.

This is an independent project, not an official PHP distribution. [PATCHES.md](PATCHES.md) lists the included fixes, upstream sources and remaining limits. Two entries are EOL mitigations with different failure behavior from modern PHP.

## Images

This is the `debian` branch. Its eight image recipes include Debian, a shell, apt and extension build tools. Choose a different branch if needed:

| Branch | Images built by default | Available image targets |
| --- | --- | --- |
| [`main`](https://github.com/RCooLeR/php-eol-patched/tree/main) | Eight minimal images | Minimal and Debian variants for all eight releases |
| [`debian`](https://github.com/RCooLeR/php-eol-patched/tree/debian) | Eight Debian images | `php56-debian` through `php81-debian` |
| [`minimal`](https://github.com/RCooLeR/php-eol-patched/tree/minimal) | Eight no-shell images | `php56` through `php81` |

Both variants run PHP CLI and FPM. The Debian images add a shell, package manager and extension build tools. The minimal images contain the PHP runtime and its required libraries. Image names stay the same across branches; a branch selects the available recipes, not a separate patch level.

For example, clone only the recipes for Debian images:

```sh
git clone --branch debian --single-branch https://github.com/RCooLeR/php-eol-patched.git
cd php-eol-patched
docker buildx bake --load php71-debian
```

Use `--branch minimal` and target `php71` for the no-shell image. Use `main` when working on shared PHP patches or comparing both variants. Develop shared fixes on `main`, then port them to both variant branches while retaining their build defaults, CI and documentation.

| Target | Local image |
| --- | --- |
| `php56-debian` | `php-eol-patched:5.6.40-debian` |
| `php70-debian` | `php-eol-patched:7.0.33-debian` |
| `php71-debian` | `php-eol-patched:7.1.33-debian` |
| `php72-debian` | `php-eol-patched:7.2.34-debian` |
| `php73-debian` | `php-eol-patched:7.3.33-debian` |
| `php74-debian` | `php-eol-patched:7.4.33-debian` |
| `php80-debian` | `php-eol-patched:8.0.30-debian` |
| `php81-debian` | `php-eol-patched:8.1.34-debian` |

Only Linux amd64 is supported. Builds start from pinned official PHP images, apply the patches once, and compile the selected modules. After a release's tests pass on a push to `main`, CI publishes its images to `ghcr.io/rcooler/php-eol-patched`. Release tags match the local tags above. Each build also gets `<tag>-<full-commit>-<run-id>-<attempt>` tags, so reruns have separate references. Tags can be reassigned; pin a digest for an exact image:

```sh
docker pull ghcr.io/rcooler/php-eol-patched:7.1.33-debian
docker image inspect --format '{{index .RepoDigests 0}}' ghcr.io/rcooler/php-eol-patched:7.1.33-debian
```

The matching source export is published first as `<release>-source-<full-commit>-<run-id>-<attempt>`. Use the same build suffix as the runtime when obtaining its source. `<release>-source` follows the current release. Only a run for the current `main` commit updates release tags; an older rerun publishes only its run-specific tags. Versions publish independently after their own checks, so a failed version does not prevent another version from publishing.

For maintainers: [GHCR creates new packages as private](https://docs.github.com/en/packages/working-with-a-github-packages-registry/working-with-the-container-registry#pushing-container-images), even for a public repository. After the first publication, set the package visibility to **Public** in its GitHub package settings and verify an anonymous pull. Until then, pulls require package access. Pull requests, other branches and manual test runs do not publish images.

The `:VERSION-debian` tags retain the matching Debian base, a shell, `apt`, `phpize`, `php-config`, PECL and the official image's `docker-php-*` helpers and entrypoint. Use them for application Dockerfiles that install packages or extensions. For the no-shell `:VERSION` tags, use the [minimal branch](https://github.com/RCooLeR/php-eol-patched/tree/minimal). Both variants use the same patched PHP build and extension set for each release; this project does not reproduce the official image's module defaults.

All targets use the shared Dockerfile. They include OpenSSL, Phar, session, SOAP and mysqlnd, plus mysqli and PDO MySQL. PostgreSQL is available through pgsql and PDO PostgreSQL, linked to source-built libpq 16.15. Application extensions such as GD, intl, curl, ZIP, bcmath, mbstring, XML, OPcache and the pinned PECL packages are enabled in every image. [EXTENSIONS.md](EXTENSIONS.md) gives the complete per-version map.

The optional extension installation sections are clearly marked after the core build. They are enabled by default. Removing a shared extension's section may require adjusting extensions that depend on it. Extensions already built into the patched core remain enabled, including mbstring, EXIF and zlib on PHP 8.1. Optional extensions and linked system libraries need their own security maintenance; the patch table describes only the included PHP fixes.

Composer, Node.js, mail tools and database servers are not included. Test-only modules and tools remain in the test stage. See [CUSTOMIZING.md](CUSTOMIZING.md) for extension installation, Composer, application configuration and UID examples.

## Build and run

Use Docker with Buildx from the repository root:

```sh
docker buildx bake --load php71-debian
docker run --rm php-eol-patched:7.1.33-debian php -v
docker run --rm php-eol-patched:7.1.33-debian sh -c 'php -v && phpize --version'
```

Replace `php71-debian` with another target. The `default`, `debian` and `all` groups each build the eight Debian images on this branch. A plain `docker build` produces the PHP 8.1 Debian image. `BUILD_JOBS` controls compiler parallelism:

```sh
docker buildx bake --load --set php71-debian.args.BUILD_JOBS=4 php71-debian
docker buildx bake --load  # All eight Debian images
```

The Debian image starts a root FPM master with `www-data` workers (`33:33`) on port 9000. Its pool uses `clear_env = no`, making container environment variables available to PHP. Mount application files at paths matching the web server's `SCRIPT_FILENAME`. Writable cache, log and upload directories must permit the worker UID. See [CUSTOMIZING.md](CUSTOMIZING.md) for an unprivileged UID 1000 example.

[compose.example.yaml](compose.example.yaml) shows a single PHP 8.1 service. Set its application bind mount before use. Publish FPM only to a trusted web server; the example binds the host port to loopback. FPM is not an HTTP server.

The image uses PHP's `php.ini-production`, with UTC as the timezone and session upload progress disabled. The default memory limit is `128M` and POST limit is `8M`. Mount additional INI files under `/usr/local/etc/php/conf.d/` or replace `/usr/local/etc/php-fpm.d/www.conf` for application-specific settings. Upload progress is disabled in the patched source and cannot be restored through INI settings.

## Test

The test stage contains the same PHP build plus the compiler, matching source and test-only modules. Tests use local fake servers and a disposable PostgreSQL server on an isolated Docker network. Database credentials are generated for each run; no existing database is used.

```sh
docker buildx bake --load php71-debian
docker buildx bake --load --set php71-debian.target=test --set php71-debian.tags=php-eol-patched:7.1.33-test php71-debian
docker pull "$(python3 -B -c "import sys; sys.path.insert(0, 'tests'); import postgresql; print(postgresql.DEFAULT_IMAGE)")"
python3 tests/run.py --variant debian --image php-eol-patched:7.1.33-debian --test-image php-eol-patched:7.1.33-test --version 7.1.33
```

See [tests/README.md](tests/README.md) for the selected regressions and resource limits. Passing them does not establish coverage of every vulnerability or application.

## Source and modified builds

Export the complete patched PHP source, PECL and libpq source archives, and build inputs:

```sh
docker buildx bake --allow fs.write=/tmp/php-eol-source \
  --set php81-debian.target=source --set php81-debian.output=type=local,dest=/tmp/php-eol-source php81-debian
mkdir -p /tmp/php-eol-modified
tar -xJf /tmp/php-eol-source/php-8.1.34-patched.tar.xz -C /tmp/php-eol-modified
```

The export's `build/` directory contains the Dockerfile, scripts, configuration, patches, licenses and this documentation. To rebuild after editing the exported source, including libmbfl:

```sh
cd /tmp/php-eol-source/build
docker buildx bake --load php81-debian \
  --allow fs.read=/tmp/php-eol-modified \
  --set php81-debian.args.SOURCE_STAGE=supplied-source \
  --set php81-debian.contexts.php-source=/tmp/php-eol-modified \
  --set php81-debian.tags=php-eol-patched:8.1.34-debian-modified
```

This explicit mode builds the supplied source instead of applying the repository's patch series. Its contents and behavior are the responsibility of the person changing it; retest the resulting image. Use the corresponding target and archive name for another PHP version.

Runtime images contain module lists, configure arguments, source and patch checksums, and linked package versions under `/usr/share/php-eol-patched/`. They retain system-library copyright files and full common-license texts. System-library source obligations are separate from the PHP source export.

## License

Original project files use the [MIT license](LICENSE). PHP, upstream patches, tests and libraries retain their own licenses. See [THIRD_PARTY_NOTICES.md](THIRD_PARTY_NOTICES.md) and [LICENSES/](LICENSES/), including the LGPL terms for libmbfl. Preserve the notices and required corresponding source when redistributing binaries.
