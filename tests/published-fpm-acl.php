<?php
// Exercise the published IPv6 ACL regression and legitimate clients on loopback.
function record($type, $content) {
    return pack('CCnnCC', 1, $type, 1, strlen($content), 0, 0) . $content;
}
function request($host, $port, $script) {
    $socket = @stream_socket_client('tcp://' . $host . ':' . $port, $errno, $error, 2);
    if (!$socket) { throw new RuntimeException('Cannot reach test FPM: ' . $error); }
    stream_set_timeout($socket, 2);
    $params = '';
    foreach (array('SCRIPT_FILENAME' => $script, 'REQUEST_METHOD' => 'GET', 'SERVER_PROTOCOL' => 'HTTP/1.1') as $key => $value) {
        $params .= chr(strlen($key)) . chr(strlen($value)) . $key . $value;
    }
    @fwrite($socket, record(1, pack('nCxxxxx', 1, 0)) . record(4, $params) . record(4, '') . record(5, ''));
    $response = @stream_get_contents($socket);
    fclose($socket);
    return strpos($response, 'ACL_CONTROL_OK') !== false;
}
$dir = sys_get_temp_dir() . '/php-fpm-acl-' . getmypid();
mkdir($dir);
file_put_contents($dir . '/index.php', '<?php echo "ACL_CONTROL_OK";');
$cases = array(
    array('ipv6-allowed', '::1', '[::1]', true),
    array('ipv6-last32-denied', '::dead:beef', '[::1]', false),
    array('mapped-allowed', '::ffff:127.0.0.1', '127.0.0.1', true),
    array('mapped-last32-denied', '::ffff:203.0.113.1', '127.0.0.1', false),
    array('ipv4-allowed', '127.0.0.1', '127.0.0.1', true),
    array('ipv4-denied', '203.0.113.1', '127.0.0.1', false),
);
$failed = 0;
foreach ($cases as $case) {
    list($name, $allowed, $host, $expected) = $case;
    $probe = stream_socket_server('tcp://[::]:0', $errno, $error);
    if (!$probe) { throw new RuntimeException('IPv6 loopback unavailable: ' . $error); }
    $address = stream_socket_get_name($probe, false);
    $port = (int) substr($address, strrpos($address, ':') + 1);
    fclose($probe);
    $conf = "[global]\ndaemonize = no\nerror_log = $dir/$name.log\n[acl]\nlisten = [::]:$port\nlisten.allowed_clients = $allowed\npm = static\npm.max_children = 1\ncatch_workers_output = yes\nuser = www-data\ngroup = www-data\n";
    file_put_contents($dir . '/fpm.conf', $conf);
    $process = proc_open('/usr/local/sbin/php-fpm -n -F -y ' . escapeshellarg($dir . '/fpm.conf'), array(0 => array('file', '/dev/null', 'r'), 1 => array('file', $dir . '/stdout', 'a'), 2 => array('file', $dir . '/stderr', 'a')), $pipes);
    if (!is_resource($process)) { throw new RuntimeException('Cannot start test FPM'); }
    try {
        $ready = false;
        for ($i = 0; $i < 100; $i++) {
            $log = @file_get_contents($dir . '/' . $name . '.log');
            if ($log !== false && strpos($log, 'ready to handle connections') !== false) { $ready = true; break; }
            usleep(20000);
        }
        if (!$ready) { throw new RuntimeException('FPM did not become ready: ' . @file_get_contents($dir . '/stderr')); }
        $actual = request($host, $port, $dir . '/index.php');
        $ok = $actual === $expected;
        echo ($ok ? 'PASS ' : 'FAIL ') . $name . "\n";
        if (!$ok) { $failed++; }
    } finally {
        proc_terminate($process);
        proc_close($process);
    }
}
exit($failed ? 1 : 0);
