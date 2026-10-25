#!/bin/sh
set -eu

version=${1:?PHP release required}
test "$(id -u)" = 0
for executable in sh apt-get php php-fpm phpize php-config pear pecl \
    docker-php-entrypoint docker-php-source docker-php-ext-configure \
    docker-php-ext-install docker-php-ext-enable autoconf gcc g++ make pkg-config; do
    command -v "$executable"
done
test "$(php-config --version)" = "$version"
test "$(php -n -r 'echo PHP_VERSION;')" = "$version"
phpize --version
pecl version
apt-get --version
test -f "$(php-config --include-dir)/main/php.h"
test -f /usr/src/php.tar.xz
test ! -d /usr/src/php
printf '%s\n' 'DEBIAN_TOOLS_OK'
