<?php
/**
 * Returns a pre sign-up's collected values as JSON, for the signup form to
 * fill itself in with.
 *
 * Reached as lookup.php?ref=0000001 (or prelead-0000001, or 1) from
 * modules/signup when it is opened with ?prelead=0000001.
 *
 * The values travel in this response rather than in the link, so the link the
 * customer is sent stays short, their own details never appear in their
 * address bar or in anyone's browser history, and a field added to the form
 * later needs no change to the link or to this endpoint's callers.
 */

global $db;
header('Content-Type: application/json; charset=utf-8');

// The signup form is served from the same host in production, but allow a
// cross-origin read so it keeps working if either side moves. Only the
// pre-signup values are exposed and only to someone who already holds the
// reference, which is the same thing the link itself hands over.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

include '../assets/db/db.php';
include '../assets/db/initDB.php';

$ref = isset($_GET['ref']) ? trim((string) $_GET['ref']) : '';

// prelead-0000001, 0000001, or 1. Anything else is not a reference.
if (!preg_match('/^(?:prelead-)?0*(\d+)$/i', $ref, $matches)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'invalid_ref']);
    exit;
}

$id  = (int) $matches[1];
$row = $db->query(
    'SELECT `id`, `shopName`, `country`, `shopType`, `customerType`,
            `mobNumber`, `shopNumber`, `websiteLink`,
            `nickname`, `firstName`, `lastName`, `email`,
            `streetAddress`, `citySuburb`, `state`, `postalCode`,
            `leadSource`, `contactChannel`, `status`
       FROM `presignup`
      WHERE `id` = ?',
    [$id]
)->fetchArray();

if (!$row) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'not_found']);
    exit;
}

if (($row['status'] ?? '') === 'cancelled') {
    http_response_code(410);
    echo json_encode(['success' => false, 'error' => 'cancelled']);
    exit;
}

$ref = 'prelead-' . str_pad((string) $row['id'], 7, '0', STR_PAD_LEFT);

// Keyed by the **element ids** on modules/signup/index.php, so the signup form
// can apply each value without knowing anything about this module's columns.
// These were checked against that form; an id that does not exist there would
// silently fill nothing.
//
// Three fields are deliberately left out, and are returned under `context`
// instead so the form can show them without filling them in:
//
//  - country, because the signup form offers six countries where the Lead
//    board knows twenty-four, and that mismatch is still open with the team.
//  - city and state, because on the signup form they are a dependent chain:
//    choosing a state populates the city dropdown, and `#city` is only a
//    hidden mirror of it. Writing to them out of order would desync the two.
$prefill = [
    'shopName'         => (string) $row['shopName'],
    'first_name'       => (string) $row['firstName'],
    'last_name'        => (string) $row['lastName'],
    'email'            => (string) $row['email'],
    'mobile'           => (string) $row['mobNumber'],
    'shopNumber'       => (string) $row['shopNumber'],
    'webURL'           => (string) $row['websiteLink'],
    'streetAddress1'   => (string) $row['streetAddress'],
    'zip'              => (string) $row['postalCode'],
    'formCustomerType' => (string) $row['customerType'],
];

// An empty field is left out rather than sent as '': the signup form should
// leave its own default in place, not blank it.
$prefill = array_filter($prefill, static function (string $value): bool {
    return trim($value) !== '';
});

echo json_encode([
    'success' => true,
    'ref'     => $ref,
    'prefill' => $prefill,
    // Collected for the sales team, or left for the customer to choose, but
    // not written into the signup form. See the note above `$prefill`.
    'context' => [
        'country'        => (string) $row['country'],
        'citySuburb'     => (string) $row['citySuburb'],
        'state'          => (string) $row['state'],
        'shopType'       => (string) $row['shopType'],
        'nickname'       => (string) $row['nickname'],
        'leadSource'     => (string) $row['leadSource'],
        'contactChannel' => (string) $row['contactChannel'],
    ],
], JSON_UNESCAPED_UNICODE);
