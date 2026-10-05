<?php
/**
 * Pure matching rules for the Monday mirror.
 *
 * Deliberately free of database and network access: these rules are the part
 * of the sync most likely to be wrong, so they are tested exhaustively on
 * their own.
 */

/**
 * Canonical form for storing and querying an email: lowercase, trimmed.
 * Stored normalized and queried normalized, so idx_country_email is usable
 * without a LOWER() call that would defeat it.
 */
function udmNormalizeEmail(?string $email): string
{
    if ($email === null) return '';
    // Includes the non-breaking space, which copy-paste from Monday carries in.
    return strtolower(trim($email, " \t\n\r\0\x0B\xC2\xA0"));
}

/**
 * Canonical form for comparing shop names: lowercase, trimmed, inner runs of
 * whitespace collapsed to one space. Used only for the fallback match when a
 * subscription has no relation to a project item.
 */
function udmNormalizeShopName(?string $name): string
{
    if ($name === null) return '';
    $name = trim($name, " \t\n\r\0\x0B\xC2\xA0");
    $name = preg_replace('/\s+/u', ' ', $name);
    return mb_strtolower($name, 'UTF-8');
}

/**
 * Decide which mirrored shop a subscription belongs to.
 *
 * The Monday board-relation column (`connect_boards`, titled "Projects") is
 * authoritative: it points straight at the project item, and a 100-row sample
 * had it set on every row. The normalized-name fallback exists because that
 * sample does not prove the whole board, and `matched_by` makes how often the
 * fallback fires measurable.
 *
 * @param array $subscription Needs 'linked_shop_id' and 'account_name'.
 * @param array $shopsByName  Map of normalized shop name => shop_id.
 * @return array{shop_id: string, matched_by: string}
 */
function udmResolveShopId(array $subscription, array $shopsByName): array
{
    $linked = (string) ($subscription['linked_shop_id'] ?? '');
    if ($linked !== '') {
        return ['shop_id' => $linked, 'matched_by' => 'relation'];
    }

    $key = udmNormalizeShopName($subscription['account_name'] ?? '');
    if ($key !== '' && isset($shopsByName[$key])) {
        return ['shop_id' => (string) $shopsByName[$key], 'matched_by' => 'name'];
    }

    return ['shop_id' => '', 'matched_by' => 'none'];
}
