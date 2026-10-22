<?php
/* Minimal FastCGI responder client; fixed to the isolated pool's loopback port. */
function fcgi_record($type, $content)
{
    $length = strlen($content);
    return pack('CCnnCC', 1, $type, 1, $length, 0, 0) . $content;
}
function fcgi_length($length)
{
    return $length < 128 ? chr($length) : pack('N', $length | 0x80000000);
}
function fcgi_read_exact($socket, $length)
{
    $result = '';
    while (strlen($result) < $length) {
        $chunk = fread($socket, $length - strlen($result));
        if ($chunk === false || $chunk === '') {
            throw new RuntimeException('FastCGI stream ended before END_REQUEST');
        }
        $result .= $chunk;
    }
    return $result;
}
function fcgi_write_all($socket, $content)
{
    while ($content !== '') {
        $length = fwrite($socket, $content);
        if ($length === false || $length === 0) {
            throw new RuntimeException('FastCGI write failed');
        }
        $content = (string) substr($content, $length);
    }
}

try {
    $request = json_decode(file_get_contents('php://stdin'), true);
    if (!is_array($request) || !isset($request['params']) || !is_array($request['params'])) {
        throw new RuntimeException('Expected a JSON request containing params');
    }
    $socket = stream_socket_client('tcp://127.0.0.1:19000', $errorNumber, $errorMessage, 2);
    if ($socket === false) {
        throw new RuntimeException('Cannot connect to isolated test FPM: ' . $errorMessage);
    }
    stream_set_timeout($socket, 12);
    fcgi_write_all($socket, fcgi_record(1, pack('nCxxxxx', 1, 0)));
    $params = '';
    foreach ($request['params'] as $name => $value) {
        $value = (string) $value;
        $params .= fcgi_length(strlen($name)) . fcgi_length(strlen($value)) . $name . $value;
    }
    fcgi_write_all($socket, fcgi_record(4, $params) . fcgi_record(4, ''));
    $body = isset($request['body_base64']) ? base64_decode($request['body_base64'], true) : '';
    if ($body === false) {
        throw new RuntimeException('Invalid body encoding');
    }
    for ($offset = 0; $offset < strlen($body); $offset += 32768) {
        fcgi_write_all($socket, fcgi_record(5, substr($body, $offset, 32768)));
    }
    fcgi_write_all($socket, fcgi_record(5, ''));
    $stdout = '';
    $stderr = '';
    while (true) {
        $header = unpack('Cversion/Ctype/nrequest/nlength/Cpadding/Creserved', fcgi_read_exact($socket, 8));
        if ($header['version'] !== 1 || $header['request'] !== 1) {
            throw new RuntimeException('Unexpected FastCGI header');
        }
        $content = fcgi_read_exact($socket, $header['length']);
        if ($header['padding']) {
            fcgi_read_exact($socket, $header['padding']);
        }
        if ($header['type'] === 6) {
            $stdout .= $content;
        } elseif ($header['type'] === 7) {
            $stderr .= $content;
        } elseif ($header['type'] === 3) {
            $end = unpack('Napplication_status/Cprotocol_status', $content);
            break;
        }
    }
    fclose($socket);
    echo json_encode(array('stdout' => $stdout, 'stderr' => $stderr, 'end' => $end)) . "\n";
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(2);
}
