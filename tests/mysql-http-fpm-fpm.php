<?php
// Bounded FPM compatibility controls for the CVE-2021-21703 scoreboard backport.
// Run as a non-root user in a disposable, network-disabled container.
function scoreboard_assert($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function scoreboard_pass($name)
{
    echo 'PASS ' . $name . "\n";
}

function scoreboard_record($type, $content)
{
    return pack('CCnnCC', 1, $type, 1, strlen($content), 0, 0) . $content;
}

function scoreboard_length($length)
{
    return $length < 128 ? chr($length) : pack('N', $length | 0x80000000);
}

function scoreboard_read($socket, $length)
{
    $result = '';
    while (strlen($result) < $length) {
        $chunk = fread($socket, $length - strlen($result));
        scoreboard_assert($chunk !== false && $chunk !== '', 'Incomplete FastCGI response');
        $result .= $chunk;
    }
    return $result;
}

function scoreboard_send($dir, $pool, $status = false, $slow = false)
{
    $socket = stream_socket_client('unix://' . $dir . '/' . $pool . '.sock', $errno, $error, 2);
    scoreboard_assert(is_resource($socket), 'FPM connection failed: ' . $error);
    stream_set_timeout($socket, 3);
    $params = '';
    foreach (array(
        'SCRIPT_FILENAME' => $dir . '/index.php',
        'SCRIPT_NAME' => $status ? '/status' : '/index.php',
        'REQUEST_URI' => $status ? '/status?json&full' : '/index.php',
        'QUERY_STRING' => $status ? 'json&full' : ($slow ? 'slow=' . $pool : ''),
        'REQUEST_METHOD' => 'GET',
        'SERVER_PROTOCOL' => 'HTTP/1.1',
        'SERVER_NAME' => 'localhost',
        'SERVER_PORT' => '80',
        'REMOTE_ADDR' => '127.0.0.1'
    ) as $key => $value) {
        $params .= scoreboard_length(strlen($key)) . scoreboard_length(strlen($value)) . $key . $value;
    }
    $wire = scoreboard_record(1, pack('nCxxxxx', 1, 0)) . scoreboard_record(4, $params)
        . scoreboard_record(4, '') . scoreboard_record(5, '');
    while ($wire !== '') {
        $written = fwrite($socket, $wire);
        scoreboard_assert($written !== false && $written > 0, 'FastCGI write failed');
        $wire = (string) substr($wire, $written);
    }
    return $socket;
}

function scoreboard_receive($socket)
{
    $stdout = '';
    $stderr = '';
    while (true) {
        $header = unpack('Cversion/Ctype/nrequest/nlength/Cpadding/Creserved', scoreboard_read($socket, 8));
        scoreboard_assert($header['version'] === 1 && $header['request'] === 1, 'Invalid FastCGI header');
        $content = scoreboard_read($socket, $header['length']);
        if ($header['padding']) {
            scoreboard_read($socket, $header['padding']);
        }
        if ($header['type'] === 6) {
            $stdout .= $content;
        } elseif ($header['type'] === 7) {
            $stderr .= $content;
        } elseif ($header['type'] === 3) {
            $end = unpack('Napplication/Cprotocol', $content);
            scoreboard_assert($end['application'] === 0 && $end['protocol'] === 0, 'FPM request failed');
            break;
        }
    }
    fclose($socket);
    scoreboard_assert($stderr === '', 'FPM request stderr: ' . $stderr);
    $parts = explode("\r\n\r\n", $stdout, 2);
    scoreboard_assert(count($parts) === 2, 'Missing response body');
    $data = json_decode($parts[1], true);
    scoreboard_assert(is_array($data), 'Invalid JSON response: ' . $stdout);
    return $data;
}

function scoreboard_request($dir, $pool, $status = false)
{
    return scoreboard_receive(scoreboard_send($dir, $pool, $status));
}

function scoreboard_check_status($dir, $pool)
{
    // A slot is reserved before its child starts; aggregate counters and full
    // records are sampled separately. Require two settled snapshots, including
    // matching positive PIDs, instead of accepting an in-progress fork/reap.
    $previous = null;
    $transitions = array();
    $deadline = microtime(true) + 5;
    for ($attempt = 0; $attempt < 40 && microtime(true) < $deadline; $attempt++) {
        $data = scoreboard_request($dir, $pool, true);
        scoreboard_assert($data['pool'] === $pool && $data['process manager'] === $pool, 'Incorrect pool metadata');
        scoreboard_assert($data['accepted conn'] >= 1, 'Missing request accounting');
        scoreboard_assert($data['total processes'] >= 1 && $data['total processes'] <= 2, 'Invalid worker count');
        scoreboard_assert(isset($data['processes']) && is_array($data['processes']) && count($data['processes']) <= 2, 'Invalid full status');
        $pids = array();
        $consistent = count($data['processes']) === $data['total processes'];
        foreach ($data['processes'] as $process) {
            scoreboard_assert($process['pid'] >= 0 && $process['requests'] >= 0, 'Invalid process accounting');
            if ($process['pid'] === 0 || ($process['state'] === '(null)' && $process['requests'] === 0)) {
                $consistent = false;
                continue;
            }
            scoreboard_assert(in_array($process['state'], array('Idle', 'Reading headers', 'Getting request informations', 'Running', 'Ending', 'Finishing'), true), 'Invalid process state: ' . json_encode($data));
            scoreboard_assert(isset($process['request uri']) && isset($process['script']), 'Missing process details');
            $pids[] = $process['pid'];
        }
        sort($pids);
        scoreboard_assert(count(array_unique($pids)) === count($pids), 'Duplicate worker PID');
        if ($consistent && $previous === $pids) {
            if ($transitions) {
                echo 'INFO ' . $pool . '-status-settled ' . json_encode($transitions) . "\n";
            }
            return;
        }
        if (!$consistent) {
            $transitions[] = array('total' => $data['total processes'], 'slots' => count($data['processes']), 'pids' => $pids);
        }
        $previous = $consistent ? $pids : null;
        usleep(50000);
    }
    throw new RuntimeException('Full status did not settle: ' . json_encode($data));
}

function scoreboard_wait_ready($log, $count)
{
    for ($i = 0; $i < 250; $i++) {
        $contents = @file_get_contents($log);
        if ($contents !== false && substr_count($contents, 'ready to handle connections') >= $count) {
            return;
        }
        usleep(20000);
    }
    throw new RuntimeException('FPM did not become ready: ' . @file_get_contents($log));
}

$dir = sys_get_temp_dir() . '/php-fpm-scoreboard-' . getmypid();
mkdir($dir, 0700);
file_put_contents($dir . '/index.php', '<?php if (isset($_GET["slow"])) { file_put_contents(__DIR__ . "/entered-" . $_GET["slow"], "ready"); usleep(500000); } echo json_encode(array("marker" => "FPM_SCOREBOARD_OK", "pid" => getmypid()));');
$pools = array('static', 'dynamic', 'ondemand');
$config = "[global]\ndaemonize = no\nerror_log = $dir/fpm.log\nlog_level = notice\n";
foreach ($pools as $pool) {
    $config .= "[$pool]\nlisten = $dir/$pool.sock\nuser = www-data\ngroup = www-data\npm = $pool\npm.max_children = 2\npm.max_requests = 4\npm.status_path = /status\ncatch_workers_output = yes\n";
    if ($pool === 'dynamic') {
        $config .= "pm.start_servers = 2\npm.min_spare_servers = 2\npm.max_spare_servers = 2\n";
    } elseif ($pool === 'ondemand') {
        $config .= "pm.process_idle_timeout = 10s\n";
    }
}
file_put_contents($dir . '/fpm.conf', $config);
$jsonExtension = getenv('PHP_TEST_JSON_EXTENSION');
$jsonArgument = $jsonExtension ? ' -d ' . escapeshellarg('extension=' . $jsonExtension) : '';
$process = proc_open('exec /usr/local/sbin/php-fpm -n' . $jsonArgument . ' -F -y ' . escapeshellarg($dir . '/fpm.conf'), array(
    0 => array('file', '/dev/null', 'r'),
    1 => array('file', $dir . '/stdout', 'a'),
    2 => array('file', $dir . '/stderr', 'a')
), $pipes);
scoreboard_assert(is_resource($process), 'Cannot start FPM');
$failed = false;
$stopped = false;
try {
    scoreboard_wait_ready($dir . '/fpm.log', 1);
    scoreboard_pass('scoreboard-startup');
    $seen = array();
    foreach ($pools as $pool) {
        $first = scoreboard_send($dir, $pool, false, true);
        // On-demand pools must see the first worker busy before the second connection.
        for ($i = 0; $i < 100 && !is_file($dir . '/entered-' . $pool); $i++) {
            usleep(10000);
            clearstatcache();
        }
        scoreboard_assert(is_file($dir . '/entered-' . $pool), 'First worker did not enter the request');
        $second = scoreboard_send($dir, $pool, false, true);
        $one = scoreboard_receive($first);
        $two = scoreboard_receive($second);
        scoreboard_assert($one['marker'] === 'FPM_SCOREBOARD_OK' && $two['marker'] === 'FPM_SCOREBOARD_OK', 'Ordinary request failed');
        scoreboard_assert($one['pid'] !== $two['pid'], 'Concurrent requests did not use two workers');
        $seen[$pool] = array($one['pid'] => true, $two['pid'] => true);
        scoreboard_pass($pool . '-two-workers');
        scoreboard_check_status($dir, $pool);
        scoreboard_pass($pool . '-full-status');
        for ($i = 0; $i < 12; $i++) {
            $data = scoreboard_request($dir, $pool);
            scoreboard_assert($data['marker'] === 'FPM_SCOREBOARD_OK', 'Recycled worker request failed');
            $seen[$pool][$data['pid']] = true;
        }
        scoreboard_assert(count($seen[$pool]) > 2, 'Workers did not recycle at pm.max_requests');
        scoreboard_check_status($dir, $pool);
        scoreboard_pass($pool . '-recycle-and-status');
    }
    scoreboard_assert(proc_terminate($process, 12), 'Cannot send SIGUSR2');
    scoreboard_wait_ready($dir . '/fpm.log', 2);
    scoreboard_pass('scoreboard-graceful-reload');
    foreach ($pools as $pool) {
        $data = scoreboard_request($dir, $pool);
        scoreboard_assert($data['marker'] === 'FPM_SCOREBOARD_OK' && !isset($seen[$pool][$data['pid']]), 'Reload did not replace workers');
        scoreboard_check_status($dir, $pool);
        scoreboard_pass($pool . '-request-and-status-after-reload');
    }
    scoreboard_assert(proc_terminate($process, 3), 'Cannot send SIGQUIT');
    for ($i = 0; $i < 250; $i++) {
        $state = proc_get_status($process);
        if (!$state['running']) {
            $stopped = true;
            break;
        }
        usleep(20000);
    }
    scoreboard_assert($stopped && $state['exitcode'] === 0, 'FPM did not shut down cleanly');
    $log = file_get_contents($dir . '/fpm.log');
    scoreboard_assert(!preg_match('/SIGSEGV|SIGBUS|no free scoreboard slot|scoreboard is NULL/i', $log), 'Scoreboard lifecycle error: ' . $log);
    scoreboard_pass('scoreboard-graceful-shutdown');
} catch (Throwable $error) {
    $failed = true;
    fwrite(STDERR, 'FAIL ' . $error->getMessage() . "\n" . @file_get_contents($dir . '/fpm.log') . @file_get_contents($dir . '/stderr'));
} finally {
    if (!$stopped) {
        proc_terminate($process, 9);
    }
    proc_close($process);
}
exit($failed ? 1 : 0);
