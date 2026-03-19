<?php
/**
 * Stripe Transactions API — returns item-level balance transactions in Monday-like format
 * Usage: getTransactions.php?account=au|us|th&days=28  OR  &start=2025-01-01&end=2025-01-31
 * Groups output into "Transaction" and "Payout" similar to Monday.com board structure
 */
header('Content-Type: application/json');
ini_set('memory_limit', '512M');
set_time_limit(120);

require_once __DIR__ . '/../../vendor/autoload.php';
$accounts = require __DIR__ . '/stripe_config.php';

$account = isset($_GET['account']) ? $_GET['account'] : '';
if (!isset($accounts[$account])) {
    echo json_encode(['error' => 'Invalid account. Use: ' . implode(', ', array_keys($accounts))]);
    exit;
}

$sk = $accounts[$account]['sk'];
$currency = $accounts[$account]['currency'];
$timezone = $accounts[$account]['timezone'] ?? 'Australia/Queensland';
date_default_timezone_set($timezone);

$stripe = new \Stripe\StripeClient($sk);

// Date range
$customStart = isset($_GET['start']) ? $_GET['start'] : '';
$customEnd = isset($_GET['end']) ? $_GET['end'] : '';
$days = isset($_GET['days']) ? intval($_GET['days']) : 28;
if ($days < 1) $days = 28;

if ($customStart && $customEnd) {
    $periodStart = strtotime($customStart . ' 00:00:00');
    $periodEnd = strtotime($customEnd . ' 23:59:59');
    $days = max(1, (int)(($periodEnd - $periodStart) / 86400) + 1);
} else {
    if ($days >= 9999) {
        $periodStart = strtotime('2020-01-01 00:00:00');
    } else {
        $periodStart = strtotime("-" . ($days - 1) . " days 00:00:00");
    }
    $periodEnd = time();
}

// Fetch balance transactions
$transactions = [];
$payouts = [];
$txnCount = 0;

try {
    $txnList = $stripe->balanceTransactions->all([
        'limit' => 100,
        'created' => ['gte' => $periodStart, 'lt' => $periodEnd + 86400]
    ]);

    foreach ($txnList->autoPagingIterator() as $tx) {
        $txnCount++;
        $item = [
            'id'           => $tx->id,
            'type'         => $tx->type,
            'source'       => $tx->source ?? '',
            'amount'       => $tx->amount / 100,
            'fee'          => $tx->fee / 100,
            'net'          => $tx->net / 100,
            'currency'     => strtoupper($tx->currency),
            'created'      => date('Y-m-d H:i:s', $tx->created),
            'created_ts'   => $tx->created,
            'available_on' => date('Y-m-d', $tx->available_on),
            'description'  => $tx->description ?? '',
        ];

        if ($tx->type === 'payout') {
            $payouts[] = $item;
        } else {
            $transactions[] = $item;
        }
    }
} catch (\Stripe\Exception\ApiErrorException $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit;
} catch (\Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    exit;
}

// Sort by created descending
usort($transactions, function($a, $b) { return $b['created_ts'] - $a['created_ts']; });
usort($payouts, function($a, $b) { return $b['created_ts'] - $a['created_ts']; });

// Aggregate summary — sum ALL items (transactions + payouts) to match Monday.com
$allItems = array_merge($transactions, $payouts);
$summary = [
    'total_amount' => 0, 'total_fee' => 0, 'total_net' => 0, 'total_revenue' => 0,
    'charge_count' => 0, 'payout_count' => count($payouts),
];
foreach ($allItems as $t) {
    $summary['total_amount'] += $t['amount'];
    $summary['total_fee'] += $t['fee'];
    $summary['total_net'] += $t['net'];
    if ($t['type'] === 'charge' || $t['type'] === 'payment') {
        $summary['charge_count']++;
        $summary['total_revenue'] += $t['amount'];
    }
}

// Monthly breakdown — ALL items (transactions + payouts) to match Monday.com
$byMonth = [];
foreach ($allItems as $t) {
    $mk = substr($t['created'], 0, 7); // "2025-03"
    if (!isset($byMonth[$mk])) {
        $byMonth[$mk] = ['amount' => 0, 'fee' => 0, 'net' => 0, 'revenue' => 0, 'count' => 0, 'charge_count' => 0];
    }
    $byMonth[$mk]['amount'] += $t['amount'];
    $byMonth[$mk]['fee'] += $t['fee'];
    $byMonth[$mk]['net'] += $t['net'];
    $byMonth[$mk]['count']++;
    if ($t['type'] === 'charge' || $t['type'] === 'payment') {
        $byMonth[$mk]['charge_count']++;
        $byMonth[$mk]['revenue'] += $t['amount'];
    }
}
ksort($byMonth);

echo json_encode([
    'account'       => strtoupper($account),
    'currency'      => strtoupper($currency),
    'period_days'   => $days,
    'period_start'  => date('Y-m-d', $periodStart),
    'period_end'    => date('Y-m-d', $periodEnd),
    'fetched_at'    => date('Y-m-d H:i:s'),
    'total_count'   => $txnCount,
    'summary'       => $summary,
    'by_month'      => $byMonth,
    'groups' => [
        ['title' => 'Transaction', 'count' => count($transactions), 'items' => $transactions],
        ['title' => 'Payout',      'count' => count($payouts),      'items' => $payouts],
    ]
], JSON_UNESCAPED_UNICODE);
