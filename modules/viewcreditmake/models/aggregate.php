<?php
/**
 * Pure aggregation + forecast math for the Make credit dashboard.
 * No network, no session — so tests/aggregate_test.php can call it directly.
 * Ported from ~/Desktop/viewtoken/src/aggregate.ts
 */

/**
 * Make returns two different date shapes, verified against the live API:
 *   /scenarios/{id}/usage      -> "21-08-2026"                     (DD-MM-YYYY)
 *   /organizations/{id}/usage  -> "2026-08-17T00:00:00.000+10:00"  (ISO, org offset)
 * Both denote a calendar day in the ORG timezone, so the ISO form is truncated
 * as written rather than converted to UTC — converting would shift the day.
 */
function mc_toISODate($v) {
    $s = is_scalar($v) ? (string)$v : '';
    if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $s, $m)) {
        return $m[3] . '-' . $m[2] . '-' . $m[1];
    }
    return preg_match('/^\d{4}-\d{2}-\d{2}/', $s) ? substr($s, 0, 10) : '';
}

/** centicredits -> credits. Tolerates `credits` already being present. */
function mc_normalizeUsage($raw) {
    $rows = [];
    if (isset($raw['data']) && is_array($raw['data'])) {
        $rows = $raw['data'];
    } elseif (isset($raw['usage']) && is_array($raw['usage'])) {
        $rows = $raw['usage'];
    } elseif (is_array($raw) && array_is_list($raw)) {
        $rows = $raw;
    }

    $out = [];
    foreach ($rows as $r) {
        if (!is_array($r)) continue;
        $date = mc_toISODate($r['date'] ?? $r['day'] ?? null);
        if ($date === '') continue; // a dateless row is unusable, not a zero
        $credits = isset($r['centicredits'])
            ? (float)$r['centicredits'] / 100
            : (float)($r['credits'] ?? 0);
        $out[] = [
            'date'       => $date,
            'credits'    => $credits,
            'operations' => (float)($r['operations'] ?? $r['ops'] ?? 0),
        ];
    }
    return $out;
}

/** Inclusive YYYY-MM-DD range filter. String compare is safe for ISO dates. */
function mc_inRange($d, $from = null, $to = null) {
    return (!$from || $d >= $from) && (!$to || $d <= $to);
}

/** Number of days in an inclusive date range. */
function mc_daysBetween($from, $to) {
    $ms = strtotime($to . ' 00:00:00 UTC') - strtotime($from . ' 00:00:00 UTC');
    return (int)floor($ms / 86400) + 1;
}

function mc_shiftDate($date, $days) {
    return gmdate('Y-m-d', strtotime($date . ' 00:00:00 UTC') + $days * 86400);
}

/** Seconds -> a readable Thai interval. */
function mc_humanInterval($sec) {
    $sec = (float)$sec;
    if (!is_finite($sec) || $sec <= 0) return 'ตามช่วงเวลา';
    if (fmod($sec, 3600) == 0) return 'ทุก ' . (int)($sec / 3600) . ' ชั่วโมง';
    if (fmod($sec, 60) == 0)   return 'ทุก ' . (int)($sec / 60) . ' นาที';
    return 'ทุก ' . (int)$sec . ' วินาที';
}

/** Every scheduling shape seen on the live org: immediately, indefinitely,
 *  daily, weekly, monthly, yearly, on-demand. */
function mc_summarizeScheduling($s) {
    static $DOW = ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'];

    $o = is_string($s) ? json_decode($s, true) : $s;
    if (!is_array($o)) return '—';

    $at   = !empty($o['time']) ? ' ' . $o['time'] : '';
    $days = isset($o['days']) && is_array($o['days']) ? $o['days'] : [];

    switch ($o['type'] ?? '') {
        case 'immediately':
            return 'ทันที';
        case 'on-demand':
            return 'เรียกใช้เอง';
        case 'indefinitely':
            $every = mc_humanInterval($o['interval'] ?? 0);
            return !empty($o['restrict']) ? $every . ' (จำกัดช่วงเวลา)' : $every;
        case 'daily':
            return 'ทุกวัน' . $at;
        case 'weekly':
            $names = array_map(fn($d) => $DOW[$d] ?? $d, $days);
            return 'ทุกสัปดาห์ ' . implode(',', $names) . $at;
        case 'monthly':
            return 'ทุกเดือน วันที่ ' . implode(',', $days) . $at;
        case 'yearly':
            $months = isset($o['months']) && is_array($o['months']) ? $o['months'] : [];
            return 'ทุกปี เดือน ' . implode(',', $months) . ' วันที่ ' . implode(',', $days) . $at;
        default:
            return (string)($o['type'] ?? '—');
    }
}

function mc_aggregateScenario($meta, $usage, $from, $to) {
    $daily = array_values(array_filter($usage, fn($d) => mc_inRange($d['date'], $from, $to)));
    usort($daily, fn($a, $b) => strcmp($a['date'], $b['date']));

    $totalCredits    = array_sum(array_column($daily, 'credits'));
    $totalOperations = array_sum(array_column($daily, 'operations'));
    $days = max(1, mc_daysBetween($from, $to));

    return [
        'id'                => $meta['id'],
        'name'              => $meta['name'],
        'isActive'          => !empty($meta['isActive']),
        'schedulingSummary' => mc_summarizeScheduling($meta['scheduling'] ?? null),
        'totalCredits'      => $totalCredits,
        'totalOperations'   => $totalOperations,
        // Divided by calendar days in range, not days with data, so idle days count.
        'avgCreditsPerDay'  => $totalCredits / $days,
        'daily'             => $daily,
    ];
}

/** Average daily credits over the last `window` days ending at `today`. */
function mc_avgDaily($usage, $today, $window) {
    $start = mc_shiftDate($today, -($window - 1));
    $total = 0;
    foreach ($usage as $d) {
        if (mc_inRange($d['date'], $start, $today)) $total += $d['credits'];
    }
    return $total / $window;
}

function mc_forecast($perScenarioUsage, $today, $renewalDate, $remainingCredits, $buffer) {
    $avgDaily7 = 0;
    $avgDaily30 = 0;
    foreach ($perScenarioUsage as $u) {
        $avgDaily7  += mc_avgDaily($u, $today, 7);
        $avgDaily30 += mc_avgDaily($u, $today, 30);
    }
    // Days remaining from today up to renewal; the renewal day itself resets the quota.
    $daysUntilRenewal = max(0, mc_daysBetween($today, $renewalDate) - 1);
    $required = $avgDaily7 * $daysUntilRenewal * $buffer;
    $balance  = $remainingCredits - $required;

    return [
        'daysUntilRenewal' => $daysUntilRenewal,
        'avgDaily7'        => $avgDaily7,
        'avgDaily30'       => $avgDaily30,
        'required'         => $required,
        'remainingCredits' => $remainingCredits,
        'balance'          => $balance,
        'isDeficit'        => $balance < 0,
        'runOutDate'       => $avgDaily7 > 0
            ? mc_shiftDate($today, (int)floor($remainingCredits / $avgDaily7))
            : null,
    ];
}

/** "2026-08-17T00:00:00.000+10:00" -> "UTC+10:00" */
function mc_offsetOf($date) {
    $s = is_scalar($date) ? (string)$date : '';
    return preg_match('/([+-]\d{2}:\d{2})$/', $s, $m) ? 'UTC' . $m[1] : null;
}

/** "UTC+10:00" -> 600 minutes. The client needs the number to render org-local times. */
function mc_offsetMinutes($tz) {
    if (!preg_match('/^UTC([+-])(\d{2}):(\d{2})$/', (string)$tz, $m)) return null;
    return ($m[1] === '-' ? -1 : 1) * ((int)$m[2] * 60 + (int)$m[3]);
}

/**
 * Epoch-ms window for /scenarios/{id}/logs from a calendar date range.
 *
 * The picked dates are calendar days in the ORG timezone, so the window is
 * shifted back by the org offset. Without this, a UTC+10 org asking for
 * "15 Sep" gets 10:00 on the 15th .. 10:00 on the 16th — measured on the live
 * org that returned 1,120 events where the correct window returns 1,297.
 */
function mc_logWindowMs($from, $to, $offsetMinutes) {
    $offMs = (int)$offsetMinutes * 60 * 1000;
    $fromMs = strtotime($from . ' 00:00:00 UTC') * 1000 - $offMs;
    $toMs   = strtotime($to . ' 00:00:00 UTC') * 1000 - $offMs + 86400000;
    return [$fromMs, $toMs];
}
