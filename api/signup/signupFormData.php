<?php
/**
 * Signup Store API
 * Endpoint: /api/signup/signupFormData.php
 *
 * GET    ?act=getAll                        — ดึงข้อมูลทั้งหมด
 * GET    ?act=getOne&id={id}                — ดึงข้อมูลตาม id
 * GET    ?act=getByCountry&country={code}   — ดึงข้อมูลตาม countryCode
 * POST   act=insert                         — เพิ่มข้อมูลใหม่
 * PUT    (body JSON) act=update, id={id}    — แก้ไขข้อมูล
 * DELETE ?id={id}                           — ลบข้อมูล
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';

date_default_timezone_set("Asia/Bangkok");

$method = $_SERVER['REQUEST_METHOD'];

function respond($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

function getBody() {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $data = $_POST;
    }
    return $data;
}

// ==================== GET ====================
if ($method === 'GET') {
    $act = $_GET['act'] ?? 'getAll';

    if ($act === 'getAll') {
        global $db;
        $rows = $db->query(
            'SELECT l.id, l.dataLogs, l.dataStripe, l.stripeResult, l.dataContract,
                    l.countryCode, l.status, l.test, l.createAt, l.createBy,
                    l.gen_report, l.reported_at
             FROM logssignup l
             WHERE l.deleteStatus = 0
             ORDER BY l.createAt DESC'
        )->fetchAll();

        foreach ($rows as &$row) {
            $row['dataLogs']    = json_decode($row['dataLogs'], true);
            $row['dataStripe']  = json_decode($row['dataStripe'], true);
            $row['stripeResult'] = json_decode($row['stripeResult'], true);
        }

        respond(["success" => true, "total" => count($rows), "data" => $rows]);

    } elseif ($act === 'getOne') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) respond(["success" => false, "message" => "id is required"], 400);

        global $db;
        $rows = $db->query(
            'SELECT id, dataLogs, dataStripe, stripeResult, dataContract,
                    countryCode, status, test, createAt, createBy, gen_report, reported_at
             FROM logssignup WHERE id = ? AND deleteStatus = 0', $id
        )->fetchAll();

        if (empty($rows)) respond(["success" => false, "message" => "Not found"], 404);

        $row = $rows[0];
        $row['dataLogs']    = json_decode($row['dataLogs'], true);
        $row['dataStripe']  = json_decode($row['dataStripe'], true);
        $row['stripeResult'] = json_decode($row['stripeResult'], true);

        respond(["success" => true, "data" => $row]);

    } elseif ($act === 'getByCountry') {
        $country = $_GET['country'] ?? '';
        if (empty($country)) respond(["success" => false, "message" => "country is required"], 400);

        global $db;
        $rows = $db->query(
            'SELECT id, dataLogs, dataStripe, stripeResult, dataContract,
                    countryCode, status, test, createAt, createBy, gen_report, reported_at
             FROM logssignup WHERE countryCode = ? AND deleteStatus = 0 ORDER BY createAt DESC', $country
        )->fetchAll();

        foreach ($rows as &$row) {
            $row['dataLogs']    = json_decode($row['dataLogs'], true);
            $row['dataStripe']  = json_decode($row['dataStripe'], true);
            $row['stripeResult'] = json_decode($row['stripeResult'], true);
        }

        respond(["success" => true, "total" => count($rows), "data" => $rows]);

    } else {
        respond(["success" => false, "message" => "Unknown act: $act"], 400);
    }
}

// ==================== POST (INSERT) ====================
if ($method === 'POST') {
    $body = getBody();
    $act  = $body['act'] ?? '';

    if ($act !== 'insert') respond(["success" => false, "message" => "act must be 'insert'"], 400);

    $dataLogs    = isset($body['dataLogs'])    ? json_encode($body['dataLogs'])    : null;
    $dataStripe  = isset($body['dataStripe'])  ? json_encode($body['dataStripe'])  : null;
    $dataContract = $body['dataContract'] ?? null;
    $countryCode  = $body['countryCode']  ?? null;
    $status       = isset($body['status'])  ? (int)$body['status']  : 1;
    $test         = isset($body['test'])    ? (int)$body['test']    : 0;
    $createBy     = isset($body['createBy']) ? (int)$body['createBy'] : 0;
    $timestamp    = date("Y-m-d H:i:s");

    if (empty($dataLogs) || empty($countryCode)) {
        respond(["success" => false, "message" => "dataLogs and countryCode are required"], 400);
    }

    global $db;
    $db->query(
        'INSERT INTO `logssignup`(`dataLogs`, `dataStripe`, `dataContract`, `countryCode`, `status`, `test`, `createAt`, `createBy`)
         VALUES (?,?,?,?,?,?,?,?)',
        $dataLogs, $dataStripe, $dataContract, $countryCode, $status, $test, $timestamp, $createBy
    );

    $newId = $db->lastInsertID();
    respond(["success" => true, "message" => "Inserted successfully", "id" => $newId], 201);
}

// ==================== PUT (UPDATE) ====================
if ($method === 'PUT') {
    $body = getBody();
    $id   = isset($body['id']) ? (int)$body['id'] : 0;

    if ($id <= 0) respond(["success" => false, "message" => "id is required"], 400);

    $fields = [];
    $params = [];
    $types  = '';

    $allowed = ['dataLogs', 'dataStripe', 'stripeResult', 'dataContract', 'countryCode', 'status', 'test', 'gen_report'];

    foreach ($allowed as $col) {
        if (!array_key_exists($col, $body)) continue;

        $val = $body[$col];

        if (in_array($col, ['dataLogs', 'dataStripe', 'stripeResult']) && is_array($val)) {
            $val = json_encode($val);
        }

        $fields[] = "`$col` = ?";
        $params[]  = $val;
    }

    if (empty($fields)) respond(["success" => false, "message" => "No fields to update"], 400);

    $params[] = $id;
    $sql = 'UPDATE `logssignup` SET ' . implode(', ', $fields) . ' WHERE id = ?';

    global $db;
    $db->query($sql, ...$params);

    respond(["success" => true, "message" => "Updated successfully", "id" => $id]);
}

// ==================== DELETE (Soft Delete) ====================
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id <= 0) {
        $body = getBody();
        $id   = isset($body['id']) ? (int)$body['id'] : 0;
    }

    if ($id <= 0) respond(["success" => false, "message" => "id is required"], 400);

    global $db;
    $rows = $db->query('SELECT id FROM logssignup WHERE id = ? AND deleteStatus = 0', $id)->fetchAll();
    if (empty($rows)) respond(["success" => false, "message" => "Not found"], 404);

    $db->query('UPDATE `logssignup` SET deleteStatus = 1 WHERE id = ?', $id);

    respond(["success" => true, "message" => "Deleted successfully", "id" => $id]);
}

respond(["success" => false, "message" => "Method not allowed"], 405);
