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
    'source_present' => is_dir('/usr/src/php') ? 'yes' : 'no'
);
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
