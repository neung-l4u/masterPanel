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
 * Overall mirror freshness, judged by the OLDEST country.
 *
 * `lastSync` and `ageHours` describe the country that was synced longest ago,
 * not the most recent one, so a mirror where five of six countries are weeks
 * behind cannot report itself fresh. `newestSync` carries the other end of the
 * range for a page that wants to show both.
 *
 * @return array{lastSync: ?string, newestSync: ?string, stale: bool,
 *               ageHours: ?float, totalShops: int, totalSubs: int}
 */
function upDownMirrorStatus($db): array
{
    // Freshness is judged per country and then taken from the OLDEST one.
    //
    // MAX(synced_at) over the whole table is the obvious query and it is wrong:
    // syncing one country stamps only that country's rows, so a single TH run
    // would make MAX() today while CA/AU/NZ/UK/US sat weeks behind, and the
    // page would show "all fresh" over a mirror that is mostly stale. The admin
    // page exists to catch exactly that, and the customer-facing banner judges
    // each shop by its own row, so the summary must not be cheerier than the
    // worst country.
    $row = $db->query(
        "SELECT MIN(last_sync) AS oldest, MAX(last_sync) AS newest, SUM(n) AS total
         FROM (
             SELECT MAX(synced_at) AS last_sync, COUNT(*) AS n
             FROM l4u_up_down_shops
             GROUP BY country
         ) AS per_country"
    )->fetchArray();

    $subs = $db->query(
        "SELECT COUNT(*) AS total FROM l4u_up_down_subscriptions"
    )->fetchArray();

    $oldest = !empty($row['oldest']) ? (string) $row['oldest'] : null;
    $newest = !empty($row['newest']) ? (string) $row['newest'] : null;

    // No rows means the sync has never run here. That is the case most worth
    // flagging, so it reports stale rather than falling through as fresh.
    if ($oldest === null) {
        return [
            'lastSync'    => null,
            'newestSync'  => null,
            'stale'       => true,
            'ageHours'    => null,
            'totalShops'  => 0,
            'totalSubs'   => (int) ($subs['total'] ?? 0),
        ];
    }

    $ageSeconds = time() - strtotime($oldest);

    return [
        // The oldest country's timestamp: what the page headlines, because it
        // is the one that tells staff whether the mirror can be trusted.
        'lastSync'   => $oldest,
        // The newest, so the page can say "TH is current, the rest are not"
        // instead of implying every country is as old as the worst.
        'newestSync' => $newest,
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
