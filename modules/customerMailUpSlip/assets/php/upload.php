<?php
/**
 * Handler สำหรับอัปโหลดสลิปจากหน้า customerUpSlip
 * ไม่ต้อง login — เปิด public สำหรับลูกค้า
 */

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
set_exception_handler(function($e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
    exit;
});
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $errstr, 'file' => $errfile, 'line' => $errline]);
    exit;
});

date_default_timezone_set("Asia/Bangkok");

include __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../../../../api/invoice/convertToBahtText.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$invoiceId = isset($_POST['invoice_id']) ? (int)$_POST['invoice_id'] : 0;
$note      = isset($_POST['note'])       ? trim($_POST['note'])            : '';

if (!$invoiceId) {
    echo json_encode(['success' => false, 'message' => 'Invoice ID ไม่ถูกต้อง']);
    exit;
}

// Validate file
if (!isset($_FILES['slip']) || $_FILES['slip']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'message' => 'กรุณาแนบไฟล์สลิป']);
    exit;
}

$file = $_FILES['slip'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Upload error: ' . $file['error']]);
    exit;
}

$allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'application/pdf'];
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
if (!in_array($mimeType, $allowedMime, true)) {
    echo json_encode(['success' => false, 'message' => 'ไฟล์ต้องเป็น JPG, PNG หรือ PDF เท่านั้น']);
    exit;
}
if ($file['size'] > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'ไฟล์ใหญ่เกิน 10MB']);
    exit;
}

// Get invoice + customer
$invRows = $db->query(
    'SELECT i.`id`, i.`invoiceID`, i.`amount`, i.`status`,
            c.`name` AS shopName, c.`email` AS customerEmail
     FROM `thInvoice` i
     JOIN `thCustomer` c ON c.`id` = i.`customer_id`
     WHERE i.`id` = ? LIMIT 1',
    $invoiceId
)->fetchAll();

if (empty($invRows[0])) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบ Invoice']);
    exit;
}

$inv = $invRows[0];

if ($inv['status'] === 'sent') {
    echo json_encode(['success' => false, 'message' => 'Invoice นี้ส่ง Receipt แล้ว']);
    exit;
}

// Save file
$safeName  = preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($inv['shopName']));
$folderName = $safeName . '-TH';
$uploadBase = dirname(__DIR__, 3) . '/signup/assets/uploads/slip/';
$uploadDir  = $uploadBase . $folderName . '/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$newFileName = 'slip_' . time() . '_' . basename($file['name']);
$destination = $uploadDir . $newFileName;
$slipPath    = 'slip/' . $folderName . '/' . $newFileName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['success' => false, 'message' => 'บันทึกไฟล์ไม่สำเร็จ']);
    exit;
}

$thBathRe  = convertToBahtText((float)$inv['amount']);
$receiptID = $inv['invoiceID'];

// Insert/update thReceipt
$existing = $db->query(
    'SELECT `id` FROM `thReceipt` WHERE `invoice_id` = ? ORDER BY `id` DESC LIMIT 1',
    $invoiceId
)->fetchAll();

if (!empty($existing[0])) {
    $db->query(
        'UPDATE `thReceipt` SET `slip` = ?, `thBathRe` = ?, `status` = ? WHERE `id` = ?',
        $slipPath, $thBathRe, 'pending', $existing[0]['id']
    );
} else {
    $db->query(
        'INSERT INTO `thReceipt`(`invoice_id`, `receiptID`, `amount_paid`, `thBathRe`, `slip`, `status`) VALUES (?,?,?,?,?,?)',
        $invoiceId, $receiptID, $inv['amount'], $thBathRe, $slipPath, 'pending'
    );
}

// Insert thSlipSubmission (tracking)
$db->query(
    'INSERT INTO `thSlipSubmission`(`invoice_id`, `submittedBy`, `slip`, `note`, `status`) VALUES (?,?,?,?,?)',
    $invoiceId, 'Customer', $slipPath, $note, 'pending'
);

// --- Notify Billing team ผ่าน Make.com webhook ---
try {
    $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $baseUrl   = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'report.localforyou.com');
    $slipUrl   = $baseUrl . '/modules/signup/assets/uploads/' . $slipPath;
    $billingUrl = $baseUrl . '/main.php?p=billingTH';

    $notifyPayload = [
        'event'        => 'slip_submitted',
        'invoice_id'   => $invoiceId,
        'invoiceID'    => $receiptID,
        'shopName'     => $inv['shopName'],
        'amount'       => $inv['amount'],
        'submittedBy'  => 'Customer',
        'note'         => $note,
        'slip_url'     => $slipUrl,
        'billing_url'  => $billingUrl,
        'submittedAt'  => date('Y-m-d H:i:s'),
    ];

    $notifyWebhook = 'https://hook.us1.make.com/gnxfafua86k6mutxg8tr65cuxmrk3v2k';
    $ch = curl_init($notifyWebhook);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notifyPayload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
} catch (\Throwable $e) {
    error_log('[customerUpSlip notify] ' . $e->getMessage());
}

// --- อัป Monday board: link_mm5867rj = slip URL (เช็ค email ตรงกัน) ---
try {
    $mondayToken   = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjYxNzI4MTY4NiwiYWFpIjoxMSwidWlkIjo1NzY1NDA2MSwiaWFkIjoiMjAyNi0wMi0wNVQwMjowNDo0NC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MjIxMTY5MjAsInJnbiI6ImFwc2UyIn0.RIk109S-veeBTyvud8wxzCc656ytoFfVMgA5zyfo3sM';
    $mondayBoardId = '5029904278';
    $customerEmail = $inv['customerEmail'] ?? '';

    if ($customerEmail) {
        $searchQuery = 'query($boardId: ID!, $email: String!) { boards(ids: [$boardId]) { items_page(limit: 1, query_params: { rules: [{ column_id: "text_mm58ns1b", compare_value: [$email] }] }) { items { id } } } }';
        $sCh = curl_init('https://api.monday.com/v2');
        curl_setopt_array($sCh, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_POSTFIELDS     => json_encode(['query' => $searchQuery, 'variables' => ['boardId' => $mondayBoardId, 'email' => $customerEmail]]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: ' . $mondayToken, 'API-Version: 2024-01'],
        ]);
        $sResp    = curl_exec($sCh);
        $sData    = json_decode($sResp, true);
        $targetId = $sData['data']['boards'][0]['items_page']['items'][0]['id'] ?? null;

        if ($targetId) {
            $fullSlipUrl  = 'https://report.localforyou.com/modules/signup/assets/uploads/' . $slipPath;
            $updateQuery  = 'mutation($boardId: ID!, $itemId: ID!, $colVals: JSON!) { change_multiple_column_values(board_id: $boardId, item_id: $itemId, column_values: $colVals, create_labels_if_missing: true) { id } }';
            $colVals      = json_encode(['link_mm5867rj' => ['url' => $fullSlipUrl, 'text' => 'ดูสลิป']]);
            $uCh = curl_init('https://api.monday.com/v2');
            curl_setopt_array($uCh, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_POSTFIELDS     => json_encode(['query' => $updateQuery, 'variables' => ['boardId' => $mondayBoardId, 'itemId' => $targetId, 'colVals' => $colVals]]),
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: ' . $mondayToken, 'API-Version: 2024-01'],
            ]);
            $uResp = curl_exec($uCh);
            error_log('[customerUpSlip] Monday update link_mm5867rj item=' . $targetId . ' resp=' . $uResp);
        } else {
            error_log('[customerUpSlip] Monday: ไม่พบ item สำหรับ email=' . $customerEmail);
        }
    }
} catch (\Throwable $e) {
    error_log('[customerUpSlip Monday] ' . $e->getMessage());
}

echo json_encode([
    'success'   => true,
    'message'   => 'ส่งหลักฐานเรียบร้อย',
    'invoiceID' => $receiptID,
    'slipPath'  => $slipPath,
]);
