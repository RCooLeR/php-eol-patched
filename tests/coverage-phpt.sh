#!/usr/bin/env bash
# Sourced from a PHPT runner after its source tree and PHP arguments are ready.
# Older runners compare extension names case-sensitively; the built-in name is Phar.
sed -i 's/^phar$/Phar/' ext/phar/tests/tar/ghsa-vc5h-9ppw-p5f3-symlink-circular.phpt

run_coverage_group() {
    local group=$1
    local optional=$2
    shift 2
    local selected status=0
    for selected in "$@"; do test -f "$selected"; done
    export TEST_PHP_JUNIT="$scratch/$group.xml"
    php "${php_args[@]}" ./run-tests.php "${php_args[@]}" -q "$@" > "$group.log" 2>&1 || status=$?
    cat "$group.log"
    if grep -Eq '(Tests borked|Tests warned|Tests failed)[[:space:]]*:[[:space:]]*[1-9]' "$group.log"; then status=1; fi
    if grep -Eq 'Tests skipped[[:space:]]*:[[:space:]]*[1-9]' "$group.log"; then
        if [ "$optional" != aes-wrap ] || [ "$#" -ne 1 ] ||
            [ "$1" != ext/openssl/tests/gh22186.phpt ]; then
            status=1
        else
            case "$version" in
                7.1.33|7.2.34|7.3.33|7.4.33|8.0.30)
                    if ! grep -Eq 'Tests skipped[[:space:]]*:[[:space:]]*1[[:space:]]' "$group.log" ||
                        ! grep -Fq '[ext/openssl/tests/gh22186.phpt] reason: aes-128-wrap-pad not usable on this OpenSSL build' "$group.log"; then
                        status=1
                    else
                        printf 'Coverage prerequisite unavailable: PHP %s cannot use AES-WRAP-PAD; this skipped regression is not counted as passing.\n' "$version"
                    fi
                    ;;
                *) status=1 ;;
            esac
        fi
    elif ! grep -Eq 'Tests passed[[:space:]]*:[[:space:]]*[1-9]' "$group.log"; then
        status=1
    fi
    if [ "$status" -ne 0 ]; then
        find "$scratch" -name '*.diff' -type f -exec cat {} \;
        return "$status"
    fi
}

if [ -f "$test_dir/coverage-tests.list" ]; then
    cp /tests/critical-controls.phpt "$scratch/"
    mapfile -t coverage < "$test_dir/coverage-tests.list"
    test "${#coverage[@]}" -gt 0
    run_coverage_group coverage required "${coverage[@]}"
fi
if [ -f "$test_dir/coverage-optional-tests.list" ]; then
    mapfile -t coverage_optional < "$test_dir/coverage-optional-tests.list"
    test "${#coverage_optional[@]}" -eq 1
    run_coverage_group coverage-optional aes-wrap "${coverage_optional[@]}"
fi
