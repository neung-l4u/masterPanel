<?php
// Competitor scan: which website builder / ordering / booking system every shop on the monday Projects boards
// uses today, current and cancelled customers alike. Read-only: monday API + public web pages.
//
//   php competitor_scan.php            — all shops → config/competitor_scan_YYYY-MM-DD.json (+ .csv)
//   php competitor_scan.php host1 …    — only these hosts, JSON to stdout (quick test)
//   php competitor_scan.php --list     — read monday only, print the shops and domains found (no web requests)
//
// Run on the production server: shops still hosted on A2 show a bot-check page to the office IP.
date_default_timezone_set('Asia/Bangkok');
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('This script runs from the command line only.'); }
set_time_limit(0);
ini_set('memory_limit', '1024M');

$CFG = require __DIR__ . '/../../config/domain_report.php';
const BOARDS = ['AU' => '1943203246', 'NZ' => '1943203264', 'UK' => '1943203305', 'US' => '1940392927', 'TH' => '1943203205', 'CA' => '1943203287'];
const COLS = ['stage3', 'color1', 'domain_name', 'website', 'text67'];   // Stage, Shop type, domain / website / other links
require_once __DIR__ . '/../../modules/competitorScan/models/detect.php';   // SYSTEMS, PARKED, host_of, action_hosts, systems_in, fetch_all
function http_json_post($url, $body, $headers) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => 1, CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $body, CURLOPT_HTTPHEADER => $headers, CURLOPT_TIMEOUT => 120]);
    $r = json_decode((string) curl_exec($ch), true);
    return $r;
}
function monday($token, $q) {
    for ($i = 0; $i < 3; $i++) {
        $r = http_json_post('https://api.monday.com/v2', json_encode(['query' => $q]), ['Content-Type: application/json', "Authorization: $token", 'API-Version: 2024-10']);
        if (isset($r['data']) && empty($r['errors'])) return $r['data'];
        sleep(5);
    }
    throw new RuntimeException('monday API failed');
}

// ---- shops
$listOnly = in_array('--list', $argv, true);
$only = array_values(array_diff(array_slice($argv, 1), ['--list']));
$shops = [];
if ($only) {
    foreach ($only as $h) $shops[] = ['country' => '', 'project' => $h, 'stage' => '', 'type' => '', 'domain' => host_of($h), 'monday_links' => []];
} else {
    $cv = 'id name column_values(ids: ' . json_encode(COLS) . ') { id text }';
    foreach (BOARDS as $cc => $board) {
        $page = monday($CFG['monday_token'], "{ boards(ids:[$board]) { items_page(limit:500) { cursor items { $cv } } } }")['boards'][0]['items_page'];
        $items = $page['items'];
        while (!empty($page['cursor'])) {
            $page = monday($CFG['monday_token'], '{ next_items_page(limit:500, cursor:"' . $page['cursor'] . "\") { cursor items { $cv } } }")['next_items_page'];
            $items = array_merge($items, $page['items']);
        }
        foreach ($items as $it) {
            $v = array_column($it['column_values'], 'text', 'id');
            if (($v['stage3'] ?? '') === 'Test Data') continue;
            $text = implode(' ', [$v['domain_name'] ?? '', $v['website'] ?? '', $v['text67'] ?? '']);
            $domain = '';
            foreach (preg_split('/[\s,;|]+/', $text) as $tok) {
                $h = host_of($tok);
                if (preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $h) && !systems_in($h) && !preg_match('/facebook|instagram|google|tiktok|yelp|localforyou\.com$|linktr\.ee/', $h)) { $domain = $h; break; }
            }
            $shops[] = ['country' => $cc, 'project' => $it['name'], 'stage' => $v['stage3'] ?? '', 'type' => $v['color1'] ?? '',
                'domain' => $domain, 'monday_links' => systems_in($text)];
        }
    }
}
fwrite(STDERR, count($shops) . " shops\n");
if ($listOnly) {
    foreach ($shops as $x) echo implode(' | ', [$x['country'], $x['stage'], $x['type'], $x['domain'] ?: '-', json_encode($x['monday_links']), $x['project']]), "\n";
    exit;
}

// ---- pass 1: home pages
$home = fetch_all(array_map(fn($s) => "https://{$s['domain']}/", array_filter($shops, fn($s) => $s['domain'] !== '')));
// no answer on https: try plain http (GloriaFood-hosted sites answer on http only)
$retry = [];
foreach ($home as $u => $r) if ($r[0] === 0) $retry[] = 'http://' . substr($u, 8);
foreach (fetch_all($retry) as $u => $r) if ($r[0] !== 0) $home['https://' . substr($u, 7)] = $r;
// ---- pass 2: booking / ordering / menu pages linked from the home page (same site, up to 3 per shop)
$sub = [];
foreach ($shops as $i => $s) {
    if ($s['domain'] === '' || ($home["https://{$s['domain']}/"][0] ?? 0) !== 200) continue;
    [, $final, $body] = $home["https://{$s['domain']}/"];
    $base = host_of($final);
    preg_match_all('#href=["\']([^"\'\#]+)["\']#i', $body, $m);
    $pick = [];
    foreach ($m[1] as $href) {
        if (!preg_match('#book|order|menu|reserv|appointment|online#i', $href)) continue;
        $abs = preg_match('#^https?://#i', $href) ? $href : 'https://' . $base . '/' . ltrim($href, '/');
        if (host_of($abs) !== $base) continue;   // other hosts are already caught by the pattern on the home page
        $pick[$abs] = 1;
        if (count($pick) >= 3) break;
    }
    $shops[$i]['subpages'] = array_keys($pick);
    $sub = array_merge($sub, array_keys($pick));
}
$subRes = $sub ? fetch_all($sub) : [];

// ---- classify
foreach ($shops as &$s) {
    $s['http'] = 0; $s['final_url'] = ''; $s['site_status'] = 'No domain in monday'; $s['found'] = [];
    if ($s['domain'] !== '') {
        [$code, $final, $body] = $home["https://{$s['domain']}/"] ?? [0, '', ''];
        $s['http'] = $code; $s['final_url'] = $final;
        $low = strtolower($body);
        $raw = $body;
        foreach ($s['subpages'] ?? [] as $u) { $low .= ' ' . strtolower($subRes[$u][2] ?? ''); $raw .= ' ' . ($subRes[$u][2] ?? ''); }
        if ($code === 0) $s['site_status'] = 'Not reachable (DNS / timeout)';
        elseif (preg_match('/one moment, please|just a moment\.\.\.|cf-chl-/', $low)) $s['site_status'] = 'Bot check (not checked)';
        elseif (preg_match(PARKED, $low)) $s['site_status'] = 'Parked / for sale';
        elseif ($code >= 400) $s['site_status'] = "HTTP $code";
        else {
            $s['site_status'] = 'Online';
            $s['found'] = systems_in($low . ' ' . strtolower($final));
            $s['action_hosts'] = action_hosts($raw, host_of($final));
            if (empty($s['found']['builder']) && strpos($low, '/wp-content/') !== false) $s['found']['builder'] = ['WordPress'];
            // redirected to a competitor's own page (e.g. domain → fresha.com/…)
            if ($final !== '' && host_of($final) !== $s['domain'] && systems_in(host_of($final))) $s['site_status'] = 'Redirects to ' . host_of($final);
        }
    }
    unset($s['subpages']);
}
unset($s);

$date = date('Y-m-d');
if ($only) { echo json_encode($shops, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), "\n"; exit; }
$dir = __DIR__ . '/../../config';
file_put_contents("$dir/competitor_scan_$date.json", json_encode(['generated_at' => date('c'), 'shops' => $shops], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
$f = fopen("$dir/competitor_scan_$date.csv", 'w');
fputcsv($f, ['Country', 'Project', 'Stage', 'Shop type', 'Domain', 'Site status', 'HTTP', 'Website builder', 'Ordering', 'Booking', 'L4U systems on site', 'Competitors linked in monday', 'Order / book buttons link to'], ',', '"', '');
foreach ($shops as $s) {
    $j = fn($g) => implode(' / ', $s['found'][$g] ?? []);
    fputcsv($f, [$s['country'], $s['project'], $s['stage'], $s['type'], $s['domain'], $s['site_status'], $s['http'], $j('builder'), $j('ordering'), $j('booking'), $j('ours'),
        implode(' / ', array_merge($s['monday_links']['ordering'] ?? [], $s['monday_links']['booking'] ?? [], $s['monday_links']['builder'] ?? [])),
        implode(' / ', $s['action_hosts'] ?? [])], ',', '"', '');
}
fclose($f);
$st = array_count_values(array_column($shops, 'site_status'));
arsort($st);
fwrite(STDERR, json_encode($st, JSON_UNESCAPED_UNICODE) . "\nconfig/competitor_scan_$date.json + .csv\n");
