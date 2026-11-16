<?php
/**
 * Google Places lookup for shops whose own site can't be checked (see models/places.php). Daily from cron:
 *
 *   php modules/competitorScan/places.php          — look up today's batch, then re-scan those shops
 *   php modules/competitorScan/places.php --dry    — list who would be looked up, no API calls
 *   php modules/competitorScan/places.php --all    — skip the daily cap (monthly cap still applies)
 *
 * Each shop is looked up once (links.json source "places"); new cancellations are picked up as they appear.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('This script runs from cron only.'); }
date_default_timezone_set('Asia/Bangkok');
set_time_limit(0);
require_once __DIR__ . '/models/places.php';

$key = cs_places_key();
$dry = in_array('--dry', $argv, true);
if ($key === '' && !$dry) exit("No places_key in config/competitor_scan.php\n");
if (is_file(cs_cache('run.json'))) exit("A scan is running; try again after it finishes\n");   // it would overwrite our re-scan

$usage = cs_read('places_usage.json');   // month => requests sent
$month = date('Y-m');
$left = min(in_array('--all', $argv, true) ? PHP_INT_MAX : CS_PLACES_DAILY, CS_PLACES_MONTHLY - ($usage[$month] ?? 0));
if ($left <= 0) exit("Monthly Places budget used ({$usage[$month]})\n");

$links = cs_read('links.json');
$cur = []; foreach (cs_read('current.json') as $s) $cur[$s['id']] = $s;
$todo = array_filter($cur, fn($s) => ($s['site_status'] ?? '') !== 'Online' && ($links[$s['id']]['source'] ?? '') !== 'places');
uasort($todo, fn($a, $b) => strcmp($b['cancel_date'], $a['cancel_date']));   // most recent cancellations first
$todo = array_slice($todo, 0, $left, true);
if ($dry) { foreach ($todo as $s) echo "{$s['cc']}  {$s['cancel_date']}  {$s['name']}\n"; exit(count($todo) . " shops\n"); }

$done = [];
foreach ($todo as $id => $s) {
    try { $p = cs_places_search($s, $key); }
    catch (Throwable $e) { echo "STOP: {$e->getMessage()}\n"; break; }   // bad key / quota: keep the rest for tomorrow
    finally { $usage[$month] = ($usage[$month] ?? 0) + 1; cs_write('places_usage.json', $usage); }
    $e = cs_places_entry($s, $p);
    if (isset($links[$id]['urls'])) $e['urls'] = array_values(array_unique(array_merge($links[$id]['urls'], $e['urls'])));   // keep earlier search finds
    if ($e['phone'] === '') $e['phone'] = $links[$id]['phone'] ?? '';
    $links[$id] = $e;
    $done[$id] = $s;
    printf("%-45s %s\n", mb_substr($s['name'], 0, 44), $e['match'] ? ($e['website'] ?: implode(' ', $e['urls']) ?: 'no website') : 'no matching listing');
    usleep(200000);
}
cs_write('links.json', $links);

// re-scan the shops just looked up with the monthly scan's own code
$scan = [];
foreach ($done as $s) $scan[] = array_diff_key($s, array_flip(['site_status', 'http', 'final_url', 'evidence', 'builder', 'notes', 'system', 'marketing', 'agency', 'scanned_at', 'error']));
$lfy = cs_read('lfy_ids.json', null);   // L4U's own pixel / tag ids, saved by the last full run
if ($lfy === null) {   // none yet: learn them now (one monday read, ~3 min)
    $run = ['tasks' => array_values(array_filter(cs_monday_tasks(), fn($t) => $t['kind'] !== 'rejoin')), 'raw' => []];
    while (cs_monday_page($run));
    $lfy = cs_learn_lfy_ids(cs_live_domains($run['raw']['projects'] ?? [], $run['raw']['subs'] ?? []));
    cs_write('lfy_ids.json', $lfy);
}
foreach (array_chunk($scan, CS_CHUNK) as $c) foreach (cs_scan_chunk($c, $lfy) as $r) $cur[$r['id']] = $r;
cs_write('current.json', cs_shared_ids(array_values($cur)));
echo count($done), " looked up, {$usage[$month]} / " . CS_PLACES_MONTHLY . " used in $month\n";
