<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../models/cache.php';

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

$name = 'test_cache_' . getmypid();

echo "== cache round trip ==\n";
test("a cold read misses", arayaCacheGet($name, 300) === null);

arayaCachePut($name, ['shops' => [['shop_id' => 'g1']]]);
$hit = arayaCacheGet($name, 300);
test("a warm read hits", $hit !== null);
test("the payload round trips", $hit['shops'][0]['shop_id'] === 'g1');

echo "== expiry ==\n";
test("a zero ttl always misses", arayaCacheGet($name, 0) === null);
test("a negative ttl misses", arayaCacheGet($name, -1) === null);

echo "== corrupt cache is a miss, not a crash ==\n";
file_put_contents(arayaCachePath($name), 'not json at all');
test("unparseable cache reads as a miss", arayaCacheGet($name, 300) === null);

file_put_contents(arayaCachePath($name), '');
test("an empty cache file reads as a miss", arayaCacheGet($name, 300) === null);

echo "== name sanitising ==\n";
$evil = '../../../etc/passwd';
arayaCachePut($evil, ['ok' => true]);
test("a traversing name cannot escape the cache directory",
     str_starts_with(realpath(dirname(arayaCachePath($evil))), realpath(__DIR__ . '/../assets/cache')));

@unlink(arayaCachePath($name));
@unlink(arayaCachePath($evil));

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
