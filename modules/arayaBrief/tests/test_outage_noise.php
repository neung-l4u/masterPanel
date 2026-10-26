<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../models/loadBrief.php';

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

/** Five live Monday shops and nothing from gcal: what an outage looks like. */
function arayaOutageBrief(bool $flag): array
{
    $monday = [];
    foreach (['A', 'B', 'C', 'D', 'E'] as $n) {
        $monday[] = [
            'name' => $n, 'live_status' => 'LIVE', 'setup_status' => 'Done',
            'po' => 'Aim', 'register_date' => '2026-01-01', 'live_date' => '2026-05-01',
            'reason_for_cancel' => '', 'note' => '',
            'calendar_id' => 'cal-' . $n, 'email' => $n . '@x.com',
        ];
    }
    $brief = arayaBuildBrief(arayaMatchShops($monday, []), '2026-10-20');
    $brief['gcal_unavailable'] = $flag;
    return $brief;
}

function arayaKinds(array $brief): array
{
    return array_column($brief['gaps'] ?? [], 'kind');
}

echo "== without the flag, nothing is suppressed ==\n";
// The per-shop verdict is correct in isolation, so it must survive when gcal
// really did answer and really had no match for these shops.
$before = arayaOutageBrief(false);
$kept = arayaSuppressOutageNoise($before);
test("unmatched_monday still raised when gcal was up",
     count(array_filter(arayaKinds($kept), fn($k) => $k === 'unmatched_monday')) === 5);
test("the live rows keep their gcal_missing flag",
     count(array_filter($kept['live'], fn($r) => !empty($r['gcal_missing']))) === 5);

echo "== with the flag, the outage noise goes ==\n";
$flagged = arayaSuppressOutageNoise(arayaOutageBrief(true));
test("no unmatched_monday gap survives an outage",
     !in_array('unmatched_monday', arayaKinds($flagged), true));
test("the gaps list is not merely emptied of everything",
     is_array($flagged['gaps']));
test("no live row still claims it is missing from gcal",
     count(array_filter($flagged['live'], fn($r) => !empty($r['gcal_missing']))) === 0);
test("the shops themselves are NOT dropped from the live list",
     count($flagged['live']) === 5);
test("the outage flag is left set, so the banner still fires",
     $flagged['gcal_unavailable'] === true);

echo "== gaps of other kinds survive an outage ==\n";
// Only the "no gcal match" verdict is meaningless during an outage. A missing
// register date is a board problem and is still worth acting on.
$mixed = arayaOutageBrief(true);
$mixed['gaps'][] = ['kind' => 'no_register_date', 'name' => 'Stalled Shop', 'detail' => 'x'];
$mixed['gaps'][] = ['kind' => 'blank_status', 'name' => 'Odd Shop', 'detail' => 'y'];
$out = arayaSuppressOutageNoise($mixed);
$kinds = arayaKinds($out);
test("a no_register_date gap survives", in_array('no_register_date', $kinds, true));
test("a blank_status gap survives", in_array('blank_status', $kinds, true));
test("and the unmatched_monday flood is still gone",
     !in_array('unmatched_monday', $kinds, true));
test("exactly the two real gaps remain", count($kinds) === 2);

echo "== gaps keys stay well formed ==\n";
test("gaps is a list with sequential keys after filtering",
     array_keys($out['gaps']) === range(0, count($out['gaps']) - 1));

echo "== an empty brief is not an error ==\n";
$empty = ['gcal_unavailable' => true, 'gaps' => [], 'live' => []];
$r = arayaSuppressOutageNoise($empty);
test("empty gaps and live stay empty arrays", $r['gaps'] === [] && $r['live'] === []);
$noKeys = ['gcal_unavailable' => true];
$r = arayaSuppressOutageNoise($noKeys);
test("absent keys do not raise a warning", is_array($r));

echo "== call errors: degrading without the credential leaking ==\n";
$errLog = tempnam(sys_get_temp_dir(), 'araya');
$oldLog = ini_set('error_log', $errLog);
$got = arayaTryFetchCallErrors(['gcal_base_url' => 'http://127.0.0.1:9', 'gcal_admin_key' => 'SECRET-KEY-123'], false);
ini_set('error_log', $oldLog === false ? '' : $oldLog);
$logged = (string) file_get_contents($errLog);
@unlink($errLog);
test("an unreachable endpoint gives null, not an exception", $got === null);
test("null, not [], so 'could not ask' differs from 'no errors'", $got !== []);
test("the failure is logged", str_contains($logged, 'call-errors fetch failed'));
test("the admin key is not in the log", !str_contains($logged, 'SECRET-KEY-123'));
test("the host is not in the log", !str_contains($logged, '127.0.0.1'));

echo "== call errors: the fixture path ==\n";
$fx = arayaLoadBrief(true);
test("the fixture brief carries call errors", count($fx['callErrors']) >= 2);
test("the fixture includes a null recording", in_array(null, array_column($fx['callErrors'], 'recording_url'), true));
test("the fixture is not flagged unavailable", $fx['call_errors_unavailable'] === false);
test("the fixture has a shop without calls data", in_array(null, array_column($fx['live'], 'calls'), true));

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
