<?php
/**
 * First Paid status lookup for SignUp Logs.
 *
 * Given a list of logssignup row ids, resolves each row's Stripe customer id
 * (stored in stripeResult / dataStripe by the signup flow) and reports the
 * status of that customer's FIRST invoice - i.e. the invoice raised at signup.
 *
 * Stripe is slow to poll per row, so results are cached on disk and the page
 * requests them over AJAX after the table has already rendered.
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

// Same country -> Stripe account split the payments app uses:
// NZ/AU/UK on the AU account, US/CA on the US account, TH on its own.
$COUNTRY_TO_ACCOUNT = [
    'AU' => 'au',
    'NZ' => 'au',
    'UK' => 'au',
    'US' => 'us',
    'CA' => 'us',
    'TH' => 'th',
];

$cacheDir = __DIR__ . '/../../api/stripe/cache/first_paid';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

// A paid/void/uncollectible invoice is final, so cache it for a long time.
// Anything still open or draft may change, so re-check those more often.
$TTL_FINAL = 86400 * 7;
$TTL_OPEN  = 900;

$ids = $_POST['ids'] ?? $_GET['ids'] ?? '';
if (is_string($ids)) {
    $ids = array_filter(array_map('intval', explode(',', $ids)));
}
$ids = array_slice(array_values(array_unique($ids)), 0, 200);

if (empty($ids)) {
    echo json_encode(['data' => []]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$rows = $db->query(
    'SELECT id, dataStripe, stripeResult, countryCode FROM logssignup WHERE id IN (' . $placeholders . ')',
    ...$ids
)->fetchAll();

$out = [];
$clients = [];

foreach ($rows as $row) {
    $id = (int)$row['id'];
    $stripeResultJson = json_decode($row['stripeResult'], true);
    $stripeJson       = json_decode($row['dataStripe'], true);

    // Same customer id extraction the AI logs page uses - the signup flow has
    // written this key under several different names over time. stripeResult is
    // sometimes a plain JSON string ("Test Mode - No Charge") rather than an
    // object, so only read keys once it has decoded to an array.
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
        $out[$id] = ['status' => 'none', 'customer_id' => null];
        continue;
    }

    $account = $COUNTRY_TO_ACCOUNT[$row['countryCode']] ?? null;
    if ($account === null || !isset($accounts[$account]['sk'])) {
        $out[$id] = ['status' => 'unknown', 'customer_id' => $customerId,
                     'error' => 'No Stripe account for ' . $row['countryCode']];
        continue;
    }

    $cacheFile = $cacheDir . '/' . $account . '_' . preg_replace('/[^A-Za-z0-9_]/', '', $customerId) . '.json';
    if (is_file($cacheFile)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cached) && isset($cached['status'])) {
            $ttl = in_array($cached['status'], ['paid', 'void', 'uncollectible'], true) ? $TTL_FINAL : $TTL_OPEN;
            if ((time() - (int)($cached['cached_at'] ?? 0)) < $ttl) {
                $out[$id] = $cached;
                continue;
            }
        }
    }

    try {
        if (!isset($clients[$account])) {
            $clients[$account] = new \Stripe\StripeClient($accounts[$account]['sk']);
        }

        // Stripe returns invoices newest-first, so page to the end and take the
        // last one - that is the first invoice the customer was ever issued.
        $first = null;
        $params = ['customer' => $customerId, 'limit' => 100];
        while (true) {
            $page = $clients[$account]->invoices->all($params);
            $items = $page->data;
            if (empty($items)) break;
            $first = end($items);
            if (!$page->has_more) break;
            $params['starting_after'] = $first->id;
        }

        if ($first === null) {
            $info = ['status' => 'none', 'customer_id' => $customerId];
        } else {
            $info = [
                'status'      => $first->status,          // draft|open|paid|void|uncollectible
                'customer_id' => $customerId,
                'invoice_id'  => $first->id,
                'number'      => $first->number,
                'amount_paid' => $first->amount_paid,
                'amount_due'  => $first->amount_due,
                'currency'    => strtoupper($first->currency ?? ''),
                'created'     => $first->created,
                'url'         => $first->hosted_invoice_url,
            ];
        }

        $info['cached_at'] = time();
        file_put_contents($cacheFile, json_encode($info));
        $out[$id] = $info;

    } catch (\Throwable $e) {
        $out[$id] = ['status' => 'error', 'customer_id' => $customerId, 'error' => $e->getMessage()];
    }
}

// Rows that were requested but not found in the DB.
foreach ($ids as $id) {
    if (!isset($out[$id])) $out[$id] = ['status' => 'none', 'customer_id' => null];
}

echo json_encode(['data' => $out]);
