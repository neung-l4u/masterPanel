<?php
/**
 * Records a pre sign-up and returns the link to hand to the customer.
 *
 * A sales rep fills this in on the customer's behalf, so the row is the only
 * copy of what was said on the call. It is written in one INSERT and the
 * reference is derived from its id, which means the link cannot exist without
 * the row behind it.
 */

global $db;
session_start();
header('Content-Type: application/json; charset=utf-8');
include '../assets/db/db.php';
include '../assets/db/initDB.php';

$result = [
    'result' => 'error',
    'msg'    => '',
    'ref'    => '',
    'link'   => '',
];

$payload = !empty($_POST['payload']) ? (string) $_POST['payload'] : '';
$data    = json_decode($payload, true);

if (!is_array($data)) {
    http_response_code(400);
    $result['msg'] = 'No form data received.';
    echo json_encode($result);
    exit;
}

$field = static function (string $key) use ($data): string {
    return trim((string) ($data[$key] ?? ''));
};

// Server-side check of the same fields the form marks required. The browser
// check is for the rep's benefit; this one is what actually holds, because a
// row missing a shop name or a way to contact the shop is not a usable lead.
$required = [
    'shopName'       => 'Shop name',
    'country'        => 'Country',
    'shopType'       => 'Shop type',
    'customerType'   => 'Customer type',
    'leadSource'     => 'Lead source',
    'contactChannel' => 'Contact channel',
];

$missing = [];
foreach ($required as $key => $label) {
    if ($field($key) === '') {
        $missing[] = $label;
    }
}

// Whoever picks this lead up next has to be able to reach the shop, so there
// must be at least one way to do that. Which way is not prescribed: a chat
// lead may arrive through Respond.io, Facebook, TikTok or Line, and a cold
// call has a phone number and often nothing else. Requiring a Respond.io link
// for every Chat lead would block the platforms that do not produce one.
$contactPoints = array_filter([
    $field('mobNumber'),
    $field('shopNumber'),
    $field('email'),
    $field('conversationLink'),
]);

if (!$contactPoints) {
    $missing[] = 'at least one way to reach the shop (mobile, shop number, email or conversation link)';
}

$email = $field('email');
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $missing[] = 'A valid email';
}

if ($missing) {
    http_response_code(422);
    $result['msg'] = 'Please complete: ' . implode(', ', $missing) . '.';
    echo json_encode($result);
    exit;
}

$db->query(
    'INSERT INTO `presignup` (
        `shopName`, `country`, `shopType`, `customerType`,
        `mobNumber`, `shopNumber`, `websiteLink`,
        `nickname`, `firstName`, `lastName`, `email`,
        `streetAddress`, `citySuburb`, `state`, `postalCode`,
        `leadSource`, `contactChannel`, `conversationLink`, `salesRep`,
        `dataLogs`
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    [
        $field('shopName'),
        $field('country'),
        $field('shopType'),
        $field('customerType'),
        $field('mobNumber'),
        $field('shopNumber'),
        $field('websiteLink'),
        $field('nickname'),
        $field('firstName'),
        $field('lastName'),
        $email,
        $field('streetAddress'),
        $field('citySuburb'),
        $field('state'),
        $field('postalCode'),
        $field('leadSource'),
        $field('contactChannel'),
        $field('conversationLink'),
        $field('salesRep'),
        $payload,
    ]
);

$id = (int) $db->lastInsertID();

if ($id <= 0) {
    http_response_code(500);
    $result['msg'] = 'Could not save this pre sign-up. Please try again.';
    echo json_encode($result);
    exit;
}

$configPath = __DIR__ . '/../assets/config.php';
$config     = is_file($configPath) ? require $configPath : [];
$baseUrl    = rtrim((string) ($config['public_base_url'] ?? ''), '/');

// prelead-0000001. Padded to 7 so the reference a customer is read over the
// phone keeps its shape, and widening past 7 digits still works.
$ref = 'prelead-' . str_pad((string) $id, 7, '0', STR_PAD_LEFT);

$result['result'] = 'success';
$result['msg']    = 'Pre sign-up saved.';
$result['ref']    = $ref;
$result['link']   = ($baseUrl !== '' ? $baseUrl : '') . '/' . $ref;

echo json_encode($result, JSON_UNESCAPED_UNICODE);
