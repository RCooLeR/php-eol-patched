<?php
$sourceRoot = getenv('PHP_SOURCE_ROOT') ?: '/usr/src/php';
$directory = $argv[1];
$version = $argv[2];

function extract_function($source, $signature) {
    $start = strpos($source, $signature);
    if ($start === false) throw new RuntimeException('Function not found: ' . $signature);
    $end = strpos($source, "\n}", $start);
    if ($end === false) throw new RuntimeException('Function end not found');
    return substr($source, $start, $end + 2 - $start);
}

function write_probe($directory, $name, $replacements) {
    $template = file_get_contents(__DIR__ . '/native-' . $name . '.c.in');
    $output = str_replace(array_keys($replacements), array_values($replacements), $template);
    if (preg_match('/@[A-Z_]+@/', $output)) throw new RuntimeException('Unexpanded probe template');
    file_put_contents($directory . '/' . $name . '.c', $output);
}

if ($version === '7.0.33') {
    $source = file_get_contents($sourceRoot . '/ext/gd/libgd/gd_interpolation.c');
    $start = strpos($source, 'typedef struct');
    $end = strpos($source, '} LineContribType;', $start) + strlen('} LineContribType;');
    write_probe($directory, 'gd-contributions', array(
        '@TYPES@' => substr($source, $start, $end - $start),
        '@FUNCTIONS@' => extract_function($source, 'static inline LineContribType * _gdContributionsAlloc(') . "\n" .
            extract_function($source, 'static inline void _gdContributionsFree(')
    ));
}
if ($version === '7.1.33') {
    $source = file_get_contents($sourceRoot . '/ext/standard/string.c');
    write_probe($directory, 'strip-tags', array(
        '@FUNCTIONS@' => extract_function($source, 'int php_tag_find(char *tag, size_t len, const char *set) {') . "\n" .
            extract_function($source, 'PHPAPI size_t php_strip_tags_ex(')
    ));
}
if (in_array($version, array('7.1.33', '7.2.34', '7.3.33', '7.4.33'), true)) {
    $source = file_get_contents($sourceRoot . '/ext/pdo_sqlite/sqlite_driver.c');
    write_probe($directory, 'sqlite-quote', array(
        '@FUNCTION@' => extract_function($source, 'static int sqlite_handle_quoter(')
    ));
}
