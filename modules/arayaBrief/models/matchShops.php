<?php
/**
 * Pair Monday client-tracker rows with gcal-booking-api shops.
 *
 * Monday is the authority on who is a customer; gcal holds the minutes. This
 * file is the join between them and nothing else — no rules, no formatting —
 * so it can be tested from arrays with no network.
 *
 * Keys are tried calendar id, then email, then normalized name. Name is last
 * and an ambiguous name match is treated as no match: the board holds
 * "KLR Massage Therapy ( Croydon )" and "KLR Massage Therapy (Purley)", two
 * real branches that normalize identically. Reporting one branch's minutes
 * against the other is worse than reporting that the pairing is unknown.
 */

/**
 * Lowercase and trim a key for comparison.
 *
 * The character class includes U+00A0 and carries the /u flag because trim()
 * is byte-based: on a Thai name ending in ภ (U+0E20 = E0 B8 A0) it eats the
 * trailing A0 and leaves invalid UTF-8.
 */
function arayaNormalizeKey(?string $s): string
{
    if ($s === null) {
        return '';
    }
    $s = preg_replace('/^[\s\x{00A0}]+|[\s\x{00A0}]+$/u', '', $s);
    $s = preg_replace('/[\s\x{00A0}]+/u', ' ', $s);
    return mb_strtolower($s, 'UTF-8');
}

/**
 * Normalize a shop name, dropping a trailing parenthesised group.
 *
 * On this board that group names the booking system or the branch — "(Amelia)",
 * "( Croydon )" — and never appears in the gcal name. Stripping it is what lets
 * the fallback work at all; the ambiguity check in arayaMatchShops is what keeps
 * it from pairing two branches that collapse to the same string.
 */
function arayaNormalizeShopName(?string $s): string
{
    $s = arayaNormalizeKey($s);
    if ($s === '') {
        return '';
    }
    // Only a trailing group, and only if something is left over.
    $stripped = preg_replace('/\s*\([^()]*\)\s*$/u', '', $s);
    if ($stripped !== null && $stripped !== '') {
        $s = $stripped;
    }
    return arayaNormalizeKey($s);
}

/** Index rows by a key, marking any key that appears more than once. */
function arayaIndexUnique(array $rows, callable $keyOf): array
{
    $index = [];
    $dupes = [];
    foreach ($rows as $i => $row) {
        $key = $keyOf($row);
        if ($key === '') {
            continue;
        }
        if (isset($index[$key])) {
            $dupes[$key] = true;
            continue;
        }
        $index[$key] = $i;
    }
    foreach (array_keys($dupes) as $key) {
        unset($index[$key]);
    }
    return $index;
}

/**
 * Pair the two sources.
 *
 * Returns matched pairs plus both unmatched lists. Nothing is dropped: a shop
 * that fails to pair is reported, because unmatched rows vanishing silently is
 * exactly how an earlier mirror-sync problem in this repo went unnoticed.
 */
function arayaMatchShops(array $mondayShops, array $gcalShops): array
{
    $byCalendar = arayaIndexUnique($gcalShops, fn($g) => arayaNormalizeKey($g['calendar_id'] ?? ''));
    $byEmail    = arayaIndexUnique($gcalShops, fn($g) => arayaNormalizeKey($g['shop_email'] ?? ''));
    $byName     = arayaIndexUnique($gcalShops, fn($g) => arayaNormalizeShopName($g['shop_name'] ?? ''));

    // A Monday name shared by two items cannot safely match by name either.
    $mondayNameCounts = [];
    foreach ($mondayShops as $m) {
        $key = arayaNormalizeShopName($m['name'] ?? '');
        if ($key !== '') {
            $mondayNameCounts[$key] = ($mondayNameCounts[$key] ?? 0) + 1;
        }
    }

    $matched = [];
    $unmatchedMonday = [];
    $claimed = [];

    foreach ($mondayShops as $m) {
        $hit = null;

        $cal = arayaNormalizeKey($m['calendar_id'] ?? '');
        if ($cal !== '' && isset($byCalendar[$cal]) && !isset($claimed[$byCalendar[$cal]])) {
            $hit = ['i' => $byCalendar[$cal], 'by' => 'calendar'];
        }

        if ($hit === null) {
            $email = arayaNormalizeKey($m['email'] ?? '');
            if ($email !== '' && isset($byEmail[$email]) && !isset($claimed[$byEmail[$email]])) {
                $hit = ['i' => $byEmail[$email], 'by' => 'email'];
            }
        }

        if ($hit === null) {
            $name = arayaNormalizeShopName($m['name'] ?? '');
            $unambiguous = $name !== ''
                && ($mondayNameCounts[$name] ?? 0) === 1
                && isset($byName[$name])
                && !isset($claimed[$byName[$name]]);
            if ($unambiguous) {
                $hit = ['i' => $byName[$name], 'by' => 'name'];
            }
        }

        if ($hit === null) {
            $unmatchedMonday[] = $m;
            continue;
        }

        $claimed[$hit['i']] = true;
        $matched[] = [
            'monday'     => $m,
            'gcal'       => $gcalShops[$hit['i']],
            'matched_by' => $hit['by'],
        ];
    }

    $unmatchedGcal = [];
    foreach ($gcalShops as $i => $g) {
        if (!isset($claimed[$i])) {
            $unmatchedGcal[] = $g;
        }
    }

    return [
        'matched'         => $matched,
        'unmatchedMonday' => $unmatchedMonday,
        'unmatchedGcal'   => $unmatchedGcal,
    ];
}
