# syntax=docker/dockerfile:1
FROM php-eol-patched:7.1.33-debian

ARG BUILD_JOBS=2
ARG APP_UID=1000
ARG APP_GID=1000

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

COPY www.conf /usr/local/etc/php-fpm.d/www.conf
COPY zz-app.ini /usr/local/etc/php/conf.d/zz-app.ini
RUN groupmod -g "${APP_GID}" www-data \
    && usermod -u "${APP_UID}" -g "${APP_GID}" www-data \
    && mkdir -p /var/www/html/var/cache /var/www/html/var/log \
    && chown -R "${APP_UID}:${APP_GID}" /var/www/html

WORKDIR /var/www/html
USER ${APP_UID}:${APP_GID}
CMD ["php-fpm"]
