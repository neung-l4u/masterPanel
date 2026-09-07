<?php
/**
 * Invoice detail lookup for the SignUp Logs "First Paid" eye icon.
 *
 * Returns the full first-invoice detail for one logssignup row, shaped for the
 * modal: header, line items, totals and whatever timeline Stripe still has.
 * Called on demand (one row at a time) so it never slows the table down.
 */
global $db;
session_start();

// Staff-only: this exposes customer billing data, so require a logged-in session
// the same way the other billing endpoints do.
if (empty($_SESSION['id'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');

$accounts = require __DIR__ . '/../../api/stripe/stripe_config.php';

$COUNTRY_TO_ACCOUNT = [
    'AU' => 'au', 'NZ' => 'au', 'UK' => 'au',
    'US' => 'us', 'CA' => 'us',
    'TH' => 'th',
];

function fail($msg, $code = 200) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : (int)($_GET['id'] ?? 0);
if ($id <= 0) fail('id is required');

$rows = $db->query(
    'SELECT id, dataLogs, dataStripe, stripeResult, countryCode FROM logssignup WHERE id = ?', $id
)->fetchAll();
if (empty($rows)) fail('Signup log not found');

$row = $rows[0];
$stripeResultJson = json_decode($row['stripeResult'], true);
$stripeJson       = json_decode($row['dataStripe'], true);

// stripeResult is sometimes a plain JSON string such as "Test Mode - No Charge"
// rather than an object, so only read keys when it actually decoded to an array.
$customerId = '';
if (is_array($stripeResultJson)) {
    $customerId = $stripeResultJson['customer_id']
        ?? $stripeResultJson['stripeID']
        ?? $stripeResultJson['customer']
        ?? $stripeResultJson['customerId']
        ?? '';
}
if (empty($customerId) && is_array($stripeJson)) {
    $customerId = $stripeJson['stripeID'] ?? $stripeJson['customerId'] ?? '';
}

if (empty($customerId) || strpos($customerId, 'cus_') !== 0) {
    // Surface why there is nothing to show: test signup, or a failed Stripe call.
    $reason = 'This signup has no Stripe customer.';
    if (is_string($stripeResultJson) && stripos($stripeResultJson, 'test mode') !== false) {
        $reason = 'Test Mode - no charge was made.';
    } elseif (is_array($stripeResultJson) && !empty($stripeResultJson['error'])) {
        $reason = 'Stripe error at signup: ' . $stripeResultJson['error'];
    }
    fail($reason);
}

$account = $COUNTRY_TO_ACCOUNT[$row['countryCode']] ?? null;
if ($account === null || !isset($accounts[$account]['sk'])) {
    fail('No Stripe account configured for ' . $row['countryCode']);
}

try {
    $stripe = new \Stripe\StripeClient($accounts[$account]['sk']);

    // Invoices come back newest-first, so page to the end to reach the first one.
    $first = null;
    $params = ['customer' => $customerId, 'limit' => 100];
    while (true) {
        $page = $stripe->invoices->all($params);
        if (empty($page->data)) break;
        $first = end($page->data);
        if (!$page->has_more) break;
        $params['starting_after'] = $first->id;
    }

    if ($first === null) fail('No invoice found for this customer.');

    // Re-retrieve so line items are fully populated.
    $inv = $stripe->invoices->retrieve($first->id, ['expand' => ['lines']]);

    $lines = [];
    foreach ($inv->lines->data as $l) {
        $lines[] = [
            'description' => $l->description,
            'quantity'    => $l->quantity,
            'amount'      => $l->amount,
            // Stripe shows a unit price column; derive it when qty allows.
            'unit_amount' => ($l->quantity > 0) ? (int)round($l->amount / $l->quantity) : $l->amount,
        ];
    }

    // Stripe's Events API only retains ~30 days, so old invoices return nothing
    // here. status_transitions never expires, so build the timeline from that
    // and treat events as a bonus when they are still available.
    $timeline = [];
    $st = $inv->status_transitions;
    if (!empty($st->finalized_at))            $timeline[] = ['at' => $st->finalized_at, 'text' => 'Invoice finalized'];
    if (!empty($st->paid_at))                 $timeline[] = ['at' => $st->paid_at, 'text' => 'Payment successfully applied'];
    if (!empty($st->voided_at))               $timeline[] = ['at' => $st->voided_at, 'text' => 'Invoice voided'];
    if (!empty($st->marked_uncollectible_at)) $timeline[] = ['at' => $st->marked_uncollectible_at, 'text' => 'Marked uncollectible'];
    usort($timeline, fn($a, $b) => $b['at'] <=> $a['at']);

    // Stripe SDK v19 moved tax off the flat `tax` field (now always null) onto
    // `total_taxes`, so read that and fall back for older API shapes.
    $taxAmount = $inv->tax ?? null;
    if ($taxAmount === null && !empty($inv->total_taxes)) {
        $taxAmount = 0;
        foreach ($inv->total_taxes as $t) {
            $taxAmount += (int)($t->amount ?? 0);
        }
    }

    $logs = json_decode($row['dataLogs'], true);

    echo json_encode(['success' => true, 'data' => [
        'shop_name'      => $logs['ShopName'] ?? '',
        'country'        => $row['countryCode'],
        'account'        => $accounts[$account]['label'] ?? $account,
        'customer_id'    => $customerId,
        'invoice_id'     => $inv->id,
        'number'         => $inv->number,
        'status'         => $inv->status,
        'customer_name'  => $inv->customer_name,
        'customer_email' => $inv->customer_email,
        'currency'       => strtoupper($inv->currency ?? ''),
        'created'        => $inv->created,
        'due_date'       => $inv->due_date,
        'collection'     => $inv->collection_method,
        'subtotal'       => $inv->subtotal,
        'tax'            => $taxAmount,
        'total'          => $inv->total,
        'amount_paid'    => $inv->amount_paid,
        'amount_due'     => $inv->amount_due,
        'hosted_url'     => $inv->hosted_invoice_url,
        'pdf_url'        => $inv->invoice_pdf,
        'lines'          => $lines,
        'timeline'       => $timeline,
    ]]);

} catch (\Throwable $e) {
    fail('Stripe error: ' . $e->getMessage());
}
