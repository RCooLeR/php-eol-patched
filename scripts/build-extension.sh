#!/usr/bin/env bash
set -euo pipefail

version=${1:?PHP release required}
kind=${2:?Extension source required}
module=${3:?Extension name required}
extension_dir=$(php-config --extension-dir)
mkdir -p /usr/local/etc/php/conf.d
export CFLAGS="${PHP_CFLAGS:-}" CPPFLAGS="${PHP_CPPFLAGS:-}" LDFLAGS="${PHP_LDFLAGS:-}"
args=(--with-php-config=/usr/local/bin/php-config "--with-libdir=lib/$(dpkg-architecture -qDEB_HOST_MULTIARCH)")
ini_order=20
ini_directive=extension
if [[ "$module" == opcache ]]; then
  ini_order=10
  ini_directive=zend_extension
fi

# Do not rebuild extensions that are part of the core binary.
check_module=$module
[[ "$module" != opcache ]] || check_module='Zend OPcache'
if php -n -r "exit(extension_loaded('$check_module') ? 0 : 1);"; then
  exit 0
fi

if [[ "$kind" == bundled ]]; then
  source_dir="/usr/src/php/ext/$module"
  test -d "$source_dir"
  case "$module" in
    gd)
      case "$version" in
        5.*|7.[0-3].*) args+=(--with-gd --with-jpeg-dir=/usr --with-png-dir=/usr --with-zlib-dir=/usr --with-freetype-dir=/usr/local) ;;
        *) args+=(--enable-gd --with-jpeg --with-freetype) ;;
      esac
      ;;
    zip)
      case "$version" in
        5.*|7.[0-3].*) args+=(--enable-zip --with-libzip=/usr) ;;
        *) args+=(--with-zip) ;;
      esac
      ;;
    pdo_sqlite) args+=(--with-pdo-sqlite=/usr) ;;
    sqlite3) args+=(--with-sqlite3=/usr) ;;
    curl|iconv|zlib) args+=("--with-$module") ;;
    xml)
      args+=(--enable-xml)
      # Legacy compat.c reads the core configuration, which has XML disabled.
      export CPPFLAGS="$CPPFLAGS -DHAVE_XML=1"
      ;;
    *) args+=("--enable-$module") ;;
  esac
elif [[ "$kind" == pecl ]]; then
  row=$(awk -F '\t' -v php="$version" -v package="$module" 'NR > 1 && $1 == php && $2 == package { print $3 " " $4 " " $5; count++ } END { if (count != 1) exit 1 }' /build/extensions/pecl.tsv)
  read -r release sha url <<< "$row"
  archive="/source/pecl/$module-$release.tgz"
  source_dir="/usr/src/pecl/$module-$release"
  mkdir -p /source/pecl /usr/src/pecl
  if [[ ! -f "$archive" ]]; then
    curl --fail --location --retry 3 --proto '=https' --tlsv1.2 "$url" -o "$archive"
  fi
  printf '%s  %s\n' "$sha" "$archive" | sha256sum --check --strict
  mkdir -p "/source/pecl/$module-$release"
  tar -xzf "$archive" --strip-components=1 -C "/source/pecl/$module-$release"
  cp -a "/source/pecl/$module-$release" "$source_dir"
  printf '%s  pecl/%s-%s.tgz\n' "$sha" "$module" "$release" >> /source/SHA256SUMS
  ini_order=30
  case "$module" in
    igbinary) args+=(--enable-igbinary) ;;
    msgpack) args+=(--with-msgpack) ;;
    apcu) args+=(--enable-apcu) ;;
    imagick) args+=(--with-imagick) ;;
    redis)
      ini_order=40
      args+=(--enable-redis --enable-redis-igbinary)
      [[ "$release" == 4.* ]] || args+=(--enable-redis-msgpack --enable-redis-json)
      ;;
    memcached)
      ini_order=40
      args+=(--enable-memcached --enable-memcached-igbinary --enable-memcached-msgpack --enable-memcached-json --enable-memcached-sasl)
      ;;
    *) printf 'Unsupported PECL extension: %s\n' "$module" >&2; exit 2 ;;
  esac
else
  printf 'Unknown extension source: %s\n' "$kind" >&2
  exit 2
fi

if [[ ! -f "$extension_dir/$module.so" ]]; then
  cd "$source_dir"
  # Core builds name early configuration fragments config0.m4.
  [[ -f config.m4 ]] || cp config0.m4 config.m4
  phpize
  # PHP8.1 tokenizer's make fragment refers to the core's generated Zend parser.
  if [[ "$kind" == bundled && "$module" == tokenizer && "$version" == 8.1.* ]]; then
    ln -s /usr/src/php/Zend "$source_dir/Zend"
  fi
  # Older filter config treats the base image's PHP_VERSION as an in-tree build.
  if [[ "$kind" == bundled && "$module" == filter ]]; then
    env -u PHP_VERSION ./configure "${args[@]}"
  else
    ./configure "${args[@]}"
  fi
  make -j"${BUILD_JOBS:-2}"
  make install
fi
ini_library="$module.so"
[[ "$ini_directive" != zend_extension ]] || ini_library="$extension_dir/$module.so"
printf '%s=%s\n' "$ini_directive" "$ini_library" > "/usr/local/etc/php/conf.d/$ini_order-$module.ini"
php -r "exit(extension_loaded('$check_module') ? 0 : 1);"
