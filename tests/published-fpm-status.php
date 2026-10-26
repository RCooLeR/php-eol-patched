<?php
/* GHSA-7qg2-v9fj-4mwv's published concurrent request/status case. */
function statusRecord($type, $content) {
    return pack('CCnnCC', 1, $type, 1, strlen($content), 0, 0) . $content;
}
function statusLength($length) {
    return $length < 128 ? chr($length) : pack('N', $length | 0x80000000);
}
function statusRequest($socketPath, $script, $uri, $query) {
    $socket = stream_socket_client('unix://' . $socketPath, $errno, $error, 2);
    if (!$socket) { throw new RuntimeException($error); }
    stream_set_timeout($socket, 3);
    $params = '';
    foreach (array('SCRIPT_FILENAME' => $script, 'SCRIPT_NAME' => $uri,
                   'REQUEST_URI' => $uri, 'QUERY_STRING' => $query,
                   'REQUEST_METHOD' => 'GET', 'SERVER_PROTOCOL' => 'HTTP/1.1') as $key => $value) {
        $params .= statusLength(strlen($key)) . statusLength(strlen($value)) . $key . $value;
    }
    $bytes = statusRecord(1, pack('nCxxxxx', 1, 0)) . statusRecord(4, $params)
        . statusRecord(4, '') . statusRecord(5, '');
    while ($bytes !== '') {
        $written = fwrite($socket, $bytes);
        if (!$written) { throw new RuntimeException('FastCGI write failed'); }
        $bytes = (string) substr($bytes, $written);
    }
    return $socket;
}
function statusRead($socket, $length) {
    $bytes = '';
    while (strlen($bytes) < $length) {
        $part = fread($socket, $length - strlen($bytes));
        if ($part === false || $part === '') { throw new RuntimeException('Incomplete FastCGI response'); }
        $bytes .= $part;
    }
    return $bytes;
}
function statusResponse($socket) {
    $stdout = '';
    while (true) {
        $header = unpack('Cversion/Ctype/nrequest/nlength/Cpadding/Creserved', statusRead($socket, 8));
        $content = statusRead($socket, $header['length']);
        if ($header['padding']) { statusRead($socket, $header['padding']); }
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
$dir = sys_get_temp_dir() . '/php-fpm-status-' . getmypid();
mkdir($dir);
$config = "[global]\ndaemonize=no\nerror_log=$dir/error.log\n[www]\n"
    . "listen=$dir/fpm.sock\npm=static\npm.max_children=2\npm.status_path=/status\n"
    . "user=www-data\ngroup=www-data\ncatch_workers_output=yes\n";
file_put_contents($dir . '/fpm.conf', $config);
file_put_contents($dir . '/index.php', '<?php file_put_contents(' . var_export($dir . '/busy', true)
    . ', "ready"); usleep(1000000); echo "ordinary response";');
$process = proc_open('/usr/local/sbin/php-fpm -n -F -y ' . escapeshellarg($dir . '/fpm.conf'),
    array(0 => array('file', '/dev/null', 'r'), 1 => array('file', $dir . '/stdout', 'a'),
          2 => array('file', $dir . '/stderr', 'a')), $pipes);
if (!is_resource($process)) { throw new RuntimeException('Cannot start test FPM'); }
try {
    for ($i = 0; $i < 100; $i++) {
        if (file_exists($dir . '/fpm.sock')) { break; }
        usleep(20000);
    }
    foreach (array('html', 'xml') as $format) {
        if (is_file($dir . '/busy')) { unlink($dir . '/busy'); }
        $request = statusRequest($dir . '/fpm.sock', $dir . '/index.php',
            '/<script>alert(1)</script>', '<script>alert(2)</script>');
        for ($i = 0; $i < 100; $i++) {
            if (is_file($dir . '/busy')) { break; }
            usleep(10000);
        }
        if (!is_file($dir . '/busy')) { throw new RuntimeException('Test request did not start'); }
        $status = statusResponse(statusRequest($dir . '/fpm.sock', $dir . '/index.php', '/status', 'full&' . $format));
        $ordinary = statusResponse($request);
        if (strpos($status, '/&lt;script&gt;alert(1)&lt;/script&gt;?&lt;script&gt;alert(2)&lt;/script&gt;') === false
            || strpos($ordinary, 'ordinary response') === false) {
            throw new RuntimeException('FPM full ' . $format . ' status did not escape the request URI');
        }
        echo "PASS FPM status " . $format . " escapes request URI\n";
    }
} finally {
    proc_terminate($process);
    proc_close($process);
}
