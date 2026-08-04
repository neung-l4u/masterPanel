<?php
require_once __DIR__ . '/../../assets/db/db.php';
require_once __DIR__ . '/../../assets/db/initDB.php';
require_once __DIR__ . '/convertToBahtText.php';

header('Content-Type: application/json; charset=utf-8');

$updated = ['thInvoice' => 0, 'thReceipt' => 0];

// Update thInvoice.thBathIn where empty or ศูนย์
$invoices = $db->query(
    "SELECT `id`, `amount` FROM `thInvoice` WHERE `amount` > 0 AND (`thBathIn` IS NULL OR `thBathIn` = '' OR `thBathIn` = 'ศูนย์')"
)->fetchAll();

foreach ($invoices as $row) {
    $text = convertToBahtText((float)$row['amount']);
    $db->query('UPDATE `thInvoice` SET `thBathIn` = ? WHERE `id` = ?', $text, $row['id']);
    $updated['thInvoice']++;
}

// Update thReceipt.thBathRe where empty or ศูนย์
$receipts = $db->query(
    "SELECT `id`, `amount_paid` FROM `thReceipt` WHERE `amount_paid` > 0 AND (`thBathRe` IS NULL OR `thBathRe` = '' OR `thBathRe` = 'ศูนย์')"
)->fetchAll();

foreach ($receipts as $row) {
    $text = convertToBahtText((float)$row['amount_paid']);
    $db->query('UPDATE `thReceipt` SET `thBathRe` = ? WHERE `id` = ?', $text, $row['id']);
    $updated['thReceipt']++;
}

echo json_encode([
    'status'  => 'success',
    'updated' => $updated
]);
