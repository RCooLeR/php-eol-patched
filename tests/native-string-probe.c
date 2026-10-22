/* Test-only native helper calls. Never expose this extension to a service. */
#include "php.h"
#include <stdint.h>
#include <string.h>

ZEND_BEGIN_ARG_INFO_EX(arginfo_native_string_probe, 0, 0, 1)
	ZEND_ARG_INFO(0, mode)
ZEND_END_ARG_INFO()

static int normal_strings(int persistent)
{
	zend_string *s, *old;
	int operation;
	for (operation = 0; operation < 3; operation++) {
		s = zend_string_init("abcdefgh", 8, persistent);
		if (operation == 0) s = zend_string_realloc(s, 12, persistent);
		if (operation == 1) s = zend_string_extend(s, 16, persistent);
		if (operation == 2) s = zend_string_truncate(s, 4, persistent);
		if (memcmp(ZSTR_VAL(s), "abcd", 4)) return 0;
		if (ZSTR_LEN(s) != (size_t)(operation == 0 ? 12 : operation == 1 ? 16 : 4)) return 0;
		zend_string_release(s);

		s = zend_string_init("abcdefgh", 8, persistent);
		old = zend_string_copy(s);
		if (operation == 0) s = zend_string_realloc(s, 12, persistent);
		if (operation == 1) s = zend_string_extend(s, 16, persistent);
		if (operation == 2) s = zend_string_truncate(s, 4, persistent);
		if (memcmp(ZSTR_VAL(s), "abcd", 4) || ZSTR_LEN(old) != 8 || memcmp(ZSTR_VAL(old), "abcdefgh", 8)) return 0;
		zend_string_release(s);
		zend_string_release(old);
	}
	return 1;
}

PHP_FUNCTION(native_string_probe)
{
	char *mode;
	size_t mode_len;
	int persistent;
	zend_string *s;
	if (zend_parse_parameters(ZEND_NUM_ARGS(), "s", &mode, &mode_len) == FAILURE) return;
	if (!strcmp(mode, "normal")) {
		if (normal_strings(0) && normal_strings(1)) RETURN_STRING("PASS");
		RETURN_STRING("FAIL");
	}
	persistent = strstr(mode, "persistent") != NULL;
	s = zend_string_init("abcd", 4, persistent);
	/* SIZE_MAX plus fixed header must be rejected before allocating. No large
	 * request, object, callback or retained worker state is constructed here. */
	if (!strncmp(mode, "realloc-", 8)) {
		(void)zend_string_realloc(s, SIZE_MAX, persistent);
	} else if (!strncmp(mode, "extend-", 7)) {
		(void)zend_string_extend(s, SIZE_MAX, persistent);
	} else {
		zend_string_release(s);
		RETURN_STRING("UNKNOWN_MODE");
	}
	/* An overflow-guard probe must never return from the allocator. */
	RETURN_STRING("GUARD_RETURNED");
}

static const zend_function_entry native_guard_functions[] = {
	PHP_FE(native_string_probe, arginfo_native_string_probe)
	PHP_FE_END
};

zend_module_entry native_guard_probe_module_entry = {
	STANDARD_MODULE_HEADER, "native_guard_probe", native_guard_functions,
	NULL, NULL, NULL, NULL, NULL, "1.0", STANDARD_MODULE_PROPERTIES
};

ZEND_GET_MODULE(native_guard_probe)
