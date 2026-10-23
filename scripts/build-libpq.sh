#!/usr/bin/env bash
set -euo pipefail

row=$(awk -F '\t' 'NR > 1 && $1 == "libpq" { print $2 " " $3 " " $4; count++ } END { if (count != 1) exit 1 }' /build/extensions/libpq.tsv)
read -r version sha url <<< "$row"
archive="/source/libpq/postgresql-$version.tar.bz2"
source_dir="/usr/src/postgresql-$version"
mkdir -p /source/libpq "$source_dir"
curl --fail --location --retry 3 --proto '=https' --tlsv1.2 "$url" -o "$archive"
printf '%s  %s\n' "$sha" "$archive" | sha256sum --check --strict
tar -xjf "$archive" --strip-components=1 -C "$source_dir"
cd "$source_dir"
# Build only the client library and pg_config required by PHP's configure scripts.
./configure --prefix=/usr/local --with-ssl=openssl --without-readline --without-icu --without-zlib
make -j"${BUILD_JOBS:-2}" -C src/port
make -j"${BUILD_JOBS:-2}" -C src/common
make -j"${BUILD_JOBS:-2}" -C src/interfaces/libpq
make -C src/interfaces/libpq install
make -C src/include install
make -j"${BUILD_JOBS:-2}" -C src/bin/pg_config
make -C src/bin/pg_config install
ldconfig
test "$(pg_config --version)" = "PostgreSQL $version"
cp COPYRIGHT /source/libpq/COPYRIGHT
printf '%s  libpq/postgresql-%s.tar.bz2\n' "$sha" "$version" >> /source/SHA256SUMS
