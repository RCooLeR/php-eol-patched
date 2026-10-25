<?php
/* Flat form-encoded output keeps this probe independent of JSON support. */
$result = array(
    'version' => PHP_VERSION,
    'sapi' => PHP_SAPI,
    'memory_limit' => ini_get('memory_limit'),
    'post_max_size' => ini_get('post_max_size'),
    'upload_progress' => ini_get('session.upload_progress.enabled'),
    'sample' => isset($_POST['sample']) ? $_POST['sample'] : '',
    'modules' => implode(',', get_loaded_extensions()),
    'shell_present' => is_file('/bin/sh') ? 'yes' : 'no',
    'source_present' => is_dir('/usr/src/php') ? 'yes' : 'no',
    'environment_probe' => getenv('PHP_EOL_FPM_PROBE'),
    'worker_pid' => (string) getmypid()
);
foreach (array('worker' => '/proc/self/status', 'master' => '/proc/1/status') as $role => $path) {
    $status = file_get_contents($path);
    foreach (array('Uid', 'Gid') as $field) {
        if (preg_match('/^' . $field . ':\s+(\d+)\s+(\d+)/m', $status, $matches)) {
            $result[$role . '_' . strtolower($field)] = $matches[2];
        }
    }
}
foreach (get_loaded_extensions() as $extension) {
    $version = phpversion($extension);
    $result['module_version:' . $extension] = $version === false ? 'not reported' : $version;
}
require '/tests/extension-smoke.php';
if (PHP_VERSION_ID >= 80100) {
    require '/tests/opcache-types.inc';
    $object = new OrdinaryCacheChild();
    $object->item = $object;
    $valid = $object->accept($object) === $object
        && unserialize(serialize(OrdinaryCachedEnum::Value)) === OrdinaryCachedEnum::Value;
    $result['cached_types'] = $valid ? 'ok' : 'failed';
}
header('Content-Type: application/x-www-form-urlencoded');
echo http_build_query($result, '', '&');
