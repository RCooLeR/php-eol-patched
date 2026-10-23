<?php
class OrdinaryIdentityClient extends SoapClient {
    public $localResponse;

    public function __doRequest($request, $location, $action, $version, $one_way = 0) {
        return $this->localResponse;
    }
}

foreach (array('1.1', '1.2-hash', '1.2-bare') as $form) {
    $soap12 = $form !== '1.1';
    $version = $soap12 ? SOAP_1_2 : SOAP_1_1;
    $env = $soap12
        ? 'http://www.w3.org/2003/05/soap-envelope'
        : 'http://schemas.xmlsoap.org/soap/envelope/';
    $enc = $soap12
        ? 'http://www.w3.org/2003/05/soap-encoding'
        : 'http://schemas.xmlsoap.org/soap/encoding/';
    $id = $soap12 ? 'e:id="shared"' : 'id="shared"';
    $ref = $soap12
        ? 'e:ref="' . ($form === '1.2-hash' ? '#' : '') . 'shared"'
        : 'href="#shared"';
    $client = new OrdinaryIdentityClient(null, array(
        'uri' => 'urn:ordinary',
        'location' => 'urn:local-stub',
        'soap_version' => $version,
    ));
    // The owning first field remains in the ordinary returned object graph.
    // The fixture has no duplicate map entries or abnormal callbacks.
    $client->localResponse = '<?xml version="1.0"?>'
        . '<s:Envelope xmlns:s="' . $env . '" xmlns:e="' . $enc . '"'
        . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
        . ' xmlns:xsd="http://www.w3.org/2001/XMLSchema">'
        . '<s:Body><p:probeResponse xmlns:p="urn:ordinary" s:encodingStyle="' . $enc . '">'
        . '<return xsi:type="e:Struct">'
        . '<first ' . $id . ' xsi:type="e:Struct">'
        . '<label xsi:type="xsd:string">ordinary</label></first>'
        . '<second ' . $ref . '/>'
        . '</return></p:probeResponse></s:Body></s:Envelope>';
    for ($iteration = 0; $iteration < 2; ++$iteration) {
        $value = $client->__soapCall('probe', array());
        $identity = is_object($value) && is_object($value->first)
            && $value->first === $value->second
            && $value->first->label === 'ordinary';
        if ($identity) {
            $value->second->label = 'shared mutation';
            $identity = $value->first->label === 'shared mutation';
        }
        echo $form, ':', $iteration, ' identity=', ($identity ? 'ok' : 'FAIL'), "\n";
    }
}
?>