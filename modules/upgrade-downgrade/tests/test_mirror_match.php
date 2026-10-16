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

echo "== upDownNormalizeEmail ==\n";
test("lowercases", upDownNormalizeEmail('Owner@Shop.COM') === 'owner@shop.com');
test("trims", upDownNormalizeEmail('  a@b.com  ') === 'a@b.com');
test("trims non-breaking space", upDownNormalizeEmail("a@b.com\xC2\xA0") === 'a@b.com');
test("trims a leading non-breaking space", upDownNormalizeEmail("\xC2\xA0a@b.com") === 'a@b.com');
test("null becomes empty string", upDownNormalizeEmail(null) === '');
test("empty stays empty", upDownNormalizeEmail('') === '');

echo "== upDownNormalizeShopName ==\n";
test("lowercases", upDownNormalizeShopName('Cher Thai Cuisine') === 'cher thai cuisine');
test("collapses inner whitespace",
     upDownNormalizeShopName("Ivory  Thai \t @ Zetland") === 'ivory thai @ zetland');
test("trims", upDownNormalizeShopName('  Demo Eats - USA  ') === 'demo eats - usa');
test("null becomes empty string", upDownNormalizeShopName(null) === '');
test("keeps non-ASCII intact", upDownNormalizeShopName('ร้านอาหารไทย') === 'ร้านอาหารไทย');

// A byte-set trim() would eat the trailing 0xA0 of ภ (U+0E20 = E0 B8 A0),
// leaving invalid UTF-8 that normalizes to '' and silently kills the fallback.
test("keeps a name ending in a 0xA0-byte character", upDownNormalizeShopName('ลาภ') === 'ลาภ');
test("keeps that character mid-name", upDownNormalizeShopName('ร้านภ') === 'ร้านภ');
test("keeps a leading U+0080-00BF character", upDownNormalizeShopName('© Shop') === '© shop');
test("strips a surrounding nbsp", upDownNormalizeShopName("\xC2\xA0Cher Thai\xC2\xA0") === 'cher thai');
test("collapses an inner nbsp", upDownNormalizeShopName("Cher\xC2\xA0Thai") === 'cher thai');

echo "== upDownResolveShopId: relation wins ==\n";
$shopsByName = [
    'cher thai cuisine' => '1879900670',
    'ivory thai @ zetland' => '1879899645',
];

$r = upDownResolveShopId(['linked_shop_id' => '1879900670', 'account_name' => 'Anything'], $shopsByName);
test("relation present -> that id", $r['shop_id'] === '1879900670');
test("relation present -> matched_by relation", $r['matched_by'] === 'relation');

$r = upDownResolveShopId(['linked_shop_id' => '1879900670', 'account_name' => 'Ivory Thai @ Zetland'], $shopsByName);
test("relation beats a conflicting name", $r['shop_id'] === '1879900670');
test("relation beats a conflicting name -> relation", $r['matched_by'] === 'relation');

echo "== upDownResolveShopId: name fallback ==\n";
$r = upDownResolveShopId(['linked_shop_id' => '', 'account_name' => 'Cher Thai Cuisine'], $shopsByName);
test("exact name -> shop id", $r['shop_id'] === '1879900670');
test("exact name -> matched_by name", $r['matched_by'] === 'name');

$r = upDownResolveShopId(['linked_shop_id' => '', 'account_name' => '  CHER   thai cuisine '], $shopsByName);
test("name differing by case and padding still matches", $r['shop_id'] === '1879900670');
test("normalized name match -> matched_by name", $r['matched_by'] === 'name');

echo "== upDownResolveShopId: no match ==\n";
$r = upDownResolveShopId(['linked_shop_id' => '', 'account_name' => 'Nowhere Noodles'], $shopsByName);
test("unknown name -> empty shop id", $r['shop_id'] === '');
test("unknown name -> matched_by none", $r['matched_by'] === 'none');

$r = upDownResolveShopId(['linked_shop_id' => '', 'account_name' => ''], $shopsByName);
test("no relation and no name -> none", $r['matched_by'] === 'none');

$r = upDownResolveShopId([], $shopsByName);
test("missing keys do not throw", $r['matched_by'] === 'none');

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
