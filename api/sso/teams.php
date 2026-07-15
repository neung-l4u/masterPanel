<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';

header('Content-Type: application/json');

$raw  = file_get_contents("php://input");
$data = json_decode($raw, true) ?: [];

// Shared-secret gate (same pattern as index.php). Required here: this endpoint
// is only ever called server-to-server by the L4U-Docs proxy.
// Shared secret: env first, then a hardcoded fallback (no env loader under plain
// Apache). Must match L4U-Docs auth-config $AUTH_SECRET fallback.
$expectedSecret = getenv('SSO_SHARED_SECRET') ?: 'd55ed0906f301cfd13f91dd3fda7786dc568322ec75c0019';
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
