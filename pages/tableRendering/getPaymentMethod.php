<?php
/**
 * Payment method lookup for the SignUp Logs "Payment Methods" column.
 *
 * Mirrors what Stripe shows on a customer: the brand/bank, the last four
 * digits, and which one is the default for invoices.
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

$cacheDir = __DIR__ . '/../../api/stripe/cache/payment_method';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

// A card can be replaced at any time, so keep this window short.
$TTL = 900;

function fail($msg) {
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : (int)($_GET['id'] ?? 0);
if ($id <= 0) fail('id is required');

$rows = $db->query(
    'SELECT id, dataStripe, stripeResult, countryCode FROM logssignup WHERE id = ?', $id
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

/**
 * Flatten one Stripe PaymentMethod into the fields the column needs. Each
 * payment type keeps its details under its own key, so read per type.
 */
function describeMethod($pm) {
    $out = ['id' => $pm->id, 'type' => $pm->type, 'brand' => '', 'last4' => '', 'exp' => ''];

    if ($pm->type === 'card' && !empty($pm->card)) {
        $out['brand'] = $pm->card->brand;
        $out['last4'] = $pm->card->last4;
        $out['exp']   = sprintf('%02d/%d', $pm->card->exp_month, $pm->card->exp_year);
    } elseif ($pm->type === 'au_becs_debit' && !empty($pm->au_becs_debit)) {
        $out['brand'] = 'BECS Direct Debit';
        $out['last4'] = $pm->au_becs_debit->last4;
    } elseif ($pm->type === 'us_bank_account' && !empty($pm->us_bank_account)) {
        $out['brand'] = $pm->us_bank_account->bank_name ?: 'Bank account';
        $out['last4'] = $pm->us_bank_account->last4;
    } elseif ($pm->type === 'sepa_debit' && !empty($pm->sepa_debit)) {
        $out['brand'] = 'SEPA Direct Debit';
        $out['last4'] = $pm->sepa_debit->last4;
    } elseif ($pm->type === 'link') {
        $out['brand'] = 'Link';
    }
    return $out;
}

try {
    $stripe = new \Stripe\StripeClient($accounts[$account]['sk']);

    $customer = $stripe->customers->retrieve($customerId, [
        'expand' => ['invoice_settings.default_payment_method'],
    ]);

    $defaultPm = $customer->invoice_settings->default_payment_method ?? null;
    $defaultId = $defaultPm ? $defaultPm->id : null;

    // The default is what Stripe bills, but a customer can have several saved.
    $methods = [];
    $list = $stripe->paymentMethods->all(['customer' => $customerId, 'limit' => 10]);
    foreach ($list->data as $pm) {
        $m = describeMethod($pm);
        $m['is_default'] = ($pm->id === $defaultId);
        $methods[] = $m;
    }

    // A default set on the customer may not appear in the list (for example a
    // card attached elsewhere), so make sure it is represented.
    if ($defaultPm && !array_filter($methods, fn($m) => $m['is_default'])) {
        $m = describeMethod($defaultPm);
        $m['is_default'] = true;
        array_unshift($methods, $m);
    }

    // Show the default first - that is the one that actually gets charged.
    usort($methods, fn($a, $b) => ($b['is_default'] <=> $a['is_default']));

    $out = [
        'customer_id' => $customerId,
        'country'     => $row['countryCode'],
        'account'     => $accounts[$account]['label'] ?? $account,
        'total'       => count($methods),
        'methods'     => $methods,
        'cached_at'   => time(),
    ];

    file_put_contents($cacheFile, json_encode($out));
    echo json_encode(['success' => true, 'data' => $out]);

} catch (\Throwable $e) {
    fail('Stripe error: ' . $e->getMessage());
}
