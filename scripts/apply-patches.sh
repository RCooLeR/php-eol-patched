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
if [[ -f "$root/$version/series" ]]; then
  sha256sum --check "$root/$version/source-before.sha256"
  while IFS= read -r filename; do
    [[ -n "$filename" ]] || continue
    if grep -q '^GIT binary patch$' "$root/$version/$filename"; then
      git apply --check "$root/$version/$filename"
      git apply --whitespace=nowarn "$root/$version/$filename"
    else
      patch --batch --forward --fuzz=0 -p1 < "$root/$version/$filename"
    fi
  done < "$root/$version/series"
  sha256sum --check "$root/$version/source-after.sha256"
else
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
fi
for stage in postgresql coverage; do
  [[ -f "$root/$version/$stage/series" ]] || continue
  sha256sum --check "$root/$version/$stage/source-before.sha256"
  while IFS= read -r filename; do
    [[ -n "$filename" ]] || continue
    if grep -q '^GIT binary patch$' "$root/$version/$stage/$filename"; then
      git apply --check "$root/$version/$stage/$filename"
      git apply --whitespace=nowarn "$root/$version/$stage/$filename"
    else
      patch --batch --forward --fuzz=0 -p1 < "$root/$version/$stage/$filename"
    fi
  done < "$root/$version/$stage/series"
  sha256sum --check "$root/$version/$stage/source-after.sha256"
done
# Retain dated modification notices in the exported LGPL library source.
case "$version" in
  5.6.40|7.0.33)
    sed -i '1i /* Modified 2026-10-23: apply the published Big5 conversion bounds fix (PHP bug 79037). */' ext/mbstring/libmbfl/filters/mbfilter_big5.c
    ;;
  7.1.33)
    sed -i '1i /* Modified 2026-10-26: apply the published CP950 conversion bounds fix (PHP bug 79037). */' ext/mbstring/libmbfl/filters/mbfilter_big5.c
    sed -i '1i /* Modified 2026-10-26: restrict BCMath numeric input to ASCII digits (PHP bug 78878). */' ext/bcmath/libbcmath/src/str2num.c
    ;;
  8.1.34)
    sed -i '1i /* Modified 2026-10-22: bound the initial mb_strcut allocation to input length. */' ext/mbstring/libmbfl/mbfl/mbfilter.c
    ;;
esac
mkdir -p /source
tar -cJf "/source/php-$version-patched.tar.xz" -C /usr/src/php .
(cd /source && sha256sum "php-$version-patched.tar.xz" > SHA256SUMS)
