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
// Asserted as the exact CLI line cron greps, not merely "does not start with
// {". The weaker form could not fail: a fatal always begins with its "[" stamp.
test("the CLI fatal keeps its exact timestamped format",
     preg_match('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \+07\] ud mirror sync FATAL: /m',
                $out) === 1);

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
//
// Deliberately NOT ['TH', 'ZZ']: if this guard ever regresses, that fixture
// runs a real TH sync plus the subscriptions pass, so the test would do
// production writes on the very run that reports it is broken. Two unknown
// codes exercise the same branch and can never sync anything.
$out = runCli(['ZZ', 'QQ']);
test("a filter of unknown codes fatals",
     strpos($out, 'unknown country code') !== false);
test("the fatal names every offending code",
     strpos($out, 'ZZ') !== false && strpos($out, 'QQ') !== false);
test("an unknown-code filter syncs nothing",
     strpos($out, 'ud mirror sync ok') === false
     && strpos($out, 'ud mirror sync with errors') === false);

// The mixed case (one real country, one typo) is what staff actually hit, and
// it is the one fixture here that WOULD write if the guard regressed -- AU is
// the largest board. Timed, so a regression shows up as a failure in seconds
// rather than as a silent live sync nobody notices until the log is read.
$started = microtime(true);
$out     = runCli(['AU', 'ZZ']);
$elapsed = microtime(true) - $started;

test("a mix of known and unknown codes is refused, not partially synced",
     strpos($out, 'unknown country code(s): ZZ') !== false
     && strpos($out, 'ud mirror sync ok') === false);
test("the mixed filter is refused before any Monday call", $elapsed < 15);

echo "== HTTP affordances are present and gated ==\n";

$src = file_get_contents($script);

// Strip comments before asserting anything about the source: a bare
// strpos/preg_match over the raw file passes just as happily when the thing it
// is looking for sits in a comment, which makes the whole block decorative.
$code = preg_replace('!/\*.*?\*/!s', '', $src);
$code = preg_replace('!//.*!', '', $code);

// Everything the HTTP path may change has to live inside `if (!$isCli) { ... }`.
// Isolate that block so presence and GATING are tested together -- the earlier
// version of these tests would have passed with the switches at top level,
// which is exactly the bug that would leak them to the CLI.
$httpBlock = '';
if (preg_match('/if\s*\(!\$isCli\)\s*\{/', $code, $m, PREG_OFFSET_CAPTURE)) {
    $start = $m[0][1] + strlen($m[0][0]);
    $depth = 1;
    for ($i = $start; $i < strlen($code) && $depth > 0; $i++) {
        if ($code[$i] === '{') { $depth++; }
        elseif ($code[$i] === '}') { $depth--; }
        if ($depth > 0) { $httpBlock .= $code[$i]; }
    }
}
test("the !\$isCli block was located", $httpBlock !== '');

// Outside the HTTP block: everything the CLI path executes.
$outsideHttp = str_replace($httpBlock, '', $code);

test("set_time_limit(300) is inside the HTTP block",
     preg_match('/set_time_limit\(\s*300\s*\)/', $httpBlock) === 1);
test("set_time_limit is NOT reachable from the CLI path",
     preg_match('/set_time_limit\s*\(/', $outsideHttp) === 0);

test("?format is read only inside the HTTP block",
     strpos($httpBlock, "\$_GET['format']") !== false
     && strpos($outsideHttp, "\$_GET['format']") === false);
test("?subs is read only inside the HTTP block",
     strpos($httpBlock, "\$_GET['subs']") !== false
     && strpos($outsideHttp, "\$_GET['subs']") === false);
test("?subsonly is read only inside the HTTP block",
     strpos($httpBlock, "\$_GET['subsonly']") !== false
     && strpos($outsideHttp, "\$_GET['subsonly']") === false);

test("the subscriptions pass is opt-OUT, so omitting ?subs still runs it",
     preg_match('/\$withSubs\s*=\s*!\(/', $httpBlock) === 1);
test("all three switches have CLI defaults declared outside the HTTP block",
     preg_match('/\$wantsJson\s*=\s*false;/', $outsideHttp) === 1
     && preg_match('/\$withSubs\s*=\s*true;/', $outsideHttp) === 1
     && preg_match('/\$subsOnly\s*=\s*false;/', $outsideHttp) === 1);

// The regression this pins: SUBSONLY was briefly a magic country code, which
// meant `php syncMondayMirror.php SUBSONLY` -- one typo away from a real
// country argument -- ran a live subscriptions pass and rewrote the table.
// The subscriptions-only run must be reachable over HTTP and nowhere else.
// Case-sensitive and bounded: the ?subsonly=1 PARAMETER is the replacement and
// must survive, while the all-caps magic country code must not come back.
test("SUBSONLY is not honoured as a country code anywhere",
     strpos($code, 'SUBSONLY') === false);

// Needed from here on for UP_DOWN_PROJECT_BOARDS; mondayMirror.php is pulled
// in further down, where the upDownRunSync signature is checked.
require_once __DIR__ . '/../sync/mondayClient.php';

echo "== ?subsonly=1 resolves to NO countries ==\n";

// The regression this pins, and it is the dangerous direction: $subsOnly was
// only read inside `if ($only !== null)`, and $only stays null when no
// countries parameter is sent. So ?subsonly=1 on its own fell through to
// upDownRunSync($db, null, ...), which treats null as "no filter" and syncs
// ALL SIX COUNTRIES -- the heaviest run there is, from the one parameter whose
// entire meaning is "none of them". The admin page sends exactly that request
// after syncing each country individually, so every full run would have ended
// by silently re-syncing everything.
//
// Asserted on the source, because reaching this branch for real costs a live
// Monday sync. The assignment must sit OUTSIDE the `if ($only !== null)` block
// -- that is the whole fix.
$subsOnlyAssign = preg_match('/if\s*\(\$subsOnly\)\s*\{\s*\$only\s*=\s*\[\];\s*\}/', $code, $mm, PREG_OFFSET_CAPTURE);
test("subsonly forces an empty country filter", $subsOnlyAssign === 1);

if ($subsOnlyAssign === 1) {
    $assignAt = $mm[0][1];
    $filterAt = strpos($code, 'if ($only !== null) {');
    test("it is assigned BEFORE the filter block, not inside it",
         $filterAt !== false && $assignAt < $filterAt);
} else {
    test("it is assigned BEFORE the filter block, not inside it", false);
}

// Behavioural cover for the same rule. The script's resolution lines are
// replayed here against fake $_GET inputs, so what reaches upDownRunSync is
// asserted directly rather than inferred from a grep. Kept deliberately
// literal: if the script's logic changes, this copy must be updated with it,
// and the grep above is what notices.
$resolveOnly = static function (array $get): string {
    $subsOnly = isset($get['subsonly']) && $get['subsonly'] === '1';

    $only = null;
    if (!empty($get['countries']) && is_string($get['countries'])) {
        $only = explode(',', $get['countries']);
    }
    if ($subsOnly) {
        $only = [];
    }

    // null is what upDownRunSync turns into every configured country.
    if ($only === null) {
        return 'ALL';
    }

    $codes = array_values(array_filter(array_map(
        static function ($c) {
            return preg_replace('/[^A-Z]/', '', strtoupper(trim((string) $c)));
        },
        $only
    )));
    $ok = array_values(array_filter(
        $codes,
        static function ($c) { return isset(UP_DOWN_PROJECT_BOARDS[$c]); }
    ));

    if ($ok === [] && !$subsOnly) {
        return 'FATAL';
    }
    return $ok === [] ? 'SUBS_ONLY' : implode(',', $ok);
};

test("?subsonly=1 alone syncs no countries",
     $resolveOnly(['subsonly' => '1']) === 'SUBS_ONLY');
test("?subsonly=1&subs=1 (what the page sends) syncs no countries",
     $resolveOnly(['subsonly' => '1', 'subs' => '1']) === 'SUBS_ONLY');
test("a country parameter still selects that country",
     $resolveOnly(['countries' => 'TH']) === 'TH');
test("no parameters at all still means every country",
     $resolveOnly([]) === 'ALL');
test("an unknown country still fatals",
     $resolveOnly(['countries' => 'ZZ']) === 'FATAL');

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
