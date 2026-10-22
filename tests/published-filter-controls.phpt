--TEST--
Binary line separators and ordinary convert filter controls
--FILE--
<?php
function converted($filter, $input, $separator) {
    $stream = fopen('php://temp', 'w+');
    stream_filter_append($stream, $filter, STREAM_FILTER_WRITE, array('line-length' => 4, 'line-break-chars' => $separator));
    fwrite($stream, $input);
    rewind($stream);
    $output = stream_get_contents($stream);
    fclose($stream);
    return $output;
}
foreach (array("\0X", "X\0Y", "\r\n") as $separator) {
    $out = converted('convert.base64-encode', 'AAAAAA', $separator);
    var_dump($out === 'QUFB' . $separator . 'QUFB');
    $out = converted('convert.quoted-printable-encode', 'AAAAAAAA', $separator);
    var_dump(strpos($out, '=' . $separator) !== false);
    $out = converted('convert.quoted-printable-decode', 'AA=' . $separator . 'BB', $separator);
    var_dump($out === 'AABB');
}
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
