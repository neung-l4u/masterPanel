<?php
/**
 * The mirror status model. Runs against the real database like the other
 * mirror tests, and only reads -- it must never write to the mirror.
 */
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../assets/db/db.php';
require_once __DIR__ . '/../assets/db/initDB.php';
require_once __DIR__ . '/../sync/mondayClient.php';
require_once __DIR__ . '/../models/shopLookup.php';
require_once __DIR__ . '/../models/syncStatus.php';

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

echo "== upDownMirrorStatus ==\n";
$status = upDownMirrorStatus($db);

foreach (['lastSync', 'newestSync', 'stale', 'ageHours', 'totalShops', 'totalSubs'] as $k) {
    test("returns key {$k}", array_key_exists($k, $status));
}
test("stale is a bool", is_bool($status['stale']));
test("totalShops is an int", is_int($status['totalShops']));
test("totalSubs is an int", is_int($status['totalSubs']));

// An empty mirror must read as stale, not as fresh-with-zero-rows: the page
// uses this flag to decide whether to warn, and "never synced" is the most
// important case to warn about.
test("an empty mirror is reported stale",
     $status['lastSync'] !== null || $status['stale'] === true);

// Freshness must agree with the constant the customer-facing banner uses, so
// the two cannot drift apart.
if ($status['lastSync'] !== null) {
    $expectedStale = (strtotime($status['lastSync']) < time() - UP_DOWN_STALE_AFTER_HOURS * 3600);
    test("staleness matches UP_DOWN_STALE_AFTER_HOURS",
         $status['stale'] === $expectedStale);
    test("ageHours is a float when there is data", is_float($status['ageHours']));
} else {
    test("staleness matches UP_DOWN_STALE_AFTER_HOURS (no data; skipped)", true);
    test("ageHours is null when there is no data", $status['ageHours'] === null);
}

echo "== freshness follows the OLDEST country, not the newest ==\n";

// The regression this pins: the first version used MAX(synced_at) over the
// whole table, so syncing one country made the whole mirror look fresh. With
// the real data at the time -- TH synced today, the other five on 2026-10-09 --
// the page would have shown a green "all fresh" banner over 2453 stale shops,
// which is the exact condition it exists to catch.
$counts    = upDownMirrorCountryCounts($db);
$perSynced = array_values(array_filter(array_map(
    static function ($r) { return $r['lastSync']; },
    $counts
)));

if (count($perSynced) > 1) {
    sort($perSynced);
    $oldestSeen = $perSynced[0];
    $newestSeen = $perSynced[count($perSynced) - 1];

    test("lastSync reports the oldest country's timestamp",
         $status['lastSync'] === $oldestSeen);
    test("newestSync reports the most recent country's timestamp",
         $status['newestSync'] === $newestSeen);

    // Only meaningful while the countries actually disagree, which is the
    // situation the bug hid.
    if ($oldestSeen !== $newestSeen) {
        $oldestAge = (time() - strtotime($oldestSeen)) / 3600;
        test("a mirror whose oldest country is past the threshold reads stale",
             $status['stale'] === ($oldestAge > UP_DOWN_STALE_AFTER_HOURS));
        test("ageHours measures the oldest country, so it is never flattering",
             abs($status['ageHours'] - round($oldestAge, 1)) < 0.2);
    } else {
        test("all countries share a timestamp; oldest/newest split not exercised", true);
        test("all countries share a timestamp; age check not exercised", true);
    }
} else {
    test("fewer than two synced countries; oldest/newest split not exercised", true);
    test("fewer than two synced countries; newestSync not exercised", true);
    test("fewer than two synced countries; staleness not exercised", true);
    test("fewer than two synced countries; ageHours not exercised", true);
}

echo "== upDownMirrorCountryCounts ==\n";

test("returns one row per configured country",
     count($counts) === count(UP_DOWN_PROJECT_BOARDS));

$seen = array_column($counts, 'country');
foreach (array_keys(UP_DOWN_PROJECT_BOARDS) as $cc) {
    test("includes {$cc} even with no rows", in_array($cc, $seen, true));
}

foreach ($counts as $row) {
    foreach (['country', 'shops', 'withEmail', 'lastSync'] as $k) {
        test("{$row['country']} row has {$k}", array_key_exists($k, $row));
    }
    test("{$row['country']} withEmail never exceeds shops",
         $row['withEmail'] <= $row['shops']);
}

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
