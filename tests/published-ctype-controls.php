<?php
$checks = 0;

function describeValue($value) {
    return is_string($value) ? 'hex:' . bin2hex($value) : var_export($value, true);
}

function verifySame($expected, $actual, $label) {
    global $checks;
    $checks++;
    if ($actual !== $expected) {
        throw new RuntimeException($label . ': expected ' . describeValue($expected)
            . ', got ' . describeValue($actual));
    }
}

function invalidInterval($input) {
    try {
        new DateInterval($input);
    } catch (Exception $error) {
        return get_class($error) === 'Exception'
            && strpos($error->getMessage(), 'Unknown or bad format') !== false;
    }
    return false;
}

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    verifySame('C', setlocale(LC_ALL, 'C'), 'fixed C locale');
    verifySame(true, date_default_timezone_set('UTC'), 'fixed UTC timezone');

    verifySame('right/left', sprintf('%2$s/%1$s', 'left', 'right'), 'positional strings');
    verifySame('x:x', sprintf('%1$s:%1$s', 'x'), 'repeated positional argument');
    verifySame('+0012.50|0007', sprintf('%2$+08.2f|%1$04d', 7, 12.5), 'positional sign width precision');
    verifySame('-0012.50', sprintf('%+08.2f', -12.5), 'negative sign and zero padding');
    verifySame('ab    |0007|abc', sprintf('%-6s|%04d|%.3s', 'ab', 7, 'abcdef'), 'alignment width precision');
    verifySame('____ab', sprintf("%'_6s", 'ab'), 'custom padding');
    verifySame('12%', sprintf('%d%%', 12), 'literal percent');

    verifySame('Hello world', strip_tags('<p>Hello <b>world</b></p>'), 'ordinary tag removal');
    verifySame('Hello <b>world</b>', strip_tags('<p>Hello <b>world</b></p>', '<b>'), 'tag allowlist');
    verifySame('<B title="x">ok</B>', strip_tags('<B title="x">ok</B>', '<b>'), 'case insensitive allowlist');
    verifySame('a < b', strip_tags('a < b'), 'less than followed by space');

    foreach (array(
        'http://user:pass@example.com/path',
        'https://user%41:pass%20word@example.com/path?x=1#fragment',
        'http://user.name+tag:pass_word@example.com/'
    ) as $url) {
        verifySame($url, filter_var($url, FILTER_VALIDATE_URL), 'ordinary URL userinfo');
    }

    verifySame('2026-10-26 10:04:56', gmdate('Y-m-d H:i:s', strtotime('2026-10-26 12:34:56 +02:30')), 'positive timezone offset');
    verifySame('2026-10-26 16:04:56', gmdate('Y-m-d H:i:s', strtotime('2026-10-26 12:34:56 -0330')), 'negative compact timezone offset');
    verifySame('2026-10-26 12:34:56', gmdate('Y-m-d H:i:s', strtotime(" \t2026-10-26 12:34:56 UTC \n")), 'date surrounding whitespace');
    foreach (array('21st' => '21', '22nd' => '22', '23rd' => '23', '24th' => '24') as $day => $expected) {
        verifySame('2026-10-' . $expected . ' 12:34:56', gmdate('Y-m-d H:i:s', strtotime($day . ' October 2026 12:34:56 UTC')), 'day suffix ' . $day);
    }
    $interval = new DateInterval(" \tP1DT2H3M4S \n");
    verifySame('1:2:3:4', $interval->format('%d:%h:%i:%s'), 'ISO interval surrounding whitespace');

    for ($byte = 128; $byte <= 255; $byte++) {
        $char = chr($byte);
        $label = ' byte ' . $byte;
        verifySame('A' . $char . '7', sprintf('A' . $char . '%d', 7), 'format literal' . $label);
        verifySame('beforeafter', sprintf('before%' . $char . 'after', 7), 'unknown format specifier' . $label);
        verifySame('A' . $char . 'BC', strip_tags('A' . $char . 'B<b>C</b>'), 'text preservation' . $label);
        verifySame('hidden', strip_tags('<' . $char . '>hidden</' . $char . '>', '<b>'), 'unlisted tag' . $label);

        foreach (array(
            'http://u' . $char . ':p@example.com/',
            'http://u:p' . $char . '@example.com/',
            'http://u%' . $char . '0:p@example.com/',
            'http://u%0' . $char . ':p@example.com/'
        ) as $url) {
            verifySame(false, filter_var($url, FILTER_VALIDATE_URL), 'invalid URL userinfo' . $label);
        }
        $parsed = parse_url('http://u' . $char . ':p@example.com/');
        verifySame('u' . $char, $parsed['user'], 'URL parser preserves user bytes' . $label);

        foreach (array(
            $char . '2026-10-26 12:34:56 UTC',
            '2026-10-26 12:34:56 UTC' . $char,
            '2026-10-26 12:34:56 +' . $char . '2:30',
            '21' . $char . ' October 2026 12:34:56 UTC'
        ) as $input) {
            $parsed = date_parse($input);
            verifySame(true, $parsed['error_count'] > 0, 'invalid date input' . $label);
        }
        verifySame(true, invalidInterval($char . 'P1D'), 'invalid interval prefix' . $label);
        verifySame(true, invalidInterval('P1D' . $char), 'invalid interval suffix' . $label);
    }
    echo "PASS: $checks ctype parsing and ordinary controls\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
} finally {
    restore_error_handler();
}
