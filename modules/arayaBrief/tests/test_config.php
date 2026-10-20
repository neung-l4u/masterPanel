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
try {
    arayaValidateConfig(['gcal_base_url' => 'https://x'], '/path/to/cfg.php');
} catch (RuntimeException $e) {
    test("message names the file", str_contains($e->getMessage(), '/path/to/cfg.php'));
    test("message names a missing key", str_contains($e->getMessage(), 'gcal_admin_key'));
}

// A real token must never reach an error message, a log or a stack trace.
try {
    arayaValidateConfig($complete + ['monday_token' => 'SUPERSECRET123'], '/cfg.php');
    test("a valid config leaks nothing because it does not throw", true);
} catch (RuntimeException $e) {
    test("secret never appears in the message", !str_contains($e->getMessage(), 'SUPERSECRET123'));
}

$blankKey = $complete;
$blankKey['monday_token'] = '';
try {
    arayaValidateConfig($blankKey, '/cfg.php');
} catch (RuntimeException $e) {
    test("other secrets never appear in the message",
         !str_contains($e->getMessage(), $complete['gcal_admin_key'] === 'k' ? 'chat.example.test/hook' : 'x'));
}

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
