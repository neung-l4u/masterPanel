<?php
/**
 * Section rules for the AI Araya daily brief.
 *
 * One pure function holds every business rule. The dashboard and the Google Chat
 * message both render its output, so the rules live here and nowhere else. It
 * takes the array from arayaMatchShops() and a date; no network, database or
 * config, which is what keeps it testable from plain arrays.
 */

// Monday live_status labels.
const ARAYA_STATUS_LIVE = 'LIVE';
const ARAYA_STATUS_PENDING_CANCEL = 'Waiting to cancel';
const ARAYA_SETUP_STATUSES = ['Not Live', 'Testing', 'Ready to LIVE', 'Waiting to onboard', 'Stuck', 'Ghosted', 'Pause'];
const ARAYA_CANCELLED_STATUSES = ['Canceled', 'Cancel Before LIVE'];

// Upsell: refresh is at most this many days away (inclusive, <=).
const ARAYA_UPSELL_MAX_DAYS = 15;
// Upsell: fewer than this many minutes left (strict, <).
const ARAYA_UPSELL_MAX_MINUTES = 70;
// Idle rule A: shop must have been live at least this many days (inclusive, >=).
const ARAYA_IDLE_MIN_DAYS_LIVE = 10;
// Idle rule A: flagged when the used fraction of the allocation is below this (strict, <).
const ARAYA_IDLE_MAX_USED_PCT = 0.15;
// Idle rule C: latest period flagged when below this fraction of the prior mean (strict, <).
const ARAYA_QUIET_MAX_PCT_OF_MEAN = 0.30;
// Idle rule C: number of prior closed periods required before the rule is evaluated.
const ARAYA_QUIET_MIN_PRIOR_PERIODS = 3;

/**
 * Trim a string safely for UTF-8. Never use trim(): it is byte-based and
 * corrupts a Thai name ending in U+0E20 (E0 B8 A0).
 */
function arayaClean($s): string
{
    if ($s === null || !is_scalar($s)) {
        return '';
    }
    $out = preg_replace('/^[\s\x{00A0}]+|[\s\x{00A0}]+$/u', '', (string)$s);
    return $out === null ? '' : $out;
}

/** Parse a leading YYYY-MM-DD into a date, or null if absent or not a real date. */
function arayaParseYmd($s): ?DateTimeImmutable
{
    $s = arayaClean($s);
    if (!preg_match('/^(\d{4}-\d{2}-\d{2})/', $s, $m)) {
        return null;
    }
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $m[1]);
    $err = DateTimeImmutable::getLastErrors();
    if ($d === false || ($err && ($err['warning_count'] || $err['error_count']))) {
        return null;
    }
    return $d;
}

/** Whole calendar months and leftover days from a date to today, or null if unusable or in the future. */
function arayaLifespan(?string $fromYmd, string $todayYmd): ?array
{
    $from = arayaParseYmd($fromYmd);
    $to = arayaParseYmd($todayYmd);
    if ($from === null || $to === null) {
        return null;
    }
    $diff = $from->diff($to);
    if ($diff->invert) {
        return null;
    }
    return ['months' => $diff->y * 12 + $diff->m, 'days' => $diff->d];
}

/** Total days from a date to today, or null if unusable or in the future. */
function arayaDaysSince(?string $fromYmd, string $todayYmd): ?int
{
    $from = arayaParseYmd($fromYmd);
    $to = arayaParseYmd($todayYmd);
    if ($from === null || $to === null) {
        return null;
    }
    $diff = $from->diff($to);
    return $diff->invert ? null : (int)$diff->days;
}

/** A number or null; blank and non-numeric values are null. */
function arayaNum($v)
{
    if ($v === null || $v === '' || !is_numeric($v)) {
        return null;
    }
    return $v + 0;
}

function arayaGap(array &$gaps, string $kind, string $name, string $detail): void
{
    $name = arayaClean($name);
    $gaps[] = ['kind' => $kind, 'name' => $name === '' ? '(unnamed shop)' : $name, 'detail' => $detail];
}

/** Rule C test. Returns the finding, or null when not flagged or not evaluable. */
function arayaQuietFinding(array $periods): ?array
{
    $rows = [];
    foreach ($periods as $p) {
        $end = arayaClean($p['period_end'] ?? '');
        $used = arayaNum($p['used_minutes'] ?? null);
        if ($end === '' || $used === null) {
            continue;
        }
        $rows[] = ['end' => $end, 'used' => $used];
    }
    usort($rows, fn($a, $b) => strcmp($b['end'], $a['end']));
    if (count($rows) < ARAYA_QUIET_MIN_PRIOR_PERIODS + 1) {
        return null;
    }
    $latest = $rows[0]['used'];
    $prior = array_slice($rows, 1, ARAYA_QUIET_MIN_PRIOR_PERIODS);
    $mean = array_sum(array_column($prior, 'used')) / count($prior);
    if ($mean <= 0) {
        return null; // never used anything: that is rule A's finding
    }
    if ($latest < ARAYA_QUIET_MAX_PCT_OF_MEAN * $mean) {
        return ['latest_used' => $latest, 'prior_mean' => $mean, 'period_end' => $rows[0]['end']];
    }
    return null;
}

function arayaBuildBrief(array $matchResult, string $todayYmd): array
{
    $gaps = [];
    $overview = ['total' => 0, 'live' => 0, 'pendingCancel' => 0, 'setup' => 0,
                 'cancelled' => 0, 'canceledAfterLive' => 0, 'cancelBeforeLive' => 0];
    $live = $upsell = $upsellExcluded = $idleNew = $idleQuiet = $setup = $cancelling = [];

    // Unmatched Monday rows count in the total and appear in `live`: Monday is the
    // authority on who is a customer, so one is real even when we cannot find its
    // numbers. Unmatched gcal rows are not counted (not yet a customer the team
    // tracks); both kinds still surface in `gaps`.
    $items = [];
    foreach ($matchResult['matched'] ?? [] as $pair) {
        $items[] = ['m' => $pair['monday'] ?? [], 'g' => $pair['gcal'] ?? []];
    }
    foreach ($matchResult['unmatchedMonday'] ?? [] as $m) {
        $items[] = ['m' => $m, 'g' => null];
    }

    foreach ($items as $it) {
        $m = $it['m'];
        $g = $it['g'];
        $name = arayaClean($m['name'] ?? '');
        if ($name === '' && $g) {
            $name = arayaClean($g['shop_name'] ?? '');
        }
        $status = arayaClean($m['live_status'] ?? '');
        $po = arayaClean($m['po'] ?? '');
        $liveDate = arayaClean($m['live_date'] ?? '');
        $overview['total']++;

        if ($g === null) {
            arayaGap($gaps, 'unmatched_monday', $name,
                'On the Monday tracker (' . ($status === '' ? 'no status' : $status) . ') but no matching shop in gcal-booking-api; minutes unknown.');
        }

        $isLive = $status === ARAYA_STATUS_LIVE;
        $isPending = $status === ARAYA_STATUS_PENDING_CANCEL;
        $isCancelled = in_array($status, ARAYA_CANCELLED_STATUSES, true);
        $isSetup = in_array($status, ARAYA_SETUP_STATUSES, true);

        if ($status === '') {
            arayaGap($gaps, 'blank_status', $name, 'Monday live_status is blank; counted in the total only.');
        } elseif (!$isLive && !$isPending && !$isCancelled && !$isSetup) {
            // Not in the brief's gap list, deliberately: the board's status list can
            // grow unannounced, and an unbucketed status would vanish from every count.
            arayaGap($gaps, 'unknown_status', $name, 'Monday live_status "' . $status . '" is not a known label; counted in the total only.');
        }

        $minutes = $g ? arayaNum($g['remaining_minutes'] ?? null) : null;
        $alloc = $g ? arayaNum($g['allocation'] ?? null) : null;
        $lifespan = arayaLifespan($liveDate, $todayYmd);

        if ($isLive) {
            $overview['live']++;
            $live[] = [
                'name' => $name, 'po' => $po, 'live_date' => $liveDate, 'lifespan' => $lifespan,
                'remaining_minutes' => $minutes, 'allocation' => $alloc,
                'days_until_refresh' => $g ? arayaNum($g['days_until_refresh'] ?? null) : null,
                'gcal_missing' => $g === null,
            ];
            if ($lifespan === null) {
                arayaGap($gaps, 'no_live_date', $name, 'Live date is missing, unreadable or in the future; lifespan and the idle rules cannot be evaluated.');
            }
        } elseif ($isPending) {
            $overview['pendingCancel']++;
            if ($lifespan === null) {
                arayaGap($gaps, 'no_live_date', $name, 'Live date is missing, unreadable or in the future; lifespan unknown.');
            }
            $cancelling[] = [
                'name' => $name, 'po' => $po, 'live_date' => $liveDate, 'lifespan' => $lifespan,
                'reason' => arayaClean($m['reason_for_cancel'] ?? ''),
            ];
        } elseif ($isSetup) {
            $overview['setup']++;
            $stalled = arayaDaysSince($m['register_date'] ?? '', $todayYmd);
            if ($stalled === null) {
                arayaGap($gaps, 'no_register_date', $name, 'Register date is missing or unreadable; days stalled unknown.');
            }
            $setup[] = [
                'name' => $name, 'live_status' => $status, 'setup_status' => arayaClean($m['setup_status'] ?? ''),
                'po' => $po, 'note' => arayaClean($m['note'] ?? ''),
                'register_date' => arayaClean($m['register_date'] ?? ''), 'days_stalled' => $stalled,
            ];
        } elseif ($isCancelled) {
            $overview['cancelled']++;
            if ($status === 'Canceled') {
                $overview['canceledAfterLive']++;
            } else {
                $overview['cancelBeforeLive']++;
            }
        }

        // Upsell: only for shops with gcal data. Shops that have given notice are
        // checked too, but land in upsellExcluded with a reason: pitching more minutes
        // at a shop that has given notice wastes time, and the reason is why it is
        // listed rather than silently dropped.
        if ($g !== null && ($isLive || $isPending || $isCancelled)) {
            $billingDay = arayaNum($g['billing_day'] ?? null);
            $refresh = arayaNum($g['days_until_refresh'] ?? null);
            if ($billingDay === null || $billingDay <= 0 || $refresh === null) {
                // Only LIVE shops: one not live yet has no reason to have a billing
                // day, and raising it would fill the section with noise.
                if ($isLive) {
                    arayaGap($gaps, 'no_billing_day', $name, 'No billing day set in gcal; days until refresh unknown, so the upsell rule cannot be evaluated.');
                }
            } elseif ($minutes !== null && $refresh <= ARAYA_UPSELL_MAX_DAYS && $minutes < ARAYA_UPSELL_MAX_MINUTES) {
                $row = ['name' => $name, 'po' => $po, 'remaining_minutes' => $minutes, 'days_until_refresh' => $refresh,
                        'allocation' => $alloc, 'currency' => arayaClean($g['currency'] ?? '')];
                // Only LIVE shops: one not live yet has no reason to have a billing
                // day, and raising it would fill the section with noise.
                if ($isLive) {
                    $upsell[] = $row;
                } else {
                    $row['reason'] = 'Status is "' . $status . '"; the shop has given notice.';
                    $upsellExcluded[] = $row;
                }
            }
        }

        // Idle rules: LIVE shops with gcal data only. Rule C first; it wins over A.
        if ($isLive && $g !== null) {
            $quiet = arayaQuietFinding(is_array($g['closed_periods'] ?? null) ? $g['closed_periods'] : []);
            if ($quiet !== null) {
                $idleQuiet[] = ['name' => $name, 'po' => $po, 'remaining_minutes' => $minutes, 'allocation' => $alloc] + $quiet;
            }
            // no_allocation is raised for LIVE shops only, for the same reason.
            if ($alloc === null || $alloc <= 0) {
                arayaGap($gaps, 'no_allocation', $name, 'No minute allocation in gcal; the idle rule would divide by zero, so it was not evaluated.');
            } elseif ($quiet === null && $minutes !== null) {
                $daysLive = arayaDaysSince($liveDate, $todayYmd);
                $usedFraction = ($alloc - $minutes) / $alloc;
                if ($daysLive !== null && $daysLive >= ARAYA_IDLE_MIN_DAYS_LIVE && $usedFraction < ARAYA_IDLE_MAX_USED_PCT) {
                    $idleNew[] = ['name' => $name, 'po' => $po, 'days_live' => $daysLive, 'remaining_minutes' => $minutes,
                                  'allocation' => $alloc, 'used_pct' => round($usedFraction * 100, 1)];
                }
            }
        }
    }

    foreach ($matchResult['unmatchedGcal'] ?? [] as $g) {
        $n = arayaClean($g['shop_name'] ?? '');
        if ($n === '') {
            $n = arayaClean($g['shop_id'] ?? '');
        }
        arayaGap($gaps, 'unmatched_gcal', $n, 'Shop exists in gcal-booking-api but has no matching Monday item.');
    }

    // Most urgent first; unknown minutes last.
    usort($live, function ($a, $b) {
        if ($a['remaining_minutes'] === null || $b['remaining_minutes'] === null) {
            return ($a['remaining_minutes'] === null) <=> ($b['remaining_minutes'] === null);
        }
        return $a['remaining_minutes'] <=> $b['remaining_minutes'];
    });
    // Longest stalled first; unknown last.
    usort($setup, function ($a, $b) {
        if ($a['days_stalled'] === null || $b['days_stalled'] === null) {
            return ($a['days_stalled'] === null) <=> ($b['days_stalled'] === null);
        }
        return $b['days_stalled'] <=> $a['days_stalled'];
    });

    return [
        'generated_for' => $todayYmd,
        'overview' => $overview,
        'live' => $live,
        'upsell' => $upsell,
        'upsellExcluded' => $upsellExcluded,
        'idleNew' => $idleNew,
        'idleQuiet' => $idleQuiet,
        'setup' => $setup,
        'cancelling' => $cancelling,
        'gaps' => $gaps,
    ];
}
