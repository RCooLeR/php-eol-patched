<?php
ob_start();
$fixtures = PHP_VERSION_ID < 70100 ? '/legacy/soap-ordinary-php/' : '/soap-ordinary-php/';
foreach (glob(__DIR__ . $fixtures . '*.php') as $file) { require $file; }
$output = ob_get_clean();
$lines = array_values(array_filter(explode("\n", trim($output)), 'strlen'));
header('Content-Type: application/json');
echo json_encode(array('pid' => getmypid(), 'php' => PHP_VERSION,
    'soap' => extension_loaded('soap'), 'pass' => count($lines) === 14 && strpos($output, 'FAIL') === false,
    'checks' => count($lines), 'output' => $output));
