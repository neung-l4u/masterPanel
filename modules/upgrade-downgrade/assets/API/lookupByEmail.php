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
 * It is narrowed deliberately: only the fields the form fills are returned, the
 * email is never echoed back, and every query is logged with its outcome.
 *
 * Note a miss and malformed input are NOT the same shape: a miss is
 * {"found":false} while rejected input carries a "reason". That distinction is
 * deliberate and costs nothing -- every "reason" is a statement about input the
 * caller already controls, so it reveals nothing about the database. The
 * unavoidable disclosure is the found/not-found bit itself.
 *
 * Rate limiting is deliberately not implemented here; see the design doc. With
 * none, an attempt costs about 11ms unthrottled.
 */

date_default_timezone_set("Asia/Bangkok");

// Set FIRST, before any output or include. A PHP warning rendered into the body
// would leak this file's absolute path to an anonymous caller and would also
// make the response invalid JSON, which the form's parse then fails on.
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
// No Access-Control-Allow-Origin: this is called same-origin from the form,
// unlike the existing read-only board caches which are shared more widely.

/**
 * Log every call so enumeration attempts are visible after the fact.
 *
 * The email is hashed: a log of plaintext customer addresses is itself a
 * liability. The hash is for correlating repeated probes, not for
 * confidentiality -- a short unsalted digest of a known candidate address is
 * trivially confirmable, which is why the log must not be publicly readable
 * (see the .htaccess in assets/logs).
 */
function udmLogLookup(string $country, string $email, string $outcome): void
{
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0700, true);
    }

    // Strip everything but letters. Callers reach this function before the
    // country is validated, and an unsanitised newline would let an attacker
    // append a complete forged line -- defeating the one control that exists to
    // detect them.
    $countrySafe = preg_replace('/[^A-Za-z]/', '', $country);
    if ($countrySafe === '' || $countrySafe === null) {
        $countrySafe = '-';
    }

    $line = sprintf(
        "[%s] %s country=%s email=%s outcome=%s\n",
        date('Y-m-d H:i:s'),
        preg_replace('/[^0-9a-fA-F:.]/', '', (string) ($_SERVER['REMOTE_ADDR'] ?? '-')) ?: '-',
        substr($countrySafe, 0, 8),
        $email !== '' ? substr(hash('sha256', strtolower($email)), 0, 12) : '-',
        preg_replace('/[^a-z-]/', '', $outcome)
    );
    @file_put_contents($logDir . '/lookup.log', $line, FILE_APPEND | LOCK_EX);
}

/** Refuse and stop, with a body that never carries internals. */
function udmLookupRefuse(int $status, string $reason, string $country, string $email, string $outcome): void
{
    if ($status !== 200) {
        http_response_code($status);
    }
    udmLogLookup($country, $email, $outcome);
    echo json_encode(['found' => false, 'reason' => $reason]);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    // Logged like any other call: a scripted POST probe should leave a trace.
    udmLookupRefuse(405, 'method not allowed', '', '', 'method-not-allowed');
}

// is_string first: ?country[]=x makes $_GET['country'] an array, and casting
// that emits a warning carrying this file's path.
$country = (isset($_GET['country']) && is_string($_GET['country'])) ? trim($_GET['country']) : '';
$email   = (isset($_GET['email'])   && is_string($_GET['email']))   ? trim($_GET['email'])   : '';

if ($country === '' || $email === '') {
    udmLookupRefuse(200, 'country and email are both required', $country, $email, 'missing-parameter');
}

if (strlen($country) !== 2 || !ctype_alpha($country)) {
    udmLookupRefuse(200, 'country must be a 2-letter code', $country, $email, 'bad-country');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    udmLookupRefuse(200, 'not a valid email address', $country, $email, 'bad-email');
}

// Inside the try: initDB.php constructs the db object, and in PHP 8 mysqli
// throws natively on a connection failure. Left outside, that threw before any
// handler ran and the response became a 200 carrying a stack trace with the
// database host, user and name in it.
try {
    require_once __DIR__ . '/../db/db.php';
    require_once __DIR__ . '/../db/initDB.php';
    require_once __DIR__ . '/../../sync/mirrorMatch.php';
    require_once __DIR__ . '/../../models/shopLookup.php';

    if (!isset($db)) {
        udmLookupRefuse(503, 'lookup temporarily unavailable', $country, $email, 'db-unavailable');
    }

    $result = udmLookupShopByEmail($db, $country, $email);
} catch (Throwable $e) {
    // $e is deliberately not reported to the caller: its message carries file
    // paths and connection details.
    udmLookupRefuse(503, 'lookup temporarily unavailable', $country, $email, 'error');
}

udmLogLookup($country, $email, $result['found'] ? 'found' : 'not-found');
echo json_encode($result, JSON_UNESCAPED_UNICODE);
