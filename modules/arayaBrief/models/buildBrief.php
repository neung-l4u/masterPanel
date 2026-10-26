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
// Running out: flagged when the projected balance at the refresh date is below
// this (inclusive, <=). Zero means the shop is predicted to hit empty exactly.
const ARAYA_RUNOUT_MIN_PROJECTED = 0;
// Running out: at least this many days of the period must have elapsed before a
// burn rate is trusted. Two days of heavy use is not a trend.
const ARAYA_RUNOUT_MIN_DAYS_ELAPSED = 5;
// At risk: a balance at or below this many minutes is urgent whatever the
// burn rate, because one long call ends it.
const ARAYA_AT_RISK_MINUTES = 30;

/**
 * Trim a string safely for UTF-8. Never use trim() here, for two reasons:
 *
 *  - trim() with \xA0 added to its character list is byte-based and corrupts a
 *    Thai name ending in U+0E20 (E0 B8 A0) by eating the trailing A0 byte,
 *    leaving invalid UTF-8. That is the bug behind
 *    modules/upgrade-downgrade/tests/test_mirror_match.php.
 *  - Default trim() leaves such a name intact, but it does not strip a
 *    non-breaking space either, so NBSP-padded Monday values survive and then
 *    fail to match their gcal counterpart.
 *
 * The /u regex below handles both. test_build_brief.php covers both cases.
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

/**
 * Record a shop a rule had to skip.
 *
 * $expected marks the ones nobody needs to act on. A shop that cancelled is
 * absent from gcal because that is what cancelling means, and on the real
 * board those outnumber the genuine problems 25 to 3 -- left in one list they
 * bury the rows that matter and train people to ignore the whole section.
 * They are still recorded, so the count stays honest and anything odd among
 * them is still findable; they are just kept apart from the work.
 */
function arayaGap(array &$gaps, string $kind, string $name, string $detail, bool $expected = false): void
{
    $name = arayaClean($name);
    $gaps[] = [
        'kind' => $kind,
        'name' => $name === '' ? '(unnamed shop)' : $name,
        'detail' => $detail,
        'expected' => $expected,
    ];
}

/**
 * A deliberately crude key for spotting two spellings of one shop name.
 *
 * Strips everything but letters and digits, so "Chiva Raks Holistic Spa and
 * Wellness Centre" and "Chivaraks Holistic Spa" start to look alike. This is
 * far looser than the matcher's normalisation on purpose: its output only ever
 * produces a suggestion for a human to check, never a pairing, so a false
 * suggestion costs a glance while a missed one costs a shop's minutes.
 */
function arayaLooseKey($s): string
{
    $s = mb_strtolower(arayaClean($s), 'UTF-8');
    // Drop the words that appear in half the names on this board and so carry
    // no distinguishing information.
    $s = preg_replace('/\b(thai|massage|spa|and|the|wellness|centre|center|holistic|therapy|therapeutic|llc|ltd)\b/u', ' ', $s);
    return preg_replace('/[^\p{L}\p{N}]+/u', '', $s) ?? '';
}

/**
 * Do two loose keys look like the same shop?
 *
 * One containing the other covers the common case, where a board name carries
 * extra words. Below that, similar_text's percentage catches a transposition
 * or a missing letter. The bar is high enough that unrelated shops on this
 * board do not reach it.
 */
function arayaLooseMatch(string $a, string $b): bool
{
    if ($a === '' || $b === '') {
        return false;
    }
    if ($a === $b) {
        return true;
    }
    // Require a reasonable stem before accepting containment, so a two-letter
    // remainder cannot match half the board.
    if (mb_strlen($a) >= 5 && mb_strlen($b) >= 5
        && (str_contains($a, $b) || str_contains($b, $a))) {
        return true;
    }
    similar_text($a, $b, $pct);
    return $pct >= 82.0;
}

/**
 * Will this shop run out of minutes before its allowance refreshes?
 *
 * Burn rate comes from what the shop has already spent this period, not from a
 * fixed number: a shop on 250 minutes a month and one on 1000 are not in the
 * same danger at the same balance. Days elapsed is derived from the refresh
 * date and the period length rather than guessed.
 *
 * Returns null when there is nothing trustworthy to say -- too early in the
 * period, no allowance recorded, or a balance that is already negative, which
 * is a different conversation and is reported as at-risk instead.
 */
function arayaRunoutFinding(?int $minutes, ?int $alloc, ?int $daysToRefresh): ?array
{
    if ($minutes === null || $alloc === null || $daysToRefresh === null) {
        return null;
    }
    if ($alloc <= 0 || $minutes < 0 || $daysToRefresh <= 0) {
        return null;
    }

    // A monthly cycle; days elapsed is what has already passed of it.
    $periodDays = 30;
    $daysElapsed = $periodDays - $daysToRefresh;
    if ($daysElapsed < ARAYA_RUNOUT_MIN_DAYS_ELAPSED) {
        return null;   // Two busy days is not a trend.
    }

    $used = $alloc - $minutes;
    if ($used <= 0) {
        return null;   // Spent nothing; the idle rules cover this shop.
    }

    $perDay = $used / $daysElapsed;
    $projectedUse = $perDay * $daysToRefresh;
    $projectedBalance = (int) round($minutes - $projectedUse);
    if ($projectedBalance > ARAYA_RUNOUT_MIN_PROJECTED) {
        return null;
    }

    // How many days from now the balance reaches zero at this rate.
    $daysLeft = $perDay > 0 ? (int) floor($minutes / $perDay) : null;

    return [
        'per_day' => round($perDay, 1),
        'days_until_empty' => $daysLeft,
        'days_until_refresh' => $daysToRefresh,
        'projected_balance' => $projectedBalance,
    ];
}

/** Rule C test. Returns the finding, or null when not flagged or not evaluable. */
function arayaQuietFinding(array $periods): ?array
{
    $rows = [];
    foreach ($periods as $p) {
        $end = arayaClean($p['period_end'] ?? '');
        $used = arayaNum($p['used_minutes'] ?? null);
        if ($end === '' || $used === null) {
            // A malformed closed period makes the history untrustworthy. Skipping it
            // would shift the comparison onto older periods and judge the shop on stale
            // data, so the shop is treated as not evaluable. No gap is raised; this
            // reuses the "not flagged" outcome of the other not-evaluable cases.
            return null;
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

/** The two reporting windows gcal sends, and the figures in each. Exact upstream keys. */
const ARAYA_CALL_WINDOWS = ['d7', 'd30'];
const ARAYA_CALL_FIGURES = ['total', 'forwarded', 'short', 'silent', 'errors', 'minutes',
                            'booked', 'booking_failed', 'long', 'booked_unknown'];

// Which window the headline rates and the alert sections are judged on. The
// 7-day figure is what a conversation about "this week" means, and a 30-day
// window would take a month to show a problem that started on Monday.
const ARAYA_RATE_WINDOW = 'd7';

// A rate computed from fewer calls than this is arithmetic, not evidence: one
// forwarded call out of three is 33% and means nothing. Such a shop still
// shows its counts; it is simply not flagged and not compared with the fleet.
const ARAYA_RATE_MIN_CALLS = 10;

// Flagged when the shop's rate is at least this many percentage points above
// the fleet's. Points, not a ratio: a shop at 13.5% against a fleet at 12.4%
// is doing roughly what everyone else does, and chasing it would bury the
// shop sitting twice as high.
const ARAYA_RATE_ALERT_MARGIN_PTS = 5.0;

// Short-hangup rate flagged from here up whatever the fleet does. The fleet
// average for this is itself high enough that comparing with it would excuse
// every shop, which is exactly how a problem this size goes unnoticed.
const ARAYA_SHORT_ALERT_PCT = 20.0;

// Conversion flagged below here, again against the fleet rather than an
// absolute: what counts as a good booking rate differs by country and by
// service, and the fleet is the only honest baseline we have.
const ARAYA_CONVERSION_ALERT_MARGIN_PTS = 5.0;

// Booking failures are a broken system, not a trend: this many in a window on
// one shop is worth naming. Not a rate, because the whole point is that two
// failed bookings out of two calls and two out of fifty are both bugs.
const ARAYA_BOOKING_FAILED_ALERT = 1;

/**
 * A shop's call-quality figures, or null when we have none.
 *
 * Null and zero are different statements: a shop with no gcal match, or one
 * whose row carries no calls block, is a shop we know nothing about, while a
 * shop with total 0 really took no calls. Collapsing the first into the second
 * would show a shop whose line may be broken as having a quiet week. So a
 * missing window is null, a missing figure inside a window is null, and only
 * a figure gcal actually sent is a number.
 */
function arayaCallsBlock(?array $g): ?array
{
    if ($g === null || !is_array($g['calls'] ?? null)) {
        return null;
    }
    $out = [];
    $any = false;
    foreach (ARAYA_CALL_WINDOWS as $w) {
        $win = $g['calls'][$w] ?? null;
        if (!is_array($win)) {
            $out[$w] = null;
            continue;
        }
        $any = true;
        foreach (ARAYA_CALL_FIGURES as $k) {
            $out[$w][$k] = arayaNum($win[$k] ?? null);
        }
    }
    return $any ? $out : null;
}

/**
 * One rate as a percentage, or null when there is nothing honest to report.
 *
 * Null covers three different situations, and all three must stay out of a
 * displayed percentage: a figure gcal never sent, a denominator of zero, and a
 * denominator too small to mean anything. The third is the one that matters in
 * practice -- one forwarded call out of three is 33% and would put a quiet
 * shop at the top of an alert list ahead of a shop with a real problem.
 *
 * $minDenominator of 0 computes the rate whatever the sample size, for the
 * fleet figure, where the sample is every call the fleet took.
 */
function arayaRate($numerator, $denominator, int $minDenominator = ARAYA_RATE_MIN_CALLS): ?float
{
    $n = arayaNum($numerator);
    $d = arayaNum($denominator);
    if ($n === null || $d === null || $d <= 0 || $d < $minDenominator) {
        return null;
    }
    return round(($n / $d) * 100, 1);
}

/**
 * The rates for one call window: forwarded, short, and conversion.
 *
 * Forwarded and short are shares of every call. Conversion is a share of the
 * calls long enough to have booked, minus the ones whose booked state is
 * unknown: dividing by every call would count misdials as lost bookings, and
 * counting unknowns as failures would report the whole pre-instrumentation
 * call history as a conversion collapse.
 *
 * Every rate can be null, and null never means zero. The window is passed
 * through untouched alongside them so a reader can always see the counts the
 * rates came from.
 */
function arayaWindowRates(?array $win, int $minDenominator = ARAYA_RATE_MIN_CALLS): array
{
    if (!is_array($win)) {
        return ['forwarded_pct' => null, 'short_pct' => null, 'conversion_pct' => null,
                'conversion_base' => null];
    }

    $long = arayaNum($win['long'] ?? null);
    $unknown = arayaNum($win['booked_unknown'] ?? null);
    // Calls we actually know the booking outcome for. Null propagates: without
    // both figures there is no denominator we can defend.
    $known = ($long === null || $unknown === null) ? null : max($long - $unknown, 0);

    return [
        'forwarded_pct' => arayaRate($win['forwarded'] ?? null, $win['total'] ?? null, $minDenominator),
        'short_pct' => arayaRate($win['short'] ?? null, $win['total'] ?? null, $minDenominator),
        'conversion_pct' => arayaRate($win['booked'] ?? null, $known, $minDenominator),
        'conversion_base' => $known,
    ];
}

/**
 * The fleet's own figures and rates, or null when gcal sent none.
 *
 * The fleet rates use no minimum sample: the denominator is every call the
 * fleet took, so there is no small-sample problem to guard against, and a
 * minimum would silently switch the comparison off on a quiet fleet.
 */
function arayaFleetRates(?array $fleet): ?array
{
    if (!is_array($fleet)) {
        return null;
    }
    $out = [];
    $any = false;
    foreach (ARAYA_CALL_WINDOWS as $w) {
        $win = $fleet[$w] ?? null;
        if (!is_array($win)) {
            $out[$w] = null;
            continue;
        }
        $any = true;
        $figures = [];
        foreach (ARAYA_CALL_FIGURES as $k) {
            $figures[$k] = arayaNum($win[$k] ?? null);
        }
        $out[$w] = $figures + arayaWindowRates($win, 0);
    }
    return $any ? $out : null;
}

/**
 * Failed calls, cleaned for display. Upstream order (newest first) is kept.
 *
 * recording_url may be null, and it ends up in an href, so only an http(s)
 * URL survives: anything else (a javascript: URI, a bare word) becomes null
 * and renders as plain text rather than a broken or hostile link.
 */
function arayaCleanCallErrors(?array $errors): array
{
    $out = [];
    foreach ($errors ?? [] as $e) {
        if (!is_array($e)) {
            continue;
        }
        $url = arayaClean($e['recording_url'] ?? null);
        $out[] = [
            'shop_id' => arayaClean($e['shop_id'] ?? ''),
            'shop_name' => arayaClean($e['shop_name'] ?? ''),
            'started_at' => arayaClean($e['started_at'] ?? ''),
            'outcome' => arayaClean($e['outcome'] ?? ''),
            'duration_sec' => arayaNum($e['duration_sec'] ?? null),
            'recording_url' => preg_match('~^https?://\S+$~i', $url) === 1 ? $url : null,
        ];
    }
    return $out;
}

function arayaBuildBrief(array $matchResult, string $todayYmd, ?array $callErrors = null, ?array $fleetCalls = null): array
{
    $gaps = [];
    $overview = ['total' => 0, 'live' => 0, 'pendingCancel' => 0, 'setup' => 0,
                 'cancelled' => 0, 'canceledAfterLive' => 0, 'cancelBeforeLive' => 0];
    $live = $upsell = $upsellExcluded = $idleNew = $idleQuiet = $setup = $cancelling = [];
    $atRisk = $runningOut = [];
    $forwardingHigh = $hangupHigh = $conversionLow = $bookingBroken = [];

    $fleet = arayaFleetRates($fleetCalls);
    $fleetWindow = is_array($fleet) ? ($fleet[ARAYA_RATE_WINDOW] ?? null) : null;

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

        // The tracker's Live Date is the right answer when it is filled in, but
        // in practice it is blank on most shops, which left every lifespan
        // unknown and switched the idle rules off for the whole fleet. gcal's
        // signup_date is the next best evidence a shop exists and since when.
        // It is a sign-up date, not a go-live date, so it reads early for a
        // shop that took a while to switch on -- hence date_source, which the
        // dashboard and the message show rather than passing one off as the
        // other.
        $liveDate = arayaClean($m['live_date'] ?? '');
        $dateSource = $liveDate !== '' ? 'live_date' : '';
        if ($liveDate === '' && $g !== null) {
            $signup = arayaClean($g['signup_date'] ?? '');
            if ($signup !== '') {
                $liveDate = $signup;
                $dateSource = 'signup_date';
            }
        }
        $overview['total']++;

        if ($g === null) {
            // A cancelled shop has no gcal row because cancelling is what
            // removes it. Recording it keeps the count honest, but it is not
            // something anyone needs to chase.
            $cancelledHere = in_array($status, ARAYA_CANCELLED_STATUSES, true);
            arayaGap($gaps, 'unmatched_monday', $name,
                'On the Monday tracker (' . ($status === '' ? 'no status' : $status) . ') but no matching shop in gcal-booking-api; minutes unknown.',
                $cancelledHere);
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
            $callsBlock = arayaCallsBlock($g);
            // Rates for every window, so the table can show whichever it
            // displays, and the alert sections below read one of them.
            $rates = [];
            foreach (ARAYA_CALL_WINDOWS as $w) {
                $rates[$w] = arayaWindowRates(is_array($callsBlock) ? ($callsBlock[$w] ?? null) : null);
            }
            $live[] = [
                'name' => $name, 'po' => $po, 'live_date' => $liveDate, 'lifespan' => $lifespan,
                'date_source' => $dateSource,
                'remaining_minutes' => $minutes, 'allocation' => $alloc,
                'days_until_refresh' => $g ? arayaNum($g['days_until_refresh'] ?? null) : null,
                'gcal_missing' => $g === null,
                'calls' => $callsBlock,
                'rates' => $rates,
            ];

            // Call-quality findings, all judged on one window so a reader is
            // never comparing a 7-day rate with a 30-day one.
            $win = is_array($callsBlock) ? ($callsBlock[ARAYA_RATE_WINDOW] ?? null) : null;
            $rate = $rates[ARAYA_RATE_WINDOW];
            $row = ['name' => $name, 'po' => $po,
                    'total' => is_array($win) ? ($win['total'] ?? null) : null];

            // Forwarding and conversion are compared with the fleet, because
            // what counts as normal differs by country and by service and the
            // fleet is the only baseline we have. Without a fleet figure
            // neither is flagged: an absolute threshold invented here would be
            // a guess presented as a finding.
            $fleetForwarded = is_array($fleetWindow) ? ($fleetWindow['forwarded_pct'] ?? null) : null;
            if ($rate['forwarded_pct'] !== null && $fleetForwarded !== null
                && $rate['forwarded_pct'] >= $fleetForwarded + ARAYA_RATE_ALERT_MARGIN_PTS) {
                $forwardingHigh[] = $row + ['pct' => $rate['forwarded_pct'],
                                            'fleet_pct' => $fleetForwarded,
                                            'forwarded' => $win['forwarded'] ?? null];
            }

            // Short hang-ups use an absolute threshold instead. The fleet
            // average for this is itself high, so comparing with it would
            // excuse every shop -- which is how a problem twice the size of
            // the forwarding one stays invisible.
            if ($rate['short_pct'] !== null && $rate['short_pct'] >= ARAYA_SHORT_ALERT_PCT) {
                $hangupHigh[] = $row + ['pct' => $rate['short_pct'],
                                        'fleet_pct' => is_array($fleetWindow) ? ($fleetWindow['short_pct'] ?? null) : null,
                                        'short' => $win['short'] ?? null];
            }

            $fleetConversion = is_array($fleetWindow) ? ($fleetWindow['conversion_pct'] ?? null) : null;
            if ($rate['conversion_pct'] !== null && $fleetConversion !== null
                && $rate['conversion_pct'] <= $fleetConversion - ARAYA_CONVERSION_ALERT_MARGIN_PTS) {
                $conversionLow[] = $row + ['pct' => $rate['conversion_pct'],
                                           'fleet_pct' => $fleetConversion,
                                           'booked' => $win['booked'] ?? null,
                                           'base' => $rate['conversion_base']];
            }

            // Booking failures are counted, not rated: two failures out of two
            // calls and two out of fifty are the same bug. Not gated on the
            // sample size either, for the same reason.
            $failed = is_array($win) ? arayaNum($win['booking_failed'] ?? null) : null;
            if ($failed !== null && $failed >= ARAYA_BOOKING_FAILED_ALERT) {
                $bookingBroken[] = $row + ['failed' => $failed,
                                           'booked' => $win['booked'] ?? null];
            }
            if ($lifespan === null) {
                arayaGap($gaps, 'no_live_date', $name, 'No usable date: the tracker Live Date is empty and gcal has no signup date either, so lifespan and the idle rules cannot be evaluated.');
            }
            if ($g !== null && $minutes === null) {
                arayaGap($gaps, 'no_remaining_minutes', $name, 'Remaining minutes are missing or not a number in gcal; the upsell and idle checks could not run for this shop.');
            }

            $daysToRefresh = $g ? arayaNum($g['days_until_refresh'] ?? null) : null;

            // At risk: already overdrawn, or so close to empty that one long
            // call ends it. This is the "call them today" list.
            if ($minutes !== null && $minutes <= ARAYA_AT_RISK_MINUTES) {
                $atRisk[] = [
                    'name' => $name, 'po' => $po,
                    'remaining_minutes' => $minutes, 'allocation' => $alloc,
                    'days_until_refresh' => $daysToRefresh,
                    'overdrawn' => $minutes < 0,
                ];
            }

            // Running out: not low yet, but spending fast enough that it will
            // be before the allowance refreshes.
            $runout = arayaRunoutFinding($minutes, $alloc, $daysToRefresh);
            if ($runout !== null && $minutes > ARAYA_AT_RISK_MINUTES) {
                $runningOut[] = ['name' => $name, 'po' => $po,
                                 'remaining_minutes' => $minutes, 'allocation' => $alloc] + $runout;
            }
        } elseif ($isPending) {
            $overview['pendingCancel']++;
            if ($lifespan === null) {
                arayaGap($gaps, 'no_live_date', $name, 'No usable date: the tracker Live Date is empty and gcal has no signup date either, so lifespan is unknown.');
            }
            $cancelling[] = [
                'name' => $name, 'po' => $po, 'live_date' => $liveDate, 'lifespan' => $lifespan,
                'date_source' => $dateSource,
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
                if ($usedFraction < 0) {
                    // Balance above allocation (typically a top-up): the allocation does
                    // not describe the current balance. Not clamped to 0, which would
                    // report real top-ups as zero usage and flag them idle.
                    arayaGap($gaps, 'no_allocation', $name, 'Remaining minutes (' . $minutes . ') exceed the allocation (' . $alloc . '); the balance is above the allocation, so the usage percentage is not meaningful and the idle rule was not evaluated.');
                } elseif ($daysLive !== null && $daysLive >= ARAYA_IDLE_MIN_DAYS_LIVE && $usedFraction < ARAYA_IDLE_MAX_USED_PCT) {
                    $idleNew[] = ['name' => $name, 'po' => $po, 'days_live' => $daysLive, 'remaining_minutes' => $minutes,
                                  'allocation' => $alloc, 'used_pct' => round($usedFraction * 100, 1)];
                }
            }
        }
    }

    // A shop unmatched on both sides is usually one shop spelled two ways --
    // "Chiva Raks Holistic Spa and Wellness Centre" on the board against
    // "Chivaraks Holistic Spa" in gcal. Reported as two separate "missing"
    // rows, nobody connects them; named as a likely pair, the fix is obvious.
    // Only shops that failed to match are compared, so this cannot contradict
    // a real pairing, and the matcher itself stays strict: a near match is a
    // hint for a person, never grounds to attribute one shop's minutes to
    // another.
    $gcalLeftovers = [];
    foreach ($matchResult['unmatchedGcal'] ?? [] as $g) {
        $n = arayaClean($g['shop_name'] ?? '');
        if ($n === '') {
            $n = arayaClean($g['shop_id'] ?? '');
        }
        $gcalLeftovers[] = ['name' => $n, 'key' => arayaLooseKey($n)];
    }

    $mondayLeftovers = [];
    foreach ($matchResult['unmatchedMonday'] ?? [] as $m) {
        $status = arayaClean($m['live_status'] ?? '');
        // A cancelled shop is gone from gcal on purpose; pairing it with a
        // live gcal row would be noise, not a finding.
        if (in_array($status, ARAYA_CANCELLED_STATUSES, true)) {
            continue;
        }
        $n = arayaClean($m['name'] ?? '');
        $mondayLeftovers[$n] = arayaLooseKey($n);
    }

    $pairedGcal = [];
    $namedMismatch = [];   // Monday shops already explained by a name_mismatch row.
    foreach ($gcalLeftovers as $i => $g) {
        foreach ($mondayLeftovers as $mName => $mKey) {
            if ($g['key'] === '' || $mKey === '') {
                continue;
            }
            if (!arayaLooseMatch($g['key'], $mKey)) {
                continue;
            }
            $pairedGcal[$i] = true;
            $namedMismatch[$mName] = true;
            arayaGap(
                $gaps,
                'name_mismatch',
                $mName,
                'Looks like the same shop as "' . $g['name'] . '" in gcal, but the '
                . 'names differ so they were not paired and this shop has no minutes '
                . 'in the report. Fix by making the names match, or better, by putting '
                . 'the Google Calendar ID on the Monday item so spelling stops mattering.'
            );
            break;
        }
    }

    foreach ($gcalLeftovers as $i => $g) {
        if (isset($pairedGcal[$i])) {
            continue;   // Already reported as the clearer name_mismatch finding.
        }
        arayaGap($gaps, 'unmatched_gcal', $g['name'],
            'In gcal-booking-api but no item on the Monday board. Either add it to the '
            . 'board, or if it is a test or retired shop, it can be ignored.');
    }

    // A shop with a name_mismatch row does not also need a bare "no match in
    // gcal" row: the mismatch says the same thing and names the likely partner.
    if ($namedMismatch) {
        $gaps = array_values(array_filter($gaps, function ($g) use ($namedMismatch) {
            return !($g['kind'] === 'unmatched_monday' && isset($namedMismatch[$g['name']]));
        }));
    }

    // Worst first in each call-quality list, so the shop to look at leads.
    // Conversion sorts ascending (lowest rate is the worst); the rest sort
    // descending. Booking failures sort on the count, which is the finding.
    usort($forwardingHigh, fn($a, $b) => $b['pct'] <=> $a['pct']);
    usort($hangupHigh, fn($a, $b) => $b['pct'] <=> $a['pct']);
    usort($conversionLow, fn($a, $b) => $a['pct'] <=> $b['pct']);
    usort($bookingBroken, fn($a, $b) => $b['failed'] <=> $a['failed']);

    // Most urgent first in both new lists: the worst balance leads.
    usort($atRisk, fn($a, $b) => $a['remaining_minutes'] <=> $b['remaining_minutes']);
    usort($runningOut, fn($a, $b) => ($a['days_until_empty'] ?? PHP_INT_MAX) <=> ($b['days_until_empty'] ?? PHP_INT_MAX));

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
        'atRisk' => $atRisk,
        'runningOut' => $runningOut,
        // Call-quality findings, all on the ARAYA_RATE_WINDOW window.
        'forwardingHigh' => $forwardingHigh,
        'hangupHigh' => $hangupHigh,
        'conversionLow' => $conversionLow,
        'bookingBroken' => $bookingBroken,
        // The baseline the first three were judged against, so the dashboard
        // can show it rather than asserting a comparison the reader cannot
        // check. Null when gcal sent no fleet figures.
        'fleetCalls' => $fleet,
        'rateWindow' => ARAYA_RATE_WINDOW,
        'setup' => $setup,
        'cancelling' => $cancelling,
        'gaps' => $gaps,
        'callErrors' => arayaCleanCallErrors($callErrors),
        // Split once here so the dashboard and the Chat message cannot
        // disagree about which gaps are worth acting on.
        'gapsNeedingAction' => count(array_filter($gaps, fn($g) => empty($g['expected']))),
        'gapsExpected' => count(array_filter($gaps, fn($g) => !empty($g['expected']))),
    ];
}
