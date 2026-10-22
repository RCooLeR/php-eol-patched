#!/usr/bin/env bash
set -euo pipefail

version=${1:?PHP release required}
root=/build/patches
test "$(php -n -r 'echo PHP_VERSION;')" = "$version"
test -n "${PHP_SHA256:-}"
printf '%s  /usr/src/php.tar.xz\n' "$PHP_SHA256" | sha256sum --check -
(cd "$root" && sha256sum --check SHA256SUMS)
docker-php-source extract
cd /usr/src/php
patch --batch --forward --fuzz=0 -p1 < "$root/$version/core.patch"
sha256sum --check "$root/$version/soap-before.sha256"
patch --batch --forward --fuzz=0 -p1 < "$root/$version/soap.patch"
sha256sum --check "$root/$version/soap-after.sha256"
for stage in published mysql-http-fpm; do
  sha256sum --check "$root/$version/$stage/source-before.sha256"
  while IFS= read -r filename; do
    [[ -n "$filename" ]] || continue
    patch --batch --forward --fuzz=0 -p1 < "$root/$version/$stage/$filename"
  done < "$root/$version/$stage/series"
  sha256sum --check "$root/$version/$stage/source-after.sha256"
done
if [[ "$version" == 8.1.34 ]]; then
  # LGPL requires a dated notice in modified library source files.
  sed -i '1i /* Modified 2026-10-22: bound the initial mb_strcut allocation to input length. */' ext/mbstring/libmbfl/mbfl/mbfilter.c
fi
mkdir -p /source
tar -cJf "/source/php-$version-patched.tar.xz" -C /usr/src/php .
(cd /source && sha256sum "php-$version-patched.tar.xz" > SHA256SUMS)
