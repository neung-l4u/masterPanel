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
const PARALLEL = 15;

// name => [group, pattern on the lower-cased HTML or on a URL written in monday]
const SYSTEMS = [
    // ours
    'GloriaFood (L4U)'      => ['ours', 'fbgcdn\.com|foodbooking\.com|data-glf-ruid'],
    'CloudWaitress (L4U)'   => ['ours', 'cloudwaitress\.com|localforyoucarts\.com'],
    'Amelia (L4U)'          => ['ours', 'ameliabooking'],
    // online ordering / POS (restaurants)
    'Toast'                 => ['ordering', 'toasttab\.com'],
    'Square Online'         => ['ordering', 'square\.site|squareup\.com/(?:store|order)|order\.online|weeblysite\.com'],
    'Uber Eats'             => ['ordering', 'ubereats\.com'],
    'DoorDash'              => ['ordering', 'doordash\.com'],
    'Menulog'               => ['ordering', 'menulog\.com\.au'],
    'Deliveroo'             => ['ordering', 'deliveroo\.'],
    'Just Eat'              => ['ordering', 'just-eat\.|justeat\.'],
    'Foodhub'               => ['ordering', 'foodhub\.(?:co\.uk|com)'],
    'Grubhub'               => ['ordering', 'grubhub\.com'],
    'ChowNow'               => ['ordering', 'chownow\.com'],
    'Owner.com'             => ['ordering', 'owner\.com'],
    'Smile Dining'          => ['ordering', 'smiledining\.com'],
    'Chowbus'               => ['ordering', 'chowbus\.com'],
    'SpotOn'                => ['ordering', 'spoton\.com'],
    'Clover'                => ['ordering', 'clover\.com'],
    'me&u / Mr Yum'         => ['ordering', 'meandu\.|mryum\.'],
    'HungryHungry'          => ['ordering', 'hungryhungry\.com'],
    'Bopple'                => ['ordering', 'bopple\.com'],
    'Slice'                 => ['ordering', 'slicelife\.com'],
    'BeyondMenu'            => ['ordering', 'beyondmenu\.com'],
    'Menufy'                => ['ordering', 'menufy\.com'],
    'Blizzfull'             => ['ordering', 'blizzfull\.com'],
    'Easi'                  => ['ordering', 'easi\.com\.au'],
    // booking (massage)
    'Fresha'                => ['booking', 'fresha\.com'],
    'Vagaro'                => ['booking', 'vagaro\.com'],
    'Square Appointments'   => ['booking', 'squareup\.com/appointments|book\.squareup\.com'],
    'Mindbody'              => ['booking', 'mindbodyonline\.com|mindbody\.io'],
    'Booksy'                => ['booking', 'booksy\.com'],
    'Timely'                => ['booking', 'gettimely\.com'],
    'Acuity'                => ['booking', 'acuityscheduling\.com'],
    'Setmore'               => ['booking', 'setmore\.com'],
    'GlossGenius'           => ['booking', 'glossgenius\.com'],
    'Mangomint'             => ['booking', 'mangomint\.com'],
    'Cliniko'               => ['booking', 'cliniko\.com'],
    'SimplyBook.me'         => ['booking', 'simplybook\.(?:me|it|asia)'],
    'Zenoti'                => ['booking', 'zenoti\.com'],
    'Phorest'               => ['booking', 'phorest\.com'],
    'Boulevard'             => ['booking', 'joinblvd\.com|blvd\.co'],
    'Schedulicity'          => ['booking', 'schedulicity\.com'],
    'StyleSeat'             => ['booking', 'styleseat\.com'],
    'Treatwell'             => ['booking', 'treatwell\.'],
    'Jane'                  => ['booking', 'janeapp\.com'],
    'Halaxy'                => ['booking', 'halaxy\.com'],
    'Nookal'                => ['booking', 'nookal\.com'],
    'MassageBook'           => ['booking', 'massagebook\.com'],
    'Booker'                => ['booking', 'secure-booker\.com|booker\.com'],
    'Genbook'               => ['booking', 'genbook\.com'],
    'Calendly'              => ['booking', 'calendly\.com'],
    'Splose'                => ['booking', 'splose\.com'],
    'Wix Bookings'          => ['booking', '/book-online'],   // the Bookings app page; every Wix site mentions "bookings.Wix…" in its code
    // website builders
    'Wix'                   => ['builder', 'wixstatic\.com|wixsite\.com|_wixcss|wix\.com'],
    'Squarespace'           => ['builder', 'squarespace(?:-cdn)?\.com'],
    'GoDaddy Builder'       => ['builder', 'img1\.wsimg\.com|godaddysites\.com'],
    'Shopify'               => ['builder', 'cdn\.shopify\.com'],
    'Webflow'               => ['builder', 'website-files\.com|webflow\.(?:com|io)'],
    'Weebly'                => ['builder', 'editmysite\.com|weebly\.com'],
    'Duda'                  => ['builder', 'multiscreensite\.com|cdn-website\.com|dudaone'],
    'Hostinger Builder'     => ['builder', 'zyrosite\.com|userapp\.zyro'],
    'Google Sites'          => ['builder', 'sites\.google\.com'],
    'Jimdo'                 => ['builder', 'jimdo'],
];
const PARKED = '~domain (?:is )?for sale|buy this domain|parked free|sedoparking|this domain (?:has expired|is parked)|domain parking|hugedomains|dan\.com/|afternic|related searches|links to advertisements|website under redevelopment~';

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
function host_of($x) { return preg_replace('/^www\./', '', preg_split('#[/?\#:]#', preg_replace('#^[a-z]+://#', '', strtolower(trim((string) $x))))[0]); }
// Hosts that order / book / menu buttons point to, so competitors not in SYSTEMS still show up
// (e.g. "View Menu & Order" → onlineorder.enrichpos.com.au). Social, maps and the shop's own site are left out.
function action_hosts($html, $ownHost) {
    $out = [];
    if (!preg_match_all('#<a\b[^>]*href=["\']\s*(https?://[^"\'\s>]+)["\'][^>]*>(.*?)</a>#is', $html, $m, PREG_SET_ORDER)) return [];
    foreach ($m as [, $href, $label]) {
        $text = strtolower($href . ' ' . strip_tags($label));
        if (!preg_match('#order|book|menu|reserv|deliver|appointment|takeaway|take-away|pickup|schedule#', $text)) continue;
        $h = host_of($href);
        if ($h === '' || $h === $ownHost || str_ends_with($h, '.' . $ownHost) || str_ends_with($ownHost, '.' . $h)) continue;
        if (preg_match('#(^|\.)(facebook|instagram|google|goo\.gl|youtube|tiktok|twitter|x\.com|yelp|tripadvisor|linkedin|pinterest|apple\.com|maps|wa\.me|whatsapp|wordpress\.org|elementor|w\.org|gravatar|schema\.org)#', $h)) continue;
        $out[$h] = 1;
    }
    return array_keys($out);
}

function systems_in($text) {
    $found = [];
    foreach (SYSTEMS as $name => [$group, $re]) if (preg_match("#$re#i", $text)) $found[$group][] = $name;
    // Wix Bookings is a Wix feature, Square Appointments also matches Square Online's domain: keep the specific one
    if (in_array('Wix Bookings', $found['booking'] ?? [], true)) $found['builder'] = array_unique(array_merge($found['builder'] ?? [], ['Wix']));
    return $found;
}

// fetch many URLs in parallel: url => [http, final_url, body]
function fetch_all(array $urls) {
    $out = []; $queue = array_values(array_unique($urls)); $mh = curl_multi_init(); $active = [];
    while ($queue || $active) {
        while ($queue && count($active) < PARALLEL) {
            $u = array_shift($queue); $ch = curl_init($u);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1, CURLOPT_MAXREDIRS => 5, CURLOPT_TIMEOUT => 20,
                CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_SSL_VERIFYPEER => 0, CURLOPT_ENCODING => '',
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/124 Safari/537.36']);
            curl_multi_add_handle($mh, $ch); $active[(int) $ch] = [$ch, $u];
        }
        curl_multi_exec($mh, $running); curl_multi_select($mh, 1.0);
        while ($info = curl_multi_info_read($mh)) {
            $ch = $info['handle']; [, $u] = $active[(int) $ch]; unset($active[(int) $ch]);
            $out[$u] = [curl_getinfo($ch, CURLINFO_HTTP_CODE), (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL), substr((string) curl_multi_getcontent($ch), 0, 1500000)];
            curl_multi_remove_handle($mh, $ch); curl_close($ch);
        }
    }
    curl_multi_close($mh);
    return $out;
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
