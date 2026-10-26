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

// Thai name ending in U+0E20 must survive byte-for-byte. Default trim() does
// NOT corrupt this one -- only a trim() with \xA0 in its character list does,
// by eating the trailing A0 byte. The NBSP-padded case below is what catches
// bare trim(), which leaves the non-breaking space in place.
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

echo "== live date falls back to gcal signup_date ==" . PHP_EOL;
// The tracker's Live Date is blank on most real shops, which switched the idle
// rules off for the whole fleet. gcal's signup date is the next best evidence.
$b = arayaBuildBrief(res([mk(['live_date' => ''], ['signup_date' => '2026-05-01'])]), TODAY);
test("a blank live date falls back to signup_date", $b['live'][0]['lifespan'] !== null);
test("the fallback is labelled as such", $b['live'][0]['date_source'] === 'signup_date');
test("no no_live_date gap when the fallback worked",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'no_live_date')) === 0);

$b = arayaBuildBrief(res([mk(['live_date' => '2026-06-01'], ['signup_date' => '2020-01-01'])]), TODAY);
test("a real live date wins over signup_date", $b['live'][0]['lifespan']['months'] === 4);
test("and is labelled live_date", $b['live'][0]['date_source'] === 'live_date');

$b = arayaBuildBrief(res([mk(['live_date' => ''], ['signup_date' => ''])]), TODAY);
test("both blank still raises the gap",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'no_live_date')) === 1);
test("and the gap text names both sources",
     str_contains($b['gaps'][0]['detail'], 'signup date'));

// The fallback must reach the idle rules, which was the point of adding it.
$b = arayaBuildBrief(res([mk(['live_date' => ''], ['signup_date' => '2026-01-01', 'allocation' => 250, 'remaining_minutes' => 240])]), TODAY);
test("the idle rule now runs off the fallback date", count($b['idleNew']) === 1);

// A cancelling shop carries the label too.
$b = arayaBuildBrief(res([mk(['live_status' => 'Waiting to cancel', 'live_date' => ''], ['signup_date' => '2026-05-01'])]), TODAY);
test("a cancelling row also carries date_source", $b['cancelling'][0]['date_source'] === 'signup_date');

echo "== expected vs actionable gaps ==" . PHP_EOL;
// A cancelled shop has no gcal row because cancelling is what removes it.
// Recording it keeps the count honest; flagging it as work does not.
$b = arayaBuildBrief(res([], [
    ['name' => 'Gone A', 'live_status' => 'Canceled'],
    ['name' => 'Gone B', 'live_status' => 'Cancel Before LIVE'],
    ['name' => 'Real',   'live_status' => 'LIVE'],
]), TODAY);
test("cancelled shops are marked expected", $b['gapsExpected'] === 2);
test("a live shop with no gcal row still needs action", $b['gapsNeedingAction'] >= 1);
test("every gap is still recorded", count($b['gaps']) === $b['gapsExpected'] + $b['gapsNeedingAction']);

$byName = [];
foreach ($b['gaps'] as $g) { $byName[$g['name']] = $g; }
test("a Canceled shop carries expected=true", $byName['Gone A']['expected'] === true);
test("a Cancel Before LIVE shop carries expected=true", $byName['Gone B']['expected'] === true);
test("a LIVE shop carries expected=false", $byName['Real']['expected'] === false);

// Gaps that are not about a missing gcal row are never "expected".
$b = arayaBuildBrief(res([mk(['live_status' => 'Not Live', 'register_date' => ''])]), TODAY);
$reg = array_values(array_filter($b['gaps'], fn($g) => $g['kind'] === 'no_register_date'));
test("a board-data gap is never marked expected", $reg[0]['expected'] === false);

echo "== near-match detection across the two systems ==" . PHP_EOL;
// The real case: one shop, two spellings, so it matched nothing and its
// minutes silently never appeared in the report.
$b = arayaBuildBrief(res([],
    [['name' => 'Chiva Raks Holistic Spa and Wellness Centre', 'live_status' => 'Not Live']],
    [['shop_id' => 'g1', 'shop_name' => 'Chivaraks Holistic Spa']]
), TODAY);
$kinds = array_column($b['gaps'], 'kind');
test("a near match is reported as name_mismatch", in_array('name_mismatch', $kinds, true));
$nm = array_values(array_filter($b['gaps'], fn($g) => $g['kind'] === 'name_mismatch'))[0];
test("the mismatch names the gcal spelling", str_contains($nm['detail'], 'Chivaraks Holistic Spa'));
test("the mismatch suggests the calendar-ID fix", str_contains($nm['detail'], 'Google Calendar ID'));
test("the weaker unmatched_monday row is dropped", !in_array('unmatched_monday', $kinds, true));
test("the gcal side is not reported separately too", !in_array('unmatched_gcal', $kinds, true));
test("a name mismatch always needs action", $nm['expected'] === false);

// Unrelated shops must not be paired, or the section cries wolf again.
$b = arayaBuildBrief(res([],
    [['name' => 'Silk Thai Therapy', 'live_status' => 'Not Live']],
    [['shop_id' => 'g2', 'shop_name' => 'SUPERTHAI MASSAGE']]
), TODAY);
$kinds = array_column($b['gaps'], 'kind');
test("unrelated names are not paired", !in_array('name_mismatch', $kinds, true));
test("both are still reported on their own side",
     in_array('unmatched_monday', $kinds, true) && in_array('unmatched_gcal', $kinds, true));

// A cancelled shop is gone from gcal on purpose and must not be paired.
$b = arayaBuildBrief(res([],
    [['name' => 'Chiva Raks Holistic Spa and Wellness Centre', 'live_status' => 'Canceled']],
    [['shop_id' => 'g3', 'shop_name' => 'Chivaraks Holistic Spa']]
), TODAY);
test("a cancelled shop is never paired",
     !in_array('name_mismatch', array_column($b['gaps'], 'kind'), true));

// The unmatched-gcal text should tell the reader what to do about it.
$b = arayaBuildBrief(res([], [], [['shop_id' => 'g4', 'shop_name' => 'Totally Unknown Shop']]), TODAY);
$ug = array_values(array_filter($b['gaps'], fn($g) => $g['kind'] === 'unmatched_gcal'))[0];
test("the unmatched-gcal detail says what to do", str_contains($ug['detail'], 'add it to the board'));

echo "== loose key helpers ==" . PHP_EOL;
test("spacing differences collapse",
     arayaLooseMatch(arayaLooseKey('Chiva Raks Holistic Spa'), arayaLooseKey('Chivaraks Holistic Spa')));
test("case differences collapse",
     arayaLooseMatch(arayaLooseKey('Tableland Traditional Thai massage'), arayaLooseKey('Tableland Traditional Thai Massage')));
test("a longer name containing a shorter one matches",
     arayaLooseMatch(arayaLooseKey('Sabai Thai Massage and Bodyworks'), arayaLooseKey('Sabai Thai Massage')));
test("two different shops do not match",
     !arayaLooseMatch(arayaLooseKey('Lila Thai Massage'), arayaLooseKey('Mira Thai Massage')));
test("filler-only names do not all collapse together",
     !arayaLooseMatch(arayaLooseKey('Thai Massage Spa'), arayaLooseKey('Wellness Centre')));
test("an empty key never matches", !arayaLooseMatch('', ''));

echo "== at risk: call these today ==" . PHP_EOL;
$b = arayaBuildBrief(res([
    mk(['name' => 'Overdrawn'],   ['remaining_minutes' => -109]),
    mk(['name' => 'AlmostEmpty'], ['remaining_minutes' => 22]),
    mk(['name' => 'Borderline'],  ['remaining_minutes' => 30]),
    mk(['name' => 'Fine'],        ['remaining_minutes' => 31]),
]), TODAY);
test("overdrawn and nearly-empty shops are at risk", count($b['atRisk']) === 3);
test("the worst balance leads", $b['atRisk'][0]['name'] === 'Overdrawn');
test("30 minutes is at risk (inclusive)",
     in_array('Borderline', array_column($b['atRisk'], 'name'), true));
test("31 minutes is not", !in_array('Fine', array_column($b['atRisk'], 'name'), true));
test("an overdrawn shop is marked as such", $b['atRisk'][0]['overdrawn'] === true);
test("a positive balance is not marked overdrawn",
     $b['atRisk'][1]['overdrawn'] === false);

// A shop that gave notice is not an at-risk sales call.
$b = arayaBuildBrief(res([mk(['live_status' => 'Waiting to cancel'], ['remaining_minutes' => 5])]), TODAY);
test("a cancelling shop is never in the at-risk list", count($b['atRisk']) === 0);

echo "== running out: burn rate, not a fixed number ==" . PHP_EOL;
// 60 left of 250 with 11 days to go: 190 used in 19 days is 10/day, so the
// balance is gone in 6 days and the shop runs dry before the refresh.
$b = arayaBuildBrief(res([mk([], ['remaining_minutes' => 60, 'allocation' => 250, 'days_until_refresh' => 11])]), TODAY);
test("a shop burning faster than its refresh is flagged", count($b['runningOut']) === 1);
test("it reports a per-day rate", $b['runningOut'][0]['per_day'] === 10.0);
test("it reports days until empty", $b['runningOut'][0]['days_until_empty'] === 6);
test("it projects a negative balance", $b['runningOut'][0]['projected_balance'] < 0);

// 120 left of 250 with 10 days to go projects to +55: not a problem.
$b = arayaBuildBrief(res([mk([], ['remaining_minutes' => 120, 'allocation' => 250, 'days_until_refresh' => 10])]), TODAY);
test("a shop that will make it is not flagged", count($b['runningOut']) === 0);

// Proportionality: the same balance on a bigger allowance is a different story.
$small = arayaBuildBrief(res([mk([], ['remaining_minutes' => 60, 'allocation' => 250, 'days_until_refresh' => 11])]), TODAY);
$big   = arayaBuildBrief(res([mk([], ['remaining_minutes' => 60, 'allocation' => 1000, 'days_until_refresh' => 11])]), TODAY);
test("the rule is proportional to the allowance, not a fixed balance",
     count($small['runningOut']) === 1 && count($big['runningOut']) === 1);

echo "== running out: when not to guess ==" . PHP_EOL;
foreach ([
    [60, 250, 28, 'too early in the period to trust a rate'],
    [60, 0,   11, 'no allowance recorded'],
    [-5, 250, 11, 'already overdrawn, which is an at-risk case instead'],
    [250, 250, 11, 'nothing spent yet'],
    [60, 250, 0,  'refresh is today'],
] as [$mins, $alloc, $days, $case]) {
    $b = arayaBuildBrief(res([mk([], ['remaining_minutes' => $mins, 'allocation' => $alloc, 'days_until_refresh' => $days])]), TODAY);
    test("not flagged when {$case}", count($b['runningOut']) === 0);
}

// The two lists must not both claim the same shop.
$b = arayaBuildBrief(res([mk([], ['remaining_minutes' => 10, 'allocation' => 250, 'days_until_refresh' => 11])]), TODAY);
test("a shop at risk is not also listed as running out",
     count($b['atRisk']) === 1 && count($b['runningOut']) === 0);

test("both sections are always present as arrays",
     is_array($b['atRisk']) && is_array($b['runningOut']));

echo "== call quality: figures reach the live row ==\n";
// Every figure the current gcal sends. A window gcal sends in full must
// arrive on the live row in full: a figure dropped here is a column that
// silently reads "?" on the dashboard forever.
$calls = ['d7'  => ['total' => 178, 'forwarded' => 24, 'short' => 41, 'silent' => 12, 'errors' => 1,
                    'minutes' => 305, 'booked' => 60, 'booking_failed' => 2, 'long' => 90, 'booked_unknown' => 5],
          'd30' => ['total' => 612, 'forwarded' => 88, 'short' => 140, 'silent' => 40, 'errors' => 3,
                    'minutes' => 1120, 'booked' => 210, 'booking_failed' => 7, 'long' => 320, 'booked_unknown' => 18]];
$b = arayaBuildBrief(res([mk([], ['calls' => $calls])]), TODAY);
test("the calls block survives onto the live row unchanged", $b['live'][0]['calls'] === $calls);
test("both windows and every figure are present",
     array_keys($b['live'][0]['calls']) === ['d7', 'd30']
     && array_keys($b['live'][0]['calls']['d7']) === ARAYA_CALL_FIGURES);

// An older gcal that sends only the original six must still work: the four
// booking figures come back null, which the dashboard shows as "?" rather
// than as zero bookings.
$sixOnly = ['d7' => ['total' => 10, 'forwarded' => 1, 'short' => 2, 'silent' => 0, 'errors' => 0, 'minutes' => 30]];
$b = arayaBuildBrief(res([mk([], ['calls' => $sixOnly])]), TODAY);
test("a gcal without the booking figures gives null for them, not zero",
     $b['live'][0]['calls']['d7']['total'] === 10
     && $b['live'][0]['calls']['d7']['booked'] === null
     && $b['live'][0]['calls']['d7']['booking_failed'] === null
     && $b['live'][0]['calls']['d7']['long'] === null
     && $b['live'][0]['calls']['d7']['booked_unknown'] === null);

echo "== call quality: absent data is not zero ==\n";
$b = arayaBuildBrief(res([mk()]), TODAY);
test("a gcal row with no calls block gives null, not zeros", $b['live'][0]['calls'] === null);
$b = arayaBuildBrief(res([], [['name' => 'Ghost', 'live_status' => 'LIVE', 'live_date' => '2026-06-01']]), TODAY);
test("a shop with no gcal match gives null and does not crash", $b['live'][0]['calls'] === null && $b['live'][0]['gcal_missing'] === true);
$b = arayaBuildBrief(res([mk([], ['calls' => 'broken'])]), TODAY);
test("a malformed calls value gives null", $b['live'][0]['calls'] === null);
$b = arayaBuildBrief(res([mk([], ['calls' => ['d30' => $calls['d30']]])]), TODAY);
test("a missing window is null and the other survives",
     $b['live'][0]['calls']['d7'] === null && $b['live'][0]['calls']['d30']['total'] === 612);
$b = arayaBuildBrief(res([mk([], ['calls' => ['d7' => ['total' => 5], 'd30' => $calls['d30']]])]), TODAY);
test("a missing figure is null, not 0", $b['live'][0]['calls']['d7']['minutes'] === null && $b['live'][0]['calls']['d7']['total'] === 5);
$b = arayaBuildBrief(res([mk([], ['calls' => ['d7' => ['total' => 0, 'forwarded' => 0, 'short' => 0, 'silent' => 0, 'errors' => 0, 'minutes' => 0], 'd30' => $calls['d30']]])]), TODAY);
test("a real zero stays 0", $b['live'][0]['calls']['d7']['total'] === 0);
$b = arayaBuildBrief(res([mk(['live_status' => 'Stuck'], ['calls' => $calls])]), TODAY);
test("non-live shops add no live row to carry calls", $b['live'] === []);

echo "== call errors in the brief ==\n";
$e1 = ['shop_id' => 's1', 'shop_name' => "  Lotus Thai Spa\u{00A0}", 'started_at' => '2026-10-21T04:12:00+00:00',
       'outcome' => 'call.in-progress.error-vapifault-worker-not-available', 'duration_sec' => 12,
       'recording_url' => 'https://example.com/r.wav'];
$e2 = ['recording_url' => null, 'shop_name' => 'Jasmine'] + $e1;
$b = arayaBuildBrief(res([mk()]), TODAY, [$e1, $e2]);
test("callErrors carries every error, order kept", count($b['callErrors']) === 2 && $b['callErrors'][1]['shop_name'] === 'Jasmine');
test("shop names are trimmed with the NBSP-safe idiom", $b['callErrors'][0]['shop_name'] === 'Lotus Thai Spa');
test("a recording URL is kept", $b['callErrors'][0]['recording_url'] === 'https://example.com/r.wav');
test("a null recording_url stays null", $b['callErrors'][1]['recording_url'] === null);
test("duration is numeric", $b['callErrors'][0]['duration_sec'] === 12);
$b = arayaBuildBrief(res([mk()]), TODAY, [['recording_url' => 'javascript:alert(1)'] + $e1, ['recording_url' => ''] + $e1]);
test("a non-http recording URL becomes null", $b['callErrors'][0]['recording_url'] === null);
test("an empty recording URL becomes null", $b['callErrors'][1]['recording_url'] === null);
$b = arayaBuildBrief(res([mk()]), TODAY, [$e1, 'junk', null]);
test("a non-array entry is skipped, not fatal", count($b['callErrors']) === 1);
test("callErrors is an empty array when none were passed", arayaBuildBrief(res([mk()]), TODAY)['callErrors'] === []);
test("callErrors is an empty array for an empty list", arayaBuildBrief(res([mk()]), TODAY, [])['callErrors'] === []);

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
