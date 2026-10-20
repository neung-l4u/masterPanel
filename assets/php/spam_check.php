<?php
// Casino SEO-spam check for every site in websiteList (read-only, public endpoints, no login).
// After the kingmassagetherapy.co.uk incident (Oct 2026): spam posts were published with "casino" in the slug,
// and the attacker logged in as a shared admin account to post them.
// Also loads the home page as a Windows visitor from Google to catch the ClickFix kit from canadathaimassage.com (Oct 2026),
// which only injects its fake "Cloudflare verification" popup for that kind of visitor. Files/backdoors need l4u-sweep.sh on the cPanel.
//
//   php spam_check.php                 — all sites in websiteList, CSV to config/spam_check_YYYY-MM-DD.csv
//   php spam_check.php host1 host2     — only these hosts, CSV to stdout (for a quick test)
//
// Run it on the production server, not from the office: the hosts' bot protection blocks the office IP.
date_default_timezone_set('Asia/Bangkok');
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('This script runs from the command line only.'); }
set_time_limit(0);

// from the spam titles on bangkokflavors (Oct 2026); words like slot/bonus/spins are left out: booking sites use them normally
const KEYWORDS = ['casino', 'pokies', 'gambling', 'roulette', '1win'];
const PARALLEL = 10;
// ClickFix kit IOCs: JS loader, C2, C2 resolver, and the commands the popup tells the visitor to run
const CLICKFIX = '/issuepopdownloadtoday|it-enable\.com|btcmempool\.org|mshta|powershell|x-d6-mode/i';
const WIN_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36';

$only = array_slice($argv, 1);
if ($only) {
    $sites = array_map(fn($h) => ['wID' => '', 'wProject' => '', 'wLiveStatus' => '', 'svID' => '', 'host' => $h], $only);
} else {
    require_once __DIR__ . '/../db/db.php';
    require_once __DIR__ . '/../db/initDB.php';
    global $db;
    $sites = [];
    foreach ($db->query("SELECT wID, wProject, wDomain, wWordpressURL, wLiveStatus, svID FROM websiteList WHERE delete_at IS NULL")->fetchAll() as $w) {
        // the site we host: booking sites live on *.localforyoubooking.com; Draft / Unpublished sites are reached on their
        // WordPress URL (preview or server temp URL) because the shop's own domain is not pointed at us (yet / any more)
        $wp = host_of($w['wWordpressURL']);
        $useWp = $wp !== '' && (str_ends_with($wp, '.localforyoubooking.com') || in_array($w['wLiveStatus'], ['Draft', 'Unpublished'], true));
        $w['host'] = $useWp ? $wp : (host_of($w['wDomain']) ?: $wp);
        if ($w['host'] !== '') $sites[] = $w;
    }
}

// one job per (site, check)
$jobs = [];
foreach ($sites as $i => $s) {
    foreach (KEYWORDS as $k) $jobs[] = [$i, "kw:$k", "https://{$s['host']}/wp-json/wp/v2/search?per_page=20&search=" . urlencode($k)];
    $jobs[] = [$i, 'users', "https://{$s['host']}/wp-json/wp/v2/users?per_page=100&_fields=id,slug,name"];
    // ?d6 makes the kit's fake plugin answer with x-d6-mode/ray/tag headers for every visitor, cloaked or not
    $jobs[] = [$i, 'home', "https://{$s['host']}/?d6=1", [CURLOPT_USERAGENT => WIN_UA, CURLOPT_REFERER => 'https://www.google.com/', CURLOPT_HEADER => 1]];
}

$res = [];
foreach (array_chunk($jobs, PARALLEL) as $batch) {
    $mh = curl_multi_init(); $hs = [];
    foreach ($batch as $j => $job) {
        $ch = curl_init($job[2]);
        curl_setopt_array($ch, ($job[3] ?? []) + [CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1, CURLOPT_MAXREDIRS => 3, CURLOPT_TIMEOUT => 25,
            CURLOPT_SSL_VERIFYPEER => 0, CURLOPT_USERAGENT => 'Mozilla/5.0 (L4U spam check)']);
        curl_multi_add_handle($mh, $ch); $hs[$j] = $ch;
    }
    do { curl_multi_exec($mh, $run); curl_multi_select($mh); } while ($run);
    foreach ($hs as $j => $ch) {
        [$i, $what] = $batch[$j];
        $body = curl_multi_getcontent($ch);
        // keep only the matches: holding ~500 home pages in memory gets the process killed on the shared host
        // spam words on the home page catch hidden link blocks the WP search API never sees (genesis, harbourthai: Oct 2026)
        if ($what === 'home') { preg_match_all(CLICKFIX, (string) $body, $m); $body = [implode(' ', array_unique(array_map('strtolower', $m[0]))), (bool) preg_match('/' . implode('|', KEYWORDS) . '/i', (string) $body)]; }
        $res[$i][$what] = [curl_getinfo($ch, CURLINFO_HTTP_CODE), $body];
        curl_multi_remove_handle($mh, $ch);
    }
    curl_multi_close($mh);
    fwrite(STDERR, "\r" . min(count($res), count($sites)) . '/' . count($sites) . ' sites');
}
fwrite(STDERR, "\n");

$out = $only ? STDOUT : fopen(__DIR__ . '/../../config/spam_check_' . date('Y-m-d') . '.csv', 'w');
fputcsv($out, ['Result', 'wID', 'Project', 'websiteList status', 'svID', 'Host', 'Spam hits', 'Spam URLs', 'Authors shown publicly', 'ClickFix hits', 'Note'], ',', '"', '');
$count = ['CLICKFIX FOUND' => 0, 'SPAM FOUND' => 0, 'Clean' => 0, 'Not checked' => 0];
foreach ($sites as $i => $s) {
    $hits = []; $notes = [];
    foreach (KEYWORDS as $k) {
        [$code, $body] = $res[$i]["kw:$k"];
        $d = json_decode((string) $body, true);
        if ($code === 200 && is_array($d)) { foreach ($d as $r) if (!empty($r['url'])) $hits[$r['url']] = 1; }
        else $notes[] = "search '$k': " . ($code ? "HTTP $code" : 'no response') . (stripos((string) $body, 'one moment') !== false || stripos((string) $body, 'just a moment') !== false ? ' (bot check)' : '');
    }
    [$code, $body] = $res[$i]['users'];
    $u = json_decode((string) $body, true);
    $authors = $code === 200 && is_array($u) ? implode(' | ', array_map(fn($x) => ($x['slug'] ?? '') . (($x['name'] ?? '') !== ($x['slug'] ?? '') ? " ({$x['name']})" : ''), $u)) : "users: HTTP $code";
    $searchFailed = count($notes) === count(KEYWORDS);
    [$code, [$clickfix, $homeSpam]] = $res[$i]['home'];
    if ($homeSpam) $hits["https://{$s['host']}/ (spam words in home page HTML)"] = 1;
    if ($code !== 200) $notes[] = 'home: ' . ($code ? "HTTP $code" : 'no response');
    $result = $clickfix !== '' ? 'CLICKFIX FOUND' : ($hits ? 'SPAM FOUND' : ($searchFailed ? 'Not checked' : 'Clean'));
    $count[$result]++;
    fputcsv($out, [$result, $s['wID'], $s['wProject'], $s['wLiveStatus'], $s['svID'], $s['host'], count($hits), implode(' ', array_keys($hits)), $authors, $clickfix, implode('; ', $notes)], ',', '"', '');
}
fwrite(STDERR, json_encode($count) . ($only ? '' : "\nCSV: config/spam_check_" . date('Y-m-d') . ".csv") . "\n");

function host_of($u) {
    $h = preg_replace('#^[a-z]+://#i', '', strtolower(trim((string) $u)));
    return preg_replace('/^www\./', '', preg_split('#[/?\#:]#', $h)[0]);
}
