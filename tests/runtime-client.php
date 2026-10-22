<?php
/* Forward a FastCGI request over loopback without optional PHP extensions. */
function read_exact($socket, $length) {
    $result = '';
    while (strlen($result) < $length) {
        $part = fread($socket, $length - strlen($result));
        if ($part === false || $part === '') {
            throw new RuntimeException('Truncated FastCGI response');
        }
        $result .= $part;
    }
    return $result;
}
try {
    $socket = stream_socket_client('tcp://127.0.0.1:9000', $number, $message, 2);
    if ($socket === false) throw new RuntimeException($message);
    stream_set_timeout($socket, 10);
    $request = file_get_contents('php://stdin');
    while ($request !== '') {
        $written = fwrite($socket, $request);
        if ($written === false || $written === 0) throw new RuntimeException('FastCGI write failed');
        $request = substr($request, $written);
    }
    do {
        $header = read_exact($socket, 8);
        $record = unpack('Cversion/Ctype/nrequest/nlength/Cpadding/Creserved', $header);
        echo $header, read_exact($socket, $record['length'] + $record['padding']);
    } while ($record['type'] !== 3);
    fclose($socket);
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
}
