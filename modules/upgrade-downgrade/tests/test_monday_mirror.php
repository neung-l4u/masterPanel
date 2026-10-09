<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../assets/db/db.php';
require_once __DIR__ . '/../assets/db/initDB.php';
require_once __DIR__ . '/../sync/mirrorMatch.php';
require_once __DIR__ . '/../sync/mirrorRepo.php';
require_once __DIR__ . '/../sync/mondayClient.php';
require_once __DIR__ . '/../sync/mondayMirror.php';

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

echo "== udmExtractProjectRows ==\n";

// Shape copied from a real live.php response: a project item carries many
// board_relation columns and only connect_boards0 points at Contacts.
$projectResponse = [
    'data' => ['boards' => [[
        'id' => '1943203205',
        'items_page' => [
            'cursor' => null,
            'items'  => [
                [
                    'id' => '1879900670',
                    'name' => 'Cher Thai Cuisine',
                    'column_values' => [
                        ['id' => 'color1', 'text' => 'Thai Restaurants & Takeaways', 'value' => ''],
                        ['id' => 'connect_boards0', 'text' => 'Somchai', 'value' => '', 'linked_item_ids' => ['555000111']],
                        ['id' => 'board_relation_other', 'text' => '', 'value' => '', 'linked_item_ids' => ['999']],
                    ],
                ],
                [
                    'id' => '1879899645',
                    'name' => 'Ivory Thai @ Zetland',
                    'column_values' => [
                        ['id' => 'color1', 'text' => '', 'value' => ''],
                        ['id' => 'connect_boards0', 'text' => '', 'value' => '', 'linked_item_ids' => []],
                    ],
                ],
            ],
        ],
    ]]],
];

$rows = udmExtractProjectRows($projectResponse, 'TH', 1943203205);
test("returns one row per item", count($rows) === 2);
test("shop_id is the item id", $rows[0]['shop_id'] === '1879900670');
test("shop_name is the item name", $rows[0]['shop_name'] === 'Cher Thai Cuisine');
test("shop_type comes from color1", $rows[0]['shop_type'] === 'Thai Restaurants & Takeaways');
test("country is the requested country", $rows[0]['country'] === 'TH');
test("board_id is the requested board", $rows[0]['board_id'] === '1943203205');
// Also proves the other board_relation_* columns are ignored: the fixture's
// second relation column carries '999', so a wrong column id would show here.
test("contact_id comes from connect_boards0", $rows[0]['contact_id'] === '555000111');
test("an unlinked item has an empty contact_id", $rows[1]['contact_id'] === '');
test("an empty color1 gives an empty shop_type", $rows[1]['shop_type'] === '');

$rows = udmExtractProjectRows(['data' => ['boards' => []]], 'TH', 1943203205);
test("an empty board gives no rows", $rows === []);
$rows = udmExtractProjectRows(['error' => 'boom'], 'TH', 1943203205);
test("an error response gives no rows", $rows === []);

echo "== udmExtractContactFields ==\n";
$contact = [
    'id' => '555000111',
    'name' => 'Fallback Name',
    'column_values' => [
        ['id' => 'text0', 'text' => 'Somchai'],
        ['id' => 'text7', 'text' => 'Jaidee'],
        ['id' => 'contact_phone', 'text' => '+66812345678'],
        ['id' => 'dup__of_mobile', 'text' => '+66899999999'],
        ['id' => 'contact_email', 'text' => 'Somchai@Shop.COM'],
    ],
];
$f = udmExtractContactFields($contact);
test("joins first and last name", $f['owner_name'] === 'Somchai Jaidee');
test("prefers contact_phone", $f['phone'] === '+66812345678');
test("normalizes the email", $f['email'] === 'somchai@shop.com');

$noPhone = $contact;
$noPhone['column_values'][2]['text'] = '';
$f = udmExtractContactFields($noPhone);
test("falls back to dup__of_mobile", $f['phone'] === '+66899999999');

// Unlike live.php, email is NEVER used as a phone substitute here: the email
// is the lookup key and must stay in its own field.
$noPhones = $contact;
$noPhones['column_values'][2]['text'] = '';
$noPhones['column_values'][3]['text'] = '';
$f = udmExtractContactFields($noPhones);
test("with no phone at all, phone is empty, not the email", $f['phone'] === '');
test("the email is still populated", $f['email'] === 'somchai@shop.com');

$nameless = ['id' => '1', 'name' => 'Fallback Name', 'column_values' => [
    ['id' => 'text0', 'text' => ''], ['id' => 'text7', 'text' => ''],
]];
$f = udmExtractContactFields($nameless);
test("falls back to the item name when both name parts are empty",
     $f['owner_name'] === 'Fallback Name');

echo "== udmExtractSubscriptionRows ==\n";
$subResponse = [
    'data' => ['boards' => [[
        'items_page' => [
            'cursor' => null,
            'items' => [
                [
                    'id' => '1886326536',
                    'name' => 'PO01M00 Pro Online Ordering System',
                    'column_values' => [
                        ['id' => 'status',  'text' => 'Active'],
                        ['id' => 'numbers', 'text' => '49'],
                        ['id' => 'status0', 'text' => 'aud'],
                        ['id' => 'text9',   'text' => 'Cher Thai Cuisine'],
                        ['id' => 'connect_boards', 'text' => '', 'linked_item_ids' => ['1879900670']],
                        ['id' => 'lookup_mkwgjt31', 'display_value' => 'Pro Online Ordering System'],
                    ],
                ],
                [
                    'id' => '1886326999',
                    'name' => 'Cancelled thing',
                    'column_values' => [
                        ['id' => 'status',  'text' => 'Cancelled'],
                        ['id' => 'numbers', 'text' => '10'],
                        ['id' => 'status0', 'text' => 'AUD'],
                        ['id' => 'text9',   'text' => 'Cher Thai Cuisine'],
                        ['id' => 'connect_boards', 'text' => '', 'linked_item_ids' => ['1879900670']],
                        ['id' => 'lookup_mkwgjt31', 'display_value' => 'Old Product'],
                    ],
                ],
                [
                    'id' => '1886327000',
                    'name' => 'Unlinked active thing',
                    'column_values' => [
                        ['id' => 'status',  'text' => 'Active'],
                        ['id' => 'numbers', 'text' => ''],
                        ['id' => 'status0', 'text' => ''],
                        ['id' => 'text9',   'text' => 'Ivory Thai @ Zetland'],
                        ['id' => 'connect_boards', 'text' => '', 'linked_item_ids' => []],
                        ['id' => 'lookup_mkwgjt31', 'display_value' => ''],
                    ],
                ],
            ],
        ],
    ]]],
];

$subs = udmExtractSubscriptionRows($subResponse);
test("drops non-active rows", count($subs) === 2);
test("sub_id is the item id", $subs[0]['sub_id'] === '1886326536');
test("raw_name is the item name", $subs[0]['raw_name'] === 'PO01M00 Pro Online Ordering System');
test("product_group comes from the mirror column",
     $subs[0]['product_group'] === 'Pro Online Ordering System');
test("price comes from numbers", $subs[0]['price'] === '49');
test("currency is uppercased", $subs[0]['currency'] === 'AUD');
test("account_name comes from text9", $subs[0]['account_name'] === 'Cher Thai Cuisine');
test("linked_shop_id comes from connect_boards", $subs[0]['linked_shop_id'] === '1879900670');
test("an empty numbers field becomes a null price", $subs[1]['price'] === null);
test("an unlinked row has an empty linked_shop_id", $subs[1]['linked_shop_id'] === '');
test("product_group falls back to the item name when the mirror is empty",
     $subs[1]['product_group'] === 'Unlinked active thing');

echo "== the fallback name is stripped of price and SKU ==\n";
// The customer-facing list is specified to show names only. The mirror column is
// empty on a sizeable minority of real rows, and the item name it falls back to
// carries the currency, price and SKU -- so dropping the price COLUMN was not
// enough on its own. These are real shapes taken from the live mirror.
foreach ([
    ['USD - $199 SS01M12 Social Media Marketing Solo - 12 Months', 'Social Media Marketing Solo - 12 Months'],
    ['USA PB - $97 Pro Online Ordering System',                    'Pro Online Ordering System'],
    ['PO01M00 Pro Online Ordering System',                         'Pro Online Ordering System'],
    ['AUD - $150 ADAU03A02 Social Media Ad Spend Credit + 15% Handling Fee 172.5',
                                                                   'Social Media Ad Spend Credit + 15% Handling Fee'],
    ['POS + Online Ordering System $0',                            'POS + Online Ordering System'],
    ['AUD - $199 PO01M00 Pro Online Ordering System, AUD - $49 PAO03M00 Promotions Add-on',
                                                                   'Pro Online Ordering System, Promotions Add-on'],
] as [$raw, $want]) {
    $got = udmCleanProductName($raw);
    test("cleans " . json_encode($raw), $got === $want);
    test("  -> no price survives", strpos($got, '$') === false);
}
// An unrecognised shape must show something imperfect rather than a blank line.
test("a plain name is left alone", udmCleanProductName('Booking System') === 'Booking System');
test("a name that would clean away is kept", udmCleanProductName('USD - $49') === 'USD - $49');

// The existing getSubscriptions.php uses a case-insensitive "contains active"
// test, so these must all count as active.
foreach (['Active', 'active', 'ACTIVE', 'Active - paid', 'Pending Active'] as $status) {
    $r = $subResponse;
    $r['data']['boards'][0]['items_page']['items'] = [[
        'id' => 'X', 'name' => 'n', 'column_values' => [['id' => 'status', 'text' => $status]],
    ]];
    test("status '{$status}' counts as active", count(udmExtractSubscriptionRows($r)) === 1);
}
foreach (['Cancelled', 'Paused', ''] as $status) {
    $r = $subResponse;
    $r['data']['boards'][0]['items_page']['items'] = [[
        'id' => 'X', 'name' => 'n', 'column_values' => [['id' => 'status', 'text' => $status]],
    ]];
    test("status '{$status}' does not count as active", count(udmExtractSubscriptionRows($r)) === 0);
}

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
