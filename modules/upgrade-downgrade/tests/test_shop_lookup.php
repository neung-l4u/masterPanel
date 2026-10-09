<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../assets/db/db.php';
require_once __DIR__ . '/../assets/db/initDB.php';
require_once __DIR__ . '/../sync/mirrorMatch.php';
require_once __DIR__ . '/../sync/mirrorRepo.php';
require_once __DIR__ . '/../models/shopLookup.php';

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

function cleanup($db) {
    $db->query("DELETE FROM l4u_ud_subscriptions WHERE sub_id LIKE 'TEST\_%'");
    $db->query("DELETE FROM l4u_ud_shops WHERE country IN ('ZZ','ZY')");
}
cleanup($db);

$fresh = date('Y-m-d H:i:s');
$old   = date('Y-m-d H:i:s', time() - 60 * 60 * 72);  // 72h ago, past the 48h threshold

// Shop with three subscriptions, matching the real pattern we sampled.
udmUpsertShop($db, [
    'shop_id' => 'TEST_SHOP_A', 'country' => 'ZZ', 'email' => 'owner@testshop.com',
    'shop_name' => 'Test Shop A', 'shop_type' => 'Thai Restaurants',
    'owner_name' => 'Somchai Test', 'phone' => '+66812345678',
    'board_id' => '1943203205',
], $fresh);

foreach ([
    ['TEST_SUB_1', 'Yelp Ad Spend',             '300.00', 'USD'],
    ['TEST_SUB_2', 'Yelp Enhanced Profile',     '120.00', 'USD'],
    ['TEST_SUB_3', 'Local Starter Bundle',     '3099.60', 'USD'],
] as [$id, $group, $price, $cur]) {
    udmUpsertSubscription($db, [
        'sub_id' => $id, 'shop_id' => 'TEST_SHOP_A',
        'raw_name' => "USD - {$price} SKU {$group}", 'product_group' => $group,
        'price' => $price, 'currency' => $cur,
        'account_name' => 'Test Shop A', 'matched_by' => 'relation',
    ], $fresh);
}

// Shop with no subscriptions at all.
udmUpsertShop($db, [
    'shop_id' => 'TEST_SHOP_NOSUB', 'country' => 'ZZ', 'email' => 'nosub@testshop.com',
    'shop_name' => 'No Subs Shop', 'shop_type' => '', 'owner_name' => 'Nobody',
    'phone' => '', 'board_id' => '1943203205',
], $fresh);

// Same email in a different country, to prove country scoping.
udmUpsertShop($db, [
    'shop_id' => 'TEST_SHOP_OTHERCTY', 'country' => 'ZY', 'email' => 'owner@testshop.com',
    'shop_name' => 'Wrong Country Shop', 'shop_type' => '', 'owner_name' => 'Other',
    'phone' => '', 'board_id' => '1943203246',
], $fresh);

echo "== a match ==\n";
$r = udmLookupShopByEmail($db, 'ZZ', 'owner@testshop.com');
test("found", $r['found'] === true);
test("returns the right shop", $r['shop']['shopId'] === 'TEST_SHOP_A');
test("returns the shop name", $r['shop']['shopName'] === 'Test Shop A');
test("returns the owner name", $r['shop']['ownerName'] === 'Somchai Test');
test("returns the phone", $r['shop']['phone'] === '+66812345678');
test("returns the board id", $r['shop']['boardId'] === '1943203205');
test("does not echo the email back", !isset($r['shop']['email']));
test("returns all three subscriptions", count($r['subscriptions']) === 3);
test("not stale", $r['stale'] === false);
test("not multiple", $r['multiple'] === false);
test("reports syncedAt", $r['syncedAt'] === $fresh);

$names = array_column($r['subscriptions'], 'name');
test("subscription name is the product group", in_array('Yelp Ad Spend', $names, true));
test("subscriptions carry the raw name too",
     isset($r['subscriptions'][0]['rawName']) && $r['subscriptions'][0]['rawName'] !== '');

// The customer-facing list shows product names only. price and currency stay in
// the mirror for internal reporting but must not reach the form.
test("does not return a price", !isset($r['subscriptions'][0]['price']));
test("does not return a currency", !isset($r['subscriptions'][0]['currency']));

echo "== the same product held several times collapses to one row ==\n";
// A real shop was found holding ten separate "Social Media Marketing Bundle"
// rows. Listing each would look like a broken form, so the lookup groups by
// product name.
foreach (['TEST_SUB_DUP_A', 'TEST_SUB_DUP_B', 'TEST_SUB_DUP_C'] as $dupId) {
    udmUpsertSubscription($db, [
        'sub_id' => $dupId, 'shop_id' => 'TEST_SHOP_A',
        'raw_name' => 'USD - 297 SMMB Social Media Marketing Bundle',
        'product_group' => 'Social Media Marketing Bundle',
        'price' => '297.00', 'currency' => 'USD',
        'account_name' => 'Test Shop A', 'matched_by' => 'relation',
    ], $fresh);
}
$r = udmLookupShopByEmail($db, 'ZZ', 'owner@testshop.com');
$names = array_column($r['subscriptions'], 'name');
test("three duplicates appear once", count(array_keys($names, 'Social Media Marketing Bundle')) === 1);
test("the other products are still listed", count($r['subscriptions']) === 4);
test("every returned name is distinct", count($names) === count(array_unique($names)));
$db->query("DELETE FROM l4u_ud_subscriptions WHERE sub_id LIKE 'TEST\_SUB\_DUP%'");

echo "== normalization ==\n";
test("uppercase email matches",
     udmLookupShopByEmail($db, 'ZZ', 'OWNER@TESTSHOP.COM')['found'] === true);
test("padded email matches",
     udmLookupShopByEmail($db, 'ZZ', '  owner@testshop.com  ')['found'] === true);
test("lowercase country code matches",
     udmLookupShopByEmail($db, 'zz', 'owner@testshop.com')['found'] === true);

echo "== country scoping ==\n";
$r = udmLookupShopByEmail($db, 'ZY', 'owner@testshop.com');
test("the same email in another country finds that country's shop",
     $r['found'] === true && $r['shop']['shopId'] === 'TEST_SHOP_OTHERCTY');
test("a country with no such email finds nothing",
     udmLookupShopByEmail($db, 'TH', 'owner@testshop.com')['found'] === false);

echo "== no match ==\n";
test("unknown email", udmLookupShopByEmail($db, 'ZZ', 'nobody@nowhere.com')['found'] === false);
test("empty email", udmLookupShopByEmail($db, 'ZZ', '')['found'] === false);
test("empty country", udmLookupShopByEmail($db, '', 'owner@testshop.com')['found'] === false);

echo "== shop with no subscriptions ==\n";
$r = udmLookupShopByEmail($db, 'ZZ', 'nosub@testshop.com');
test("the shop is still found", $r['found'] === true);
test("subscriptions is an empty array, not a row of nulls", $r['subscriptions'] === []);

echo "== duplicate email in one country ==\n";
udmUpsertShop($db, [
    'shop_id' => 'TEST_SHOP_DUP', 'country' => 'ZZ', 'email' => 'owner@testshop.com',
    'shop_name' => 'Aaa First Alphabetically', 'shop_type' => '',
    'owner_name' => 'Dup', 'phone' => '', 'board_id' => '1943203205',
], $fresh);
$r = udmLookupShopByEmail($db, 'ZZ', 'owner@testshop.com');
test("flags multiple", $r['multiple'] === true);
test("returns the first shop by name", $r['shop']['shopId'] === 'TEST_SHOP_DUP');

// Every match comes back with its own products, so the form can ask which
// branch is theirs. Returning only the first showed "Found <shop>" with an
// empty product list whenever the first by name had none and a sibling did --
// which is 15 of the 69 real duplicate-email groups.
test("returns every matching shop", count($r['shops']) === 2);
$byId = [];
foreach ($r['shops'] as $s) { $byId[$s['shopId']] = $s; }
test("the sibling with no products is listed",
     isset($byId['TEST_SHOP_DUP']) && $byId['TEST_SHOP_DUP']['subscriptions'] === []);
test("the sibling WITH products carries them",
     isset($byId['TEST_SHOP_A']) && count($byId['TEST_SHOP_A']['subscriptions']) === 3);
test("each listed shop still omits the email",
     !isset($byId['TEST_SHOP_A']['email']) && !isset($byId['TEST_SHOP_DUP']['email']));
test("no listed product carries a price",
     array_filter($byId['TEST_SHOP_A']['subscriptions'],
         function ($p) { return isset($p['price']) || isset($p['currency']); }) === []);

$db->query("DELETE FROM l4u_ud_shops WHERE shop_id = ?", 'TEST_SHOP_DUP');

// A single match must keep the one-shop shape, so a caller that does not know
// about `shops` is unaffected.
$one = udmLookupShopByEmail($db, 'ZZ', 'nosub@testshop.com');
test("a single match still lists exactly one shop", count($one['shops']) === 1);
test("a single match is not flagged multiple", $one['multiple'] === false);
test("the top-level shop matches the only listed one",
     $one['shop']['shopId'] === $one['shops'][0]['shopId']);

echo "== staleness ==\n";
udmUpsertShop($db, [
    'shop_id' => 'TEST_SHOP_STALE', 'country' => 'ZZ', 'email' => 'stale@testshop.com',
    'shop_name' => 'Stale Shop', 'shop_type' => '', 'owner_name' => 'Old',
    'phone' => '', 'board_id' => '1943203205',
], $old);
$r = udmLookupShopByEmail($db, 'ZZ', 'stale@testshop.com');
test("still returns the data", $r['found'] === true);
test("flags it stale", $r['stale'] === true);

// The 48-hour threshold is what the whole stale flag turns on, so test both
// sides of it rather than only a value far past it.
$justUnder = date('Y-m-d H:i:s', time() - (UDM_STALE_AFTER_HOURS - 1) * 3600);
udmUpsertShop($db, [
    'shop_id' => 'TEST_SHOP_FRESH47', 'country' => 'ZZ', 'email' => 'fresh47@testshop.com',
    'shop_name' => 'Nearly Stale Shop', 'shop_type' => '', 'owner_name' => 'Recent',
    'phone' => '', 'board_id' => '1943203205',
], $justUnder);
$r = udmLookupShopByEmail($db, 'ZZ', 'fresh47@testshop.com');
test("an hour inside the threshold is NOT stale", $r['stale'] === false);

$justOver = date('Y-m-d H:i:s', time() - (UDM_STALE_AFTER_HOURS + 1) * 3600);
udmUpsertShop($db, [
    'shop_id' => 'TEST_SHOP_STALE49', 'country' => 'ZZ', 'email' => 'stale49@testshop.com',
    'shop_name' => 'Just Stale Shop', 'shop_type' => '', 'owner_name' => 'Older',
    'phone' => '', 'board_id' => '1943203205',
], $justOver);
$r = udmLookupShopByEmail($db, 'ZZ', 'stale49@testshop.com');
test("an hour past the threshold IS stale", $r['stale'] === true);

echo "== injection attempt is bound, not interpreted ==\n";
// Counted rather than hardcoded: the point is that the injection changed
// nothing, which a fixed number stops proving the moment a fixture is added.
$zzBefore = (int) $db->query("SELECT COUNT(*) AS c FROM l4u_ud_shops WHERE country = 'ZZ'")
                     ->fetchArray()['c'];
$r = udmLookupShopByEmail($db, 'ZZ', "' OR 1=1 --");
test("an injection string finds nothing", $r['found'] === false);
test("the shops table is intact",
     (int) $db->query("SELECT COUNT(*) AS c FROM l4u_ud_shops WHERE country = 'ZZ'")
              ->fetchArray()['c'] === $zzBefore);
test("there were fixtures to delete, so the check is not vacuous", $zzBefore > 0);

cleanup($db);
echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
