<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';

header('Content-Type: application/json');

$raw  = file_get_contents("php://input");
$data = json_decode($raw, true) ?: [];

// Shared-secret gate (same pattern as index.php). Required here: this endpoint
// is only ever called server-to-server by the L4U-Docs proxy.
$expectedSecret = getenv('SSO_SHARED_SECRET') ?: '';
if ($expectedSecret === '' || !isset($data['secret']) || !hash_equals($expectedSecret, (string)$data['secret'])) {
    echo json_encode(["status" => false, "msg" => "Unauthorized"]);
    exit;
}

$rows = $db->query('SELECT `id`, `name`, `fullName` FROM `Team` ORDER BY `idx`')->fetchAll();
$teams = [];
foreach ($rows as $r) {
    $teams[] = ['id' => (string)$r['id'], 'name' => $r['name'], 'fullName' => $r['fullName']];
}
echo json_encode(["status" => true, "teams" => $teams]);
