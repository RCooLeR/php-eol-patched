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
extension_dir=$(php-config --extension-dir)
for extension in "$extension_dir"/*.so; do
  [[ -f "$extension" ]] || continue
  cp --parents "$extension" "$root"
  binaries+=("$extension")
done

printf '%s\n' ca-certificates > /tmp/php-runtime-packages.txt
copy_package_file() {
  local file=$1 owner
  cp --parents --dereference "$file" "$root"
  # This library is built from the locked upstream archive, not a Debian package.
  case "$file" in /usr/local/lib/libpq.so*) return ;; esac
  owner=$(dpkg-query --search "$file" 2>/dev/null || dpkg-query --search "$(readlink -f "$file")")
  owner=${owner%%: /*}
  printf '%s\n' "$owner" >> /tmp/php-runtime-packages.txt
}

# ImageMagick loads its coders and filters dynamically. Keep their libraries,
# format configuration, policy and fonts, without copying command-line tools.
if php -r 'exit(extension_loaded("imagick") ? 0 : 1);'; then
  for directory in /etc/ImageMagick-* /usr/share/ImageMagick-* /usr/lib/*/ImageMagick-* \
      /etc/fonts /usr/share/fonts/truetype/dejavu; do
    [[ -d "$directory" ]] || continue
    while IFS= read -r -d '' file; do
      copy_package_file "$file"
      [[ "$file" != *.so ]] || binaries+=("$file")
    done < <(find -L "$directory" -type f ! -name .uuid -print0)
  done
  # FPM clears environment variables, so give Fontconfig an explicit cache path.
  cat > "$root/etc/fonts/conf.d/99-runtime-cache.conf" <<'EOF'
<?xml version="1.0"?>
<!DOCTYPE fontconfig SYSTEM "urn:fontconfig:fonts.dtd">
<fontconfig>
  <cachedir>/tmp/fontconfig</cachedir>
</fontconfig>
EOF
  cp "$root/etc/fonts/conf.d/99-runtime-cache.conf" /etc/fonts/conf.d/
fi

# iconv and SASL discover these modules at runtime rather than through ELF links.
for directory in /usr/lib/*/gconv /usr/lib/*/sasl2; do
  [[ -d "$directory" ]] || continue
  while IFS= read -r -d '' file; do
    copy_package_file "$file"
    case "$file" in *.so|*.so.*) binaries+=("$file") ;; esac
  done < <(find -L "$directory" -type f \( -name '*.so' -o -name '*.so.*' -o -name 'gconv-modules' -o -name '*.conf' \) -print0)
done

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
while IFS= read -r library; do
  copy_package_file "$library"
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
cp /build/LICENSE /build/THIRD_PARTY_NOTICES.md /build/README.md /build/PATCHES.md /build/EXTENSIONS.md "$root/usr/share/licenses/php-eol-patched/"
cp /source/SHA256SUMS "$root/usr/share/php-eol-patched/source.sha256"
cp /build/patches/SHA256SUMS "$root/usr/share/php-eol-patched/patches.sha256"
cp /build/extensions/pecl.tsv "$root/usr/share/php-eol-patched/pecl.tsv"
cp /build/extensions/libpq.tsv "$root/usr/share/php-eol-patched/libpq.tsv"
cp /source/libpq/COPYRIGHT "$root/usr/share/licenses/php-eol-patched/PostgreSQL-COPYRIGHT"
php-config --configure-options > "$root/usr/share/php-eol-patched/configure-options.txt"
php -m > "$root/usr/share/php-eol-patched/modules.txt"
printf '%s\n' "$1" > "$root/usr/share/php-eol-patched/php-version.txt"
