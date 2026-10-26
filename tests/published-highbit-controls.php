<?php
$checks = 0;
function verify($condition, $label) {
    global $checks;
    $checks++;
    if (!$condition) {
        fwrite(STDERR, "FAIL: $label\n");
        exit(1);
    }
}
verify(urldecode('A%20B+%2f%00') === "A B /\0", 'ordinary urldecode');
verify(rawurldecode('A%20B+%2f%00') === "A B+/\0", 'ordinary rawurldecode');
verify(urldecode('%41%61%39') === 'Aa9', 'valid hex pairs');
verify(quoted_printable_decode('A=20B=3D') === 'A B=', 'ordinary quoted printable');
verify(strnatcmp('item2', 'item10') < 0, 'ordinary natural order');
verify(strnatcasecmp('ITEM2', 'item2') === 0, 'ordinary case insensitive order');
for ($byte = 128; $byte <= 255; $byte++) {
    foreach (array('%' . chr($byte) . '0', '%0' . chr($byte), '%' . chr($byte)) as $input) {
        verify(urldecode($input) === $input, 'invalid high-bit URL hex');
        verify(rawurldecode($input) === $input, 'invalid high-bit raw URL hex');
    }
    $query = 'v=%' . chr($byte) . '0';
    parse_str($query, $parsed);
    verify($parsed['v'] === '%' . chr($byte) . '0', 'request decoder high-bit input');
}
echo "PASS: $checks high-bit and ordinary controls\n";
