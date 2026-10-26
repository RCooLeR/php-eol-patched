--TEST--
Ordinary array merging and quoted-printable decoding after published fixes
--FILE--
<?php
var_dump(array_merge(array('x' => 1, 9), array('x' => 2, 10)) === array('x' => 2, 9, 10));
var_dump(array_merge_recursive(array('x' => array(1)), array('x' => array(2))) === array('x' => array(1, 2)));
var_dump(array_merge(array(), array()) === array());
foreach (array('hello=20world' => 'hello world', "a=\r\nb" => 'ab', "a=\nb" => 'ab') as $input => $expected) {
    $url = 'php://filter/convert.quoted-printable-decode/resource=data:,' . urlencode($input);
    var_dump(file_get_contents($url) === $expected);
}
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
