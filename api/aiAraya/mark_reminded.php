<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

// ─── CONFIG ────────────────────────────────────────────────────────────────
// Endpoint สำหรับให้ Make.com เรียกหลังส่งเมลสำเร็จ เพื่อ mark reminderStep ของ order นั้น
// เรียกได้ทั้ง GET ?id=123&step=1  หรือ POST JSON { "id": 123, "step": 1 }
// step เป็น optional (default = 1)
// ───────────────────────────────────────────────────────────────────────────

try {
    require_once '../../assets/db/db.php';
    require_once '../../assets/db/initDB.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => ["code" => 500, "message" => "DB init failed: " . $e->getMessage()],
        "data"   => null
    ]);
    exit;
}

// ── 1. อ่าน input จาก GET / POST (form) / POST (JSON body) ─────────────────
$input = [];
$raw   = file_get_contents('php://input');
if (!empty($raw)) {
    $json = json_decode($raw, true);
    if (is_array($json)) {
        $input = $json;
    }
}

$id   = $_GET['id']   ?? $_POST['id']   ?? $input['id']   ?? null;
$step = $_GET['step'] ?? $_POST['step'] ?? $input['step'] ?? 1;

// ── 2. Validate ─────────────────────────────────────────────────────────────
if ($id === null || !is_numeric($id) || (int)$id <= 0) {
    http_response_code(400);
    echo json_encode([
        "status" => ["code" => 400, "message" => "missing or invalid id"],
        "data"   => null
    ]);
    exit;
}

if (!is_numeric($step)) {
    http_response_code(400);
    echo json_encode([
        "status" => ["code" => 400, "message" => "invalid step"],
        "data"   => null
    ]);
    exit;
}

$id   = (int)$id;
$step = (int)$step;

// ── 3. UPDATE ───────────────────────────────────────────────────────────────
try {
    $db->query(
        'UPDATE order_tracking SET reminderStep = ? WHERE id = ?',
        $step,
        $id
    );

    $affected = $db->affectedRows();

    echo json_encode([
        "status" => [
            "code"    => 200,
            "message" => $affected > 0 ? "marked" : "no_row_updated"
        ],
        "data" => [
            "id"           => $id,
            "reminderStep" => $step,
            "affectedRows" => $affected,
        ]
    ]);

} catch (Throwable $e) {
    error_log('[mark_reminded] UPDATE failed for id=' . $id . ' — ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "status" => ["code" => 500, "message" => "DB error: " . $e->getMessage()],
        "data"   => null
    ]);
}
