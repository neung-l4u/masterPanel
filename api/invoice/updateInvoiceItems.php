<?php
/**
 * updateInvoiceItems.php
 * 
 * แก้ไข product และ amount ใน thInvoice
 * แล้วอัพเดท thBathIn ผ่าน bahtText.php
 * 
 * POST params:
 *   invoice_id  int     thInvoice.id
 *   items       json    array of {product, qyt, amount}
 */

session_start();
global $db;
$docRoot = dirname(__DIR__, 2);
include $docRoot . '/assets/db/db.php';
include $docRoot . '/assets/db/initDB.php';

header('Content-Type: application/json; charset=utf-8');

// Auth check
$isLoggedIn = !empty($_SESSION['id']);
if (!$isLoggedIn) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $invoiceId = (int)($_POST['invoice_id'] ?? 0);
    $itemsRaw = $_POST['items'] ?? '[]';
    $items = is_array($itemsRaw) ? $itemsRaw : (json_decode($itemsRaw, true) ?? []);
    
    if ($invoiceId <= 0) {
        throw new Exception('Invalid invoice ID');
    }
    
    if (empty($items)) {
        throw new Exception('No items provided');
    }
    
    // Get current invoice with customer info
    $invoiceResult = $db->query(
        "SELECT i.*, c.type AS customerType 
         FROM thInvoice i
         LEFT JOIN thCustomer c ON c.id = i.customer_id
         WHERE i.id = ? LIMIT 1",
        $invoiceId
    );
    $invoice = $invoiceResult->fetch();
    
    if (!$invoice) {
        throw new Exception('Invoice not found');
    }
    
    // Calculate total amount
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += (float)($item['amount'] ?? 0);
    }
    $subtotal = round($subtotal, 2);
    
    // Get old product data to preserve VAT rate and other settings
    $oldProduct = json_decode($invoice['product'] ?? '{}', true);
    $oldSummary = $oldProduct['summary'] ?? [];
    
    // Calculate VAT (7% by default, or use old rate)
    $vatRate = $oldSummary['vat_rate'] ?? 0.07;
    $vat = round($subtotal * $vatRate, 2);
    $grandtotalIncVat = round($subtotal + $vat, 2);
    
    // Calculate withholding tax (3% for นิติบุคคล)
    $withholdingTax = 0;
    $customerType = $invoice['customerType'] ?? '';
    
    if ($customerType === 'นิติบุคคล') {
        $withholdingTax = round($grandtotalIncVat * 0.03, 2);
    }
    
    // Net payment
    $netPayment = round($grandtotalIncVat - $withholdingTax, 2);
    
    // Update thInvoice with new items and total amount
    $productData = [
        'table' => $items,
        'summary' => [
            'subtotal' => $subtotal,
            'vat_rate' => $vatRate,
            'vat' => $vat,
            'grandtotal_inc_vat' => $grandtotalIncVat,
            'withholdingTax' => $withholdingTax,
            'net_payment' => $netPayment
        ]
    ];
    
    $productJson = json_encode($productData, JSON_UNESCAPED_UNICODE);
    $totalAmount = $netPayment;
    
    // Update invoice
    $db->query(
        "UPDATE thInvoice SET product = ?, amount = ? WHERE id = ?",
        $productJson,
        $totalAmount,
        $invoiceId
    );
    
    // Convert amount to Thai text
    $thaiText = convertToBahtText($totalAmount);
    
    // Update thBathIn
    $db->query(
        "UPDATE thInvoice SET thBathIn = ? WHERE id = ?",
        $thaiText,
        $invoiceId
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Invoice items updated successfully',
        'data' => [
            'invoice_id' => $invoiceId,
            'total_amount' => $totalAmount,
            'thai_text' => $thaiText,
            'items_count' => count($items)
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * แปลงตัวเลขเป็นคำอ่านภาษาไทย (บาทถ้วน)
 */
function convertToBahtText($number) {
    if ($number == 0) {
        return "ศูนย์บาทถ้วน";
    }

    $txtNum = ['ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
    $txtUnit = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน'];

    // แยกบาทกับสตางค์
    $number = number_format($number, 2, '.', '');
    $parts = explode('.', $number);
    $baht = intval($parts[0]);
    $satang = intval($parts[1]);

    $result = '';

    // แปลงส่วนบาท
    if ($baht > 0) {
        $result .= readNumber($baht, $txtNum, $txtUnit) . 'บาท';
    }

    // แปลงส่วนสตางค์
    if ($satang > 0) {
        $result .= readNumber($satang, $txtNum, $txtUnit) . 'สตางค์';
    } else {
        $result .= 'ถ้วน';
    }

    return $result;
}

/**
 * อ่านตัวเลขเป็นคำไทย (รองรับหลักล้าน)
 */
function readNumber($number, $txtNum, $txtUnit) {
    $result = '';
    $number = intval($number);
    
    if ($number == 0) {
        return 'ศูนย์';
    }

    // จัดการหลักล้านขึ้นไป
    if ($number >= 1000000) {
        $millions = floor($number / 1000000);
        $result .= readNumber($millions, $txtNum, $txtUnit) . 'ล้าน';
        $number = $number % 1000000;
    }

    // แปลงส่วนที่เหลือ (ไม่เกิน 999,999)
    $numStr = strval($number);
    $len = strlen($numStr);

    for ($i = 0; $i < $len; $i++) {
        $digit = intval($numStr[$i]);
        $position = $len - $i - 1; // ตำแหน่งหลัก (0=หน่วย, 1=สิบ, ...)

        if ($digit == 0) {
            continue;
        }

        // กรณีพิเศษ: หลักหน่วยเป็น 1 และไม่ใช่เลขหลักเดียว
        if ($position == 0 && $digit == 1 && $len > 1) {
            $result .= 'เอ็ด';
        }
        // กรณีพิเศษ: หลักสิบเป็น 2
        elseif ($position == 1 && $digit == 2) {
            $result .= 'ยี่สิบ';
        }
        // กรณีพิเศษ: หลักสิบเป็น 1
        elseif ($position == 1 && $digit == 1) {
            $result .= 'สิบ';
        }
        // กรณีปกติ
        else {
            $result .= $txtNum[$digit] . $txtUnit[$position];
        }
    }

    return $result;
}
?>
