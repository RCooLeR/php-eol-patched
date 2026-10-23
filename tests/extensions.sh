#!/usr/bin/env bash
# Load application extensions explicitly under -n, then test-only helpers.
set -euo pipefail
php_args=(-n -d date.timezone=UTC)
extension_dir=$(php-config --extension-dir)
test_ini=${TEST_INI:-/tmp/php-test.ini}
printf '%s\n' 'date.timezone=UTC' > "$test_ini"
for configuration in /usr/local/etc/php/conf.d/*.ini; do
    [ -f "$configuration" ] || continue
    while IFS= read -r directive || [ -n "$directive" ]; do
        if [[ "$directive" =~ ^[[:space:]]*(zend_)?extension[[:space:]]*= ]]; then
            php_args+=(-d "$directive")
            printf '%s\n' "$directive" >> "$test_ini"
        fi
    done < "$configuration"
done
for extension in json pcntl posix dom; do
    if php "${php_args[@]}" -r "exit(extension_loaded('$extension') ? 0 : 1);"; then
        continue
    fi
    module="$extension_dir/$extension.so"
    if [ ! -f "$module" ]; then module="/test-support/$extension.so"; fi
    if [ ! -f "$module" ]; then
        printf 'Missing test extension: %s\n' "$extension" >&2
        exit 1
    fi
    php_args+=(-d "extension=$module")
    printf 'extension=%s\n' "$module" >> "$test_ini"
    if [ "$extension" = json ]; then export PHP_TEST_JSON_EXTENSION="$module"; fi
done
if [ -f "$extension_dir/json.so" ]; then
    export PHP_TEST_JSON_EXTENSION="$extension_dir/json.so"
fi
scan_dir=$(mktemp -d /tmp/php-test-ini.XXXXXX)
export PHPRC="$test_ini" PHP_INI_SCAN_DIR="$scan_dir"
export TEST_PHP_EXECUTABLE=/usr/local/bin/php REPORT_EXIT_STATUS=1 NO_INTERACTION=1
