<?php
/* Credentials remain in this process tree; never include them in report output. */
$config = json_decode(file_get_contents('php://stdin'), true);
$certificate = tempnam('/tmp', 'postgresql-ca-');
file_put_contents($certificate, $config['certificate']);
$connection = 'host=postgresql port=5432 dbname=' . $config['database']
    . ' user=' . $config['username'] . ' password=' . $config['password']
    . ' sslmode=verify-full sslrootcert=' . $certificate;
$dsn = 'pgsql:host=postgresql;port=5432;dbname=' . $config['database']
    . ';sslmode=verify-full;sslrootcert=' . $certificate;
putenv('PGSQL_TEST_CONNSTR=' . $connection);
putenv('PDO_PGSQL_TEST_DSN=' . $dsn);
putenv('PDOTEST_DSN=' . $dsn);
putenv('PDOTEST_USER=' . $config['username']);
putenv('PDOTEST_PASS=' . $config['password']);
$pipes = array();
$process = proc_open('/bin/bash /tests/postgresql-phpt.sh ' . escapeshellarg($argv[1]) . ' 2>&1',
    array(0 => array('pipe', 'r'), 1 => array('pipe', 'w')), $pipes);
if (!is_resource($process)) { unlink($certificate); exit(1); }
fclose($pipes[0]);
$output = stream_get_contents($pipes[1]);
fclose($pipes[1]);
$status = proc_close($process);
unlink($certificate);
echo str_replace($config['password'], '[redacted]', $output);
exit($status);
