# Application images

The [`debian` branch](https://github.com/RCooLeR/php-eol-patched/tree/debian) contains the extensible images used by these examples. The [`minimal` branch](https://github.com/RCooLeR/php-eol-patched/tree/minimal) contains only no-shell runtimes; use its build-stage recipe when adding extensions. [`main`](https://github.com/RCooLeR/php-eol-patched/tree/main) contains both variants. Check out the matching branch before running its build commands.

Use a `:VERSION-debian` image when your application Dockerfile needs a shell, Debian packages, Composer or extra extensions. Build that image locally first:

```sh
docker buildx bake --load php71-debian
docker run --rm php-eol-patched:7.1.33-debian sh -c 'php -v && php --ini'
```

The Debian variants keep the matching pinned Debian base and official `docker-php-entrypoint`, `docker-php-source`, `docker-php-ext-configure`, `docker-php-ext-install` and `docker-php-ext-enable` scripts. They include `phpize`, `php-config` and PECL. They use this project's patched PHP and [extension profile](EXTENSIONS.md), rather than the official image's module defaults. The minimal tags without `-debian` contain no shell, `apt`, PECL or compiler.

All examples use local tags. The repository's default build still produces the eight minimal images. `docker buildx bake --load debian` builds the eight Debian variants; `docker buildx bake --load all` builds both variants for every release. Only Linux amd64 is supported.

## Extra extensions on PHP 7.1

[examples/php71-app/Dockerfile](examples/php71-app/Dockerfile) is a complete example adding GMP, FTP with TLS support, and POSIX, with a separate start/end section for each extension. It also installs application configuration and uses UID/GID `1000:1000`:

```sh
docker buildx bake --load php71-debian
docker build --pull=false -t php71-app:local examples/php71-app
docker run --rm php71-app:local php --ri gmp
docker run --rm php71-app:local php --ri ftp
docker run --rm php71-app:local php --ri posix
docker run --rm php71-app:local php-fpm -tt
```

The extension part follows the familiar official image pattern:

```dockerfile
FROM php-eol-patched:7.1.33-debian
ARG BUILD_JOBS=2

# Start extension gmp installation
RUN apt-get update \
    && apt-get install -y --no-install-recommends $PHPIZE_DEPS libgmp-dev \
    && docker-php-ext-configure gmp --with-gmp=/usr \
        --with-libdir="lib/$(dpkg-architecture -qDEB_HOST_MULTIARCH)" \
    && docker-php-ext-install -j"${BUILD_JOBS}" gmp \
    && rm -rf /var/lib/apt/lists/*
# End extension gmp installation

# Start extension ftp installation
RUN apt-get update \
    && apt-get install -y --no-install-recommends libssl-dev \
    && docker-php-ext-configure ftp --enable-ftp --with-openssl-dir=/usr \
        --with-libdir="lib/$(dpkg-architecture -qDEB_HOST_MULTIARCH)" \
    && docker-php-ext-install -j"${BUILD_JOBS}" ftp \
    && rm -rf /var/lib/apt/lists/*
# End extension ftp installation

# Start extension posix installation
RUN docker-php-ext-install -j"${BUILD_JOBS}" posix
# End extension posix installation
```

The GMP library directory uses Debian's architecture name, not a PHP extension ABI path. Configure flags vary across PHP versions; these flags are for PHP 7.1. FTP support does not install an FTP server. Use `php -m` before adding an extension: GD, intl, ZIP, bcmath, mbstring, OPcache and many other common modules are already present.

The helpers extract `/usr/src/php.tar.xz`, which contains the patched PHP source. They may remove the extracted `/usr/src/php` directory after installation; the next invocation extracts the same patched archive. Do not replace that archive with an upstream PHP tarball or copy PHP executables from another image. Extra extensions and their dependencies are outside the core patch coverage in [PATCHES.md](PATCHES.md).

The examples retain development packages to keep subsequent extension builds straightforward. For a smaller final image, use a separate build stage and retain the shared libraries and data each module requires. Blindly removing all packages installed for a build can remove runtime libraries too.

### Legacy mcrypt

Add mcrypt only if the existing application requires it. PHP 7.1 still includes its source, but it is deprecated there and removed from PHP 7.2. This PHP 7.1 section can be added before the final `USER` instruction:

```dockerfile
# Start extension mcrypt installation
RUN apt-get update \
    && apt-get install -y --no-install-recommends $PHPIZE_DEPS libmcrypt-dev libltdl-dev \
    && docker-php-ext-configure mcrypt --with-mcrypt=/usr \
    && docker-php-ext-install -j"${BUILD_JOBS}" mcrypt \
    && rm -rf /var/lib/apt/lists/*
# End extension mcrypt installation
```

Keep the existing encrypted-data format readable while migrating to a maintained cryptographic API. Do not treat installing mcrypt as a recommendation for new application code. Later PHP versions need a separately reviewed, version-compatible PECL package instead of this bundled-source recipe. See the [PHP mcrypt manual](https://www.php.net/manual/en/intro.mcrypt.php).

### Sodium on PHP 8.1

[examples/php81-sodium/Dockerfile](examples/php81-sodium/Dockerfile) demonstrates a different PHP release and system library:

```sh
docker buildx bake --load php81-debian
docker build --pull=false -t php81-sodium:local examples/php81-sodium
docker run --rm php81-sodium:local php --ri sodium
```

It installs `libsodium-dev`, configures the bundled extension with `docker-php-ext-configure sodium --with-sodium`, and runs `docker-php-ext-install sodium`.

## Composer and shell tools

Install required system commands with `apt-get` while the build runs as root. For example, `git` and `unzip` can be useful in a dependency-build stage:

```dockerfile
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*
```

Use Composer 2.2 LTS for PHP 7.1. The following example pins Composer 2.2.30 and the SHA-256 published on the [Composer download page](https://getcomposer.org/download/). Update the version and hash together after review:

```dockerfile
ARG COMPOSER_VERSION=2.2.30
ARG COMPOSER_SHA256=8c2b4478b64f8f7cdf1574838fdb0033b29049ca821dad452db7a3dcfcdbffc2
RUN curl --fail --location --proto '=https' --tlsv1.2 \
        "https://getcomposer.org/download/${COMPOSER_VERSION}/composer.phar" \
        -o /usr/local/bin/composer \
    && printf '%s  %s\n' "${COMPOSER_SHA256}" /usr/local/bin/composer | sha256sum --check --strict \
    && chmod 0755 /usr/local/bin/composer
```

Run Composer as the application user with a writable `COMPOSER_HOME`, and keep `composer.lock` in the application repository. A dependency-build stage can run `composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader`, then copy `vendor/` into the final image. Install and verify dependencies under the same PHP release and extension profile as the final runtime; do not use `--ignore-platform-reqs` to hide mismatches. Run `composer check-platform-reqs --no-dev` against the resulting application. Scripts and plugins need the application's usual review because installation can execute them.

Composer, Git and frontend toolchains can stay in build or maintenance stages when FPM does not need them. Use a separate Node build stage for frontend assets. The presence of a shell does not add a mail service or database server; install or configure application-specific services separately.

## Development-only Xdebug

PHP 7.1 needs an older Xdebug release. The following development image pins [Xdebug 2.9.8](https://xdebug.org/download/historical), checks the source archive, and compiles it with the image's `phpize`. Keep this in a separate Dockerfile or development target; do not make it the production default:

```dockerfile
FROM php71-app:local AS development
USER root
ARG BUILD_JOBS=2
ARG XDEBUG_VERSION=2.9.8
ARG XDEBUG_SHA256=f555b6cc58d96c9965af942d22e0f1818b7a477a410c76b1ab0eebe85a762f8a

# Start extension xdebug installation
RUN apt-get update \
    && apt-get install -y --no-install-recommends $PHPIZE_DEPS \
    && curl --fail --location --proto '=https' --tlsv1.2 \
        "https://xdebug.org/files/xdebug-${XDEBUG_VERSION}.tgz" -o /tmp/xdebug.tgz \
    && printf '%s  %s\n' "${XDEBUG_SHA256}" /tmp/xdebug.tgz | sha256sum --check --strict \
    && mkdir -p /tmp/xdebug \
    && tar -xzf /tmp/xdebug.tgz --strip-components=1 -C /tmp/xdebug \
    && cd /tmp/xdebug \
    && phpize \
    && ./configure --enable-xdebug --with-php-config=/usr/local/bin/php-config \
    && make -j"${BUILD_JOBS}" \
    && make install \
    && docker-php-ext-enable xdebug \
    && mkdir -p /usr/share/licenses/xdebug \
    && cp LICENSE /usr/share/licenses/xdebug/LICENSE \
    && rm -rf /tmp/xdebug /tmp/xdebug.tgz /var/lib/apt/lists/*
# End extension xdebug installation

USER 1000:1000
```

Set debugger connection settings in a development-only INI file using the Xdebug 2 setting names. Xdebug 3 settings such as `xdebug.mode` do not apply to 2.9.8. Test with `php --ri xdebug`. Other PHP versions need their own compatible, pinned Xdebug release; do not carry this pin forward indiscriminately.

## Application configuration and ownership

Copy application overrides to `/usr/local/etc/php/conf.d/zz-app.ini`, and replace the FPM pool at `/usr/local/etc/php-fpm.d/www.conf`. The complete example includes both [zz-app.ini](examples/php71-app/zz-app.ini) and [www.conf](examples/php71-app/www.conf). Keep the FPM listener on port 9000 and match the application's path to the web server's `SCRIPT_FILENAME`.

Debian variants start a root master and `www-data` workers, UID/GID `33:33`, by default. Their pool uses `clear_env = no`, making container environment variables available to workers. The minimal variants run entirely as `33:33` and retain `clear_env = yes`. Use only the environment variables the application should receive; FPM configuration can restrict them with `clear_env = yes` and explicit `env[NAME]` entries.

The PHP 7.1 example changes `www-data` to `1000:1000` and finishes with `USER 1000:1000`. Its FPM master also runs without root; pool `user` and `group` settings are ignored when the master is already unprivileged. If you need the usual root-master model, omit the final `USER` and retain the pool's `www-data` settings. Change `APP_UID` and `APP_GID` at build time only to unused IDs in the image:

```sh
docker build --pull=false --build-arg APP_UID=1001 --build-arg APP_GID=1001 \
  -t php71-app:local examples/php71-app
```

Copy application files with the intended owner, for example `COPY --chown=1000:1000 . /var/www/html`, and create writable cache, log, session and upload directories for the worker UID. Do not make the entire application tree writable when only those directories need writes. A bind mount replaces the image's directory and ownership: `chown` during the image build cannot fix host-mounted files. Set host permissions or volume initialization for the actual runtime UID. Windows Docker Desktop and Linux host mounts can behave differently; verify the directories your application writes.

The sample INI sets `memory_limit = 256M`, `upload_max_filesize = 16M` and `post_max_size = 20M`; choose values for the application's workload. Session upload progress remains disabled in the patched PHP source and cannot be restored with an INI override.

### Compose with the Debian variant

[compose.example.yaml](compose.example.yaml) uses the minimal image and drops all Linux capabilities. To use a Debian variant with its root master, save this override as `compose.debian.yaml` beside it:

```yaml
services:
  php:
    build:
      target: debian
    image: php-eol-patched:8.1.34-debian
    cap_add: [SETUID, SETGID, KILL]
```

These capabilities let the master change worker identity and signal its workers. The merged configuration retains `cap_drop: [ALL]`, the read-only filesystem, `/tmp` tmpfs and the other settings in the base file. Alternatively, replace `cap_add` in the override with `user: "33:33"` to run the master and workers as UID/GID 33 with all capabilities dropped. Ensure application writes are directed to writable mounts.

Set the base file's application bind mount before starting the service. Review and run the merged configuration explicitly:

```sh
docker compose -f compose.example.yaml -f compose.debian.yaml config
docker compose -f compose.example.yaml -f compose.debian.yaml up --build -d
```

## Framework and maintenance commands

Laravel and Symfony applications can use the same Debian image for HTTP requests and separate containers for queue workers, console commands or scheduled jobs. Typical writable directories include Laravel's `storage/` and `bootstrap/cache/`, and Symfony's `var/`. Adapt those paths to the application version.

If a CLI worker needs `pcntl`, build it without globally enabling it in FPM. In a derivative image, before the final `USER` instruction:

```dockerfile
# Start extension pcntl installation
RUN docker-php-source extract \
    && cd /usr/src/php/ext/pcntl \
    && phpize \
    && ./configure --enable-pcntl --with-php-config=/usr/local/bin/php-config \
    && make -j2 \
    && make install \
    && docker-php-source delete
# End extension pcntl installation
```

No `docker-php-ext-enable` call or shared INI file is needed. Load it only for the relevant CLI process:

```sh
docker run --rm -v "$PWD:/var/www/html" php71-app:local \
  php -d extension=pcntl.so artisan queue:work
docker run --rm -v "$PWD:/var/www/html" php71-app:local \
  php -d extension=pcntl.so bin/console messenger:consume async
```

These commands assume you added the section and rebuilt `php71-app:local`; command availability depends on the framework version. Process control is intended for CLI use; see the [PHP PCNTL documentation](https://www.php.net/manual/en/intro.pcntl.php).

For WordPress, run WP-CLI and Composer in a maintenance or dependency-build image as the application user. Pin and verify the WP-CLI PHAR just as with Composer. Check its PHP requirement before selecting a release: the current [WP-CLI installation requirements](https://make.wordpress.org/cli/handbook/guides/installing/) require PHP 7.2.24 or later, so the current release is not a PHP 7.1 recipe. WordPress plugins may require extensions beyond the default map; test the actual installation, media handling and database commands.

For SQL Server on PHP 8.0, select a compatible `sqlsrv`/`pdo_sqlsrv` release such as the 5.11 line and a supported Microsoft ODBC driver. Both the PHP compatibility table and the Debian distribution/ODBC combination matter; newer driver lines can drop PHP 8.0. Follow the [Microsoft PHP driver support matrix](https://learn.microsoft.com/en-us/sql/connect/php/microsoft-php-drivers-for-sql-server-support-matrix) and [Linux installation guidance](https://learn.microsoft.com/en-us/sql/connect/php/installation-tutorial-linux-mac), pin the reviewed versions and checksums, and preserve their license notices. There is no tested SQL Server installation recipe in this repository. Validate encrypted database connectivity with your chosen server and driver configuration.

## Advanced: a customized minimal image

Use the Debian variant for normal downstream Dockerfiles. A minimal image requires a build stage that collects the extension's INI, transitive shared libraries, dynamically loaded data and license notices. Copying just a `.so` from another PHP image is insufficient, even if its directory name looks compatible.

The repository has an internal `debian-prepared` stage that includes the assembled minimal filesystem and a helper-compatible patched source archive. Build and tag it locally; it is not a separately published image:

```sh
docker buildx bake --load php71 \
  --set php71.target=debian-prepared \
  --set php71.tags=php-eol-patched:7.1.33-extension-builder
```

For a bundled extension such as GMP, a downstream Dockerfile can add the extension and rerun the assembler:

```dockerfile
FROM php-eol-patched:7.1.33-extension-builder AS application

# Keep helper re-extraction on the same patched source as the PHP binary.
RUN cp /debian-files/usr/src/php.tar.xz /usr/src/php.tar.xz

# Start extension gmp installation
RUN apt-get update \
    && apt-get install -y --no-install-recommends libgmp-dev \
    && docker-php-ext-configure gmp --with-gmp=/usr \
        --with-libdir="lib/$(dpkg-architecture -qDEB_HOST_MULTIARCH)" \
    && docker-php-ext-install -j2 gmp \
    && rm -rf /var/lib/apt/lists/*
# End extension gmp installation

RUN /bin/bash /build/scripts/assemble-runtime.sh 7.1.33

FROM scratch
COPY --from=application /runtime-rootfs/ /
ENV PATH=/usr/local/bin:/usr/local/sbin
USER 33:33
WORKDIR /var/www/html
EXPOSE 9000
STOPSIGNAL SIGQUIT
CMD ["/usr/local/sbin/php-fpm", "--nodaemonize"]
```

The archive in `/debian-files` has the top-level directory expected by `docker-php-source`. The source export's `php-VERSION-patched.tar.xz` has a different layout and cannot directly replace `/usr/src/php.tar.xz`.

The assembler obtains the extension directory from `php-config`, includes loaded modules and INI files, resolves linked libraries, and copies Debian package copyright notices. Modules that load plugins or data dynamically may need additional packaging rules; newly added PECL packages also need their own source archives, pins and notices. Preserve the full project notices and meet the corresponding-source obligations of added libraries. The repository's source export covers its base build, so retain the source and license material required for downstream additions separately. Apply application configuration to `/runtime-rootfs` after assembly, because the assembler installs the repository's defaults.

Build and test against the same repository revision and exact PHP release. Verify loaded modules, FPM configuration, writable paths and application behavior in the final image. A successful extension build alone does not establish that its runtime dependencies or application behavior are correct.
