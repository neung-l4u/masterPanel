<?php
/**
 * Step 1 — Customer Identification: who left, what they had, why.
 *
 * Sources (read-only): monday Projects boards, Active Subscriptions board, "rejoin" tasks,
 * and the Cancellation table (the customer cancellation form).
 * cs_build_shops() is pure so the selection rules can be tested without the network.
 */
require_once __DIR__ . '/detect.php';

const CS_PROJECT_BOARDS = ['AU' => '1943203246', 'NZ' => '1943203264', 'UK' => '1943203305', 'US' => '1940392927', 'TH' => '1943203205', 'CA' => '1943203287'];
const CS_PROJECT_COLS = ['stage3', 'color1', 'domain_name', 'website', 'text67',
    'text', 'marketing_package8', 'text_mm2f4zq5',                 // Initial Product Offering, Marketing Package, Main Products
    'cancel_date', 'date_mm26gw5p', 'date_mm26kg00',               // Cancel Date, System Cancel Date, Marketing Cancel Date
    'color_mks7j57k', 'text_mks7rcqh', 'color_mks76fr6'];          // Cancellation Reason, Reason Details, Cancel Type
const CS_SUBS_BOARD = '1881439330';   // Active Subscriptions (groups: Active Subscriptions / Cancelled Subscription)
const CS_SUBS_COLS = ['status', 'lookup_mkwgjt31', 'date', 'date4', 'connect_boards', 'text_mks62r3g'];
const CS_REJOIN_BOARDS = ['1878448050', '1902864036'];   // Tasks Board, Unsubscription Board
const CS_PAGE = 100;   // 500 per page timed out on the project boards (many columns)

function cs_monday_token() {
    static $t = null;
    if ($t === null) $t = (require __DIR__ . '/../../../config/domain_report.php')['monday_token'] ?? '';
    return $t;
}

function cs_monday($q) {
    for ($i = 0; $i < 3; $i++) {
        $ch = curl_init('https://api.monday.com/v2');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => 1, CURLOPT_POST => 1, CURLOPT_POSTFIELDS => json_encode(['query' => $q]), CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: ' . cs_monday_token(), 'API-Version: 2024-10']]);
        $r = json_decode((string) curl_exec($ch), true);
        if (isset($r['data']) && empty($r['errors'])) return $r['data'];
        sleep(3);
    }
    // the token never goes into the message: this text reaches the browser
    throw new RuntimeException('monday API ไม่ตอบ: ' . substr(json_encode($r['errors'] ?? $r['error_message'] ?? 'no response'), 0, 200));
}

/** One item's column_values as id => text (mirror: display_value, relation: linked ids). */
function cs_cols($item) {
    $v = [];
    foreach ($item['column_values'] as $c) {
        if (array_key_exists('linked_item_ids', $c)) $v[$c['id']] = array_map('strval', $c['linked_item_ids'] ?? []);
        elseif (array_key_exists('display_value', $c)) $v[$c['id']] = (string) $c['display_value'];
        else $v[$c['id']] = (string) ($c['text'] ?? '');
    }
    return $v;
}

/** The fetch plan: one task per board (or board search). Each is paged across steps with its cursor. */
function cs_monday_tasks() {
    $t = [];
    foreach (CS_PROJECT_BOARDS as $cc => $b) $t[] = ['kind' => 'projects', 'cc' => $cc, 'board' => $b, 'cols' => CS_PROJECT_COLS, 'rules' => ''];
    $t[] = ['kind' => 'subs', 'cc' => '', 'board' => CS_SUBS_BOARD, 'cols' => CS_SUBS_COLS, 'rules' => ''];
    foreach (CS_REJOIN_BOARDS as $b) $t[] = ['kind' => 'rejoin', 'cc' => '', 'board' => $b, 'cols' => null,
        'rules' => ', query_params:{rules:[{column_id:"name",compare_value:["rejoin"],operator:contains_text}]}'];
    return array_map(fn($x) => $x + ['cursor' => null, 'started' => false, 'done' => false], $t);
}

/** Fetch one page for the first unfinished task. Returns false when every task is done. */
function cs_monday_page(array &$run) {
    foreach ($run['tasks'] as &$t) {
        if ($t['done']) continue;
        $ids = $t['cols'] === null ? '' : '(ids:' . json_encode($t['cols']) . ')';
        $cv = "id name group { title } column_values$ids { id text ... on BoardRelationValue { linked_item_ids } ... on MirrorValue { display_value } }";
        if (!$t['started']) {
            $p = cs_monday("{ boards(ids:[{$t['board']}]) { items_page(limit:" . CS_PAGE . "{$t['rules']}) { cursor items { $cv } } } }")['boards'][0]['items_page'];
            $t['started'] = true;
        } else {
            $p = cs_monday('{ next_items_page(limit:' . CS_PAGE . ', cursor:"' . $t['cursor'] . "\") { cursor items { $cv } } }")['next_items_page'];
        }
        foreach ($p['items'] as $it) {
            $run['raw'][$t['kind']][] = ['id' => (string) $it['id'], 'cc' => $t['cc'], 'name' => $it['name'],
                'group' => $it['group']['title'] ?? '', 'v' => cs_cols($it)];
        }
        $t['cursor'] = $p['cursor'] ?? null;
        $t['done'] = empty($t['cursor']);
        return true;
    }
    return false;
}

/** Cancellation form rows. A DB failure is reported but does not stop the scan (reasons just stay empty). */
function cs_cancellation_rows() {
    // db::error() calls exit(), which would leave the run stuck on this step forever: make mysqli throw instead
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require_once __DIR__ . '/../../../assets/db/db.php';
    require __DIR__ . '/../../../assets/db/initDB.php';
    return $db->query('SELECT county, shopname, reason, other, lastdate, `timestamp` FROM Cancellation WHERE test = 0')->fetchAll();
}

/**
 * What "Add-on Only" signups actually bought, from logssignup.dataLogs (JSON), latest signup per shop.
 * Newer forms store MainAddons, older ones AddOn1..AddOnN; many only keep the Stripe price / SKU codes
 * (keys of usageAddonDiscountCode, JSON encoded twice), which cs_price_names() turns into product names.
 * Returns country|base name => ['names' => [...], 'codes' => [...]].
 */
function cs_signup_addons() {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require_once __DIR__ . '/../../../assets/db/db.php';
    require __DIR__ . '/../../../assets/db/initDB.php';
    $rows = $db->query("SELECT countryCode, createAt, dataLogs FROM logssignup WHERE test = 0 AND dataLogs LIKE '%Add-on Only%' ORDER BY createAt")->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $d = json_decode($r['dataLogs'], true);
        if (!is_array($d)) continue;
        $names = [];
        foreach ($d as $k => $v) if (preg_match('/^(?:MainAddons|AddOn\d+)$/', $k) && is_string($v))
            foreach (explode(',', $v) as $n) if (trim($n) !== '') $names[] = cs_addon_name($n);
        $codes = json_decode($d['usageAddonDiscountCode'] ?? '{}', true);
        if (is_string($codes)) $codes = json_decode($codes, true);
        $out[strtoupper($r['countryCode']) . '|' . cs_base($d['ShopName'] ?? '')] =   // ORDER BY createAt: the latest signup wins
            ['names' => array_values(array_unique(array_filter($names))), 'codes' => array_keys(is_array($codes) ? $codes : [])];
    }
    return $out;
}

/** "USD - $199 MEN03M00 Menu / Massage Pricing Design" / "Website Hosting - U$ 39.00" → the product name only. Set-up fees are not add-ons. */
function cs_addon_name($n) {
    $n = trim(preg_replace(['/^[A-Z]{3}\s*-\s*\S+\s+[A-Z0-9]{5,}\s+/', '/\s+-\s+[A-Z]{0,3}[$£€฿]\s*[\d.,]+.*$/u'], '', trim($n)));
    return preg_match('/set ?up fee/i', $n) ? '' : $n;
}

/** Stripe price ids / SKU codes → product names, from the Products & Prices board (one API call per 100 codes). */
function cs_price_names(array $codes) {
    $map = [];
    foreach (array_chunk(array_values(array_unique($codes)), 100) as $chunk) {
        $items = cs_monday('{ items_page_by_column_values(board_id:1863122265, limit:100, columns:[{column_id:"text9", column_values:'
            . json_encode($chunk) . '}]) { items { name column_values(ids:["text9"]) { text } } } }')['items_page_by_column_values']['items'];
        foreach ($items as $it) $map[$it['column_values'][0]['text']] = cs_addon_name($it['name']);
    }
    // signup codes are often a variant of a listed price ("AUAI02M00O-22" vs "AUAI02M00S-20"): fall back to the
    // product SKU in the middle ("AI02M00"), which item names carry ("AUD - $249 AI02M00 AI Receptionist")
    $bySku = [];
    foreach ($codes as $c) if (!isset($map[$c]) && preg_match('/^[A-Z]{2}([A-Z]{2,4}\d{2}[A-Z]\d{2})/', $c, $m)) $bySku[$m[1]][] = $c;
    foreach ($bySku as $sku => $list) {
        $items = cs_monday('{ boards(ids:[1863122265]) { items_page(limit:1, query_params:{rules:[{column_id:"name", compare_value:["'
            . $sku . '"], operator:contains_text}]}) { items { name } } } }')['boards'][0]['items_page']['items'];
        if ($items) foreach ($list as $c) $map[$c] = cs_addon_name($items[0]['name']);
    }
    return $map;
}

/** country|base => "Add-on Only (A, B)" label, or nothing when the signup listed no add-on we can name. */
function cs_addon_labels(array $signups, array $priceNames) {
    $out = [];
    foreach ($signups as $k => $s) {
        $names = $s['names'];
        foreach ($s['codes'] as $c) if (($priceNames[$c] ?? '') !== '') $names[] = $priceNames[$c];
        if ($names = array_values(array_unique($names))) $out[$k] = 'Add-on Only (' . implode(', ', $names) . ')';
    }
    return $out;
}

function cs_norm($name) { return preg_replace('/[^a-z0-9]/', '', strtolower((string) $name)); }
/** "Nina Thai Massage - BR.South Melbourne (cancel…)" → "ninathaimassage": the shop without branch / notes */
function cs_base($name) { return cs_norm(preg_split('/\s+[-–|]\s+|\s*\(/u', (string) $name)[0]); }

/** First host in monday's domain / website / links text that is the shop's own site (not social, not a platform). */
function cs_domain_of(array $v) {
    $text = implode(' ', [$v['domain_name'] ?? '', $v['website'] ?? '', $v['text67'] ?? '']);
    foreach (preg_split('/[\s,;|]+/', $text) as $tok) {
        $h = host_of($tok);
        if (preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $h) && !systems_in($h)
            && !preg_match('/facebook|instagram|google|tiktok|yelp|localforyou\.com$|linktr\.ee/', $h)) return $h;
    }
    return '';
}

/**
 * The selection rules. Inputs are the raw rows collected by cs_monday_page() plus the Cancellation table.
 *   lost (full):    project Cancelled and no live subscription linked to it
 *   lost (partial): project still running but has a Marketing or System Cancel Date
 *   excluded:       Test Data, cancel-before-live, a Cancelled project whose shop has another running project
 *   duplicates:     Cancelled rows for the same shop (name + country) collapse to the latest cancellation
 */
function cs_build_shops(array $projects, array $subs, array $rejoins, array $cancelForm, array $addonLabels = []) {
    $cancelledSub = ['Cancelled', 'Cancel Subscription', 'Request Cancellation', 'Cancellation Approved'];
    $live = []; $subsOf = [];
    foreach ($subs as $s) {
        foreach ($s['v']['connect_boards'] ?? [] as $pid) {
            $subsOf[$pid][] = $s;
            if ($s['group'] === 'Active Subscriptions' && !in_array($s['v']['status'] ?? '', $cancelledSub, true)) $live[$pid] = true;
        }
    }
    $rejoined = [];
    foreach ($rejoins as $r) {
        if (preg_match('/due for renewal|unable to rejoin/i', $r['name'])) continue;
        foreach ($r['v'] as $ids) if (is_array($ids)) foreach ($ids as $pid) $rejoined[$pid] = true;
    }
    $form = [];   // country + base name => latest form row
    foreach ($cancelForm as $c) {
        $k = strtoupper(substr(str_replace('Australia', 'AU', (string) $c['county']), 0, 2)) . '|' . cs_base($c['shopname']);
        if (!isset($form[$k]) || $c['timestamp'] > $form[$k]['timestamp']) $form[$k] = $c;
    }
    $running = [];   // shops (country + full name) that still have a non-cancelled project
    foreach ($projects as $p) {
        $st = $p['v']['stage3'] ?? '';
        if ($st !== 'Cancelled' && $st !== 'Test Data') $running[$p['cc'] . '|' . cs_norm($p['name'])] = true;
    }

    $out = [];
    foreach ($projects as $p) {
        $v = $p['v']; $st = $v['stage3'] ?? '';
        if ($st === 'Test Data') continue;
        $beforeLive = preg_match('/before live/i', ($v['color_mks76fr6'] ?? '') . ' ' . ($v['color_mks7j57k'] ?? '') . ' ' . $p['name']);
        if ($beforeLive) continue;

        $partial = '';
        if ($st === 'Cancelled') {
            if (!empty($live[$p['id']]) || isset($running[$p['cc'] . '|' . cs_norm($p['name'])])) continue;
        } else {
            $m = ($v['date_mm26kg00'] ?? '') !== ''; $s = ($v['date_mm26gw5p'] ?? '') !== '';
            if (!$m && !$s) continue;
            $partial = $m && $s ? 'Marketing + System' : ($m ? 'Marketing' : 'System');
        }

        // package: the subscriptions cancelled last (same 31-day window), else the project's own text columns
        $mine = array_values(array_filter($subsOf[$p['id']] ?? [], fn($s) => $s['group'] === 'Cancelled Subscription'
            || in_array($s['v']['status'] ?? '', $cancelledSub, true)));
        $subDate = fn($s) => max($s['v']['date'] ?? '', $s['v']['date4'] ?? '');
        $last = $mine ? max(array_map($subDate, $mine)) : '';
        $names = [];
        foreach ($mine as $s) if ($last === '' || $subDate($s) === '' || strtotime($subDate($s)) >= strtotime($last) - 31 * 86400)
            $names[] = trim(($s['v']['lookup_mkwgjt31'] ?? '') ?: $s['name']);
        if (!$names) foreach (['text_mm2f4zq5', 'text', 'marketing_package8'] as $col) if (trim($v[$col] ?? '') !== '') { $names[] = trim($v[$col]); break; }
        $names = array_values(array_unique($names));
        // "Add-on Only - $0.00" says nothing; name the add-ons from the signup form when we can
        $label = $addonLabels[$p['cc'] . '|' . cs_base($p['name'])] ?? '';
        if ($label !== '') $names = array_map(fn($n) => preg_match('/add-?on only/i', $n) ? $label : $n, $names);

        $kinds = [];
        foreach ($names as $n) $kinds = array_merge($kinds, cs_package_kinds($n));
        $kinds = array_values(array_unique($kinds));
        if ($partial !== '') $scope = ['marketing' => str_contains($partial, 'Marketing'), 'system' => str_contains($partial, 'System')];
        elseif (!$names) $scope = ['marketing' => true, 'system' => true];   // package unknown: check both
        else $scope = ['marketing' => in_array('marketing', $kinds, true), 'system' => in_array('system', $kinds, true)];

        $cancelDate = $partial === 'Marketing' ? ($v['date_mm26kg00'] ?? '') : ($partial === 'System' ? ($v['date_mm26gw5p'] ?? '')
            : max($v['cancel_date'] ?? '', $last, $v['date_mm26kg00'] ?? '', $v['date_mm26gw5p'] ?? ''));

        $reason = trim(($v['color_mks7j57k'] ?? '') . (($v['text_mks7rcqh'] ?? '') !== '' ? ' — ' . $v['text_mks7rcqh'] : ''), ' —');
        $reasonSrc = $reason !== '' ? 'monday' : '';
        $f = $form[$p['cc'] . '|' . cs_base($p['name'])] ?? null;
        if ($reason === '' && $f) {
            $other = trim((string) $f['other']);
            $reason = trim($f['reason'] . ($other !== '' && $other !== 'NULL' ? ' — ' . $other : ''));
            $reasonSrc = 'form';
        }
        if ($reason === '') foreach ($mine as $s) if (trim($s['v']['text_mks62r3g'] ?? '') !== '') { $reason = trim($s['v']['text_mks62r3g']); $reasonSrc = 'subscription'; break; }
        // a part "cancelled" to move to a bigger / new plan is a plan change, not a lost customer (a downgrade still is)
        if ($partial !== '' && preg_match('/upgrade|new package/i', $reason)) continue;

        $out[] = [
            'id' => $p['id'], 'cc' => $p['cc'], 'name' => $p['name'], 'stage' => $st, 'type' => $v['color1'] ?? '',
            'package' => $names ? implode(' / ', $names) : 'Unknown', 'kinds' => $kinds,
            'addon_only' => $names && $kinds === ['addon'], 'scope' => $scope, 'partial' => $partial,
            'cancel_date' => $cancelDate, 'reason' => $reason, 'reason_src' => $reasonSrc,
            'rejoined' => !empty($rejoined[$p['id']]), 'domain' => cs_domain_of($v),
        ];
    }

    // same shop cancelled more than once (re-signed, cancelled again): keep the latest
    $byShop = [];
    foreach ($out as $s) {
        $k = $s['cc'] . '|' . cs_norm($s['name']);
        if (!isset($byShop[$k]) || $s['cancel_date'] > $byShop[$k]['cancel_date']) $byShop[$k] = $s + ['dup_rows' => 1 + ($byShop[$k]['dup_rows'] ?? 0)];
        else $byShop[$k]['dup_rows']++;
    }
    return array_values($byShop);
}
