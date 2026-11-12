<?php
/**
 * JSON endpoints. ?act=list | step | history&id=
 * Internal customer data, so the panel's central session is required (same guard as viewcreditmake).
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
if (empty($_SESSION['id']) || ($_SESSION['level'] ?? 99) >= 4) {
    http_response_code(401);
    echo json_encode(['error' => 'ต้องเข้าสู่ระบบก่อน'], JSON_UNESCAPED_UNICODE);
    exit;
}
session_write_close();   // a scan step runs ~20-40s; do not block the user's other tabs
set_time_limit(120);     // a chunk can overrun the 20s budget by up to 3 web timeouts

require_once __DIR__ . '/scan.php';

$act = $_GET['act'] ?? 'list';
try {
    if ($act === 'list') {
        echo json_encode(['status' => cs_status(), 'shops' => cs_read('current.json'), 'errors' => cs_read('errors.json')],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } elseif ($act === 'step') {
        echo json_encode(cs_step(20, ($_GET['start'] ?? '') === '1'), JSON_UNESCAPED_UNICODE);
    } elseif ($act === 'history') {
        echo json_encode(cs_history(preg_replace('/\D/', '', $_GET['id'] ?? '')), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'ไม่รู้จักคำสั่งนี้'], JSON_UNESCAPED_UNICODE);
    }
} catch (Throwable $e) {
    http_response_code(502);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
