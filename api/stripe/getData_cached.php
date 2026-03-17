<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';
$accounts = require __DIR__ . '/stripe_config.php';

$account = isset($_GET['account']) ? $_GET['account'] : '';
if (!isset($accounts[$account])) {
    echo json_encode(['error' => 'Invalid account. Use: au, us, th, connect']);
    exit;
}

$days = isset($_GET['days']) ? intval($_GET['days']) : 7;
if ($days < 1) $days = 7;

// Support custom date range
$customStart = isset($_GET['start']) ? $_GET['start'] : '';
$customEnd = isset($_GET['end']) ? $_GET['end'] : '';

$cacheDir = __DIR__ . '/cache';
$cacheFile = $cacheDir . "/{$account}_{$days}days.json";

// Check for custom date range cache
if ($customStart && $customEnd) {
    $hash = md5($customStart . '_' . $customEnd);
    $cacheFile = $cacheDir . "/{$account}_custom_{$hash}.json";
}

// Return cached data if available and not expired (5 minutes)
if (file_exists($cacheFile)) {
    $cacheTime = filemtime($cacheFile);
    if (time() - $cacheTime < 300) { // 5 minutes
        $cached = json_decode(file_get_contents($cacheFile), true);
        if ($cached) {
            echo json_encode($cached, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}

// If no cache or expired, return error for custom ranges (they need to be pre-cached)
if ($customStart && $customEnd) {
    echo json_encode(['error' => 'Custom date range not cached. Please run cache generator first.']);
    exit;
}

// Fallback: try to fetch fresh data with reduced limits
try {
    ini_set('memory_limit', '256M');
    set_time_limit(60); // 1 minute max for fallback
    
    $sk = $accounts[$account]['sk'];
    $currency = $accounts[$account]['currency'];
    $primaryCur = strtolower($currency);
    $timezone = $accounts[$account]['timezone'] ?? 'Australia/Queensland';
    date_default_timezone_set($timezone);
    
    $stripe = new \Stripe\StripeClient($sk);
    
    $result = [];
    $now = time();
    $todayStart = strtotime('today 00:00:00');
    $yesterdayStart = strtotime('yesterday 00:00:00');
    
    $periodStart = strtotime("-" . ($days - 1) . " days 00:00:00");
    $periodEnd = $now;
    $prevPeriodStart = $periodStart - ($periodEnd - $periodStart);
    
    // Quick balance fetch
    $balance = $stripe->balance->retrieve();
    $availableAmount = 0;
    $pendingAmount = 0;
    foreach ($balance->available as $b) {
        if (strtolower($b->currency) === $primaryCur) $availableAmount += $b->amount;
    }
    foreach ($balance->pending as $b) {
        if (strtolower($b->currency) === $primaryCur) $pendingAmount += $b->amount;
    }
    $result['balance'] = [
        'available' => $availableAmount / 100,
        'pending' => $pendingAmount / 100,
        'currency' => $currency
    ];
    
    // Quick payouts
    $payouts = $stripe->payouts->all(['limit' => 5]);
    $result['payouts'] = [];
    $nextExpectedPayout = 0;
    foreach ($payouts->data as $p) {
        $result['payouts'][] = [
            'id' => $p->id,
            'amount' => $p->amount / 100,
            'currency' => strtoupper($p->currency),
            'status' => $p->status,
            'arrival_date' => date('Y-m-d', $p->arrival_date),
            'created' => date('Y-m-d H:i', $p->created)
        ];
        if ($nextExpectedPayout === 0 && in_array($p->status, ['in_transit', 'pending'])) {
            $nextExpectedPayout = $p->amount / 100;
        }
    }
    if ($nextExpectedPayout === 0) {
        $nextExpectedPayout = $availableAmount / 100;
    }
    $result['nextPayout'] = $nextExpectedPayout;
    
    // Minimal other data
    $result['today'] = ['gross' => 0, 'yesterday' => 0, 'time' => date('g:i A')];
    $result['grossVolume'] = ['total' => 0, 'previous' => 0, 'change' => 0, 'daily' => []];
    $result['netVolume'] = ['total' => 0, 'previous' => 0, 'change' => 0, 'daily' => []];
    $result['payments'] = ['succeeded' => 0, 'failed' => 0, 'blocked' => 0, 'uncaptured' => 0, 'refunded' => 0];
    $result['failedPayments'] = [];
    $result['topCustomers'] = [];
    $result['subscriptions'] = ['active_count' => 0];
    $result['mrr'] = ['total' => 0, 'previous' => 0, 'change' => 0];
    $result['newCustomers'] = ['count' => 0, 'previous' => 0, 'change' => 0, 'daily' => []];
    $result['period'] = ['days' => $days, 'start' => date('M j', $periodStart), 'end' => date('M j', $periodEnd), 'timezone' => $timezone];
    $result['fallback'] = true;
    $result['message'] = 'Using fallback data. Cache not available.';
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
} catch (\Exception $e) {
    echo json_encode(['error' => 'Cache not available and fallback failed: ' . $e->getMessage()]);
}
