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
        ['id' => 'text_mks6asn5', 'text' => 'shop@shop.com', 'value' => '"shop@shop.com"'],
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
test("email comes from text_mks6asn5 (the column gcal matches on)", $row['email'] === 'shop@shop.com');
test("email_alt comes from email_mm7r176d, and differs from email", $row['email_alt'] === 'Owner@Shop.com' && $row['email'] !== $row['email_alt']);
$expectedKeys = ['name', 'live_status', 'setup_status', 'po', 'register_date', 'live_date',
                 'reason_for_cancel', 'note', 'calendar_id', 'email', 'email_alt'];
$gotKeys = array_keys($row); sort($gotKeys); sort($expectedKeys);
test("the row has exactly the eleven keys the consumers read", $gotKeys === $expectedKeys);

echo "== monday: missing and odd columns ==\n";
$bare = arayaFlattenMondayItem(['id' => '2', 'name' => 'Bare', 'column_values' => []]);
test("absent columns default to empty strings", $bare['live_status'] === '' && $bare['live_date'] === '' && $bare['email'] === '' && $bare['email_alt'] === '');
$odd = arayaFlattenMondayItem(['id' => '3', 'name' => 'Odd', 'column_values' => [
    ['id' => 'date4', 'text' => '', 'value' => null],
    ['id' => 'date_mm7zba5t', 'text' => 'Sept 15', 'value' => '{"date":"not-a-date"}'],
    ['id' => 'email_mm7r176d', 'text' => '', 'value' => '{"email":"hidden@x.com"}'],
    ['id' => 'text_mks6asn5', 'text' => '', 'value' => null],
    ['id' => 'unrelated_col', 'text' => 'ignored', 'value' => null],
]]);
test("a null date value reads as empty", $odd['register_date'] === '');
test("an unparseable date reads as empty, not garbage", $odd['live_date'] === '');
test("empty email_alt text falls back to the JSON value", $odd['email_alt'] === 'hidden@x.com');
test("an empty email column stays empty and is not filled from email_alt", $odd['email'] === '');
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

echo "== monday: page walk (fake poster) ==\n";
function fakePage(array $names, ?string $cursor, bool $first): array {
    $items = array_map(fn($n) => ['id' => $n, 'name' => $n, 'column_values' => []], $names);
    $page = ['cursor' => $cursor, 'items' => $items];
    return ['data' => $first ? ['boards' => [['items_page' => $page]]] : ['next_items_page' => $page]];
}
/** Fake poster serving $pages in order; records each cursor it is handed. */
function fakePoster(array $pages, array &$seen): callable {
    return function (?string $cursor) use ($pages, &$seen): array {
        $i = count($seen);
        $seen[] = $cursor;
        if (!isset($pages[$i])) { throw new LogicException('poster called past the end'); }
        $p = $pages[$i];
        if ($p instanceof Throwable) { throw $p; }
        return $p;
    };
}
$names = fn(array $rows) => array_column($rows, 'name');

$seen = [];
$rows = arayaCollectMondayPages(fakePoster([
    fakePage(['a', 'b'], 'c1', true), fakePage(['c'], 'c2', false), fakePage(['d', 'e'], null, false),
], $seen));
test("three pages merge every row in page order", $names($rows) === ['a', 'b', 'c', 'd', 'e']);
test("three pages: poster called exactly 3 times", count($seen) === 3);
test("three pages: cursors handed over are null, c1, c2", $seen === [null, 'c1', 'c2']);

$seen = [];
$rows = arayaCollectMondayPages(fakePoster([fakePage(['a'], 'c1', true), fakePage(['b'], '', false)], $seen));
test("an empty cursor ends the walk after the pages served", $names($rows) === ['a', 'b'] && count($seen) === 2);
$seen = [];
$rows = arayaCollectMondayPages(fakePoster([fakePage(['a'], 'c1', true), fakePage(['b'], null, false), fakePage(['zz'], null, false)], $seen));
test("a null cursor ends the walk and no further page is requested", $names($rows) === ['a', 'b'] && count($seen) === 2);

$seen = [];
$rows = arayaCollectMondayPages(fakePoster([fakePage(['a', 'b'], null, true)], $seen));
test("a single page with a null cursor returns exactly its rows", $names($rows) === ['a', 'b'] && $seen === [null]);

$seen = [];
$thrown = throwsRuntime(function () use (&$seen) { arayaCollectMondayPages(fakePoster([
    fakePage(['a'], 'c1', true), fakePage(['b'], 'c2', false), fakePage(['c'], 'c1', false),
], $seen)); }, $msg);
test("a repeated cursor throws RuntimeException", $thrown && strpos($msg, 'repeated cursor') !== false);
test("a repeated cursor stops the walk at the page that repeated it", count($seen) === 3);

$seen = [];
$endless = function (?string $cursor) use (&$seen): array {
    $seen[] = $cursor;
    $n = count($seen);
    if ($n > 500) { throw new LogicException('runaway walk: no page cap'); }
    return fakePage(["r{$n}"], "cur{$n}", $n === 1);
};
$thrown = throwsRuntime(fn() => arayaCollectMondayPages($endless), $msg);
test("an endless cursor throws at the page cap", $thrown && strpos($msg, 'page limit') !== false);
test("an endless cursor stops after exactly the cap of " . ARAYA_MONDAY_MAX_PAGES . " requests", count($seen) === ARAYA_MONDAY_MAX_PAGES);
test("the endless walk handed over null, cur1, cur2 in order", array_slice($seen, 0, 3) === [null, 'cur1', 'cur2']);

$seen = [];
$rows = arayaCollectMondayPages(fakePoster([
    fakePage(['a'], 'c1', true), fakePage([], 'c2', false), fakePage(['b', 'c'], null, false),
], $seen));
test("a non-null cursor with zero items does not end the walk early", $names($rows) === ['a', 'b', 'c']);
test("the empty page was followed by a request for its cursor", $seen === [null, 'c1', 'c2']);

$seen = [];
$result = null;
$caught = null;
try {
    $result = arayaCollectMondayPages(fakePoster([
        fakePage(['a', 'b'], 'c1', true),
        ['errors' => [['message' => 'boom']]],
    ], $seen));
} catch (RuntimeException $e) { $caught = $e; }
test("an error on a later page throws the page's own error, not a later guard", $caught !== null && strpos($caught->getMessage(), 'GraphQL error') !== false);
test("an error on a later page returns no partial rows", $result === null);
test("an error on a later page stopped the walk there", $seen === [null, 'c1']);

$seen = [];
$caught = null;
try {
    arayaCollectMondayPages(fakePoster([fakePage(['a'], 'c1', true), new RuntimeException('Monday returned HTTP 502.')], $seen));
} catch (RuntimeException $e) { $caught = $e; }
test("a transport failure on a later page propagates", $caught !== null && $caught->getMessage() === 'Monday returned HTTP 502.');

echo "== monday: arayaFetchMonday ==\n";
test("arayaFetchMonday is callable", is_callable('arayaFetchMonday'));

echo "== gcal call errors: good response ==\n";
$err = ['shop_id' => 'g1', 'shop_name' => 'Sabai Thai', 'started_at' => '2026-10-21T04:12:00+00:00',
        'outcome' => 'call.in-progress.error-vapifault-worker-not-available', 'duration_sec' => 12,
        'recording_url' => 'https://example.com/r.wav'];
$noRec = ['recording_url' => null] + $err;
$rows = arayaParseCallErrorsResponse(200, json_encode(['errors' => [$err, $noRec]]));
test("both rows come back, in upstream order", count($rows) === 2 && $rows[0] === $err && $rows[1] === $noRec);
test("a null recording_url survives parsing as null", $rows[1]['recording_url'] === null);
test("an empty errors list is a valid answer, not a failure",
     arayaParseCallErrorsResponse(200, '{"errors":[]}') === []);

echo "== gcal call errors: every failure throws ==\n";
test("a 500 throws", throwsRuntime(fn() => arayaParseCallErrorsResponse(500, '{"errors":[]}')));
test("a 401 throws", throwsRuntime(fn() => arayaParseCallErrorsResponse(401, '{"detail":"no"}')));
test("a non-JSON body throws", throwsRuntime(fn() => arayaParseCallErrorsResponse(200, '<html>oops</html>')));
test("an empty body throws", throwsRuntime(fn() => arayaParseCallErrorsResponse(200, '')));
test("a body without errors throws", throwsRuntime(fn() => arayaParseCallErrorsResponse(200, '{"shops":[]}')));
test("errors that is not an array throws", throwsRuntime(fn() => arayaParseCallErrorsResponse(200, '{"errors":"none"}')));
test("a non-object row throws", throwsRuntime(fn() => arayaParseCallErrorsResponse(200, '{"errors":["x"]}')));
throwsRuntime(fn() => arayaParseCallErrorsResponse(200, '<html>secret-body</html>'), $m);
test("the response body never reaches the message", strpos($m, 'secret-body') === false);
test("arayaFetchCallErrors is callable", is_callable('arayaFetchCallErrors'));

echo "== the call-errors list key ==" . PHP_EOL;
// The deployed endpoint names this list "calls"; this consumer was first
// written expecting "errors", so every fetch threw and the panel silently
// never rendered. Both names are accepted, and both are pinned here so the
// mismatch cannot come back from either side.
$row = ['shop_id' => 's1', 'shop_name' => 'A', 'started_at' => '2026-10-25T04:00:00+00:00',
        'outcome' => 'pipeline-error-openai-llm-failed', 'duration_sec' => 12,
        'recording_url' => 'https://example.test/r.mp3'];
foreach (['calls', 'errors'] as $key) {
    $body = json_encode(['generated_at' => '2026-10-26T02:00:00+00:00', $key => [$row]]);
    $out = arayaParseCallErrorsResponse(200, $body);
    test("a list under {$key} is read", count($out) === 1);
    test("the row survives under {$key}", ($out[0]['outcome'] ?? '') === 'pipeline-error-openai-llm-failed');
}

// An empty list is a real answer -- no errors today -- not a failure.
$out = arayaParseCallErrorsResponse(200, json_encode(['generated_at' => 'x', 'calls' => []]));
test("an empty list parses as no errors", $out === []);

// A body carrying neither name is a contract break and must be loud.
$threw = false;
try { arayaParseCallErrorsResponse(200, json_encode(['generated_at' => 'x'])); }
catch (RuntimeException $e) { $threw = true; }
test("a response with no call list throws", $threw);

echo "== gcal: the fleet block ==\n";
// The fleet figures are the baseline every per-shop rate is compared with, so
// the one thing that must not happen is an absent block reading as a fleet
// that took no calls: every shop would then look worse than the fleet.
$fleet = ['d7' => ['total' => 1000, 'forwarded' => 124, 'short' => 200, 'silent' => 40,
                   'errors' => 2, 'minutes' => 1800, 'booked' => 300,
                   'booking_failed' => 5, 'long' => 500, 'booked_unknown' => 0],
          'd30' => ['total' => 4000, 'forwarded' => 500, 'short' => 820, 'silent' => 150,
                    'errors' => 9, 'minutes' => 7200, 'booked' => 1200,
                    'booking_failed' => 18, 'long' => 2000, 'booked_unknown' => 60]];
$withFleet = json_encode(['generated_at' => 'x', 'shops' => [$shop], 'fleet_calls' => $fleet]);
test("the fleet block is passed through intact", arayaParseGcalFleet($withFleet) === $fleet);
// An older gcal does not send it at all. Null, so the dashboard shows no
// comparison rather than comparing against zeros.
test("a response without a fleet block gives null", arayaParseGcalFleet($body) === null);
test("a non-array fleet block gives null",
     arayaParseGcalFleet('{"shops":[],"fleet_calls":"none"}') === null);
test("a non-JSON body gives null, not a crash", arayaParseGcalFleet('<html>oops</html>') === null);
test("an empty body gives null", arayaParseGcalFleet('') === null);
// Shops and fleet are read from the same response, so they cannot disagree
// about which fetch they came from.
test("the shops still parse from a response carrying a fleet block",
     arayaParseGcalResponse(200, $withFleet) === [$shop]);

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
