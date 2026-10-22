#!/bin/sh
set -eu
# Disposable test container only. No HTTP listener or application callbacks.
cc -shared -fPIC -O2 $(php-config --includes) /tests/native-string-probe.c -o /tmp/native-guard-probe.so
set +e
normal_output=$(php -n -d extension=/tmp/native-guard-probe.so /tests/guard-probe.php normal 2>&1)
normal_result=$?
set -e
printf '%s\n' "$normal_output"
test "$normal_result" -eq 0
test "$normal_output" = PASS
echo 'PASS native unique/shared string resize controls, request and persistent allocation'
for mode in realloc-request extend-request realloc-persistent extend-persistent; do
    set +e
    php -n -d extension=/tmp/native-guard-probe.so /tests/guard-probe.php "$mode" > /tmp/guard-out 2> /tmp/guard-err
    result=$?
    set -e
    test "$result" -eq 1
    grep -F 'Possible integer overflow in memory allocation' /tmp/guard-err
    echo "PASS checked native string header addition: $mode (exit 1)"
done
php -n /tests/extract-native-byte-bound.php
cc -O2 -std=c99 -Wall -Wextra -Werror /tmp/native-byte-bound.c -o /tmp/native-byte-bound
/tmp/native-byte-bound
cc -O2 -std=c99 -Wall -Wextra -Werror /tmp/native-state-init.c -o /tmp/native-state-init
/tmp/native-state-init
if php -n -r 'exit(PHP_VERSION_ID >= 80100 ? 0 : 1);'; then
    php -n /tests/extract-native-exif.php
    cc -O2 -std=c99 -Wall -Wextra -Werror /tmp/native-exif-bound.c -o /tmp/native-exif-bound
    /tmp/native-exif-bound
fi
