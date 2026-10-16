<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../assets/db/db.php';
require_once __DIR__ . '/../assets/db/initDB.php';
require_once __DIR__ . '/../sync/mirrorMatch.php';
require_once __DIR__ . '/../sync/mirrorRepo.php';

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

// Derived from this file's own location rather than hardcoded, because the
// checkout has moved once already (a git worktree that was later removed) and
// a baked-in path turns every assertion here into a false failure.
// UD_TEST_BASE_URL overrides it for a host served from somewhere else.
$udDocRoot = realpath(__DIR__ . '/../../..');
$udAppDir  = $udDocRoot === false ? 'masterPanel' : basename($udDocRoot);
define('ENDPOINT', rtrim(getenv('UD_TEST_BASE_URL') ?: "http://localhost/{$udAppDir}", '/')
    . '/modules/upgrade-downgrade/assets/API/lookupByEmail.php');

/** @return array{status:int, json:?array, body:string} */
function call(array $params): array
{
    $url = ENDPOINT . '?' . http_build_query($params);
    $ch  = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15]);
    $body   = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'json' => json_decode((string) $body, true), 'body' => (string) $body];
}

// Fixture
$db->query("DELETE FROM l4u_up_down_subscriptions WHERE sub_id LIKE 'TEST\_%'");
$db->query("DELETE FROM l4u_up_down_shops WHERE country = 'ZZ'");
$now = date('Y-m-d H:i:s');
upDownUpsertShop($db, [
    'shop_id' => 'TEST_SHOP_EP', 'country' => 'ZZ', 'email' => 'endpoint@testshop.com',
    'shop_name' => 'Endpoint Shop', 'shop_type' => 'Thai', 'owner_name' => 'Ep Owner',
    'phone' => '+66800000000', 'board_id' => '1943203205',
], $now);
upDownUpsertSubscription($db, [
    'sub_id' => 'TEST_SUB_EP', 'shop_id' => 'TEST_SHOP_EP',
    'raw_name' => 'THB - 4900 LSS01M00 Local Starter Solo', 'product_group' => 'Local Starter Solo',
    'price' => '4900.00', 'currency' => 'THB', 'account_name' => 'Endpoint Shop',
    'matched_by' => 'relation',
], $now);

echo "== reachability ==\n";
$r = call(['country' => 'ZZ', 'email' => 'endpoint@testshop.com']);
if ($r['status'] === 0) {
    echo "  FAIL: cannot reach {$r['body']} -- is Apache running and is the project at /masterPanel?\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
}
test("responds 200", $r['status'] === 200);
test("returns JSON", is_array($r['json']));

echo "== a match ==\n";
test("found", ($r['json']['found'] ?? null) === true);
test("returns the shop name", ($r['json']['shop']['shopName'] ?? '') === 'Endpoint Shop');
test("returns one subscription", count($r['json']['subscriptions'] ?? []) === 1);
test("subscription name is the product group",
     ($r['json']['subscriptions'][0]['name'] ?? '') === 'Local Starter Solo');

$shopKeys = array_keys($r['json']['shop'] ?? []);
test("the hit actually returned a shop object, so the key checks below mean something",
     $shopKeys !== []);
// Asserted POSITIVELY, against the exact key set. A negative !isset() check
// passes vacuously whenever the payload is null -- which is what happens on a
// 404 or any failed decode -- so the two assertions guarding this endpoint's
// headline promises were the two that proved nothing. Pinning the key list also
// catches a field added later, which a !isset() check never would.
sort($shopKeys);
test("the shop returns exactly the fields the form fills, and no email",
     $shopKeys === ['boardId', 'ownerName', 'phone', 'shopId', 'shopName', 'shopType']);

$subKeys = array_keys($r['json']['subscriptions'][0] ?? []);
sort($subKeys);
test("a subscription returns exactly id, name and rawName, with no price",
     $subKeys === ['id', 'name', 'rawName']);

echo "== no match ==\n";
$r = call(['country' => 'ZZ', 'email' => 'nobody@nowhere.com']);
test("responds 200 for a miss, not 404", $r['status'] === 200);
test("found is false", ($r['json']['found'] ?? null) === false);

echo "== rejected input ==\n";
$r = call(['country' => 'ZZ']);
test("a missing email responds 200", $r['status'] === 200);
test("a missing email reports found false", ($r['json']['found'] ?? null) === false);
test("a missing email gives a reason", !empty($r['json']['reason']));

$r = call(['email' => 'endpoint@testshop.com']);
test("a missing country reports found false", ($r['json']['found'] ?? null) === false);

$r = call(['country' => 'ZZ', 'email' => 'not-an-email']);
test("a malformed email reports found false", ($r['json']['found'] ?? null) === false);
test("a malformed email gives a reason", !empty($r['json']['reason']));

$r = call(['country' => 'TOOLONG', 'email' => 'endpoint@testshop.com']);
test("an over-long country reports found false", ($r['json']['found'] ?? null) === false);

echo "== injection ==\n";
$r = call(['country' => 'ZZ', 'email' => "' OR 1=1 --"]);
test("an injection string reports found false", ($r['json']['found'] ?? null) === false);
test("the fixture row survives",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_up_down_shops WHERE shop_id = ?", 'TEST_SHOP_EP')
              ->fetchArray()['c'] === 1);

echo "== method ==\n";
$ch = curl_init(ENDPOINT . '?' . http_build_query(['country' => 'ZZ', 'email' => 'endpoint@testshop.com']));
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => '', CURLOPT_TIMEOUT => 15]);
$body = curl_exec($ch);
$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
test("POST is rejected with 405", $status === 405);

echo "== the log directory is not web-readable ==\n";
// The deny is Apache-only. nginx ignores .htaccess entirely and would serve the
// client IPs and the email-hash list with no error, so this must be asserted
// rather than assumed -- it is the control that detects enumeration.
foreach (['logs/', 'logs/lookup.log'] as $path) {
    $probe = preg_replace('#assets/API/lookupByEmail\.php$#', 'assets/' . $path, ENDPOINT);
    $ch = curl_init($probe);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    test("{$path} is not served over HTTP (got {$code})", $code === 403 || $code === 404);
}

echo "== response headers ==\n";
// Nothing in the suite covered these, so adding CORS or dropping no-store would
// have gone unnoticed -- both are stated security properties of this endpoint.
$ch = curl_init(ENDPOINT . '?' . http_build_query(['country' => 'ZZ', 'email' => 'endpoint@testshop.com']));
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true,
                        CURLOPT_NOBODY => true, CURLOPT_TIMEOUT => 15]);
$rawHeaders = (string) curl_exec($ch);
curl_close($ch);
test("sends Cache-Control: no-store, so a proxy cannot retain a shop's details",
     stripos($rawHeaders, 'Cache-Control: no-store') !== false);
test("sends no Access-Control-Allow-Origin, keeping it same-origin only",
     stripos($rawHeaders, 'Access-Control-Allow-Origin') === false);
test("sends JSON content type", stripos($rawHeaders, 'application/json') !== false);

echo "== array input cannot leak a path or break the JSON ==\n";
// A (string) cast on an array emits a warning carrying this file's absolute
// path, and that warning also makes the body invalid JSON.
$r = call(['country' => ['ZZ'], 'email' => 'endpoint@testshop.com']);
test("still valid JSON", is_array($r['json']));
// Asserted as "the body is nothing but the expected keys" rather than by hunting
// for ':\' and 'Warning'. Those two only match a Windows path and that one
// severity: a POSIX "Fatal error" or "Notice" leak -- the shape production would
// actually produce -- slipped straight through.
test("the body carries nothing but the expected keys",
     is_array($r['json'])
     && array_diff(array_keys($r['json']), ['found', 'reason']) === []);
test("reports found false", ($r['json']['found'] ?? null) === false);

echo "== a newline in country cannot forge a log line ==\n";
$logFile = __DIR__ . '/../assets/logs/lookup.log';
$linesBefore = is_file($logFile) ? count(file($logFile)) : 0;
call(['country' => "Z\n[2026-01-01 00:00:00] 9.9.9.9 country=XX email=forged outcome=found",
      'email'   => 'endpoint@testshop.com']);
$linesAfter = is_file($logFile) ? count(file($logFile)) : 0;
test("one request appends exactly one log line", ($linesAfter - $linesBefore) === 1);

$db->query("DELETE FROM l4u_up_down_subscriptions WHERE sub_id LIKE 'TEST\_%'");
$db->query("DELETE FROM l4u_up_down_shops WHERE country = 'ZZ'");
echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
