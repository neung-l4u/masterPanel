<?php
/**
 * addProductStripeUS — Create Stripe Product + Prices (temporary tool)
 * ---------------------------------------------------------------------------
 * Flow:
 *   1. Create ONE Product in Stripe            → returns stripe_product_id
 *   2. Create MULTIPLE Prices for that product → one per (currency × type × interval)
 *
 * The Stripe Secret Key is provided by the caller per-request (never stored).
 * All calls go directly to Stripe's REST API via cURL (no SDK dependency).
 *
 * Request  (POST, application/json):
 * {
 *   "secret_key": "sk_live_... or sk_test_...",
 *   "product": { "name": "MAIVB02M00 Massage AI + Visibility Boost - No Contract",
 *                "code": "MAIVB02M00" },
 *   "prices": [
 *     { "lookup_key":"USMAIVB02M00O-27", "currency":"usd", "type":"onetime",
 *       "amount":449.00 },
 *     { "lookup_key":"USMAIVB02M00S-27", "currency":"usd", "type":"subscription",
 *       "amount":449.00, "interval":"month", "interval_count":1 },
 *     ...
 *   ]
 * }
 *
 * Response (JSON): see final echo — { success, product{}, prices[] }
 * ---------------------------------------------------------------------------
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

/** Send a JSON response and stop. */
function respond(int $httpCode, array $body): void
{
    http_response_code($httpCode);
    echo json_encode($body, JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Call the Stripe REST API.
 * @param string $method  HTTP method (GET|POST)
 * @param string $path    e.g. "products" or "prices"
 * @param array  $form    form-encoded body (Stripe uses application/x-www-form-urlencoded)
 * @param string $secret  Stripe secret key
 * @return array [httpCode, decodedBody]
 */
function stripeCall(string $method, string $path, array $form, string $secret): array
{
    $ch = curl_init('https://api.stripe.com/v1/' . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $secret,
            'Content-Type: application/x-www-form-urlencoded',
        ],
        CURLOPT_TIMEOUT        => 30,
    ]);
    if ($method === 'POST') {
        // Stripe expects PHP-style bracket nesting for nested params (e.g. recurring[interval]).
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return [0, ['error' => ['message' => 'cURL error: ' . $err]]];
    }
    return [$code, json_decode($raw, true) ?: []];
}

// ─── Parse & validate input ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['success' => false, 'error' => 'POST only']);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    respond(400, ['success' => false, 'error' => 'Invalid JSON body']);
}

$secret  = trim($input['secret_key'] ?? '');
$product = $input['product'] ?? null;
$prices  = $input['prices'] ?? null;

if ($secret === '' || strpos($secret, 'sk_') !== 0) {
    respond(400, ['success' => false, 'error' => 'Missing or invalid secret_key (must start with sk_)']);
}
if (!is_array($product) || empty($product['name'])) {
    respond(400, ['success' => false, 'error' => 'product.name is required']);
}
if (!is_array($prices) || count($prices) === 0) {
    respond(400, ['success' => false, 'error' => 'prices[] is required and must be non-empty']);
}

// ─── Step 1: Create the Product ──────────────────────────────────────────────
$productForm = ['name' => $product['name']];
if (!empty($product['code'])) {
    $productForm['metadata']['code'] = $product['code'];
}

[$pCode, $pBody] = stripeCall('POST', 'products', $productForm, $secret);
if ($pCode !== 200 || empty($pBody['id'])) {
    respond($pCode ?: 502, [
        'success' => false,
        'stage'   => 'create_product',
        'error'   => $pBody['error']['message'] ?? 'Failed to create product',
    ]);
}
$productId = $pBody['id'];

// ─── Step 2: Create all Prices ───────────────────────────────────────────────
// PHP has no Promise.all; requests run sequentially. For a temp tool with a
// handful of prices this is fine. Any single failure is reported but does NOT
// roll back already-created prices (Stripe prices cannot be deleted, only
// archived) — the product id is returned so the caller can inspect/clean up.
$results = [];
$hasError = false;

foreach ($prices as $i => $p) {
    $currency = strtolower(trim($p['currency'] ?? ''));
    $type     = strtolower(trim($p['type'] ?? 'onetime'));
    $amount   = $p['amount'] ?? null;
    $lookup   = trim($p['lookup_key'] ?? '');

    if ($currency === '' || $amount === null) {
        $results[] = [
            'lookup_key' => $lookup, 'currency' => $currency, 'type' => $type,
            'success' => false, 'error' => 'currency and amount are required',
        ];
        $hasError = true;
        continue;
    }

    // Stripe amounts are in the smallest currency unit (cents). All target
    // currencies here (usd, cad) are 2-decimal, so ×100.
    $unitAmount = (int) round(((float) $amount) * 100);

    $priceForm = [
        'product'     => $productId,
        'currency'    => $currency,
        'unit_amount' => $unitAmount,
    ];
    if ($lookup !== '') {
        $priceForm['lookup_key'] = $lookup;
        // Allow re-running: reassign the lookup_key if it already exists elsewhere.
        $priceForm['transfer_lookup_key'] = 'true';
    }
    if ($type === 'subscription') {
        $priceForm['recurring'] = [
            'interval'       => $p['interval'] ?? 'month',
            'interval_count' => (int) ($p['interval_count'] ?? 1),
        ];
    }

    [$prCode, $prBody] = stripeCall('POST', 'prices', $priceForm, $secret);

    if ($prCode === 200 && !empty($prBody['id'])) {
        $results[] = [
            'lookup_key'      => $lookup,
            'stripe_price_id' => $prBody['id'],
            'currency'        => $currency,
            'type'            => $type,
            'success'         => true,
        ];
    } else {
        $results[] = [
            'lookup_key' => $lookup,
            'currency'   => $currency,
            'type'       => $type,
            'success'    => false,
            'error'      => $prBody['error']['message'] ?? 'Failed to create price',
        ];
        $hasError = true;
    }
}

// ─── Response ────────────────────────────────────────────────────────────────
respond(200, [
    'success' => !$hasError,
    'product' => [
        'stripe_product_id' => $productId,
        'code'              => $product['code'] ?? ($product['name'] ?? ''),
        'name'              => $product['name'],
    ],
    'prices'  => $results,
]);
