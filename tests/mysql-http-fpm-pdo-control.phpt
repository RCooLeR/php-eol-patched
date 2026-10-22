--TEST--
PDO MySQL reads valid values through the published mysqlnd fake-server fixture
--SKIPIF--
<?php
if (!extension_loaded('pdo_mysql')) die('skip pdo_mysql required');
?>
--FILE--
<?php
require __DIR__ . '/ext/mysqli/tests/fake_server.inc';
// Reuse the published protocol's valid query-response control with PDO's consumer.
ob_start();
$process = run_fake_server_in_background('query_response_row_read_two_fields', 33305);
$conn = null;
try {
    $process->wait();
    $conn = new PDO('mysql:host=127.0.0.1;port=33305', 'root', '', array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_STRINGIFY_FETCHES => true
    ));
    $expected = array('intval' => '14', 'fltval' => '2.3', 'dblval' => '1.2',
        'datval' => '2014-12-15', 'timval' => '13:00:02',
        'dtival' => '2014-12-16 13:00:01', 'bitval' => '578721382704613384', 'strval' => 'test');
    foreach ($expected as $field => $value) {
        $statement = $conn->query('SELECT strval, ' . $field . ' FROM data');
        $row = $statement->fetch(PDO::FETCH_NUM);
        if (count($row) !== 2 || $row[0] !== 'test' || (string) $row[1] !== $value
            || $statement->fetch(PDO::FETCH_NUM) !== false) {
            throw new RuntimeException('Unexpected PDO result for ' . $field);
        }
        $statement = null;
    }
    $conn = null;
    $process->terminate(true);
} finally {
    $conn = null;
    $process->terminate();
    ob_end_clean();
}
echo "PASS PDO MySQL valid row values (8 fields)\n";
?>
--EXPECTF--
%APASS PDO MySQL valid row values (8 fields)
