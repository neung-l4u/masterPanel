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

// Fixture rows use a reserved country code 'ZZ' and TEST_ ids so they can
// never collide with mirrored production data.
function cleanup($db) {
    $db->query("DELETE FROM l4u_ud_subscriptions WHERE sub_id LIKE 'TEST_%'");
    $db->query("DELETE FROM l4u_ud_shops WHERE country = 'ZZ'");
}
cleanup($db);

$now   = '2026-10-05 06:00:00';
$later = '2026-10-05 18:00:00';

$shopA = [
    'shop_id' => 'TEST_SHOP_A', 'country' => 'ZZ',
    'email' => 'owner@testshop.com', 'shop_name' => 'Test Shop A',
    'shop_type' => 'Thai Restaurants', 'owner_name' => 'Somchai Test',
    'phone' => '+66812345678', 'board_id' => '1943203205',
];

echo "== udmUpsertShop ==\n";
test("first write reports inserted", udmUpsertShop($db, $shopA, $now) === 'inserted');
test("second write reports updated", udmUpsertShop($db, $shopA, $now) === 'updated');

$row = $db->query("SELECT * FROM l4u_ud_shops WHERE shop_id = ?", 'TEST_SHOP_A')->fetchArray();
test("stores shop_name", $row['shop_name'] === 'Test Shop A');
test("stores email", $row['email'] === 'owner@testshop.com');
test("stores synced_at", $row['synced_at'] === $now);

$shopA['shop_name'] = 'Test Shop A Renamed';
udmUpsertShop($db, $shopA, $later);
$row = $db->query("SELECT * FROM l4u_ud_shops WHERE shop_id = ?", 'TEST_SHOP_A')->fetchArray();
test("upsert overwrites a changed name", $row['shop_name'] === 'Test Shop A Renamed');
test("upsert advances synced_at", $row['synced_at'] === $later);

echo "== udmUpsertSubscription ==\n";
$subA = [
    'sub_id' => 'TEST_SUB_A', 'shop_id' => 'TEST_SHOP_A',
    'raw_name' => 'THB - 4900 LSS01M00 Local Starter Solo - No Contract',
    'product_group' => 'Local Starter Solo', 'price' => '4900.00',
    'currency' => 'THB', 'account_name' => 'Test Shop A',
    'matched_by' => 'relation',
];
test("first write reports inserted", udmUpsertSubscription($db, $subA, $later) === 'inserted');
test("second write reports updated", udmUpsertSubscription($db, $subA, $later) === 'updated');

$row = $db->query("SELECT * FROM l4u_ud_subscriptions WHERE sub_id = ?", 'TEST_SUB_A')->fetchArray();
test("stores product_group", $row['product_group'] === 'Local Starter Solo');
test("stores price as decimal", (float) $row['price'] === 4900.0);
test("stores matched_by", $row['matched_by'] === 'relation');

$subNull = $subA;
$subNull['sub_id'] = 'TEST_SUB_NULLPRICE';
$subNull['price']  = null;
udmUpsertSubscription($db, $subNull, $later);
$row = $db->query("SELECT price FROM l4u_ud_subscriptions WHERE sub_id = ?", 'TEST_SUB_NULLPRICE')->fetchArray();
test("a null price stays null, not 0.00", $row['price'] === null);

echo "== udmLoadShopNameMap ==\n";
$map = udmLoadShopNameMap($db, 'ZZ');
test("keys are normalized names", isset($map['test shop a renamed']));
test("value is the shop id", ($map['test shop a renamed'] ?? '') === 'TEST_SHOP_A');

echo "== stale deletion ==\n";
// A shop written in the earlier run and not refreshed in this one is stale.
$shopB = $shopA;
$shopB['shop_id']   = 'TEST_SHOP_B';
$shopB['shop_name'] = 'Test Shop B';
$shopB['email']     = 'b@testshop.com';
udmUpsertShop($db, $shopB, $now);   // older timestamp

$deleted = udmDeleteStaleShops($db, 'ZZ', $later);
test("deletes the shop not seen in this run", $deleted === 1);
test("keeps the shop seen in this run",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_ud_shops WHERE shop_id = ?", 'TEST_SHOP_A')
              ->fetchArray()['c'] === 1);
test("stale shop is gone",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_ud_shops WHERE shop_id = ?", 'TEST_SHOP_B')
              ->fetchArray()['c'] === 0);

$subStale = $subA;
$subStale['sub_id'] = 'TEST_SUB_STALE';
udmUpsertSubscription($db, $subStale, $now);   // older timestamp
$deletedSubs = udmDeleteStaleSubscriptions($db, $later);
test("deletes subscriptions not seen in this run", $deletedSubs >= 1);
test("keeps subscriptions seen in this run",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_ud_subscriptions WHERE sub_id = ?", 'TEST_SUB_A')
              ->fetchArray()['c'] === 1);

echo "== country isolation ==\n";
// Deleting stale rows for one country must not touch another country's rows.
$shopC = $shopA;
$shopC['shop_id'] = 'TEST_SHOP_C';
$shopC['country'] = 'ZY';
$shopC['email']   = 'c@testshop.com';
udmUpsertShop($db, $shopC, $now);
udmDeleteStaleShops($db, 'ZZ', $later);
test("a different country's stale row survives",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_ud_shops WHERE shop_id = ?", 'TEST_SHOP_C')
              ->fetchArray()['c'] === 1);
$db->query("DELETE FROM l4u_ud_shops WHERE country = 'ZY'");

cleanup($db);
echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
