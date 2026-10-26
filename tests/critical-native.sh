#!/usr/bin/env bash
set -euo pipefail
version=${1:?PHP version required}
source_root=${PHP_SOURCE_ROOT:-/usr/src/php}
scratch=$(mktemp -d /tmp/php-critical-native.XXXXXX)
trap 'rm -rf "$scratch"' EXIT
php -n /tests/extract-critical-native.php "$scratch" "$version"
case "$version" in
    7.1.33|7.2.34|7.3.33)
        php -n /tests/extract-auth-writer.php "$scratch/auth-writer.c"
        cc -std=c99 -O1 -Wall -Wextra "$scratch/auth-writer.c" -o "$scratch/auth-writer"
        "$scratch/auth-writer"
        ;;
esac
case "$version" in
    7.0.33)
        cc -std=c99 -O1 -Wall -Wextra "$scratch/gd-contributions.c" -o "$scratch/gd-contributions"
        for mode in normal later first overflow; do "$scratch/gd-contributions" "$mode"; done
        ;;
    7.1.33)
        cc -std=gnu99 -O1 -Wall -Wextra "$scratch/strip-tags.c" -o "$scratch/strip-tags"
        "$scratch/strip-tags"
        ;;
esac
case "$version" in
    7.1.33|7.2.34|7.3.33|7.4.33)
        cc -std=c99 -O1 -Wall -Wextra -Wno-unused-parameter "$scratch/sqlite-quote.c" -lsqlite3 -o "$scratch/sqlite-quote"
        "$scratch/sqlite-quote"
        ;;
esac
case "$version" in
    7.2.34|7.3.33)
        for variant in generic32lc generic64lc; do
            directory="$source_root/ext/hash/sha3/$variant"
            cc -std=c99 -O2 -I"$directory" -DKeccakP200_excluded -DKeccakP400_excluded -DKeccakP800_excluded \
                /tests/native-sha3.c "$directory"/*.c -o "$scratch/sha3-$variant"
            result=$("$scratch/sha3-$variant")
            test "$result" = 3a985da74fe225b2045c172d6bd390bd855f086e3e9d525b46bfe24511431532
            printf 'PASS native %s SHA3-256 vector\n' "$variant"
        done
        ;;
esac
