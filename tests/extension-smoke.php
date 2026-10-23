<?php
/* Local operations only: no database or cache server is needed. */
$extensionChecks = array();
function extensionCheck($name, $callback) {
    global $extensionChecks;
    try {
        $extensionChecks[$name] = $callback() ? 'ok' : 'failed';
    } catch (Exception $error) {
        $extensionChecks[$name] = get_class($error) . ': ' . $error->getMessage();
    } catch (Throwable $error) {
        $extensionChecks[$name] = get_class($error) . ': ' . $error->getMessage();
    }
}
extensionCheck('gd-exif', function () {
    $image = imagecreatetruecolor(3, 2);
    ob_start();
    imagepng($image);
    $bytes = ob_get_clean();
    imagedestroy($image);
    $path = tempnam('/tmp', 'php-image-');
    file_put_contents($path, $bytes);
    $valid = exif_imagetype($path) === IMAGETYPE_PNG;
    $info = getimagesizefromstring($bytes);
    unlink($path);
    return $valid && $info[0] === 3 && $info[1] === 2;
});
extensionCheck('intl', function () {
    $formatter = new NumberFormatter('en_US', NumberFormatter::DECIMAL);
    return $formatter->format(1234) === '1,234'
        && Normalizer::normalize("e\xcc\x81") === "\xc3\xa9";
});
extensionCheck('curl', function () {
    $path = tempnam('/tmp', 'php-curl-');
    file_put_contents($path, 'curl-local-read');
    $handle = curl_init('file://' . $path);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    $bytes = curl_exec($handle);
    curl_close($handle);
    unlink($path);
    return $bytes === 'curl-local-read';
});
extensionCheck('zip', function () {
    $path = tempnam('/tmp', 'php-zip-');
    $zip = new ZipArchive();
    if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
        unlink($path);
        return false;
    }
    $zip->addFromString('value.txt', 'zip-local-roundtrip');
    $zip->close();
    $zip = new ZipArchive();
    $opened = $zip->open($path) === true;
    $valid = $opened && $zip->getFromName('value.txt') === 'zip-local-roundtrip';
    if ($opened) { $zip->close(); }
    unlink($path);
    return $valid;
});
extensionCheck('bcmath', function () { return bcadd('0.1', '0.2', 2) === '0.30'; });
extensionCheck('mbstring', function () { return mb_strtolower("\xc3\x89", 'UTF-8') === "\xc3\xa9"; });
extensionCheck('xml', function () {
    $writer = new XMLWriter();
    $writer->openMemory();
    $writer->writeElement('value', 'ordinary');
    $xml = $writer->outputMemory();
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    $simple = simplexml_load_string($xml);
    $reader = new XMLReader();
    $reader->XML($xml);
    $reader->read();
    $name = $reader->name;
    $reader->close();
    $parser = xml_parser_create();
    $parsed = xml_parse($parser, $xml, true);
    xml_parser_free($parser);
    return $parsed === 1 && $name === 'value' && (string) $simple === 'ordinary'
        && $dom->documentElement->textContent === 'ordinary';
});
extensionCheck('framework-functions', function () {
    $info = new finfo(FILEINFO_MIME_TYPE);
    return ctype_digit('123') && filter_var('42', FILTER_VALIDATE_INT) === 42
        && iconv('UTF-8', 'ISO-8859-1', "\xc3\xa9") === "\xe9"
        && count(token_get_all('<?php echo 1;')) > 1
        && $info->buffer('ordinary text') === 'text/plain';
});
extensionCheck('sqlite', function () {
    $pdo = new PDO('sqlite::memory:');
    $native = new SQLite3(':memory:');
    $valid = (int) $pdo->query('SELECT 42')->fetchColumn() === 42
        && $native->querySingle('SELECT 42') === 42;
    $native->close();
    return $valid;
});
extensionCheck('postgresql-drivers', function () {
    return extension_loaded('pgsql') && function_exists('pg_query_params')
        && extension_loaded('pdo_pgsql') && in_array('pgsql', PDO::getAvailableDrivers(), true);
});
extensionCheck('sockets', function () {
    $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
    if ($socket === false) { return false; }
    socket_close($socket);
    return true;
});
extensionCheck('json-zlib', function () {
    $value = array('ordinary' => array(1, true, 'value'));
    return json_decode(gzuncompress(gzcompress(json_encode($value))), true) === $value;
});
extensionCheck('igbinary', function () {
    $value = array('ordinary' => array(1, true, 'value'));
    return igbinary_unserialize(igbinary_serialize($value)) === $value;
});
extensionCheck('msgpack', function () {
    $value = array('ordinary' => array(1, true, 'value'));
    return msgpack_unpack(msgpack_pack($value)) === $value;
});
extensionCheck('apcu', function () {
    $key = 'php-extension-test-' . uniqid('', true);
    $value = array('ordinary' => 42);
    $stored = apcu_store($key, $value, 10);
    $read = apcu_fetch($key, $success);
    $deleted = apcu_delete($key);
    return $stored && $success && $read === $value && $deleted;
});
extensionCheck('imagick', function () {
    $image = new Imagick();
    $image->newImage(3, 2, new ImagickPixel('white'));
    $image->setImageFormat('png');
    $bytes = $image->getImageBlob();
    $image->clear();
    $copy = new Imagick();
    $copy->readImageBlob($bytes);
    $valid = $copy->getImageWidth() === 3 && $copy->getImageHeight() === 2;
    $copy->clear();
    return $valid;
});
extensionCheck('redis', function () {
    $client = new Redis();
    // Older Redis clients require a connection even for setOption().
    $valid = $client instanceof Redis && defined('Redis::SERIALIZER_IGBINARY');
    if (PHP_VERSION_ID >= 70000) {
        $valid = defined('Redis::SERIALIZER_MSGPACK') && $valid;
    }
    return $valid;
});
extensionCheck('memcached', function () {
    $client = new Memcached();
    return $client->setOption(Memcached::OPT_SERIALIZER, Memcached::SERIALIZER_IGBINARY)
        && $client->setOption(Memcached::OPT_SERIALIZER, Memcached::SERIALIZER_MSGPACK)
        && $client->addServer('127.0.0.1', 11211) && count($client->getServerList()) === 1;
});
foreach ($extensionChecks as $name => $value) { $result['smoke:' . $name] = $value; }
