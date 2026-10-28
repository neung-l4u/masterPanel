<?php
/**
 * The sync script's HTTP contract. These guard the boundary the admin page
 * depends on, and the CLI/cron contract it must not disturb.
 *
 * The script is invoked as a subprocess rather than included: it calls exit()
 * and reads superglobals, so including it would end the test run. The HTTP
 * path cannot be entered from CLI at all, so the switches it adds are asserted
 * against the source; what is actually executed here is the CLI path, which is
 * the one that must not change.
 */
date_default_timezone_set("Asia/Bangkok");

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

$script = realpath(__DIR__ . '/../sync/syncMondayMirror.php');

function runCli(array $args): string {
    global $script;
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script);
    foreach ($args as $a) { $cmd .= ' ' . escapeshellarg($a); }
    return (string) shell_exec($cmd . ' 2>&1');
}

echo "== CLI contract is preserved ==\n";

// "123" strips to an empty code, so the filter rejects it before any Monday
// call. Do NOT use a letters-only code such as "ZZ" here: it survives the
// A-Z strip, and before the membership check existed it reached the sync and
// ran a full live subscriptions pass just to report "unknown country".
$out = runCli(['123']);
test("a filter with no usable code still fatals",
     strpos($out, 'country filter given but no valid country codes') !== false);
test("fatal output is not JSON",
     strpos(trim($out), '{') !== 0);

echo "== an unknown country is rejected before any Monday call ==\n";

// The regression this pins: "ZZ" passes the A-Z strip, so it used to reach
// upDownRunSync and was only reported as "unknown country" AFTER a full live
// sync had run -- about 90 seconds of Monday API calls, and a rewrite of the
// subscriptions table, to answer a typo. It must now cost nothing.
$started = microtime(true);
$out     = runCli(['ZZ']);
$elapsed = microtime(true) - $started;

test("an all-letters unknown code fatals",
     strpos($out, 'unknown country code') !== false
     || strpos($out, 'country filter given but no valid country codes') !== false);
test("it never reports a sync summary",
     strpos($out, 'ud mirror sync ok') === false
     && strpos($out, 'ud mirror sync with errors') === false);
// A real run takes ~90s. Anything near that means Monday was called.
test("it returns immediately rather than syncing", $elapsed < 15);

// A partially valid filter must be refused outright: syncing the valid subset
// would quietly do less than the caller asked for.
$out = runCli(['TH', 'ZZ']);
test("a mix of known and unknown codes fatals",
     strpos($out, 'unknown country code') !== false);
test("the mixed-filter fatal names the offending code",
     strpos($out, 'ZZ') !== false);
test("a mixed filter syncs nothing",
     strpos($out, 'ud mirror sync ok') === false
     && strpos($out, 'ud mirror sync with errors') === false);

echo "== HTTP affordances are present and gated ==\n";

$src = file_get_contents($script);

test("sets a time limit on the HTTP path",
     preg_match('/set_time_limit\(\s*300\s*\)/', $src) === 1);
test("json output is opt-in via ?format",
     strpos($src, "\$_GET['format']") !== false);
test("subscriptions pass is opt-out, not opt-in",
     strpos($src, "\$_GET['subs']") !== false
     && preg_match('/\$withSubs\s*=\s*!\(/', $src) === 1);
test("both switches are declared outside the HTTP block so CLI has defaults",
     preg_match('/\$wantsJson\s*=\s*false;\s*\n\s*\$withSubs\s*=\s*true;/', $src) === 1);

echo "== upDownRunSync honours the subscriptions flag ==\n";

require_once __DIR__ . '/../sync/mondayClient.php';
require_once __DIR__ . '/../sync/mondayMirror.php';

$ref    = new ReflectionFunction('upDownRunSync');
$params = $ref->getParameters();

test("upDownRunSync takes a third parameter", count($params) === 3);
test("third parameter is \$withSubscriptions",
     isset($params[2]) && $params[2]->getName() === 'withSubscriptions');
test("it defaults to true so existing callers are unchanged",
     isset($params[2]) && $params[2]->isDefaultValueAvailable()
     && $params[2]->getDefaultValue() === true);

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
