#!/usr/bin/env bash
set -euo pipefail
version=${1:?PHP version required}
case "$version" in
    5.6.40|7.0.33) exec /bin/bash /tests/legacy-phpt.sh "$version" ;;
esac
test_dir="/tests/versions/$version"
test -d "$test_dir"
test "$(php -n -r 'echo PHP_VERSION;')" = "$version"
source /tests/extensions.sh
source_root=${PHP_SOURCE_ROOT:-/usr/src/php}
scratch=$(mktemp -d /tmp/php-phpt.XXXXXX)
cp "$source_root/run-tests.php" "$scratch/"
mkdir -p "$scratch/ext" "$scratch/sapi/cli" "$scratch/Zend"
for extension in openssl standard soap phar mysqli pdo_mysql gd; do
    mkdir -p "$scratch/ext/$extension"
    cp -a "$source_root/ext/$extension/tests" "$scratch/ext/$extension/"
done
cp -a "$source_root/sapi/cli/tests" "$scratch/sapi/cli/"
cp -a "$source_root/Zend/tests" "$scratch/Zend/"
cp /tests/published-filter-controls.phpt /tests/mysql-http-fpm-pdo-control.phpt "$scratch/"
cp -a /tests/soap-regressions "$scratch/"
if [ -d "$test_dir/overrides" ]; then cp -a "$test_dir/overrides/." "$scratch/"; fi
cd "$scratch"

run_group() {
    local group=$1
    shift
    export TEST_PHP_JUNIT="$scratch/$group.xml"
    local status=0
    php "${php_args[@]}" ./run-tests.php "${php_args[@]}" -d soap.wsdl_cache_enabled=0 -q "$@" > "$group.log" 2>&1 || status=$?
    cat "$group.log"
    if grep -Eq '(Tests skipped|Tests borked|Tests warned|Tests failed)[[:space:]]*:[[:space:]]*[1-9]' "$group.log"; then status=1; fi
    if [ "$status" -ne 0 ]; then
        find "$scratch" -name '*.diff' -type f -exec cat {} \;
        return "$status"
    fi
}

mapfile -t published < "$test_dir/published-tests.list"
mapfile -t mysql_http < "$test_dir/mysql-http-fpm-tests.list"
run_group published "${published[@]}"
run_group mysql-http "${mysql_http[@]}"
source /tests/coverage-phpt.sh
run_group soap soap-regressions/*.phpt \
    ext/soap/tests/bugs/bug30994.phpt ext/soap/tests/bugs/bug37013.phpt \
    ext/soap/tests/bugs/bug41566.phpt ext/soap/tests/soap12/T76_2.phpt \
    ext/soap/tests/bug71996.phpt ext/soap/tests/classmap001.phpt \
    ext/soap/tests/classmap002.phpt ext/soap/tests/classmap003.phpt
core_tests=(Zend/tests/bug70253.phpt ext/standard/tests/strings/parse_str_*.phpt
    ext/standard/tests/serialize/serialization_arrays_001.phpt
    ext/standard/tests/general_functions/output_add_rewrite_var_basic[1-4].phpt)
if [ "$version" != 8.1.34 ]; then core_tests+=(Zend/tests/bug70258.phpt); fi
run_group core "${core_tests[@]}"
if [ "$version" = 8.1.34 ]; then
    mkdir -p ext/exif
    cp -a "$source_root/ext/exif/tests" ext/exif/
    run_group exif ext/exif/tests/exif010.phpt ext/exif/tests/exif011.phpt ext/exif/tests/exif_thumbnail_streams.phpt
fi
if [ "${SOAP_RUN_RESOURCE_HEAVY_TESTS:-0}" = 1 ]; then
    run_group soap-large ext/soap/tests/bugs/GHSA-cj93-vc83-wgqv.phpt
fi
