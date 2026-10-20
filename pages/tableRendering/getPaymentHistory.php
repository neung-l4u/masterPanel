<?php
/**
 * Full invoice history for the SignUp Logs "History Payment" column.
 *
 * Returns every invoice Stripe holds for the customer - the signup charge and
 * every recurring subscription invoice after it - newest first, for the card
 * list.
 */
global $db;
session_start();

// Staff-only: this exposes customer billing data, so require a logged-in
// session the same way the other billing endpoints do.
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

$cacheDir = __DIR__ . '/../../api/stripe/cache/payment_history';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

// Subscription invoices keep arriving, so this list is never "final" the way a
// single settled invoice is. Keep the window short.
$TTL = 900;

function fail($msg) {
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

// stripeResult is sometimes a plain JSON string ("Test Mode - No Charge")
// rather than an object, so only read keys once it decoded to an array.
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

$cacheFile = $cacheDir . '/' . $account . '_' . preg_replace('/[^A-Za-z0-9_]/', '', $customerId) . '.json';
if (is_file($cacheFile)) {
    $cached = json_decode(file_get_contents($cacheFile), true);
    if (is_array($cached) && (time() - (int)($cached['cached_at'] ?? 0)) < $TTL) {
        echo json_encode(['success' => true, 'data' => $cached]);
        exit;
    }
}

try {
    $stripe = new \Stripe\StripeClient($accounts[$account]['sk']);

    $all = [];
    $params = ['customer' => $customerId, 'limit' => 100];
    while (true) {
        $page = $stripe->invoices->all($params);
        if (empty($page->data)) break;
        foreach ($page->data as $inv) $all[] = $inv;
        if (!$page->has_more) break;
        $params['starting_after'] = end($page->data)->id;
    }

    if (empty($all)) fail('No invoice found for this customer.');

    // Stripe returns newest first, so the last one is the customer's first
    // invoice - the signup charge. Flagged so the card list can label it.
    $firstInvoiceId = end($all)->id;

    $items = [];
    foreach ($all as $inv) {
        // Stripe moved the subscription reference under `parent` in newer API
        // versions; read both so older and newer invoices both resolve.
        $subId = $inv->subscription
            ?? ($inv->parent->subscription_details->subscription ?? null);

        // Keep every invoice: the signup charge and the recurring ones.
        $items[] = [
            'is_first'     => ($inv->id === $firstInvoiceId),
            'invoice_id'   => $inv->id,
            'number'       => $inv->number,
            'status'       => $inv->status,
            'total'        => $inv->total,
            'amount_paid'  => $inv->amount_paid,
            'amount_due'   => $inv->amount_due,
            'currency'     => strtoupper($inv->currency ?? ''),
            'created'      => $inv->created,
            'period_start' => $inv->period_start,
            'period_end'   => $inv->period_end,
            'subscription' => $subId,
            'description'  => $inv->lines->data[0]->description ?? '',
        ];
    }

    // Show the history the way a statement reads: oldest first, so 0001 is at
    // the top and each renewal follows it.
    $items = array_reverse($items);

    $logs = json_decode($row['dataLogs'], true);
    $paidCount = 0;
    foreach ($items as $it) if ($it['status'] === 'paid') $paidCount++;

    $out = [
        'shop_name'   => $logs['ShopName'] ?? '',
        'customer_id' => $customerId,
        'country'     => $row['countryCode'],
        'account'     => $accounts[$account]['label'] ?? $account,
        'total'       => count($items),
        'paid'        => $paidCount,
        'items'       => $items,
        'cached_at'   => time(),
    ];

    file_put_contents($cacheFile, json_encode($out));
    echo json_encode(['success' => true, 'data' => $out]);

} catch (\Throwable $e) {
    fail('Stripe error: ' . $e->getMessage());
}
