#!/usr/bin/env bash
# Shared extensions used by the test harness, never by the runtime image.
set -euo pipefail
php_args=(-n)
extension_dir=$(php-config --extension-dir)
test_ini=${TEST_INI:-/tmp/php-test.ini}
printf '%s\n' 'date.timezone=UTC' > "$test_ini"
for extension in json pcntl posix dom; do
    if php -n -r "exit(extension_loaded('$extension') ? 0 : 1);"; then
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
export PHPRC="$test_ini" PHP_INI_SCAN_DIR=""
export TEST_PHP_EXECUTABLE=/usr/local/bin/php REPORT_EXIT_STATUS=1 NO_INTERACTION=1
