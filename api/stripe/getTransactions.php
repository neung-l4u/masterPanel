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
        'expand' => ['data.source'],
        'created' => ['gte' => $periodStart, 'lt' => $periodEnd + 86400]
    ]);

    foreach ($txnList->autoPagingIterator() as $tx) {
        $txnCount++;
        $src = $tx->source;
        $srcId = is_object($src) ? ($src->id ?? '') : ($src ?? '');
        $item = [
            'id'           => $tx->id,
            'type'         => $tx->type,
            'source'       => $srcId,
            'amount'       => $tx->amount / 100,
            'fee'          => $tx->fee / 100,
            'net'          => $tx->net / 100,
            'currency'     => strtoupper($tx->currency),
            'created'      => date('Y-m-d H:i:s', $tx->created),
            'created_ts'   => $tx->created,
            'available_on' => date('Y-m-d', $tx->available_on),
            'description'  => $tx->description ?? '',
            'customer_name'  => is_object($src) ? ($src->billing_details->name ?? '') : '',
            'customer_email' => is_object($src) ? ($src->billing_details->email ?? ($src->receipt_email ?? '')) : '',
            'payment_method' => is_object($src) ? ($src->payment_method_details->type ?? '') : '',
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

// Aggregate summary — separate charge revenue from other balance transaction types
$allItems = array_merge($transactions, $payouts);
$summary = [
    'total_amount' => 0, 'total_fee' => 0, 'total_net' => 0, 'total_revenue' => 0,
    'charge_count' => 0, 'payout_count' => count($payouts),
    'refund_total' => 0, 'refund_count' => 0,
    'by_type' => [],
];
foreach ($allItems as $t) {
    $type = $t['type'];
    // Track per-type breakdown
    if (!isset($summary['by_type'][$type])) {
        $summary['by_type'][$type] = ['count' => 0, 'amount' => 0, 'fee' => 0, 'net' => 0];
    }
    $summary['by_type'][$type]['count']++;
    $summary['by_type'][$type]['amount'] += $t['amount'];
    $summary['by_type'][$type]['fee'] += $t['fee'];
    $summary['by_type'][$type]['net'] += $t['net'];

    // Revenue = only charges/payments (what customer actually paid)
    if ($type === 'charge' || $type === 'payment') {
        $summary['charge_count']++;
        $summary['total_revenue'] += $t['amount'];
    }
    // Refunds
    if ($type === 'refund' || $type === 'payment_refund') {
        $summary['refund_count']++;
        $summary['refund_total'] += abs($t['amount']);
    }
    // Amount/Fee/Net = sum all items (balance-level, matches Stripe balance)
    $summary['total_amount'] += $t['amount'];
    $summary['total_fee'] += $t['fee'];
    $summary['total_net'] += $t['net'];
}

// Monthly breakdown — track revenue (charges only) vs amount (all balance txns)
$byMonth = [];
foreach ($allItems as $t) {
    $mk = substr($t['created'], 0, 7); // "2025-03"
    if (!isset($byMonth[$mk])) {
        $byMonth[$mk] = ['amount' => 0, 'fee' => 0, 'net' => 0, 'revenue' => 0, 'count' => 0, 'charge_count' => 0, 'refund' => 0];
    }
    $byMonth[$mk]['amount'] += $t['amount'];
    $byMonth[$mk]['fee'] += $t['fee'];
    $byMonth[$mk]['net'] += $t['net'];
    $byMonth[$mk]['count']++;
    if ($t['type'] === 'charge' || $t['type'] === 'payment') {
        $byMonth[$mk]['charge_count']++;
        $byMonth[$mk]['revenue'] += $t['amount'];
    }
    if ($t['type'] === 'refund' || $t['type'] === 'payment_refund') {
        $byMonth[$mk]['refund'] += abs($t['amount']);
    }
}
ksort($byMonth);

// Round all numbers
$summary['total_amount'] = round($summary['total_amount'], 2);
$summary['total_fee'] = round($summary['total_fee'], 2);
$summary['total_net'] = round($summary['total_net'], 2);
$summary['total_revenue'] = round($summary['total_revenue'], 2);
$summary['refund_total'] = round($summary['refund_total'], 2);
foreach ($summary['by_type'] as &$bt) {
    $bt['amount'] = round($bt['amount'], 2);
    $bt['fee'] = round($bt['fee'], 2);
    $bt['net'] = round($bt['net'], 2);
}
unset($bt);
foreach ($byMonth as &$bm) {
    $bm['amount'] = round($bm['amount'], 2);
    $bm['fee'] = round($bm['fee'], 2);
    $bm['net'] = round($bm['net'], 2);
    $bm['revenue'] = round($bm['revenue'], 2);
    $bm['refund'] = round($bm['refund'], 2);
}
unset($bm);

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
