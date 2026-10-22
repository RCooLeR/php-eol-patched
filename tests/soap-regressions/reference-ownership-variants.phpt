--TEST--
SOAP reference ownership across protocol spellings and normalized Apache map keys
--EXTENSIONS--
soap
--FILE--
<?php
class SoapOwnershipVariantHandler {
    public function probe($map, $reference) {
        $GLOBALS['ownershipDecoded'] = array($map, $reference);
    }
}
foreach (array('1.1', '1.2-hash', '1.2-bare') as $form) {
    foreach (array('string', 'numeric') as $keyKind) {
        $soap12 = $form !== '1.1';
        $version = $soap12 ? SOAP_1_2 : SOAP_1_1;
        $env = $soap12 ? 'http://www.w3.org/2003/05/soap-envelope' : 'http://schemas.xmlsoap.org/soap/envelope/';
        $enc = $soap12 ? 'http://www.w3.org/2003/05/soap-encoding' : 'http://schemas.xmlsoap.org/soap/encoding/';
        $id = $soap12 ? 'e:id="retained"' : 'id="retained"';
        $ref = $soap12 ? 'e:ref="' . ($form === '1.2-hash' ? '#' : '') . 'retained"' : 'href="#retained"';
        $firstKey = $keyKind === 'numeric' ? '<key xsi:type="xsd:string">2</key>' : '<key xsi:type="xsd:string">label</key>';
        $secondKey = $keyKind === 'numeric' ? '<key xsi:type="xsd:int">2</key>' : '<key xsi:type="xsd:string">label</key>';
        $xml = '<?xml version="1.0"?><s:Envelope xmlns:s="' . $env . '" xmlns:e="' . $enc . '"'
            . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"'
            . ' xmlns:a="http://xml.apache.org/xml-soap"><s:Body>'
            . '<p:probe xmlns:p="urn:ownership" s:encodingStyle="' . $enc . '">'
            . '<map xsi:type="a:Map"><item>' . $firstKey . '<value ' . $id . ' xsi:type="e:Struct">'
            . '<label xsi:type="xsd:string">retained object</label></value></item>'
            . '<item>' . $secondKey . '<value xsi:type="xsd:string">replacement</value></item></map>'
            . '<reference ' . $ref . '/></p:probe></s:Body></s:Envelope>';
        $server = new SoapServer(null, array('uri' => 'urn:ownership', 'soap_version' => $version));
        $server->setClass('SoapOwnershipVariantHandler');
        ob_start();
        $server->handle($xml);
        ob_end_clean();
        $decoded = $GLOBALS['ownershipDecoded'];
        $expected = $keyKind === 'numeric' ? array(2 => 'replacement') : array('label' => 'replacement');
        $ok = $decoded[0] === $expected && is_object($decoded[1]) && $decoded[1]->label === 'retained object';
        echo $form, ':', $keyKind, ' ownership=', ($ok ? 'ok' : 'FAIL'), "\n";
    }
}
?>
--EXPECT--
1.1:string ownership=ok
1.1:numeric ownership=ok
1.2-hash:string ownership=ok
1.2-hash:numeric ownership=ok
1.2-bare:string ownership=ok
1.2-bare:numeric ownership=ok
