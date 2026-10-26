<?php
/* Positive controls preserve explicit XML options and surrounding library state. */
libxml_use_internal_errors(true);
zend_test_override_libxml_global_state();
$expected = libxml_probe_state();
function unchangedGlobals() {
    global $expected;
    if (libxml_probe_state() !== $expected) {
        throw new RuntimeException('The caller libxml global state was not restored');
    }
    libxml_clear_errors();
}
$xml = '<!DOCTYPE root [<!ENTITY value "ordinary">]><root>&value;</root>';
$doc = new DOMDocument();
$doc->substituteEntities = true;
if (!$doc->loadXML($xml) || $doc->documentElement->textContent !== 'ordinary'
    || $doc->documentElement->firstChild->nodeType !== XML_TEXT_NODE) {
    throw new RuntimeException('DOM explicit entity substitution changed');
}
unchangedGlobals();
$fragment = $doc->createDocumentFragment();
if (!$fragment->appendXML('<child/>')) { throw new RuntimeException('DOM fragment parsing changed'); }
unchangedGlobals();
$simple = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOENT);
if ((string)$simple !== 'ordinary') { throw new RuntimeException('SimpleXML explicit entity substitution changed'); }
unchangedGlobals();
$constructed = new SimpleXMLElement($xml, LIBXML_NOENT);
if ((string)$constructed !== 'ordinary') { throw new RuntimeException('SimpleXML constructor changed'); }
unchangedGlobals();
$reader = new XMLReader();
if (!$reader->XML($xml, null, LIBXML_NOENT)) { throw new RuntimeException('XMLReader explicit parser options rejected'); }
$text = '';
while ($reader->read()) { if ($reader->nodeType === XMLReader::TEXT) { $text .= $reader->value; } }
$reader->close();
if ($text !== 'ordinary') { throw new RuntimeException('XMLReader explicit entity substitution changed'); }
unchangedGlobals();
$plain = new DOMDocument();
$plain->loadXML('<root>ordinary</root>');
$schema = '<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"><xs:element name="root" type="xs:string"/></xs:schema>';
if (!$plain->schemaValidateSource($schema)) { throw new RuntimeException('Schema validation changed'); }
unchangedGlobals();
if (@$plain->schemaValidateSource('<invalid/>')) { throw new RuntimeException('Invalid schema accepted'); }
unchangedGlobals();
if (!$plain->relaxNGValidateSource('<element xmlns="http://relaxng.org/ns/structure/1.0" name="root"><text/></element>')) {
    throw new RuntimeException('RelaxNG validation changed');
}
unchangedGlobals();
$parser = xml_parser_create();
if (!xml_parse($parser, '<root>ordinary</root>', true)) { throw new RuntimeException('XML parser changed'); }
xml_parser_free($parser);
unchangedGlobals();
echo "PASS XML explicit options, schema and RelaxNG validation, fragments and parser; libxml globals restored\n";
