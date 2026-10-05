<?php
/**
 * All writes to the Monday mirror tables.
 *
 * Every statement is a prepared statement through the repo's db wrapper; no
 * value is ever interpolated into SQL.
 */

/**
 * Insert or update one mirrored shop. Returns 'inserted' or 'updated'.
 *
 * MySQL reports affected_rows as 1 for an insert and 2 for an update that
 * changed something, but 0 for an update that changed nothing -- and a sync
 * re-running over unchanged data hits that case constantly. So existence is
 * checked first rather than inferred from affected_rows.
 */
function udmUpsertShop($db, array $shop, string $syncedAt): string
{
    $existing = $db->query(
        "SELECT shop_id FROM l4u_ud_shops WHERE shop_id = ?",
        (string) $shop['shop_id']
    )->fetchArray();
    $action = empty($existing) ? 'inserted' : 'updated';

    $db->query(
        "INSERT INTO l4u_ud_shops
            (shop_id, country, email, shop_name, shop_type,
             owner_name, phone, board_id, synced_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            country    = VALUES(country),
            email      = VALUES(email),
            shop_name  = VALUES(shop_name),
            shop_type  = VALUES(shop_type),
            owner_name = VALUES(owner_name),
            phone      = VALUES(phone),
            board_id   = VALUES(board_id),
            synced_at  = VALUES(synced_at)",
        (string) $shop['shop_id'],
        (string) $shop['country'],
        udmNormalizeEmail($shop['email'] ?? ''),
        (string) ($shop['shop_name']  ?? ''),
        (string) ($shop['shop_type']  ?? ''),
        (string) ($shop['owner_name'] ?? ''),
        (string) ($shop['phone']      ?? ''),
        (string) ($shop['board_id']   ?? ''),
        $syncedAt
    );

    return $action;
}

/**
 * Insert or update one mirrored subscription. Returns 'inserted' or 'updated'.
 *
 * price is bound as a string so a null stays null instead of being coerced to
 * 0.00, which would misreport a free or unpriced line as costing nothing.
 */
function udmUpsertSubscription($db, array $sub, string $syncedAt): string
{
    $existing = $db->query(
        "SELECT sub_id FROM l4u_ud_subscriptions WHERE sub_id = ?",
        (string) $sub['sub_id']
    )->fetchArray();
    $action = empty($existing) ? 'inserted' : 'updated';

    $price = $sub['price'];
    $price = ($price === null || $price === '') ? null : (string) $price;

    $db->query(
        "INSERT INTO l4u_ud_subscriptions
            (sub_id, shop_id, raw_name, product_group, price,
             currency, account_name, matched_by, synced_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            shop_id       = VALUES(shop_id),
            raw_name      = VALUES(raw_name),
            product_group = VALUES(product_group),
            price         = VALUES(price),
            currency      = VALUES(currency),
            account_name  = VALUES(account_name),
            matched_by    = VALUES(matched_by),
            synced_at     = VALUES(synced_at)",
        (string) $sub['sub_id'],
        (string) ($sub['shop_id']       ?? ''),
        (string) ($sub['raw_name']      ?? ''),
        (string) ($sub['product_group'] ?? ''),
        $price,
        (string) ($sub['currency']      ?? ''),
        (string) ($sub['account_name']  ?? ''),
        (string) ($sub['matched_by']    ?? 'none'),
        $syncedAt
    );

    return $action;
}

/**
 * Map of normalized shop name => shop_id for one country, for the fallback
 * match in udmResolveShopId.
 */
function udmLoadShopNameMap($db, string $country): array
{
    $rows = $db->query(
        "SELECT shop_id, shop_name FROM l4u_ud_shops WHERE country = ?",
        $country
    )->fetchAll();

    $map = [];
    foreach ($rows as $r) {
        $key = udmNormalizeShopName($r['shop_name']);
        if ($key !== '') $map[$key] = (string) $r['shop_id'];
    }
    return $map;
}

/**
 * Drop shops in this country that the current run did not refresh, so closed
 * projects disappear from the mirror instead of lingering forever.
 *
 * Scoped by country because a country whose Monday fetch failed is skipped
 * entirely; deleting globally would wipe the rows of every skipped country.
 */
function udmDeleteStaleShops($db, string $country, string $syncedAt): int
{
    $db->query(
        "DELETE FROM l4u_ud_shops WHERE country = ? AND synced_at < ?",
        $country, $syncedAt
    );
    return (int) $db->affectedRows();
}

/**
 * Drop subscriptions the current run did not refresh -- cancellations, and
 * rows whose status stopped containing "active".
 *
 * Not scoped by country: the subscriptions board is one global board, synced
 * in one pass, so anything older than this run's timestamp is genuinely gone.
 */
function udmDeleteStaleSubscriptions($db, string $syncedAt): int
{
    $db->query(
        "DELETE FROM l4u_ud_subscriptions WHERE synced_at < ?",
        $syncedAt
    );
    return (int) $db->affectedRows();
}
