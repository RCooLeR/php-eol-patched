<?php
/* GHSA-865w-9rf3-2wh5 published split-marker payloads and ordinary log controls. */
function flushRecord($type, $content) {
    return pack('CCnnCC', 1, $type, 1, strlen($content), 0, 0) . $content;
}
function flushLength($length) {
    return $length < 128 ? chr($length) : pack('N', $length | 0x80000000);
}
function flushRequest($socketPath, $script, $uri, $query) {
    $socket = stream_socket_client('unix://' . $socketPath, $errno, $error, 2);
    if (!$socket) { throw new RuntimeException($error); }
    stream_set_timeout($socket, 3);
    $params = '';
    foreach (array('SCRIPT_FILENAME' => $script, 'SCRIPT_NAME' => $uri,
                   'REQUEST_URI' => $uri, 'QUERY_STRING' => $query,
                   'REQUEST_METHOD' => 'GET', 'SERVER_PROTOCOL' => 'HTTP/1.1') as $key => $value) {
        $params .= flushLength(strlen($key)) . flushLength(strlen($value)) . $key . $value;
    }
    $bytes = flushRecord(1, pack('nCxxxxx', 1, 0)) . flushRecord(4, $params)
        . flushRecord(4, '') . flushRecord(5, '');
    while ($bytes !== '') {
        $written = fwrite($socket, $bytes);
        if (!$written) { throw new RuntimeException('FastCGI write failed'); }
        $bytes = (string) substr($bytes, $written);
    }
    return $socket;
}
function flushRead($socket, $length) {
    $bytes = '';
    while (strlen($bytes) < $length) {
        $part = fread($socket, $length - strlen($bytes));
        if ($part === false || $part === '') { throw new RuntimeException('Incomplete FastCGI response'); }
        $bytes .= $part;
    }
    return $bytes;
}
function flushResponse($socket) {
    $stdout = '';
    while (true) {
        $header = unpack('Cversion/Ctype/nrequest/nlength/Cpadding/Creserved', flushRead($socket, 8));
        $content = flushRead($socket, $header['length']);
        if ($header['padding']) { flushRead($socket, $header['padding']); }
        if ($header['type'] === 6) { $stdout .= $content; }
        if ($header['type'] === 7 && $content !== '') { throw new RuntimeException($content); }
        if ($header['type'] === 3) {
            $end = unpack('Napplication/Cprotocol', $content);
            if ($end['application'] || $end['protocol']) { throw new RuntimeException('FastCGI request failed'); }
            break;
        }
    }
    fclose($socket);
    return $stdout;
}
$dir = sys_get_temp_dir() . '/php-fpm-flush-' . getmypid();
mkdir($dir);
$config = "[global]\ndaemonize=no\nerror_log=$dir/error.log\nlog_level=warning\nlog_limit=4096\n[www]\n"
    . "listen=$dir/fpm.sock\npm=static\npm.max_children=1\n"
    . "user=www-data\ngroup=www-data\ncatch_workers_output=yes\ndecorate_workers_output=no\n";
file_put_contents($dir . '/fpm.conf', $config);
file_put_contents($dir . '/index.php', '<?php file_put_contents("php://stderr", file_get_contents('
    . var_export($dir . '/payload', true) . ')); echo "ordinary response";');
$process = proc_open('/usr/local/sbin/php-fpm -n -F -y ' . escapeshellarg($dir . '/fpm.conf'),
    array(0 => array('file', '/dev/null', 'r'), 1 => array('file', $dir . '/stdout', 'a'),
          2 => array('file', $dir . '/stderr', 'a')), $pipes);
if (!is_resource($process)) { throw new RuntimeException('Cannot start test FPM'); }
try {
    for ($i = 0; $i < 100; $i++) {
        if (file_exists($dir . '/fpm.sock')) { break; }
        usleep(20000);
    }
    if (!file_exists($dir . '/fpm.sock')) { throw new RuntimeException('Test FPM socket did not appear'); }
    $cases = array(
        array('ordinary newline', "ordinary line\nsecond line\n", "ordinary line\nsecond line\n"),
        array('partial marker mismatch', str_repeat('p', 1019) . "\0fscXtail\n", str_repeat('p', 1019) . "\0fscXtail\n")
    );
    foreach (array(1009, 1013) as $length) {
        $prefix = str_repeat('a', $length) . 'Quarkslab';
        $cases[] = array('published split ' . $length, $prefix . "\0fscf\0Quarkslab", $prefix . "\nQuarkslab\n");
    }
    $failures = array();
    foreach ($cases as $case) {
        $before = is_file($dir . '/error.log') ? strlen(file_get_contents($dir . '/error.log')) : 0;
        file_put_contents($dir . '/payload', $case[1]);
        $response = flushResponse(flushRequest($dir . '/fpm.sock', $dir . '/index.php', '/index.php', ''));
        if (substr($response, -strlen('ordinary response')) !== 'ordinary response') {
            throw new RuntimeException('Missing ordinary response for ' . $case[0]);
        }
        $actual = '';
        for ($i = 0; $i < 100; $i++) {
            $actual = substr(file_get_contents($dir . '/error.log'), $before);
            if (substr_count($actual, "\n") >= substr_count($case[2], "\n")) { break; }
            usleep(10000);
        }
        if ($actual !== $case[2]) {
            $failures[] = $case[0] . ': expected ' . strlen($case[2]) . ' log bytes, received '
                . strlen($actual) . '; actual tail hex=' . bin2hex(substr($actual, -24));
        } else {
            echo 'PASS FPM ' . $case[0] . " exact log bytes and ordinary response\n";
        }
    }
    if ($failures) { throw new RuntimeException(implode('; ', $failures)); }
} finally {
    proc_terminate($process);
    proc_close($process);
}