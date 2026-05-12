<?php
/**
 * Stripe Webhook — aiAraya
 * ---------------------------------------------------------------------------
 * Pure filter + forward. No DB writes.
 * Multi-account: supports AU, US, TH Stripe accounts.
 * Route each Stripe webhook to:  stripe_webhook.php?account=au|us|th
 *
 * Supported events:
 *   - checkout.session.completed  (one-time Checkout flow)
 *   - invoice.paid                (subscription / invoice flow)
 *
 * Event is forwarded to Make.com only if BOTH:
 *   1. Payment is actually paid (payment_status/status === 'paid')
 *   2. Product ID matches the account's allowed list (Stripe Product/Price ID)
 *
 * ALWAYS replies HTTP 200 to Stripe so it does not retry the webhook.
 * ---------------------------------------------------------------------------
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ─── CONFIG ────────────────────────────────────────────────────────────────
// Three Stripe accounts, each serving multiple countries:
//   - 'au' account  → AU, NZ, UK
//   - 'us' account  → US, CA
//   - 'th' account  → TH
//
// Each account has ONE product (product_id) with MULTIPLE prices — one
// Price ID per currency. A webhook event matches if the Stripe Product ID
// on the line equals `product_id`, OR the Price ID is listed in `price_ids`.
// Fill in US/TH placeholders before going live.
$STRIPE_ACCOUNTS = [
    'au' => [
        'countries'      => ['au', 'nz', 'uk'],
        'webhook_secret' => 'whsec_q5muKPjQWrEECZ1N2yZdMElckPPlrEka',
        'make_url'       => 'https://hook.us1.make.com/6vloshre04tb1xtkjhgawblx2jk7a2ji',
        'product_id'     => 'prod_TcJ65297HW99bE',
        'price_ids'      => [
            'aud' => 'AUAI02M00O-20',       // AU
            'nzd' => 'NZAI02M00O-20',   // NZ
            'gbp' => 'UKAI02M00O-20',   // UK
        ],
    ],
    'us' => [
        'countries'      => ['us', 'ca'],
        'webhook_secret' => 'whsec_IrotZUi7sJuxgWitKQjYMDP23bW7OyNj',
        'make_url'       => 'https://hook.us1.make.com/6vloshre04tb1xtkjhgawblx2jk7a2ji',
        'product_id'     => 'prod_TcJeLZeufWMSIw',
        'price_ids'      => [
            'usd' => 'price_1Sf516I6vmxJT6OmVI1bPAxe',   // US 
            'cad' => 'price_1Sf516I6vmxJT6Omxjnt0nVP',   // CA
        ],
    ],
    'th' => [
        'countries'      => ['th'],
        'webhook_secret' => 'whsec_9BFZpnul9OIK73w9llqNI7SmgP22utpS',
        'make_url'       => 'https://hook.us1.make.com/6vloshre04tb1xtkjhgawblx2jk7a2ji',
        'product_id'     => 'prod_TcJuebWo6cwN5q',
        'price_ids'      => [
            'thb' => 'THAI02M00O-20',   // TH
        ],
    ],
];

// Build a country → account lookup table from the config above.
// e.g. 'nz' → 'au',  'ca' → 'us',  'th' → 'th'
$COUNTRY_TO_ACCOUNT = [];
foreach ($STRIPE_ACCOUNTS as $acctKey => $acctCfg) {
    foreach (($acctCfg['countries'] ?? []) as $cc) {
        $COUNTRY_TO_ACCOUNT[strtolower($cc)] = $acctKey;
    }
}
// ───────────────────────────────────────────────────────────────────────────

/**
 * Always reply HTTP 200 + JSON to Stripe so it does NOT retry.
 */
function respondOk(string $message = 'ok'): void
{
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'message' => $message]);
    exit;
}

/**
 * Verify Stripe signature against the raw payload.
 * Prefers the official Stripe PHP SDK; falls back to manual HMAC-SHA256.
 * Returns the parsed event as an associative array, or exits (200) on failure.
 */
function verifyAndParseEvent(string $payload, string $sigHeader, string $secret): array
{
    if (empty($sigHeader)) {
        error_log('[stripe_webhook] Missing Stripe-Signature header');
        respondOk('missing signature');
    }

    // Preferred path: Stripe SDK
    if (class_exists('\Stripe\Webhook')) {
        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
            return json_decode(json_encode($event), true); // cast to array for uniform access
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            error_log('[stripe_webhook] Invalid signature (SDK): ' . $e->getMessage());
            respondOk('invalid signature');
        }
    }

    // Fallback: manual HMAC-SHA256
    $parts = [];
    foreach (explode(',', $sigHeader) as $part) {
        [$k, $v] = array_pad(explode('=', $part, 2), 2, '');
        $parts[$k] = $v;
    }
    $timestamp     = $parts['t']  ?? '';
    $givenSig      = $parts['v1'] ?? '';
    $signedPayload = $timestamp . '.' . $payload;
    $expectedSig   = hash_hmac('sha256', $signedPayload, $secret);

    if (!hash_equals($expectedSig, $givenSig)) {
        error_log('[stripe_webhook] Signature mismatch (manual)');
        respondOk('invalid signature');
    }

    return json_decode($payload, true) ?: [];
}

try {
    // ── 0. Resolve account config (accepts account key OR country code) ────
    // Examples: ?account=au | ?account=nz | ?account=uk → 'au' account
    //           ?account=us | ?account=ca              → 'us' account
    //           ?account=th                            → 'th' account
    $requested = strtolower(trim($_GET['account'] ?? 'au'));
    $country   = $requested;

    if (isset($STRIPE_ACCOUNTS[$requested])) {
        // Direct account key — use as-is
        $account = $requested;
    } elseif (isset($COUNTRY_TO_ACCOUNT[$requested])) {
        // Country code — map to parent account
        $account = $COUNTRY_TO_ACCOUNT[$requested];
    } else {
        error_log('[stripe_webhook] Unknown account/country "' . $requested . '" — falling back to au');
        $account = 'au';
        $country = 'au';
    }

    $accountCfg    = $STRIPE_ACCOUNTS[$account];
    $webhookSecret = $accountCfg['webhook_secret'];
    $makeUrl       = $accountCfg['make_url'];
    $productId     = $accountCfg['product_id'] ?? '';
    $priceIds      = $accountCfg['price_ids'] ?? []; // currency => price_id

    // Reverse map: price_id => currency (used to derive country from the matched line)
    $priceIdToCurrency = array_flip($priceIds);

    // ── 1. Read raw payload + verify Stripe signature ───────────────────────
    $payload   = file_get_contents('php://input');
    $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
    $event     = verifyAndParseEvent($payload, $sigHeader, $webhookSecret);

    $eventType = $event['type'] ?? '';
    $dataObj   = $event['data']['object'] ?? [];
    error_log('[stripe_webhook][' . $account . '] Received event: ' . $eventType);

    // ── 2. Branch by event type (ignore everything else) ────────────────────
    $stripeID       = '';
    $customerEmail  = '';
    $shopName       = '';
    $productMatch   = false;
    $matchedId      = '';
    $matchedCurrency = '';

    /**
     * Helper: does this ID match one of our configured price_ids?
     * Returns the currency when a price_id matches, or null if no match.
     * NOTE: product_id alone is NOT a valid match — only explicitly listed
     * price_ids are accepted, otherwise any new/legacy price under the same
     * Stripe Product would leak through to Make.com.
     */
    $matchAgainstConfig = function (string $id) use ($priceIdToCurrency) {
        if ($id === '') return null;
        if (isset($priceIdToCurrency[$id])) return $priceIdToCurrency[$id]; // price match
        return null;
    };

    if ($eventType === 'checkout.session.completed') {
        // ── Flow A: one-time Checkout ───────────────────────────────────────
        $paymentStatus = $dataObj['payment_status'] ?? '';
        if ($paymentStatus !== 'paid') {
            error_log('[stripe_webhook][' . $account . '] Checkout not paid: ' . $paymentStatus);
            respondOk('checkout not paid');
        }

        // Checkout.Session payload does NOT include line_items by default.
        // Rely on metadata set by your app when creating the Session, plus
        // the `currency` on the Session itself as a fallback signal.
        $metadata = $dataObj['metadata'] ?? [];
        foreach (['price_id'] as $k) {
            $id = trim($metadata[$k] ?? '');
            $hit = $matchAgainstConfig($id);
            if ($hit !== null) {
                $productMatch    = true;
                $matchedId       = $id;
                $matchedCurrency = $hit;
                break;
            }
        }

        // If metadata didn't identify which currency, fall back to Session.currency
        if ($productMatch && $matchedCurrency === '') {
            $matchedCurrency = strtolower(trim($dataObj['currency'] ?? ''));
        }

        $stripeID      = $dataObj['customer'] ?? '';
        $customerEmail = $dataObj['customer_details']['email'] ?? $dataObj['customer_email'] ?? '';
        $shopName      = trim((string)($dataObj['customer_details']['name'] ?? $dataObj['customer_name'] ?? ''));

    } elseif ($eventType === 'invoice.paid') {
        // ── Flow B: subscription / invoice ──────────────────────────────────
        $status = $dataObj['status'] ?? '';
        if ($status !== 'paid') {
            error_log('[stripe_webhook][' . $account . '] Invoice not paid: ' . $status);
            respondOk('invoice not paid');
        }

        // Loop invoice line items. Prefer price.id (currency-specific); then
        // price.product; then metadata overrides.
        $lineItems = $dataObj['lines']['data'] ?? [];
        foreach ($lineItems as $li) {
            $price  = $li['price'] ?? [];
            $liMeta = $li['metadata'] ?? [];

            // Only consider price_id candidates — product_id alone must
            // never pass the filter (see $matchAgainstConfig).
            $candidates = array_filter([
                $price['id']            ?? null,
                $liMeta['price_id']     ?? null,
            ]);

            foreach ($candidates as $cid) {
                $hit = $matchAgainstConfig($cid);
                if ($hit !== null) {
                    $productMatch    = true;
                    $matchedId       = $cid;
                    $matchedCurrency = $hit !== '' ? $hit : strtolower($price['currency'] ?? $li['currency'] ?? '');
                    break 2;
                }
            }
        }

        // Last-ditch fallback: invoice-level currency
        if ($productMatch && $matchedCurrency === '') {
            $matchedCurrency = strtolower(trim($dataObj['currency'] ?? ''));
        }

        $stripeID      = $dataObj['customer'] ?? '';
        $customerEmail = $dataObj['customer_email'] ?? $dataObj['customer_details']['email'] ?? '';
        $shopName      = trim((string)($dataObj['customer_name'] ?? $dataObj['customer_details']['name'] ?? ''));

    } else {
        // Not an event we care about — ack and exit
        error_log('[stripe_webhook][' . $account . '] Event ignored: ' . $eventType);
        respondOk('event ignored: ' . $eventType);
    }

    // ── 3. Guard: product ID must be in the account's allowed list ──────────
    if (!$productMatch) {
        error_log('[stripe_webhook][' . $account . '] product_id not in allowed list (event: ' . $eventType . ', stripeID: ' . $stripeID . ')');
        respondOk('product mismatch');
    }
    error_log('[stripe_webhook][' . $account . '] Product matched: ' . $matchedId . ' (currency: ' . $matchedCurrency . ')');

    // ── 4. Guard: required fields present ───────────────────────────────────
    if (empty($stripeID) || empty($customerEmail)) {
        error_log('[stripe_webhook][' . $account . '] Missing fields — stripeID="' . $stripeID . '", email="' . $customerEmail . '"');
        respondOk('missing fields');
    }

    error_log('[stripe_webhook][' . $account . '] Passed filters — event: ' . $eventType . ' | stripeID: ' . $stripeID . ' | email: ' . $customerEmail);

    // ── 5. Forward to Make.com via cURL ─────────────────────────────────────
    $makePayload = json_encode([
        'stripeID'      => $stripeID,
        'customerEmail' => $customerEmail,
        'shopName'      => $shopName,         // Stripe customer_name (e.g. "Sawad home spa")
        'account'       => $account,          // Stripe account: au | us | th
        'country'       => $country,          // country from URL: au|nz|uk|us|ca|th
        'currency'      => $matchedCurrency,  // currency of the matched price: aud|nzd|gbp|usd|cad|thb
        'priceId'       => $matchedId,        // the Stripe Product/Price ID that matched
    ]);

    $ch = curl_init($makeUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $makePayload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($makePayload),
        ],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $makeResponse = curl_exec($ch);
    $curlError    = curl_error($ch);
    $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlError) {
        error_log('[stripe_webhook] cURL error to Make.com: ' . $curlError);
        respondOk('forwarded with curl error');
    }
    if ($httpCode < 200 || $httpCode >= 300) {
        error_log('[stripe_webhook] Make.com responded ' . $httpCode . ': ' . $makeResponse);
        respondOk('make responded ' . $httpCode);
    }

    error_log('[stripe_webhook][' . $account . '] Forwarded to Make.com OK — event: ' . $eventType . ' | stripeID: ' . $stripeID);
    respondOk('forwarded to make');

} catch (Throwable $e) {
    // Catch-all — still reply 200 so Stripe does not retry.
    error_log('[stripe_webhook] Unhandled error: ' . $e->getMessage());
    respondOk('internal error');
}
