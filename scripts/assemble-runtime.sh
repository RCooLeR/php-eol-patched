#!/usr/bin/env bash
set -euo pipefail

root=/runtime-rootfs
# Do not let the test runner discover binaries from the original base build.
rm -f /usr/local/bin/phpdbg /usr/local/bin/php-cgi
cp /build/config/php.ini /usr/local/etc/php/conf.d/00-defaults.ini
cp /build/config/php-fpm.conf /usr/local/etc/php-fpm.conf
mkdir -p /usr/local/etc/php-fpm.d
rm -f /usr/local/etc/php-fpm.d/*.conf
cp /build/config/www.conf /usr/local/etc/php-fpm.d/www.conf
if [[ "$1" == 8.1.34 ]]; then
  printf 'zend_extension=opcache\n' > /usr/local/etc/php/conf.d/10-opcache.ini
fi
mkdir -p "$root/usr/local/bin" "$root/usr/local/sbin" "$root/usr/local/etc" \
  "$root/etc/ssl/certs" "$root/usr/share/php-eol-patched" "$root/tmp" "$root/var/www/html"
chmod 1777 "$root/tmp"
chown 33:33 "$root/var/www/html"
cp /usr/local/bin/php "$root/usr/local/bin/"
cp /usr/local/sbin/php-fpm "$root/usr/local/sbin/"
cp -a /usr/local/etc/php "$root/usr/local/etc/"
cp /usr/local/etc/php-fpm.conf "$root/usr/local/etc/"
cp -a /usr/local/etc/php-fpm.d "$root/usr/local/etc/"
binaries=(/usr/local/bin/php /usr/local/sbin/php-fpm)
if [[ "$1" == 8.1.34 ]]; then
  opcache="$(php-config --extension-dir)/opcache.so"
  cp --parents "$opcache" "$root"
  binaries+=("$opcache")
fi

# ldd includes transitive dependencies. NSS modules are loaded at runtime.
ldd "${binaries[@]}" > /tmp/php-libraries.txt
if grep -q 'not found' /tmp/php-libraries.txt; then
  cat /tmp/php-libraries.txt >&2
  exit 1
fi
awk '/=> \// { print $3; next } $1 ~ /^\// && $1 !~ /:$/ { print $1 }' /tmp/php-libraries.txt > /tmp/php-library-paths.txt
for library in /lib/*/libnss_dns.so.2 /lib/*/libnss_files.so.2 /lib/*/libresolv.so.2; do
  [[ ! -f "$library" ]] || printf '%s\n' "$library" >> /tmp/php-library-paths.txt
done
sort -u /tmp/php-library-paths.txt -o /tmp/php-library-paths.txt
printf '%s\n' ca-certificates > /tmp/php-runtime-packages.txt
while IFS= read -r library; do
  cp --parents --dereference "$library" "$root"
  owner=$(dpkg-query --search "$library" 2>/dev/null || dpkg-query --search "$(readlink -f "$library")")
  owner=${owner%%: /*}
  printf '%s\n' "$owner" >> /tmp/php-runtime-packages.txt
done < /tmp/php-library-paths.txt
sort -u /tmp/php-runtime-packages.txt -o /tmp/php-runtime-packages.txt
while IFS= read -r package; do
  copyright="/usr/share/doc/${package%%:*}/copyright"
  test -f "$copyright"
  cp --parents --dereference "$copyright" "$root"
  dpkg-query --show --showformat='${binary:Package}\t${Version}\t${source:Package}\t${source:Version}\n' "$package"
done < /tmp/php-runtime-packages.txt > "$root/usr/share/php-eol-patched/runtime-packages.tsv"
cp -a /usr/share/common-licenses "$root/usr/share/"
cp /etc/ssl/certs/ca-certificates.crt "$root/etc/ssl/certs/"
cp /etc/ssl/openssl.cnf "$root/etc/ssl/"
cp /etc/os-release "$root/etc/"
printf 'root:x:0:0:root:/root:/nonexistent\nwww-data:x:33:33:www-data:/var/www:/nonexistent\n' > "$root/etc/passwd"
printf 'root:x:0:\nwww-data:x:33:\n' > "$root/etc/group"
printf 'passwd: files\ngroup: files\nhosts: files dns\n' > "$root/etc/nsswitch.conf"
mkdir -p "$root/usr/share/licenses/php-eol-patched"
cp -a /build/LICENSES "$root/usr/share/licenses/php-eol-patched/"
cp /build/LICENSE /build/THIRD_PARTY_NOTICES.md /build/README.md /build/PATCHES.md "$root/usr/share/licenses/php-eol-patched/"
cp /source/SHA256SUMS "$root/usr/share/php-eol-patched/source.sha256"
cp /build/patches/SHA256SUMS "$root/usr/share/php-eol-patched/patches.sha256"
php-config --configure-options > "$root/usr/share/php-eol-patched/configure-options.txt"
php -m > "$root/usr/share/php-eol-patched/modules.txt"
printf '%s\n' "$1" > "$root/usr/share/php-eol-patched/php-version.txt"
