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

set_exception_handler(function ($e) use (&$db) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    // Clean up even on the failure path, so a crashed run does not leave
    // fixture rows behind for the next one.
    if (isset($db) && function_exists('cleanup')) cleanup($db);
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

// Fixture rows use a reserved country code 'ZZ' and TEST_ ids so they can
// never collide with mirrored production data.
// ZY is included because the country-isolation test below creates a row there;
// without it, a run that dies early leaves TEST_SHOP_C behind and the next
// run's cleanup does not clear it.
function cleanup($db) {
    $db->query("DELETE FROM l4u_up_down_subscriptions WHERE sub_id LIKE 'TEST\_%'");
    $db->query("DELETE FROM l4u_up_down_shops WHERE country IN ('ZZ','ZY')");
}
cleanup($db);

// Deliberately far in the past. upDownDeleteStaleSubscriptions is global by
// design (the subscriptions board is one board, synced in one pass), so a
// fixture timestamp anywhere near today would make this test delete real
// mirrored rows synced earlier the same day.
$now   = '2000-01-01 06:00:00';
$later = '2000-01-01 18:00:00';

$shopA = [
    'shop_id' => 'TEST_SHOP_A', 'country' => 'ZZ',
    'email' => 'owner@testshop.com', 'shop_name' => 'Test Shop A',
    'shop_type' => 'Thai Restaurants', 'owner_name' => 'Somchai Test',
    'phone' => '+66812345678', 'board_id' => '1943203205',
];

echo "== upDownUpsertShop ==\n";
test("first write reports inserted", upDownUpsertShop($db, $shopA, $now) === 'inserted');
test("second write reports updated", upDownUpsertShop($db, $shopA, $now) === 'updated');

$row = $db->query("SELECT * FROM l4u_up_down_shops WHERE shop_id = ?", 'TEST_SHOP_A')->fetchArray();
test("stores shop_name", $row['shop_name'] === 'Test Shop A');
test("stores email", $row['email'] === 'owner@testshop.com');

// The fixture email above is already lowercase and unpadded, so it cannot tell
// a normalized write from a raw one. Task 7's lookup queries by the normalized
// form, so an unnormalized write here would make that lookup silently miss.
$shopMixed = $shopA;
$shopMixed['shop_id'] = 'TEST_SHOP_MIXEDMAIL';
$shopMixed['email']   = "  Owner.MixedCase@TestShop.COM\xC2\xA0";
// Written at $later so it counts as "seen in this run" and is not swept up by
// the stale-shop deletion below (which expects to delete exactly TEST_SHOP_B).
upDownUpsertShop($db, $shopMixed, $later);
$rowMixed = $db->query("SELECT email FROM l4u_up_down_shops WHERE shop_id = ?", 'TEST_SHOP_MIXEDMAIL')->fetchArray();
test("normalizes a mixed-case padded email before storing",
     $rowMixed['email'] === 'owner.mixedcase@testshop.com');
test("stores synced_at", $row['synced_at'] === $now);

$shopA['shop_name'] = 'Test Shop A Renamed';
upDownUpsertShop($db, $shopA, $later);
$row = $db->query("SELECT * FROM l4u_up_down_shops WHERE shop_id = ?", 'TEST_SHOP_A')->fetchArray();
test("upsert overwrites a changed name", $row['shop_name'] === 'Test Shop A Renamed');
test("upsert advances synced_at", $row['synced_at'] === $later);

echo "== upDownUpsertSubscription ==\n";
$subA = [
    'sub_id' => 'TEST_SUB_A', 'shop_id' => 'TEST_SHOP_A',
    'raw_name' => 'THB - 4900 LSS01M00 Local Starter Solo - No Contract',
    'product_group' => 'Local Starter Solo', 'price' => '4900.00',
    'currency' => 'THB', 'account_name' => 'Test Shop A',
    'matched_by' => 'relation',
];
test("first write reports inserted", upDownUpsertSubscription($db, $subA, $later) === 'inserted');
test("second write reports updated", upDownUpsertSubscription($db, $subA, $later) === 'updated');

$row = $db->query("SELECT * FROM l4u_up_down_subscriptions WHERE sub_id = ?", 'TEST_SUB_A')->fetchArray();
test("stores product_group", $row['product_group'] === 'Local Starter Solo');
test("stores price as decimal", (float) $row['price'] === 4900.0);
test("stores matched_by", $row['matched_by'] === 'relation');

$subNull = $subA;
$subNull['sub_id'] = 'TEST_SUB_NULLPRICE';
$subNull['price']  = null;
upDownUpsertSubscription($db, $subNull, $later);
$row = $db->query("SELECT price FROM l4u_up_down_subscriptions WHERE sub_id = ?", 'TEST_SUB_NULLPRICE')->fetchArray();
test("a null price stays null, not 0.00", $row['price'] === null);

echo "== upDownLoadShopNameMap ==\n";
$map = upDownLoadShopNameMap($db, 'ZZ');
test("keys are normalized names", isset($map['test shop a renamed']));
test("value is the shop id", ($map['test shop a renamed'] ?? '') === 'TEST_SHOP_A');

echo "== stale deletion ==\n";
// A shop written in the earlier run and not refreshed in this one is stale.
$shopB = $shopA;
$shopB['shop_id']   = 'TEST_SHOP_B';
$shopB['shop_name'] = 'Test Shop B';
$shopB['email']     = 'b@testshop.com';
upDownUpsertShop($db, $shopB, $now);   // older timestamp

$deleted = upDownDeleteStaleShops($db, 'ZZ', $later);
test("deletes the shop not seen in this run", $deleted === 1);
test("keeps the shop seen in this run",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_up_down_shops WHERE shop_id = ?", 'TEST_SHOP_A')
              ->fetchArray()['c'] === 1);
test("stale shop is gone",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_up_down_shops WHERE shop_id = ?", 'TEST_SHOP_B')
              ->fetchArray()['c'] === 0);

$subStale = $subA;
$subStale['sub_id'] = 'TEST_SUB_STALE';
upDownUpsertSubscription($db, $subStale, $now);   // older timestamp
// Count real rows first: the delete is global, so this also proves it cannot
// reach anything outside the fixture window.
$realBefore = (int) $db->query(
    "SELECT COUNT(*) AS c FROM l4u_up_down_subscriptions WHERE sub_id NOT LIKE 'TEST\_%'"
)->fetchArray()['c'];

$deletedSubs = upDownDeleteStaleSubscriptions($db, $later);
test("deletes exactly the one stale fixture subscription", $deletedSubs === 1);
test("the stale fixture subscription is gone",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_up_down_subscriptions WHERE sub_id = ?", 'TEST_SUB_STALE')
              ->fetchArray()['c'] === 0);
test("no real subscription row was touched",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_up_down_subscriptions WHERE sub_id NOT LIKE 'TEST\_%'")
              ->fetchArray()['c'] === $realBefore);
test("keeps subscriptions seen in this run",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_up_down_subscriptions WHERE sub_id = ?", 'TEST_SUB_A')
              ->fetchArray()['c'] === 1);

echo "== country isolation ==\n";
// Deleting stale rows for one country must not touch another country's rows.
$shopC = $shopA;
$shopC['shop_id'] = 'TEST_SHOP_C';
$shopC['country'] = 'ZY';
$shopC['email']   = 'c@testshop.com';
upDownUpsertShop($db, $shopC, $now);
upDownDeleteStaleShops($db, 'ZZ', $later);
test("a different country's stale row survives",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_up_down_shops WHERE shop_id = ?", 'TEST_SHOP_C')
              ->fetchArray()['c'] === 1);
$db->query("DELETE FROM l4u_up_down_shops WHERE country = 'ZY'");

cleanup($db);
echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
