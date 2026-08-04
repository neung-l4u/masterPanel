<?php
/**
 * checkBillingDateTH.php
 *
 * เช็ค monday.com Projects TH board หา item ที่ Billing Date = วันนี้ (หรือระบุวันที่ผ่าน POST/GET)
 * จากนั้น upsert thInvoice (billingSeq +1) + อัป billingDate ใน DB
 *
 * เรียกได้จาก:
 *   - Make.com Scheduled trigger (POST ทุกวัน)
 *   - CLI: php checkBillingDateTH.php
 *   - masterPanel (manual trigger)
 *
 * Response JSON:
 *   { success: true, processed: 3, skipped: 1, errors: [], items: [...] }
 */

ob_start();
session_start();
global $db;
$docRoot = dirname(__DIR__, 2);
include $docRoot . '/assets/db/db.php';
include $docRoot . '/assets/db/initDB.php';
require_once $docRoot . '/api/invoice/convertToBahtText.php';
ob_clean();
header('Content-Type: application/json');

// --- Auth: ต้องมี session หรือ secret key จาก Make.com ---
$secret = $_POST['secret'] ?? $_GET['secret'] ?? '';
$allowedSecret = 'L4U_BILLING_TH_2026'; // เปลี่ยนได้
$isLoggedIn = !empty($_SESSION['id']);
$isValidSecret = ($secret === $allowedSecret);

if (!$isLoggedIn && !$isValidSecret) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// --- Config ---
$mondayToken   = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjYxNzI4MTY4NiwiYWFpIjoxMSwidWlkIjo1NzY1NDA2MSwiaWFkIjoiMjAyNi0wMi0wNVQwMjowNDo0NC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MjIxMTY5MjAsInJnbiI6ImFwc2UyIn0.RIk109S-veeBTyvud8wxzCc656ytoFfVMgA5zyfo3sM';
$mondayBoardId = '1881439330'; // Projects TH board
$mondayUrl     = 'https://api.monday.com/v2';

// --- Target date: วันนี้ หรือรับมาจาก POST ---
$targetDate = isset($_POST['date']) ? trim($_POST['date']) : date('Y-m-d');

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $targetDate)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format, use YYYY-MM-DD']);
    exit;
}

// --- Helper: call monday API ---
function callMonday(string $url, string $token, string $query): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: ' . $token,
        'API-Version: 2024-01',
    ]);
    $response  = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);
    if ($curlError) return ['error' => $curlError];
    return json_decode($response, true) ?? [];
}

// --- GraphQL: ดึง items จาก board ที่มี Billing Date = targetDate ---
// column_id ของ Billing Date ใน Projects TH = "date4" (ตรวจสอบจาก monday board structure)
$gql = <<<GQL
{
  boards(ids: {$mondayBoardId}) {
    items_page(limit: 100, query_params: {
      rules: [
        { column_id: "date4", compare_value: ["{$targetDate}"] }
      ]
    }) {
      items {
        id
        name
        column_values(ids: ["date4","numbers","text","email5","text9","status"]) {
          id
          text
          value
        }
      }
    }
  }
}
GQL;

$mondayResult = callMonday($mondayUrl, $mondayToken, $gql);

if (isset($mondayResult['error'])) {
    echo json_encode(['success' => false, 'message' => 'monday API error: ' . $mondayResult['error']]);
    exit;
}

if (isset($mondayResult['errors'])) {
    echo json_encode(['success' => false, 'message' => 'monday GraphQL error', 'details' => $mondayResult['errors']]);
    exit;
}

$mondayItems = $mondayResult['data']['boards'][0]['items_page']['items'] ?? [];

$processed = 0;
$skipped   = 0;
$errors    = [];
$resultItems = [];

foreach ($mondayItems as $item) {
    $mondayItemId = $item['id'];
    $shopName     = trim($item['name']);

    // Parse column values
    $colMap = [];
    foreach ($item['column_values'] as $col) {
        $colMap[$col['id']] = $col['text'] ?? '';
    }

    $billingDate  = $colMap['date4']   ?? $targetDate;
    $amountText   = $colMap['numbers'] ?? '0';
    $amount       = (float) preg_replace('/[^0-9.]/', '', $amountText);
    $customerName = $colMap['text']    ?? $shopName;
    $email        = $colMap['email5']  ?? '';
    $status       = $colMap['status']  ?? '';

    // ค้นหา thCustomer ตามชื่อร้าน
    $custRows = $db->query(
        'SELECT `id`, `customerCode`, `name`, `address`, `taxNumber`, `email`, `phone`, `type` FROM `thCustomer` WHERE `name` LIKE ? LIMIT 1',
        '%' . $shopName . '%'
    )->fetchAll();

    if (empty($custRows[0])) {
        $skipped++;
        $errors[] = "ไม่พบ customer: {$shopName} (monday item_id: {$mondayItemId})";
        continue;
    }

    $customerId   = (int)$custRows[0]['id'];
    $customerCode = $custRows[0]['customerCode'];

    // หา billingSeq ล่าสุด
    $seqRows = $db->query(
        'SELECT MAX(`billingSeq`) AS maxSeq FROM `thInvoice` WHERE `customer_id` = ?',
        $customerId
    )->fetchAll();
    $lastSeq = (int)($seqRows[0]['maxSeq'] ?? 0);
    $nextSeq = $lastSeq + 1;
    $invoiceID = $customerCode . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

    // เช็คว่า invoice รอบนี้มีอยู่แล้วไหม (ป้องกัน duplicate)
    $existRows = $db->query(
        'SELECT `id` FROM `thInvoice` WHERE `customer_id` = ? AND `billingSeq` = ? LIMIT 1',
        $customerId, $nextSeq
    )->fetchAll();

    if (!empty($existRows[0])) {
        $skipped++;
        $errors[] = "Invoice ซ้ำ: {$invoiceID} (customer_id: {$customerId})";
        continue;
    }

    // สร้าง product JSON: amount จาก monday.com คือราคารวม VAT (gross)
    $gross = (float)$amount;
    $net   = round($gross / 1.07, 2);
    $vat   = round($gross - $net, 2);
    $withhold = round($net * 0.03, 2);
    $netPay   = round($gross - $withhold, 2);

    $thBathIn = convertToBahtText($netPay);

    $taxType = match ($cust['type'] ?? '') {
        'นิติบุคคล' => 'นิติบุคคล',
        'บุคคลธรรมดา' => 'บุคคลธรรมดา',
        default => 'นิติบุคคล',
    };

    $billingDateThai = date('d/m/Y', strtotime($billingDate));

    $productJson = json_encode([
        'quotation' => [[
            'date'   => $billingDateThai,
            'detail' => [[
                'company'  => $cust['name'],
                'address'  => $cust['address'],
                'tax_id'   => $cust['taxNumber'],
                'email'    => $cust['email'],
                'phone'    => $cust['phone'],
                'tax_type' => $taxType,
            ]],
        ]],
        'table' => [],
        'summary' => [
            'subtotal'           => number_format($net, 2, '.', ''),
            'vat'                => number_format($vat, 2, '.', ''),
            'grandtotal_inc_vat' => number_format($gross, 2, '.', ''),
            'withholdingTax'     => number_format($withhold, 2, '.', ''),
            'net_payment'        => number_format($netPay, 2, '.', ''),
        ],
    ], JSON_UNESCAPED_UNICODE);

    // INSERT thInvoice (status pending, เก็บ net_payment)
    $db->query(
        'INSERT INTO `thInvoice`(`customer_id`, `invoiceID`, `type`, `product`, `amount`, `thBathIn`, `status`, `source`, `billingSeq`, `billingDate`, `createdAt`)
         VALUES (?,?,?,?,?,?,?,?,?,?,NOW())',
        $customerId, $invoiceID, 'subscription', $productJson, $netPay, $thBathIn, 'pending', 'monday', $nextSeq, $billingDate
    );

    $newInvoiceId = $db->lastInsertId();

    // INSERT thReceipt รอไว้
    $db->query(
        'INSERT INTO `thReceipt`(`invoice_id`, `receiptID`, `amount_paid`, `thBathRe`, `status`) VALUES (?,?,?,?,?)',
        $newInvoiceId, $invoiceID, $netPay, $thBathIn, 'pending'
    );

    $processed++;
    $resultItems[] = [
        'invoice_id'    => (int)$newInvoiceId,
        'invoiceID'     => $invoiceID,
        'shopName'      => $shopName,
        'amount'        => $netPay,
        'billingSeq'    => $nextSeq,
        'billingDate'   => $billingDate,
        'monday_item_id'=> $mondayItemId,
    ];
}

echo json_encode([
    'success'    => true,
    'targetDate' => $targetDate,
    'processed'  => $processed,
    'skipped'    => $skipped,
    'errors'     => $errors,
    'items'      => $resultItems,
]);
?>
