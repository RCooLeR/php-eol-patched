--TEST--
GHSA-cj93-vc83-wgqv: PHP 5 rejects a chunk beyond its native response-size limit
--INI--
memory_limit=128M
soap.wsdl_cache_enabled=0
--SKIPIF--
<?php
if (!extension_loaded('soap')) die('skip soap required');
if (!function_exists('pcntl_fork')) die('skip pcntl_fork required');
?>
--FILE--
<?php
/* The published GHSA-cj93-vc83-wgqv case starts with a 0x7fffffff chunk.
 * PHP 5's response API uses int lengths, so its backport must reject that
 * header before reading the multi-gigabyte body used by the PHP 7 test. */
$server = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
$location = 'http://' . stream_socket_get_name($server, false);
$pid = pcntl_fork();
if ($pid === -1) die('could not fork');
if ($pid === 0) {
    pcntl_alarm(20);
    $socket = stream_socket_accept($server, 10);
    if (!$socket) exit(1);
    $length = 0;
    while (($line = fgets($socket)) !== false && trim($line) !== '') {
        if (stripos($line, 'Content-Length:') === 0) {
            $length = (int) substr($line, strlen('Content-Length:'));
        }
    }
    while ($length > 0 && ($data = fread($socket, $length)) !== false && $data !== '') {
        $length -= strlen($data);
    }
    $response = "HTTP/1.1 200 OK\r\nContent-Type: text/xml\r\n"
        . "Transfer-Encoding: chunked\r\nConnection: close\r\n\r\n7fffffff\r\nxxxx";
    while ($response !== '') {
        $written = fwrite($socket, $response);
        if (!$written) exit(1);
        $response = (string) substr($response, $written);
    }
    fclose($socket);
    fclose($server);
    exit(0);
}
fclose($server);
$client = new SoapClient(null, array('uri' => 'http://testuri.org/', 'location' => $location));
try {
    $client->__soapCall('getItems', array());
    echo "unexpected success\n";
} catch (SoapFault $error) {
    echo $error->faultcode, ': ', $error->getMessage(), "\n";
}
pcntl_waitpid($pid, $status);
var_dump(pcntl_wifexited($status) && pcntl_wexitstatus($status) === 0);
?>
--EXPECT--
HTTP: Error Fetching http body, No Content-Length, connection closed or chunked data
bool(true)
