<?php
/**
 * updateProductJson.php
 *
 * แก้ไข column `product` (JSON) ของ thInvoice แบบเต็มรูปแบบ
 * ใช้คู่กับ modal "ตรวจสอบข้อมูลก่อนส่ง" ในหน้า pages/invoiceThailand.php
 * ที่แสดงรายการสินค้าเป็นฟอร์มให้ทีม billing แก้ได้ในตัว
 *
 * ต่างจาก updateInvoiceItems.php ตรงที่ไฟล์นี้
 *   - เก็บ quotation (ข้อมูลลูกค้า + วันที่) ไว้ครบ ไม่สร้างทับ
 *   - รองรับรายการทั้ง 3 แบบ: product / setupfee / addon
 *   - คิดภาษีตามรูปแบบเดิมในฐานข้อมูล (หัก ณ ที่จ่าย = subtotal x 3%
 *     เฉพาะนิติบุคคล) ไม่ใช่คิดจากยอดรวม VAT
 *   - ยอมให้ยอดเป็น 0.00 ได้ เพราะมีใบรอบเริ่ม subscription ที่ยอด 0 จริง
 *
 * POST params:
 *   invoice_id int    thInvoice.id
 *   payload    json   { date, company, address, tax_id, email, phone, tax_type,
 *                       items:[{kind,name,qyt,amount,fullamount}],
 *                       withholding_mode, sync_amount }
 */

session_start();
global $db;
$docRoot = dirname(__DIR__, 2);
include $docRoot . '/assets/db/db.php';
include $docRoot . '/assets/db/initDB.php';
include_once __DIR__ . '/convertToBahtText.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
    exit;
}

/** ปัดทศนิยม 2 ตำแหน่งแบบเดียวกับที่ใบเดิมใช้ */
function money($n) { return round((float)$n, 2); }
function money2($n) { return number_format(money($n), 2, '.', ''); }

try {
    $invoiceId = (int)($_POST['invoice_id'] ?? 0);
    if ($invoiceId <= 0) {
        throw new Exception('invoice_id ไม่ถูกต้อง');
    }

    $raw = $_POST['payload'] ?? '';
    $p   = is_array($raw) ? $raw : json_decode($raw, true);
    if (!is_array($p)) {
        throw new Exception('payload ไม่ใช่ JSON ที่ถูกต้อง');
    }

    // ---- โหลดใบเดิม ----
    $rows = $db->query(
        'SELECT i.`id`, i.`invoiceID`, i.`product`, i.`amount`, i.`customer_id`, i.`createdAt`,
                c.`type` AS customerType, c.`name` AS customerName, c.`address` AS customerAddress,
                c.`taxNumber` AS customerTaxNumber, c.`email` AS customerEmail,
                c.`phone` AS customerPhone
         FROM `thInvoice` i
         LEFT JOIN `thCustomer` c ON c.`id` = i.`customer_id`
         WHERE i.`id` = ? LIMIT 1',
        $invoiceId
    )->fetchAll();

    $invoice = $rows[0] ?? null;
    if (!$invoice) {
        throw new Exception('ไม่พบ invoice id ' . $invoiceId);
    }

    $old = json_decode($invoice['product'] ?? '', true);
    if (!is_array($old)) { $old = []; }

    // ---- รายการสินค้า ----
    $itemsIn = $p['items'] ?? [];
    if (!is_array($itemsIn) || count($itemsIn) === 0) {
        throw new Exception('ต้องมีอย่างน้อย 1 รายการ');
    }

    $allowedKinds = ['product', 'setupfee', 'addon'];
    $table    = [];
    $subtotal = 0.0;

    foreach ($itemsIn as $idx => $it) {
        $kind = $it['kind'] ?? 'product';
        if (!in_array($kind, $allowedKinds, true)) {
            $kind = 'product';
        }
        $name = trim((string)($it['name'] ?? ''));
        if ($name === '') {
            throw new Exception('รายการที่ ' . ($idx + 1) . ' ยังไม่ได้ใส่ชื่อ');
        }

        // qyt เก็บเป็น string เหมือนรูปแบบเดิมในฐานข้อมูล
        $qyt    = (string)($it['qyt'] ?? '1');
        $amount = money($it['amount'] ?? 0);
        if ($amount < 0) {
            throw new Exception('รายการที่ ' . ($idx + 1) . ' ราคาติดลบไม่ได้');
        }

        $entry = [
            $kind    => $name,
            'qyt'    => $qyt,
            'amount' => money2($amount),
        ];
        // fullamount = ราคาเต็มก่อนส่วนลด ใส่เฉพาะเมื่อมีค่าจริง
        if (isset($it['fullamount']) && $it['fullamount'] !== '' && $it['fullamount'] !== null) {
            $entry['fullamount'] = money2($it['fullamount']);
        }

        $table[]   = $entry;
        $subtotal += $amount * (float)($qyt === '' ? 1 : $qyt);
    }

    $subtotal = money($subtotal);
    $vat      = money($subtotal * 0.07);
    $gross    = money($subtotal + $vat);

    // ---- ภาษีหัก ณ ที่จ่าย ----
    // ค่าเริ่มต้นอิงประเภทลูกค้า: นิติบุคคล 3% ของฐานก่อน VAT, บุคคลธรรมดา 0%
    // (ตรงกับใบเดิมในฐานข้อมูลทุกใบ) ผู้ใช้เลือก override ได้ที่หน้าจอ
    $custType = $invoice['customerType'] ?? '';
    $mode     = $p['withholding_mode'] ?? 'auto';

    if ($mode === 'none') {
        $wht = 0.0;
    } elseif ($mode === 'force3') {
        $wht = money($subtotal * 0.03);
    } else {
        $wht = ($custType === 'นิติบุคคล') ? money($subtotal * 0.03) : 0.0;
    }

    $netPayment = money($gross - $wht);

    // ---- quotation: เก็บของเดิมไว้ แล้วทับเฉพาะช่องที่ส่งมา ----
    $oldQuot   = $old['quotation'][0] ?? [];
    $oldDetail = $oldQuot['detail'][0] ?? [];

    $pick = function ($key, $fallback) use ($p) {
        return array_key_exists($key, $p) ? trim((string)$p[$key]) : $fallback;
    };

    // ถ้าใบนี้ยังไม่เคยมี quotation (product เป็น NULL) ให้ดึงข้อมูลลูกค้า
    // จาก thCustomer มาเป็นค่าตั้งต้น จะได้ไม่เกิดใบที่ชื่อ/ที่อยู่ว่างเปล่า
    $detail = [
        'company'  => $pick('company',  $oldDetail['company']  ?? ($invoice['customerName']      ?? '')),
        'address'  => $pick('address',  $oldDetail['address']  ?? ($invoice['customerAddress']   ?? '')),
        'tax_id'   => $pick('tax_id',   $oldDetail['tax_id']   ?? ($invoice['customerTaxNumber'] ?? '')),
        'email'    => $pick('email',    $oldDetail['email']    ?? ($invoice['customerEmail']     ?? '')),
        'phone'    => $pick('phone',    $oldDetail['phone']    ?? ($invoice['customerPhone']     ?? '')),
        'tax_type' => $pick('tax_type', $oldDetail['tax_type'] ?? $custType),
    ];

    // ไม่มีวันที่เดิม ให้ใช้วันที่สร้าง invoice เป็นค่าตั้งต้น
    $fallbackDate = !empty($invoice['createdAt'])
        ? date('d/m/Y', strtotime($invoice['createdAt']))
        : '';
    $date = $pick('date', $oldQuot['date'] ?? $fallbackDate);
    if ($date !== '' && !preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
        throw new Exception('รูปแบบวันที่ต้องเป็น dd/mm/yyyy');
    }

    // ---- ประกอบ JSON ใหม่ โดยรักษา key อื่น ๆ ที่เคยมีไว้ ----
    $new = $old;
    $new['quotation'] = [[
        'date'   => $date,
        'detail' => [$detail],
    ]];
    $new['table']   = $table;
    $new['summary'] = [
        'subtotal'           => money2($subtotal),
        'vat'                => money2($vat),
        'grandtotal_inc_vat' => money2($gross),
        'withholdingTax'     => money2($wht),
        'net_payment'        => money2($netPayment),
    ];

    $productJson = json_encode($new, JSON_UNESCAPED_UNICODE);
    if ($productJson === false) {
        throw new Exception('แปลงข้อมูลเป็น JSON ไม่สำเร็จ');
    }

    // ---- เขียนลงฐานข้อมูล ----
    // sync_amount = ให้ column amount ตามยอดสุทธิใหม่ด้วยหรือไม่
    $syncAmount = !empty($p['sync_amount']);

    if ($syncAmount) {
        $bahtText = convertToBahtText($netPayment);
        $db->query(
            'UPDATE `thInvoice` SET `product` = ?, `amount` = ?, `thBathIn` = ? WHERE `id` = ?',
            $productJson, $netPayment, $bahtText, $invoiceId
        );
    } else {
        $db->query(
            'UPDATE `thInvoice` SET `product` = ? WHERE `id` = ?',
            $productJson, $invoiceId
        );
    }

    echo json_encode([
        'success' => true,
        'message' => 'บันทึกข้อมูลเรียบร้อย',
        'data'    => [
            'invoice_id' => $invoiceId,
            'invoiceID'  => $invoice['invoiceID'],
            'summary'    => $new['summary'],
            'items'      => count($table),
            'amount_synced' => $syncAmount,
        ],
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
