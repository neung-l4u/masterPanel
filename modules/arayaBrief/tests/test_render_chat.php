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
        'forwardingHigh' => [], 'hangupHigh' => [], 'conversionLow' => [],
        'bookingBroken' => [], 'fleetCalls' => null, 'rateWindow' => ARAYA_RATE_WINDOW,
    ], $sections);
}

function render(array $r): string {
    return arayaRenderChat(arayaBuildBrief($r, TODAY));
}

echo "== the shop line format the user asked for ==\n";
$out = render(res([mk(['name' => 'Aunya Shop', 'live_date' => '2026-06-01'], ['remaining_minutes' => 152])]));
test("shop renders as 'Name (152 mins · ...)'", str_contains($out, 'Aunya Shop* (152 mins'));
test("lifespan follows the minutes in the same parens", preg_match('/Aunya Shop\* \(152 mins · 4 ด\. 19 ว/u', $out) === 1);

echo "== negative minutes ==\n";
$out = render(res([mk(['name' => 'Me Ya Wi'], ['remaining_minutes' => -109])]));
test("a negative balance shows its sign", str_contains($out, 'Me Ya Wi* (-109 mins'));

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
test("a Thai name appears unchanged", str_contains($out, $thai . '* ('));

echo "== NBSP padding is cleaned (catches bare trim) ==\n";
$nb = "\u{00A0}";
$emptyBrief = ['generated_for' => TODAY, 'overview' => [], 'live' => [], 'upsell' => [], 'upsellExcluded' => [],
               'idleNew' => [], 'idleQuiet' => [], 'setup' => [], 'cancelling' => [], 'gaps' => []];
$b = $emptyBrief;
$b['live'] = [['name' => $nb . $nb . $thai . $nb, 'remaining_minutes' => 50, 'lifespan' => ['months' => 1, 'days' => 2], 'gcal_missing' => false]];
$out = arayaRenderChat($b);
test("padded Thai name is cleaned and intact", str_contains($out, '*' . $thai . '* (50 mins'));
test("no NBSP survives in the line", !str_contains($out, $nb));
test("output still valid UTF-8", mb_check_encoding($out, 'UTF-8'));
$b = $emptyBrief;
$b['gaps'] = [['kind' => 'blank_status', 'name' => $nb . $thai . $nb, 'detail' => 'x']];
$out = arayaRenderChat($b);
test("padded gap name is cleaned", str_contains($out, '• *' . $thai . '* [blank_status]'));

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
        // The instruction is on its own indented line under the name now, so
        // look for Thai there rather than beside the kind tag.
        !str_contains($out, $detail) && preg_match('/\n    [^\n]*[\x{0E00}-\x{0E7F}]/u', $out) === 1);
}
echo "== an unknown gap kind falls back to its English detail ==\n";
$b = $emptyBrief;
$b['gaps'] = [['kind' => 'invented_kind', 'name' => 'Odd Shop', 'detail' => 'something new went wrong']];
$out = arayaRenderChat($b);
test("unknown kind prints its English detail",
     str_contains($out, '• *Odd Shop* [invented_kind]')
     && str_contains($out, "\n    something new went wrong"));

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
    '🚨 ต้องโทรวันนี้ (นาทีหมดหรือใกล้หมด)', '⏳ นาทีจะหมดก่อนรอบรีเฟรช', '📞 สายที่เกิด error (30 วันล่าสุด)',
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
     str_contains($out, 'Me Ya Wi* (-109 mins'));

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
test("setup note is cleaned", str_contains($out, "\n    call back\n"));
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

echo "== setup notes stay readable ==" . PHP_EOL;
// A board note is free text and some run to several paragraphs with their own
// line breaks. Printed raw they turn this section into a wall of text in which
// the shop names -- the thing the list exists to show -- disappear.
$longNote = str_repeat('เนื้อหายาวมาก ', 40);
$out = render(res([mk(['name' => 'Wall Of Text', 'live_status' => 'Not Live',
                       'register_date' => '2026-01-01', 'note' => $longNote])]));
$noteLines = array_values(array_filter(explode("\n", $out), fn($l) => str_starts_with($l, '    ')));
test("a long note is cut to one line", count($noteLines) === 1);
test("the cut note is marked with an ellipsis", str_contains($noteLines[0], '…'));
test("the cut note is not longer than the limit plus the ellipsis",
     mb_strlen(trim($noteLines[0])) <= ARAYA_CHAT_NOTE_MAX + 1);

// Newlines inside a note must not break the layout.
$out = render(res([mk(['name' => 'Multi', 'live_status' => 'Not Live',
                       'register_date' => '2026-01-01', 'note' => "line one

line two
line three"])]));
$noteLines = array_values(array_filter(explode("\n", $out), fn($l) => str_starts_with($l, '    ')));
test("a multi-paragraph note collapses to one line", count($noteLines) === 1);
test("its words survive the collapse",
     str_contains($noteLines[0], 'line one') && str_contains($noteLines[0], 'line three'));

// A short note is left exactly as written.
$out = render(res([mk(['name' => 'Short', 'live_status' => 'Not Live',
                       'register_date' => '2026-01-01', 'note' => 'รอลูกค้าตอบกลับ'])]));
test("a short note is not cut", str_contains($out, '   รอลูกค้าตอบกลับ'));
test("and gets no ellipsis", !str_contains($out, 'รอลูกค้าตอบกลับ…'));

echo "== setup entries are separated ==" . PHP_EOL;
$out = render(res([
    mk(['name' => 'Shop One', 'live_status' => 'Not Live', 'register_date' => '2026-01-01', 'note' => 'note one']),
    mk(['name' => 'Shop Two', 'live_status' => 'Not Live', 'register_date' => '2026-02-01', 'note' => 'note two']),
]));
// Bold names now carry the separation a blank line used to, so six entries
// stay six rows instead of eighteen. Assert the compact shape directly.
test("each shop is one bulleted row with its name in bold",
     str_contains($out, '• *Shop One*') && str_contains($out, '• *Shop Two*'));
test("no blank line is spent between entries",
     !str_contains($out, "note one

"));

// Those blank lines must not leak to the edges of the block, which would
// double the gap around the separator rules.
test("no block ends with a blank line before a rule", !str_contains($out, "


━"));
test("no block starts with a blank line after a rule", !str_contains($out, "━


"));

echo "== call errors section ==\n";
const ERR_HEAD = '📞 สายที่เกิด error (30 วันล่าสุด)';
function errRow(string $shop, string $at = '2026-10-21T04:12:00+00:00', ?string $rec = 'https://example.com/r.wav'): array {
    return ['shop_id' => 's', 'shop_name' => $shop, 'started_at' => $at,
            'outcome' => 'call.in-progress.error-vapifault-worker-not-available', 'duration_sec' => 12, 'recording_url' => $rec];
}
function errRender(array $errors): string {
    return arayaRenderChat(arayaBuildBrief(res([mk()]), TODAY, $errors));
}
$quiet = errRender([]);
test("no errors: no heading at all", !str_contains($quiet, ERR_HEAD) && !str_contains($quiet, '📞'));
test("no errors: the message is identical to one built without the argument",
     $quiet === arayaRenderChat(arayaBuildBrief(res([mk()]), TODAY)));
test("no errors: nothing about errors leaks into the text", !str_contains($quiet, 'error'));

$withErr = errRender([errRow('Lotus Thai Spa'), errRow('Jasmine Wellness', '2026-10-20T23:30:00+00:00', null)]);
test("errors: the heading appears exactly once", substr_count($withErr, ERR_HEAD) === 1);
test("errors: heading is verbatim on its own line", str_contains($withErr, "\n" . ERR_HEAD . "\n"));
test("errors: bold-name bullet format", str_contains($withErr, '• *Lotus Thai Spa* — '));
test("errors: time is shown in Bangkok time", str_contains($withErr, '21 Oct 11:12'));
test("errors: Bangkok conversion crosses midnight", str_contains($withErr, '21 Oct 06:30'));
test("errors: the cause is on its own indented line", str_contains($withErr, "\n    call.in-progress.error-vapifault-worker-not-available\n"));
test("errors: a null recording does not break the message", str_contains($withErr, 'Jasmine Wellness'));
test("errors: no URL in the phone message", !str_contains($withErr, 'https://'));
test("errors: it sits after the overview and before upsell",
     strpos($withErr, ERR_HEAD) < strpos($withErr, '💰') && strpos($withErr, '📊 OVERVIEW') < strpos($withErr, ERR_HEAD));
test("errors: no call-quality table in the message", !str_contains($withErr, 'Forwarded') && !str_contains($withErr, 'Silent'));

$many = array_map(fn($i) => errRow("Shop {$i}"), range(1, 8));
$out = errRender($many);
test("errors: capped at 5 entries for the phone", substr_count($out, 'error-vapifault') === 5);
test("errors: the overflow is counted, not hidden", str_contains($out, '(อีก 3 สาย'));
test("errors: exactly at the cap has no overflow line", !str_contains(errRender(array_slice($many, 0, 5)), '(อีก'));
test("errors: an unparseable time is shown as given", str_contains(errRender([errRow('X', 'yesterday-ish?')]), 'yesterday-ish?'));
test("errors: an unnamed shop does not render an empty name", str_contains(errRender([errRow('')]), '(unnamed shop)'));

echo "== call-quality sections ==\n";
// Each line has to carry the baseline it was judged against: a rate with no
// "normal" beside it is a number nobody can act on, which is the state the
// message was in before these sections existed.
$out = arayaRenderChat(arayaChatFixtureBrief([
    'forwardingHigh' => [['name' => 'Me Ya Wi', 'po' => 'Sai', 'pct' => 13.5,
                          'fleet_pct' => 12.4, 'total' => 200, 'forwarded' => 27]],
]));
test("forwarding: the shop and its rate appear", str_contains($out, 'Me Ya Wi* — 13.5%'));
test("forwarding: the fleet figure appears beside it", str_contains($out, 'ภาพรวมระบบ 12.4%'));
test("forwarding: the call count appears", str_contains($out, 'จาก 200 สาย'));
test("forwarding: the owner is tagged", str_contains($out, '@Sai'));

// Without a fleet figure the message says nothing rather than implying one.
$out = arayaRenderChat(arayaChatFixtureBrief([
    'hangupHigh' => [['name' => 'Repose', 'po' => 'Noey', 'pct' => 31.0,
                      'fleet_pct' => null, 'total' => 100, 'short' => 31]],
]));
test("hang-ups: the rate appears", str_contains($out, 'Repose* — 31.0%'));
test("hang-ups: no fleet figure is invented", !str_contains($out, 'ภาพรวมระบบ'));
test("hang-ups: the greeting hint is there", str_contains($out, 'greeting'));

$out = arayaRenderChat(arayaChatFixtureBrief([
    'conversionLow' => [['name' => 'Tableland', 'po' => 'Sai', 'pct' => 9.2,
                         'fleet_pct' => 60.0, 'total' => 120, 'booked' => 9, 'base' => 98]],
]));
test("conversion: the rate appears", str_contains($out, 'Tableland* — 9.2%'));
// The denominator is quoted so a rate off a thin base reads as thin.
test("conversion: the denominator is quoted", str_contains($out, 'จาก 98 สายที่คุยนานพอจะจองได้'));
test("conversion: the follow-up action is there", str_contains($out, 'ฟังสายย้อนหลัง'));

$out = arayaRenderChat(arayaChatFixtureBrief([
    'bookingBroken' => [['name' => 'Me Ya Wi', 'po' => 'Mark', 'failed' => 3,
                         'booked' => 10, 'total' => 50]],
]));
test("booking failures: the count appears", str_contains($out, 'ล้มเหลว 3 สาย'));
test("booking failures: successful bookings give context", str_contains($out, 'จองสำเร็จ 10 สาย'));
test("booking failures: the action names the booking API", str_contains($out, 'booking API'));

// A null booked count must not render as "0 สาย": those are different claims.
$out = arayaRenderChat(arayaChatFixtureBrief([
    'bookingBroken' => [['name' => 'X', 'po' => '', 'failed' => 1, 'booked' => null, 'total' => 5]],
]));
test("booking failures: an unknown booked count is omitted, not shown as 0",
     !str_contains($out, 'จองสำเร็จ 0 สาย'));

// An empty heading every morning is what teaches people to skip a section.
$out = arayaRenderChat(arayaChatFixtureBrief());
foreach (['อัตราโอนสายสูงกว่าภาพรวม', 'วางสายเร็ว', 'Conversion ต่ำกว่าภาพรวม', 'จองไม่ติด'] as $h) {
    test("no heading '{$h}' on a day with no findings", !str_contains($out, $h));
}

// All four at once: the separator count must keep up, or the message runs
// together into one block.
$out = arayaRenderChat(arayaChatFixtureBrief([
    'forwardingHigh' => [['name' => 'A', 'po' => '', 'pct' => 20.0, 'fleet_pct' => 12.4, 'total' => 100, 'forwarded' => 20]],
    'hangupHigh' => [['name' => 'B', 'po' => '', 'pct' => 30.0, 'fleet_pct' => 25.0, 'total' => 100, 'short' => 30]],
    'conversionLow' => [['name' => 'C', 'po' => '', 'pct' => 10.0, 'fleet_pct' => 60.0, 'total' => 100, 'booked' => 10, 'base' => 100]],
    'bookingBroken' => [['name' => 'D', 'po' => '', 'failed' => 2, 'booked' => 1, 'total' => 50]],
]));
test("all four sections render together",
     str_contains($out, 'A* — 20.0%') && str_contains($out, 'B* — 30.0%')
     && str_contains($out, 'C* — 10.0%') && str_contains($out, 'ล้มเหลว 2 สาย'));
// Overview + upsell are always printed, plus the four here: 6 sections, so 5
// rules between them and 1 under the header.
test("the separators keep up with the new sections", substr_count($out, ARAYA_CHAT_SEP) === 6);
test("it is still one message", substr_count($out, 'ARAYA DAILY BRIEF') === 1);

// The window label follows the constant rather than being written out, so it
// cannot keep saying "7 วันล่าสุด" after someone changes the rule.
test("the window label matches the configured window",
     str_contains($out, ARAYA_RATE_WINDOW === 'd7' ? '7 วันล่าสุด' : '30 วันล่าสุด'));

// The thresholds are read from the constants, for the same reason.
test("the hang-up heading quotes the real threshold",
     str_contains($out, (string)ARAYA_SHORT_ALERT_PCT . '%'));

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
