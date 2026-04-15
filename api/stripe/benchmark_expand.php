<?php
/**
 * Benchmark: Stripe Expanding Objects — compare speed with/without expand
 * Run: php benchmark_expand.php
 */
require __DIR__ . '/../../vendor/autoload.php';
$accounts = require __DIR__ . '/stripe_config.php';
$stripe = new \Stripe\StripeClient($accounts['au']['sk']);

$periodStart = strtotime('-7 days 00:00:00');
$periodEnd = time();

echo "=== Stripe Expanding Objects Benchmark ===\n";
echo "Period: " . date('Y-m-d', $periodStart) . " to " . date('Y-m-d', $periodEnd) . "\n\n";

// -------------------------------------------------------
// TEST 1: Balance Transactions — without vs with expand
// -------------------------------------------------------
echo "--- TEST 1: Balance Transactions (source expand) ---\n";

// Without expand
$t1 = microtime(true);
$txnList = $stripe->balanceTransactions->all([
    'limit' => 100,
    'created' => ['gte' => $periodStart, 'lt' => $periodEnd + 86400]
]);
$countNoExpand = 0;
foreach ($txnList->autoPagingIterator() as $tx) {
    $countNoExpand++;
    // Access basic fields only
    $_ = $tx->id . $tx->type . $tx->amount . ($tx->description ?? '');
}
$t1End = microtime(true);
$time1NoExpand = round($t1End - $t1, 3);
echo "  Without expand: {$countNoExpand} txns in {$time1NoExpand}s\n";

// With expand
$t2 = microtime(true);
$txnList2 = $stripe->balanceTransactions->all([
    'limit' => 100,
    'expand' => ['data.source'],
    'created' => ['gte' => $periodStart, 'lt' => $periodEnd + 86400]
]);
$countExpand = 0;
foreach ($txnList2->autoPagingIterator() as $tx) {
    $countExpand++;
    // Access expanded source fields
    $src = $tx->source;
    $_ = $tx->id . $tx->type . $tx->amount;
    if (is_object($src)) {
        $_ .= ($src->billing_details->name ?? '') . ($src->billing_details->email ?? '');
    }
}
$t2End = microtime(true);
$time1Expand = round($t2End - $t2, 3);
echo "  With expand:    {$countExpand} txns in {$time1Expand}s\n";
$diff1 = round($time1NoExpand - $time1Expand, 3);
echo "  Diff: " . ($diff1 > 0 ? "expand FASTER by {$diff1}s" : "expand SLOWER by " . abs($diff1) . "s") . "\n\n";

// -------------------------------------------------------
// TEST 2: Charges — without vs with expand (customer)
// -------------------------------------------------------
echo "--- TEST 2: Charges (customer expand) ---\n";

// Without expand — then manually retrieve customer
$t3 = microtime(true);
$charges = $stripe->charges->all([
    'limit' => 100,
    'created' => ['gte' => $periodStart, 'lt' => $periodEnd + 86400]
]);
$countChargesNo = 0;
$custRetrieved = 0;
foreach ($charges->autoPagingIterator() as $c) {
    $countChargesNo++;
    $custId = $c->customer ?: '';
    $custName = $c->billing_details->name ?: '';
    $custEmail = $c->billing_details->email ?: ($c->receipt_email ?: '');
    // Simulate: if name is empty and we have custId, we'd need to retrieve
    if (!$custName && $custId && $custRetrieved < 10) {
        try {
            $cust = $stripe->customers->retrieve($custId);
            $custName = $cust->name ?? '';
            $custEmail = $custEmail ?: ($cust->email ?? '');
            $custRetrieved++;
        } catch (\Exception $e) {}
    }
}
$t3End = microtime(true);
$time2NoExpand = round($t3End - $t3, 3);
echo "  Without expand: {$countChargesNo} charges in {$time2NoExpand}s (+ {$custRetrieved} extra customer retrieves)\n";

// With expand
$t4 = microtime(true);
$charges2 = $stripe->charges->all([
    'limit' => 100,
    'expand' => ['data.customer'],
    'created' => ['gte' => $periodStart, 'lt' => $periodEnd + 86400]
]);
$countChargesEx = 0;
foreach ($charges2->autoPagingIterator() as $c) {
    $countChargesEx++;
    $custObj = $c->customer;
    $custId = is_object($custObj) ? ($custObj->id ?? '') : ($custObj ?: '');
    $custName = $c->billing_details->name ?: (is_object($custObj) ? ($custObj->name ?? '') : '');
    $custEmail = $c->billing_details->email ?: ($c->receipt_email ?: (is_object($custObj) ? ($custObj->email ?? '') : ''));
    // No extra API calls needed!
}
$t4End = microtime(true);
$time2Expand = round($t4End - $t4, 3);
echo "  With expand:    {$countChargesEx} charges in {$time2Expand}s (0 extra calls)\n";
$diff2 = round($time2NoExpand - $time2Expand, 3);
echo "  Diff: " . ($diff2 > 0 ? "expand FASTER by {$diff2}s" : "expand SLOWER by " . abs($diff2) . "s") . "\n\n";

// -------------------------------------------------------
// TEST 3: Payouts — without vs with expand (destination)
// -------------------------------------------------------
echo "--- TEST 3: Payouts (destination expand) ---\n";

$t5 = microtime(true);
$payouts = $stripe->payouts->all(['limit' => 10]);
$countPayNo = 0;
foreach ($payouts->data as $p) {
    $countPayNo++;
    $_ = $p->id . $p->amount . $p->status;
}
$t5End = microtime(true);
$time3NoExpand = round($t5End - $t5, 3);
echo "  Without expand: {$countPayNo} payouts in {$time3NoExpand}s\n";

$t6 = microtime(true);
$payouts2 = $stripe->payouts->all(['limit' => 10, 'expand' => ['data.destination']]);
$countPayEx = 0;
foreach ($payouts2->data as $p) {
    $countPayEx++;
    $dest = is_object($p->destination) ? ($p->destination->bank_name ?? '') . ' ****' . ($p->destination->last4 ?? '') : '';
    $_ = $p->id . $p->amount . $p->status . $dest;
}
$t6End = microtime(true);
$time3Expand = round($t6End - $t6, 3);
echo "  With expand:    {$countPayEx} payouts in {$time3Expand}s\n";
$diff3 = round($time3NoExpand - $time3Expand, 3);
echo "  Diff: " . ($diff3 > 0 ? "expand FASTER by {$diff3}s" : "expand SLOWER by " . abs($diff3) . "s") . "\n\n";

// -------------------------------------------------------
// SUMMARY
// -------------------------------------------------------
echo "========== SUMMARY ==========\n";
echo "Balance Txns:  no-expand={$time1NoExpand}s  expand={$time1Expand}s  diff={$diff1}s\n";
echo "Charges:       no-expand={$time2NoExpand}s  expand={$time2Expand}s  diff={$diff2}s\n";
echo "Payouts:       no-expand={$time3NoExpand}s  expand={$time3Expand}s  diff={$diff3}s\n";
$totalNo = round($time1NoExpand + $time2NoExpand + $time3NoExpand, 3);
$totalEx = round($time1Expand + $time2Expand + $time3Expand, 3);
$totalDiff = round($totalNo - $totalEx, 3);
echo "TOTAL:         no-expand={$totalNo}s  expand={$totalEx}s  diff={$totalDiff}s\n";
echo "Memory peak: " . round(memory_get_peak_usage(true) / 1024 / 1024, 1) . "MB\n";
