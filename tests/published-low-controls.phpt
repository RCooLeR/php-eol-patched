--TEST--
Ordinary mbregex encodings, ZIP Phar aliases and SOAP Apache map decoding
--EXTENSIONS--
mbstring
Phar
soap
--INI--
phar.readonly=0
--FILE--
<?php
function requireControl($ok, $message) { if (!$ok) { throw new RuntimeException($message); } }
foreach (array('UTF-8', 'ISO-8859-1', 'SJIS') as $encoding) {
    requireControl(mb_regex_encoding($encoding), 'Cannot select ordinary encoding');
    requireControl(mb_ereg_search_init('x'), 'Cannot initialize ordinary search');
    requireControl(mb_ereg_search_pos('x') === array(0, 1), 'Wrong ordinary match');
}
mb_regex_encoding('UTF-8');
$archivePath = sys_get_temp_dir() . '/low-control-' . getmypid() . '.phar.zip';
$archive = new Phar($archivePath, 0, 'low-control.phar');
$archive['control.txt'] = 'ordinary archive';
requireControl(file_get_contents('phar://' . $archivePath . '/control.txt') === 'ordinary archive', 'ZIP Phar content mismatch');
requireControl($archive->getAlias() === 'low-control.phar', 'ZIP Phar alias mismatch');
$request = '<?xml version="1.0" encoding="UTF-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:apache="http://xml.apache.org/xml-soap"><soap:Body><test><map xsi:type="apache:Map"><item><key>hello</key><value xsi:type="xsd:string">world</value></item></map></test></soap:Body></soap:Envelope>';
$server = new SoapServer(null, array('uri' => 'urn:test', 'typemap' => array(array('type_name' => 'anything'))));
$server->addFunction('test');
function test($m) { requireControl($m === array('hello' => 'world'), 'Wrong ordinary map'); return 'map-control-ok'; }
ob_start();
$server->handle($request);
$response = ob_get_clean();
requireControl(strpos($response, 'map-control-ok') !== false, 'Missing ordinary map response');
unset($archive);
unlink($archivePath);
echo "PASS ordinary mbregex, ZIP Phar alias/content, SOAP typed-map controls\n";
--EXPECT--
PASS ordinary mbregex, ZIP Phar alias/content, SOAP typed-map controls
