<?php
/**
 * JWT Token Verification Middleware
 * include ไฟล์นี้ใน API ที่ต้องการ protect
 *
 * อ่าน Authorization: Bearer <token> จาก header
 * ถ้า valid จะ set $authPartner = [ partner_id, client_id, scopes ]
 * ถ้า invalid จะ return 401 และ exit ทันที
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

$JWT_SECRET = 'LOCAL_FOR_YOU_STRONG_RANDOM_SECRET_KEY_BY_MARK';

function getAuthToken() {
    $headers = getallheaders();
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'authorization') {
            if (preg_match('/Bearer\s+(.+)/i', $value, $matches)) {
                return $matches[1];
            }
        }
    }
    return null;
}

$token = getAuthToken();

if (empty($token)) {
    http_response_code(401);
    echo json_encode(["error" => "Authorization token is required"]);
    exit();
}

try {
    $decoded    = JWT::decode($token, new Key($JWT_SECRET, 'HS256'));
    $authPartner = [
        'partnerID'   => $decoded->partnerID,
        'partnerName' => $decoded->partnerName,
        'partner_id'  => $decoded->partner_id,
        'scopes'      => (array)$decoded->scopes,
    ];
} catch (ExpiredException $e) {
    http_response_code(401);
    echo json_encode(["error" => "Token has expired"]);
    exit();
} catch (SignatureInvalidException $e) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid token signature"]);
    exit();
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid token"]);
    exit();
}
