#!/usr/bin/env bash
set -euo pipefail

version=${1:?PHP release required}
cd /usr/src/php
test "$(sed -n 's/^#define PHP_VERSION "\([^"]*\)"/\1/p' main/php_version.h)" = "$version"
args=(
  --prefix=/usr/local
  --with-config-file-path=/usr/local/etc/php
  --with-config-file-scan-dir=/usr/local/etc/php/conf.d
  --disable-all --without-pear --disable-cgi --disable-phpdbg
  --enable-cli --enable-fpm --with-fpm-user=www-data --with-fpm-group=www-data
  --enable-session --enable-phar --enable-soap --with-openssl
  --enable-mysqlnd --with-mysqli=mysqlnd --enable-pdo --with-pdo-mysql=mysqlnd
)
case "$version" in
  5.6.40|7.0.33|7.1.33|7.2.34|7.3.33) args+=(--enable-libxml --enable-hash --disable-opcache) ;;
  7.4.33|8.0.30) args+=(--with-libxml --disable-opcache) ;;
  8.1.34) args+=(--with-libxml --enable-exif --enable-mbstring --enable-opcache --with-zlib) ;;
  *) printf 'Unsupported PHP release: %s\n' "$version" >&2; exit 2 ;;
esac
export CFLAGS="${PHP_CFLAGS:-}" CPPFLAGS="${PHP_CPPFLAGS:-}" LDFLAGS="${PHP_LDFLAGS:-}"
if [[ "$version" == 5.6.40 ]]; then
  ./buildconf --force
fi
./configure "${args[@]}"
make -j"${BUILD_JOBS:-2}"
# The builder's original extension modules are not part of this build.
rm -rf /usr/local/lib/php/extensions
make install
rm -f /usr/local/etc/php/conf.d/*.ini
cp php.ini-production /usr/local/etc/php/php.ini
test "$(php -n -r 'echo PHP_VERSION;')" = "$version"
php -m
