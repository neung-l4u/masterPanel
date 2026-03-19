<?php
/**
 * TransmitSMS Data API — Monthly Summary
 * Fetches reseller-level transactions per account (au, us, uk)
 * and groups by month to produce a summary matching the portal's "Clients Summary".
 *
 * Usage: GET ?account=au|us|uk[&refresh=1]
 *
 * Caches results for 5 minutes (300s). Pass refresh=1 to force fresh data.
 */
header('Content-Type: application/json');

ini_set('memory_limit', '256M');
set_time_limit(180);

$accounts = require __DIR__ . '/sms_config.php';

$account = isset($_GET['account']) ? $_GET['account'] : '';
if (!isset($accounts[$account])) {
    echo json_encode(['error' => 'Invalid account. Use: ' . implode(', ', array_keys($accounts))]);
    exit;
}

$forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] === '1';

// Cache
$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
$cacheFile = $cacheDir . "/{$account}_monthly.json";
$cacheTtl = 300; // 5 minutes

if (!$forceRefresh && file_exists($cacheFile)) {
    $cacheAge = time() - filemtime($cacheFile);
    if ($cacheAge <= $cacheTtl) {
        $cached = file_get_contents($cacheFile);
        $data = json_decode($cached, true);
        if (is_array($data)) {
            $data['debug']['source'] = 'cache';
            $data['debug']['cache_age_seconds'] = $cacheAge;
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}

// ===== TransmitSMS API helpers =====
$cfg = $accounts[$account];
$apiKey = $cfg['key'];
$apiSecret = $cfg['secret'];
$baseUrl = 'https://api.transmitsms.com';

function smsApiCall($endpoint, $params = []) {
    global $baseUrl, $apiKey, $apiSecret;
    $url = $baseUrl . '/' . $endpoint . '.json';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_USERPWD, $apiKey . ':' . $apiSecret);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'masterPanel/1.0');
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) return ['error' => ['code' => 'CURL_ERROR', 'description' => $error]];
    $decoded = json_decode($response, true);
    return $decoded ?: ['error' => ['code' => 'PARSE_ERROR', 'description' => 'Invalid JSON', 'http_code' => $httpCode]];
}

function isApiError($resp) {
    if (!is_array($resp)) return true;
    if (isset($resp['error']['code']) && $resp['error']['code'] !== 'SUCCESS') return true;
    return false;
}

function apiErrorMsg($resp) {
    return isset($resp['error']['description']) ? $resp['error']['description'] : 'unknown';
}

/**
 * Fetch all paginated transactions for a client_id within a date range (max 1 year per call).
 */
function fetchTransactions($clientId, $start, $end, $maxPages = 50) {
    $all = [];
    $page = 1;
    while ($page <= $maxPages) {
        $resp = smsApiCall('get-transactions', [
            'client_id' => $clientId,
            'start'     => $start,
            'end'       => $end,
            'page'      => $page,
            'max'       => 100,
        ]);
        if (isApiError($resp)) {
            if ($page === 1) return ['error' => apiErrorMsg($resp)];
            break;
        }
        if (isset($resp['transactions'])) {
            $all = array_merge($all, $resp['transactions']);
        }
        $totalPages = isset($resp['page']['count']) ? intval($resp['page']['count']) : 1;
        if ($page >= $totalPages) break;
        $page++;
    }
    return $all;
}

// ===== START =====
$startTime = microtime(true);
$errors = [];

$result = [
    'account'  => $account,
    'label'    => $cfg['label'],
    'currency' => $cfg['currency'],
    'symbol'   => $cfg['symbol'],
];

// 1. Get Balance
$balResp = smsApiCall('get-balance');
if (isApiError($balResp)) {
    $errors[] = 'balance: ' . apiErrorMsg($balResp);
    $result['balance'] = ['balance' => 0, 'currency' => $cfg['currency']];
} else {
    $result['balance'] = [
        'balance'  => isset($balResp['balance']) ? floatval($balResp['balance']) : 0,
        'currency' => isset($balResp['currency']) ? $balResp['currency'] : $cfg['currency'],
    ];
}

// 2. Discover reseller's own client_id via get-client (client_id=0)
$selfResp = smsApiCall('get-client', ['client_id' => 0]);
if (isApiError($selfResp) || !isset($selfResp['id'])) {
    $errors[] = 'self-discovery: ' . apiErrorMsg($selfResp);
    echo json_encode(array_merge($result, [
        'monthly' => [], 'totals' => [], 'errors' => $errors,
        'debug' => ['source' => 'error', 'errors' => $errors],
        'fetched_at' => date('Y-m-d H:i:s'),
    ]), JSON_UNESCAPED_UNICODE);
    exit;
}
$resellerId = intval($selfResp['id']);
$result['reseller_id'] = $resellerId;

// 3. Get Leased Numbers
$numbersResp = smsApiCall('get-numbers', ['filter' => 'owned', 'max' => 100]);
$result['numbers'] = [];
if (!isApiError($numbersResp) && isset($numbersResp['numbers'])) {
    foreach ($numbersResp['numbers'] as $num) {
        $result['numbers'][] = [
            'number' => isset($num['number']) ? $num['number'] : '',
            'label'  => isset($num['label']) ? $num['label'] : '',
            'type'   => isset($num['type']) ? $num['type'] : '',
        ];
    }
}
$result['numbers_count'] = count($result['numbers']);

// 4. Fetch transactions in 1-year chunks (API limit: max 1 year per call)
$allTxn = [];
$now = time();
$chunks = [
    // Current year
    [date('Y-m-d', strtotime('-364 days', $now)), date('Y-m-d', $now)],
    // Previous year
    [date('Y-m-d', strtotime('-729 days', $now)), date('Y-m-d', strtotime('-365 days', $now))],
    // Year before that
    [date('Y-m-d', strtotime('-1094 days', $now)), date('Y-m-d', strtotime('-730 days', $now))],
];

foreach ($chunks as $chunk) {
    $txns = fetchTransactions($resellerId, $chunk[0], $chunk[1]);
    if (is_array($txns) && !isset($txns['error'])) {
        $allTxn = array_merge($allTxn, $txns);
    } elseif (isset($txns['error'])) {
        $errors[] = "txn [{$chunk[0]} → {$chunk[1]}]: {$txns['error']}";
    }
}

// 5. Group transactions by month
// Transaction types from TransmitSMS:
//   type_id=4  (client-revenue)  → SMS margin: amount=margin, message_count=SMS, message_cost=cost
//   type_id=8  (longcode-lease)  → Number lease fee
//   type_id=13                   → Client credit transfer
//   type_id=19                   → Balance payout
//   type_id=36                   → MO Fee
$monthly = []; // 'YYYY-MM' => [...]

foreach ($allTxn as $txn) {
    $date = isset($txn['created']) ? $txn['created'] : '';
    if (!$date) continue;
    $mk = date('Y-m', strtotime($date));
    if (!isset($monthly[$mk])) {
        $monthly[$mk] = [
            'sms_sent'    => 0,
            'sms_margin'  => 0,
            'numbers_sum' => 0,
            'keywords_sum'=> 0,
            'deposit'     => 0,
            'cost'        => 0,
            'revenue'     => 0,
        ];
    }

    $typeId = isset($txn['type_id']) ? intval($txn['type_id']) : 0;
    $amount = isset($txn['amount']) ? floatval($txn['amount']) : 0;
    $msgCount = isset($txn['message_count']) ? intval($txn['message_count']) : 0;
    $msgCost = isset($txn['message_cost']) ? floatval($txn['message_cost']) : 0;

    if ($typeId === 4) {
        // SMS margin revenue
        $monthly[$mk]['sms_sent'] += $msgCount;
        $monthly[$mk]['sms_margin'] += $amount;       // reseller's margin earned
        $monthly[$mk]['cost'] += $msgCost;             // total SMS cost (turnover)
        $monthly[$mk]['revenue'] += $amount;           // profit from SMS
    } elseif ($typeId === 8) {
        // Number lease — cost to reseller, revenue from client
        $monthly[$mk]['numbers_sum'] += abs($amount);
    } elseif ($typeId === 13 && $amount < 0) {
        // Client credit transfer (reseller → client deposit)
        $monthly[$mk]['deposit'] += abs($amount);
    } elseif ($typeId === 19 && $amount < 0) {
        // Balance payout — not included in monthly summary
    }
}

// Build sorted monthly rows (newest first)
$monthlyRows = [];
krsort($monthly);
foreach ($monthly as $mk => $m) {
    $monthlyRows[] = [
        'month'       => $mk,
        'label'       => date('F Y', strtotime($mk . '-01')),
        'sms_sent'    => $m['sms_sent'],
        'sms_margin'  => round($m['sms_margin'], 3),
        'numbers_sum' => round($m['numbers_sum'], 3),
        'keywords_sum'=> round($m['keywords_sum'], 3),
        'deposit'     => round($m['deposit'], 3),
        'cost'        => round($m['cost'], 3),
        'revenue'     => round($m['revenue'], 3),
    ];
}
$result['monthly'] = $monthlyRows;

// Totals
$totals = ['sms_sent'=>0, 'sms_margin'=>0, 'numbers_sum'=>0, 'keywords_sum'=>0, 'deposit'=>0, 'cost'=>0, 'revenue'=>0];
foreach ($monthly as $m) {
    foreach ($totals as $k => &$v) $v += $m[$k];
}
unset($v);
foreach ($totals as $k => $v) {
    if ($k !== 'sms_sent') $totals[$k] = round($v, 3);
}
$result['totals'] = $totals;

// Recent transactions (last 20)
$result['recent_transactions'] = array_slice($allTxn, 0, 20);

// Debug
$elapsed = round(microtime(true) - $startTime, 1);
$result['debug'] = [
    'source'       => 'realtime',
    'time_seconds' => $elapsed,
    'memory_peak'  => round(memory_get_peak_usage(true) / 1024 / 1024, 1) . 'MB',
    'reseller_id'  => $resellerId,
    'txn_fetched'  => count($allTxn),
    'months'       => count($monthlyRows),
    'errors'       => $errors,
];

$result['fetched_at'] = date('Y-m-d H:i:s');

// Save cache
file_put_contents($cacheFile, json_encode($result, JSON_UNESCAPED_UNICODE));

echo json_encode($result, JSON_UNESCAPED_UNICODE);
exit;
