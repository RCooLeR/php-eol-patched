<?php
/* Bounded path and ordinary XML controls for CVE-2023-0568. */
if (isset($argv[1]) && $argv[1] === '--child') {
    if (strlen(getcwd()) !== PHP_MAXPATHLEN - 1) {
        throw new RuntimeException('Boundary CWD length mismatch');
    }
    $doc = new DOMDocument();
    if (!$doc->loadXML('<root><item>ordinary</item></root>')
        || $doc->documentElement->textContent !== 'ordinary') {
        throw new RuntimeException('DOM parsing at the path boundary failed');
    }
    $reader = new XMLReader();
    if (!$reader->XML('<root><item>ordinary</item></root>')) {
        throw new RuntimeException('XMLReader parsing at the path boundary failed');
    }
    while ($reader->read()) {}
    $reader->close();
    echo "PASS maximum-length CWD: DOM and XMLReader parsing\n";
    exit;
}
if (isset($argv[1]) && $argv[1] === '--basedir') {
    ini_set('open_basedir', '.');
    if (!is_dir(getcwd()) || @is_dir(dirname(getcwd()))) {
        throw new RuntimeException('Ordinary open_basedir restriction changed');
    }
    echo "PASS ordinary inside/outside open_basedir restriction\n";
    exit;
}
$origin = getcwd();
$script = __FILE__;
$root = sys_get_temp_dir() . '/php-path-control-' . getmypid();
$created = array();
mkdir($root);
chdir($root);
try {
    $command = escapeshellarg(PHP_BINARY) . ' -n ' . escapeshellarg($script) . ' --basedir';
    passthru($command, $status);
    if ($status !== 0) { throw new RuntimeException('Ordinary open_basedir child failed'); }
    $remaining = PHP_MAXPATHLEN - 1 - strlen(getcwd());
    while ($remaining > 0) {
        if ($remaining < 2) { throw new RuntimeException('Unrepresentable directory length'); }
        $length = min(200, $remaining - 1);
        if ($remaining - $length - 1 === 1) { --$length; }
        $segment = str_repeat('d', $length);
        if (!mkdir($segment) || !chdir($segment)) { throw new RuntimeException('Cannot create boundary CWD'); }
        $created[] = $segment;
        $remaining -= $length + 1;
    }
    $command = escapeshellarg(PHP_BINARY) . ' -n -d extension=dom.so -d extension=xmlreader.so '
        . escapeshellarg($script) . ' --child';
    passthru($command, $status);
    if ($status !== 0) { throw new RuntimeException('Boundary child failed'); }
} finally {
    foreach (array_reverse($created) as $segment) {
        chdir('..');
        rmdir($segment);
    }
    chdir($origin);
    rmdir($root);
}
