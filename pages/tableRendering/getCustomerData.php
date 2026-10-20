<?php
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';

$draw = (int)($_POST['draw'] ?? 1);
$start = (int)($_POST['start'] ?? 0);
$length = (int)($_POST['length'] ?? 10);
$search = (isset($_POST['search']) && isset($_POST['search']['value'])) ? $_POST['search']['value'] : '';

// Build query
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?";
    $searchTerm = '%' . $search . '%';
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

// Get total records
$countResult = $db->query("SELECT COUNT(*) as cnt FROM thCustomer");
$countRow = $countResult->fetch();
$totalRows = $countRow ? (int)$countRow['cnt'] : 0;

// Get filtered records
if (!empty($params)) {
    $filteredResult = $db->query("SELECT COUNT(*) as cnt FROM thCustomer $where", ...$params);
    $filteredRow = $filteredResult ? $filteredResult->fetch() : null;
    $filteredRows = $filteredRow ? (int)$filteredRow['cnt'] : 0;
} else {
    $filteredRows = $totalRows;
}

// Get data
if (!empty($params)) {
    $rowsResult = $db->query(
        "SELECT id, name, email, phone, type, address, taxNumber FROM thCustomer $where ORDER BY id DESC LIMIT ?, ?",
        ...[...$params, $start, $length]
    );
    $rows = $rowsResult ? $rowsResult->fetchAll() : [];
} else {
    $rowsResult = $db->query(
        "SELECT id, name, email, phone, type, address, taxNumber FROM thCustomer ORDER BY id DESC LIMIT ?, ?",
        $start, $length
    );
    $rows = $rowsResult ? $rowsResult->fetchAll() : [];
}

$data = [];
if ($rows) {
    foreach ($rows as $row) {
        $data[] = [
            'id' => $row['id'],
            'name' => $row['name'] ?? '-',
            'email' => $row['email'] ?? '-',
            'phone' => $row['phone'] ?? '-',
            'type' => $row['type'] ?? '-',
            'address' => $row['address'] ?? '-'
        ];
    }
}

$response = [
    'draw' => $draw,
    'recordsTotal' => (int)$totalRows,
    'recordsFiltered' => (int)$filteredRows,
    'data' => $data
];

echo json_encode($response);
?>
