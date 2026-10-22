<?php
/* Ordinary tiny inputs with valid large length parameters; no callbacks. */
function expect_same($actual, $expected, $name) {
    if ($actual !== $expected) { fwrite(STDERR, $name . " failed\n"); exit(1); }
}
expect_same(mb_strcut('abc', 0, 104857600, 'UTF-7'), 'abc', 'UTF-7 cut capacity');
expect_same(mb_str_split('abc', 104857600, 'UTF-7'), array('abc'), 'UTF-7 split capacity');
foreach (array('UTF-8', 'ASCII', 'UTF-16LE', 'UTF-7') as $encoding) {
    $input = mb_convert_encoding('abcdef', $encoding, 'UTF-8');
    expect_same(implode('', mb_str_split($input, 2, $encoding)), $input, $encoding . ' ordinary split');
    expect_same(mb_strcut($input, 0, 1000, $encoding), $input, $encoding . ' ordinary cut');
}
$fiber = new Fiber(function () { Fiber::suspend('suspended'); return 'returned'; });
expect_same($fiber->start(), 'suspended', 'ordinary fiber suspend');
$fiber->resume();
expect_same($fiber->getReturn(), 'returned', 'ordinary fiber return');
enum OrdinaryEnum: string { case Value = 'value'; }
expect_same(unserialize(serialize(OrdinaryEnum::Value)), OrdinaryEnum::Value, 'ordinary enum serialization');
echo "PASS mbstring tiny-input capacity controls, ordinary encoding/fiber/enum compatibility\n";
