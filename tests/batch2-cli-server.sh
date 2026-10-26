#!/usr/bin/env bash
set -euo pipefail
test "$(php -n -r 'echo PHP_VERSION;')" = 7.4.33
scratch=$(mktemp -d /tmp/php-cli-workers.XXXXXX)
server_pid=
cleanup() {
    if [ -n "$server_pid" ]; then
        local workers=()
        if [ -r "/proc/$server_pid/task/$server_pid/children" ]; then
            read -r -a workers < "/proc/$server_pid/task/$server_pid/children" || true
        fi
        kill -TERM "${workers[@]}" "$server_pid" 2>/dev/null || true
        wait "$server_pid" 2>/dev/null || true
    fi
    rm -rf "$scratch"
}
trap cleanup EXIT
# CVE-2022-4900: the published checked allocator must reject multiplication
# overflow before starting workers. The input requires no large allocation.
status=0
PHP_CLI_SERVER_WORKERS=9223372036854775807 timeout 5 php -n -S 127.0.0.1:18074 -t "$scratch" > "$scratch/overflow.log" 2>&1 || status=$?
test "$status" -ne 0
test "$status" -ne 124
grep -Fq 'Possible integer overflow in memory allocation' "$scratch/overflow.log"
printf 'PASS CLI worker-count allocation overflow rejected\n'

printf '%s\n' '<?php echo "ordinary worker response";' > "$scratch/index.php"
PHP_CLI_SERVER_WORKERS=2 php -n -S 127.0.0.1:18074 -t "$scratch" > "$scratch/ordinary.log" 2>&1 &
server_pid=$!
php -n <<'PHP'
<?php
for ($i = 0; $i < 50; $i++) {
    $response = @file_get_contents('http://127.0.0.1:18074/');
    if ($response === 'ordinary worker response') break;
    usleep(100000);
}
if ($response !== 'ordinary worker response') exit(1);
for ($i = 0; $i < 5; $i++) {
    if (file_get_contents('http://127.0.0.1:18074/') !== 'ordinary worker response') exit(1);
}
PHP
test "$(grep -c 'Development Server' "$scratch/ordinary.log")" -eq 3
printf 'PASS CLI server starts two workers and serves ordinary requests\n'
