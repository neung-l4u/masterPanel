<?php
ob_start();
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';
ob_clean();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Content-Type: application/json');
}

$isGet      = $_SERVER['REQUEST_METHOD'] === 'GET';
$receipt_id = $isGet ? (int)($_GET['invoice_id'] ?? 0) : (int)($_POST['invoice_id'] ?? 0);
$status     = $isGet ? 'confirmed' : ($_POST['status'] ?? '');
$allowed    = ['pending', 'confirmed', 'rejected'];

if (!$receipt_id || !in_array($status, $allowed)) {
    if ($isGet) {
        http_response_code(400);
        echo '<p style="font-family:sans-serif;text-align:center;margin-top:60px;color:red;">ลิงก์ไม่ถูกต้อง กรุณาติดต่อทีมงาน</p>';
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid params']);
    }
    exit;
}

// invoice_id ที่รับมาคือ thInvoice.id โดยตรง
$invoice_id = $receipt_id;

// ตรวจว่ามี thReceipt สำหรับ invoice นี้จริง
$receiptLookup = $db->query(
    'SELECT `id` FROM `thReceipt` WHERE `invoice_id` = ? ORDER BY `id` DESC LIMIT 1',
    $invoice_id
)->fetchAll();
if (empty($receiptLookup[0])) {
    if ($isGet) {
        http_response_code(404);
        echo '<p style="font-family:sans-serif;text-align:center;margin-top:60px;color:red;">ไม่พบข้อมูล Receipt</p>';
    } else {
        echo json_encode(['success' => false, 'message' => 'Receipt not found']);
    }
    exit;
}

// Get previous status before update
$prevRows = $db->query(
    'SELECT `status` FROM `thReceipt` WHERE `invoice_id` = ? ORDER BY `id` DESC LIMIT 1',
    $invoice_id
)->fetchAll();
$prevStatus = $prevRows[0]['status'] ?? '';

$db->query(
    'UPDATE `thReceipt` SET `status` = ? WHERE `invoice_id` = ?',
    $status, $invoice_id
);

$invoiceStatus = $status === 'confirmed' ? 'sent' : 'pending';
$db->query(
    'UPDATE `thInvoice` SET `status` = ? WHERE `id` = ?',
    $invoiceStatus, $invoice_id
);

// Fire thApoMonday webhook when receipt becomes confirmed for the first invoice (signup)
if ($status === 'confirmed') {
    require_once dirname(__DIR__, 2) . '/api/invoice/thApoMondayHelper.php';
    sendThApoMondayPayload($db, $invoice_id);
}

if ($status === 'rejected') {
    $needfix = trim($_POST['needfix'] ?? $_GET['needfix'] ?? '');
    if ($needfix !== '') {
        $db->query('UPDATE `thReceipt` SET `needfix` = ? WHERE `invoice_id` = ?', $needfix, $invoice_id);
    }
}

// Fire webhook when rejected → pending (customer resubmits for review)
if ($status === 'pending' && $prevStatus === 'rejected') {
    require_once dirname(__DIR__, 2) . '/api/invoice/convertToBahtText.php';

    $invRows = $db->query(
        'SELECT i.`id`, i.`invoiceID`, i.`product`, i.`amount`, i.`thBathIn`,
                c.`name`, c.`address`, c.`taxNumber`, c.`type`, c.`sale`,
                c.`email` AS customerEmail, c.`phone` AS customerPhone,
                c.`bankName`, c.`bankNumber` AS bankThaiNumber, c.`bankAccName` AS bankThaiName
         FROM `thInvoice` i
         JOIN `thCustomer` c ON c.`id` = i.`customer_id`
         WHERE i.`id` = ? LIMIT 1',
        $invoice_id
    )->fetchAll();

    if (!empty($invRows[0])) {
        $row        = $invRows[0];
        $productArr = json_decode($row['product'] ?? '', true) ?? [];
        $summary    = $productArr['summary'] ?? [];

        $receiptRows = $db->query(
            'SELECT `slip`, `thBathRe`, `receiptID` FROM `thReceipt` WHERE `invoice_id` = ? ORDER BY `id` DESC LIMIT 1',
            $invoice_id
        )->fetchAll();
        $receipt   = $receiptRows[0] ?? [];
        $slipPath  = $receipt['slip'] ?? '';
        $slipUrl   = $slipPath ? 'https://report.localforyou.com/modules/signup/assets/uploads/' . $slipPath : '';
        $receiptID = $receipt['receiptID'] ?? $row['invoiceID'];
        $thBathRe  = $receipt['thBathRe']  ?? convertToBahtText((float)$row['amount']);

        $webhookPayload = [
            'invoice_id'     => (int)$row['id'],
            'invoiceID'      => $row['invoiceID'],
            'name'           => $row['name'],
            'address'        => $row['address'],
            'taxNumber'      => $row['taxNumber'],
            'type'           => $row['type'],
            'sale'           => $row['sale'],
            'customerEmail'  => $row['customerEmail'],
            'customerPhone'  => $row['customerPhone'],
            'bankName'       => $row['bankName'],
            'bankThaiNumber' => $row['bankThaiNumber'],
            'bankThaiName'   => $row['bankThaiName'],
            'subtotal'       => $summary['subtotal']           ?? '',
            'vat'            => $summary['vat']                ?? '',
            'grandtotal'     => $summary['grandtotal_inc_vat'] ?? '',
            'withholdingTax' => $summary['withholdingTax']     ?? '',
            'net_payment'    => $summary['net_payment']        ?? '',
            'receiptID'      => $receiptID,
            'receipt_url'    => 'https://report.localforyou.com/pages/receiptTH.php?invoice_id=' . $invoice_id,
            'slip_url'       => $slipUrl,
            'thBathIn'       => $row['thBathIn'] ?? '',
            'thBathRe'       => $thBathRe,
        ];

        $ch = curl_init('https://hook.us1.make.com/rxexlgi6kk68fsygk64fvx6mz2uhbhdp');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookPayload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
    }
}

// --- Sync status to Monday board "Past billing (Receipt TH)" ---
$statusMap = [
    'pending'   => 'Wait for Approve',
    'confirmed' => 'Approve',
    'rejected'  => 'Fix',
];
$mondayLabel = $statusMap[$status] ?? null;

if ($mondayLabel) {
    try {
        $receiptRow = $db->query(
            'SELECT `receiptID` FROM `thReceipt` WHERE `invoice_id` = ? ORDER BY `id` DESC LIMIT 1',
            $invoice_id
        )->fetchAll();
        $receiptCode = $receiptRow[0]['receiptID'] ?? null;

        if ($receiptCode) {
            $mondayToken   = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjYxNzI4MTY4NiwiYWFpIjoxMSwidWlkIjo1NzY1NDA2MSwiaWFkIjoiMjAyNi0wMi0wNVQwMjowNDo0NC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MjIxMTY5MjAsInJnbiI6ImFwc2UyIn0.RIk109S-veeBTyvud8wxzCc656ytoFfVMgA5zyfo3sM';
            $mondayBoardId = 5029904278;

            // Search item by receiptID in column text_mm58ay9j
            $searchQuery = 'query ($board: ID!, $colId: String!, $val: String!) { items_page_by_column_values (limit: 1, board_id: $board, columns: [{column_id: $colId, column_values: [$val]}]) { items { id } } }';
            $searchVars  = ['board' => (int)$mondayBoardId, 'colId' => 'text_mm58ay9j', 'val' => $receiptCode];

            $sCurl = curl_init('https://api.monday.com/v2');
            curl_setopt_array($sCurl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode(['query' => $searchQuery, 'variables' => $searchVars]),
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: ' . $mondayToken, 'API-Version: 2024-01'],
                CURLOPT_TIMEOUT        => 10,
            ]);
            $sResponse = curl_exec($sCurl);

            $sData     = json_decode($sResponse, true);
            $mondayItemId = $sData['data']['items_page_by_column_values']['items'][0]['id'] ?? null;

            if ($mondayItemId) {
                // Update status column (id: "status") on Monday item
                $colVals       = json_encode(['status' => ['label' => $mondayLabel]]);
                $mutationQuery = 'mutation ($board: ID!, $item: ID!, $colVals: JSON!) { change_multiple_column_values (board_id: $board, item_id: $item, column_values: $colVals, create_labels_if_missing: true) { id } }';
                $mutationVars  = ['board' => (int)$mondayBoardId, 'item' => (int)$mondayItemId, 'colVals' => $colVals];

                $mCurl = curl_init('https://api.monday.com/v2');
                curl_setopt_array($mCurl, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => json_encode(['query' => $mutationQuery, 'variables' => $mutationVars]),
                    CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: ' . $mondayToken, 'API-Version: 2024-01'],
                    CURLOPT_TIMEOUT        => 10,
                ]);
                $mResponse = curl_exec($mCurl);
                error_log('[Monday updateStatus Receipt TH] item_id=' . $mondayItemId . ' status=' . $mondayLabel . ' response=' . $mResponse);
            } else {
                error_log('[Monday updateStatus Receipt TH] item not found for receiptCode=' . $receiptCode . ' searchResponse=' . $sResponse);
            }
        }
    } catch (\Throwable $e) {
        error_log('[Monday updateStatus Receipt TH] ' . $e->getMessage());
    }
}

if ($isGet) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>ยืนยันสำเร็จ</title></head><body style="font-family:sans-serif;text-align:center;margin-top:80px;">';
    echo '<div style="color:#16a34a;font-size:48px;">&#10003;</div>';
    echo '<h2 style="color:#16a34a;">ยืนยันข้อมูลเรียบร้อยแล้ว</h2>';
    echo '<p style="color:#555;">สถานะใบแจ้งหนี้ #' . htmlspecialchars((string)$invoice_id) . ' ได้รับการอัปเดตเป็น <b>confirmed</b> แล้ว</p>';
    echo '</body></html>';
} else {
    echo json_encode(['success' => true]);
}
?>
