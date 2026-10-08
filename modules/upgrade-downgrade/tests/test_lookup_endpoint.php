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

const ENDPOINT = 'http://localhost/masterPanel-ud-lookup/modules/upgrade-downgrade/assets/API/lookupByEmail.php';

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
$db->query("DELETE FROM l4u_ud_subscriptions WHERE sub_id LIKE 'TEST\_%'");
$db->query("DELETE FROM l4u_ud_shops WHERE country = 'ZZ'");
$now = date('Y-m-d H:i:s');
udmUpsertShop($db, [
    'shop_id' => 'TEST_SHOP_EP', 'country' => 'ZZ', 'email' => 'endpoint@testshop.com',
    'shop_name' => 'Endpoint Shop', 'shop_type' => 'Thai', 'owner_name' => 'Ep Owner',
    'phone' => '+66800000000', 'board_id' => '1943203205',
], $now);
udmUpsertSubscription($db, [
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
test("no price reaches the client",
     !isset($r['json']['subscriptions'][0]['price'])
     && !isset($r['json']['subscriptions'][0]['currency']));
test("does not echo the email back", !isset($r['json']['shop']['email']));

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
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_ud_shops WHERE shop_id = ?", 'TEST_SHOP_EP')
              ->fetchArray()['c'] === 1);

echo "== method ==\n";
$ch = curl_init(ENDPOINT . '?' . http_build_query(['country' => 'ZZ', 'email' => 'endpoint@testshop.com']));
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => '', CURLOPT_TIMEOUT => 15]);
$body = curl_exec($ch);
$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
test("POST is rejected with 405", $status === 405);

$db->query("DELETE FROM l4u_ud_subscriptions WHERE sub_id LIKE 'TEST\_%'");
$db->query("DELETE FROM l4u_ud_shops WHERE country = 'ZZ'");
echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
