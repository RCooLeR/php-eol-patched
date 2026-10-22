#!/usr/bin/env bash
set -euo pipefail

. /etc/os-release
case "$VERSION_CODENAME" in
  stretch|buster|bullseye)
    # Debian's archived releases have expired metadata, but retain signature checks.
    sed -i 's/deb.debian.org/archive.debian.org/g; s|security.debian.org|archive.debian.org|g' /etc/apt/sources.list
    sed -i '/stretch-updates/d; /buster-updates/d; /bullseye-updates/d' /etc/apt/sources.list
    printf 'Acquire::Check-Valid-Until "false";\n' > /etc/apt/apt.conf.d/99archive
    ;;
esac
apt-get update
packages=(autoconf bison build-essential ca-certificates patch pkg-config re2c xz-utils libssl-dev libxml2-dev zlib1g-dev)
if [[ "$1" == 8.1.34 ]]; then
  packages+=(libonig-dev)
fi
DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends "${packages[@]}"
rm -rf /var/lib/apt/lists/*
