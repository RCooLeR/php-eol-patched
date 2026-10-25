# Application images on the minimal branch

These images contain PHP CLI/FPM and their runtime dependencies, with no shell, apt, PECL or compiler. A downstream Dockerfile cannot run shell commands or compile extensions inside the final image. For conventional application Dockerfiles, Composer, development tools and Debian package installation, use the [`debian` branch's customization guide](https://github.com/RCooLeR/php-eol-patched/blob/debian/CUSTOMIZING.md). [`main`](https://github.com/RCooLeR/php-eol-patched/tree/main) contains both variants.

## Application configuration and ownership

Copy or mount INI overrides under `/usr/local/etc/php/conf.d/`, and replace the pool at `/usr/local/etc/php-fpm.d/www.conf` when needed. Match the application's path to the web server's `SCRIPT_FILENAME` and keep the FPM listener on port 9000.

The master and workers run as UID/GID `33:33`. The pool uses `clear_env = yes`; explicitly allow required environment variables with `env[NAME]` pool entries. PHP defaults are `memory_limit = 128M` and `post_max_size = 8M`. Session upload progress is disabled in the patched source and cannot be restored through INI settings.

A simple application image needs only file copies and runtime metadata:

```dockerfile
FROM php-eol-patched:7.1.33
COPY --chown=33:33 app/ /var/www/html/
COPY conf/zz-app.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY conf/www.conf /usr/local/etc/php-fpm.d/www.conf
WORKDIR /var/www/html
USER 33:33
CMD ["/usr/local/sbin/php-fpm", "--nodaemonize"]
```

Prepare writable cache, log, session and upload directories for the worker UID. For example, Laravel commonly writes to `storage/` and `bootstrap/cache/`; Symfony commonly writes to `var/`. Adapt paths to the application version. Use build-stage filesystem commands or volume initialization when creating directories; the final image has no `mkdir` or `chown` executable.

If the application needs UID `1000:1000`, change `COPY --chown` and `USER` to those numeric IDs, or set `user: "1000:1000"` in Compose. Align writable volume permissions with that UID. A bind mount replaces image ownership, so build-time ownership cannot repair host-mounted files. Keep application code read-only where only cache or upload directories need writes.

[compose.example.yaml](compose.example.yaml) uses a read-only root filesystem, drops all Linux capabilities and provides a writable `/tmp`. Set its application bind mount before starting it. CLI commands must be explicit, for example:

```sh
docker run --rm -v "$PWD:/var/www/html" php-eol-patched:7.1.33 php bin/console about
```

The command and required extensions depend on the application's version. Use a separate build or maintenance image for Composer and other tools; install dependencies under the same PHP release and extension profile, then copy the resulting application into the runtime.

## Add an extension in a build stage

A customized minimal image requires a build stage that collects the extension's INI, transitive shared libraries, dynamically loaded data and license notices. Copying just a `.so` from another PHP image is insufficient, even if its directory name looks compatible. The following PHP 7.1 GMP recipe keeps the final image shell-free.

The internal `debian-prepared` build stage is retained on this branch for customization; it does not create a Debian runtime target. It includes the assembled minimal filesystem and a helper-compatible patched source archive. Build and tag it locally; it is not a separately published image:

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
