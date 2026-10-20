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

echo "== deferred sections are absent, not empty headings ==\n";
$out = render(res([mk()]));
foreach (['รายได้', 'Subscription', 'Top-up', 'คุณภาพสาย', 'Respond.io', 'ค้างชำระ'] as $deferred) {
    test("no heading for the deferred '{$deferred}'", !str_contains($out, $deferred));
}

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
foreach ($kinds as $k) {
    $b = $emptyBrief;
    $b['gaps'] = [['kind' => $k, 'name' => "Shop-{$k}", 'detail' => "detail for {$k}"]];
    $out = arayaRenderChat($b);
    test("gap kind '{$k}' names its shop and kind", str_contains($out, "Shop-{$k}") && str_contains($out, "[{$k}]"));
    test("gap kind '{$k}' renders a Thai detail, not the English one",
        !str_contains($out, "detail for {$k}") && preg_match('/\] [^\n]*[\x{0E00}-\x{0E7F}]/u', $out) === 1);
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
$order = ['ดึงข้อมูลนาทีไม่ได้', 'ARAYA DAILY BRIEF', 'LIVE: 1', 'S-live', 'S-upsell', 'S-idlenew', 'S-idlequiet', 'S-setup', 'S-cancel', 'S-gap'];
$pos = array_map(fn($s) => mb_strpos($out, $s), $order);
test("every section is present", !in_array(false, $pos, true));
$sorted = $pos; sort($sorted);
test("sections appear in the documented order", $pos === $sorted);
foreach (['✅ ร้านที่ LIVE (นาทีคงเหลือ · อยู่กับเรามานาน)', '💰 โอกาสอัพเซลล์ (นาที <70 และรีเฟรชอีก ≤15 วัน)', '📉 ไลฟ์แล้วแต่ไม่มีการใช้งาน',
          '📉 เคยใช้แล้วเงียบไป', '🚧 ทำไมยังไม่ไลฟ์ (Setup ค้าง)', '❌ แจ้งยกเลิก', '⚠️ ข้อมูลไม่ครบ'] as $h) {
    test("heading '{$h}' is verbatim", str_contains($out, "\n" . $h . "\n"));
}
test("sections are separated by the rule", substr_count($out, ARAYA_CHAT_SEP) >= 8);
test("it is one message (no split markers)", substr_count($out, 'ARAYA DAILY BRIEF') === 1);

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
