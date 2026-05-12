<?php
/**
 * Stripe Cache Refresh API — triggers cache regeneration for a specific account + period
 * Called via AJAX from the frontend refresh button
 * Usage: GET ?account=au&period=7days
 */
header('Content-Type: application/json');

ini_set('memory_limit', '1G');
set_time_limit(300);

require_once __DIR__ . '/../../vendor/autoload.php';
$accounts = require __DIR__ . '/stripe_config.php';

$account = isset($_GET['account']) ? $_GET['account'] : '';
if (!isset($accounts[$account])) {
    echo json_encode(['error' => 'Invalid account']);
    exit;
}

$periodName = isset($_GET['period']) ? $_GET['period'] : '7days';
$validPeriods = ['7days' => 7, '28days' => 28, '90days' => 90, '180days' => 180, '365days' => 365, 'alltime' => 0];
if (!isset($validPeriods[$periodName])) {
    echo json_encode(['error' => 'Invalid period. Use: ' . implode(', ', array_keys($validPeriods))]);
    exit;
}

$periodDays = $validPeriods[$periodName];
$sk = $accounts[$account]['sk'];
$currency = $accounts[$account]['currency'];
$primaryCur = strtolower($currency);
$timezone = $accounts[$account]['timezone'] ?? 'Australia/Queensland';
date_default_timezone_set($timezone);

$stripe = new \Stripe\StripeClient($sk);
$now = time();
$todayStart = strtotime('today 00:00:00');
$yesterdayStart = strtotime('yesterday 00:00:00');

$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

try {

$startTime = microtime(true);

// ===== Balance =====
$balance = $stripe->balance->retrieve();
$availableAmount = 0; $pendingAmount = 0;
foreach ($balance->available as $b) { if (strtolower($b->currency) === $primaryCur) $availableAmount += $b->amount; }
foreach ($balance->pending as $b) { if (strtolower($b->currency) === $primaryCur) $pendingAmount += $b->amount; }
$balanceData = ['available' => $availableAmount / 100, 'pending' => $pendingAmount / 100, 'currency' => $currency];

// ===== Payouts — upcoming + recent =====
$upcomingPayouts = $stripe->payouts->all(['limit' => 10, 'arrival_date' => ['gte' => strtotime('today')]]);
$recentPayouts = $stripe->payouts->all(['limit' => 10]);
$payoutsData = [];
$seenIds = [];
$allPayoutsList = array_merge($upcomingPayouts->data ?? [], $recentPayouts->data ?? []);
foreach ($allPayoutsList as $p) {
    if (isset($seenIds[$p->id])) continue;
    $seenIds[$p->id] = true;
    $payoutsData[] = [
        'id' => $p->id, 'amount' => $p->amount / 100, 'currency' => strtoupper($p->currency),
        'status' => $p->status, 'arrival_date' => date('Y-m-d', $p->arrival_date), 'created' => date('Y-m-d H:i', $p->created)
    ];
}
$nextExpectedPayout = 0; $payoutExpectedDate = '';
$nearestArrival = PHP_INT_MAX;
foreach ($upcomingPayouts->data as $p) {
    if ($p->arrival_date < $nearestArrival) {
        $nearestArrival = $p->arrival_date;
        $nextExpectedPayout = $p->amount / 100;
        $payoutExpectedDate = date('M j', $p->arrival_date);
    }
}
if ($nextExpectedPayout === 0) {
    foreach ($recentPayouts->data as $p) {
        if (in_array($p->status, ['in_transit', 'pending'])) {
            $nextExpectedPayout = $p->amount / 100;
            $payoutExpectedDate = date('M j', $p->arrival_date);
            break;
        }
    }
}
if ($nextExpectedPayout === 0 && $pendingAmount > 0) {
    $nextExpectedPayout = $pendingAmount / 100;
    $estDate = strtotime('+2 days'); $dow = date('N', $estDate);
    if ($dow == 6) $estDate = strtotime('+2 days', $estDate);
    if ($dow == 7) $estDate = strtotime('+1 day', $estDate);
    $payoutExpectedDate = date('M j', $estDate);
} elseif ($nextExpectedPayout === 0 && $availableAmount > 0) {
    $nextExpectedPayout = $availableAmount / 100;
    $payoutExpectedDate = '';
}

// ===== Subscriptions (MRR) =====
$activeSubCount = 0; $mrr = 0; $subCount = 0; $newSubMrr = 0;
$mrrStatuses = ['active', 'trialing', 'past_due'];
$subCreatedMap = [];
foreach ($mrrStatuses as $subStatus) {
    $subList = $stripe->subscriptions->all(['status' => $subStatus, 'limit' => 100]);
    foreach ($subList->autoPagingIterator() as $s) {
        $subCount++;
        if ($subStatus === 'active') $activeSubCount++;
        $subMrr = 0;
        if (isset($s->items->data[0]->price)) {
            $price = $s->items->data[0]->price;
            $unitAmt = ($price->unit_amount ?? 0) / 100;
            $interval = $price->recurring->interval ?? 'month';
            $intervalCount = $price->recurring->interval_count ?? 1;
            if ($interval === 'year') $unitAmt = $unitAmt / (12 * $intervalCount);
            elseif ($interval === 'week') $unitAmt = $unitAmt * (52 / 12) / $intervalCount;
            elseif ($interval === 'day') $unitAmt = $unitAmt * (365 / 12) / $intervalCount;
            else $unitAmt = $unitAmt / $intervalCount;
            $qty = $s->items->data[0]->quantity ?? 1;
            $subMrr = $unitAmt * $qty;
        }
        $mrr += $subMrr;
        $subCreatedMap[] = ['created' => $s->created, 'mrr' => $subMrr];
    }
    unset($subList, $s);
}

// ===== Period calculation =====
if ($periodDays === 0) {
    $periodStart = strtotime('2020-01-01 00:00:00');
    $periodEnd = $now;
    $days = max(1, (int)(($periodEnd - $periodStart) / 86400) + 1);
} else {
    $days = $periodDays;
    $periodStart = strtotime("-" . ($days - 1) . " days 00:00:00");
    $periodEnd = $now;
}
$prevPeriodStart = $periodStart - ($periodEnd - $periodStart);
$skipDaily = ($periodDays === 0 || $periodDays > 365);

$result = [];
$result['balance'] = $balanceData;
$result['payouts'] = $payoutsData;
$result['nextPayout'] = $nextExpectedPayout;
$result['payoutExpectedDate'] = $payoutExpectedDate;

// ===== Balance Transactions =====
$dailyGross = []; $dailyNet = [];
if (!$skipDaily) {
    for ($t = $periodStart; $t <= $periodEnd; $t += 86400) { $d = date('M j', $t); $dailyGross[$d] = 0; $dailyNet[$d] = 0; }
}
$todayGross = 0; $yesterdayGross = 0;
$grossTotal = 0; $netTotal = 0; $feeTotal = 0; $refundTotal = 0;
$prevGross = 0; $prevNet = 0; $txnCount = 0;

$txnList = $stripe->balanceTransactions->all(['limit' => 100, 'created' => ['gte' => $prevPeriodStart, 'lt' => $periodEnd + 86400]]);
foreach ($txnList->autoPagingIterator() as $tx) {
    $txnCount++;
    $amt = $tx->amount / 100; $net = $tx->net / 100; $fee = $tx->fee / 100;
    $ts = $tx->created; $type = $tx->type;
    if ($ts >= $periodStart) {
        if ($type === 'charge' || $type === 'payment') {
            $grossTotal += $amt; $netTotal += $net; $feeTotal += $fee;
            if (!$skipDaily) { $day = date('M j', $ts); if (isset($dailyGross[$day])) $dailyGross[$day] += $amt; if (isset($dailyNet[$day])) $dailyNet[$day] += $net; }
        } elseif ($type === 'refund') { $refundTotal += abs($amt); }
        if ($ts >= $todayStart && ($type === 'charge' || $type === 'payment')) $todayGross += $amt;
        if ($ts >= $yesterdayStart && $ts < $todayStart && ($type === 'charge' || $type === 'payment')) $yesterdayGross += $amt;
    } elseif ($ts >= $prevPeriodStart && $ts < $periodStart) {
        if ($type === 'charge' || $type === 'payment') { $prevGross += $amt; $prevNet += $net; }
    }
}
unset($txnList, $tx);

$grossChange = $prevGross > 0 ? round(($grossTotal - $prevGross) / $prevGross * 100, 1) : 0;
$netChange = $prevNet > 0 ? round(($netTotal - $prevNet) / $prevNet * 100, 1) : 0;
$result['today'] = ['gross' => round($todayGross, 2), 'yesterday' => round($yesterdayGross, 2), 'time' => date('g:i A')];
$result['grossVolume'] = ['total' => round($grossTotal, 2), 'previous' => round($prevGross, 2), 'change' => $grossChange, 'daily' => $dailyGross];
$result['netVolume'] = ['total' => round($netTotal, 2), 'previous' => round($prevNet, 2), 'change' => $netChange, 'daily' => $dailyNet];

// ===== Charges =====
$succeededTotal = 0; $failedTotal = 0; $blockedTotal = 0;
$uncapturedTotal = 0; $refundedTotal2 = 0;
$seenPISucc = []; $seenPIFail = [];
$customerSpend = []; $failedPayments = []; $pcCount = 0;

$chargeList = $stripe->charges->all(['limit' => 100, 'expand' => ['data.customer'], 'created' => ['gte' => $periodStart, 'lt' => $periodEnd + 86400]]);
foreach ($chargeList->autoPagingIterator() as $c) {
    $pcCount++;
    $amt = $c->amount / 100;
    $chargeCur = strtolower($c->currency);
    $custObj = $c->customer;
    $custId = is_object($custObj) ? ($custObj->id ?? '') : ($custObj ?: '');
    $custEmail = $c->billing_details->email ?: ($c->receipt_email ?: (is_object($custObj) ? ($custObj->email ?? '') : ''));
    $pi = $c->payment_intent ?: '';
    if ($c->status === 'succeeded') {
        $isDupe = false;
        if ($pi) { if (isset($seenPISucc[$pi])) $isDupe = true; else $seenPISucc[$pi] = true; }
        if (!$isDupe && $chargeCur === $primaryCur) {
            if ($c->captured) { $succeededTotal += $amt; if ($c->refunded) $refundedTotal2 += ($c->amount_refunded ?? 0) / 100; }
            else $uncapturedTotal += $amt;
            if ($custId) {
                $custName = $c->billing_details->name ?: (is_object($custObj) ? ($custObj->name ?? '') : '');
                if (!isset($customerSpend[$custId])) $customerSpend[$custId] = ['email' => $custEmail, 'name' => $custName, 'amount' => 0];
                $customerSpend[$custId]['amount'] += $amt;
            }
        }
    } elseif ($c->status === 'failed') {
        $isDupe = false;
        if ($pi) { if (isset($seenPIFail[$pi])) $isDupe = true; else $seenPIFail[$pi] = true; }
        if (!$isDupe && $chargeCur === $primaryCur) {
            $failedTotal += $amt;
            if (count($failedPayments) < 4) {
                $failedPayments[] = ['id' => $c->id, 'amount' => round($amt, 2), 'created' => date('M j, g:i A', $c->created), 'description' => $custEmail ?: ($c->description ?: 'Payment')];
            }
        }
    }
}
unset($chargeList, $c, $seenPISucc, $seenPIFail);
$result['payments'] = ['succeeded' => round($succeededTotal, 2), 'failed' => round($failedTotal, 2), 'blocked' => round($blockedTotal, 2), 'uncaptured' => round($uncapturedTotal, 2), 'refunded' => round($refundedTotal2, 2)];
$result['failedPayments'] = array_slice($failedPayments, 0, 4);

uasort($customerSpend, function($a, $b) { return $b['amount'] <=> $a['amount']; });
$result['topCustomers'] = [];
$i = 0;
foreach ($customerSpend as $cid => $info) {
    if ($i >= 5) break;
    $result['topCustomers'][] = ['id' => $cid, 'name' => $info['name'] ?: 'Unknown', 'email' => $info['email'] ?: '', 'amount' => round($info['amount'], 2)];
    $i++;
}
unset($customerSpend);

// ===== MRR =====
$newSubMrr = 0;
foreach ($subCreatedMap as $sub) {
    if ($sub['created'] >= $periodStart) $newSubMrr += $sub['mrr'];
}
$result['subscriptions'] = ['active_count' => $activeSubCount];
$prevMrr = $mrr - $newSubMrr; if ($prevMrr < 0) $prevMrr = 0;
$mrrChange = $prevMrr > 0 ? round(($mrr - $prevMrr) / $prevMrr * 100, 1) : 0;
$result['mrr'] = ['total' => round($mrr, 2), 'previous' => round($prevMrr, 2), 'change' => $mrrChange];

// ===== Customers =====
$newCustCount = 0; $dailyNewCust = [];
if (!$skipDaily) { for ($t = $periodStart; $t <= $periodEnd; $t += 86400) { $d = date('M j', $t); $dailyNewCust[$d] = 0; } }
$custList = $stripe->customers->all(['limit' => 100, 'created' => ['gte' => $periodStart, 'lt' => $periodEnd + 86400]]);
foreach ($custList->autoPagingIterator() as $cu) { $newCustCount++; if (!$skipDaily) { $d = date('M j', $cu->created); if (isset($dailyNewCust[$d])) $dailyNewCust[$d]++; } }
unset($custList, $cu);
$prevCustCount = 0;
$prevCustList = $stripe->customers->all(['limit' => 100, 'created' => ['gte' => $prevPeriodStart, 'lt' => $periodStart]]);
foreach ($prevCustList->autoPagingIterator() as $cu) { $prevCustCount++; }
unset($prevCustList, $cu);
$newCustChange = $prevCustCount > 0 ? round(($newCustCount - $prevCustCount) / $prevCustCount * 100, 0) : 0;
$result['newCustomers'] = ['count' => $newCustCount, 'previous' => $prevCustCount, 'change' => $newCustChange, 'daily' => $dailyNewCust];

// ===== Save =====
$elapsed = round(microtime(true) - $startTime, 1);
$result['period'] = ['days' => $days, 'start' => date('M j', $periodStart), 'end' => date('M j', $periodEnd), 'timezone' => $timezone];
$result['debug'] = [
    'balance_txns_fetched' => $txnCount, 'charges_fetched' => $pcCount,
    'subscriptions_fetched' => $subCount, 'customers_fetched' => $newCustCount + $prevCustCount,
    'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 1) . 'MB',
    'time_seconds' => $elapsed,
    'source' => 'realtime_refresh'
];
$result['cached_at'] = date('Y-m-d H:i:s');

$cacheFile = $cacheDir . "/{$account}_{$periodName}.json";
file_put_contents($cacheFile, json_encode($result, JSON_UNESCAPED_UNICODE));

echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (\Stripe\Exception\ApiErrorException $e) {
    echo json_encode(['error' => 'Stripe API: ' . $e->getMessage()]);
} catch (\Exception $e) {
    echo json_encode(['error' => 'Server: ' . $e->getMessage()]);
}
