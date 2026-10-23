--TEST--
GHSA-r6x9-5r99-36j7 (Various packet overreads in mysqlnd_writeprotocol.c - greet packet over-read string)
--SKIPIF--
<?php if (!extension_loaded("mysqli")) die("skip mysqli extension not available"); ?>
--FILE--
<?php
require_once 'fake_server.inc';

$servername = "127.0.0.1";
$username = "root";
$password = "";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$process = run_fake_server_in_background('greet_over_read_string');
$process->wait();

try {
    $conn = new mysqli( $servername, $username, $password, "", 33305 );
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}

$process->terminate();

print "done!";
?>
--EXPECTF--
[*] Server started
[*] Connection established
[*] Sending - Malicious Server Greeting: 080000000a352e3503030303473e3f6047257c67

Warning: mysqli::mysqli(): Premature end of data (mysqlnd_wireprotocol.c:%d) in %s on line %d

Warning: mysqli::mysqli(): GREET packet shorter than expected in %s on line %d

Warning: mysqli::mysqli(): Error while reading greeting packet. PID=%d in %s on line %d

Warning: mysqli::mysqli(): [%d]  (trying to connect via tcp://127.0.0.1:33305) in %s on line %d

done!
