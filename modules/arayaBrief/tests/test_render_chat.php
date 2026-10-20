<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../models/buildBrief.php';
require_once __DIR__ . '/../models/renderChat.php';

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

function mk(array $monday = [], array $gcal = []): array {
    return [
        'monday' => array_merge([
            'name' => 'Test Shop', 'live_status' => 'LIVE', 'setup_status' => 'Done',
            'po' => 'Aim', 'register_date' => '2026-01-01', 'live_date' => '2026-06-01',
            'reason_for_cancel' => '', 'note' => '', 'calendar_id' => 'cal-x', 'email' => 'a@x.com',
        ], $monday),
        'gcal' => array_merge([
            'shop_id' => 'g1', 'shop_name' => 'Test Shop', 'remaining_minutes' => 200,
            'allocation' => 250, 'billing_day' => 15, 'days_until_refresh' => 26,
            'currency' => 'AUD', 'closed_periods' => [],
        ], $gcal),
        'matched_by' => 'calendar',
    ];
}
function res(array $m, array $um = [], array $ug = []): array {
    return ['matched' => $m, 'unmatchedMonday' => $um, 'unmatchedGcal' => $ug];
}
/**
 * A minimal, fully-populated brief whose sections can be overridden directly.
 *
 * Some rendering cases cannot be reached through arayaBuildBrief, because the
 * rules legitimately refuse to produce them -- an unrecognised exclusion
 * reason, for instance. Building the brief shape here lets the renderer be
 * tested on input a future rules change might emit.
 */
function arayaChatFixtureBrief(array $sections = []): array {
    return array_merge([
        'generated_for' => TODAY,
        'overview' => [
            'live' => 0, 'pendingCancel' => 0, 'setup' => 0, 'cancelled' => 0,
            'canceledAfterLive' => 0, 'cancelBeforeLive' => 0, 'total' => 0,
        ],
        'live' => [], 'upsell' => [], 'upsellExcluded' => [],
        'idleNew' => [], 'idleQuiet' => [], 'setup' => [], 'cancelling' => [],
        'gaps' => [],
    ], $sections);
}

function render(array $r): string {
    return arayaRenderChat(arayaBuildBrief($r, TODAY));
}

echo "== the shop line format the user asked for ==\n";
$out = render(res([mk(['name' => 'Aunya Shop', 'live_date' => '2026-06-01'], ['remaining_minutes' => 152])]));
test("shop renders as 'Name (152 mins · ...)'", str_contains($out, 'Aunya Shop (152 mins'));
test("lifespan follows the minutes in the same parens", preg_match('/Aunya Shop \(152 mins · 4 ด\. 19 ว/u', $out) === 1);

echo "== negative minutes ==\n";
$out = render(res([mk(['name' => 'Me Ya Wi'], ['remaining_minutes' => -109])]));
test("a negative balance shows its sign", str_contains($out, 'Me Ya Wi (-109 mins'));

echo "== overview ==\n";
$out = render(res([mk(['live_status' => 'LIVE']), mk(['live_status' => 'Canceled'])]));
test("the overview reports the live count", preg_match('/LIVE: 1/u', $out) === 1);
test("the header renders the date as 20 Oct 2026 (07:00)", str_contains($out, '☀️ ARAYA DAILY BRIEF — 20 Oct 2026 (07:00)'));
test("the header is followed by the separator rule", str_contains($out, "(07:00)\n━━━━━━━━━━━━━━━━━━━━━━━━\n"));
test("the overview heading is the template's", str_contains($out, '📊 OVERVIEW'));
foreach (['LIVE: 1 ร้าน', 'แจ้งยกเลิก (pending): 0 ร้าน', 'Setup ค้างอยู่: 0 ร้าน', 'ยกเลิกแล้ว: 1 ร้าน (', 'รวมลูกค้าทั้งหมด: 2 ร้าน'] as $ln) {
    test("overview line '{$ln}' carries ร้าน", str_contains($out, $ln));
}
test("cancelled breakdown lists Cancel Before LIVE before Canceled",
    preg_match('/Cancel Before LIVE \d+ · Canceled \d+\)/u', $out) === 1);

echo "== empty sections say so rather than being blank ==\n";
$out = render(res([mk([], ['days_until_refresh' => 26, 'remaining_minutes' => 200])]));
test("no upsell today is stated explicitly", str_contains($out, 'ไม่มีวันนี้'));

echo "== data gaps are shown, never swallowed ==\n";
$out = render(res([mk()], [['name' => 'Orphan Shop', 'live_status' => 'LIVE']]));
test("an unmatched shop is named in the message", str_contains($out, 'Orphan Shop'));

echo "== owners are mentioned ==\n";
$out = render(res([mk(['name' => 'Stuck Shop', 'live_status' => 'Not Live', 'po' => 'Namva', 'register_date' => '2026-04-16'])]));
test("the owner appears", str_contains($out, '@Namva'));
test("days stalled appear", str_contains($out, '187'));

echo "== an unrecorded cancellation reason is an action item ==\n";
$out = render(res([mk(['live_status' => 'Waiting to cancel', 'reason_for_cancel' => ''])]));
test("blank reason renders as unrecorded", str_contains($out, 'ยังไม่ได้บันทึก'));

echo "== degraded mode ==\n";
$brief = arayaBuildBrief(res([mk()]), TODAY);
$brief['gcal_unavailable'] = true;
$out = arayaRenderChat($brief);
test("a gcal outage is stated in the message", str_contains($out, 'ดึงข้อมูลนาทีไม่ได้'));

echo "== output is plain text ==\n";
$out = render(res([mk()]));
test("no JSON card wrapper", !str_contains($out, '"cardsV2"'));
test("not empty", strlen(trim($out)) > 0);
test("valid UTF-8", mb_check_encoding($out, 'UTF-8'));


echo "== Thai shop names survive byte for byte ==\n";
$thai = 'ร้านนวดไทยสบายภ';
$out = render(res([mk(['name' => $thai], ['shop_name' => $thai])]));
test("a Thai name appears unchanged", str_contains($out, $thai . ' ('));

echo "== NBSP padding is cleaned (catches bare trim) ==\n";
$nb = "\u{00A0}";
$emptyBrief = ['generated_for' => TODAY, 'overview' => [], 'live' => [], 'upsell' => [], 'upsellExcluded' => [],
               'idleNew' => [], 'idleQuiet' => [], 'setup' => [], 'cancelling' => [], 'gaps' => []];
$b = $emptyBrief;
$b['live'] = [['name' => $nb . $nb . $thai . $nb, 'remaining_minutes' => 50, 'lifespan' => ['months' => 1, 'days' => 2], 'gcal_missing' => false]];
$out = arayaRenderChat($b);
test("padded Thai name is cleaned and intact", str_contains($out, "\n" . $thai . ' (50 mins'));
test("no NBSP survives in the line", !str_contains($out, $nb));
test("output still valid UTF-8", mb_check_encoding($out, 'UTF-8'));
$b = $emptyBrief;
$b['gaps'] = [['kind' => 'blank_status', 'name' => $nb . $thai . $nb, 'detail' => 'x']];
$out = arayaRenderChat($b);
test("padded gap name is cleaned", str_contains($out, '• ' . $thai . ' [blank_status]'));

echo "== every gap kind renders and names its shop ==\n";
$kinds = ['unmatched_monday', 'unmatched_gcal', 'no_live_date', 'no_billing_day', 'no_allocation',
          'no_register_date', 'blank_status', 'unknown_status', 'no_remaining_minutes'];
// Details that match the shapes buildBrief.php really emits.
$realDetail = [
    'unmatched_monday' => 'On the Monday tracker (LIVE) but no matching shop in gcal-booking-api; minutes unknown.',
    'unknown_status'   => 'Monday live_status "Paused" is not a known label; counted in the total only.',
    'no_allocation'    => 'No minute allocation in gcal; the idle rule would divide by zero, so it was not evaluated.',
];
foreach ($kinds as $k) {
    $b = $emptyBrief;
    $detail = $realDetail[$k] ?? "detail for {$k}";
    $b['gaps'] = [['kind' => $k, 'name' => "Shop-{$k}", 'detail' => $detail]];
    $out = arayaRenderChat($b);
    test("gap kind '{$k}' names its shop and kind", str_contains($out, "Shop-{$k}") && str_contains($out, "[{$k}]"));
    test("gap kind '{$k}' renders a Thai detail, not the English one",
        !str_contains($out, $detail) && preg_match('/\] [^\n]*[\x{0E00}-\x{0E7F}]/u', $out) === 1);
}
echo "== an unknown gap kind falls back to its English detail ==\n";
$b = $emptyBrief;
$b['gaps'] = [['kind' => 'invented_kind', 'name' => 'Odd Shop', 'detail' => 'something new went wrong']];
$out = arayaRenderChat($b);
test("unknown kind prints its English detail", str_contains($out, '• Odd Shop [invented_kind] something new went wrong'));

echo "== a fully populated brief renders in section order ==\n";
$full = [
    'generated_for' => TODAY,
    'gcal_unavailable' => true,
    'overview' => ['live' => 1, 'pendingCancel' => 1, 'setup' => 1, 'cancelled' => 0, 'canceledAfterLive' => 0, 'cancelBeforeLive' => 0, 'total' => 3],
    'live' => [['name' => 'S-live', 'remaining_minutes' => 10, 'lifespan' => ['months' => 1, 'days' => 1], 'gcal_missing' => false]],
    'upsell' => [['name' => 'S-upsell', 'po' => 'A', 'remaining_minutes' => 10, 'days_until_refresh' => 3]],
    'upsellExcluded' => [],
    'idleNew' => [['name' => 'S-idlenew', 'po' => 'A', 'days_live' => 12, 'remaining_minutes' => 240, 'allocation' => 250, 'used_pct' => 4.0]],
    'idleQuiet' => [['name' => 'S-idlequiet', 'po' => 'A', 'remaining_minutes' => 100, 'allocation' => 250, 'latest_used' => 5, 'prior_mean' => 100.0, 'period_end' => '2026-10-01']],
    'setup' => [['name' => 'S-setup', 'live_status' => 'Stuck', 'setup_status' => 'Stuck', 'po' => 'A', 'note' => '', 'register_date' => '2026-04-16', 'days_stalled' => 187]],
    'cancelling' => [['name' => 'S-cancel', 'po' => 'A', 'live_date' => '2026-01-01', 'lifespan' => ['months' => 9, 'days' => 19], 'reason' => '']],
    'gaps' => [['kind' => 'no_allocation', 'name' => 'S-gap', 'detail' => 'd']],
];
$out = arayaRenderChat($full);
$out_full = $out;
$order = ['ดึงข้อมูลนาทีไม่ได้', 'ARAYA DAILY BRIEF', 'LIVE: 1', 'S-live', 'S-upsell', 'S-idlenew', 'S-idlequiet', 'S-setup', 'S-cancel', 'S-gap'];
$pos = array_map(fn($s) => mb_strpos($out, $s), $order);
test("every section is present", !in_array(false, $pos, true));
$sorted = $pos; sort($sorted);
test("sections appear in the documented order", $pos === $sorted);
foreach (['✅ ร้านที่ LIVE (นาทีคงเหลือ · อยู่กับเรามานาน)', '💰 โอกาสอัพเซลล์ (นาที <70 และรีเฟรชอีก ≤15 วัน)', '📉 ไลฟ์แล้วแต่แทบไม่ได้ใช้',
          '📉 เคยใช้แล้วหายไป', '🚧 ทำไมยังไม่ไลฟ์ (Setup ค้าง)', '❌ แจ้งยกเลิก', '⚠️ ข้อมูลไม่ครบ'] as $h) {
    test("heading '{$h}' is verbatim", str_contains($out, "\n" . $h . "\n"));
}
test("exactly 8 rules: 1 after the header + 7 between 8 sections", substr_count($out, ARAYA_CHAT_SEP) === 8);
test("it is one message (no split markers)", substr_count($out, 'ARAYA DAILY BRIEF') === 1);

echo "== gap details carry their values ==\n";
function gapOut(string $kind, string $detail): string {
    global $emptyBrief;
    $b = $emptyBrief;
    $b['gaps'] = [['kind' => $kind, 'name' => 'G', 'detail' => $detail]];
    return arayaRenderChat($b);
}
$out = gapOut('unknown_status', 'Monday live_status "Paused" is not a known label; counted in the total only.');
test("unknown_status names the unrecognised status", str_contains($out, '"Paused"'));
$out = gapOut('unmatched_monday', 'On the Monday tracker (Not Live) but no matching shop in gcal-booking-api; minutes unknown.');
test("unmatched_monday names the Monday status", str_contains($out, 'สถานะ: Not Live'));
$out = gapOut('no_allocation', 'Remaining minutes (310) exceed the allocation (250); the balance is above the allocation, so the usage percentage is not meaningful and the idle rule was not evaluated.');
test("no_allocation (exceeds) carries both numbers",
     str_contains($out, '(310)') && str_contains($out, '(250)'));
$out = gapOut('no_allocation', 'No minute allocation in gcal; the idle rule would divide by zero, so it was not evaluated.');
test("no_allocation (missing) is a different sentence from the exceed case",
    str_contains($out, 'ยังไม่ได้ตั้งโควตา') && !str_contains($out, 'มากกว่า'));
foreach (['unknown_status', 'unmatched_monday', 'no_allocation'] as $k) {
    $out = gapOut($k, 'unexpected wording 42');
    test("{$k}: when extraction fails the English detail is appended", str_contains($out, '(unexpected wording 42)'));
}
$out = gapOut('no_live_date', 'Live date is missing, unreadable or in the future; lifespan unknown.');
test("no_live_date for a non-LIVE shop does not claim an idle check", !str_contains($out, 'กฎร้านไม่ใช้งาน'));
$out = gapOut('no_live_date', 'Live date is missing, unreadable or in the future; lifespan and the idle rules cannot be evaluated.');
test("no_live_date for a LIVE shop mentions the idle check", str_contains($out, 'กฎร้านไม่ใช้งาน'));

echo "== the allowlist of headings ==\n";
$headingAllow = ['📊 OVERVIEW', '✅ ร้านที่ LIVE (นาทีคงเหลือ · อยู่กับเรามานาน)',
    '🚨 ต้องโทรวันนี้ (นาทีหมดหรือใกล้หมด)', '⏳ นาทีจะหมดก่อนรอบรีเฟรช',
    '💰 โอกาสอัพเซลล์ (นาที <70 และรีเฟรชอีก ≤15 วัน)',
    '📉 ไลฟ์แล้วแต่แทบไม่ได้ใช้', '📉 เคยใช้แล้วหายไป', '🚧 ทำไมยังไม่ไลฟ์ (Setup ค้าง)', '❌ แจ้งยกเลิก', '⚠️ ข้อมูลไม่ครบ'];
// A heading is the first non-blank line of each block after a rule (the header block excluded).
$blocks = explode(ARAYA_CHAT_SEP, $out_full);
array_shift($blocks);
$found = [];
foreach ($blocks as $blk) {
    $lines = array_values(array_filter(explode("\n", $blk), fn($l) => $l !== ''));
    $found[] = $lines[0] ?? '';
}
// Every heading must be on the allowlist, and they must appear in the
// allowlist's order. Equality would additionally demand that every section has
// data, which this fixture does not provide -- and that would make the test
// fail for the wrong reason the next time a section is legitimately empty.
test("every heading emitted is on the allowlist",
     array_values(array_diff($found, $headingAllow)) === []);
$positions = array_map(fn($h) => array_search($h, $headingAllow, true), $found);
$sorted = $positions;
sort($sorted);
test("headings appear in the allowlist's order", $positions === $sorted);
test("no heading is emitted twice", count($found) === count(array_unique($found)));

// The upsell-exclusion reason is the line someone acts on, so it must read in
// Thai while still naming the board status they will look up on Monday.
echo "== upsellExcluded reason ==\n";
$excluded = [
    'name' => 'Me Ya Wi', 'remaining_minutes' => -109, 'days_until_refresh' => 5,
    'live_status' => 'Waiting to cancel',
    'reason' => 'Status is "Waiting to cancel"; the shop has given notice.',
];
$out = arayaRenderChat(arayaChatFixtureBrief(['upsellExcluded' => [$excluded]]));
test("the reason is Thai, not the English source string",
     str_contains($out, 'ร้านแจ้งยกเลิกแล้ว') && !str_contains($out, 'has given notice'));
test("the board status stays verbatim so it can be looked up",
     str_contains($out, 'สถานะ "Waiting to cancel"'));
test("the excluded shop still shows its signed balance",
     str_contains($out, 'Me Ya Wi (-109 mins'));

// Derived from the reason text when live_status is absent on the row.
$noStatus = $excluded;
unset($noStatus['live_status']);
$out = arayaRenderChat(arayaChatFixtureBrief(['upsellExcluded' => [$noStatus]]));
test("the status is recovered from the reason when the row omits it",
     str_contains($out, 'สถานะ "Waiting to cancel"'));

// An unrecognised reason shape must degrade to the English text, never vanish.
$novel = ['name' => 'Odd Shop', 'remaining_minutes' => 5, 'days_until_refresh' => 1,
          'reason' => 'some new policy reason'];
$out = arayaRenderChat(arayaChatFixtureBrief(['upsellExcluded' => [$novel]]));
test("an unknown reason falls back to its English text rather than blank",
     str_contains($out, 'some new policy reason'));

echo "== exclusion reason is never invented ==\n";
foreach (['Plan "Enterprise" is excluded from upsell.', 'Shop "Aunya" is on a trial.'] as $r) {
    $out = arayaRenderChat(arayaChatFixtureBrief(['upsellExcluded' => [['name' => 'T', 'remaining_minutes' => 5, 'days_until_refresh' => 1, 'reason' => $r]]]));
    test("'{$r}' stays English and is not reported as cancelling",
        str_contains($out, $r) && !str_contains($out, 'ร้านแจ้งยกเลิกแล้ว'));
}

echo "== sparse brief ==\n";
$out = arayaRenderChat(arayaChatFixtureBrief());
test("no doubled rule", preg_match('/━\n+━/u', $out) === 0);
test("no trailing rule", preg_match('/━\s*$/u', $out) === 0);
test("ends with exactly one newline", substr($out, -1) === "\n" && substr($out, -2, 1) !== "\n");

echo "== null data in live rows ==\n";
$warn = [];
set_error_handler(function ($no, $str) use (&$warn) { $warn[] = $str; return true; });
foreach ([
    'null remaining_minutes' => ['name' => 'NullMin', 'remaining_minutes' => null, 'lifespan' => ['months' => 1, 'days' => 2], 'gcal_missing' => false],
    'null lifespan'          => ['name' => 'NullLife', 'remaining_minutes' => 50, 'lifespan' => null, 'gcal_missing' => false],
    'gcal_missing'           => ['name' => 'NoGcal', 'remaining_minutes' => null, 'lifespan' => null, 'gcal_missing' => true],
] as $label => $row) {
    $out = arayaRenderChat(arayaChatFixtureBrief(['live' => [$row]]));
    test("{$label}: names the shop", str_contains($out, $row['name']));
    test("{$label}: no 'null' and no empty ()", !str_contains($out, 'null') && !str_contains($out, '()'));
}
restore_error_handler();
test("null data raised no PHP warnings", $warn === []);

echo "== NBSP padding on owner, cancel reason, setup note ==\n";
$b = arayaChatFixtureBrief([
    'upsell' => [['name' => 'U', 'po' => $nb . 'Namva' . $nb, 'remaining_minutes' => 10, 'days_until_refresh' => 3]],
    'cancelling' => [['name' => 'C', 'po' => 'A', 'lifespan' => null, 'reason' => $nb . $nb . 'too pricey' . $nb]],
    'setup' => [['name' => 'S', 'live_status' => 'Stuck', 'setup_status' => 'Stuck', 'po' => 'A', 'note' => $nb . 'call back' . $nb, 'days_stalled' => 3]],
]);
$out = arayaRenderChat($b);
test("owner is cleaned", str_contains($out, '· @Namva') && !str_contains($out, '@Namva' . $nb));
test("cancel reason is cleaned", str_contains($out, 'เหตุผล: too pricey'));
test("setup note is cleaned", str_contains($out, "\n   call back\n"));
test("no NBSP survives anywhere", !str_contains($out, $nb));

echo "== gap texts are written as instructions ==" . PHP_EOL;
// These lines go to the team that owns the Monday board, not to whoever wrote
// the rules. "The matcher could not pair them" is not something they can act
// on; each line has to name the change to make.
$instructionCases = [
    ['unmatched_gcal', 'In gcal-booking-api but no item on the Monday board. Either add it to the board, or if it is a test or retired shop, it can be ignored.'],
    ['no_billing_day', 'No billing day set in gcal, so the upsell rule cannot be evaluated.'],
    ['no_register_date', 'No register date on the Monday item, so days stalled cannot be computed.'],
    ['blank_status', 'Live Status is empty on the Monday item.'],
    ['unknown_status', 'Monday live_status "Zzz" is not a known label; counted in the total only.'],
    ['no_live_date', 'No usable date: the tracker Live Date is empty and gcal has no signup date either, so lifespan and the idle rules cannot be evaluated.'],
    ['name_mismatch', 'Looks like the same shop as "Other Spelling" in gcal, but the names differ so they were not paired.'],
];
foreach ($instructionCases as [$kind, $detail]) {
    $text = arayaChatGapDetail(['kind' => $kind, 'detail' => $detail]);
    test("{$kind} tells the reader what to do", str_contains($text, '→'));
    test("{$kind} is not left in English", !str_contains($text, 'cannot be'));
}
$text = arayaChatGapDetail(['kind' => 'name_mismatch', 'detail' => $instructionCases[6][1]]);
test("name_mismatch names the other spelling", str_contains($text, 'Other Spelling'));
test("name_mismatch offers the durable fix", str_contains($text, 'Google Calendar ID'));

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
