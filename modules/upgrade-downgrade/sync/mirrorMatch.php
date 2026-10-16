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
function upDownNormalizeEmail(?string $email): string
{
    if ($email === null) return '';
    // Strips the non-breaking space that copy-paste from Monday carries in.
    // A /u regex, not trim(): trim() takes a BYTE set, so listing "\xC2\xA0"
    // there strips a lone trailing 0xA0 byte and truncates any multibyte
    // character whose encoding ends in 0xA0, leaving invalid UTF-8.
    $email = preg_replace('/^[\s\x{00A0}]+|[\s\x{00A0}]+$/u', '', $email);
    return strtolower($email);
}

/**
 * Canonical form for comparing shop names: lowercase, trimmed, inner runs of
 * whitespace collapsed to one space. Used only for the fallback match when a
 * subscription has no relation to a project item.
 */
function upDownNormalizeShopName(?string $name): string
{
    if ($name === null) return '';
    // See upDownNormalizeEmail: a byte-set trim() would corrupt a name ending in
    // a multibyte character whose encoding ends in 0xA0 -- Thai U+0E20 is
    // E0 B8 A0, so 'ลาภ' would be truncated to invalid UTF-8 and then
    // normalize to an empty string, silently breaking the name fallback.
    $name = preg_replace('/^[\s\x{00A0}]+|[\s\x{00A0}]+$/u', '', $name);
    $name = preg_replace('/[\s\x{00A0}]+/u', ' ', $name);
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
function upDownResolveShopId(array $subscription, array $shopsByName): array
{
    $linked = (string) ($subscription['linked_shop_id'] ?? '');
    if ($linked !== '') {
        return ['shop_id' => $linked, 'matched_by' => 'relation'];
    }

    $key = upDownNormalizeShopName($subscription['account_name'] ?? '');
    if ($key !== '' && isset($shopsByName[$key])) {
        return ['shop_id' => (string) $shopsByName[$key], 'matched_by' => 'name'];
    }

    return ['shop_id' => '', 'matched_by' => 'none'];
}
