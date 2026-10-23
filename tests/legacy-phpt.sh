#!/usr/bin/env bash
set -euo pipefail
version=${1:?PHP version required}
test_dir="/tests/versions/$version"
test "$(php -n -r 'echo PHP_VERSION;')" = "$version"
test -f "$test_dir/published-tests.list"
source /tests/extensions.sh
mapfile -t tests < "$test_dir/published-tests.list"
test "${#tests[@]}" -gt 0
source_root=${PHP_SOURCE_ROOT:-/usr/src/php}
scratch=$(mktemp -d /tmp/php-published.XXXXXX)
cp "$source_root/run-tests.php" "$scratch/"
if [ "$version" = 5.6.40 ]; then
    # The backported EXTENSIONS handler ignores modules already loaded by -d.
    sed -i '/^[[:space:]]*\$loaded = explode(.*get_loaded_extensions()/c\        $loaded = get_loaded_extensions();' "$scratch/run-tests.php"
fi
mkdir -p "$scratch/ext" "$scratch/sapi" "$scratch/Zend"
directories=(ext/standard/tests ext/pdo_mysql/tests Zend/tests)
for selected in "${tests[@]}"; do
    if [[ "$selected" =~ ^(ext|sapi)/([^/]+)/tests/ ]]; then
        directories+=("${BASH_REMATCH[1]}/${BASH_REMATCH[2]}/tests")
    fi
done
for directory in "${directories[@]}"; do
    if [ ! -d "$scratch/$directory" ]; then
        mkdir -p "$scratch/$(dirname "$directory")"
        cp -a "$source_root/$directory" "$scratch/$directory"
    fi
done
cp /tests/published-filter-controls.phpt /tests/mysql-http-fpm-pdo-control.phpt "$scratch/"
if [ -d "$test_dir/overrides" ]; then cp -a "$test_dir/overrides/." "$scratch/"; fi
cd "$scratch"
for selected in "${tests[@]}"; do test -f "$selected"; done
export TEST_PHP_JUNIT="$scratch/results.xml"
export TEST_PHP_FPM_EXECUTABLE=/usr/local/sbin/php-fpm
status=0
php "${php_args[@]}" ./run-tests.php "${php_args[@]}" \
    -d soap.wsdl_cache_enabled=0 -q "${tests[@]}" > result.log 2>&1 || status=$?
cat result.log
if grep -Eq '(Tests skipped|Tests borked|Tests warned|Tests failed)[[:space:]]*:[[:space:]]*[1-9]' result.log; then status=1; fi
if [ "$status" -ne 0 ]; then find "$scratch" -name '*.diff' -type f -exec cat {} \;; fi
exit "$status"
