<?php
/**
 * The customer-mode lookup: one shop and its products, by country and email.
 *
 * Read-only, and the only place that knows the mirror's schema. Everything
 * else -- the endpoint, the form -- deals in the array this returns.
 */

/** Past this age the mirror is reported stale but still served. */
const UP_DOWN_STALE_AFTER_HOURS = 48;

/**
 * Find a mirrored shop by country and email.
 *
 * The email is normalized the same way the sync normalizes it before storing,
 * so idx_country_email is used without a LOWER() that would defeat it.
 *
 * @return array{found: bool, shop?: array, subscriptions?: array,
 *               syncedAt?: string, stale?: bool, multiple?: bool}
 */
function upDownLookupShopByEmail($db, string $country, string $email): array
{
    $country = strtoupper(trim($country));
    $email   = upDownNormalizeEmail($email);

    if ($country === '' || $email === '') {
        return ['found' => false];
    }

    // Ordered by shop_name so a duplicated email returns the same shop every
    // time rather than whichever row the engine happens to hand back first.
    // shop_id breaks the remaining tie: two shops sharing an email AND a name
    // would otherwise still come back in index order, which is not a promise.
    $shops = $db->query(
        "SELECT shop_id, shop_name, shop_type, owner_name, phone, board_id, synced_at
         FROM l4u_up_down_shops
         WHERE country = ? AND email = ?
         ORDER BY shop_name ASC, shop_id ASC",
        $country, $email
    )->fetchAll();

    if (empty($shops)) {
        return ['found' => false];
    }

    // Every matching shop is returned with its own products, because one owner
    // running several branches under one email is common here: 69 emails match
    // more than one shop, and in 15 of those the first by name has no
    // subscriptions while a sibling does. Returning only the first showed
    // "Found <shop>" with an empty product list for those customers. The form
    // asks which branch is theirs instead of guessing.
    $matches = [];
    foreach ($shops as $shop) {
        $matches[] = [
            'shopId'    => (string) $shop['shop_id'],
            'shopName'  => (string) $shop['shop_name'],
            'shopType'  => (string) $shop['shop_type'],
            'ownerName' => (string) $shop['owner_name'],
            'phone'     => (string) $shop['phone'],
            'boardId'   => (string) $shop['board_id'],
            // The email is deliberately not echoed back: the caller already
            // has it, and not returning it keeps this response from being a
            // confirmation oracle for an address someone merely guessed.
            'subscriptions' => upDownLookupSubscriptions($db, (string) $shop['shop_id']),
        ];
    }

    $syncedAt = (string) $shops[0]['synced_at'];
    $stale    = (strtotime($syncedAt) < time() - UP_DOWN_STALE_AFTER_HOURS * 3600);

    // The first match stays at the top level unchanged, so a caller that only
    // understands one shop keeps working; `shops` carries the full list.
    $first = $matches[0];
    $subscriptions = $first['subscriptions'];
    unset($first['subscriptions']);

    return [
        'found'         => true,
        'shop'          => $first,
        'subscriptions' => $subscriptions,
        'shops'         => $matches,
        'syncedAt'      => $syncedAt,
        'stale'         => $stale,
        'multiple'      => count($shops) > 1,
    ];
}

/**
 * One row per distinct product name for a shop, not per Monday subscription row.
 *
 * A shop can carry the same product many times over -- one sampled shop holds
 * ten separate "Social Media Marketing Bundle" rows -- and listing each would
 * look like a broken form. MIN(sub_id) just gives the group a stable id.
 *
 * price and currency are deliberately NOT selected: the customer-facing form
 * shows product names only. The columns stay populated in the mirror for
 * internal reporting.
 */
function upDownLookupSubscriptions($db, string $shopId): array
{
    $subs = $db->query(
        "SELECT MIN(sub_id) AS sub_id, product_group, MIN(raw_name) AS raw_name
         FROM l4u_up_down_subscriptions
         WHERE shop_id = ?
         GROUP BY product_group
         ORDER BY product_group ASC",
        $shopId
    )->fetchAll();

    $out = [];
    foreach ($subs as $s) {
        $out[] = [
            'id'      => (string) $s['sub_id'],
            // product_group is the customer-facing name; rawName carries the SKU.
            'name'    => (string) $s['product_group'],
            'rawName' => (string) $s['raw_name'],
        ];
    }

    return $out;
}
