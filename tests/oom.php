<?php
/* PHP 7.1-compatible behavioral probe for the EOL hard-OOM backport. */
class OomShutdownProbe
{
    public function __destruct()
    {
        echo "DESTRUCTOR_MARKER\n";
    }
}

register_shutdown_function(function () {
    echo "SHUTDOWN_MARKER\n";
});
$probe = new OomShutdownProbe();
echo "PROBE_STARTED\n";
if (isset($argv[1]) && $argv[1] === 'control') {
    $data = str_repeat('x', 1024 * 1024);
    if (strlen($data) !== 1024 * 1024) {
        exit(2);
    }
    echo "CONTROL_OK\n";
} elseif (isset($argv[1]) && $argv[1] === 'many') {
    /* A second allocation class reaches OOM through repeated small allocations
     * and hash growth, rather than one large string request. */
    $data = array();
    for ($i = 0; $i < 100000; $i++) {
        $data[] = str_repeat('x', 1024);
    }
    echo "UNEXPECTED_ALLOCATION\n";
    exit(3);
} else {
    /* The runner sets memory_limit=8M. Do not use a host/cgroup OOM. */
    $data = str_repeat('x', 64 * 1024 * 1024);
    echo "UNEXPECTED_ALLOCATION\n";
    exit(3);
}
