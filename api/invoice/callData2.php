<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';

header('Content-Type: application/json');

$email = $_GET['email'] ?? null;
if (!$email) {
    echo json_encode([
        "status" => [
            "code" => 400, 
            "message" => "Bad Request - Email is required"
        ], 
        "data" => null
    ]);
    exit;
}

$rows = $db->query(
    'SELECT i.`id`, i.`invoiceID`, i.`product`, i.`amount`, i.`status`, i.`createdAt`,
            c.`name`, c.`address`, c.`taxNumber`, c.`type`,
            c.`email` AS customerEmail, c.`phone` AS customerPhone,
            c.`bankName`, c.`bankNumber` AS bankThaiNumber, c.`bankAccName` AS bankThaiName,
            r.`receiptID`, r.`slip`, r.`status` AS receiptStatus
     FROM `thInvoice` i
     JOIN `thCustomer` c ON c.`id` = i.`customer_id`
     LEFT JOIN `thReceipt` r ON r.`id` = (
         SELECT MAX(`id`) FROM `thReceipt` WHERE `invoice_id` = i.`id`
     )
     WHERE c.`email` = ?
     ORDER BY i.`id` DESC',
    $email
)->fetchAll();

if (!$rows || count($rows) == 0) {
    echo json_encode([
        "status" => [
            "code" => 404,
            "message" => "No invoices found for this email"
        ],
        "data" => null
    ]);
    exit;
}

echo json_encode([
    "status" => [
        "code" => 200,
        "message" => "Success"
    ],
    "data" => $rows
]);
