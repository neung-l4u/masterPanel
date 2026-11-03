<?php
/* ============================================================
   Test: every id lookup.php prefills exists on the signup form
   php tests/test_lookup_field_ids.php

   The prefill is keyed by element ids on modules/signup/index.php. An id that
   does not exist there fails silently — the field is simply never filled, and
   nothing reports it. This test is what catches a renamed or removed field,
   on either side.
   ============================================================ */

$passed = 0;
$failed = 0;

function check(string $label, callable $assert): void
{
    global $passed, $failed;
    $problem = $assert();
    if ($problem === null) {
        echo "  ok  $label\n";
        $passed++;
    } else {
        echo "  FAIL  $label\n        $problem\n";
        $failed++;
    }
}

$lookupPath = __DIR__ . '/../models/lookup.php';
$signupPath = __DIR__ . '/../../signup/index.php';

echo "lookup.php prefill keys — against the real signup form\n";

check('the signup form is where we expect it', static function () use ($signupPath) {
    return is_file($signupPath) ? null : "not found: $signupPath";
});

// Read the $prefill map out of the source rather than running the endpoint, so
// the test needs no database and no web server.
$lookupSource = (string) file_get_contents($lookupPath);

preg_match('/\$prefill = \[(.*?)\];/s', $lookupSource, $block);
preg_match_all("/'([A-Za-z0-9_]+)'\s*=>/", $block[1] ?? '', $found);
$keys = $found[1] ?? [];

check('the prefill map was found and is not empty', static function () use ($keys) {
    return $keys ? null : 'could not parse $prefill out of models/lookup.php';
});

$signupSource = (string) file_get_contents($signupPath);

foreach ($keys as $key) {
    check("#$key exists on the signup form", static function () use ($key, $signupSource) {
        // Both quote styles, since the form mixes them.
        if (strpos($signupSource, 'id="' . $key . '"') !== false) {
            return null;
        }
        if (strpos($signupSource, "id='" . $key . "'") !== false) {
            return null;
        }
        return "no element with id=\"$key\" in modules/signup/index.php — "
             . 'the prefill would silently do nothing';
    });
}

// City and state are a dependent chain on the signup form: picking a state
// populates the city dropdown, and #city is only a hidden mirror of it.
// Prefilling either out of order desyncs them, so they must stay out.
foreach (['city', 'state'] as $excluded) {
    check("$excluded is deliberately NOT prefilled", static function () use ($excluded, $keys) {
        return in_array($excluded, $keys, true)
            ? "'$excluded' is in \$prefill; it feeds, or is fed by, the other and must be left to the customer"
            : null;
    });
}

check('country is deliberately NOT prefilled', static function () use ($keys) {
    foreach (['country', 'country_code', 'formCountry'] as $name) {
        if (in_array($name, $keys, true)) {
            return "'$name' is in \$prefill; the signup form offers fewer countries than the Lead board";
        }
    }
    return null;
});

echo "\n$passed checks passed";
echo $failed ? ", $failed FAILED\n" : "\n";
exit($failed ? 1 : 0);
