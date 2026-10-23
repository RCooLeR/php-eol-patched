#!/usr/bin/env bash
set -euo pipefail

mkdir -p /test-support
for module in pcntl posix dom json; do
  if php -r "exit(extension_loaded('$module') ? 0 : 1);"; then
    continue
  fi
  cd "/usr/src/php/ext/$module"
  phpize
  ./configure --with-php-config=/usr/local/bin/php-config "--enable-$module"
  make -j"${BUILD_JOBS:-2}"
  cp "modules/$module.so" /test-support/
done
