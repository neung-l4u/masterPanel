<?php
require __DIR__ . '/../../vendor/autoload.php';
$accounts = require __DIR__ . '/stripe_config.php';
$stripe = new \Stripe\StripeClient($accounts['au']['sk']);

// test upcoming payouts
$up = $stripe->payouts->all(['limit' => 5, 'arrival_date' => ['gte' => strtotime('today')]]);
echo "Upcoming payouts (arrival >= today): " . count($up->data) . "\n";
foreach ($up->data as $p) {
    echo "  " . ($p->amount/100) . " " . $p->status . " arrive:" . date('Y-m-d', $p->arrival_date) . "\n";
}

// test recent
$re = $stripe->payouts->all(['limit' => 5]);
echo "Recent payouts:\n";
foreach ($re->data as $p) {
    echo "  " . ($p->amount/100) . " " . $p->status . " arrive:" . date('Y-m-d', $p->arrival_date) . "\n";
}

// balance
$b = $stripe->balance->retrieve();
$pending = 0; $avail = 0;
foreach ($b->pending as $x) { if (strtolower($x->currency)==='aud') $pending += $x->amount; }
foreach ($b->available as $x) { if (strtolower($x->currency)==='aud') $avail += $x->amount; }
echo "Pending: " . ($pending/100) . ", Available: " . ($avail/100) . "\n";
