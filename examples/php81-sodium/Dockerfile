# syntax=docker/dockerfile:1
FROM php-eol-patched:8.1.34-debian

ARG BUILD_JOBS=2

# Start extension sodium installation
RUN apt-get update \
    && apt-get install -y --no-install-recommends $PHPIZE_DEPS libsodium-dev \
    && docker-php-ext-configure sodium --with-sodium \
    && docker-php-ext-install -j"${BUILD_JOBS}" sodium \
    && rm -rf /var/lib/apt/lists/*
# End extension sodium installation
