#!/bin/sh
set -eu

version=${1:?PHP release required}
metadata=/usr/share/php-eol-patched
test "$(php -n -r 'echo PHP_VERSION;')" = "$version"
test -s "$metadata/debian-source.sha256"
test -s "$metadata/debian-source-files.sha256"
test ! -d /usr/src/php

check_archive() {
    (cd /usr/src && sha256sum --check --strict --quiet "$metadata/debian-source.sha256")
}

check_source() {
    test -f /usr/src/php/main/php_version.h
    test -f /usr/src/php/ext/posix/posix.c
    (cd /usr/src/php && sha256sum --check --strict --quiet "$metadata/debian-source-files.sha256")
}

check_archive
docker-php-source extract
check_source
docker-php-source delete
test ! -d /usr/src/php
docker-php-source extract
check_source
docker-php-source delete
test ! -d /usr/src/php
printf '%s\n' 'DEBIAN_PATCHED_SOURCE_REEXTRACTION_OK'

# The helper must extract the packaged source itself, run phpize/configure,
# install the module and enable it, then remove its temporary source tree.
php -r 'exit(extension_loaded("posix") ? 1 : 0);'
docker-php-ext-install -j2 posix
test ! -d /usr/src/php
php -r '
if (!extension_loaded("posix") || posix_geteuid() !== 0 || posix_getegid() !== 0
    || posix_getpid() !== getmypid()) {
    fwrite(STDERR, "Installed POSIX extension failed its runtime control\n");
    exit(1);
}
echo "DEBIAN_NATIVE_EXTENSION_INSTALL_OK\n";
'
php -n -d extension=posix.so -r 'exit(extension_loaded("posix") ? 0 : 1);'

# Extension builds must not replace or mutate the retained patched archive.
check_archive
docker-php-source extract
check_source
docker-php-source delete
test ! -d /usr/src/php
printf '%s\n' 'DEBIAN_PATCHED_SOURCE_AFTER_BUILD_OK'
