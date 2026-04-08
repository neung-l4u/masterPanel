<?php
/**
 * Compare Stripe vs Monday.com data for the same date range
 * Usage: compareData.php?account=us&start=2025-11-01&end=2025-12-31
 * Returns detailed comparison: totals, by-month, missing items, value mismatches
 */
header('Content-Type: application/json');
ini_set('memory_limit', '512M');
set_time_limit(300);

require_once __DIR__ . '/../../vendor/autoload.php';
$accounts = require __DIR__ . '/stripe_config.php';

// Monday.com config
$mondayApiUrl = "https://api.monday.com/v2";
$mondayToken = "eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjYxNzI4MTY4NiwiYWFpIjoxMSwidWlkIjo1NzY1NDA2MSwiaWFkIjoiMjAyNi0wMi0wNVQwMjowNDo0NC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MjIxMTY5MjAsInJnbiI6ImFwc2UyIn0.RIk109S-veeBTyvud8wxzCc656ytoFfVMgA5zyfo3sM";
$mondayBoards = [
    'au' => '5026435242',
    'us' => '5026427192',
    'th' => '5026435384'
];
$mondayHeaders = [
    "Content-Type: application/json",
    "Authorization: " . $mondayToken
];

// Column IDs
$COL_ID          = 'text_mm0832vx';
$COL_AMOUNT      = 'text_mm086z4k';
$COL_FEE         = 'text_mm08p503';
$COL_NET         = 'text_mm08ab90';
$COL_CREATED     = 'date_mm086vct';
$COL_TYPE        = 'text_mm08ka8n';
$COL_SOURCE      = 'text_mm08qfz7';
$COL_DESCRIPTION = 'text_mm08js6t';
$COL_REVENUE_TXT = 'text_mm082qz9';
$COL_CURRENCY    = 'text_mm08xpqq';

$columnIds = [$COL_ID, $COL_AMOUNT, $COL_FEE, $COL_NET, $COL_CREATED, $COL_TYPE, $COL_SOURCE, $COL_DESCRIPTION, $COL_REVENUE_TXT, $COL_CURRENCY];
$columnIdsStr = '["' . implode('","', $columnIds) . '"]';

// Params
$account = $_GET['account'] ?? '';
if (!isset($accounts[$account]) || !isset($mondayBoards[$account])) {
    echo json_encode(['error' => 'Invalid account. Use: au, us, th']);
    exit;
}

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
if (!$start || !$end) {
    echo json_encode(['error' => 'Provide start and end dates (YYYY-MM-DD)']);
    exit;
}

$sk = $accounts[$account]['sk'];
$currency = $accounts[$account]['currency'];
$timezone = $accounts[$account]['timezone'] ?? 'Australia/Queensland';
date_default_timezone_set($timezone);

$periodStart = strtotime($start . ' 00:00:00');
$periodEnd = strtotime($end . ' 23:59:59');

function parseNum($v) {
    if (empty($v)) return 0;
    return floatval(preg_replace('/[^0-9.\-]/', '', str_replace('..', '.', $v)));
}

function callMondayAPI($url, $headers, $query) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) return ['error' => $error];
    return json_decode($response, true);
}

// ========== 1. FETCH STRIPE DATA ==========
$stripe = new \Stripe\StripeClient($sk);
$stripeItems = [];

try {
    $txnList = $stripe->balanceTransactions->all([
        'limit' => 100,
        'created' => ['gte' => $periodStart, 'lt' => $periodEnd + 86400]
    ]);
    foreach ($txnList->autoPagingIterator() as $tx) {
        $stripeItems[$tx->id] = [
            'id'           => $tx->id,
            'type'         => $tx->type,
            'amount'       => round($tx->amount / 100, 2),
            'fee'          => round($tx->fee / 100, 2),
            'net'          => round($tx->net / 100, 2),
            'currency'     => strtoupper($tx->currency),
            'created'      => date('Y-m-d', $tx->created),
            'description'  => $tx->description ?? '',
        ];
    }
} catch (\Exception $e) {
    echo json_encode(['error' => 'Stripe error: ' . $e->getMessage()]);
    exit;
}

// ========== 2. FETCH MONDAY DATA ==========
$boardId = $mondayBoards[$account];
$mondayItems = [];

// Fetch all groups, filter for stripe (payout/transaction) groups
$query = 'query {
  boards (ids: ' . $boardId . ') {
    groups {
      id
      title
      items_page (limit: 500) {
        cursor
        items {
          id
          name
          column_values (ids: ' . $columnIdsStr . ') { id text }
        }
      }
    }
  }
}';

$result = callMondayAPI($mondayApiUrl, $mondayHeaders, $query);
if (isset($result['error'])) {
    echo json_encode(['error' => 'Monday API error: ' . $result['error']]);
    exit;
}

$groups = $result['data']['boards'][0]['groups'] ?? [];
foreach ($groups as $group) {
    $gt = strtolower($group['title']);
    // Only stripe groups
    if (strpos($gt, 'payout') === false && strpos($gt, 'transaction') === false) continue;

    $processItems = function($items) use (&$mondayItems, $group, $COL_ID, $COL_AMOUNT, $COL_FEE, $COL_NET, $COL_CREATED, $COL_TYPE, $COL_REVENUE_TXT, $COL_DESCRIPTION, $COL_CURRENCY, $periodStart, $periodEnd) {
        foreach ($items as $item) {
            $cols = [];
            foreach ($item['column_values'] as $cv) {
                $cols[$cv['id']] = $cv['text'] ?? '';
            }
            $created = $cols[$COL_CREATED] ?? '';
            // Filter by date range
            if (!empty($created)) {
                $ts = strtotime($created);
                if ($ts < $periodStart || $ts > $periodEnd) continue;
            } else {
                continue; // skip items without created date
            }
            $txnId = trim($cols[$COL_ID] ?? '');
            $mondayItems[$txnId ?: ('mon_' . $item['id'])] = [
                'monday_id'   => $item['id'],
                'monday_name' => $item['name'],
                'txn_id'      => $txnId,
                'type'        => $cols[$COL_TYPE] ?? '',
                'amount'      => parseNum($cols[$COL_AMOUNT] ?? ''),
                'fee'         => parseNum($cols[$COL_FEE] ?? ''),
                'net'         => parseNum($cols[$COL_NET] ?? ''),
                'revenue'     => parseNum($cols[$COL_REVENUE_TXT] ?? ''),
                'currency'    => $cols[$COL_CURRENCY] ?? '',
                'created'     => $created,
                'description' => $cols[$COL_DESCRIPTION] ?? '',
                'group'       => $group['title'],
            ];
        }
    };

    $processItems($group['items_page']['items'] ?? []);

    // Paginate
    $cursor = $group['items_page']['cursor'] ?? null;
    while ($cursor) {
        $nextQuery = 'query {
  next_items_page (limit: 500, cursor: "' . $cursor . '") {
    cursor
    items {
      id
      name
      column_values (ids: ' . $columnIdsStr . ') { id text }
    }
  }
}';
        $nextResult = callMondayAPI($mondayApiUrl, $mondayHeaders, $nextQuery);
        if (isset($nextResult['error'])) break;
        $nextItems = $nextResult['data']['next_items_page']['items'] ?? [];
        $processItems($nextItems);
        $cursor = $nextResult['data']['next_items_page']['cursor'] ?? null;
    }
}

// ========== 3. COMPARE ==========
$onlyInStripe = [];
$onlyInMonday = [];
$valueMismatch = [];
$matched = [];

// Stripe items → check if in Monday
foreach ($stripeItems as $txnId => $si) {
    if (isset($mondayItems[$txnId])) {
        $mi = $mondayItems[$txnId];
        $diffs = [];
        if (abs($si['amount'] - $mi['amount']) > 0.01) $diffs['amount'] = ['stripe' => $si['amount'], 'monday' => $mi['amount']];
        if (abs($si['fee'] - $mi['fee']) > 0.01) $diffs['fee'] = ['stripe' => $si['fee'], 'monday' => $mi['fee']];
        if (abs($si['net'] - $mi['net']) > 0.01) $diffs['net'] = ['stripe' => $si['net'], 'monday' => $mi['net']];
        if (!empty($diffs)) {
            $valueMismatch[] = [
                'txn_id' => $txnId,
                'type' => $si['type'],
                'created' => $si['created'],
                'diffs' => $diffs,
                'monday_revenue' => $mi['revenue'],
            ];
        } else {
            $matched[] = $txnId;
        }
    } else {
        $onlyInStripe[] = [
            'txn_id' => $txnId,
            'type' => $si['type'],
            'amount' => $si['amount'],
            'fee' => $si['fee'],
            'net' => $si['net'],
            'created' => $si['created'],
            'description' => $si['description'],
        ];
    }
}

// Monday items not matched to any Stripe txn
$mondayMatched = array_merge($matched, array_column($valueMismatch, 'txn_id'));
foreach ($mondayItems as $key => $mi) {
    if (!in_array($key, $mondayMatched) && !isset($stripeItems[$key])) {
        $onlyInMonday[] = [
            'key' => $key,
            'monday_id' => $mi['monday_id'],
            'monday_name' => $mi['monday_name'],
            'txn_id' => $mi['txn_id'],
            'type' => $mi['type'],
            'amount' => $mi['amount'],
            'fee' => $mi['fee'],
            'net' => $mi['net'],
            'revenue' => $mi['revenue'],
            'created' => $mi['created'],
            'group' => $mi['group'],
        ];
    }
}

// ========== 4. TOTALS ==========
$stripeTotals = ['amount' => 0, 'fee' => 0, 'net' => 0, 'revenue' => 0, 'count' => 0, 'charge_count' => 0];
foreach ($stripeItems as $si) {
    $stripeTotals['amount'] += $si['amount'];
    $stripeTotals['fee'] += $si['fee'];
    $stripeTotals['net'] += $si['net'];
    $stripeTotals['count']++;
    if ($si['type'] === 'charge' || $si['type'] === 'payment') {
        $stripeTotals['charge_count']++;
        $stripeTotals['revenue'] += $si['amount'];
    }
}

$mondayTotals = ['amount' => 0, 'fee' => 0, 'net' => 0, 'revenue' => 0, 'count' => 0, 'charge_count' => 0];
foreach ($mondayItems as $mi) {
    $mondayTotals['amount'] += $mi['amount'];
    $mondayTotals['fee'] += $mi['fee'];
    $mondayTotals['net'] += $mi['net'];
    $mondayTotals['revenue'] += $mi['revenue'];
    $mondayTotals['count']++;
    if (strtolower($mi['type']) === 'charge') {
        $mondayTotals['charge_count']++;
    }
}

// Revenue analysis: matched charge items — compare stripe amount vs monday revenue
$matchedRevSamples = [];
$matchedRevStats = ['mon_rev_total' => 0, 'stripe_amt_total' => 0, 'count' => 0,
    'types_with_rev' => [], 'non_charge_rev' => 0];
foreach ($matched as $txnId) {
    $mi = $mondayItems[$txnId];
    $si = $stripeItems[$txnId];
    if ($mi['revenue'] != 0) {
        $matchedRevStats['mon_rev_total'] += $mi['revenue'];
        $matchedRevStats['count']++;
        $t = $si['type'] ?: $mi['type'];
        $matchedRevStats['types_with_rev'][$t] = ($matchedRevStats['types_with_rev'][$t] ?? 0) + 1;
        if ($si['type'] !== 'charge' && $si['type'] !== 'payment') {
            $matchedRevStats['non_charge_rev'] += $mi['revenue'];
        }
        if (count($matchedRevSamples) < 15) {
            $matchedRevSamples[] = [
                'txn_id' => $txnId,
                'stripe_type' => $si['type'],
                'monday_type' => $mi['type'],
                'stripe_amount' => $si['amount'],
                'monday_amount' => $mi['amount'],
                'monday_revenue' => $mi['revenue'],
                'monday_fee' => $mi['fee'],
                'stripe_fee' => $si['fee'],
                'created' => $si['created'],
                'description' => substr($si['description'], 0, 60),
            ];
        }
    }
    $matchedRevStats['stripe_amt_total'] += ($si['type'] === 'charge' || $si['type'] === 'payment') ? $si['amount'] : 0;
}

echo json_encode([
    'account' => strtoupper($account),
    'period' => $start . ' — ' . $end,
    'stripe_totals' => $stripeTotals,
    'monday_totals' => $mondayTotals,
    'diff_totals' => [
        'amount' => round($mondayTotals['amount'] - $stripeTotals['amount'], 2),
        'fee' => round($mondayTotals['fee'] - $stripeTotals['fee'], 2),
        'net' => round($mondayTotals['net'] - $stripeTotals['net'], 2),
        'revenue' => round($mondayTotals['revenue'] - $stripeTotals['revenue'], 2),
        'count' => $mondayTotals['count'] - $stripeTotals['count'],
        'charge_count' => $mondayTotals['charge_count'] - $stripeTotals['charge_count'],
    ],
    'matched_count' => count($matched),
    'value_mismatch_count' => count($valueMismatch),
    'only_in_stripe_count' => count($onlyInStripe),
    'only_in_monday_count' => count($onlyInMonday),
    'matched_rev_stats' => $matchedRevStats,
    'matched_rev_samples' => $matchedRevSamples,
    'value_mismatches' => array_slice($valueMismatch, 0, 20),
    'only_in_stripe' => array_slice($onlyInStripe, 0, 20),
    'only_in_monday' => array_slice($onlyInMonday, 0, 50),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
