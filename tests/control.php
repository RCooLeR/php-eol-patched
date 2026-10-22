<?php
/* Normal behavior through parser/array/error-handler paths touched by the fix. */
function check($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(2);
    }
}

parse_str('a[x]=one&a[y]=two&single=value&empty=&list[]=a&list[]=b', $parsed);
check($parsed === array(
    'a' => array('x' => 'one', 'y' => 'two'),
    'single' => 'value', 'empty' => '', 'list' => array('a', 'b')
), 'Nested input parsing changed');

$concat = 'ordinary';
$concat .= '-concat';
$concat .= 42;
check($concat === 'ordinary-concat42', 'Generic concatenation changed');
$small_left = 123;
$small_right = 4.5;
check("{$small_left}{$small_right}" === '1234.5', 'Scalar interpolation conversion changed');

for ($i = 0; $i < 500; $i++) {
    parse_str('item[x]=one', $one);
    $one['item']['y'] = 'two';
    $copy = $one;
    unset($copy['item']['x']);
    $copy['item']['z'] = 'three';
    check($one['item'] === array('x' => 'one', 'y' => 'two'), 'Array copy-on-write changed');
    check($copy['item'] === array('y' => 'two', 'z' => 'three'), 'Singleton growth/deletion changed');
}

parse_str('item[0]=zero&item[key]=named&item[1]=one&repeat=old&repeat=new', $mixed);
check($mixed['repeat'] === 'new', 'Repeated scalar input changed');
$sorted = $mixed['item'];
ksort($sorted);
check(count($sorted) === 3 && $sorted[0] === 'zero' && $sorted[1] === 'one' && $sorted['key'] === 'named', 'Mixed singleton sorting changed');
foreach (array_keys($sorted) as $key) unset($sorted[$key]);
$sorted['after'] = 'clear';
check($sorted === array('after' => 'clear'), 'Cleared singleton reuse changed');

$seen = array();
set_error_handler(function ($number, $message) use (&$seen) {
    $seen[] = $message;
    return true;
});
trigger_error('normal-handler-control', E_USER_NOTICE);
restore_error_handler();
check($seen === array('normal-handler-control'), 'Normal error handler changed');
try {
    throw new RuntimeException('normal-exception-control');
} catch (RuntimeException $error) {
    check($error->getMessage() === 'normal-exception-control', 'Normal exception changed');
}
echo 'PASS PHP ', PHP_VERSION, ' ', PHP_SAPI, " controls\n";
