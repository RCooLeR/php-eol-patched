<?php
/* Extract source, not a reimplementation, for the finite C input feeder. */
$source = file_get_contents('/usr/src/php/main/rfc1867.c');
$start = strpos($source, 'static int fill_buffer(multipart_buffer *self)');
if ($start === false) { fwrite(STDERR, "fill_buffer missing\n"); exit(2); }
$opening = strpos($source, '{', $start);
$depth = 1;
$end = $opening + 1;
while ($depth && $end < strlen($source)) {
    if ($source[$end] === '{') $depth++;
    if ($source[$end] === '}') $depth--;
    $end++;
}
if ($depth) { fwrite(STDERR, "Unbalanced function\n"); exit(2); }
$function = substr($source, $start, $end - $start);
$template = file_get_contents('/tests/native-byte-bound.c.in');
file_put_contents('/tmp/native-byte-bound.c', str_replace('@FILL_BUFFER@', $function, $template));

function extractFunction($source, $signature) {
    $start = strpos($source, $signature);
    if ($start === false) { fwrite(STDERR, "Native helper missing\n"); exit(2); }
    $end = strpos($source, '{', $start) + 1;
    $depth = 1;
    while ($depth && $end < strlen($source)) {
        if ($source[$end] === '{') $depth++;
        if ($source[$end] === '}') $depth--;
        $end++;
    }
    if ($depth) exit(2);
    return substr($source, $start, $end - $start);
}
$scanner = file_get_contents('/usr/src/php/ext/standard/url_scanner_ex.re');
$basic = file_get_contents('/usr/src/php/ext/standard/basic_functions.c');
$template = file_get_contents('/tests/native-state-init.c.in');
$template = str_replace('@SCANNER_ACTIVATE@', extractFunction($scanner, 'static int php_url_scanner_ex_activate(int type)'), $template);
$template = str_replace('@SCANNER_EARLY@', extractFunction($scanner, 'void php_url_scanner_early_rinit(void)'), $template);
$template = str_replace('@BASIC_EARLY@', extractFunction($basic, 'PHPAPI void php_basic_early_rinit(void)'), $template);
file_put_contents('/tmp/native-state-init.c', $template);
