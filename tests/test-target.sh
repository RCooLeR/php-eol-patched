#!/usr/bin/env bash
set -euo pipefail
version=${1:-$(php -n -r 'echo PHP_VERSION;')}
source /tests/extensions.sh
bash /tests/phpt.sh "$version"
bash /tests/critical-native.sh "$version"
php -n /tests/published-fpm-acl.php
case "$version" in
    5.6.40|7.0.33) php -n /tests/published-fpm-status.php; exit 0 ;;
esac
sh /tests/native-guards.sh
case "$version" in
    7.1.*|7.2.*) php "${php_args[@]}" /tests/mysql-http-fpm-fpm.php ;;
esac
if [ "$version" = 8.1.34 ]; then
    php -n -d memory_limit=16M /tests/mb-bounded.php
    php -n -d memory_limit=16M /tests/exif-small.php
    php -n -d memory_limit=16M -d display_errors=0 /tests/mb-empty.php
fi
