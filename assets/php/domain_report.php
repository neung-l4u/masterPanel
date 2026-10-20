<?php
// Domain & Website Report bot.
//
//   php domain_report.php scan   — 00:00 Bangkok: collect monday + Dreamscape + websiteList,
//                                  scan every customer site, write the Google Sheets and the
//                                  06:00 message (stored in daily_summary).
//   php domain_report.php send   — 06:00 Bangkok: post the latest message to Google Chat.
//
// Settings and secrets live in /config/domain_report.php (gitignored, copy to the server by hand).
date_default_timezone_set('Asia/Bangkok');
error_reporting(E_ERROR | E_PARSE);
set_time_limit(0);
ini_set('memory_limit', '1024M');

if (PHP_SAPI !== 'cli') { http_response_code(403); exit('This script runs from cron only.'); }

$mode = $argv[1] ?? '';
if (!in_array($mode, ['scan', 'send', 'dashboard'], true)) exit("usage: php domain_report.php scan|send|dashboard\n");

// --at=HH: run only when it is HH o'clock in Bangkok. Cron calls the script every hour, so the schedule
// does not depend on the server's timezone or its daylight-saving changes.
// Scheduled runs also happen at most once a day, so a cron line set to every minute cannot post the
// report sixty times. Manual runs (no --at) are never blocked.
// --dry-run: do everything but write nothing to the sheets; print the 06:00 message instead.
define('DRY_RUN', in_array('--dry-run', $argv, true));
$scheduled = false;
foreach ($argv as $a) if (preg_match('/^--at=(\d{1,2})$/', $a, $m)) {
    if ((int) date('G') !== (int) $m[1]) exit(0);
    $scheduled = true;
}
$doneFile = __DIR__ . "/../../config/domain_report_last_$mode.txt";
if ($scheduled && @trim(file_get_contents($doneFile)) === date('Y-m-d')) exit(0);

$lock = @fopen(sys_get_temp_dir() . "/domain_report_$mode.lock", 'c');
if ($lock && !flock($lock, LOCK_EX | LOCK_NB)) exit(0);   // previous run of the same mode still going

$CFG = require __DIR__ . '/../../config/domain_report.php';
define('WHOIS_CACHE', __DIR__ . '/../../config/domain_report_whois_cache.json');
define('WHOIS_MAX_AGE_DAYS', 7);   // domains outside Dreamscape are re-checked weekly; registries rate-limit
define('EXPIRY_WARN_DAYS', 10);
const DASHBOARD_TAB = 'dashboard';
const CURRENT_STAGES = ['Completed', 'Cancellation Req', 'Pause', 'Suspend'];
const MONDAY_BOARDS = ['AU' => '1943203246', 'NZ' => '1943203264', 'UK' => '1943203305', 'US' => '1940392927', 'TH' => '1943203205', 'CA' => '1943203287'];
const MONDAY_COLS = ['stage3', 'color1', 'domain_name', 'website', 'text67'];
const PIPELINE_BOARD = '5029892180';   // IT : Website Tasks (New)
const PIPELINE_COLS = ['status' => 'color_mm58m4mk', 'due' => 'date_mm58d58r', 'qt' => 'boolean_mm58vj6m', 'brief' => 'link_mm58qk0e', 'task' => 'color_mm58jka'];   // Site Status, Due date, IT QT Web, Brief, Status (task)
const PIPELINE_ACTIVE_GROUPS = ['New Request', 'Task Queue', 'Working On It'];
// Links that are not the shop's own domain (social pages, third-party ordering/booking hosts).
const NOT_A_SHOP_DOMAIN = '/facebook|instagram|google|foodbooking|gloriafood|tiktok|yelp|ubereats|doordash|menulog|localforyou\.com$|toasttab|vagaro|squareup|spoton|chowbus|glossgenius|blizzfull|smiledining|acuityscheduling|fresha\.com$|linktr\.ee|wixsite\.com$/';
const CLOUDFLARE_PREFIXES = ['104.16.', '104.17.', '104.18.', '104.19.', '104.20.', '104.21.', '104.22.', '104.23.', '104.24.', '104.25.',
    '104.26.', '104.27.', '172.64.', '172.65.', '172.66.', '172.67.', '172.68.', '172.69.', '172.70.', '172.71.', '188.114.'];

function logmsg($s) { fwrite(STDERR, date('H:i:s') . " $s\n"); }

// ---------------------------------------------------------------- HTTP helpers
function http_json($url, $opts = []) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 120] + $opts);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, json_decode($body, true)];
}

// ---------------------------------------------------------------- Google Sheets
function google_token($keyFile) {
    static $tok = null, $exp = 0;
    if ($tok && time() < $exp - 60) return $tok;
    $k = json_decode(file_get_contents($keyFile), true);
    $b64 = fn($s) => rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
    $now = time();
    $jwt = $b64(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])) . '.' . $b64(json_encode([
        'iss' => $k['client_email'], 'scope' => 'https://www.googleapis.com/auth/spreadsheets',
        'aud' => $k['token_uri'], 'iat' => $now, 'exp' => $now + 3600]));
    openssl_sign($jwt, $sig, $k['private_key'], 'sha256WithRSAEncryption');
    [$code, $r] = http_json($k['token_uri'], [CURLOPT_POST => 1, CURLOPT_POSTFIELDS => http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt . '.' . $b64($sig)])]);
    if (empty($r['access_token'])) throw new RuntimeException("Google token failed (HTTP $code)");
    [$tok, $exp] = [$r['access_token'], $now + (int) $r['expires_in']];
    return $tok;
}

function sheets_call($method, $path, $body = null) {
    global $CFG;
    $opts = [CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . google_token($CFG['google_service_account']), 'Content-Type: application/json']];
    if ($body !== null) $opts[CURLOPT_POSTFIELDS] = json_encode($body, JSON_UNESCAPED_UNICODE);
    [$code, $r] = http_json('https://sheets.googleapis.com/v4/spreadsheets/' . $path, $opts);
    if ($code !== 200) throw new RuntimeException("Sheets $method $path -> HTTP $code " . ($r['error']['message'] ?? ''));
    return $r;
}

function sheet_url($name) { global $CFG; return 'https://docs.google.com/spreadsheets/d/' . $CFG['sheets'][$name] . '/edit'; }

// The tab the bot reads and writes: the first one that is not the chart tab, so 'dashboard' can sit in front.
function first_tab($id) {
    static $cache = [];
    if (!isset($cache[$id])) {
        $tabs = array_column(sheets_call('GET', "$id?fields=sheets.properties(sheetId,title)")['sheets'], 'properties');
        $cache[$id] = current(array_filter($tabs, fn($t) => $t['title'] !== DASHBOARD_TAB)) ?: $tabs[0];
    }
    return $cache[$id];
}

// Replace the whole first tab with $rows (row 0 = header), freeze and bold the header.
function sheet_replace($name, array $rows) {
    global $CFG;
    if (DRY_RUN) { logmsg("dry-run: would write " . (count($rows) - 1) . " rows to $name"); return; }
    $id = $CFG['sheets'][$name]; $tab = first_tab($id);
    $range = rawurlencode("'{$tab['title']}'");
    sheets_call('POST', "$id/values/$range:clear", new stdClass());
    sheets_call('PUT', "$id/values/$range!A1?valueInputOption=RAW", ['values' => $rows]);
    sheets_call('POST', "$id:batchUpdate", ['requests' => [
        ['updateSheetProperties' => ['properties' => ['sheetId' => $tab['sheetId'], 'gridProperties' => ['frozenRowCount' => 1]], 'fields' => 'gridProperties.frozenRowCount']],
        ['repeatCell' => ['range' => ['sheetId' => $tab['sheetId'], 'startRowIndex' => 0, 'endRowIndex' => 1],
            'cell' => ['userEnteredFormat' => ['textFormat' => ['bold' => true]]], 'fields' => 'userEnteredFormat.textFormat.bold']],
    ]]);
}

function sheet_read($name) {
    global $CFG;
    $id = $CFG['sheets'][$name]; $tab = first_tab($id);
    return sheets_call('GET', "$id/values/" . rawurlencode("'{$tab['title']}'"))['values'] ?? [];
}

// ---------------------------------------------------------------- Sources
function monday_call($token, $q) {
    for ($i = 0; $i < 3; $i++) {
        [$code, $r] = http_json('https://api.monday.com/v2', [CURLOPT_POST => 1, CURLOPT_POSTFIELDS => json_encode(['query' => $q]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', "Authorization: $token", 'API-Version: 2024-10']]);
        if ($code === 200 && empty($r['errors'])) return $r['data'];
        sleep(5);
    }
    throw new RuntimeException('monday API failed');
}

function monday_projects($token) {
    $cv = 'id name column_values(ids: ' . json_encode(MONDAY_COLS) . ') { id text }';
    $call = fn($q) => monday_call($token, $q);
    $out = [];
    foreach (MONDAY_BOARDS as $cc => $board) {
        $page = $call("{ boards(ids:[$board]) { items_page(limit:500) { cursor items { $cv } } } }")['boards'][0]['items_page'];
        $items = $page['items'];
        while (!empty($page['cursor'])) {
            $page = $call('{ next_items_page(limit:500, cursor:"' . $page['cursor'] . "\") { cursor items { $cv } } }")['next_items_page'];
            $items = array_merge($items, $page['items']);
        }
        foreach ($items as $it) {
            $r = ['country' => $cc, 'item_id' => $it['id'], 'project' => $it['name']];
            foreach ($it['column_values'] as $c) $r[$c['id']] = (string) $c['text'];
            $out[] = $r;
        }
    }
    return $out;
}

// Websites not live yet on the IT : Website Tasks board, with how late the draft is.
// Due date = deadline for sending the draft. A draft counts as sent when an update says "draft is now finished" or "Website Draft version"
// (the update also holds logins, so only its date is kept) or the IT QT Web box is ticked.
function monday_pipeline($token) {
    $cv = 'id name group { title } column_values(ids: ' . json_encode(array_values(PIPELINE_COLS)) . ') { id text }';
    $page = monday_call($token, '{ boards(ids:[' . PIPELINE_BOARD . "]) { items_page(limit:500) { cursor items { $cv } } } }")['boards'][0]['items_page'];
    $items = $page['items'];
    while (!empty($page['cursor'])) {
        $page = monday_call($token, '{ next_items_page(limit:500, cursor:"' . $page['cursor'] . "\") { cursor items { $cv } } }")['next_items_page'];
        $items = array_merge($items, $page['items']);
    }
    // A shop can have a second item with the same name (King Thai Massage: one in New Request, one Online in
    // Completion tasks); when any copy is Online the site is live, so the others are not listed.
    $live = [];
    foreach ($items as $it) foreach ($it['column_values'] as $c) if ($c['id'] === PIPELINE_COLS['status'] && $c['text'] === 'Online') $live[$it['name']] = 1;
    $out = [];
    foreach ($items as $it) {
        if (isset($live[$it['name']])) continue;
        $v = array_column($it['column_values'], 'text', 'id');
        $st = (string) $v[PIPELINE_COLS['status']];
        $active = in_array($it['group']['title'], PIPELINE_ACTIVE_GROUPS, true);
        // active groups: anything not live yet (blank = not started); other groups (Completion, Hold): only Draft / Pre Live
        if ((string) $v[PIPELINE_COLS['brief']] === '') continue;   // only website builds (they have a Brief), not edit tasks
        if (!(in_array($st, ['Draft', 'Pre Live'], true) || ($active && in_array($st, ['', 'Wait shop owner'], true)))) continue;
        $out[$it['id']] = ['id' => $it['id'], 'name' => $it['name'], 'group' => $it['group']['title'], 'status' => $st ?: 'Not started',
            'due' => (string) $v[PIPELINE_COLS['due']], 'qt' => $v[PIPELINE_COLS['qt']] !== '', 'task' => (string) $v[PIPELINE_COLS['task']], 'draft_sent' => ''];
    }
    foreach (array_chunk(array_keys($out), 50) as $ids) {
        foreach (monday_call($token, '{ items(ids:[' . implode(',', $ids) . ']) { id updates(limit:100) { created_at text_body } } }')['items'] as $it) {
            foreach ($it['updates'] as $u) if (preg_match('/draft is now finished|website draft version/i', $u['text_body'])) {
                $d = substr($u['created_at'], 0, 10);
                if ($out[$it['id']]['draft_sent'] === '' || $d < $out[$it['id']]['draft_sent']) $out[$it['id']]['draft_sent'] = $d;   // first time it was sent
            }
        }
    }
    return array_values($out);
}

function dreamscape_domains() {
    require_once __DIR__ . '/DreamscapeAPI.php';
    $api = new DreamscapeAPI(); $out = [];
    for ($p = 1; ; $p++) {
        $r = $api->getDomains(['limit' => 100, 'page' => $p]);
        foreach ($r['data']['data'] ?? [] as $d) $out[strtolower($d['domain_name'])] = substr($d['expiry_date'] ?? '', 0, 10);
        if (empty($r['data']['data']) || $p >= ($r['data']['pagination']['total_pages'] ?? 0)) break;
    }
    if (!$out) throw new RuntimeException('Dreamscape returned no domains');
    return $out;
}

function norm_domain($x) {
    $x = strtolower(trim((string) $x));
    if ($x === '' || strpos($x, ' ') !== false || strpos($x, '@') !== false || strpos($x, '.') === false) return '';
    $x = preg_replace('#^[a-z]+://#', '', $x);
    $x = preg_split('#[/?\#:]#', $x)[0];
    $x = rtrim(preg_replace('/^www\./', '', $x), '.');
    if (!preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $x) || preg_match(NOT_A_SHOP_DOMAIN, $x)) return '';
    return $x;
}

// The site we actually host for a websiteList row. Booking sites live on *.localforyoubooking.com while
// wDomain holds the shop's own main domain (often a site we do not host), so scan the booking host instead.
function site_host($w) {
    $wp = host_of($w['wWordpressURL'] ?? '');
    return str_ends_with($wp, '.localforyoubooking.com') ? $wp : host_of($w['wDomain']);
}

function host_of($x) { return preg_replace('/^www\./', '', preg_split('#[/?\#:]#', preg_replace('#^[a-z]+://#', '', strtolower(trim((string) $x))))[0]); }

// ---------------------------------------------------------------- Site scan
// Fetch every host's home page in parallel. Returns host => [http, body, ip].
function scan_sites(array $hosts) {
    $res = []; $queue = array_values($hosts); $mh = curl_multi_init(); $active = [];
    $add = function ($h, $scheme) use ($mh, &$active) {
        $ch = curl_init("$scheme://$h/");
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1, CURLOPT_MAXREDIRS => 5, CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh) Chrome/124', CURLOPT_ENCODING => '']);
        curl_multi_add_handle($mh, $ch); $active[(int) $ch] = [$ch, $h, $scheme];
    };
    while ($queue || $active) {
        while ($queue && count($active) < 20) $add(array_shift($queue), 'https');
        curl_multi_exec($mh, $running); curl_multi_select($mh, 1.0);
        while ($info = curl_multi_info_read($mh)) {
            $ch = $info['handle']; [, $h, $scheme] = $active[(int) $ch]; unset($active[(int) $ch]);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); $body = curl_multi_getcontent($ch);
            curl_multi_remove_handle($mh, $ch); curl_close($ch);
            if ($code === 0 && $scheme === 'https') { $add($h, 'http'); continue; }
            $a = @dns_get_record($h, DNS_A);
            $res[$h] = ['http' => $code, 'body' => (string) $body, 'ip' => $a[0]['ip'] ?? ''];
        }
    }
    curl_multi_close($mh);
    return $res;
}

function page_facts($body) {
    $low = strtolower($body);
    $txt = strtolower(strip_tags(preg_replace('#<(script|style)\b.*?</\1>#si', ' ', $body)));
    $words = count(preg_split('/\s+/u', trim($txt), -1, PREG_SPLIT_NO_EMPTY));
    $wp = strpos($low, '/wp-content/') !== false || strpos($low, '/wp-json/') !== false;
    return [
        // A bot-check page instead of the site: it says nothing about whether the site is up.
        'challenge' => (bool) preg_match('#<title>\s*(One moment, please|Just a moment)#i', $body),
        'holding' => $body !== '' && $words < 120 && (strpos($txt, '404') !== false || strpos($txt, 'not found') !== false),
        'wp' => $wp,
        'gloriafood_site' => !$wp && strpos($low, 'fbgcdn.com') !== false,
        'cloudwaitress_site' => !$wp && strpos($low, 'cloudwaitress.com') !== false,
        'amelia' => strpos($low, 'ameliabooking') !== false,
        'gf_button' => (bool) preg_match('#data-glf-ruid|foodbooking\.com/ordering|/api/(fb|res)/#', $low),
        'cw_button' => (bool) preg_match('#[a-z0-9-]+\.localforyoucarts\.com#', $low),
    ];
}

// ---------------------------------------------------------------- Registrar / expiry for non-Dreamscape domains
function registrable($d) {
    $p = explode('.', $d);
    return (count($p) > 2 && in_array($p[count($p) - 2], ['com', 'net', 'org', 'co', 'ac', 'gov', 'edu', 'id', 'asn'], true))
        ? implode('.', array_slice($p, -3)) : implode('.', array_slice($p, -2));
}

function whois_au($d) {   // auDA publishes the registrar but not the expiry date
    $fp = @fsockopen('whois.auda.org.au', 43, $e, $es, 15);
    if (!$fp) return ['registrar' => '', 'expiry' => '', 'state' => 'unknown'];
    fwrite($fp, "$d\r\n"); $o = ''; while (!feof($fp)) $o .= fgets($fp, 4096); fclose($fp);
    if (preg_match('/^Registrar Name:\s*(.+)$/mi', $o, $m)) return ['registrar' => trim($m[1]), 'expiry' => '', 'state' => 'registered'];
    return ['registrar' => '', 'expiry' => '', 'state' => stripos($o, 'not found') !== false ? 'not found' : 'unknown'];
}

function rdap($d, $boot) {
    $tld = substr(strrchr($d, '.'), 1);
    if (empty($boot[$tld])) return ['registrar' => '', 'expiry' => '', 'state' => 'unknown'];
    for ($i = 0; $i < 3; $i++) {
        [$code, $j] = http_json($boot[$tld] . 'domain/' . $d, [CURLOPT_TIMEOUT => 20, CURLOPT_HTTPHEADER => ['Accept: application/rdap+json']]);
        if ($code === 404) return ['registrar' => '', 'expiry' => '', 'state' => 'not found'];
        if ($code === 429) { sleep(20 * ($i + 1)); continue; }
        if ($code !== 200) break;
        $reg = ''; $exp = '';
        foreach ($j['entities'] ?? [] as $e) if (in_array('registrar', $e['roles'] ?? [], true))
            foreach ($e['vcardArray'][1] ?? [] as $v) if ($v[0] === 'fn') $reg = $v[3];
        foreach ($j['events'] ?? [] as $e) if ($e['eventAction'] === 'expiration') $exp = substr($e['eventDate'], 0, 10);
        return ['registrar' => $reg, 'expiry' => $exp, 'state' => 'registered'];
    }
    return ['registrar' => '', 'expiry' => '', 'state' => 'unknown'];
}

function whois_lookup(array $domains) {
    $cache = is_readable(WHOIS_CACHE) ? json_decode(file_get_contents(WHOIS_CACHE), true) : [];
    $boot = [];
    [, $j] = http_json('https://data.iana.org/rdap/dns.json');
    foreach ($j['services'] ?? [] as [$tlds, $urls]) foreach ($tlds as $t) $boot[$t] = $urls[0];
    $fresh = time() - WHOIS_MAX_AGE_DAYS * 86400; $n = 0;
    foreach ($domains as $d) {
        $c = $cache[$d] ?? null;
        $stale = !$c || $c['checked'] < $fresh || ($c['expiry'] && $c['expiry'] <= date('Y-m-d', strtotime('+' . EXPIRY_WARN_DAYS . ' days')));
        if (!$stale) continue;
        $rd = registrable($d);
        $r = substr($rd, -3) === '.au' ? whois_au($rd) : rdap($rd, $boot);
        $cache[$d] = $r + ['checked' => time()];
        sleep(substr($rd, -3) === '.au' ? 3 : 1);
        if (++$n % 50 === 0) file_put_contents(WHOIS_CACHE, json_encode($cache));
    }
    file_put_contents(WHOIS_CACHE, json_encode($cache));
    logmsg("whois: refreshed $n of " . count($domains));
    return $cache;
}

// ---------------------------------------------------------------- Scan run
function run_scan() {
    global $CFG, $db;
    require_once __DIR__ . '/../db/db.php';
    require_once __DIR__ . '/../db/initDB.php';
    $today = date('Y-m-d');

    logmsg('monday…');      $projects = monday_projects($CFG['monday_token']);
    logmsg('pipeline…');    $pipeline = monday_pipeline($CFG['monday_token']);
    logmsg('dreamscape…');  $ds = dreamscape_domains();
    $servers = []; $serverByIp = []; $serverName = [];
    foreach ($db->query("SELECT svID, svName, svIP FROM L4UServers WHERE svStatus = 1")->fetchAll() as $s) {
        $no = preg_match('/^(\d+):/', $s['svName'], $m) ? (int) $m[1] : $s['svID'];
        $servers[$s['svID']] = $no; if ($s['svIP']) $serverByIp[trim($s['svIP'])] = $no;
        $serverName[$no] = trim(preg_replace('/^\d+:\s*/', '', $s['svName']));   // "1: adayinbkk.com.au" -> "adayinbkk.com.au"
    }
    // Extra IPs a server answers on that L4UServers does not hold (e.g. Server 4 sites resolve to .48, the table has .49).
    foreach ($CFG['server_ip_aliases'] ?? [] as $ip => $no) $serverByIp[$ip] = $no;
    $wl = $db->query("SELECT wID, wProject, wDomain, wWordpressURL, wLiveStatus, wSystemVoucher, wIndustry, svID FROM websiteList WHERE delete_at IS NULL")->fetchAll();

    // customers = current-stage projects with at least one usable domain
    $customers = [];
    foreach ($projects as $p) {
        if (!in_array($p['stage3'], CURRENT_STAGES, true)) continue;
        $doms = array_values(array_unique(array_filter(array_map('norm_domain', [$p['domain_name'], $p['website'], $p['text67']]))));
        $customers[] = $p + ['domains' => $doms];
    }
    $hosts = [];
    foreach ($customers as $c) foreach ($c['domains'] as $d) $hosts[$d] = 1;
    foreach ($wl as $w) if ($w['wLiveStatus'] !== 'Draft' && ($h = site_host($w))) $hosts[$h] = 1;
    logmsg('scanning ' . count($hosts) . ' sites…');
    $scan = scan_sites(array_keys($hosts));
    // One more pass for anything that did not answer 200: a slow or briefly busy site should not be reported offline.
    $retry = array_keys(array_filter($scan, fn($s) => $s['http'] !== 200));
    logmsg('re-checking ' . count($retry) . ' sites…');
    $scan = array_merge($scan, scan_sites($retry));
    $facts = array_map(fn($s) => page_facts($s['body']), $scan);

    $outside = [];
    foreach ($customers as $c) foreach ($c['domains'] as $d) if (!isset($ds[$d])) $outside[$d] = 1;
    logmsg('whois for ' . count($outside) . ' domains outside Dreamscape…');
    $who = whois_lookup(array_keys($outside));

    $wlByHost = [];
    foreach ($wl as $w) if ($h = site_host($w)) $wlByHost[$h] = $w;

    // ---- one primary domain per customer
    $rows = [];
    foreach ($customers as $c) {
        if (!$c['domains']) continue;
        usort($c['domains'], function ($a, $b) use ($scan, $facts, $ds) {
            $s = fn($d) => (($scan[$d]['http'] ?? 0) === 200) * 4 + isset($ds[$d]) * 2 + !empty($facts[$d]['wp']);
            return $s($b) <=> $s($a);
        });
        $d = $c['domains'][0]; $f = $facts[$d] ?? []; $s = $scan[$d] ?? ['http' => 0, 'ip' => ''];
        if (isset($ds[$d])) { $reg = 'Dreamscape'; $regName = 'Dreamscape'; $exp = $ds[$d]; $state = 'registered'; }
        else {
            $w = $who[$d] ?? ['registrar' => '', 'expiry' => '', 'state' => 'unknown'];
            $regName = $w['registrar']; $exp = $w['expiry']; $state = $w['state'];
            $reg = preg_match('/godaddy|wild west/i', $regName) ? 'GoDaddy' : 'Other';
        }
        $days = $exp ? (int) floor((strtotime($exp) - strtotime($today)) / 86400) : null;
        $dstate = $state === 'not found' ? 'Not registered' : ($days !== null && $days < 0 ? 'Expired'
                : ($days !== null && $days <= EXPIRY_WARN_DAYS ? "Expires in $days days" : ($state === 'registered' ? 'Active' : 'Unknown')));
        $onA2 = isset($serverByIp[$s['ip']]) || (array_filter(CLOUDFLARE_PREFIXES, fn($p) => strpos($s['ip'], $p) === 0) && isset($wlByHost[$d]) && !empty($f['wp']));
        $system = !empty($f['challenge']) ? 'Not checked (bot protection)' : (!empty($f['gloriafood_site']) ? 'GloriaFood' : (!empty($f['cloudwaitress_site']) ? 'CloudWaitress'
                : (!empty($f['holding']) ? 'Holding 404' : (!empty($f['wp']) ? ($onA2 ? 'WordPress (ours)' : 'WordPress (other host)')
                : ($s['http'] === 200 ? 'Other' : 'Not reachable (HTTP ' . $s['http'] . ')')))));
        $rows[] = $c + ['domain' => $d, 'reg' => $reg, 'reg_name' => $regName, 'expiry' => $exp, 'days' => $days, 'dstate' => $dstate,
            'hosting' => $onA2 ? 'A2' : ($s['ip'] ? 'Elsewhere' : 'No DNS'), 'system' => $system, 'http' => $s['http']];
    }

    // ---- our WordPress (websiteList = sites on A2)
    $wp = [];
    foreach ($wl as $w) {
        $h = site_host($w); $f = $facts[$h] ?? []; $s = $scan[$h] ?? ['http' => 0];
        $ok = $s['http'] === 200 && !empty($f['wp']) && empty($f['holding']);
        $st = $w['wLiveStatus'];
        // A subdomain is told by its name (bankstown.thaiparagon.com.au), not by the websiteList status:
        // branch sites are often saved as "Live", and a few main domains are saved as "Subdomain".
        $sub = $h !== '' && registrable($h) !== $h;
        // Transferred / Redirect sites are no longer hosted by us, so they are listed but not counted.
        // Unpublished sites show a holding 404 on purpose (awaiting payment), so they are not "down".
        $state = in_array($st, ['Draft', 'Pre Live', 'Transferred', 'Redirect', 'Unpublished'], true) ? $st
               : (!empty($f['challenge']) ? 'Not checked' : (in_array($st, ['Live', 'Subdomain'], true) && $ok ? 'Online' : 'Down'));
        $res = $sub && in_array($state, ['Online', 'Down'], true) ? "Subdomain " . strtolower($state) : $state;
        $wp[] = $w + ['host' => $h, 'result' => $res, 'server' => $servers[$w['svID']] ?? '', 'http' => $s['http'],
            'amelia' => !empty($f['amelia']), 'gf' => !empty($f['gf_button']), 'cw' => !empty($f['cw_button']),
            'voucher' => !empty($w['wSystemVoucher'])];
    }

    // ---- counts
    $cnt = fn($arr, $fn) => count(array_filter($arr, $fn));
    $online = array_filter($wp, fn($w) => $w['result'] === 'Online');
    $subOn = array_filter($wp, fn($w) => $w['result'] === 'Subdomain online');
    $byCountry = []; foreach (array_keys(MONDAY_BOARDS) as $cc) $byCountry[$cc] = $cnt($rows, fn($r) => $r['country'] === $cc);
    $n = [
        'domains' => count($rows), 'dreamscape' => $cnt($rows, fn($r) => $r['reg'] === 'Dreamscape'),
        'godaddy' => $cnt($rows, fn($r) => $r['reg'] === 'GoDaddy'), 'other' => $cnt($rows, fn($r) => $r['reg'] === 'Other'),
        'expiring' => $cnt($rows, fn($r) => strpos($r['dstate'], 'Expires') === 0), 'expired' => $cnt($rows, fn($r) => $r['dstate'] === 'Expired'),
        'wp_online' => count($online), 'wp_draft' => $cnt($wp, fn($w) => $w['result'] === 'Draft'), 'wp_prelive' => $cnt($wp, fn($w) => $w['result'] === 'Pre Live'),
        'wp_down' => $cnt($wp, fn($w) => $w['result'] === 'Down'), 'wp_unpublished' => $cnt($wp, fn($w) => $w['result'] === 'Unpublished'),
        'amelia' => $cnt($online, fn($w) => $w['amelia']), 'amelia_sub' => $cnt($subOn, fn($w) => $w['amelia']),
        'gf_button' => $cnt($online, fn($w) => $w['gf']), 'cw_button' => $cnt($online, fn($w) => $w['cw']),
        'voucher' => $cnt($online, fn($w) => $w['voucher']),   // from the websiteList flag: no HTML fingerprint for vouchers yet
        'gloriafood' => $cnt($rows, fn($r) => $r['system'] === 'GloriaFood'), 'cloudwaitress' => $cnt($rows, fn($r) => $r['system'] === 'CloudWaitress'),
        'sub_online' => count($subOn), 'sub_offline' => $cnt($wp, fn($w) => $w['result'] === 'Subdomain down'),
        'not_checked' => $cnt($wp, fn($w) => $w['result'] === 'Not checked'),
    ];
    // shop type from websiteList.wIndustry (tb_shopType: 1 Restaurant, 2 Massage), over the sites counted in WORDPRESS
    $counted = array_filter($wp, fn($w) => !in_array($w['result'], ['Transferred', 'Redirect'], true));
    $n['type_restaurant'] = $cnt($counted, fn($w) => (int) $w['wIndustry'] === 1);
    $n['type_massage'] = $cnt($counted, fn($w) => (int) $w['wIndustry'] === 2);
    $n['type_not_set'] = $cnt($counted, fn($w) => empty($w['wIndustry']));
    $n['type_other'] = count($counted) - $n['type_restaurant'] - $n['type_massage'] - $n['type_not_set'];
    // ---- draft deadlines (IT : Website Tasks board): Due date is when the draft must be sent
    $days = fn($from, $to) => (int) round((strtotime($to) - strtotime($from)) / 86400);
    $dm = fn($d) => date(substr($d, 0, 4) === date('Y') ? 'd/m' : 'd/m/y', strtotime($d));   // year only when it is not this year
    // Due date = deadline for the draft, so a site is overdue when the draft was not sent by then:
    // not sent and the date has passed, or sent after the date. Sent drafts are listed by how long they
    // have waited to go live; drafts not sent and not late yet are "upcoming".
    $noDraftLate = []; $sent = []; $upcoming = []; $goingLive = [];
    foreach (sort_by($pipeline, fn($p) => $p['due'] ?: '9999') as $p) {
        $p['name'] = preg_replace('/^WEB-[\d-]*\s*/', '', $p['name']);
        $sentOn = $p['draft_sent'];   // '' when no draft update was found
        $p['late'] = $p['due'] === '' ? 0 : max(0, $days($p['due'], $sentOn ?: $today));
        // the task Status "Queue go to Live" = the shop approved and IT is putting the site live now
        if ($p['task'] === 'Queue go to Live') { $p['waiting'] = $sentOn !== '' ? $days($sentOn, $today) : null; $goingLive[] = $p; }
        elseif ($sentOn !== '' || $p['qt']) { $p['waiting'] = $sentOn !== '' ? $days($sentOn, $today) : null; $sent[] = $p; }
        elseif ($p['late']) $noDraftLate[] = $p;
        else $upcoming[] = $p;
    }
    usort($sent, fn($x, $y) => ($y['waiting'] ?? -1) <=> ($x['waiting'] ?? -1));   // longest wait first
    $overdue = count($noDraftLate) + count(array_filter(array_merge($sent, $goingLive), fn($p) => $p['late'] > 0));
    $n['pipeline'] = count($pipeline); $n['pipeline_overdue'] = $overdue;

    // wp_offline keeps its old meaning (down + unpublished) for the dashboard chart and annual_summary.
    $n['wp_offline'] = $n['wp_down'] + $n['wp_unpublished'];

    // ---- detail sheets
    $updated = 'Updated ' . date('d/m/Y H:i');
    sheet_replace('1_domains', array_merge([['Country', 'Project', 'Stage', 'Domain', 'Other domains in monday', 'monday item', $updated]],
        array_map(fn($r) => [$r['country'], $r['project'], $r['stage3'], $r['domain'], implode(' ', array_slice($r['domains'], 1)), $r['item_id']], $rows)));
    sheet_replace('2_registrar', array_merge([['Registrar group', 'Registrar', 'Domain', 'Project', 'Country', 'Hosting', $updated]],
        array_map(fn($r) => [$r['reg'], $r['reg_name'], $r['domain'], $r['project'], $r['country'], $r['hosting']],
            sort_by($rows, fn($r) => $r['reg'] . $r['domain']))));
    $exp = array_filter($rows, fn($r) => $r['dstate'] === 'Expired' || strpos($r['dstate'], 'Expires') === 0);
    sheet_replace('3_expiry', array_merge([['Status', 'Expiry date', 'Days left', 'Domain', 'Registrar', 'Project', 'Country', $updated]],
        array_map(fn($r) => [$r['dstate'], $r['expiry'], $r['days'], $r['domain'], $r['reg_name'], $r['project'], $r['country']], sort_by($exp, fn($r) => $r['expiry']))));
    sheet_replace('4_wordpress', array_merge([['Result', 'websiteList status', 'Server', 'Domain', 'Project', 'HTTP', 'Amelia', 'GloriaFood button', 'CloudWaitress button', 'Voucher (websiteList)', 'Shop type', 'wID', $updated]],
        array_map(fn($w) => [$w['result'], $w['wLiveStatus'], $w['server'] === '' ? '' : trim('Server ' . $w['server'] . ' ' . ($serverName[$w['server']] ?? '')), $w['host'], $w['wProject'], $w['http'],
            $w['amelia'] ? 'Yes' : '', $w['gf'] ? 'Yes' : '', $w['cw'] ? 'Yes' : '', $w['voucher'] ? 'Yes' : '', [1 => 'Restaurant', 2 => 'Massage'][(int) $w['wIndustry']] ?? (empty($w['wIndustry']) ? 'Not set' : 'Other'), $w['wID']], sort_by($wp, fn($w) => $w['result'] . $w['server'] . $w['host']))));
    $ord = array_filter($rows, fn($r) => in_array($r['system'], ['GloriaFood', 'CloudWaitress'], true));
    sheet_replace('5_ordering', array_merge([['System', 'Domain', 'Project', 'Country', $updated]],
        array_map(fn($r) => [$r['system'], $r['domain'], $r['project'], $r['country']], sort_by($ord, fn($r) => $r['system'] . $r['domain']))));

    // ---- message for 06:00 (Google Chat markup: *bold*, `code`, <url|text>)
    $sheet = sheet_read('daily_summary');
    $prev = [];   // the most recent earlier day, for the ▲/▼ markers
    foreach (array_slice($sheet, 1) as $r) {
        $row = array_combine(array_slice($sheet[0], 0, count($r)), $r);
        if (($row['Date'] ?? '') < $today && ($row['Date'] ?? '') >= ($prev['Date'] ?? '')) $prev = $row;
    }
    $delta = function ($k) use ($n, $prev) {
        if (!isset($prev[$k]) || !is_numeric($prev[$k]) || $n[$k] == $prev[$k]) return '';
        $d = $n[$k] - $prev[$k];
        return ' (' . ($d > 0 ? '▲' : '▼') . abs($d) . ')';
    };
    $view = fn($sheet, $text = 'View') => '<' . sheet_url($sheet) . "|$text>";

    // Layout agreed with the team lead (Oct 2026): numbered sections, "label : value (▲n)" lines.
    $row = fn($emoji, $label, $v, $k = null) => trim("$emoji $label") . " : $v" . ($k ? $delta($k) : '');
    $wpTotal = $n['wp_online'] + $n['wp_down'] + $n['sub_online'] + $n['sub_offline'] + $n['wp_unpublished'] + $n['wp_draft'] + $n['wp_prelive'] + $n['not_checked'];
    $dueTxt = fn($p) => $p['due'] !== '' ? $dm($p['due']) : 'not set';
    $numbered = function (array $items, callable $detail) {
        $out = []; $i = 1;
        foreach ($items as $p) $out[] = ($i++) . ". {$p['name']}\n   • " . $detail($p);
        return $out;
    };
    $sentLines = $numbered($sent, fn($p) => ($p['late'] ? '🔴 ' : '') . "Status : {$p['status']} | Sent : " . ($p['draft_sent'] !== '' ? $dm($p['draft_sent']) : 'IT QT ✓')
        . ' (Due date : ' . $dueTxt($p) . ')' . ($p['late'] ? " | Sent {$p['late']} days late" : '') . ($p['waiting'] !== null ? " | Waiting : {$p['waiting']} days" : ''));
    $lateLines = $numbered($noDraftLate, fn($p) => "Status : {$p['status']} | Not sent (Due date : " . $dueTxt($p) . ") | Over due : {$p['late']} days");
    $liveLines = $numbered($goingLive, fn($p) => "Status : {$p['status']} | Sent : " . ($p['draft_sent'] !== '' ? $dm($p['draft_sent']) : 'IT QT ✓')
        . ' (Due date : ' . $dueTxt($p) . ')' . ($p['waiting'] !== null ? " | Since draft : {$p['waiting']} days" : ''));
    $upcomingLines = $numbered($upcoming, fn($p) => "Status : {$p['status']} (Due date : " . $dueTxt($p) . ')');

    $sections = [
        '📊 *Domain & Website Report* · ' . date('d/m/Y'),
        implode("\n", [
            "*1. Domains (Total : {$n['domains']})*" . $delta('domains') . '  ' . $view('1_domains', 'View'),
            '*Status*',
            $row('🔴', 'Expired', $n['expired'], 'expired'),
            $row('🟠', 'Expires ≤ ' . EXPIRY_WARN_DAYS . ' days', $n['expiring'], 'expiring') . '  ' . $view('3_expiry', 'Expiry list'),
        ]),
        implode("\n", array_merge([
            "*2. WordPress sites (Total : $wpTotal)*  " . $view('4_wordpress', 'View'),
            '*Categorization*',
            $row('🍜', 'Restaurant', $n['type_restaurant'], 'type_restaurant'),
            $row('💆', 'Massage', $n['type_massage'], 'type_massage'),
            $row('❔', 'Not set', $n['type_not_set'], 'type_not_set'),
            $row('🏷️', 'Other', $n['type_other'], 'type_other'),
            '',
            '*Sites status*',
            $row('🟢', 'Live - online', $n['wp_online'], 'wp_online'),
            $row('🔴', 'Live - down', $n['wp_down'], 'wp_down'),
            $row('🔗', 'Subdomain - online', $n['sub_online'], 'sub_online'),
            $row('🔴', 'Subdomain - down', $n['sub_offline'], 'sub_offline'),
            $row('⏸️', 'Unpublished', $n['wp_unpublished'], 'wp_unpublished'),
            $row('📝', 'Draft', $n['wp_draft'], 'wp_draft'),
            $row('🚀', 'Pre Live', $n['wp_prelive'], 'wp_prelive'),
        ], $n['not_checked'] ? [$row('⚠️', 'Not checked (bot-check)', $n['not_checked'])] : [])),
        implode("\n", [
            '*3. Plugin & integrations (status : on live sites)*  ' . $view('4_wordpress', 'View'),
            $row('📅', 'Amelia', $n['amelia'], 'amelia'),
            $row('📅', 'Amelia - subdomain', $n['amelia_sub'], 'amelia_sub'),
            $row('🎟️', 'Voucher', $n['voucher'], 'voucher'),
            $row('🍔', 'GloriaFood button', $n['gf_button'], 'gf_button'),
            $row('🛒', 'CloudWaitress button', $n['cw_button'], 'cw_button'),
            '',
            "( Ordering platform total : GloriaFood {$n['gloriafood']}" . $delta('gloriafood') . " & CloudWaitress {$n['cloudwaitress']}" . $delta('cloudwaitress') . ' )  ' . $view('5_ordering', 'View'),
        ]),
        implode("\n", array_merge(
            ["*4. Draft & Onboard status (Total : {$n['pipeline']})*  <https://local-for-you.monday.com/boards/" . PIPELINE_BOARD . '|View board>',
             "*Status summary :* 🔴 Over due : {$n['pipeline_overdue']}" . $delta('pipeline_overdue') . ' | 🚀 Going live : ' . count($goingLive) . ' | 🟡 Draft sent : ' . count($sent) . ' | ⚪ Upcoming : ' . count($upcoming)],
            $lateLines ? ['', '*Over due - draft not sent (Total : ' . count($lateLines) . ')*'] : [], $lateLines,
            $liveLines ? ['', '*🚀 Going live - in progress (Total : ' . count($liveLines) . ')*'] : [], $liveLines,
            $sentLines ? ['', '*Draft sent - Waiting to go live (Total : ' . count($sentLines) . ')*', '_Sorted by waiting time (from longest to shortest)_'] : [], $sentLines,
            $upcomingLines ? ['', '*Upcoming draft (Total : ' . count($upcomingLines) . ')*'] : [], $upcomingLines,
        )),
        '📈 *' . $view('daily_summary', 'View all (dashboard)') . '*',
    ];
    $message = implode("\n\n", $sections);

    if (DRY_RUN) { echo $message, "\n"; return; }

    // ---- daily_summary: one row per day (re-running the same day replaces that day's row)
    // Columns are never renamed or reordered (the dashboard charts and Data Studio point at them);
    // a new metric is appended after the existing columns and older rows are left blank for it.
    $today_row = ['Date' => $today] + $n;
    foreach ($byCountry as $cc => $v) $today_row["domains_$cc"] = $v;
    $today_row['message'] = $message;
    $hdr = $sheet[0] ?? [];
    foreach (array_keys($today_row) as $k) if (!in_array($k, $hdr, true)) $hdr[] = $k;
    $hist = [];
    foreach (array_slice($sheet, 1) as $r) {
        $row = array_combine(array_slice($sheet[0], 0, count($r)), $r);
        if (($row['Date'] ?? '') === $today) continue;   // re-running the same day replaces that day's row
        $hist[] = array_map(fn($k) => is_numeric($row[$k] ?? '') ? +$row[$k] : ($row[$k] ?? ''), $hdr);
    }
    $hist[] = array_map(fn($k) => $today_row[$k] ?? '', $hdr);
    sheet_replace('daily_summary', array_merge([$hdr], $hist));

    // ---- annual_summary: last snapshot of every month
    $col = array_flip($hdr); $months = [];
    foreach ($hist as $r) $months[substr($r[0], 0, 7)] = $r;
    ksort($months);
    $keys = ['domains', 'dreamscape', 'godaddy', 'other', 'expired', 'wp_online', 'wp_offline', 'gloriafood', 'cloudwaitress', 'gf_button', 'cw_button', 'amelia'];
    sheet_replace('annual_summary', array_merge([array_merge(['Month', 'Snapshot date'], $keys, [$updated])],
        array_map(fn($m, $r) => array_merge([$m, $r[0]], array_map(fn($k) => (int) $r[$col[$k]], $keys)), array_keys($months), $months)));

    logmsg('done: ' . json_encode($n));
}

function sort_by(array $a, callable $key) { usort($a, fn($x, $y) => strcmp((string) $key($x), (string) $key($y))); return $a; }

// ---------------------------------------------------------------- Send run
function run_send() {
    global $CFG;
    $hist = sheet_read('daily_summary');
    $last = end($hist);
    $ok = true;
    // every config key that starts with chat_webhook gets the same report (add a space = add a key, no code change)
    foreach (array_filter(array_keys($CFG), fn($k) => str_starts_with($k, 'chat_webhook')) as $hook) {
        if (empty($CFG[$hook])) { logmsg("chat $hook: not set in config/domain_report.php, skipped"); continue; }
        $col = array_search('message', $hist[0] ?? [], true);
        if (count($hist) < 2 || $last[0] !== date('Y-m-d') || $col === false || empty($last[$col])) {
            // Report what was observed, not why.
            $text = 'Domain report: no scan result for ' . date('d/m/Y') . ' (latest row: ' . ($last[0] ?? 'none') . ')';
        } else {
            $text = $last[$col];
        }
        [$code] = http_json($CFG[$hook], [CURLOPT_POST => 1, CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=UTF-8'],
            CURLOPT_POSTFIELDS => json_encode(['text' => $text], JSON_UNESCAPED_UNICODE)]);
        logmsg("chat $hook HTTP $code");
        $ok = $ok && $code === 200;
    }
    if (!$ok) exit(1);
}

// ---------------------------------------------------------------- Dashboard (one-off: php domain_report.php dashboard)
// Rebuilds a 'dashboard' tab in daily_summary: latest numbers with change vs the previous day, plus charts
// that read the data tab directly, so they grow by themselves as the nightly scan appends rows.
function run_dashboard() {
    global $CFG;
    $id = $CFG['sheets']['daily_summary'];
    $data = first_tab($id); $hdr = sheet_read('daily_summary')[0] ?? [];
    if (!$hdr) throw new RuntimeException('daily_summary is empty — run scan first');
    $c = array_flip($hdr);
    $L = fn($k) => chr(65 + $c[$k]);                       // column letter (A–Z is enough for this sheet)
    $T = "'" . $data['title'] . "'";
    // Rows are one per day with no gaps in Date, so the last row is COUNTA(Date) and the one before it is the previous day.
    $n = "COUNTA({$T}!A:A)";
    $last = fn($k) => "=INDEX({$T}!{$L($k)}:{$L($k)},$n)";
    $prev = fn($k) => "=IF($n<3,\"\",INDEX({$T}!{$L($k)}:{$L($k)},$n-1))";

    // recreate the tab at position 0
    $tabs = array_column(sheets_call('GET', "$id?fields=sheets.properties(sheetId,title)")['sheets'], 'properties');
    $req = [];
    foreach ($tabs as $t) if ($t['title'] === DASHBOARD_TAB) $req[] = ['deleteSheet' => ['sheetId' => $t['sheetId']]];
    $req[] = ['addSheet' => ['properties' => ['title' => DASHBOARD_TAB, 'index' => 0, 'gridProperties' => ['rowCount' => 80, 'columnCount' => 14]]]];
    $r = sheets_call('POST', "$id:batchUpdate", ['requests' => $req]);
    $dash = end($r['replies'])['addSheet']['properties']['sheetId'];

    // numbers block
    $cards = ['domains' => 'Domains', 'wp_online' => 'WordPress online', 'gloriafood' => 'GloriaFood', 'cloudwaitress' => 'CloudWaitress',
              'expiring' => 'Expire in 10 days', 'expired' => 'Expired'];
    $rows = [['Domain & Website Report', '', '', '', '', ''],
             ['="Last scan: "&' . substr($last('Date'), 1), '', '', '', '', ''], array_values($cards),
             array_map($last, array_keys($cards)),
             array_map(fn($k) => "=IF($n<3,\"\",IF(" . substr($last($k), 1) . "=" . substr($prev($k), 1) . ",\"– vs previous day\",TEXT("
                 . substr($last($k), 1) . "-" . substr($prev($k), 1) . ",\"▲0 vs previous day;▼0 vs previous day\")))", array_keys($cards))];
    // helper tables for the latest-day bar/pie charts
    $rows[] = []; $rows[] = ['Country', 'Domains', '', 'Registrar', 'Domains'];
    $countries = array_keys(MONDAY_BOARDS); $regs = ['dreamscape' => 'Dreamscape', 'godaddy' => 'GoDaddy', 'other' => 'Other'];
    $rk = array_keys($regs);
    for ($i = 0; $i < count($countries); $i++)
        $rows[] = [$countries[$i], $last('domains_' . $countries[$i]), '', $rk[$i] ?? '' ? $regs[$rk[$i]] : '', isset($rk[$i]) ? $last($rk[$i]) : ''];
    sheets_call('PUT', "$id/values/" . rawurlencode(DASHBOARD_TAB . '!A1') . '?valueInputOption=USER_ENTERED', ['values' => $rows]);

    $range = fn($r1, $r2, $c1, $c2, $sheet) => ['sheetId' => $sheet, 'startRowIndex' => $r1, 'endRowIndex' => $r2, 'startColumnIndex' => $c1, 'endColumnIndex' => $c2];
    $col = fn($k) => ['sourceRange' => ['sources' => [$range(0, 5000, $c[$k], $c[$k] + 1, $data['sheetId'])]]];
    $rgb = fn($hex) => ['rgbColor' => ['red' => hexdec(substr($hex, 0, 2)) / 255, 'green' => hexdec(substr($hex, 2, 2)) / 255, 'blue' => hexdec(substr($hex, 4, 2)) / 255]];
    // Line over time, with visible points and value labels so it still reads while there are only a few days.
    $line = fn($title, $series) => ['spec' => ['title' => $title, 'basicChart' => ['chartType' => 'LINE', 'legendPosition' => 'BOTTOM_LEGEND', 'headerCount' => 1,
        'domains' => [['domain' => $col('Date')]],
        'series' => array_map(fn($k, $hex) => ['series' => $col($k), 'targetAxis' => 'LEFT_AXIS', 'colorStyle' => $rgb($hex),
            'pointStyle' => ['size' => 7, 'shape' => 'CIRCLE'], 'dataLabel' => ['type' => 'DATA', 'placement' => 'ABOVE']], array_keys($series), $series)]]];
    // Two charts per row, lined up with the six 150px number columns above (left at A, right at D).
    $at = fn($chart, $row, $right) => ['addChart' => ['chart' => $chart + ['position' => ['overlayPosition' => [
        'anchorCell' => ['sheetId' => $dash, 'rowIndex' => $row, 'columnIndex' => $right ? 3 : 0], 'offsetXPixels' => $right ? 5 : 0,
        'widthPixels' => 445, 'heightPixels' => 290]]]]];
    $helperRows = 7 + count($countries);   // header row index 6, data rows 7..
    sheets_call('POST', "$id:batchUpdate", ['requests' => [
        // styling: title, big numbers, deltas
        ['repeatCell' => ['range' => $range(0, 1, 0, 1, $dash), 'cell' => ['userEnteredFormat' => ['textFormat' => ['bold' => true, 'fontSize' => 16]]], 'fields' => 'userEnteredFormat.textFormat']],
        ['repeatCell' => ['range' => $range(2, 3, 0, 6, $dash), 'cell' => ['userEnteredFormat' => ['textFormat' => ['bold' => true, 'foregroundColor' => ['red' => .4, 'green' => .4, 'blue' => .45]], 'horizontalAlignment' => 'CENTER']], 'fields' => 'userEnteredFormat(textFormat,horizontalAlignment)']],
        ['repeatCell' => ['range' => $range(3, 4, 0, 6, $dash), 'cell' => ['userEnteredFormat' => ['textFormat' => ['bold' => true, 'fontSize' => 22], 'horizontalAlignment' => 'CENTER']], 'fields' => 'userEnteredFormat(textFormat,horizontalAlignment)']],
        ['repeatCell' => ['range' => $range(4, 5, 0, 6, $dash), 'cell' => ['userEnteredFormat' => ['horizontalAlignment' => 'CENTER']], 'fields' => 'userEnteredFormat.horizontalAlignment']],
        ['repeatCell' => ['range' => $range(3, 4, 4, 6, $dash), 'cell' => ['userEnteredFormat' => ['textFormat' => ['bold' => true, 'fontSize' => 22, 'foregroundColor' => ['red' => .8, 'green' => .2, 'blue' => .2]], 'horizontalAlignment' => 'CENTER']], 'fields' => 'userEnteredFormat(textFormat,horizontalAlignment)']],
        ['updateDimensionProperties' => ['range' => ['sheetId' => $dash, 'dimension' => 'COLUMNS', 'startIndex' => 0, 'endIndex' => 6], 'properties' => ['pixelSize' => 150], 'fields' => 'pixelSize']],
        ['updateDimensionProperties' => ['range' => ['sheetId' => $dash, 'dimension' => 'ROWS', 'startIndex' => 3, 'endIndex' => 4], 'properties' => ['pixelSize' => 48], 'fields' => 'pixelSize']],
        ['repeatCell' => ['range' => $range(6, 7, 0, 6, $dash), 'cell' => ['userEnteredFormat' => ['textFormat' => ['bold' => true]]], 'fields' => 'userEnteredFormat.textFormat.bold']],
        // charts
        $at($line('GloriaFood vs CloudWaitress', ['gloriafood' => 'E8710A', 'cloudwaitress' => '1A73E8']), 15, false),
        $at($line('WordPress on A2: online / offline', ['wp_online' => '1E8E3E', 'wp_offline' => '9AA0A6']), 15, true),
        $at($line('Domains: expiring / expired', ['expiring' => 'F29900', 'expired' => 'D93025']), 31, false),
        $at(['spec' => ['title' => 'Domains by country (latest)', 'basicChart' => ['chartType' => 'COLUMN', 'legendPosition' => 'NO_LEGEND', 'headerCount' => 1,
            'domains' => [['domain' => ['sourceRange' => ['sources' => [$range(6, $helperRows, 0, 1, $dash)]]]]],
            'series' => [['series' => ['sourceRange' => ['sources' => [$range(6, $helperRows, 1, 2, $dash)]]], 'targetAxis' => 'LEFT_AXIS',
                'colorStyle' => $rgb('1A73E8'), 'dataLabel' => ['type' => 'DATA', 'placement' => 'OUTSIDE_END']]]]]], 31, true),
        $at(['spec' => ['title' => 'Registrar (latest)', 'pieChart' => ['legendPosition' => 'LABELED_LEGEND', 'pieHole' => 0.45,
            'domain' => ['sourceRange' => ['sources' => [$range(6, 10, 3, 4, $dash)]]],
            'series' => ['sourceRange' => ['sources' => [$range(6, 10, 4, 5, $dash)]]]]]], 47, false),
    ]]);
    logmsg('dashboard tab rebuilt');
}

try {
    if ($mode === 'dashboard') run_dashboard();
    else $mode === 'scan' ? run_scan() : run_send();
    if ($scheduled) file_put_contents($doneFile, date('Y-m-d'));   // only reached when the run succeeded
} catch (Throwable $e) {
    logmsg('ERROR: ' . $e->getMessage());
    exit(1);
}
