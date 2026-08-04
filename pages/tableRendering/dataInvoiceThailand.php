<?php
ob_start();
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';
ob_clean();
header('Content-Type: application/json');

$dateStart  = $_POST['dateStart']  ?? '';
$dateEnd    = $_POST['dateEnd']    ?? '';
$searchVal  = $_POST['search_val'] ?? '';

$params = [];
$query = "SELECT r.`id` AS receiptId, r.`invoice_id` AS id, r.`receiptID`, r.`amount_paid`, r.`status` AS receiptStatus, r.`slip`, r.`createdAt`, r.`sentAt`,
                 i.`invoiceID`, i.`product`, i.`amount` AS invoiceAmount, i.`status` AS invoiceStatus, i.`wantGM`,
                 c.`name`, c.`email` AS customerEmail, c.`phone` AS customerPhone,
                 c.`type`, c.`clientType`, c.`bankName`, c.`bankNumber` AS bankThaiNumber
          FROM `thReceipt` r
          JOIN `thInvoice` i ON i.`id` = r.`invoice_id`
          JOIN `thCustomer` c ON c.`id` = i.`customer_id`
          WHERE 1=1";

if (!empty($dateStart) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStart)) {
    $query .= ' AND DATE(r.createdAt) >= ?';
    $params[] = $dateStart;
}
if (!empty($dateEnd) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateEnd)) {
    $query .= ' AND DATE(r.createdAt) <= ?';
    $params[] = $dateEnd;
}
if (!empty($searchVal)) {
    $s = '%' . strip_tags(trim($searchVal)) . '%';
    $query .= ' AND (c.`name` LIKE ? OR c.`email` LIKE ? OR i.`invoiceID` LIKE ? OR r.`receiptID` LIKE ?)';
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
}

$query .= ' AND (c.`clientType` != "subscription" OR (c.`clientType` = "subscription" AND i.`wantGM` = "1"))';
$query .= ' ORDER BY r.id DESC';

$rows = empty($params) ? $db->query($query)->fetchAll() : $db->query($query, ...$params)->fetchAll();
$data  = ['data' => []];

foreach ($rows as $row) {
    $id       = $row['id'];
    $invID    = htmlspecialchars($row['receiptID'] ?? $row['invoiceID'] ?? '-');
    $name     = htmlspecialchars($row['name'] ?? '-');
    $email    = htmlspecialchars($row['customerEmail'] ?? '-');
    $phone    = htmlspecialchars($row['customerPhone'] ?? '-');
    $amount = number_format((float)($row['amount_paid'] ?? 0), 2) . ' ฿';
    $date   = $row['createdAt'];

    // Type badge
    $typeVal = $row['type'] ?? '';
    if ($typeVal === 'นิติบุคคล') {
        $typeBadge = '<span class="badge badge-info">นิติบุคคล</span>';
    } elseif ($typeVal === 'บุคคลธรรมดา') {
        $typeBadge = '<span class="badge badge-warning">บุคคลธรรมดา</span>';
    } else {
        $typeBadge = '<span class="badge badge-secondary">-</span>';
    }

    // Client Type badge (first_time / subscription)
    $clientType = $row['clientType'] ?? 'first_time';
    $wantGM = $row['wantGM'] ?? '0';
    if ($clientType === 'subscription') {
        $gmLabel = ($wantGM === '1' || $wantGM === 1) ? ' ✓' : '';
        $clientBadge = '<span class="badge badge-success"><i class="bi bi-arrow-repeat mr-1"></i>Sub' . $gmLabel . '</span>';
    } else {
        $clientBadge = '<span class="badge badge-primary"><i class="bi bi-star mr-1"></i>First</span>';
    }

    // Slip badge — based on actual slip in thReceipt
    $slipPath = $row['slip'] ?? '';
    if (!empty($slipPath)) {
        $slipHtml = '<span class="badge badge-success"><i class="bi bi-check-circle"></i> มีสลิป</span>';
    } else {
        $invoiceStatus = $row['invoiceStatus'] ?? 'pending';
        if ($invoiceStatus === 'sent') {
            $slipHtml = '<span class="badge badge-success"><i class="bi bi-check-circle"></i> ส่งแล้ว</span>';
        } else {
            $slipHtml = '<span class="badge badge-warning"><i class="bi bi-clock"></i> รอหลักฐาน</span>';
        }
    }

    // Status badge (clickable)
    $receiptStatus = $row['receiptStatus'] ?? 'pending';
    $safeInvID = htmlspecialchars($row['invoiceID'] ?? '-', ENT_QUOTES);
    $onclick = 'openEditStatus(' . $id . ', \'' . $safeInvID . '\', \'' . $receiptStatus . '\')';
    if ($receiptStatus === 'confirmed') {
        $statusBadge = '<span class="badge badge-success" style="cursor:pointer;" onclick="' . $onclick . '"><i class="bi bi-check-circle-fill"></i> ยืนยันเรียบร้อย</span>';
    } elseif ($receiptStatus === 'rejected') {
        $statusBadge = '<span class="badge badge-danger" style="cursor:pointer;" onclick="' . $onclick . '"><i class="bi bi-x-circle-fill"></i> แก้ไขข้อมูล</span>';
    } else {
        $statusBadge = '<span class="badge badge-warning" style="cursor:pointer;" onclick="' . $onclick . '"><i class="bi bi-hourglass-split"></i> รอยืนยันหลักฐาน</span>';
    }

    // Send button
    $sendBtn = '<button class="btn btn-sm btn-primary" onclick="openSendModal(' . $id . ')"><i class="bi bi-send"></i> Send</button>';

    $data['data'][] = [
        $date,
        $invID,
        $name,
        $email,
        $clientBadge,
        $amount,
        $statusBadge,
        $sendBtn,
    ];
}

echo json_encode($data);
?>
