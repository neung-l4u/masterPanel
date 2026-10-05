<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../sync/mirrorMatch.php';

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

echo "== udmNormalizeEmail ==\n";
test("lowercases", udmNormalizeEmail('Owner@Shop.COM') === 'owner@shop.com');
test("trims", udmNormalizeEmail('  a@b.com  ') === 'a@b.com');
test("trims non-breaking space", udmNormalizeEmail("a@b.com\xC2\xA0") === 'a@b.com');
test("null becomes empty string", udmNormalizeEmail(null) === '');
test("empty stays empty", udmNormalizeEmail('') === '');

echo "== udmNormalizeShopName ==\n";
test("lowercases", udmNormalizeShopName('Cher Thai Cuisine') === 'cher thai cuisine');
test("collapses inner whitespace",
     udmNormalizeShopName("Ivory  Thai \t @ Zetland") === 'ivory thai @ zetland');
test("trims", udmNormalizeShopName('  Demo Eats - USA  ') === 'demo eats - usa');
test("null becomes empty string", udmNormalizeShopName(null) === '');
test("keeps non-ASCII intact", udmNormalizeShopName('ร้านอาหารไทย') === 'ร้านอาหารไทย');

echo "== udmResolveShopId: relation wins ==\n";
$shopsByName = [
    'cher thai cuisine' => '1879900670',
    'ivory thai @ zetland' => '1879899645',
];

$r = udmResolveShopId(['linked_shop_id' => '1879900670', 'account_name' => 'Anything'], $shopsByName);
test("relation present -> that id", $r['shop_id'] === '1879900670');
test("relation present -> matched_by relation", $r['matched_by'] === 'relation');

$r = udmResolveShopId(['linked_shop_id' => '1879900670', 'account_name' => 'Ivory Thai @ Zetland'], $shopsByName);
test("relation beats a conflicting name", $r['shop_id'] === '1879900670');
test("relation beats a conflicting name -> relation", $r['matched_by'] === 'relation');

echo "== udmResolveShopId: name fallback ==\n";
$r = udmResolveShopId(['linked_shop_id' => '', 'account_name' => 'Cher Thai Cuisine'], $shopsByName);
test("exact name -> shop id", $r['shop_id'] === '1879900670');
test("exact name -> matched_by name", $r['matched_by'] === 'name');

$r = udmResolveShopId(['linked_shop_id' => '', 'account_name' => '  CHER   thai cuisine '], $shopsByName);
test("name differing by case and padding still matches", $r['shop_id'] === '1879900670');
test("normalized name match -> matched_by name", $r['matched_by'] === 'name');

echo "== udmResolveShopId: no match ==\n";
$r = udmResolveShopId(['linked_shop_id' => '', 'account_name' => 'Nowhere Noodles'], $shopsByName);
test("unknown name -> empty shop id", $r['shop_id'] === '');
test("unknown name -> matched_by none", $r['matched_by'] === 'none');

$r = udmResolveShopId(['linked_shop_id' => '', 'account_name' => ''], $shopsByName);
test("no relation and no name -> none", $r['matched_by'] === 'none');

$r = udmResolveShopId([], $shopsByName);
test("missing keys do not throw", $r['matched_by'] === 'none');

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
