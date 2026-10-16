<?php
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

// ---- Stubs ----------------------------------------------------------------
// upDownMondayRequestRetrying and the repo writers are replaced with recorders, so
// these tests exercise the orchestration's decisions without a network call or
// a database write. Declared BEFORE the real files load; the production files
// guard with function_exists, so these definitions win.

$GLOBALS['stub_responses'] = [];
$GLOBALS['stub_calls']     = [];
$GLOBALS['stub_writes']    = [];

function upDownMondayRequestRetrying(string $token, string $gql, int $attempts = 3): array
{
    $GLOBALS['stub_calls'][] = $gql;
    $next = array_shift($GLOBALS['stub_responses']);
    return $next ?? ['error' => 'stub exhausted'];
}

function upDownUpsertShop($db, array $shop, string $syncedAt): string
{
    $GLOBALS['stub_writes'][] = ['upsert_shop', $shop['shop_id'], $shop['email']];
    return 'inserted';
}

function upDownUpsertSubscription($db, array $sub, string $syncedAt): string
{
    $GLOBALS['stub_writes'][] = ['upsert_sub', $sub['sub_id'], $sub['shop_id']];
    return 'inserted';
}

function upDownDeleteStaleShops($db, string $country, string $syncedAt): int
{
    $GLOBALS['stub_writes'][] = ['delete_shops', $country];
    return 0;
}

function upDownDeleteStaleSubscriptions($db, string $syncedAt): int
{
    $GLOBALS['stub_writes'][] = ['delete_subs'];
    return 0;
}

function upDownLoadShopNameMap($db, string $country): array { return []; }

require_once __DIR__ . '/../sync/mirrorMatch.php';
require_once __DIR__ . '/../sync/mondayClient.php';
require_once __DIR__ . '/../sync/mondayMirror.php';

function resetStubs(array $responses) {
    $GLOBALS['stub_responses'] = $responses;
    $GLOBALS['stub_calls']     = [];
    $GLOBALS['stub_writes']    = [];
}

/** One page of project items; a non-null cursor forces another page. */
function projectPage(array $ids, $cursor) {
    $items = [];
    foreach ($ids as $id) {
        $items[] = ['id' => (string) $id, 'name' => "Shop {$id}", 'column_values' => [
            ['id' => 'color1', 'text' => 'Thai'],
            ['id' => 'connect_boards0', 'text' => '', 'linked_item_ids' => ["c{$id}"]],
        ]];
    }
    return ['data' => ['boards' => [['id' => '1', 'items_page' => [
        'cursor' => $cursor, 'items' => $items,
    ]]]]];
}

function contactBatch(array $ids) {
    $items = [];
    foreach ($ids as $id) {
        $items[] = ['id' => (string) $id, 'name' => 'N', 'column_values' => [
            ['id' => 'text0', 'text' => 'First'],
            ['id' => 'contact_email', 'text' => "{$id}@shop.com"],
        ]];
    }
    return ['data' => ['items' => $items]];
}

echo "== upDownFetchAllPages: a failed page discards partial rows ==\n";
resetStubs([projectPage([1, 2], 'CURSOR1'), ['error' => 'curl timeout']]);
$r = upDownFetchAllPages('tok',
    function ($c) { return upDownProjectPageQuery(1943203205, $c); },
    function ($res) { return upDownExtractProjectRows($res, 'TH', 1943203205); });
test("reports the error", $r['error'] === 'curl timeout');
test("returns NO rows, not the first page's two", $r['rows'] === []);

echo "== upDownFetchAllPages: an error_message body is not success ==\n";
// Monday answers rate limits with error_message and no data. An error-key check
// would read this as success; the isset($res['data']) test catches it.
resetStubs([['error_message' => 'Rate limit exceeded', 'error_code' => 'RateLimit']]);
$r = upDownFetchAllPages('tok',
    function ($c) { return upDownProjectPageQuery(1943203205, $c); },
    function ($res) { return upDownExtractProjectRows($res, 'TH', 1943203205); });
test("treated as an error", $r['error'] !== null);
test("returns no rows", $r['rows'] === []);

echo "== upDownFetchAllPages: the page cap does not truncate silently ==\n";
// Every page carries a cursor, so the loop exhausts UP_DOWN_MAX_PAGES with more
// pending. Returning the partial set would feed a truncated board to the
// stale-row deletion.
$always = [];
for ($i = 0; $i < UP_DOWN_MAX_PAGES + 2; $i++) { $always[] = projectPage([$i], 'MORE'); }
resetStubs($always);
$r = upDownFetchAllPages('tok',
    function ($c) { return upDownProjectPageQuery(1943203205, $c); },
    function ($res) { return upDownExtractProjectRows($res, 'TH', 1943203205); });
test("reports an error rather than success", $r['error'] !== null);
test("the error names the page cap", strpos($r['error'], 'page cap') !== false);
test("returns no rows", $r['rows'] === []);
test("stopped at the cap", count($GLOBALS['stub_calls']) === UP_DOWN_MAX_PAGES);

echo "== upDownSyncCountry: a failed project fetch writes nothing ==\n";
resetStubs([['error' => 'boom']]);
$s = upDownSyncCountry(null, 'tok', 'TH', '2026-01-01 00:00:00');
test("reports the error", $s['error'] !== null);
test("no writes at all", $GLOBALS['stub_writes'] === []);

echo "== upDownSyncCountry: a zero-item board writes nothing ==\n";
resetStubs([projectPage([], null)]);
$s = upDownSyncCountry(null, 'tok', 'TH', '2026-01-01 00:00:00');
test("reports zero items as an error", $s['error'] === 'board returned zero items');
test("no writes at all", $GLOBALS['stub_writes'] === []);

echo "== upDownSyncCountry: a failed CONTACT batch must not erase emails ==\n";
// The upsert sets email = VALUES(email) unconditionally, so writing a shop with
// an empty email ERASES the mirrored address -- and the email is the lookup key.
resetStubs([
    projectPage([1, 2], null),                                      // projects fine
    ['errors' => [['message' => 'complexity budget exhausted']]],   // contacts fail
]);
$s = upDownSyncCountry(null, 'tok', 'TH', '2026-01-01 00:00:00');
test("reports an error instead of partial success", $s['error'] !== null);
test("the error names the contact batch", strpos($s['error'], 'contact batch') !== false);
// Inspects the email actually passed to the upsert, rather than merely
// observing that nothing was written: the name has to be true for the reason it
// claims, and this also catches a write that slips through with a blank address.
test("no shop was written with a blank email",
     array_filter($GLOBALS['stub_writes'],
         function ($w) { return $w[0] === 'upsert_shop' && $w[2] === ''; }) === []);
test("nothing was written at all", $GLOBALS['stub_writes'] === []);

echo "== upDownSyncCountry: an EMPTY contacts result must not erase emails ==\n";
// Success-shaped body, empty items array. Without a guard this writes every
// shop with a blank email and runs the stale deletion, reporting no error.
resetStubs([projectPage([1, 2], null), ['data' => ['items' => []]]]);
$s = upDownSyncCountry(null, 'tok', 'TH', '2026-01-01 00:00:00');
test("reports an error", $s['error'] !== null);
test("the error names the empty contact batch",
     strpos($s['error'], 'no items') !== false);
test("no shop was written with a blank email",
     array_filter($GLOBALS['stub_writes'],
         function ($w) { return $w[0] === 'upsert_shop' && $w[2] === ''; }) === []);
test("stale deletion did NOT run",
     !in_array(['delete_shops', 'TH'], $GLOBALS['stub_writes'], true));

echo "== upDownSyncCountry: the happy path still writes and deletes ==\n";
resetStubs([projectPage([1, 2], null), contactBatch(['c1', 'c2'])]);
$s = upDownSyncCountry(null, 'tok', 'TH', '2026-01-01 00:00:00');
test("no error", $s['error'] === null);
test("both shops written", count(array_filter($GLOBALS['stub_writes'],
     function ($w) { return $w[0] === 'upsert_shop'; })) === 2);
test("emails came through", $GLOBALS['stub_writes'][0][2] === 'c1@shop.com');
test("counts the shops with an email", $s['withEmail'] === 2);
test("stale deletion ran", in_array(['delete_shops', 'TH'], $GLOBALS['stub_writes'], true));

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
