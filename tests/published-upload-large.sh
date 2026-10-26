#!/usr/bin/env bash
set -euo pipefail
test "$(php -n -r 'echo PHP_VERSION;')" = 7.1.33
scratch=$(mktemp -d /tmp/php-upload-large.XXXXXX)
master=
cleanup() {
    if [ -n "$master" ]; then
        kill -TERM "$master" 2>/dev/null || true
        wait "$master" 2>/dev/null || true
    fi
    rm -rf "$scratch"
}
trap cleanup EXIT
mkdir "$scratch/uploads"
cat > "$scratch/fpm.conf" <<EOF
[global]
daemonize = no
error_log = $scratch/fpm.log
[upload]
listen = 127.0.0.1:19048
user = www-data
group = www-data
pm = static
pm.max_children = 1
request_terminate_timeout = 180s
php_admin_value[memory_limit] = 6G
php_admin_value[post_max_size] = 3G
php_admin_value[upload_max_filesize] = 1M
php_admin_value[upload_tmp_dir] = $scratch/uploads
EOF
php-fpm -n -d extension=json.so -F -y "$scratch/fpm.conf" > "$scratch/process.log" 2>&1 &
master=$!
for attempt in {1..100}; do
    if php -n -r '$s=@stream_socket_client("tcp://127.0.0.1:19048"); exit($s ? 0 : 1);'; then break; fi
    sleep 0.05
done
status=0
timeout 200 php -n -d extension=json.so /tests/published-upload-large.php "$scratch/uploads" || status=$?
if [ "$status" -ne 0 ]; then cat "$scratch/fpm.log" "$scratch/process.log"; fi
exit "$status"
