#!/usr/bin/env bash
set -euo pipefail
version=${1:?PHP version required}
test "$(php -n -r 'echo PHP_VERSION;')" = "$version"
case "$version" in
    7.1.33|7.2.34|7.3.33|7.4.33|8.0.30|8.1.34) ;;
    *) printf 'No batch-two large tests configured for %s\n' "$version" >&2; exit 1 ;;
esac
source /tests/extensions.sh
source_root=${PHP_SOURCE_ROOT:-/usr/src/php}
scratch=$(mktemp -d /tmp/php-batch2-large.XXXXXX)
trap 'rm -rf "$scratch"' EXIT
cp "$source_root/run-tests.php" "$source_root/ext/standard/tests/GHSA-96wq-48vp-hh57.phpt" "$scratch/"
selected=(GHSA-96wq-48vp-hh57.phpt)
if [ "$version" != 8.1.34 ]; then
    cp "$source_root/ext/soap/tests/soap_qname_crash.phpt" "$scratch/"
    selected+=(soap_qname_crash.phpt)
fi
cd "$scratch"
unset SKIP_SLOW_TESTS
export RUN_RESOURCE_HEAVY_TESTS=1
status=0
timeout 180 php "${php_args[@]}" run-tests.php "${php_args[@]}" -q "${selected[@]}" > result.log 2>&1 || status=$?
cat result.log
if grep -Eq '(Tests skipped|Tests borked|Tests warned|Tests failed)[[:space:]]*:[[:space:]]*[1-9]' result.log; then status=1; fi
if ! grep -Eq "Tests passed[[:space:]]*:[[:space:]]*${#selected[@]}[[:space:]]" result.log; then status=1; fi
if [ "$status" -ne 0 ]; then find "$scratch" -name '*.diff' -exec cat {} \;; fi
exit "$status"
