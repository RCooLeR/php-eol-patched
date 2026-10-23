#!/usr/bin/env bash
set -euo pipefail

# Private key and database contents exist only in this container's tmpfs mounts.
openssl req -new -x509 -nodes -newkey rsa:2048 -days 1 \
  -subj /CN=postgresql -addext subjectAltName=DNS:postgresql \
  -keyout /tmp/postgresql-server.key -out /tmp/postgresql-server.crt >/dev/null 2>&1
chmod 0600 /tmp/postgresql-server.key
exec /usr/local/bin/docker-entrypoint.sh postgres \
  -c ssl=on -c ssl_cert_file=/tmp/postgresql-server.crt -c ssl_key_file=/tmp/postgresql-server.key
