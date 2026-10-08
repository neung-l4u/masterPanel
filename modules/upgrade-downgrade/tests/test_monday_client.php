<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../sync/mondayClient.php';

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

echo "== board constants ==\n";
test("six project boards", count(UDM_PROJECT_BOARDS) === 6);
test("TH board matches live.php", UDM_PROJECT_BOARDS['TH'] === 1943203205);
test("AU board matches live.php", UDM_PROJECT_BOARDS['AU'] === 1943203246);
test("NZ board matches live.php", UDM_PROJECT_BOARDS['NZ'] === 1943203264);
test("CA board matches live.php", UDM_PROJECT_BOARDS['CA'] === 1943203287);
test("UK board matches live.php", UDM_PROJECT_BOARDS['UK'] === 1943203305);
test("US board matches live.php", UDM_PROJECT_BOARDS['US'] === 1940392927);
test("subscriptions board id", UDM_SUBSCRIPTIONS_BOARD === 1881439330);

echo "== udmLoadConfig ==\n";
$config = udmLoadConfig();
test("returns an array", is_array($config));
test("monday_token is present and non-empty",
     isset($config['monday_token']) && $config['monday_token'] !== '');
test("the token is not the committed placeholder", $config['monday_token'] !== 'YOUR_TOKEN_HERE');

echo "== udmMondayRequest: no token means an error, not a crash ==\n";
$res = udmMondayRequest('clearly-not-a-valid-token', '{ me { id } }', 15);
test("returns an array", is_array($res));
test("reports a problem rather than looking successful",
     isset($res['error']) || isset($res['errors']) || isset($res['error_message']));

echo "== udmMondayRequest: a real query works ==\n";
$res = udmMondayRequest($config['monday_token'], '{ me { id name } }', 30);
test("no transport error", !isset($res['error']));
test("returns data.me.id", !empty($res['data']['me']['id']));

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
