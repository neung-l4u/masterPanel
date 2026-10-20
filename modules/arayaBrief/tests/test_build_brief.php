<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../models/buildBrief.php';

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

const TODAY = '2026-10-20';

/** A matched pair with sane live defaults, overridable per test. */
function mk(array $monday = [], array $gcal = []): array {
    return [
        'monday' => array_merge([
            'name' => 'Test Shop', 'live_status' => 'LIVE', 'setup_status' => 'Done',
            'po' => 'Aim', 'register_date' => '2026-01-01', 'live_date' => '2026-06-01',
            'reason_for_cancel' => '', 'note' => '',
            'calendar_id' => 'cal-x', 'email' => 'a@x.com',
        ], $monday),
        'gcal' => array_merge([
            'shop_id' => 'g1', 'shop_name' => 'Test Shop', 'remaining_minutes' => 200,
            'allocation' => 250, 'billing_day' => 15, 'days_until_refresh' => 26,
            'currency' => 'AUD', 'closed_periods' => [],
        ], $gcal),
        'matched_by' => 'calendar',
    ];
}
function res(array $matched, array $um = [], array $ug = []): array {
    return ['matched' => $matched, 'unmatchedMonday' => $um, 'unmatchedGcal' => $ug];
}

echo "== overview counts ==\n";
$b = arayaBuildBrief(res([
    mk(['live_status' => 'LIVE']), mk(['live_status' => 'LIVE']),
    mk(['live_status' => 'Waiting to cancel']),
    mk(['live_status' => 'Not Live']), mk(['live_status' => 'Testing']),
    mk(['live_status' => 'Ready to LIVE']), mk(['live_status' => 'Waiting to onboard']),
    mk(['live_status' => 'Stuck']), mk(['live_status' => 'Ghosted']), mk(['live_status' => 'Pause']),
    mk(['live_status' => 'Canceled']), mk(['live_status' => 'Canceled']),
    mk(['live_status' => 'Cancel Before LIVE']),
]), TODAY);
test("live count", $b['overview']['live'] === 2);
test("pending cancel count", $b['overview']['pendingCancel'] === 1);
test("setup bucket count", $b['overview']['setup'] === 7);
test("cancelled total", $b['overview']['cancelled'] === 3);
test("cancelled split: canceled", $b['overview']['canceledAfterLive'] === 2);
test("cancelled split: before live", $b['overview']['cancelBeforeLive'] === 1);
test("total counts every item", $b['overview']['total'] === 13);

$b = arayaBuildBrief(res([mk(['live_status' => ''])]), TODAY);
test("blank status counts only toward the total", $b['overview']['total'] === 1 && $b['overview']['live'] === 0);
test("blank status is a data gap",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'blank_status')) === 1);

echo "== live section ==\n";
$b = arayaBuildBrief(res([
    mk(['name' => 'Rich'],   ['remaining_minutes' => 500]),
    mk(['name' => 'Broke'],  ['remaining_minutes' => -109]),
    mk(['name' => 'Middle'], ['remaining_minutes' => 60]),
]), TODAY);
test("sorted by minutes ascending, most urgent first",
     array_column($b['live'], 'name') === ['Broke', 'Middle', 'Rich']);
test("negative minutes stay negative", $b['live'][0]['remaining_minutes'] === -109);

$b = arayaBuildBrief(res([mk([], ['remaining_minutes' => 152])]), TODAY);
test("minutes carried through", $b['live'][0]['remaining_minutes'] === 152);

// Lifespan from live_date.
$b = arayaBuildBrief(res([mk(['live_date' => '2026-06-01'])]), TODAY);
test("lifespan months", $b['live'][0]['lifespan']['months'] === 4);
test("lifespan days", $b['live'][0]['lifespan']['days'] === 19);

$b = arayaBuildBrief(res([mk(['live_date' => ''])]), TODAY);
test("missing live date is flagged on the row", $b['live'][0]['lifespan'] === null);
test("missing live date is a data gap",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'no_live_date')) === 1);

// A live shop with no gcal match still appears.
$b = arayaBuildBrief(res([], [['name' => 'Ghost Live', 'live_status' => 'LIVE', 'live_date' => '2026-06-01']]), TODAY);
test("an unmatched live shop is still listed", count($b['live']) === 1);
test("it is marked as having no gcal data", $b['live'][0]['gcal_missing'] === true);
test("and it is a data gap", count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'unmatched_monday')) === 1);

echo "== upsell: <=15 days AND <70 minutes ==\n";
foreach ([
    [15, 69, true,  'at both boundaries'],
    [15, 70, false, '70 minutes is not under 70'],
    [16, 69, false, '16 days is not within 15'],
    [14, 1,  true,  'well inside'],
    [0,  69, true,  'refresh today'],
    [15, -5, true,  'negative is under 70'],
] as [$days, $mins, $want, $case]) {
    $b = arayaBuildBrief(res([mk([], ['days_until_refresh' => $days, 'remaining_minutes' => $mins])]), TODAY);
    test("upsell {$case}", (count($b['upsell']) === 1) === $want);
}

// Shops that have given notice are excluded but explained.
foreach (['Waiting to cancel', 'Canceled', 'Cancel Before LIVE'] as $status) {
    $b = arayaBuildBrief(res([mk(['live_status' => $status], ['days_until_refresh' => 5, 'remaining_minutes' => 10])]), TODAY);
    test("{$status} is not an upsell target", count($b['upsell']) === 0);
    test("{$status} exclusion is explained", count($b['upsellExcluded']) === 1);
    test("{$status} exclusion carries a reason",
         count($b['upsellExcluded']) === 1 && is_string($b['upsellExcluded'][0]['reason']) && $b['upsellExcluded'][0]['reason'] !== '');
}

$b = arayaBuildBrief(res([mk([], ['billing_day' => 0, 'days_until_refresh' => null, 'remaining_minutes' => 10])]), TODAY);
test("no billing day is not an upsell target", count($b['upsell']) === 0);
test("no billing day is a data gap",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'no_billing_day')) === 1);

echo "== idle rule A: live >=10 days and used <15% ==\n";
foreach ([
    ['2026-10-10', 250, 230, true,  'day 10, used 8%'],
    ['2026-10-11', 250, 230, false, 'day 9 is too early'],
    // 38 of 250 is 15.2% used, which is NOT under the 15% line. This row and
    // the next one bracket the threshold from either side.
    ['2026-10-01', 250, 212, false, 'used 15.2%, just over the line'],
    ['2026-10-01', 250, 213, true,  'used 14.8%, just under the line'],
    ['2026-10-01', 250, 100, false, 'used 60%, busy'],
    ['2026-10-01', 1000, 900, true, 'big allocation, used 10%'],
    ['2026-10-01', 1000, 800, false, 'big allocation, used 20%'],
    ['2026-10-01', 200, 170, false, 'exactly 15% used is not under the line'],
    ['2026-10-01', 250, 250, true,  'fraction 0 is valid and idle'],
] as [$liveDate, $alloc, $remaining, $want, $case]) {
    $b = arayaBuildBrief(res([mk(['live_date' => $liveDate], ['allocation' => $alloc, 'remaining_minutes' => $remaining])]), TODAY);
    test("idleNew {$case}", (count($b['idleNew']) === 1) === $want);
}

$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['allocation' => 0, 'remaining_minutes' => 0])]), TODAY);
test("zero allocation is never divided by", count($b['idleNew']) === 0);
test("zero allocation is a data gap",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'no_allocation')) === 1);

// A missing key and a null allocation behave like zero.
foreach (['missing key' => false, 'null' => true] as $label => $setNull) {
    $m = mk(['live_date' => '2026-01-01'], ['remaining_minutes' => 10]);
    if ($setNull) { $m['gcal']['allocation'] = null; } else { unset($m['gcal']['allocation']); }
    $b = arayaBuildBrief(res([$m]), TODAY);
    test("{$label} allocation is never divided by", count($b['idleNew']) === 0);
    test("{$label} allocation is a data gap",
         count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'no_allocation')) === 1);
}

// Balance above allocation (top-up): excluded with a gap, not clamped or flagged.
$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['allocation' => 250, 'remaining_minutes' => 300])]), TODAY);
test("a topped-up shop is not reported idle", count($b['idleNew']) === 0);
$gp = array_values(array_filter($b['gaps'], fn($g) => $g['kind'] === 'no_allocation'));
test("a topped-up shop raises no_allocation", count($gp) === 1);
test("the top-up gap mentions both numbers",
     count($gp) === 1 && strpos($gp[0]['detail'], '300') !== false && strpos($gp[0]['detail'], '250') !== false);

echo "== idle rule C: latest period under 30% of the prior three ==\n";
$quiet = [
    ['period_end' => '2026-09-30', 'allocation' => 250, 'used_minutes' => 10],
    ['period_end' => '2026-08-31', 'allocation' => 250, 'used_minutes' => 100],
    ['period_end' => '2026-07-31', 'allocation' => 250, 'used_minutes' => 110],
    ['period_end' => '2026-06-30', 'allocation' => 250, 'used_minutes' => 90],
];
$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['closed_periods' => $quiet])]), TODAY);
test("a shop that went quiet is flagged", count($b['idleQuiet']) === 1);

$busy = $quiet; $busy[0]['used_minutes'] = 95;
$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['closed_periods' => $busy])]), TODAY);
test("a shop still using normally is not flagged", count($b['idleQuiet']) === 0);

// Mean of 100, 110, 90 is 100; 30 is exactly 30% and so not under it.
$edge = $quiet; $edge[0]['used_minutes'] = 30;
$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['closed_periods' => $edge])]), TODAY);
test("exactly 30% is not flagged", count($b['idleQuiet']) === 0);
$edge[0]['used_minutes'] = 29;
$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['closed_periods' => $edge])]), TODAY);
test("just under 30% is flagged", count($b['idleQuiet']) === 1);

$twoPriors = array_slice($quiet, 0, 3);
$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['closed_periods' => $twoPriors])]), TODAY);
test("fewer than 3 prior periods is not evaluated by rule C", count($b['idleQuiet']) === 0);

$neverUsed = [
    ['period_end' => '2026-09-30', 'allocation' => 250, 'used_minutes' => 0],
    ['period_end' => '2026-08-31', 'allocation' => 250, 'used_minutes' => 0],
    ['period_end' => '2026-07-31', 'allocation' => 250, 'used_minutes' => 0],
    ['period_end' => '2026-06-30', 'allocation' => 250, 'used_minutes' => 0],
];
$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['allocation' => 250, 'remaining_minutes' => 250, 'closed_periods' => $neverUsed])]), TODAY);
test("a shop that never used anything is not a rule C finding", count($b['idleQuiet']) === 0);
test("it is a rule A finding instead", count($b['idleNew']) === 1);

// A shop matching both appears only under C, the more specific finding.
$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['allocation' => 250, 'remaining_minutes' => 240, 'closed_periods' => $quiet])]), TODAY);
test("a shop matching A and C appears only under C",
     count($b['idleQuiet']) === 1 && count($b['idleNew']) === 0);

// A malformed closed period makes rule C not evaluable rather than shifting onto older data.
$bad = $quiet; unset($bad[1]['used_minutes']);
$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['closed_periods' => $bad])]), TODAY);
test("a malformed closed period is not evaluated by rule C", count($b['idleQuiet']) === 0);

echo "== idle rules only consider live shops ==\n";
$b = arayaBuildBrief(res([mk(['live_status' => 'Waiting to cancel', 'live_date' => '2026-01-01'], ['allocation' => 250, 'remaining_minutes' => 250])]), TODAY);
test("a cancelling shop is not an idle finding", count($b['idleNew']) === 0);

// A LIVE shop with unusable minutes raises a gap but is still listed.
foreach (['null' => null, 'non-numeric' => 'abc'] as $label => $val) {
    $b = arayaBuildBrief(res([mk([], ['remaining_minutes' => $val])]), TODAY);
    test("{$label} remaining minutes still lists the shop with null minutes",
         count($b['live']) === 1 && $b['live'][0]['remaining_minutes'] === null);
    test("{$label} remaining minutes raises no_remaining_minutes",
         count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'no_remaining_minutes' && $g['name'] !== '' && $g['detail'] !== '')) === 1);
}
$b = arayaBuildBrief(res([mk(['live_status' => 'Waiting to cancel'], ['remaining_minutes' => null])]), TODAY);
test("no_remaining_minutes is raised for LIVE shops only",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'no_remaining_minutes')) === 0);

echo "== setup section ==\n";
$b = arayaBuildBrief(res([
    mk(['name' => 'Newer', 'live_status' => 'Not Live', 'setup_status' => 'Not Started', 'register_date' => '2026-09-20', 'po' => 'Aim', 'note' => 'waiting on Fresha']),
    mk(['name' => 'Older', 'live_status' => 'Not Live', 'setup_status' => 'In Progress', 'register_date' => '2026-04-16', 'po' => 'Namva', 'note' => '']),
]), TODAY);
test("longest stalled first", array_column($b['setup'], 'name') === ['Older', 'Newer']);
test("days stalled computed", $b['setup'][0]['days_stalled'] === 187);
test("setup status carried", $b['setup'][0]['setup_status'] === 'In Progress');
test("owner carried", $b['setup'][0]['po'] === 'Namva');
test("empty note is empty", $b['setup'][0]['note'] === '');
test("note carried when present", $b['setup'][1]['note'] === 'waiting on Fresha');

$b = arayaBuildBrief(res([mk(['live_status' => 'Not Live', 'register_date' => ''])]), TODAY);
test("missing register date sorts last without crashing", $b['setup'][0]['days_stalled'] === null);
test("missing register date is a data gap",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'no_register_date')) === 1);

echo "== cancelling section ==\n";
$b = arayaBuildBrief(res([mk(['name' => 'Me Ya Wi', 'live_status' => 'Waiting to cancel', 'live_date' => '2026-07-21', 'po' => 'Noey', 'reason_for_cancel' => ''])]), TODAY);
test("one cancelling shop", count($b['cancelling']) === 1);
test("blank reason is reported as unrecorded", $b['cancelling'][0]['reason'] === '');
test("lifespan still computed", $b['cancelling'][0]['lifespan']['months'] === 2);

$b = arayaBuildBrief(res([mk(['live_status' => 'Waiting to cancel', 'reason_for_cancel' => 'Save Cost'])]), TODAY);
test("a recorded reason is carried", $b['cancelling'][0]['reason'] === 'Save Cost');

echo "== gaps section ==\n";
$b = arayaBuildBrief(res(
    [mk()],
    [['name' => 'No gcal row', 'live_status' => 'LIVE']],
    [['shop_id' => 'orphan', 'shop_name' => 'No Monday item']]
), TODAY);
test("unmatched Monday shop is a gap",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'unmatched_monday')) === 1);
test("unmatched gcal shop is a gap",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'unmatched_gcal')) === 1);
test("every gap names a shop",
     count(array_filter($b['gaps'], fn($g) => ($g['name'] ?? '') === '')) === 0);

// Thai name ending in U+0E20 must survive byte-for-byte (bare trim() corrupts it).
$thai = "ร้านลาภ";
$b = arayaBuildBrief(res([mk(['name' => $thai])]), TODAY);
test("a Thai name ending in ภ survives byte-for-byte", $b['live'][0]['name'] === $thai);
$b = arayaBuildBrief(res([mk(['name' => "  {$thai} "])]), TODAY);
test("a padded Thai name is trimmed without corruption", $b['live'][0]['name'] === $thai);
// NBSP padding (U+00A0 = C2 A0) is stripped by the safe trim; bare trim() leaves it.
$b = arayaBuildBrief(res([mk(['name' => "\u{00A0}{$thai}\u{00A0}"])]), TODAY);
test("an NBSP-padded Thai name is trimmed exactly", $b['live'][0]['name'] === $thai);

// A status outside every bucket is a gap but still counts in the total.
$b = arayaBuildBrief(res([mk(['live_status' => 'Mystery'])]), TODAY);
test("unknown status raises unknown_status",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'unknown_status')) === 1);
test("unknown status still counts toward the total", $b['overview']['total'] === 1);

echo "== empty input ==\n";
$b = arayaBuildBrief(res([]), TODAY);
test("no shops is not an error", $b['overview']['total'] === 0);
test("sections are empty arrays, not null",
     $b['live'] === [] && $b['upsell'] === [] && $b['gaps'] === []);
foreach (['live', 'upsell', 'upsellExcluded', 'idleNew', 'idleQuiet', 'setup', 'cancelling', 'gaps'] as $k) {
    test("section '{$k}' is present and an array", array_key_exists($k, $b) && is_array($b[$k]));
}
test("generated_for is the given day", $b['generated_for'] === TODAY);

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
