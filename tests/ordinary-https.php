<?php
/* A mixed-case HTTPS URL must connect to a trusted TLS-only local endpoint. */
function httpsCheck($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$directory = sys_get_temp_dir() . '/php-https-' . getmypid() . '-' . uniqid();
httpsCheck(mkdir($directory, 0700), 'Create HTTPS test directory');
try {
    $options = array('private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA,
        'digest_alg' => 'sha256');
    $key = openssl_pkey_new($options);
    httpsCheck($key !== false, 'Create ephemeral HTTPS key');
    $request = openssl_csr_new(array('commonName' => 'localhost'), $key, $options);
    httpsCheck($request !== false, 'Create local HTTPS certificate request');
    $certificate = openssl_csr_sign($request, null, $key, 1, $options);
    httpsCheck($certificate !== false, 'Sign local HTTPS certificate');
    httpsCheck(openssl_x509_export($certificate, $certificateText), 'Export HTTPS certificate');
    httpsCheck(openssl_pkey_export($key, $privateKey), 'Export ephemeral HTTPS key');
    $certificatePath = $directory . '/certificate.pem';
    $serverPath = $directory . '/server.pem';
    httpsCheck(file_put_contents($certificatePath, $certificateText) !== false,
        'Save HTTPS trust anchor');
    httpsCheck(file_put_contents($serverPath, $certificateText . $privateKey) !== false,
        'Save temporary TLS server identity');
    chmod($serverPath, 0600);
    unset($key, $privateKey, $request, $certificate);

    $serverContext = stream_context_create(array('ssl' => array(
        'local_cert' => $serverPath, 'verify_peer' => false)));
    $listener = stream_socket_server('tls://127.0.0.1:0', $errorNumber, $errorMessage,
        STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $serverContext);
    httpsCheck($listener !== false, 'Listen for ordinary TLS request');
    $address = stream_socket_get_name($listener, false);
    $child = pcntl_fork();
    httpsCheck($child !== -1, 'Start ordinary TLS fixture');
    if ($child === 0) {
        $connection = stream_socket_accept($listener, 5);
        if ($connection === false) {
            exit(2);
        }
        stream_set_timeout($connection, 5);
        $requestLine = fgets($connection);
        while (($line = fgets($connection)) !== false && $line !== "\r\n") {
        }
        $response = "HTTP/1.0 200 OK\r\nContent-Length: 10\r\nConnection: close\r\n\r\nsecure-ok\n";
        $written = fwrite($connection, $response);
        fclose($connection);
        fclose($listener);
        exit($requestLine === "GET /ordinary HTTP/1.0\r\n"
            && $written === strlen($response) ? 0 : 2);
    }
    fclose($listener);
    $clientContext = stream_context_create(array(
        'http' => array('timeout' => 5, 'protocol_version' => 1.0),
        'ssl' => array('cafile' => $certificatePath, 'verify_peer' => true,
            'verify_peer_name' => true, 'peer_name' => 'localhost')));
    $body = file_get_contents('HtTpS://' . $address . '/ordinary', false, $clientContext);
    pcntl_waitpid($child, $status);
    httpsCheck(pcntl_wifexited($status) && pcntl_wexitstatus($status) === 0,
        'Ordinary TLS fixture completes');
    httpsCheck($body === "secure-ok\n", 'Mixed-case HTTPS returns the complete TLS response');
} finally {
    foreach (glob($directory . '/*') as $file) {
        unlink($file);
    }
    rmdir($directory);
}

echo 'PASS PHP ', PHP_VERSION, " ordinary mixed-case HTTPS compatibility\n";
