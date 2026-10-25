#!/usr/bin/env bash
set -euo pipefail
version=${1:?PHP version required}
test "$(php -n -r 'echo PHP_VERSION;')" = "$version"
case "$version" in
    7.1.33|7.2.34|7.3.33|7.4.33) ;;
    *) printf 'No large critical-CVE tests configured for %s\n' "$version" >&2; exit 1 ;;
esac
source /tests/extensions.sh
source_root=${PHP_SOURCE_ROOT:-/usr/src/php}
scratch=$(mktemp -d /tmp/php-critical-large.XXXXXX)
trap 'rm -rf "$scratch"' EXIT
cp "$source_root/run-tests.php" "$source_root/ext/pdo_sqlite/tests/bug81740.phpt" "$scratch/"
cd "$scratch"
unset SKIP_SLOW_TESTS
php "${php_args[@]}" run-tests.php "${php_args[@]}" -q bug81740.phpt > result.log 2>&1
cat result.log
if grep -Eq '(Tests skipped|Tests borked|Tests warned|Tests failed)[[:space:]]*:[[:space:]]*[1-9]' result.log; then exit 1; fi
grep -Eq 'Tests passed[[:space:]]*:[[:space:]]*1[[:space:]]' result.log
case "$version" in
    7.2.34|7.3.33)
        directory="$source_root/ext/hash/sha3/generic64lc"
        cc -std=c99 -O2 -I"$directory" -DKeccakP200_excluded -DKeccakP400_excluded -DKeccakP800_excluded \
            /tests/native-sha3.c "$directory"/*.c -o "$scratch/sha3"
        result=$(timeout 90 "$scratch/sha3" large)
        test "$result" = c5bcc3bc73b5ef45e91d2d7c70b64f196fac08eee4e4acf6e6571ebe
        printf 'PASS native Keccak published 4 GiB SHA3-224 vector\n'
        ;;
esac
