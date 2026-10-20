<?php
// Read-only malware triage of every WordPress in every cPanel account the WHM reseller tokens can reach.
// After the ClickFix / adminbackup incident (Oct 2026). Our reseller tokens can read files through
// uapi_cpanel but cannot run commands (no cron, no user session), so this only looks at files:
// no database (adminbackup user) and no logs — a site flagged here gets the full l4u-sweep.sh by hand.
//
//   php wp_sweep_all.php                 — every account on every server in whm_tokens.json
//   php wp_sweep_all.php 3               — only svID 3
//   php wp_sweep_all.php 3 user1 user2   — only these accounts (for a quick test)
//
// CSV to config/wp_sweep_YYYY-MM-DD.csv. Nothing is written to the client accounts.
date_default_timezone_set('Asia/Bangkok');
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('This script runs from the command line only.'); }
set_time_limit(0);
require_once __DIR__ . '/whmDiskCheck.php';

// kit signatures (see the l4u-wp-malware-sweep runbook) and the C2 / loader domains
const SIG = '/wp_enqueue_async_script|wp_generate_dynamic_cache|wp_schedule_event_action|_pre_user_id|adminbackup|WORDPRESS_ADMIN_USER|b6d8bc5cf66b_|issuepopdownloadtoday|it-enable\.com|btcmempool\.org'
         // SEO-spam injector posing as "Static ZIP Importer" (harbourthai, Oct 2026)
         . '|szi_home_content|SZI_Persistent|szi-static-pages'
         // code run straight from a request header or a remote URL (harbourthai wp-config.php / wp-load.php, Oct 2026)
         . '|eval\s*\(\s*\$_(SERVER|GET|POST|REQUEST|COOKIE)|eval\s*\(\s*curl_exec|Tiny File Manager/';
// the kit hid its hidden-admin PHP at the top of this stylesheet on chariceatsquare
const STASH = ['wp-content/plugins/elementor/assets/lib/font-awesome/css/font-awesome.min.css'];

[$svOnly, $users] = [$argv[1] ?? '', array_slice($argv, 2)];
$servers = whmServers();
if (!$servers) exit("No whm_tokens.json configured.\n");

$file = __DIR__ . '/../../config/wp_sweep_' . date('Y-m-d') . '.csv';
$out = fopen($file, 'w');
fputcsv($out, ['Result', 'svID', 'cPanel user', 'Domain', 'Docroot', 'Findings', 'Hardening'], ',', '"', '');
$count = ['SUSPECT' => 0, 'Clean' => 0, 'No WordPress' => 0, 'Not reachable' => 0];

foreach ($servers as $svID => $s) {
    if ($svOnly !== '' && $svOnly !== (string) $svID) continue;
    $accts = whm($s, 'listaccts', ['api.version' => 1])['data']['acct'] ?? null;
    if (!is_array($accts)) { fwrite(STDERR, "svID $svID {$s['host']}: listaccts failed\n"); continue; }
    fwrite(STDERR, "svID $svID {$s['host']}: " . count($accts) . " accounts\n");

    foreach ($accts as $n => $a) {
        $u = $a['user'] ?? '';
        if ($u === '' || ($users && !in_array($u, $users, true))) continue;
        fwrite(STDERR, "\r  " . ($n + 1) . '/' . count($accts) . " $u                ");

        $doms = uapi($s, $u, 'DomainInfo', 'domains_data', ['format' => 'list']);
        if (!($doms['status'] ?? 0)) { row($out, $count, 'Not reachable', $svID, $u, $a['domain'] ?? '', '', implode('; ', (array) ($doms['errors'] ?? ['?'])), ''); continue; }
        $roots = [];
        foreach ($doms['data'] ?? [] as $d) if (!empty($d['documentroot'])) $roots[$d['documentroot']] ??= $d['domain'];

        $wp = 0;
        foreach ($roots as $root => $domain) {
            if (!in_array('wp-config.php', names($s, $u, $root), true)) continue;
            $wp++;
            [$find, $hard] = checkSite($s, $u, $root, $a);
            row($out, $count, $find ? 'SUSPECT' : 'Clean', $svID, $u, $domain, $root, implode(' | ', $find), implode(' | ', $hard));
        }
        if (!$wp) row($out, $count, 'No WordPress', $svID, $u, $a['domain'] ?? '', '', '', '');
    }
    fwrite(STDERR, "\n");
}
fwrite(STDERR, json_encode($count) . "\nCSV: config/wp_sweep_" . date('Y-m-d') . ".csv\n");

/** Findings point at the kit; hardening is just the state of wp-config. */
function checkSite(array $s, string $u, string $root, array $acct): array {
    $find = [];
    $plugins = names($s, $u, "$root/wp-content/plugins");
    foreach ($plugins as $p) if (preg_match('/^wp_plugin_\d+$/', $p)) $find[] = "fake plugin $p";

    // read every mu-plugin: the ones we could not read on ferrnspa / byronbay are the suspicious ones
    foreach (names($s, $u, "$root/wp-content/mu-plugins") as $m) {
        if (!str_ends_with($m, '.php')) continue;
        $c = content($s, $u, "$root/wp-content/mu-plugins/$m");
        // A2 Hosting symlinks its own am-bundle.php from /opt/a2-optimized (outside the account, so the API cannot read it)
        if (($c === null || $c === '') && $m !== 'am-bundle.php') $find[] = "mu-plugin $m unreadable (check by hand)";
        elseif (preg_match(SIG, $c, $hit)) $find[] = "mu-plugin $m contains '{$hit[0]}'";
    }

    $files = ['wp-config.php', 'index.php'];
    foreach (STASH as $st) if (in_array(explode('/', $st)[2], $plugins, true)) $files[] = $st;
    $hard = [];
    foreach ($files as $f) {
        $c = content($s, $u, "$root/$f");
        if ($c === null) continue;
        if (preg_match(SIG, $c, $m)) $find[] = "$f contains '{$m[0]}'";
        if (str_ends_with($f, '.css') && str_contains($c, '<?php')) $find[] = "PHP hidden in $f";
        if ($f === 'index.php' && (strlen($c) > 1000 || !str_contains($c, 'wp-blog-header.php'))) $find[] = 'index.php is not the stock WordPress one (check)';
        if ($f === 'wp-config.php') {
            $hard[] = preg_match("/DISALLOW_FILE_EDIT['\"]\s*,\s*true/i", $c) ? 'editor off' : 'editor ON';
            $hard[] = preg_match("/DISALLOW_FILE_MODS['\"]\s*,\s*true/i", $c) ? 'uploads off' : 'uploads ON';
        }
    }

    // the fake plugin's C2 cache (CageFS keeps each account's /tmp under ~/.cagefs/tmp)
    $home = rtrim($acct['partition'] ?? '/home', '/') . '/' . $u;
    foreach (["$home/.cagefs/tmp", "$home/tmp"] as $t)
        foreach (names($s, $u, $t) as $f) if (str_starts_with($f, 'php_sess_')) { $find[] = "C2 cache $t/$f"; break 2; }

    return [$find, $hard];
}

function row($out, array &$count, string $result, ...$cols): void {
    $count[$result]++;
    fputcsv($out, [$result, ...$cols], ',', '"', '');
}

/** File names in a directory; [] when it does not exist. */
function names(array $s, string $u, string $dir): array {
    return array_column(uapi($s, $u, 'Fileman', 'list_files', ['dir' => $dir, 'include_mime' => 0])['data'] ?? [], 'file');
}

function content(array $s, string $u, string $path): ?string {
    $r = uapi($s, $u, 'Fileman', 'get_file_content', ['dir' => dirname($path), 'file' => basename($path)]);
    return ($r['status'] ?? 0) ? (string) ($r['data']['content'] ?? '') : null;
}

function whm(array $s, string $fn, array $params): array {
    $ch = curl_init("https://{$s['host']}:2087/json-api/$fn");
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 60, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($params), CURLOPT_HTTPHEADER => ["Authorization: whm {$s['user']}:{$s['token']}"]]);
    $body = (string) curl_exec($ch);
    curl_close($ch);
    return json_decode($body, true) ?? [];
}

/** cPanel UAPI as $user through the reseller token (uapi_cpanel: the legacy json-api/cpanel gives "Token denied"). */
function uapi(array $s, string $user, string $mod, string $fn, array $p = []): array {
    return whm($s, 'uapi_cpanel', ['api.version' => 1, 'cpanel.user' => $user, 'cpanel.module' => $mod, 'cpanel.function' => $fn] + $p)['data']['uapi'] ?? [];
}
