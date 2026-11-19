<?php
/**
 * Automatic re-scan. Cron can call this daily; it only starts a new run when the last one finished
 * 30+ days ago, and otherwise finishes a run left half-way (e.g. one started from the page).
 *
 *   php modules/competitorScan/cron.php           — start if due, then run to the end
 *   php modules/competitorScan/cron.php --force   — start a new run now
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('This script runs from cron only.'); }
date_default_timezone_set('Asia/Bangkok');
set_time_limit(0);
require_once __DIR__ . '/models/scan.php';

const CS_RESCAN_DAYS = 30;
$meta = cs_read('meta.json');
$due = in_array('--force', $argv, true) || empty($meta['last_finished_at'])
    || strtotime($meta['last_finished_at']) < time() - CS_RESCAN_DAYS * 86400;
if (!$due && !is_file(cs_cache('run.json'))) exit(0);

$start = $due;
$fails = 0;
do {
    $s = cs_step(300, $start);
    $start = false;
    if (!empty($s['busy'])) exit(0);    // a browser is driving the same run right now
    echo date('H:i:s'), ' ', $s['phase'] ?? 'done', ' ', $s['loaded'] ?? '', '/', $s['total'] ?? '', !empty($s['error']) ? '  ERROR ' . $s['error'] : '', "\n";
    if (!empty($s['error'])) {
        if (++$fails >= 10) exit(1);    // run.json keeps the place; tomorrow's cron continues from here
        sleep(30);
    }
} while (empty($s['complete']));
