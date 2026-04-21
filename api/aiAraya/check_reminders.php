<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

// ─── CONFIG ────────────────────────────────────────────────────────────────
// How many hours an order must be pending before the first reminder fires.
const REMINDER_AFTER_HOURS = 21;
// const REMINDER_AFTER_MINUTES = 5;
// ───────────────────────────────────────────────────────────────────────────

try {
    require_once '../../assets/db/db.php';
    require_once '../../assets/db/initDB.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => ["code" => 500, "message" => "DB init failed: " . $e->getMessage()],
        "data" => null
    ]);
    exit;
}

try {
    // SELECT: pending orders older than REMINDER_AFTER_HOURS that
    //         have not received any reminder yet (reminderStep = 0).
    // Make.com จะเป็นคน iterate, ส่งเมล, และเรียก mark_reminded.php เพื่อ mark reminderStep=1
    $rows = $db->query(
        "SELECT id, shopName, stripeID, customerEmail
         FROM order_tracking
         WHERE statusUser = 'pending'
           AND reminderStep = 0
           AND TIMESTAMPDIFF(HOUR, startedAt, NOW()) >= ?",
        REMINDER_AFTER_HOURS
    )->fetchAll();

    // normalize id เป็น int เผื่อ Make.com ใช้ง่าย
    foreach ($rows as &$r) {
        $r['id'] = (int)$r['id'];
    }
    unset($r);

    echo json_encode([
        "status" => [
            "code"    => 200,
            "message" => "Reminder check completed"
        ],
        "data" => [
            "count"   => count($rows),
            "pending" => $rows,
        ]
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => [
            "code"    => 500,
            "message" => "DB error: " . $e->getMessage()
        ],
        "data" => null
    ]);
}
