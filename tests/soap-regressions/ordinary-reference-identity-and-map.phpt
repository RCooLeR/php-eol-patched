--TEST--
Ordinary SOAP 1.1/1.2 object identity and scalar Apache Map last-write-wins
--EXTENSIONS--
soap
--FILE--
<?php
class OrdinaryReferenceHandler {
    public function probe($first, $second, $map) {
        $GLOBALS['ordinaryDecoded'] = array($first, $second, $map);
    }
}

foreach (array(SOAP_1_1, SOAP_1_2) as $version) {
    $soap12 = $version === SOAP_1_2;
    $env = $soap12
        ? 'http://www.w3.org/2003/05/soap-envelope'
        : 'http://schemas.xmlsoap.org/soap/envelope/';
    $enc = $soap12
        ? 'http://www.w3.org/2003/05/soap-encoding'
        : 'http://schemas.xmlsoap.org/soap/encoding/';
    $id = $soap12 ? 'e:id="shared"' : 'id="shared"';
    // The two protocol spellings identify a live, ordinary object. No result
    // entry containing it is overwritten. SOAP 1.2 deliberately omits '#'.
    $ref = $soap12 ? 'e:ref="shared"' : 'href="#shared"';
    $xml = '<?xml version="1.0"?>'
        . '<s:Envelope xmlns:s="' . $env . '" xmlns:e="' . $enc . '"'
        . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
        . ' xmlns:xsd="http://www.w3.org/2001/XMLSchema"'
        . ' xmlns:a="http://xml.apache.org/xml-soap">'
        . '<s:Body><p:probe xmlns:p="urn:ordinary" s:encodingStyle="' . $enc . '">'
        . '<first ' . $id . ' xsi:type="e:Struct">'
        . '<label xsi:type="xsd:string">ordinary</label></first>'
        . '<second ' . $ref . '/>'
        . '<map xsi:type="a:Map">'
        . '<item><key xsi:type="xsd:string">label</key><value xsi:type="xsd:string">old</value></item>'
        . '<item><key xsi:type="xsd:string">label</key><value xsi:type="xsd:string">current</value></item>'
        . '<item><key xsi:type="xsd:string">2</key><value xsi:type="xsd:string">old</value></item>'
        . '<item><key xsi:type="xsd:int">2</key><value xsi:type="xsd:string">numeric</value></item>'
        . '</map></p:probe></s:Body></s:Envelope>';

    // A second normal handle on the same server checks normal lifecycle reuse.
    $server = new SoapServer(null, array('uri' => 'urn:ordinary', 'soap_version' => $version));
    $server->setClass('OrdinaryReferenceHandler');
    for ($iteration = 0; $iteration < 2; ++$iteration) {
        $GLOBALS['ordinaryDecoded'] = null;
        ob_start();
        $server->handle($xml);
        ob_end_clean();
        $args = $GLOBALS['ordinaryDecoded'];
        $identity = is_object($args[0]) && $args[0] === $args[1]
            && $args[0]->label === 'ordinary';
        if ($identity) {
            $args[1]->label = 'shared mutation';
            $identity = $args[0]->label === 'shared mutation';
        }
        $contents = $args[2] === array('label' => 'current', 2 => 'numeric');
        echo ($soap12 ? '1.2' : '1.1'), ':', $iteration,
            ' identity=', ($identity ? 'ok' : 'FAIL'),
            ' map=', ($contents ? 'ok' : 'FAIL'), "\n";
    }
}
?>
--EXPECT--
1.1:0 identity=ok map=ok
1.1:1 identity=ok map=ok
1.2:0 identity=ok map=ok
1.2:1 identity=ok map=ok
