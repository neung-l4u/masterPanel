<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../models/fetchGcal.php';
require_once __DIR__ . '/../models/fetchMonday.php';

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

/** True when $fn throws a RuntimeException; returns the message via $msg. */
function throwsRuntime(callable $fn, &$msg = null): bool {
    try { $fn(); } catch (RuntimeException $e) { $msg = $e->getMessage(); return true; }
    return false;
}

$nb = "\u{00A0}";

echo "== gcal: good response ==\n";
$shop = [
    'shop_id' => 'g1', 'shop_name' => 'Sabai Thai', 'calendar_id' => 'cal-1', 'shop_email' => 'a@x.com',
    'remaining_minutes' => 120, 'allocation' => 500, 'billing_day' => 5, 'days_until_refresh' => 9,
    'currency' => 'AUD', 'closed_periods' => [['period_end' => '2026-10-05', 'used_minutes' => 400]],
];
$body = json_encode(['generated_at' => '2026-10-20T01:00:00Z', 'shops' => [$shop]]);
$rows = arayaParseGcalResponse(200, $body);
test("one shop comes back", count($rows) === 1);
test("the shop row is passed through intact", $rows[0] === $shop);

echo "== gcal: every failure throws, none returns an empty array ==\n";
test("a 500 throws", throwsRuntime(fn() => arayaParseGcalResponse(500, $body)));
test("a 401 throws", throwsRuntime(fn() => arayaParseGcalResponse(401, '{"detail":"no"}')));
test("a non-JSON body throws", throwsRuntime(fn() => arayaParseGcalResponse(200, '<html>oops</html>')));
test("an empty body throws", throwsRuntime(fn() => arayaParseGcalResponse(200, '')));
test("a body without shops throws", throwsRuntime(fn() => arayaParseGcalResponse(200, '{"generated_at":"x"}')));
test("shops that is not an array throws", throwsRuntime(fn() => arayaParseGcalResponse(200, '{"shops":"none"}')));
test("an empty shops list throws", throwsRuntime(fn() => arayaParseGcalResponse(200, '{"shops":[]}')));
test("a non-object shop throws", throwsRuntime(fn() => arayaParseGcalResponse(200, '{"shops":["x"]}')));
throwsRuntime(fn() => arayaParseGcalResponse(200, '<html>secret-body</html>'), $m);
test("the response body never reaches the message", strpos($m, 'secret-body') === false);

echo "== monday: flattening ==\n";
$item = [
    'id' => '111', 'name' => " Sabai Thai{$nb}",
    'column_values' => [
        ['id' => 'color_mks6nf9m', 'text' => 'Live', 'value' => '{"index":1}'],
        ['id' => 'status', 'text' => 'Done', 'value' => '{"index":2}'],
        ['id' => 'multiple_person_mm1jwyxn', 'text' => 'Beam, Mark', 'value' => '{"personsAndTeams":[]}'],
        ['id' => 'date4', 'text' => '2026-08-01', 'value' => '{"date":"2026-08-01","changed_at":"2026-08-01T00:00:00.000Z"}'],
        ['id' => 'date_mm7zba5t', 'text' => '2026-09-15', 'value' => '{"date":"2026-09-15"}'],
        ['id' => 'text_mm7z30rd', 'text' => '', 'value' => null],
        ['id' => 'text_mm1b8q0z', 'text' => "  waiting on POS{$nb}", 'value' => '"waiting on POS"'],
        ['id' => 'text_mm2y84v4', 'text' => "{$nb}cal-1 ", 'value' => '"cal-1"'],
        ['id' => 'email_mm7r176d', 'text' => 'Owner@Shop.com', 'value' => '{"email":"Owner@Shop.com","text":"Owner@Shop.com"}'],
    ],
];
$row = arayaFlattenMondayItem($item);
test("name is trimmed of nbsp", $row['name'] === 'Sabai Thai');
test("live_status from text", $row['live_status'] === 'Live');
test("setup_status from text", $row['setup_status'] === 'Done');
test("po from text", $row['po'] === 'Beam, Mark');
test("register_date is YYYY-MM-DD", $row['register_date'] === '2026-08-01');
test("live_date is YYYY-MM-DD", $row['live_date'] === '2026-09-15');
test("empty text column is an empty string", $row['reason_for_cancel'] === '');
test("note is trimmed of nbsp", $row['note'] === 'waiting on POS');
test("calendar_id is trimmed of nbsp", $row['calendar_id'] === 'cal-1');
test("email read from the email column", $row['email'] === 'Owner@Shop.com');
$expectedKeys = ['name', 'live_status', 'setup_status', 'po', 'register_date', 'live_date',
                 'reason_for_cancel', 'note', 'calendar_id', 'email'];
$gotKeys = array_keys($row); sort($gotKeys); sort($expectedKeys);
test("the row has exactly the ten keys the consumers read", $gotKeys === $expectedKeys);

echo "== monday: missing and odd columns ==\n";
$bare = arayaFlattenMondayItem(['id' => '2', 'name' => 'Bare', 'column_values' => []]);
test("absent columns default to empty strings", $bare['live_status'] === '' && $bare['live_date'] === '' && $bare['email'] === '');
$odd = arayaFlattenMondayItem(['id' => '3', 'name' => 'Odd', 'column_values' => [
    ['id' => 'date4', 'text' => '', 'value' => null],
    ['id' => 'date_mm7zba5t', 'text' => 'Sept 15', 'value' => '{"date":"not-a-date"}'],
    ['id' => 'email_mm7r176d', 'text' => '', 'value' => '{"email":"hidden@x.com"}'],
    ['id' => 'unrelated_col', 'text' => 'ignored', 'value' => null],
]]);
test("a null date value reads as empty", $odd['register_date'] === '');
test("an unparseable date reads as empty, not garbage", $odd['live_date'] === '');
test("empty email text falls back to the JSON value", $odd['email'] === 'hidden@x.com');
test("an unrelated column is ignored", !in_array('ignored', $odd, true));
$datetimeOnly = arayaFlattenMondayItem(['name' => 'T', 'column_values' => [
    ['id' => 'date4', 'text' => '2026-01-02 10:30', 'value' => '{"date":"2026-01-02","time":"10:30:00"}'],
]]);
test("a date with a time part still yields the date", $datetimeOnly['register_date'] === '2026-01-02');

echo "== monday: Thai names survive ==\n";
$thai = "ร้านนวดสบายภ";
$thaiRow = arayaFlattenMondayItem(['name' => $thai . $nb, 'column_values' => [
    ['id' => 'text_mm1b8q0z', 'text' => $thai, 'value' => null],
]]);
test("a name ending in U+0E20 keeps its last character", $thaiRow['name'] === $thai);
test("a note ending in U+0E20 stays valid UTF-8", $thaiRow['note'] === $thai && mb_check_encoding($thaiRow['note'], 'UTF-8'));

echo "== monday: page parsing ==\n";
$page1 = ['data' => ['boards' => [['items_page' => ['cursor' => 'abc123', 'items' => [$item]]]]]];
$p = arayaParseMondayPage($page1, true);
test("first page yields its rows", count($p['rows']) === 1 && $p['rows'][0]['name'] === 'Sabai Thai');
test("first page surfaces the cursor", $p['cursor'] === 'abc123');

$page2 = ['data' => ['next_items_page' => ['cursor' => null, 'items' => [$item, $item]]]];
$p2 = arayaParseMondayPage($page2, false);
test("a later page reads from next_items_page", count($p2['rows']) === 2);
test("a null cursor ends pagination", $p2['cursor'] === null);

$p3 = arayaParseMondayPage(['data' => ['next_items_page' => ['cursor' => '', 'items' => []]]], false);
test("an empty-string cursor ends pagination", $p3['cursor'] === null);

echo "== monday: every failure throws ==\n";
test("a GraphQL errors key throws", throwsRuntime(fn() => arayaParseMondayPage(['errors' => [['message' => 'Complexity budget exhausted']]], true)));
test("errors alongside data still throws", throwsRuntime(fn() => arayaParseMondayPage(['errors' => [['message' => 'x']], 'data' => $page1['data']], true)));
test("a missing data key throws", throwsRuntime(fn() => arayaParseMondayPage(['account_id' => 1], true)));
test("a null data key throws", throwsRuntime(fn() => arayaParseMondayPage(['data' => null], true)));
test("an empty boards list throws", throwsRuntime(fn() => arayaParseMondayPage(['data' => ['boards' => []]], true)));
test("a missing items list throws", throwsRuntime(fn() => arayaParseMondayPage(['data' => ['boards' => [['items_page' => ['cursor' => null]]]]], true)));
test("a missing next page throws", throwsRuntime(fn() => arayaParseMondayPage(['data' => ['boards' => []]], false)));
test("a malformed item throws", throwsRuntime(fn() => arayaParseMondayPage(['data' => ['boards' => [['items_page' => ['items' => ['x']]]]]], true)));

echo "== monday: query ==\n";
$q1 = arayaMondayBuildQuery(null);
test("the first query targets the board", strpos($q1, 'boards(ids: [2032016220])') !== false && strpos($q1, 'items_page(limit: 100)') !== false);
foreach (array_keys(ARAYA_MONDAY_COLUMNS) as $col) {
    test("the query requests column {$col}", strpos($q1, '"' . $col . '"') !== false);
}
test("the query asks for a cursor", strpos($q1, 'cursor') !== false);
$q2 = arayaMondayBuildQuery('MSw5N|"q');
test("a later query uses next_items_page with the cursor", strpos($q2, 'next_items_page(limit: 100, cursor: "MSw5N|\\"q")') !== false);
test("a later query does not repeat the board", strpos($q2, '2032016220') === false);

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
