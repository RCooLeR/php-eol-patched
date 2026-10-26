--TEST--
Unsupported mbregex encoding rejection preserves current encoding and search state
--EXTENSIONS--
mbstring
--FILE--
<?php
mb_regex_encoding('UTF-8');
try {
    mb_regex_encoding('iso-8859-11');
    throw new RuntimeException('Expected unsupported encoding rejection');
} catch (ValueError $error) {
    if (strpos($error->getMessage(), 'must be a valid encoding') === false) throw $error;
}
if (mb_regex_encoding() !== 'UTF-8') throw new RuntimeException('Rejected encoding changed regex state');
if (!mb_ereg_search_init('x') || mb_ereg_search_pos('x') !== array(0, 1)) throw new RuntimeException('Search failed after rejection');
echo "PASS unsupported encoding rejection preserves ordinary search state\n";
--EXPECT--
PASS unsupported encoding rejection preserves ordinary search state
