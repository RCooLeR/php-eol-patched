#!/usr/bin/env bash
set -euo pipefail

version=${1:?PHP release required}
overlay=/debian-files
metadata="$overlay/usr/share/php-eol-patched"
mkdir -p "$overlay/usr/src" "$overlay/usr/share/licenses" "$metadata"
cp -a /runtime-rootfs/usr/share/php-eol-patched/. "$metadata/"
# The minimal tree also records ImageMagick development files encountered during
# plugin collection. Debian installs the runtime plugins from their own packages.
awk -F '\t' '$1 !~ /-dev(:[^:]+)?$/' "$metadata/runtime-packages.tsv" > /tmp/php-debian-runtime-packages.tsv
mv /tmp/php-debian-runtime-packages.tsv "$metadata/runtime-packages.tsv"
cp -a /runtime-rootfs/usr/share/licenses/php-eol-patched "$overlay/usr/share/licenses/"

# The official extension helpers extract this archive whenever the source tree
# is absent. Package the verified patched source, never the inherited archive.
cd /source
awk -v archive="php-$version-patched.tar.xz" '$2 == archive' SHA256SUMS > /tmp/php-debian-source.sha256
test "$(wc -l < /tmp/php-debian-source.sha256)" -eq 1
sha256sum --check /tmp/php-debian-source.sha256
source_dir=$(mktemp -d)
trap 'rm -rf "$source_dir"' EXIT
mkdir "$source_dir/php-$version"
tar -xJf "php-$version-patched.tar.xz" -C "$source_dir/php-$version"
rm -f "$source_dir/php-$version/.docker-extracted" "$source_dir/php-$version/.docker-delete-me"
(
  cd "$source_dir/php-$version"
  find . -type f -print0 | LC_ALL=C sort -z | xargs -0 sha256sum
) > "$metadata/debian-source-files.sha256"
tar --owner=0 --group=0 --numeric-owner -cJf "$overlay/usr/src/php.tar.xz" -C "$source_dir" "php-$version"
(cd "$overlay/usr/src" && sha256sum php.tar.xz) > "$metadata/debian-source.sha256"

if [[ -f /runtime-rootfs/etc/fonts/conf.d/99-runtime-cache.conf ]]; then
  mkdir -p "$overlay/etc/fonts/conf.d"
  cp /runtime-rootfs/etc/fonts/conf.d/99-runtime-cache.conf "$overlay/etc/fonts/conf.d/"
fi
