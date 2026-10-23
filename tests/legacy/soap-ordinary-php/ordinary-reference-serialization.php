<?php
class OrdinaryReferenceClient extends SoapClient {
    public $capturedRequest;

    public function __doRequest($request, $location, $action, $version, $one_way = 0) {
        $this->capturedRequest = $request;
        $env = $version === SOAP_1_2
            ? 'http://www.w3.org/2003/05/soap-envelope'
            : 'http://schemas.xmlsoap.org/soap/envelope/';
        // No parent call, URL access, external entity or external transport.
        return '<?xml version="1.0"?>'
            . '<s:Envelope xmlns:s="' . $env . '"'
            . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
            . ' xmlns:xsd="http://www.w3.org/2001/XMLSchema">'
            . '<s:Body><p:probeResponse xmlns:p="urn:ordinary">'
            . '<return xsi:type="xsd:string">ok</return>'
            . '</p:probeResponse></s:Body></s:Envelope>';
    }
}

foreach (array(SOAP_1_1, SOAP_1_2) as $version) {
    $soap12 = $version === SOAP_1_2;
    $enc = $soap12
        ? 'http://www.w3.org/2003/05/soap-encoding'
        : 'http://schemas.xmlsoap.org/soap/encoding/';
    $client = new OrdinaryReferenceClient(null, array(
        'uri' => 'urn:ordinary',
        'location' => 'urn:local-stub',
        'soap_version' => $version,
        'style' => SOAP_RPC,
        'use' => SOAP_ENCODED,
    ));
    $shared = new stdClass();
    $shared->label = 'ordinary';
    for ($iteration = 0; $iteration < 2; ++$iteration) {
        $reply = $client->__soapCall('probe', array($shared, $shared));
        // Inspect only the generated in-memory XML. LIBXML_NONET also prevents
        // accidental network access if this control is later extended.
        $doc = new DOMDocument();
        $doc->loadXML($client->capturedRequest, LIBXML_NONET);
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('e', $enc);
        $ids = $xpath->query($soap12 ? '//*[@e:id]' : '//*[@id]');
        $refs = $xpath->query($soap12 ? '//*[@e:ref]' : '//*[@href]');
        $identity = $ids->length === 1 && $refs->length === 1;
        if ($identity) {
            $id = $soap12
                ? $ids->item(0)->getAttributeNS($enc, 'id')
                : $ids->item(0)->getAttribute('id');
            $ref = $soap12
                ? $refs->item(0)->getAttributeNS($enc, 'ref')
                : $refs->item(0)->getAttribute('href');
            $identity = $id !== '' && $ref === '#' . $id;
        }
        echo ($soap12 ? '1.2' : '1.1'), ':', $iteration,
            ' serialized-reference=', ($identity ? 'ok' : 'FAIL'),
            ' reply=', ($reply === 'ok' ? 'ok' : 'FAIL'), "\n";
    }
}
?>