#!/usr/bin/env bash
set -euo pipefail
version=${1:?PHP version required}
test_dir="/tests/versions/$version"
test "$(php -n -r 'echo PHP_VERSION;')" = "$version"
source /tests/extensions.sh
mapfile -t tests < "$test_dir/postgresql-tests.list"
test "${#tests[@]}" -gt 0
# These published extension cases share the upstream fixture tables.
tests=(ext/pgsql/tests/01createdb.phpt "${tests[@]}")
source_root=${PHP_SOURCE_ROOT:-/usr/src/php}
scratch=$(mktemp -d /tmp/php-postgresql-phpt.XXXXXX)
cp "$source_root/run-tests.php" "$scratch/"
if [ "$version" = 5.6.40 ]; then
    # Match the legacy runner's handling of modules already loaded by -d.
    sed -i '/^[[:space:]]*\$loaded = explode(.*get_loaded_extensions()/c\        $loaded = get_loaded_extensions();' "$scratch/run-tests.php"
fi
for extension in pgsql pdo_pgsql pdo; do
    mkdir -p "$scratch/ext/$extension"
    cp -a "$source_root/ext/$extension/tests" "$scratch/ext/$extension/"
    if [ -d "$test_dir/overrides/ext/$extension" ]; then
        cp -a "$test_dir/overrides/ext/$extension/." "$scratch/ext/$extension/"
    fi
done
cd "$scratch"
for selected in "${tests[@]}"; do test -f "$selected"; done
export TEST_PHP_JUNIT="$scratch/results.xml"
status=0
php "${php_args[@]}" ./run-tests.php "${php_args[@]}" -q "${tests[@]}" > result.log 2>&1 || status=$?
cat result.log
if grep -Eq '(Tests skipped|Tests borked|Tests warned|Tests failed)[[:space:]]*:[[:space:]]*[1-9]' result.log; then status=1; fi
if [ "$status" -ne 0 ]; then find "$scratch" -name '*.diff' -type f -exec cat {} \;; fi
exit "$status"
