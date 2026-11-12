<?php
/**
 * The scan runner. One run = monday → learn L4U's own tracking IDs → scan shops → finish.
 * cs_step() does as much as fits in $budgetSec and saves its place in cache/run.json, so the web page
 * (30s max_execution_time, polls until done) and cron.php (loops) drive the same code.
 *
 * Files in cache/ (gitignored — customer data, and the repo is public):
 *   run.json       the run in progress
 *   current.json   latest result per shop (replaced at the end of each run)
 *   history.jsonl  one line per shop whose result changed, per run
 *   errors.json    shops / sources that failed in the last run
 *
 * Memory: production has a 1G limit for the whole cPanel account, so HTML is reduced to a small
 * result right after it is fetched and never kept.
 */
require_once __DIR__ . '/identify.php';

const CS_CHUNK = 20;          // shops fetched in parallel per chunk
const CS_TIMEOUT = 8;         // per web request; a chunk is at most ~3 rounds of this
const CS_LFY_SAMPLE = 60;     // live customers' sites used to learn L4U's own pixel / GTM / Ads IDs

function cs_cache($f = '') {
    $dir = __DIR__ . '/../cache';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    return $dir . ($f !== '' ? "/$f" : '');
}
function cs_read($f, $default = []) {
    $j = is_file(cs_cache($f)) ? json_decode((string) file_get_contents(cs_cache($f)), true) : null;
    return is_array($j) ? $j : $default;
}
function cs_write($f, $data) {
    file_put_contents(cs_cache($f) . '.tmp', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    rename(cs_cache($f) . '.tmp', cs_cache($f));   // readers never see half a file
}

/** Public progress view of the run (or of the last finished one). */
function cs_status() {
    $run = cs_read('run.json', null);
    $meta = cs_read('meta.json');
    if (!$run) return ['running' => false] + $meta;
    $total = count($run['queue'] ?? []);
    return ['running' => true, 'phase' => $run['phase'], 'started_at' => $run['started_at'],
        'loaded' => $run['phase'] === 'scan' ? $run['pos'] : array_sum(array_map('count', $run['raw'] ?? [])),   // monday: items fetched so far
        'total' => $total] + $meta;
}

/**
 * Advance the run. Returns cs_status() plus 'complete' => bool.
 * Two callers at once (a browser poll and cron) are serialised by a lock; the loser just reports status.
 */
function cs_step($budgetSec = 20, $start = false) {
    $lock = fopen(cs_cache('lock'), 'c');
    if (!flock($lock, LOCK_EX | LOCK_NB)) return cs_status() + ['complete' => false, 'busy' => true];
    $deadline = microtime(true) + $budgetSec;

    $run = cs_read('run.json', null);
    if (!$run) {
        if (!$start) { flock($lock, LOCK_UN); return cs_status() + ['complete' => true]; }
        $run = ['phase' => 'monday', 'started_at' => date('c'), 'tasks' => cs_monday_tasks(), 'raw' => [], 'errors' => []];
    }

    try {
        if ($run['phase'] === 'monday') {
            while (microtime(true) < $deadline && cs_monday_page($run));
            if (!array_filter($run['tasks'], fn($t) => !$t['done'])) {
                try { $form = cs_cancellation_rows(); }
                catch (Throwable $e) { $form = []; $run['errors'][] = ['shop' => '', 'name' => 'Cancellation table', 'error' => 'อ่านตาราง Cancellation ไม่ได้ (Reason จะว่าง)']; }
                try {
                    $signups = cs_signup_addons();
                    $labels = cs_addon_labels($signups, cs_price_names(array_merge([], ...array_column($signups, 'codes'))));
                } catch (Throwable $e) { $labels = []; $run['errors'][] = ['shop' => '', 'name' => 'logssignup', 'error' => 'อ่าน add-on จาก logssignup ไม่ได้ (Add-on Only จะไม่มีรายละเอียด)']; }
                $run['queue'] = cs_build_shops($run['raw']['projects'] ?? [], $run['raw']['subs'] ?? [], $run['raw']['rejoin'] ?? [], $form, $labels);
                $run['lfy_domains'] = cs_live_domains($run['raw']['projects'] ?? [], $run['raw']['subs'] ?? []);
                unset($run['raw'], $run['tasks']);
                $run['phase'] = 'lfy'; $run['pos'] = 0; $run['results'] = [];
            }
        }
        if ($run['phase'] === 'lfy' && microtime(true) < $deadline) {
            $run['lfy_ids'] = cs_learn_lfy_ids($run['lfy_domains']);
            $run['phase'] = 'scan';
        }
        while ($run['phase'] === 'scan' && microtime(true) < $deadline) {
            $chunk = array_slice($run['queue'], $run['pos'], CS_CHUNK);
            if (!$chunk) { cs_finish($run); flock($lock, LOCK_UN); return cs_status() + ['complete' => true]; }
            foreach (cs_scan_chunk($chunk, $run['lfy_ids']) as $r) $run['results'][] = $r;
            $run['pos'] += count($chunk);
        }
    } catch (Throwable $e) {
        // monday failing mid-run: keep the place, the next step retries from the same cursor
        $run['last_error'] = $e->getMessage();
    }
    cs_write('run.json', $run);
    flock($lock, LOCK_UN);
    return cs_status() + ['complete' => false, 'error' => $run['last_error'] ?? null];
}

/** Domains of customers who are live today (not cancelled, has a live subscription). */
function cs_live_domains(array $projects, array $subs) {
    $live = [];
    foreach ($subs as $s) if ($s['group'] === 'Active Subscriptions') foreach ($s['v']['connect_boards'] ?? [] as $pid) $live[$pid] = true;
    $d = [];
    foreach ($projects as $p) if (($p['v']['stage3'] ?? '') === 'Completed' && !empty($live[$p['id']]) && ($h = cs_domain_of($p['v'])) !== '') $d[] = $h;
    return array_slice(array_values(array_unique($d)), 0, CS_LFY_SAMPLE);
}

/**
 * IDs that also sit on live L4U customers' sites are ours (left behind on a site we built), so on a
 * cancelled shop they prove nothing about the shop's own marketing.
 */
function cs_learn_lfy_ids(array $domains) {
    $ids = [];
    foreach (fetch_all(array_map(fn($d) => "https://$d/", $domains), CS_CHUNK, CS_TIMEOUT) as [$code, , $body]) {
        if ($code !== 200) continue;
        foreach (cs_tracking_ids($body) as $list) foreach ($list as $id) $ids[$id] = true;
    }
    return array_keys($ids);
}

/** Fetch one chunk of shops (home page, http fallback, up to 3 own order/book/menu pages) and classify. */
function cs_scan_chunk(array $shops, array $lfyIds) {
    $withDomain = array_filter($shops, fn($s) => $s['domain'] !== '');
    $home = fetch_all(array_map(fn($s) => "https://{$s['domain']}/", $withDomain), CS_CHUNK, CS_TIMEOUT);
    $retry = [];
    foreach ($home as $u => $r) if ($r[0] === 0) $retry[] = 'http://' . substr($u, 8);   // GloriaFood-hosted sites answer on http only
    foreach ($retry ? fetch_all($retry, CS_CHUNK, CS_TIMEOUT) : [] as $u => $r) if ($r[0] !== 0) $home['https://' . substr($u, 7)] = $r;

    $subs = []; $js = [];
    foreach ($withDomain as $s) {
        [$code, $final, $body] = $home["https://{$s['domain']}/"] ?? [0, '', ''];
        if ($code !== 200) continue;
        $base = host_of($final);
        preg_match_all('#href=["\']([^"\'\#]+)["\']#i', $body, $m);
        $n = 0;
        foreach ($m[1] as $href) {
            if (!preg_match('#book|order|menu|reserv|appointment|online#i', $href)) continue;
            if (preg_match('#/wp-(?:content|includes)/|\.(?:css|js|png|jpe?g|gif|svg|webp|pdf|woff2?|ico)(?:\?|$)#i', $href)) continue;   // assets, not pages
            $abs = preg_match('#^https?://#i', $href) ? $href : 'https://' . $base . '/' . ltrim($href, '/');
            if (host_of($abs) !== $base) continue;
            $subs[$s['domain']][] = $abs;
            if (++$n >= 3) break;
        }
        // JavaScript-app sites keep the order / booking link in a script: read those when the HTML shows no system
        $f = systems_in(strtolower($body));
        if (empty($f['ordering']) && empty($f['booking']) && empty($f['ours'])) $js[$s['domain']] = cs_app_scripts($body, $final);
    }
    // links found outside the shop's own site (Google listing etc., see cs_apply_links): fetched with the rest
    $known = cs_read('links.json');
    $ext = [];
    foreach ($shops as $s) $ext[$s['id']] = array_slice($known[$s['id']]['urls'] ?? [], 0, 5);
    $more = array_merge(...array_values($subs), ...array_values($js ?? []), ...array_values($ext));
    $subRes = $more ? fetch_all($more, CS_CHUNK, CS_TIMEOUT) : [];

    $out = [];
    foreach ($shops as $s) {
        try {
            $pages = [];
            if ($s['domain'] !== '') {
                $pages[] = $home["https://{$s['domain']}/"] ?? [0, '', ''];
                foreach ($subs[$s['domain']] ?? [] as $u) if (isset($subRes[$u])) $pages[] = $subRes[$u];
            }
            $scripts = [];
            foreach ($js[$s['domain']] ?? [] as $u) if (($subRes[$u][0] ?? 0) === 200) $scripts[] = $subRes[$u][2];
            $r = cs_classify($s, $pages, $lfyIds, $scripts);
            $linkPages = [];
            foreach ($ext[$s['id']] as $u) $linkPages[$u] = $subRes[$u] ?? [0, $u, ''];
            $out[] = $linkPages ? cs_apply_links($r, $linkPages, $known[$s['id']]['phone'] ?? '') : $r;
        } catch (Throwable $e) {
            $out[] = $s + ['site_status' => 'Error', 'error' => $e->getMessage(), 'scanned_at' => date('Y-m-d')];
        }
    }
    return $out;
}

/**
 * From a search's result links, keep those to a known system that belong to this shop: the URL must carry a
 * word of the shop's name ("fresha.com/a/let-s-relax-massage-charmhaven…" yes, "fresha.com/lp/…/au-cairns" no).
 * L4U's own links are kept as they are (an L4U cart link in a shop's results is that shop's).
 */
function cs_links_from_search($shopName, array $urls) {
    $base = strtolower(preg_split('/\s+[-–|]\s+BR\.|\s*\(/u', $shopName)[0]);
    $words = array_filter(preg_split('/[^a-z0-9]+/', $base), fn($w) => strlen($w) >= 4
        && !in_array($w, ['thai', 'restaurant', 'massage', 'kitchen', 'cuisine', 'street', 'food', 'beauty', 'therapy', 'bowls', 'nails'], true));
    $words[] = cs_norm(implode('', array_slice(preg_split('/[^a-z0-9]+/', trim($base)), 0, 2)));   // "Cha Ba Thai" → "chaba"
    $keep = [];
    foreach ($urls as $u) {
        $sys = systems_in(strtolower(host_of($u)));
        if (empty($sys['ordering']) && empty($sys['booking']) && empty($sys['ours'])) continue;
        if (preg_match('#^https?://[^/]+/(?:[a-z]{2}(?:-[a-z]{2})?/)?(?:lp|s|search|places|locations?|near-me)/#i', $u)) continue;   // area listings, not a shop page
        $n = cs_norm($u);
        if (!empty($sys['ours']) || array_filter($words, fn($w) => strlen($w) >= 4 && str_contains($n, $w))) $keep[] = preg_replace('/[?#].*$/', '', $u);
    }
    return array_values(array_unique($keep));
}

/**
 * Use links found outside the shop's own site (its Google listing / search results, cache/links.json) when the
 * site itself showed no system — e.g. a shop whose old domain is dead but whose listing says "Order: smiledining".
 * A known provider link counts as Confirmed only when the page names the shop (name or phone); otherwise Likely.
 * $linkPages: url => [http, final_url, body].
 */
function cs_apply_links(array $r, array $linkPages, $phone = '') {
    $digits = substr(preg_replace('/\D/', '', (string) $phone), -8);
    $words = preg_split('/\s+/', trim(preg_replace('/[^a-z0-9 ]/', ' ', strtolower(preg_split('/\s+[-–|]\s+|\s*\(/u', $r['name'])[0]))));
    $key = cs_norm(implode('', array_slice(array_filter($words, fn($w) => !in_array($w, ['the', 'thai', 'restaurant', 'massage', 'spa', 'and'], true)) ?: $words, 0, 2)));
    $found = []; $verified = false; $urls = [];
    foreach ($linkPages as $u => [$code, $final, $body]) {
        $sys = systems_in(strtolower(host_of($u) . ' ' . host_of($final) . ' ' . ($code === 200 ? $body : '')));
        $names = array_merge($sys['ordering'] ?? [], $sys['booking'] ?? [], array_map(fn($x) => $x . ' — still LFY', $sys['ours'] ?? []));
        if (!$names) continue;
        $found = array_merge($found, $names); $urls[] = $u;
        $text = cs_norm(html_entity_decode(strip_tags($body)) . ' ' . $u);
        if ($code === 200 && (($digits !== '' && str_contains(preg_replace('/\D/', '', $body), $digits)) || (strlen($key) >= 5 && str_contains($text, $key)))) $verified = true;
    }
    if (!$found) return $r;
    $found = array_values(array_unique($found));
    $r['evidence'] = array_values(array_unique(array_merge($r['evidence'], $urls)));
    $r['notes'][] = 'From Google listing: ' . implode(', ', $found) . ($verified ? '' : ' (shop not confirmed on the page)');
    $onlyLfy = $r['system']['providers'] && !array_filter($r['system']['providers'], fn($p) => !str_contains($p, 'still LFY'));
    if (in_array($r['system']['status'], ['Unknown', 'Not Detected', 'Likely'], true) || ($r['system']['status'] === 'Confirmed' && $onlyLfy && array_filter($found, fn($p) => !str_contains($p, 'still LFY'))))
        $r['system'] = ['status' => $verified ? 'Confirmed' : 'Likely', 'providers' => $found];
    elseif ($r['system']['status'] === 'Not in package')
        $r['system']['providers'] = array_values(array_unique(array_merge($r['system']['providers'], $found)));
    return $r;
}

/**
 * Turn fetched pages into the reported result. Status words follow the brief:
 * Confirmed / Likely / Not Detected (looked, found nothing — NOT "stopped") / Unknown (could not look).
 * Parts outside the customer's package are "Not in package".
 */
function cs_classify(array $s, array $pages, array $lfyIds, array $scripts = []) {
    [$code, $final, $body] = $pages[0] ?? [0, '', ''];
    $low = strtolower($body);
    if ($s['domain'] === '') $site = 'No domain in monday';
    elseif ($code === 0) $site = 'Not reachable (DNS / timeout)';
    elseif (preg_match('/one moment, please|just a moment\.\.\.|cf-chl-/', $low)) $site = 'Bot check (not checked)';
    elseif (preg_match(PARKED, $low)) $site = 'Parked / for sale';
    elseif ($code >= 400) $site = "HTTP $code";
    else $site = 'Online';

    $r = $s + ['site_status' => $site, 'http' => $code, 'final_url' => $final, 'evidence' => [], 'builder' => [], 'notes' => [],
        'system' => ['status' => 'Unknown', 'providers' => []], 'marketing' => ['status' => 'Unknown', 'channels' => [], 'ids' => []],
        'agency' => ['status' => 'Unknown', 'name' => '', 'url' => ''], 'scanned_at' => date('Y-m-d')];

    if ($site === 'Online') {
        $all = ''; $raw = '';
        foreach ($pages as [$c, $u, $b]) { if ($c === 200) { $all .= ' ' . strtolower($b); $raw .= ' ' . $b; $r['evidence'][] = $u; } }
        // scripts count for systems and tracking IDs only: their strings are not page text (no credits, no button labels)
        $js = strtolower(implode(' ', $scripts));
        $found = systems_in($all . ' ' . $js . ' ' . strtolower($final));
        $inJs = array_diff(array_merge(...array_values(systems_in($js) ?: [[]])), array_merge(...array_values(systems_in($all) ?: [[]])));
        if ($inJs) $r['notes'][] = 'Found in the site\'s script (behind a button): ' . implode(', ', $inJs);
        $r['builder'] = $found['builder'] ?? [];
        if (!$r['builder'] && cs_is_wordpress($raw)) $r['builder'] = ['WordPress'];
        $finalHost = host_of($final);
        if ($finalHost !== '' && $finalHost !== $s['domain'] && !str_ends_with($finalHost, '.' . $s['domain'])) {
            $r['notes'][] = 'Domain redirects to ' . $finalHost;
            if (systems_in($finalHost)) $found = array_merge_recursive($found, systems_in($finalHost));
        }

        // System: competitor found on the site = Confirmed; still on L4U's = Confirmed "LFY"; only an unknown host behind an order/book button = Likely
        $comp = array_values(array_unique(array_merge($found['ordering'] ?? [], $found['booking'] ?? [])));
        // L4U's own WordPress template ships WooCommerce ("Website Maintained by Local For You"), and massage shops use
        // it to sell vouchers: it is a competitor's ordering system only on a restaurant site L4U did not build
        $lfySite = (bool) preg_match('/local ?for ?you/i', strip_tags($raw));
        if ($lfySite) $r['notes'][] = 'Website still credits Local For You';
        if ($lfySite || stripos($s['type'] ?? '', 'massage') !== false) $comp = array_values(array_diff($comp, ['WooCommerce (self-hosted)']));
        // the shop's own old domain (site moved to a new one) is not a provider
        $hosts = array_values(array_filter(action_hosts($raw, $finalHost),
            fn($h) => $h !== $s['domain'] && !str_ends_with($h, '.' . $s['domain'])));
        if ($comp) $r['system'] = ['status' => 'Confirmed', 'providers' => $comp];
        elseif (!empty($found['ours'])) $r['system'] = ['status' => 'Confirmed', 'providers' => array_map(fn($x) => $x . ' — still LFY', $found['ours'])];
        elseif ($hosts || !empty($found['chat'])) $r['system'] = ['status' => 'Likely', 'providers' => array_merge($hosts, $found['chat'] ?? [])];
        else $r['system'] = ['status' => 'Not Detected', 'providers' => []];
        if ($comp && !empty($found['ours'])) $r['notes'][] = 'LFY system still on site: ' . implode(', ', $found['ours']);

        // Marketing tier 0: a tag / pixel means an ad account is wired up, not that ads run today → Likely at most
        $ids = cs_tracking_ids($raw . ' ' . implode(' ', $scripts));
        foreach ($ids as $k => $list) $ids[$k] = array_values(array_diff($list, $lfyIds));
        $ch = [];
        if ($ids['google_ads']) $ch[] = 'Google Ads';
        if ($ids['meta_pixel']) $ch[] = 'Meta (Facebook / Instagram)';
        $r['marketing'] = ['status' => $ch ? 'Likely' : 'Not Detected', 'channels' => $ch, 'ids' => $ids];

        $credits = cs_agency_credits($raw);
        if ($credits) $r['agency'] = ['status' => 'Confirmed', 'name' => $credits[0]['name'], 'url' => $credits[0]['url']];
        elseif ($p = array_values(array_intersect($r['builder'], CS_AGENCY_PLATFORMS)))
            $r['agency'] = ['status' => 'Likely', 'name' => $p[0] . ' (platform + marketing)', 'url' => ''];
    }
    if (!$s['scope']['system']) $r['system'] = ['status' => 'Not in package', 'providers' => $r['system']['providers']];
    if (!$s['scope']['marketing']) $r['marketing']['status'] = 'Not in package';
    return $r;
}

/** End of run: replace current.json, append changed shops to history.jsonl, write errors and meta. */
function cs_finish(array $run) {
    $prev = [];
    foreach (cs_read('current.json') as $p) $prev[$p['id']] = $p;
    $key = fn($x) => json_encode([$x['site_status'], $x['final_url'] ?? '', $x['system'] ?? [], $x['marketing']['status'] ?? '',
        $x['marketing']['channels'] ?? [], $x['agency'] ?? [], $x['builder'] ?? []]);
    $lines = '';
    $errors = $run['errors'];
    foreach ($run['results'] as $r) {
        if (!isset($prev[$r['id']]) || $key($prev[$r['id']]) !== $key($r)) {
            $lines .= json_encode(['date' => $r['scanned_at'], 'id' => $r['id'], 'site_status' => $r['site_status'], 'final_url' => $r['final_url'] ?? '',
                'system' => $r['system'] ?? null, 'marketing' => $r['marketing'] ?? null, 'agency' => $r['agency'] ?? null,
                'builder' => $r['builder'] ?? [], 'evidence' => $r['evidence'] ?? []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
        }
        if (($r['site_status'] ?? '') === 'Error') $errors[] = ['shop' => $r['id'], 'name' => $r['name'], 'error' => $r['error'] ?? ''];
    }
    cs_write('current.json', $run['results']);
    if ($lines !== '') file_put_contents(cs_cache('history.jsonl'), $lines, FILE_APPEND);
    cs_write('errors.json', $errors);
    cs_write('meta.json', ['last_started_at' => $run['started_at'], 'last_finished_at' => date('c'),
        'shops' => count($run['results']), 'lfy_ids' => count($run['lfy_ids'] ?? [])]);
    @unlink(cs_cache('run.json'));
}

/** All history lines for one shop, oldest first. */
function cs_history($id) {
    $out = [];
    if (!is_file(cs_cache('history.jsonl'))) return $out;
    $fh = fopen(cs_cache('history.jsonl'), 'r');
    while (($line = fgets($fh)) !== false) {
        if (!str_contains($line, '"id":"' . $id . '"')) continue;
        $j = json_decode($line, true);
        if ($j) $out[] = $j;
    }
    fclose($fh);
    return $out;
}
