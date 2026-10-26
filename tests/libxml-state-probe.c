/* Test-only bridge for the published CVE-2023-3823 regression tests.
 * The override body follows php/php-src c283c3ab0ba45d21b2b8745c1f9c7cbfe771c975.
 */
#include "php.h"
#include <libxml/globals.h>
#include <libxml/parser.h>

PHP_FUNCTION(zend_test_override_libxml_global_state)
{
    if (zend_parse_parameters_none() == FAILURE) { return; }
    xmlLoadExtDtdDefaultValue = 1;
    xmlDoValidityCheckingDefaultValue = 1;
    (void) xmlPedanticParserDefault(1);
    (void) xmlSubstituteEntitiesDefault(1);
    (void) xmlLineNumbersDefault(1);
    (void) xmlKeepBlanksDefault(0);
}

PHP_FUNCTION(libxml_probe_state)
{
    if (zend_parse_parameters_none() == FAILURE) { return; }
    array_init(return_value);
    add_next_index_long(return_value, xmlLoadExtDtdDefaultValue);
    add_next_index_long(return_value, xmlDoValidityCheckingDefaultValue);
    add_next_index_long(return_value, xmlPedanticParserDefaultValue);
    add_next_index_long(return_value, xmlSubstituteEntitiesDefaultValue);
    add_next_index_long(return_value, xmlLineNumbersDefaultValue);
    add_next_index_long(return_value, xmlKeepBlanksDefaultValue);
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_override, 0, 0, 0)
ZEND_END_ARG_INFO()

static const zend_function_entry libxml_probe_functions[] = {
    PHP_FE(zend_test_override_libxml_global_state, arginfo_override)
    PHP_FE(libxml_probe_state, arginfo_override)
    PHP_FE_END
};

zend_module_entry libxml_probe_module_entry = {
    STANDARD_MODULE_HEADER,
    "zend-test",
    libxml_probe_functions,
    NULL, NULL, NULL, NULL, NULL,
    "1.0",
    STANDARD_MODULE_PROPERTIES
};

ZEND_GET_MODULE(libxml_probe)
