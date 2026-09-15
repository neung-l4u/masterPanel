<?php
/**
 * Port of ~/Desktop/viewtoken/test/aggregate.test.ts — plain asserts, no framework.
 * Run: php modules/viewcreditmake/tests/aggregate_test.php
 */
require_once __DIR__ . '/../models/aggregate.php';

$pass = 0; $fail = 0;

function eq($actual, $expected, $label) {
    global $pass, $fail;
    $ok = (is_float($expected) || is_float($actual))
        ? (is_numeric($actual) && abs((float)$actual - (float)$expected) < 1e-9)
        : $actual === $expected;
    if ($ok) { $pass++; echo "  ok   $label\n"; return; }
    $fail++;
    echo "  FAIL $label\n       expected: " . json_encode($expected, JSON_UNESCAPED_UNICODE)
       . "\n       actual:   " . json_encode($actual, JSON_UNESCAPED_UNICODE) . "\n";
}

function eqRows($actual, $expected, $label) {
    // Compare numerically so 25 and 25.0 match.
    $norm = fn($rows) => array_map(fn($r) => [
        'date' => $r['date'], 'credits' => (float)$r['credits'], 'operations' => (float)$r['operations'],
    ], $rows);
    eq(json_encode($norm($actual)), json_encode($norm($expected)), $label);
}

echo "\n== normalizeUsage ==\n";
eqRows(
    mc_normalizeUsage(['data' => [['date' => '2026-09-01T00:00:00Z', 'operations' => 10, 'centicredits' => 2500]]]),
    [['date' => '2026-09-01', 'credits' => 25, 'operations' => 10]],
    'converts centicredits to credits'
);
eqRows(
    mc_normalizeUsage(['data' => [['date' => '2026-09-02', 'credits' => 3], ['operations' => 9]]]),
    [['date' => '2026-09-02', 'credits' => 3, 'operations' => 0]],
    'tolerates plain credits field and drops dateless rows'
);
eqRows(
    mc_normalizeUsage(['data' => [['date' => '21-08-2026', 'operations' => 62, 'dataTransfer' => 119649, 'centicredits' => 6200]]]),
    [['date' => '2026-08-21', 'credits' => 62, 'operations' => 62]],
    'parses a real scenario usage row'
);
eqRows(
    mc_normalizeUsage(['data' => [['date' => '2026-08-17T00:00:00.000+10:00', 'operations' => 5342, 'centicredits' => 534200]]]),
    [['date' => '2026-08-17', 'credits' => 5342, 'operations' => 5342]],
    'parses a real org usage row'
);

echo "\n== toISODate (trap 1 + 2) ==\n";
eq(mc_toISODate('21-08-2026'), '2026-08-21', 'converts the DD-MM-YYYY form from /scenarios/{id}/usage');
eq(mc_toISODate('01-01-2026'), '2026-01-01', 'converts DD-MM-YYYY at the year boundary');
// +10:00 offset: converting to UTC would roll this back to 2026-08-16.
eq(mc_toISODate('2026-08-17T00:00:00.000+10:00'), '2026-08-17', 'truncates the org ISO form without shifting the day');
eq(mc_toISODate(null), '', 'rejects junk rather than inventing a date');
eq(mc_toISODate('not a date'), '', 'rejects a non-date string');

echo "\n== date helpers ==\n";
eq(mc_daysBetween('2026-09-01', '2026-09-01'), 1, 'daysBetween is inclusive (same day)');
eq(mc_daysBetween('2026-09-01', '2026-09-07'), 7, 'daysBetween is inclusive (a week)');
eq(mc_shiftDate('2026-09-01', -1), '2026-08-31', 'shiftDate crosses a month boundary back');
eq(mc_shiftDate('2026-02-28', 1), '2026-03-01', 'shiftDate crosses a month boundary forward');

echo "\n== aggregateScenario / avgDaily ==\n";
$usage = [
    ['date' => '2026-09-01', 'credits' => 10, 'operations' => 5],
    ['date' => '2026-09-02', 'credits' => 20, 'operations' => 7],
    ['date' => '2026-09-09', 'credits' => 99, 'operations' => 1], // outside range
];
$a = mc_aggregateScenario(['id' => 1, 'name' => 'A', 'isActive' => true], $usage, '2026-09-01', '2026-09-03');
eq((float)$a['totalCredits'], 30.0, 'sums credits in range only');
eq((float)$a['totalOperations'], 12.0, 'sums operations in range only');
// 30 credits / 3 calendar days — the idle day counts.
eq((float)$a['avgCreditsPerDay'], 10.0, 'averages over calendar days, not days with data');
eq(count($a['daily']), 2, 'keeps only in-range daily points');

eq((float)mc_avgDaily([
    ['date' => '2026-09-10', 'credits' => 70, 'operations' => 0],
    ['date' => '2026-08-01', 'credits' => 700, 'operations' => 0], // outside the 7-day window
], '2026-09-10', 7), 10.0, 'avgDaily divides by the window, not by days with data');

echo "\n== forecast ==\n";
$u = [];
for ($i = 0; $i < 7; $i++) {
    $u[] = ['date' => mc_shiftDate('2026-09-15', -$i), 'credits' => 100, 'operations' => 0];
}
$f = mc_forecast([$u, $u], '2026-09-15', '2026-09-25', 1000, 1.2);
eq((float)$f['avgDaily7'], 200.0, 'avgDaily7 sums across scenarios');
// Sep 15 .. Sep 24 inclusive = 10 billable days; renewal day itself resets.
eq($f['daysUntilRenewal'], 10, 'daysUntilRenewal excludes the renewal day');
eq((float)$f['required'], 2400.0, 'required = avgDaily7 * days * buffer');
eq((float)$f['balance'], -1400.0, 'balance is remaining minus required');
eq($f['isDeficit'], true, 'flags a deficit');
eq($f['runOutDate'], '2026-09-20', 'run-out date is 1000/200 = 5 days out');

$f2 = mc_forecast([[]], '2026-09-15', '2026-09-20', 500, 1.2);
eq((float)$f2['required'], 0.0, 'idle org requires nothing');
eq($f2['isDeficit'], false, 'flags a surplus');
eq($f2['runOutDate'], null, 'no run-out date when idle');

echo "\n== scheduling (all 7 shapes) ==\n";
eq(mc_summarizeScheduling(['type' => 'immediately']), 'ทันที', 'immediately');
eq(mc_summarizeScheduling(['type' => 'immediately', 'maximum_runs_per_minute' => 100]), 'ทันที', 'immediately with max runs');
eq(mc_summarizeScheduling(['type' => 'on-demand']), 'เรียกใช้เอง', 'on-demand');
eq(mc_summarizeScheduling(['type' => 'indefinitely', 'interval' => 7200]), 'ทุก 2 ชั่วโมง', 'indefinitely in hours');
eq(mc_summarizeScheduling(['type' => 'indefinitely', 'interval' => 900]), 'ทุก 15 นาที', 'indefinitely in minutes');
eq(mc_summarizeScheduling(['type' => 'indefinitely', 'interval' => 900, 'restrict' => [['days' => [1], 'time' => ['07:00']]]]),
   'ทุก 15 นาที (จำกัดช่วงเวลา)', 'indefinitely with a restriction');
eq(mc_summarizeScheduling(['type' => 'daily', 'time' => '08:00']), 'ทุกวัน 08:00', 'daily');
eq(mc_summarizeScheduling(['type' => 'weekly', 'days' => [6], 'time' => '23:59']), 'ทุกสัปดาห์ ส. 23:59', 'weekly');
eq(mc_summarizeScheduling(['type' => 'monthly', 'days' => [1], 'time' => '00:00']), 'ทุกเดือน วันที่ 1 00:00', 'monthly');
eq(mc_summarizeScheduling(['type' => 'yearly', 'days' => [1], 'months' => [1], 'time' => '00:00']),
   'ทุกปี เดือน 1 วันที่ 1 00:00', 'yearly');
eq(mc_summarizeScheduling(null), '—', 'null scheduling');
eq(mc_summarizeScheduling('{"type":"daily","time":"09:00"}'), 'ทุกวัน 09:00', 'JSON string scheduling');

echo "\n== org timezone (trap 2 + 4) ==\n";
eq(mc_offsetOf('2026-08-17T00:00:00.000+10:00'), 'UTC+10:00', 'reads the org UTC offset');
eq(mc_offsetOf('2026-08-17T00:00:00.000-05:00'), 'UTC-05:00', 'reads a negative offset');
eq(mc_offsetOf('2026-08-17T00:00:00.000Z'), null, 'Z carries no org offset');
eq(mc_offsetOf(null), null, 'no date, no offset');
eq(mc_offsetMinutes('UTC+10:00'), 600, 'offset to signed minutes (+10)');
eq(mc_offsetMinutes('UTC-05:30'), -330, 'offset to signed minutes (-5:30)');
eq(mc_offsetMinutes('UTC+00:00'), 0, 'offset to signed minutes (zero)');
eq(mc_offsetMinutes('ไม่ทราบ'), null, 'unknown timezone yields null');
eq(mc_offsetMinutes(null), null, 'null timezone yields null');

echo "\n== epoch window for /logs (trap 4) ==\n";
// A UTC+10 org's "15 Sep" must span 14 Sep 14:00Z .. 15 Sep 14:00Z, not 15 Sep 00:00Z onward.
$w = mc_logWindowMs('2026-09-15', '2026-09-15', 600);
eq(gmdate('Y-m-d H:i', (int)($w[0] / 1000)), '2026-09-14 14:00', 'window start shifts back by the org offset');
eq(gmdate('Y-m-d H:i', (int)($w[1] / 1000)), '2026-09-15 14:00', 'window end is start + 1 day');
$w0 = mc_logWindowMs('2026-09-15', '2026-09-15', 0);
eq(gmdate('Y-m-d H:i', (int)($w0[0] / 1000)), '2026-09-15 00:00', 'a UTC org gets a plain midnight window');

echo "\n" . str_repeat('-', 46) . "\n";
echo ($fail === 0 ? "ALL PASS" : "FAILURES") . ": $pass passed, $fail failed\n\n";
exit($fail === 0 ? 0 : 1);
