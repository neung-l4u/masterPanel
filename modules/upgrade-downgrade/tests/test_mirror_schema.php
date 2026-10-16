<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../assets/db/db.php';
require_once __DIR__ . '/../assets/db/initDB.php';

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

function columnsOf($db, $table) {
    $rows = $db->query("SHOW COLUMNS FROM `{$table}`")->fetchAll();
    $out = [];
    foreach ($rows as $r) { $out[$r['Field']] = $r['Type']; }
    return $out;
}

echo "== l4u_up_down_shops ==\n";
$shops = columnsOf($db, 'l4u_up_down_shops');
foreach (['shop_id','country','email','shop_name','shop_type',
          'owner_name','phone','board_id','synced_at'] as $col) {
    test("has column {$col}", isset($shops[$col]));
}

echo "== l4u_up_down_subscriptions ==\n";
$subs = columnsOf($db, 'l4u_up_down_subscriptions');
foreach (['sub_id','shop_id','raw_name','product_group','price',
          'currency','account_name','matched_by','synced_at'] as $col) {
    test("has column {$col}", isset($subs[$col]));
}
test("matched_by is the expected enum",
     isset($subs['matched_by']) &&
     strpos($subs['matched_by'], "'relation'") !== false &&
     strpos($subs['matched_by'], "'name'") !== false &&
     strpos($subs['matched_by'], "'none'") !== false);

echo "== indexes ==\n";
$idx = $db->query("SHOW INDEX FROM `l4u_up_down_shops`")->fetchAll();
$names = array_column($idx, 'Key_name');
test("l4u_up_down_shops has idx_country_email", in_array('idx_country_email', $names, true));

$idx2 = $db->query("SHOW INDEX FROM `l4u_up_down_subscriptions`")->fetchAll();
$names2 = array_column($idx2, 'Key_name');
test("l4u_up_down_subscriptions has idx_shop", in_array('idx_shop', $names2, true));

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
