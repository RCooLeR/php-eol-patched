#!/usr/bin/env bash
set -euo pipefail

. /etc/os-release
codename=${VERSION_CODENAME:-}
if [[ -z "$codename" && "${VERSION_ID:-}" == 9 ]]; then
  codename=stretch
fi
case "$codename" in
  stretch|buster|bullseye)
    # Debian's archived releases have expired metadata, but retain signature checks.
    sed -i 's/deb.debian.org/archive.debian.org/g; s|security.debian.org|archive.debian.org|g' /etc/apt/sources.list
    sed -i '/stretch-updates/d; /buster-updates/d; /bullseye-updates/d' /etc/apt/sources.list
    printf 'Acquire::Check-Valid-Until "false";\n' > /etc/apt/apt.conf.d/99archive
    ;;
esac
apt-get update
packages=(
  autoconf bison build-essential ca-certificates curl git patch pkg-config re2c xz-utils
  libssl-dev libxml2-dev zlib1g-dev libcurl4-openssl-dev libicu-dev libzip-dev
  libfreetype6-dev libjpeg62-turbo-dev libpng-dev libsqlite3-dev
  libmagickwand-dev libmemcached-dev libsasl2-dev fonts-dejavu-core
)
case "$1" in
  7.4.*|8.*) packages+=(libonig-dev) ;;
esac
DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends "${packages[@]}"
rm -rf /var/lib/apt/lists/*

# PHP before7.4 asks for freetype-config; newer Debian provides pkg-config.
if [[ ! -x /usr/local/bin/freetype-config ]]; then
  cat > /usr/local/bin/freetype-config <<'FREETYPE'
#!/bin/sh
exec pkg-config freetype2 "$@"
FREETYPE
  chmod 0755 /usr/local/bin/freetype-config
fi
