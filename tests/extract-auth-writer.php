<?php
$root = getenv('PHP_SOURCE_ROOT') ?: '/usr/src/php';
$source = file_get_contents($root . '/ext/mysqlnd/mysqlnd_wireprotocol.c');
$start = strpos($source, "static size_t\nphp_mysqlnd_change_auth_response_write(");
if ($start === false) { throw new RuntimeException('Auth writer signature missing'); }
$end = strpos($source, '/* }}} */', $start);
if ($end === false) { throw new RuntimeException('Auth writer end missing'); }
$writer = substr($source, $start, $end - $start);
$call = strpos($writer, 'MYSQLND_CONN_DATA * conn, void * _packet') !== false
    ? 'php_mysqlnd_change_auth_response_write(&conn, &packet)'
    : 'php_mysqlnd_change_auth_response_write(&packet)';
$template = file_get_contents(__DIR__ . '/native-auth-writer.c.in');
$output = str_replace(array('@AUTH_WRITER@', '@CALL@'), array($writer, $call), $template);
file_put_contents($argv[1], $output);
