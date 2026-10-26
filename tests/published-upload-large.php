<?php
/* CVE-2019-11048: stream a large form field after an uploaded file. */
if (PHP_SAPI === 'fpm-fcgi') {
    header('Content-Type: application/json');
    echo json_encode(array(
        'length' => strlen($_POST['field']),
        'first' => substr($_POST['field'], 0, 1),
        'last' => substr($_POST['field'], -1),
        'upload' => file_get_contents($_FILES['file']['tmp_name']),
        'error' => $_FILES['file']['error'],
    ));
    exit;
}

function upload_write($socket, $content)
{
    while ($content !== '') {
        $written = fwrite($socket, $content);
        if (!$written) throw new RuntimeException('FastCGI write failed');
        $content = (string) substr($content, $written);
    }
}

function upload_record($socket, $type, $content)
{
    upload_write($socket, pack('CCnnCC', 1, $type, 1, strlen($content), 0, 0) . $content);
}

function upload_read($socket, $length)
{
    $result = '';
    while (strlen($result) < $length) {
        $chunk = fread($socket, $length - strlen($result));
        if ($chunk === false || $chunk === '') throw new RuntimeException('FastCGI response ended early');
        $result .= $chunk;
    }
    return $result;
}

function upload_parameter_length($length)
{
    return $length < 128 ? chr($length) : pack('N', $length | 0x80000000);
}

try {
    foreach (array(64, 2147483649) as $length) {
        $prefix = "--large-upload\r\nContent-Disposition: form-data; name=\"file\"; filename=\"control.txt\"\r\n\r\nupload-control\r\n";
        $prefix .= "--large-upload\r\nContent-Disposition: form-data; name=\"field\"\r\n\r\n";
        $suffix = "\r\n--large-upload--\r\n";
        $socket = stream_socket_client('tcp://127.0.0.1:19048', $errno, $error, 5);
        if (!$socket) throw new RuntimeException($error);
        stream_set_timeout($socket, 180);
        upload_record($socket, 1, pack('nCxxxxx', 1, 0));
        $params = array(
            'REQUEST_METHOD' => 'POST',
            'SCRIPT_FILENAME' => __FILE__,
            'SCRIPT_NAME' => '/published-upload-large.php',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'CONTENT_TYPE' => 'multipart/form-data; boundary=large-upload',
            'CONTENT_LENGTH' => (string) (strlen($prefix) + $length + strlen($suffix)),
        );
        $encoded = '';
        foreach ($params as $name => $value) {
            $encoded .= upload_parameter_length(strlen($name)) . upload_parameter_length(strlen($value)) . $name . $value;
        }
        upload_record($socket, 4, $encoded);
        upload_record($socket, 4, '');
        upload_record($socket, 5, $prefix);
        $chunk = str_repeat('a', 32768);
        for ($remaining = $length; $remaining > 0; $remaining -= $size) {
            $size = min($remaining, strlen($chunk));
            upload_record($socket, 5, substr($chunk, 0, $size));
        }
        upload_record($socket, 5, $suffix);
        upload_record($socket, 5, '');
        $stdout = $stderr = '';
        while (true) {
            $header = unpack('Cversion/Ctype/nrequest/nlength/Cpadding/Creserved', upload_read($socket, 8));
            if ($header['version'] !== 1 || $header['request'] !== 1) throw new RuntimeException('Unexpected FastCGI response');
            $content = upload_read($socket, $header['length']);
            if ($header['padding']) upload_read($socket, $header['padding']);
            if ($header['type'] === 6) $stdout .= $content;
            if ($header['type'] === 7) $stderr .= $content;
            if ($header['type'] === 3) {
                $end = unpack('Napplication/Cprotocol', $content);
                if ($end['application'] || $end['protocol']) throw new RuntimeException('FastCGI request failed');
                break;
            }
        }
        fclose($socket);
        $parts = explode("\r\n\r\n", $stdout, 2);
        $result = count($parts) === 2 ? json_decode($parts[1], true) : null;
        $expected = array('length' => $length, 'first' => 'a', 'last' => 'a', 'upload' => 'upload-control', 'error' => 0);
        if ($result !== $expected || $stderr !== '') throw new RuntimeException('Unexpected upload result: ' . $stdout . $stderr);
        for ($i = 0; $i < 50 && glob($argv[1] . '/*'); $i++) usleep(10000);
        if (glob($argv[1] . '/*')) throw new RuntimeException('Uploaded temporary file was not removed');
        echo 'PASS upload field length ', $length, ' and temporary-file cleanup', PHP_EOL;
    }
} catch (Exception $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
