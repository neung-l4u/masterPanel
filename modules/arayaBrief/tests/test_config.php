<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../models/config.php';

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

echo "== arayaValidateConfig ==\n";

$complete = [
    'gcal_base_url'    => 'https://example.test',
    'gcal_admin_key'   => 'k',
    'monday_token'     => 't',
    'chat_webhook_url' => 'https://chat.example.test/hook',
];

$ok = true;
try { arayaValidateConfig($complete, '/cfg.php'); } catch (Throwable $e) { $ok = false; }
test("a complete config passes", $ok);

foreach (array_keys($complete) as $key) {
    $missing = $complete;
    unset($missing[$key]);
    $threw = false;
    try { arayaValidateConfig($missing, '/cfg.php'); } catch (RuntimeException $e) { $threw = true; }
    test("missing {$key} throws", $threw);

    $blank = $complete;
    $blank[$key] = '';
    $threw = false;
    try { arayaValidateConfig($blank, '/cfg.php'); } catch (RuntimeException $e) { $threw = true; }
    test("blank {$key} throws", $threw);
}

$threw = false;
try { arayaValidateConfig('not an array', '/cfg.php'); } catch (RuntimeException $e) { $threw = true; }
test("a non-array throws", $threw);

// The message must help whoever hit it, and must never echo a secret.
$threw = false;
try {
    arayaValidateConfig(['gcal_base_url' => 'https://x'], '/path/to/cfg.php');
} catch (RuntimeException $e) {
    $threw = true;
    test("message names the file", str_contains($e->getMessage(), '/path/to/cfg.php'));
    test("message names a missing key", str_contains($e->getMessage(), 'gcal_admin_key'));
}
test("the incomplete config actually threw, so the assertions above ran", $threw);

// Every key must be a non-empty string, not merely truthy.
foreach (array_keys($complete) as $key) {
    foreach (['whitespace-only' => "   \t\n", 'nbsp-only' => "\xC2\xA0", 'true' => true, 'int' => 123, 'array' => ['x']] as $label => $bad) {
        $cfg = $complete;
        $cfg[$key] = $bad;
        $threw = false;
        try { arayaValidateConfig($cfg, '/cfg.php'); } catch (RuntimeException $e) { $threw = true; }
        test("{$label} {$key} throws", $threw);
    }
}

// The message for a whitespace-only secret must not contain it.
$padded = $complete;
$padded['monday_token'] = "  \xC2\xA0  ";
$threw = false;
try {
    arayaValidateConfig($padded, '/cfg.php');
} catch (RuntimeException $e) {
    $threw = true;
    test("whitespace-only secret is not echoed", !str_contains($e->getMessage(), "  \xC2\xA0  "));
    test("whitespace-only secret message names the key", str_contains($e->getMessage(), 'monday_token'));
}
test("the whitespace-only config actually threw, so the assertions above ran", $threw);

// gcal_base_url is returned without trailing slashes; other values are untouched.
$slash = $complete;
$slash['gcal_base_url'] = 'https://example.test/';
$out = arayaValidateConfig($slash, '/cfg.php');
test("a trailing slash is accepted and stripped", $out['gcal_base_url'] === 'https://example.test');

$slashes = $complete;
$slashes['gcal_base_url'] = 'https://example.test///';
$out = arayaValidateConfig($slashes, '/cfg.php');
test("several trailing slashes are all stripped", $out['gcal_base_url'] === 'https://example.test');

$out = arayaValidateConfig($complete, '/cfg.php');
test("a valid config passes through unchanged", $out === $complete);

// A real token must never reach an error message, a log or a stack trace.
// The config below is invalid, so validation throws and the catch really runs:
// the secrets are on the keys that ARE set, while the missing key is what
// triggers the throw. Asserting inside a block that never executes would pass
// no matter what the message contained.
$leaky = [
    'gcal_base_url'    => 'https://example.test',
    'gcal_admin_key'   => 'ADMINKEY_SUPERSECRET',
    'monday_token'     => 'MONDAY_SUPERSECRET',
    // chat_webhook_url deliberately absent -> throws.
];
$threw = false;
try {
    arayaValidateConfig($leaky, '/cfg.php');
} catch (RuntimeException $e) {
    $threw = true;
    $msg = $e->getMessage();
    test("the admin key never appears in the message", !str_contains($msg, 'ADMINKEY_SUPERSECRET'));
    test("the monday token never appears in the message", !str_contains($msg, 'MONDAY_SUPERSECRET'));
    test("the message still names the missing key", str_contains($msg, 'chat_webhook_url'));
}
test("the leaky config actually threw, so the assertions above ran", $threw);

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
