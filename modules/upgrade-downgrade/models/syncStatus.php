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
 * Overall mirror freshness, judged by the WORST configured country.
 *
 * `lastSync` and `ageHours` describe the country that was synced longest ago,
 * not the most recent one, so a mirror where five of six countries are weeks
 * behind cannot report itself fresh. `newestSync` carries the other end of the
 * range for a page that wants to show both.
 *
 * A configured country with no rows at all counts as worse than any date:
 * `stale` is true and `missingCountries` names them.
 *
 * @return array{lastSync: ?string, newestSync: ?string, stale: bool,
 *               ageHours: ?float, totalShops: int, totalSubs: int,
 *               missingCountries: list<string>}
 */
function upDownMirrorStatus($db): array
{
    // Built from the per-country list rather than its own aggregate query, so
    // that a configured country with NO rows is counted.
    //
    // Two wrong versions preceded this, each a weaker form of the same
    // mistake -- letting a healthy part of the mirror speak for the whole:
    //   1. MAX(synced_at) over the table. Syncing one country stamps only its
    //      rows, so a single TH run read "fresh" while the other five sat
    //      weeks behind.
    //   2. MIN over the countries that HAVE rows. A country that has never
    //      synced forms no group, so a mirror holding only TH -- five
    //      countries missing entirely -- still read fresh.
    // The page exists to catch exactly these, and the customer-facing banner
    // judges each shop by its own row, so this summary must never be cheerier
    // than the worst country.
    $counts = upDownMirrorCountryCounts($db);

    $subs = $db->query(
        "SELECT COUNT(*) AS total FROM l4u_up_down_subscriptions"
    )->fetchArray();

    $oldest     = null;
    $newest     = null;
    $totalShops = 0;
    $missing    = [];

    foreach ($counts as $row) {
        $totalShops += $row['shops'];

        if ($row['lastSync'] === null) {
            $missing[] = $row['country'];
            continue;
        }
        if ($oldest === null || strtotime($row['lastSync']) < strtotime($oldest)) {
            $oldest = $row['lastSync'];
        }
        if ($newest === null || strtotime($row['lastSync']) > strtotime($newest)) {
            $newest = $row['lastSync'];
        }
    }

    // Nothing anywhere: the sync has never run here, which is the case most
    // worth flagging, so it reports stale rather than falling through as fresh.
    if ($oldest === null) {
        return [
            'lastSync'         => null,
            'newestSync'       => null,
            'stale'            => true,
            'ageHours'         => null,
            'totalShops'       => 0,
            'totalSubs'        => (int) ($subs['total'] ?? 0),
            'missingCountries' => $missing,
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
        // A missing country has no date to age, and waiting 48 hours to warn
        // about data that does not exist would be the wrong way round.
        'stale'      => $missing !== []
                        || $ageSeconds > UP_DOWN_STALE_AFTER_HOURS * 3600,
        'ageHours'   => (float) round($ageSeconds / 3600, 1),
        'totalShops' => $totalShops,
        'totalSubs'  => (int) ($subs['total'] ?? 0),
        'missingCountries' => $missing,
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
