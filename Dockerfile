# syntax=docker/dockerfile:1
# check=error=true
ARG BASE_IMAGE=php:8.1.34-fpm-bookworm@sha256:e3893eeb8f6fb719b6efa7e011e76a65cb1322054226250cedac1138b406aba4
ARG SOURCE_STAGE=patched-source
FROM php:8.1.34-fpm-bookworm@sha256:e3893eeb8f6fb719b6efa7e011e76a65cb1322054226250cedac1138b406aba4 AS archive-keyring

FROM ${BASE_IMAGE} AS dependencies
ARG PHP_RELEASE=8.1.34
COPY --from=archive-keyring /usr/share/keyrings/debian-archive-keyring.gpg /etc/apt/trusted.gpg.d/debian-archive.gpg
COPY --from=archive-keyring /usr/share/keyrings/debian-archive-removed-keys.gpg /etc/apt/trusted.gpg.d/debian-archive-removed.gpg
COPY scripts/install-build-deps.sh /build/scripts/install-build-deps.sh
RUN test "$(dpkg --print-architecture)" = amd64 \
    && /bin/bash /build/scripts/install-build-deps.sh "${PHP_RELEASE}"

FROM dependencies AS patched-source
COPY scripts/apply-patches.sh /build/scripts/apply-patches.sh
COPY patches/ /build/patches/
RUN /bin/bash /build/scripts/apply-patches.sh "${PHP_RELEASE}"

# An explicit named build context permits rebuilding modified LGPL source.
FROM dependencies AS supplied-source
COPY --from=php-source / /usr/src/php/
RUN mkdir -p /source \
    && tar -cJf "/source/php-${PHP_RELEASE}-patched.tar.xz" -C /usr/src/php . \
    && cd /source && sha256sum "php-${PHP_RELEASE}-patched.tar.xz" > SHA256SUMS

FROM ${SOURCE_STAGE} AS selected-source

FROM selected-source AS build
ARG BUILD_JOBS=2
COPY scripts/build-php.sh /build/scripts/build-php.sh
RUN /bin/bash /build/scripts/build-php.sh "${PHP_RELEASE}"

# Optional installation sections are enabled by default. Built-in modules remain enabled.
# These are the last installation steps; packaging stages follow.
# Build serializer extensions before Redis and Memcached.
FROM build AS application-extensions
COPY scripts/build-extension.sh /build/scripts/build-extension.sh
COPY scripts/build-libpq.sh /build/scripts/build-libpq.sh
COPY extensions/ /build/extensions/
RUN /bin/bash /build/scripts/build-libpq.sh

# Start extension json installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled json
# End extension json installation

# Start extension gd installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled gd
# End extension gd installation

# Start extension intl installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled intl
# End extension intl installation

# Start extension curl installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled curl
# End extension curl installation

# Start extension zip installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled zip
# End extension zip installation

# Start extension bcmath installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled bcmath
# End extension bcmath installation

# Start extension mbstring installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled mbstring
# End extension mbstring installation

# Start extension xml installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled xml
# End extension xml installation

# Start extension dom installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled dom
# End extension dom installation

# Start extension simplexml installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled simplexml
# End extension simplexml installation

# Start extension xmlreader installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled xmlreader
# End extension xmlreader installation

# Start extension xmlwriter installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled xmlwriter
# End extension xmlwriter installation

# Start extension ctype installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled ctype
# End extension ctype installation

# Start extension fileinfo installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled fileinfo
# End extension fileinfo installation

# Start extension filter installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled filter
# End extension filter installation

# Start extension iconv installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled iconv
# End extension iconv installation

# Start extension tokenizer installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled tokenizer
# End extension tokenizer installation

# Start extension opcache installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled opcache
# End extension opcache installation

# Start extension exif installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled exif
# End extension exif installation

# Start extension sockets installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled sockets
# End extension sockets installation

# Start extension sqlite3 installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled sqlite3
# End extension sqlite3 installation

# Start extension pdo_sqlite installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled pdo_sqlite
# End extension pdo_sqlite installation

# Start extension pgsql installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled pgsql
# End extension pgsql installation

# Start extension pdo_pgsql installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled pdo_pgsql
# End extension pdo_pgsql installation

# Start extension zlib installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" bundled zlib
# End extension zlib installation

# Start extension igbinary installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" pecl igbinary
# End extension igbinary installation

# Start extension msgpack installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" pecl msgpack
# End extension msgpack installation

# Start extension redis installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" pecl redis
# End extension redis installation

# Start extension apcu installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" pecl apcu
# End extension apcu installation

# Start extension imagick installation
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" pecl imagick
# End extension imagick installation

# Start extension memcached installation
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends libsasl2-modules \
    && rm -rf /var/lib/apt/lists/*
RUN /bin/bash /build/scripts/build-extension.sh "${PHP_RELEASE}" pecl memcached
# End extension memcached installation

FROM application-extensions AS assembled
COPY scripts/assemble-runtime.sh /build/scripts/assemble-runtime.sh
COPY config/ /build/config/
COPY LICENSES/ /build/LICENSES/
COPY LICENSE THIRD_PARTY_NOTICES.md README.md PATCHES.md EXTENSIONS.md /build/
COPY patches/SHA256SUMS /build/patches/SHA256SUMS
RUN /bin/bash /build/scripts/assemble-runtime.sh "${PHP_RELEASE}"

FROM assembled AS test
COPY tests/ /tests/
COPY scripts/build-test-modules.sh /build/scripts/build-test-modules.sh
RUN /bin/bash /build/scripts/build-test-modules.sh
USER 33:33
WORKDIR /tmp
ENTRYPOINT []
CMD ["/bin/bash", "/tests/test-target.sh"]

FROM assembled AS debian-prepared
COPY scripts/prepare-debian.sh /build/scripts/prepare-debian.sh
RUN /bin/bash /build/scripts/prepare-debian.sh "${PHP_RELEASE}"

# Debian variant for applications that need a shell, apt and extension helpers.
# Install matching runtime packages so dpkg records the libraries actually used.
FROM ${BASE_IMAGE} AS debian
ARG PHP_RELEASE=8.1.34
COPY --from=dependencies /etc/apt/ /etc/apt/
COPY --from=assembled /runtime-rootfs/usr/share/php-eol-patched/runtime-packages.tsv /tmp/runtime-packages.tsv
RUN apt-get update \
    && awk -F '\t' '$1 !~ /-dev(:[^:]+)?$/ { print $1 "=" $2 }' /tmp/runtime-packages.tsv \
        | xargs apt-get install -y --no-install-recommends \
    && rm -rf /var/lib/apt/lists/* /usr/local /usr/src/php \
    && rm -f /tmp/runtime-packages.tsv
COPY --from=assembled /usr/local/ /usr/local/
COPY --from=debian-prepared /debian-files/ /
COPY config/www-debian.conf /usr/local/etc/php-fpm.d/www.conf
RUN ldconfig \
    && test "$(php -r 'echo PHP_VERSION;')" = "${PHP_RELEASE}" \
    && mkdir -p /var/www/html \
    && chown www-data:www-data /var/www/html \
    && chmod 1777 /var/www/html
# Upstream download metadata does not describe our patched source archive.
ENV PHP_URL="" PHP_ASC_URL="" PHP_SHA256=""
LABEL org.opencontainers.image.title="php-eol-patched Debian" \
      org.opencontainers.image.version="${PHP_RELEASE}" \
      org.opencontainers.image.source="https://github.com/RCooLeR/php-eol-patched" \
      org.opencontainers.image.description="Debian PHP-FPM with selected security backports and extension build tools. Upgrade to supported PHP."
USER 0:0
WORKDIR /var/www/html
EXPOSE 9000
STOPSIGNAL SIGQUIT
ENTRYPOINT ["docker-php-entrypoint"]
CMD ["php-fpm"]

FROM scratch AS source
COPY --from=application-extensions /source/ /
COPY Dockerfile docker-bake.hcl /build/
COPY .dockerignore /build/
COPY scripts/ /build/scripts/
COPY config/ /build/config/
COPY patches/ /build/patches/
COPY extensions/ /build/extensions/
COPY LICENSES/ /build/LICENSES/
COPY tests/ /build/tests/
COPY examples/ /build/examples/
COPY LICENSE THIRD_PARTY_NOTICES.md README.md PATCHES.md EXTENSIONS.md CUSTOMIZING.md SECURITY.md compose.example.yaml /build/

FROM scratch AS runtime
ARG PHP_RELEASE=8.1.34
LABEL org.opencontainers.image.title="php-eol-patched" \
      org.opencontainers.image.version="${PHP_RELEASE}" \
      org.opencontainers.image.source="https://github.com/RCooLeR/php-eol-patched" \
      org.opencontainers.image.description="Selected PHP security backports for temporary migration use. Upgrade to supported PHP."
COPY --from=assembled /runtime-rootfs/ /
ENV PATH=/usr/local/bin:/usr/local/sbin
USER 33:33
WORKDIR /var/www/html
EXPOSE 9000
STOPSIGNAL SIGQUIT
CMD ["/usr/local/sbin/php-fpm", "--nodaemonize"]

# A plain docker build produces the runtime image.
FROM runtime AS final
