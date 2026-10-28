<?php
/**
 * Mirror freshness, for the admin sync page.
 *
 * Read-only, and the only place outside shopLookup.php that knows the mirror
 * schema. The page deals in the arrays these return.
 *
 * Staleness reuses UP_DOWN_STALE_AFTER_HOURS from shopLookup.php rather than
 * repeating 48: the admin warning and the customer-facing "may be out of date"
 * banner must flip at the same moment, or staff see green while customers are
 * being warned.
 */

/**
 * Overall mirror freshness.
 *
 * @return array{lastSync: ?string, stale: bool, ageHours: ?float,
 *               totalShops: int, totalSubs: int}
 */
function upDownMirrorStatus($db): array
{
    $row = $db->query(
        "SELECT MAX(synced_at) AS last_sync, COUNT(*) AS total FROM l4u_up_down_shops"
    )->fetchArray();

    $subs = $db->query(
        "SELECT COUNT(*) AS total FROM l4u_up_down_subscriptions"
    )->fetchArray();

    $lastSync = !empty($row['last_sync']) ? (string) $row['last_sync'] : null;

    // No rows means the sync has never run here. That is the case most worth
    // flagging, so it reports stale rather than falling through as fresh.
    if ($lastSync === null) {
        return [
            'lastSync'   => null,
            'stale'      => true,
            'ageHours'   => null,
            'totalShops' => 0,
            'totalSubs'  => (int) ($subs['total'] ?? 0),
        ];
    }

    $ageSeconds = time() - strtotime($lastSync);

    return [
        'lastSync'   => $lastSync,
        'stale'      => $ageSeconds > UP_DOWN_STALE_AFTER_HOURS * 3600,
        'ageHours'   => (float) round($ageSeconds / 3600, 1),
        'totalShops' => (int) ($row['total'] ?? 0),
        'totalSubs'  => (int) ($subs['total'] ?? 0),
    ];
}

/**
 * Per-country mirror contents, in the configured board order.
 *
 * Every configured country is returned whether or not it has rows: a country
 * that has never synced is exactly the one staff need to see listed, and
 * omitting it would silently drop it from the page's checkboxes.
 *
 * @return list<array{country: string, shops: int, withEmail: int, lastSync: ?string}>
 */
function upDownMirrorCountryCounts($db): array
{
    $rows = $db->query(
        "SELECT country,
                COUNT(*)                                     AS shops,
                SUM(CASE WHEN email <> '' THEN 1 ELSE 0 END) AS with_email,
                MAX(synced_at)                               AS last_sync
         FROM l4u_up_down_shops
         GROUP BY country"
    )->fetchAll();

    $byCountry = [];
    foreach ($rows as $r) {
        $byCountry[(string) $r['country']] = $r;
    }

    $out = [];
    foreach (array_keys(UP_DOWN_PROJECT_BOARDS) as $cc) {
        $r = $byCountry[$cc] ?? null;
        $out[] = [
            'country'   => $cc,
            'shops'     => $r ? (int) $r['shops'] : 0,
            'withEmail' => $r ? (int) $r['with_email'] : 0,
            'lastSync'  => ($r && !empty($r['last_sync'])) ? (string) $r['last_sync'] : null,
        ];
    }

    return $out;
}
