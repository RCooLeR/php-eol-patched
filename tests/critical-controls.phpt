--TEST--
Ordinary GD interpolation, PDO SQLite quotes and CP950 conversions
--FILE--
<?php
$image = imagecreatetruecolor(8, 8);
imagefill($image, 0, 0, imagecolorallocate($image, 12, 32, 56));
$scaled = imagescale($image, 4, 4, IMG_TRIANGLE);
var_dump(imagesx($scaled) === 4 && imagesy($scaled) === 4);
$pdo = new PDO('sqlite::memory:');
foreach (array('', 'ordinary', "O'Reilly") as $input) {
    $quoted = $pdo->quote($input);
    var_dump($pdo->query('SELECT ' . $quoted)->fetchColumn() === $input);
}
var_dump(mb_convert_encoding('ASCII', 'UTF-8', 'CP950') === 'ASCII');
var_dump(bin2hex(mb_convert_encoding("\xa4\x40", 'UTF-8', 'CP950')) === 'e4b880');
foreach (array("\xfa\x40", "\xfe\xfe", "\x8e\x40", "\xa0\xfe", "\x81\x40", "\x8d\xfe", "\xc6\xa1", "\xc6\xfe", "\xc7\x40", "\xc8\xfe") as $input) {
    $converted = mb_convert_encoding($input, 'UTF-8', 'CP950');
    if (mb_convert_encoding($converted, 'CP950', 'UTF-8') !== $input) die('PUA round-trip failed');
}
echo "PUA round-trips passed\n";
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
PUA round-trips passed
