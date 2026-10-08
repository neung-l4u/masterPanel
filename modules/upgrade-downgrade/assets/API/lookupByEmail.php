<?php
/**
 * Customer-mode shop lookup: GET ?country=TH&email=owner@shop.com
 *
 * Read-only. Validates input, calls the lookup, encodes JSON. No business
 * logic lives here.
 *
 * SECURITY: this endpoint is unauthenticated -- it sits behind a public
 * customer-facing form -- and it returns an owner name and phone number to
 * anyone who supplies a matching email. That is an email enumeration surface.
 * It is narrowed deliberately: only the fields the form fills are returned,
 * the email is never echoed back, a miss is indistinguishable from malformed
 * input in shape, and every query is logged with its outcome.
 */

date_default_timezone_set("Asia/Bangkok");

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
// No Access-Control-Allow-Origin: this is called same-origin from the form,
// unlike the existing read-only board caches which are shared more widely.

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    echo json_encode(['found' => false, 'reason' => 'method not allowed']);
    exit;
}

$country = isset($_GET['country']) ? trim((string) $_GET['country']) : '';
$email   = isset($_GET['email'])   ? trim((string) $_GET['email'])   : '';

/** Log every call so enumeration attempts are visible after the fact. */
function udmLogLookup(string $country, string $email, string $outcome): void
{
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0700, true);
    }
    $line = sprintf(
        "[%s] %s country=%s email=%s outcome=%s\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'] ?? '-',
        $country !== '' ? $country : '-',
        $email !== '' ? substr(hash('sha256', strtolower($email)), 0, 12) : '-',
        $outcome
    );
    @file_put_contents($logDir . '/lookup.log', $line, FILE_APPEND | LOCK_EX);
}

if ($country === '' || $email === '') {
    udmLogLookup($country, $email, 'missing-parameter');
    echo json_encode(['found' => false, 'reason' => 'country and email are both required']);
    exit;
}

if (strlen($country) !== 2) {
    udmLogLookup($country, $email, 'bad-country');
    echo json_encode(['found' => false, 'reason' => 'country must be a 2-letter code']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    udmLogLookup($country, $email, 'bad-email');
    echo json_encode(['found' => false, 'reason' => 'not a valid email address']);
    exit;
}

require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../db/initDB.php';
require_once __DIR__ . '/../../sync/mirrorMatch.php';
require_once __DIR__ . '/../../models/shopLookup.php';

if (!isset($db)) {
    http_response_code(503);
    udmLogLookup($country, $email, 'db-unavailable');
    echo json_encode(['found' => false, 'reason' => 'lookup temporarily unavailable']);
    exit;
}

try {
    $result = udmLookupShopByEmail($db, $country, $email);
} catch (Throwable $e) {
    http_response_code(503);
    udmLogLookup($country, $email, 'error');
    echo json_encode(['found' => false, 'reason' => 'lookup temporarily unavailable']);
    exit;
}

udmLogLookup($country, $email, $result['found'] ? 'found' : 'not-found');
echo json_encode($result, JSON_UNESCAPED_UNICODE);
