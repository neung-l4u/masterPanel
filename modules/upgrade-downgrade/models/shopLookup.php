<?php
/**
 * The customer-mode lookup: one shop and its products, by country and email.
 *
 * Read-only, and the only place that knows the mirror's schema. Everything
 * else -- the endpoint, the form -- deals in the array this returns.
 */

/** Past this age the mirror is reported stale but still served. */
const UDM_STALE_AFTER_HOURS = 48;

/**
 * Find a mirrored shop by country and email.
 *
 * The email is normalized the same way the sync normalizes it before storing,
 * so idx_country_email is used without a LOWER() that would defeat it.
 *
 * @return array{found: bool, shop?: array, subscriptions?: array,
 *               syncedAt?: string, stale?: bool, multiple?: bool}
 */
function udmLookupShopByEmail($db, string $country, string $email): array
{
    $country = strtoupper(trim($country));
    $email   = udmNormalizeEmail($email);

    if ($country === '' || $email === '') {
        return ['found' => false];
    }

    // Ordered by shop_name so a duplicated email returns the same shop every
    // time rather than whichever row the engine happens to hand back first.
    // shop_id breaks the remaining tie: two shops sharing an email AND a name
    // would otherwise still come back in index order, which is not a promise.
    $shops = $db->query(
        "SELECT shop_id, shop_name, shop_type, owner_name, phone, board_id, synced_at
         FROM l4u_ud_shops
         WHERE country = ? AND email = ?
         ORDER BY shop_name ASC, shop_id ASC",
        $country, $email
    )->fetchAll();

    if (empty($shops)) {
        return ['found' => false];
    }

    $shop = $shops[0];

    // One row per distinct product name, not per Monday subscription row. A shop
    // can carry the same product many times over -- one sampled shop holds ten
    // separate "Social Media Marketing Bundle" rows -- and listing each would
    // look like a broken form. MIN(sub_id) just gives the group a stable id.
    //
    // price and currency are deliberately NOT selected: the customer-facing form
    // shows product names only. The columns stay populated in the mirror for
    // internal reporting.
    $subs = $db->query(
        "SELECT MIN(sub_id) AS sub_id, product_group, MIN(raw_name) AS raw_name
         FROM l4u_ud_subscriptions
         WHERE shop_id = ?
         GROUP BY product_group
         ORDER BY product_group ASC",
        (string) $shop['shop_id']
    )->fetchAll();

    $subscriptions = [];
    foreach ($subs as $s) {
        $subscriptions[] = [
            'id'      => (string) $s['sub_id'],
            // product_group is the customer-facing name; rawName carries the SKU.
            'name'    => (string) $s['product_group'],
            'rawName' => (string) $s['raw_name'],
        ];
    }

    $syncedAt = (string) $shop['synced_at'];
    $stale    = (strtotime($syncedAt) < time() - UDM_STALE_AFTER_HOURS * 3600);

    return [
        'found' => true,
        'shop'  => [
            'shopId'    => (string) $shop['shop_id'],
            'shopName'  => (string) $shop['shop_name'],
            'shopType'  => (string) $shop['shop_type'],
            'ownerName' => (string) $shop['owner_name'],
            'phone'     => (string) $shop['phone'],
            'boardId'   => (string) $shop['board_id'],
            // The email is deliberately not echoed back: the caller already
            // has it, and not returning it keeps this response from being a
            // confirmation oracle for an address someone merely guessed.
        ],
        'subscriptions' => $subscriptions,
        'syncedAt'      => $syncedAt,
        'stale'         => $stale,
        'multiple'      => count($shops) > 1,
    ];
}
