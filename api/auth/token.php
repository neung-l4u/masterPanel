<?php
/**
 * Partner Auth — Issue JWT Token
 * POST /api/auth/token.php
 *
 * Body (JSON):
 *   { "partner_id": "...", "partner_secret": "..." }
 *
 * Response:
 *   { "access_token": "...", "token_type": "Bearer", "expires_in": 3600 }
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit();
}

require_once '../../vendor/autoload.php';
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';

use Firebase\JWT\JWT;

date_default_timezone_set("Asia/Bangkok");

$JWT_SECRET  = 'LOCAL_FOR_YOU_STRONG_RANDOM_SECRET_KEY_BY_MARK';
$JWT_EXPIRES = 3600; // 1 hour

$raw  = file_get_contents("php://input");
$body = json_decode($raw, true);

$clientId     = trim($body['partner_id']     ?? '');
$clientSecret = trim($body['partner_secret'] ?? '');

if (empty($clientId) || empty($clientSecret)) {
    http_response_code(400);
    echo json_encode(["error" => "partner_id and partner_secret are required"]);
    exit();
}

$rows = $db->query(
    "SELECT partnerID, partnerName, partner_secret, scopes, status FROM partner WHERE partner_id = ? LIMIT 1",
    $clientId
)->fetchAll();

if (empty($rows)) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
    exit();
}

$partner = $rows[0];

if ($partner['status'] !== 'active') {
    http_response_code(403);
    echo json_encode(["error" => "Partner account is not active"]);
    exit();
}

if (!password_verify($clientSecret, $partner['partner_secret'])) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
    exit();
}

$scopes = json_decode($partner['scopes'], true) ?? [];
$now    = time();

$payload = [
    'iss'         => 'masterPanel',
    'iat'         => $now,
    'exp'         => $now + $JWT_EXPIRES,
    'partnerID'   => $partner['partnerID'],
    'partnerName' => $partner['partnerName'],
    'partner_id'  => $clientId,
    'scopes'      => $scopes,
];

$token = JWT::encode($payload, $JWT_SECRET, 'HS256');

echo json_encode([
    "access_token" => $token,
    "token_type"   => "Bearer",
    "expires_in"   => $JWT_EXPIRES,
]);
