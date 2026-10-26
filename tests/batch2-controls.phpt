--TEST--
Password, URL validation and get_headers path controls after published fixes
--SKIPIF--
<?php
if (!extension_loaded('filter')) die('skip filter extension required');
?>
--FILE--
<?php
foreach (array('', 'ordinary passphrase', 'password-with-digits-123') as $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT, array('cost' => 4));
    if (!is_string($hash) || !password_verify($password, $hash)
        || password_verify($password . 'x', $hash)) {
        die("Password control failed\n");
    }
}
echo "Password controls passed\n";

// CVE-2024-3096: bcrypt must reject embedded and leading NUL bytes.
foreach (array("\0password", "null\0password") as $password) {
    try {
        $rejected = !is_string(@password_hash($password, PASSWORD_BCRYPT));
    } catch (ValueError $error) {
        $rejected = true;
    }
    if (!$rejected) die("NUL password was accepted\n");
}
echo "NUL password controls passed\n";

foreach (array('https://example.com/path?q=value', 'https://user:pass@example.com/',
               'http://test@127.0.0.1', 'http://test@[::1]') as $url) {
    if (filter_var($url, FILTER_VALIDATE_URL) !== $url) {
        die("Valid URL rejected\n");
    }
}
echo "Valid URL controls passed\n";

// Published payloads from bug77423.phpt, bug81122.phpt and
// ghsa-w8qr-v226-r27w.phpt (CVE-2020-7071, CVE-2021-21705, CVE-2024-5458).
// https://github.com/php/php-src/commit/a5538c62293fa782fcc382d0635cfc0c8b9190e3
// https://github.com/php/php-src/commit/4066610b47e22c24cbee91be434a94357056a479
foreach (array('http://php.net\\@aliyun.com/aaa.do', 'https://example.com:\\@test.com/',
               'https://user:\\epass@test.com', 'https://user:\\@test.com',
               'http://t[est@127.0.0.1', 'http://t[est@[::1]') as $url) {
    if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
        die("Invalid URL accepted\n");
    }
}
echo "Invalid URL controls passed\n";

// CVE-2020-7066: reject NUL before opening a stream, without network access.
// https://github.com/php/php-src/commit/0d139c5b94a5f485a66901919e51faddb0371c43
class BatchHeadersStream
{
    public $context;
    public static $opened = false;

    public function stream_open($path, $mode, $options, &$openedPath)
    {
        self::$opened = true;
        return false;
    }
}
stream_wrapper_register('batchheaders', 'BatchHeadersStream', STREAM_IS_URL);
@get_headers('batchheaders://ordinary');
if (!BatchHeadersStream::$opened) die("Stream control was not reached\n");
BatchHeadersStream::$opened = false;
try {
    $rejected = @get_headers("batchheaders://ordinary\0ignored") === null;
} catch (ValueError $error) {
    $rejected = true;
}
stream_wrapper_unregister('batchheaders');
if (!$rejected || BatchHeadersStream::$opened) die("NUL URL was not rejected before opening\n");
echo "get_headers NUL control passed\n";
?>
--EXPECT--
Password controls passed
NUL password controls passed
Valid URL controls passed
Invalid URL controls passed
get_headers NUL control passed
