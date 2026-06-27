<?php
ob_start();
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';
ob_clean();
header('Content-Type: application/json');

$invoice_id = isset($_POST['invoice_id']) ? (int)$_POST['invoice_id'] : 0;
$field      = $_POST['field'] ?? '';
$value      = trim($_POST['value'] ?? '');

$customerFields = ['name', 'address', 'taxNumber', 'email', 'phone'];
$bankFields     = ['bankName', 'bankNumber', 'bankAccName'];
$invoiceFields  = ['invoiceID'];
$allowedFields  = array_merge($customerFields, $bankFields, $invoiceFields);

if (!$invoice_id || !in_array($field, $allowedFields)) {
    echo json_encode(['success' => false, 'message' => 'Invalid params']);
    exit;
}

if (in_array($field, $invoiceFields)) {
    $db->query('UPDATE `thInvoice` SET `' . $field . '` = ? WHERE `id` = ?', $value, $invoice_id);
} else {
    $rows = $db->query(
        'SELECT `customer_id` FROM `thInvoice` WHERE `id` = ? LIMIT 1',
        $invoice_id
    )->fetchAll();

    if (empty($rows[0])) {
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        exit;
    }

    $customerId = $rows[0]['customer_id'];
    $db->query('UPDATE `thCustomer` SET `' . $field . '` = ? WHERE `id` = ?', $value, $customerId);
}

echo json_encode(['success' => true]);
?>
