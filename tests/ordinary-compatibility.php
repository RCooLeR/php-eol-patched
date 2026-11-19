<?php
/* Small valid inputs for archive, session and timezone compatibility. */
function ordinaryCheck($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$directory = sys_get_temp_dir() . '/php-ordinary-' . getmypid() . '-' . uniqid();
ordinaryCheck(mkdir($directory, 0700), 'Create ordinary test directory');
try {
    $archivePath = $directory . '/original.phar';
    $readPath = $directory . '/read.phar';
    $archive = new Phar($archivePath);
    $archive->startBuffering();
    $archive->setStub('<?php __HALT_COMPILER(); ?>');
    $archive['documents/hello.txt'] = "Ordinary signed archive\n";
    $archive['empty.txt'] = '';
    $archive->setSignatureAlgorithm(Phar::SHA256);
    $archive->stopBuffering();
    unset($archive);
    // A distinct pathname forces a fresh parse of the saved archive.
    ordinaryCheck(copy($archivePath, $readPath), 'Copy signed archive');
    $archive = new Phar($readPath);
    $signature = $archive->getSignature();
    ordinaryCheck($signature['hash_type'] === 'SHA-256', 'Signed archive algorithm');
    ordinaryCheck($archive['documents/hello.txt']->getContent() === "Ordinary signed archive\n",
        'Signed archive nested file');
    ordinaryCheck(file_get_contents('phar://' . $readPath . '/empty.txt') === '',
        'Signed archive empty file');
    unset($archive);

    $key = openssl_pkey_new(array('private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA));
    ordinaryCheck($key !== false, 'Create ephemeral archive signing key');
    ordinaryCheck(openssl_pkey_export($key, $privateKey), 'Export ephemeral archive signing key');
    $keyDetails = openssl_pkey_get_details($key);
    ordinaryCheck($keyDetails !== false, 'Read archive verification key');
    $signedPath = $directory . '/openssl.phar';
    $signedReadPath = $directory . '/openssl-read.phar';
    $archive = new Phar($signedPath);
    $archive->startBuffering();
    $archive->setStub('<?php __HALT_COMPILER(); ?>');
    $archive['message.txt'] = "Ordinary OpenSSL signed archive\n";
    $archive->setSignatureAlgorithm(Phar::OPENSSL, $privateKey);
    $archive->stopBuffering();
    unset($archive, $privateKey, $key);
    ordinaryCheck(copy($signedPath, $signedReadPath), 'Copy OpenSSL signed archive');
    ordinaryCheck(file_put_contents($signedReadPath . '.pubkey', $keyDetails['key']) !== false,
        'Save matching archive public key');
    unset($keyDetails);
    $archive = new Phar($signedReadPath);
    $signature = $archive->getSignature();
    ordinaryCheck($signature['hash_type'] === 'OpenSSL', 'OpenSSL archive signature verified');
    ordinaryCheck($archive['message.txt']->getContent() === "Ordinary OpenSSL signed archive\n",
        'OpenSSL archive file contents');
    unset($archive);

    ini_set('session.save_handler', 'files');
    ini_set('session.save_path', $directory);
    ini_set('session.use_cookies', '0');
    ini_set('session.cache_limiter', '');
    $sessionId = 'ordinary' . getmypid();
    session_id($sessionId);
    ordinaryCheck(session_start(), 'Start ordinary session');
    $_SESSION = array('counter' => 1, 'cart' => array('book', 'pen'));
    session_write_close();
    ordinaryCheck(session_status() === PHP_SESSION_NONE, 'Save ordinary session');
    $_SESSION = array();
    session_id($sessionId);
    ordinaryCheck(session_start(), 'Reopen ordinary session');
    ordinaryCheck($_SESSION === array('counter' => 1, 'cart' => array('book', 'pen')),
        'Session values persist');
    $_SESSION = array('counter' => 2);
    session_write_close();
    ordinaryCheck(session_status() === PHP_SESSION_NONE, 'Save shorter ordinary session');
    $_SESSION = array();
    session_id($sessionId);
    ordinaryCheck(session_start(), 'Reopen shorter ordinary session');
    ordinaryCheck($_SESSION === array('counter' => 2), 'Shorter session replaces earlier values');
    ordinaryCheck(session_destroy(), 'Destroy ordinary session');

    $timezone = new DateTimeZone('Europe/London');
    $winter = new DateTime('2026-01-15 12:00:00', $timezone);
    $summer = new DateTime('2026-07-15 12:00:00', $timezone);
    ordinaryCheck($winter->format('P T') === '+00:00 GMT', 'Winter timezone offset');
    ordinaryCheck($summer->format('P T') === '+01:00 BST', 'Summer timezone offset');
    $summer->setTimezone(new DateTimeZone('UTC'));
    ordinaryCheck($summer->format('Y-m-d H:i:s') === '2026-07-15 11:00:00',
        'Timezone conversion preserves instant');

    $listener = stream_socket_server('tcp://127.0.0.1:0', $errorNumber, $errorMessage);
    ordinaryCheck($listener !== false, 'Listen for ordinary HTTP response');
    $address = stream_socket_get_name($listener, false);
    $child = pcntl_fork();
    ordinaryCheck($child !== -1, 'Start ordinary HTTP response fixture');
    if ($child === 0) {
        $connection = stream_socket_accept($listener, 5);
        if ($connection === false) {
            exit(2);
        }
        stream_set_timeout($connection, 5);
        while (($line = fgets($connection)) !== false && $line !== "\r\n") {
        }
        $response = "HTTP/1.0 200 OK\r\nX-Ordinary: first\r\n second\r\n\tthird\r\n"
            . "Content-Length: 5\r\nConnection: close\r\n\r\nhello";
        $written = fwrite($connection, $response);
        fclose($connection);
        fclose($listener);
        exit($written === strlen($response) ? 0 : 2);
    }
    fclose($listener);
    $context = stream_context_create(array('http' => array('timeout' => 5)));
    $body = file_get_contents('http://' . $address . '/', false, $context);
    pcntl_waitpid($child, $status);
    ordinaryCheck(pcntl_wifexited($status) && pcntl_wexitstatus($status) === 0,
        'Ordinary HTTP response fixture completes');
    ordinaryCheck($body === 'hello', 'Folded response retains body');
    ordinaryCheck(in_array('X-Ordinary: first second third', $http_response_header, true),
        'Folded response retains all header text');
} finally {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    foreach (glob($directory . '/*') as $file) {
        unlink($file);
    }
    rmdir($directory);
}

echo 'PASS PHP ', PHP_VERSION, " ordinary archive, session, timezone and HTTP compatibility\n";
