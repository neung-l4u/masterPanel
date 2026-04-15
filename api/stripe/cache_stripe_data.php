<?php
/**
 * Stripe Cache Generator — generates cache for ALL periods
 * Run via CLI: php cache_stripe_data.php [account]
 * Or: php cache_stripe_data.php all  (generates for all accounts)
 * Set up cron every 5 min: php /path/to/cache_stripe_data.php all
 */

ini_set('memory_limit', '2G');
set_time_limit(0);

require_once __DIR__ . '/../../vendor/autoload.php';
$accounts = require __DIR__ . '/stripe_config.php';

$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

// Periods to cache: name => days
$periods = [
    '7days'    => 7,
    '28days'   => 28,
    '90days'   => 90,
    '180days'  => 180,
    '365days'  => 365,
    'alltime'  => 0, // special: all time
];

$targetAccount = $argv[1] ?? 'au';

// If "all", generate for every account
if ($targetAccount === 'all') {
    $accountKeys = array_keys($accounts);
} else {
    if (!isset($accounts[$targetAccount])) {
        echo "Invalid account. Use: " . implode(', ', array_keys($accounts)) . " or 'all'\n";
        exit(1);
    }
    $accountKeys = [$targetAccount];
}

foreach ($accountKeys as $account) {
    $sk = $accounts[$account]['sk'];
    $currency = $accounts[$account]['currency'];
    $primaryCur = strtolower($currency);
    $timezone = $accounts[$account]['timezone'] ?? 'Australia/Queensland';
    date_default_timezone_set($timezone);

    $stripe = new \Stripe\StripeClient($sk);
    $now = time();
    $todayStart = strtotime('today 00:00:00');
    $yesterdayStart = strtotime('yesterday 00:00:00');

    echo "\n========================================\n";
    echo "Account: {$account} ({$currency})\n";
    echo "========================================\n";

    // ===== Shared data: Balance, Payouts, Subscriptions (same for all periods) =====
    echo "[Shared] Balance... ";
    $balance = $stripe->balance->retrieve();
    $availableAmount = 0; $pendingAmount = 0;
    foreach ($balance->available as $b) {
        if (strtolower($b->currency) === $primaryCur) $availableAmount += $b->amount;
    }
    foreach ($balance->pending as $b) {
        if (strtolower($b->currency) === $primaryCur) $pendingAmount += $b->amount;
    }
    $balanceData = ['available' => $availableAmount / 100, 'pending' => $pendingAmount / 100, 'currency' => $currency];
    echo "OK\n";

    echo "[Shared] Payouts... ";
    // 1) ดึง upcoming payouts (arrival_date >= today)
    $upcomingPayouts = $stripe->payouts->all([
        'limit' => 10,
        'arrival_date' => ['gte' => strtotime('today')]
    ]);
    // 2) ดึง recent payouts (สำหรับ history)
    $recentPayouts = $stripe->payouts->all(['limit' => 10]);

    $payoutsData = [];
    $seenIds = [];
    // รวม upcoming + recent โดยไม่ซ้ำ
    $allPayoutsList = array_merge($upcomingPayouts->data ?? [], $recentPayouts->data ?? []);
    foreach ($allPayoutsList as $p) {
        if (isset($seenIds[$p->id])) continue;
        $seenIds[$p->id] = true;
        $payoutsData[] = [
            'id' => $p->id, 'amount' => $p->amount / 100, 'currency' => strtoupper($p->currency),
            'status' => $p->status, 'arrival_date' => date('Y-m-d', $p->arrival_date), 'created' => date('Y-m-d H:i', $p->created)
        ];
    }

    // หา next payout จาก upcoming (arrival_date เร็วที่สุด)
    $nextExpectedPayout = 0; $payoutExpectedDate = '';
    $nearestArrival = PHP_INT_MAX;
    foreach ($upcomingPayouts->data as $p) {
        if ($p->arrival_date < $nearestArrival) {
            $nearestArrival = $p->arrival_date;
            $nextExpectedPayout = $p->amount / 100;
            $payoutExpectedDate = date('M j', $p->arrival_date);
        }
    }
    // fallback: เช็ค in_transit/pending จาก recent
    if ($nextExpectedPayout === 0) {
        foreach ($recentPayouts->data as $p) {
            if (in_array($p->status, ['in_transit', 'pending'])) {
                $nextExpectedPayout = $p->amount / 100;
                $payoutExpectedDate = date('M j', $p->arrival_date);
                break;
            }
        }
    }
    // fallback สุดท้าย: ใช้ pending balance (= total upcoming payouts ที่ Stripe Dashboard แสดง)
    if ($nextExpectedPayout === 0 && $pendingAmount > 0) {
        $nextExpectedPayout = $pendingAmount / 100;
        // ประมาณวันจ่าย: business day ถัดไป +2 วัน (ตาม Stripe schedule)
        $estDate = strtotime('+2 days'); $dow = date('N', $estDate);
        if ($dow == 6) $estDate = strtotime('+2 days', $estDate);
        if ($dow == 7) $estDate = strtotime('+1 day', $estDate);
        $payoutExpectedDate = date('M j', $estDate);
    } elseif ($nextExpectedPayout === 0 && $availableAmount > 0) {
        $nextExpectedPayout = $availableAmount / 100;
        $payoutExpectedDate = '';
    }
    echo "OK\n";

    echo "[Shared] Subscriptions (MRR)... ";
    $activeSubCount = 0; $mrr = 0; $subCount = 0;
    $subCreatedMap = []; // store created timestamps for period-based MRR calc
    $mrrStatuses = ['active', 'trialing', 'past_due'];
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
    echo "{$subCount} subs, MRR=" . round($mrr, 2) . "\n";

    // ===== Generate each period =====
    foreach ($periods as $periodName => $periodDays) {
        $startTime = microtime(true);

        if ($periodDays === 0) {
            // All time
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

        echo "\n--- {$periodName} (days={$days}) ---\n";

        $result = [];
        $result['balance'] = $balanceData;
        $result['payouts'] = $payoutsData;
        $result['nextPayout'] = $nextExpectedPayout;
        $result['payoutExpectedDate'] = $payoutExpectedDate;

        // ===== Balance Transactions =====
        echo "  Balance txns... ";
        $dailyGross = []; $dailyNet = [];
        if (!$skipDaily) {
            for ($t = $periodStart; $t <= $periodEnd; $t += 86400) {
                $d = date('M j', $t); $dailyGross[$d] = 0; $dailyNet[$d] = 0;
            }
        }
        $todayGross = 0; $yesterdayGross = 0;
        $grossTotal = 0; $netTotal = 0; $feeTotal = 0; $refundTotal = 0;
        $prevGross = 0; $prevNet = 0; $txnCount = 0;

        $txnList = $stripe->balanceTransactions->all([
            'limit' => 100,
            'created' => ['gte' => $prevPeriodStart, 'lt' => $periodEnd + 86400]
        ]);
        foreach ($txnList->autoPagingIterator() as $tx) {
            $txnCount++;
            $amt = $tx->amount / 100; $net = $tx->net / 100; $fee = $tx->fee / 100;
            $ts = $tx->created; $type = $tx->type;

            if ($ts >= $periodStart) {
                if ($type === 'charge' || $type === 'payment') {
                    $grossTotal += $amt; $netTotal += $net; $feeTotal += $fee;
                    if (!$skipDaily) {
                        $day = date('M j', $ts);
                        if (isset($dailyGross[$day])) $dailyGross[$day] += $amt;
                        if (isset($dailyNet[$day])) $dailyNet[$day] += $net;
                    }
                } elseif ($type === 'refund') {
                    $refundTotal += abs($amt);
                }
                if ($ts >= $todayStart && ($type === 'charge' || $type === 'payment')) $todayGross += $amt;
                if ($ts >= $yesterdayStart && $ts < $todayStart && ($type === 'charge' || $type === 'payment')) $yesterdayGross += $amt;
            } elseif ($ts >= $prevPeriodStart && $ts < $periodStart) {
                if ($type === 'charge' || $type === 'payment') { $prevGross += $amt; $prevNet += $net; }
            }
        }
        unset($txnList, $tx);
        echo "{$txnCount} txns\n";

        $grossChange = $prevGross > 0 ? round(($grossTotal - $prevGross) / $prevGross * 100, 1) : 0;
        $netChange = $prevNet > 0 ? round(($netTotal - $prevNet) / $prevNet * 100, 1) : 0;

        $result['today'] = ['gross' => round($todayGross, 2), 'yesterday' => round($yesterdayGross, 2), 'time' => date('g:i A')];
        $result['grossVolume'] = ['total' => round($grossTotal, 2), 'previous' => round($prevGross, 2), 'change' => $grossChange, 'daily' => $dailyGross];
        $result['netVolume'] = ['total' => round($netTotal, 2), 'previous' => round($prevNet, 2), 'change' => $netChange, 'daily' => $dailyNet];

        // ===== Charges — Payments + Top Customers =====
        echo "  Charges... ";
        $succeededTotal = 0; $failedTotal = 0; $blockedTotal = 0;
        $uncapturedTotal = 0; $refundedTotal2 = 0;
        $seenPISucc = []; $seenPIFail = [];
        $customerSpend = []; $failedPayments = []; $pcCount = 0;

        $chargeList = $stripe->charges->all([
            'limit' => 100,
            'expand' => ['data.customer'],
            'created' => ['gte' => $periodStart, 'lt' => $periodEnd + 86400]
        ]);
        foreach ($chargeList->autoPagingIterator() as $c) {
            $pcCount++;
            if ($pcCount % 1000 === 0) echo "{$pcCount}... ";
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
                    if ($c->captured) {
                        $succeededTotal += $amt;
                        if ($c->refunded) $refundedTotal2 += ($c->amount_refunded ?? 0) / 100;
                    } else {
                        $uncapturedTotal += $amt;
                    }
                    if ($custId) {
                        $custName = $c->billing_details->name ?: (is_object($custObj) ? ($custObj->name ?? '') : '');
                        if (!isset($customerSpend[$custId])) $customerSpend[$custId] = ['email' => $custEmail, 'name' => $custName, 'amount' => 0];
                        $customerSpend[$custId]['amount'] += $amt;
                        if (!$customerSpend[$custId]['name'] && $custName) $customerSpend[$custId]['name'] = $custName;
                        if (!$customerSpend[$custId]['email'] && $custEmail) $customerSpend[$custId]['email'] = $custEmail;
                    }
                }
            } elseif ($c->status === 'failed') {
                $isDupe = false;
                if ($pi) { if (isset($seenPIFail[$pi])) $isDupe = true; else $seenPIFail[$pi] = true; }
                if (!$isDupe && $chargeCur === $primaryCur) {
                    $failedTotal += $amt;
                    if (count($failedPayments) < 4) {
                        $failedPayments[] = [
                            'id' => $c->id, 'amount' => round($amt, 2),
                            'created' => date('M j, Y', $c->created),
                            'description' => $c->description ?: ($c->metadata->description ?? 'Payment')
                        ];
                    }
                }
            } elseif ($c->status === 'blocked') {
                if ($chargeCur === $primaryCur) $blockedTotal += $amt;
            }
        }
        unset($chargeList, $c, $seenPISucc, $seenPIFail);
        echo "{$pcCount} charges\n";

        $result['payments'] = [
            'succeeded' => round($succeededTotal, 2), 'failed' => round($failedTotal, 2),
            'blocked' => round($blockedTotal, 2), 'uncaptured' => round($uncapturedTotal, 2),
            'refunded' => round($refundedTotal2, 2)
        ];
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

        // ===== MRR (from shared subscription data) =====
        $newSubMrr = 0;
        foreach ($subCreatedMap as $sub) {
            if ($sub['created'] >= $periodStart) $newSubMrr += $sub['mrr'];
        }
        $result['subscriptions'] = ['active_count' => $activeSubCount];
        $prevMrr = $mrr - $newSubMrr;
        if ($prevMrr < 0) $prevMrr = 0;
        $mrrChange = $prevMrr > 0 ? round(($mrr - $prevMrr) / $prevMrr * 100, 1) : 0;
        $result['mrr'] = ['total' => round($mrr, 2), 'previous' => round($prevMrr, 2), 'change' => $mrrChange];

        // ===== Customers =====
        echo "  Customers... ";
        $newCustCount = 0; $dailyNewCust = [];
        if (!$skipDaily) {
            for ($t = $periodStart; $t <= $periodEnd; $t += 86400) { $d = date('M j', $t); $dailyNewCust[$d] = 0; }
        }
        $custList = $stripe->customers->all([
            'limit' => 100, 'created' => ['gte' => $periodStart, 'lt' => $periodEnd + 86400]
        ]);
        foreach ($custList->autoPagingIterator() as $cu) {
            $newCustCount++;
            if (!$skipDaily) { $d = date('M j', $cu->created); if (isset($dailyNewCust[$d])) $dailyNewCust[$d]++; }
        }
        unset($custList, $cu);

        $prevCustCount = 0;
        $prevCustList = $stripe->customers->all([
            'limit' => 100, 'created' => ['gte' => $prevPeriodStart, 'lt' => $periodStart]
        ]);
        foreach ($prevCustList->autoPagingIterator() as $cu) { $prevCustCount++; }
        unset($prevCustList, $cu);
        echo "{$newCustCount} new, {$prevCustCount} prev\n";

        $newCustChange = $prevCustCount > 0 ? round(($newCustCount - $prevCustCount) / $prevCustCount * 100, 0) : 0;
        $result['newCustomers'] = ['count' => $newCustCount, 'previous' => $prevCustCount, 'change' => $newCustChange, 'daily' => $dailyNewCust];

        // ===== Save =====
        $elapsed = round(microtime(true) - $startTime, 1);
        $result['period'] = ['days' => $days, 'start' => date('M j', $periodStart), 'end' => date('M j', $periodEnd), 'timezone' => $timezone];
        $result['debug'] = [
            'balance_txns_fetched' => $txnCount, 'charges_fetched' => $pcCount,
            'subscriptions_fetched' => $subCount, 'customers_fetched' => $newCustCount + $prevCustCount,
            'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 1) . 'MB',
            'time_seconds' => $elapsed
        ];
        $result['cached_at'] = date('Y-m-d H:i:s');

        $cacheFile = $cacheDir . "/{$account}_{$periodName}.json";
        file_put_contents($cacheFile, json_encode($result, JSON_UNESCAPED_UNICODE));
        echo "  => Saved: {$cacheFile} ({$elapsed}s)\n";
    }
}

echo "\n=== ALL DONE ===\n";
