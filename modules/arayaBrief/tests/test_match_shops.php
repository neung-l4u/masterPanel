<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../models/matchShops.php';

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

echo "== arayaNormalizeKey ==\n";
test("lowercases", arayaNormalizeKey('AbC@X.CoM') === 'abc@x.com');
test("trims", arayaNormalizeKey('  a@b.com  ') === 'a@b.com');
test("trims nbsp", arayaNormalizeKey("a@b.com\xC2\xA0") === 'a@b.com');
test("null is empty", arayaNormalizeKey(null) === '');

echo "== arayaNormalizeShopName ==\n";
test("lowercases and trims", arayaNormalizeShopName('  Silk Thai Therapy ') === 'silk thai therapy');
test("collapses inner whitespace", arayaNormalizeShopName("Silk  Thai\tTherapy") === 'silk thai therapy');

// The booking system, not the shop: these must match their gcal counterparts.
test("strips a trailing (Amelia)",
     arayaNormalizeShopName('Roanoke Thai Massage & Spa (Amelia)') === arayaNormalizeShopName('Roanoke Thai Massage & Spa'));
test("strips a padded trailing group",
     arayaNormalizeShopName('KLR Massage Therapy ( Croydon )') === 'klr massage therapy');
test("strips only the trailing group",
     arayaNormalizeShopName('A (X) B (Y)') === arayaNormalizeShopName('A (X) B'));

// Thai names: trim() is byte-based and would corrupt these.
test("keeps a name ending in a 0xA0-byte character", arayaNormalizeShopName('ลาภ') === 'ลาภ');
test("keeps that character mid-name", arayaNormalizeShopName('ร้านภ') === 'ร้านภ');
test("keeps non-ASCII intact", arayaNormalizeShopName('ร้านอาหารไทย') === 'ร้านอาหารไทย');
test("a name that is only a parenthesised group does not become empty",
     arayaNormalizeShopName('(Amelia)') !== '');

echo "== arayaMatchShops: calendar id wins ==\n";
$monday = [['name' => 'Shop A', 'calendar_id' => 'CAL-1', 'email' => 'a@x.com']];
$gcal   = [
    ['shop_id' => 'g1', 'shop_name' => 'Totally Different', 'calendar_id' => 'cal-1', 'shop_email' => 'other@x.com'],
    ['shop_id' => 'g2', 'shop_name' => 'Shop A', 'calendar_id' => '', 'shop_email' => 'a@x.com'],
];
$r = arayaMatchShops($monday, $gcal);
test("calendar id matches case-insensitively", count($r['matched']) === 1);
test("calendar id beats name and email", $r['matched'][0]['gcal']['shop_id'] === 'g1');
test("matched_by is calendar", $r['matched'][0]['matched_by'] === 'calendar');
test("the unused gcal shop is reported", count($r['unmatchedGcal']) === 1);

echo "== arayaMatchShops: email fallback ==\n";
$monday = [['name' => 'Shop B', 'calendar_id' => '', 'email' => 'B@X.com']];
$gcal   = [['shop_id' => 'g3', 'shop_name' => 'Nope', 'calendar_id' => '', 'shop_email' => 'b@x.com']];
$r = arayaMatchShops($monday, $gcal);
test("email matches when no calendar id", $r['matched'][0]['gcal']['shop_id'] === 'g3');
test("matched_by is email", $r['matched'][0]['matched_by'] === 'email');

echo "== arayaMatchShops: name fallback ==\n";
$monday = [['name' => 'Tableland Traditional Thai massage (Amelia)', 'calendar_id' => '', 'email' => '']];
$gcal   = [['shop_id' => 'g4', 'shop_name' => 'Tableland Traditional Thai Massage', 'calendar_id' => '', 'shop_email' => '']];
$r = arayaMatchShops($monday, $gcal);
test("name matches after stripping (Amelia)", count($r['matched']) === 1);
test("matched_by is name", $r['matched'][0]['matched_by'] === 'name');

echo "== arayaMatchShops: ambiguous names are NOT matched ==\n";
// Two real, distinct KLR branches that normalize to the same string. Guessing
// would report one branch's minutes against the other.
$monday = [
    ['name' => 'KLR Massage Therapy ( Croydon )', 'calendar_id' => '', 'email' => ''],
    ['name' => 'KLR Massage Therapy (Purley)',    'calendar_id' => '', 'email' => ''],
];
$gcal = [['shop_id' => 'g5', 'shop_name' => 'KLR Massage Therapy', 'calendar_id' => '', 'shop_email' => '']];
$r = arayaMatchShops($monday, $gcal);
test("two Monday shops normalizing alike match nothing", count($r['matched']) === 0);
test("both are reported unmatched", count($r['unmatchedMonday']) === 2);
test("the gcal shop is reported unmatched too", count($r['unmatchedGcal']) === 1);

// Ambiguity on the gcal side is just as unsafe.
$monday = [['name' => 'Sabai Thai Massage', 'calendar_id' => '', 'email' => '']];
$gcal = [
    ['shop_id' => 'g6', 'shop_name' => 'Sabai Thai Massage', 'calendar_id' => '', 'shop_email' => ''],
    ['shop_id' => 'g7', 'shop_name' => 'Sabai  Thai  Massage', 'calendar_id' => '', 'shop_email' => ''],
];
$r = arayaMatchShops($monday, $gcal);
test("two gcal shops normalizing alike match nothing", count($r['matched']) === 0);
test("the Monday shop is reported unmatched", count($r['unmatchedMonday']) === 1);

echo "== arayaMatchShops: a gcal shop is used at most once ==\n";
$monday = [
    ['name' => 'First',  'calendar_id' => 'CAL-9', 'email' => ''],
    ['name' => 'Second', 'calendar_id' => 'CAL-9', 'email' => ''],
];
$gcal = [['shop_id' => 'g8', 'shop_name' => 'X', 'calendar_id' => 'cal-9', 'shop_email' => '']];
$r = arayaMatchShops($monday, $gcal);
test("only one Monday shop claims it", count($r['matched']) === 1);
test("the loser is reported unmatched", count($r['unmatchedMonday']) === 1);

echo "== arayaMatchShops: blanks never match blanks ==\n";
$monday = [['name' => 'Has No Keys', 'calendar_id' => '', 'email' => '']];
$gcal   = [['shop_id' => 'g9', 'shop_name' => 'Also No Keys', 'calendar_id' => '', 'shop_email' => '']];
$r = arayaMatchShops($monday, $gcal);
test("empty calendar ids do not match each other", count($r['matched']) === 0);
test("empty emails do not match each other", count($r['unmatchedMonday']) === 1);

echo "== arayaMatchShops: empty inputs ==\n";
$r = arayaMatchShops([], []);
test("no shops is not an error", $r['matched'] === [] && $r['unmatchedMonday'] === [] && $r['unmatchedGcal'] === []);
$r = arayaMatchShops([], [['shop_id' => 'gx', 'shop_name' => 'Orphan', 'calendar_id' => '', 'shop_email' => '']]);
test("a gcal shop with no Monday item is reported", count($r['unmatchedGcal']) === 1);

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
