<?php
global $db;
include dirname(__DIR__) . '/assets/db/db.php';
include dirname(__DIR__) . '/assets/db/initDB.php';

$invoiceId = isset($_GET['invoice_id']) ? (int)$_GET['invoice_id'] : 0;

if (!$invoiceId) {
    echo '<p style="padding:40px;color:red;">ไม่พบข้อมูล (invoice_id required)</p>';
    exit;
}

$rows = $db->query(
    'SELECT i.`id`, i.`invoiceID`, i.`product`, i.`amount`, i.`status`, i.`createdAt`,
            c.`name`, c.`address`, c.`taxNumber`, c.`type`,
            c.`email` AS customerEmail, c.`phone` AS customerPhone,
            c.`sale`, c.`bankName`, c.`bankNumber` AS bankThaiNumber, c.`bankAccName` AS bankThaiName
     FROM `thInvoice` i
     JOIN `thCustomer` c ON c.`id` = i.`customer_id`
     WHERE i.`id` = ? LIMIT 1',
    $invoiceId
)->fetchAll();

$row = $rows[0] ?? null;
if (!$row) {
    echo '<p style="padding:40px;color:red;">ไม่พบข้อมูล Invoice ID: ' . $invoiceId . '</p>';
    exit;
}

$productJson = json_decode($row['product'] ?? '', true) ?: [];
$summary     = $productJson['summary']   ?? [];
$tableItems  = $productJson['table']     ?? [];
$quotation   = $productJson['quotation'][0] ?? [];
$detail      = $quotation['detail'][0]   ?? [];

$invoiceDate = $quotation['date'] ?? date('d/m/Y', strtotime($row['createdAt']));
$type        = $row['type'] ?? '';
$isJuristic  = ($type === 'นิติบุคคล');

$receiptRows = $db->query(
    'SELECT `receiptID`, `status`, `createdAt` FROM `thReceipt` WHERE `invoice_id` = ? ORDER BY `id` DESC LIMIT 1',
    $invoiceId
)->fetchAll();
$receipt     = $receiptRows[0] ?? null;
$receiptID   = $receipt['receiptID'] ?? $row['invoiceID'] ?? '-';
$receiptDate = $receipt ? date('d/m/Y', strtotime($receipt['createdAt'])) : $invoiceDate;

$grandTotal    = (float)($summary['grandtotal_inc_vat'] ?? 0);
$subtotal      = (float)($summary['subtotal'] ?? 0);
$vat           = (float)($summary['vat'] ?? 0);
$wht           = (float)($summary['withholdingTax'] ?? 0);
$netPayment    = (float)($summary['net_payment'] ?? $row['amount'] ?? 0);
$thaiText      = $productJson['thaiPrice'] ?? '';

function fmt($n) { return number_format((float)$n, 2, '.', ','); }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบกำกับภาษี/ใบเสร็จรับเงิน</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Thai:wght@100..900&display=swap" rel="stylesheet">
    <style>
        body { font-family: "Noto Serif Thai", serif; margin: 0; background: #f5f5f5; }
        .invoice-container { margin: 0 auto; padding: 40px 40px; background-color: #fff; max-width: 900px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
        .logo-section h2 { font-size: 24px; margin: 0; color: #007bff; }
        .invoice-title-section { text-align: right; }
        .invoice-title-section h3 { font-size: 30px; font-weight: normal; margin-top: 10px; }
        .invoice-title-section p { font-size: 18px; color: #666; margin-top: -30px; }
        .invoice-details { display: flex; justify-content: space-between; }
        .customer-info, .seller-info { width: 48%; margin-bottom: 15px; padding-bottom: 20px; }
        .customer-info p, .seller-info p { margin: 0; font-size: 11px; line-height: 1.5; }
        .invoice-info { width: 45%; line-height: 1.5; }
        .invoice-div { padding-left: 20px; }
        .invoice-info div { font-size: 11px; }
        .horizontal-divider { border: 0; height: 1px; background-color: #ddd; margin-top: 70px; }
        table { width: 100%; border-collapse: collapse; margin-top: 0px; }
        th, td { border-bottom: 1px solid #ddd; padding: 10px 0; text-align: left; font-size: 14px; }
        th { font-weight: bold; color: #555; }
        strong, .primary { color: #045AD1 !important; }
        strong.black { color: #000000 !important; }
        .text-right { text-align: right; }
        .totals-table { width: 300px; float: right; margin-top: 20px; }
        .totals-table td { border: none; padding: 5px 0; }
        .thai-amount { font-style: italic; text-align: right; }
        .footer { margin-top: 90px; text-align: left; font-size: 10px; color: #999999; }
        ul.dash { list-style: none; margin-left: 0; padding-left: 1em; }
        ul.dash > li:before { display: inline-block; content: "-"; width: 1em; margin-left: -1em; }
        .print-bar { text-align: center; padding: 10px; background: #045AD1; position: sticky; top: 0; z-index: 99; }
        .print-bar button { background: #fff; color: #045AD1; border: none; padding: 6px 24px; border-radius: 4px; font-size: 13px; font-weight: 600; cursor: pointer; margin: 0 6px; }
        .print-bar button:hover { background: #e8f0fe; }
        @media print {
            body { background: white; }
            .print-bar { display: none !important; }
            .invoice-container { max-width: 100%; padding: 20px 30px; }
        }
    </style>
</head>
<body>

<div class="print-bar">
    <button onclick="window.print()">🖨️ Print / Save PDF</button>
    <button onclick="window.close()">✕ ปิด</button>
</div>

<div class="invoice-container">

    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            <img src="https://report.localforyou.com/modules/signup/assets/img/newL4U-logo-100x100-2.png" alt="Company logo" height="70" />
        </div>
        <div class="invoice-title-section">
            <h3 class="primary">ใบกำกับภาษี/ใบเสร็จรับเงิน</h3>
            <p class="primary">(ต้นฉบับ)</p>
        </div>
    </div>

    <!-- Seller + Invoice Info -->
    <div class="invoice-details">
        <div class="customer-info">
            <p><strong>บริษัท โลคอล อีทส์ จำกัด</strong></p>
            <p>216/61 อาคารเลควิว คอนโดมิเนียม อาคารสงขลา (เดอะเลค)</p>
            <p>ชั้นที่ 4 ถนนบอนด์สตรีท ตำบลบางพูด</p>
            <p>อำเภอปากเกร็ด นนทบุรี 11120 ประเทศไทย</p>
            <p><strong class="black">เลขประจำตัวผู้เสียภาษี:</strong> 0125562017473</p>
            <p><strong class="black">อีเมล:</strong> admin@localforyou.com</p>
            <p><strong class="black">เบอร์โทร:</strong> +6621251205</p>
        </div>
        <div class="invoice-info">
            <hr style="border-top: 1px solid #ccc; margin-bottom: 20px;">
            <div class="invoice-div"><strong>เลขที่:</strong> <?= htmlspecialchars($receiptID) ?></div>
            <div class="invoice-div"><strong>วันที่:</strong> <?= htmlspecialchars($receiptDate) ?></div>
            <?php if (!empty($row['sale'])): ?>
            <div class="invoice-div"><strong>ผู้ขาย:</strong> <?= htmlspecialchars($row['sale']) ?></div>
            <?php endif; ?>
            <hr style="border-top: 1px solid #ccc; margin-top: 20px;">
        </div>
    </div>

    <!-- Customer Info -->
    <div class="customer-info">
        <p><strong>ลูกค้า</strong></p>
        <p><?= htmlspecialchars($row['name'] ?? '-') ?></p>
        <?php if (!empty($row['address'])): ?>
        <p><?= nl2br(htmlspecialchars($row['address'])) ?></p>
        <?php endif; ?>
        <?php if (!empty($row['taxNumber'])): ?>
        <p><strong class="black">เลขประจำตัวผู้เสียภาษี:</strong> <?= htmlspecialchars($row['taxNumber']) ?></p>
        <?php endif; ?>
        <?php if (!empty($row['customerEmail'])): ?>
        <p><strong class="black">อีเมล:</strong> <?= htmlspecialchars($row['customerEmail']) ?></p>
        <?php endif; ?>
        <?php if (!empty($row['customerPhone'])): ?>
        <p><strong class="black">เบอร์โทร:</strong> <?= htmlspecialchars($row['customerPhone']) ?></p>
        <?php endif; ?>
    </div>

    <div class="horizontal-divider"></div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:50%;">รายละเอียด</th>
                <th style="width:15%;" class="text-right">จำนวน</th>
                <th style="width:15%;" class="text-right">ราคาต่อหน่วย</th>
                <th style="width:15%;" class="text-right">ยอดรวม</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tableItems as $i => $item): ?>
                <?php
                $label     = trim($item['product'] ?? $item['setupfee'] ?? $item['addon'] ?? '-');
                $qyt       = (float)($item['qyt'] ?? 1);
                $unitPrice = isset($item['fullamount']) ? (float)$item['fullamount'] : (float)($item['amount'] ?? 0);
                $lineTotal = $unitPrice * $qyt;
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($label) ?></td>
                    <td class="text-right"><?= $qyt ?></td>
                    <td class="text-right"><?= fmt($unitPrice) ?></td>
                    <td class="text-right"><?= fmt($lineTotal) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totals -->
    <table class="totals-table">
        <tbody>
            <tr>
                <td class="text-right"><strong>รวมเป็นเงิน</strong></td>
                <td class="text-right"><?= fmt($grandTotal) ?> บาท</td>
            </tr>
            <tr>
                <td class="text-right"><strong>ราคาก่อนภาษีมูลค่าเพิ่ม</strong></td>
                <td class="text-right"><?= fmt($subtotal) ?> บาท</td>
            </tr>
            <tr>
                <td class="text-right"><strong>ภาษีมูลค่าเพิ่ม 7%</strong></td>
                <td class="text-right"><?= fmt($vat) ?> บาท</td>
            </tr>
            <?php if ($isJuristic): ?>
            <tr>
                <td class="text-right"><strong>ภาษีเงินได้หัก ณ ที่จ่าย 3%</strong></td>
                <td class="text-right"><?= fmt($wht) ?> บาท</td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="text-right"><strong>จำนวนเงินสุทธิที่ต้องชำระ</strong></td>
                <td class="text-right"><?= fmt($netPayment) ?> บาท</td>
            </tr>
            <tr>
                <td colspan="2"><hr style="border-top: 1px dashed #ccc; margin: 10px 0;"></td>
            </tr>
        </tbody>
    </table>

    <div style="clear:both;"></div>

    <?php if (!empty($thaiText)): ?>
    <p class="thai-amount">(<?= htmlspecialchars($thaiText) ?>)</p>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        <figure>
            <figcaption>หมายเหตุ</figcaption>
            <ul class="dash">
                <li>ราคานี้ได้รวมภาษีมูลค่าเพิ่ม 7% แล้ว</li>
                <li>เอกสารฉบับนี้สามารถแก้ไขได้ภายในวันที่ซื้อสินค้าเท่านั้น</li>
                <li>หากมีข้อผิดพลาดประการใดหรือประสงค์ขอคืนเงิน กรุณาแจ้งภายใน 7 วันหลังชำระเงิน มิฉะนั้นบริษัทขอสงวนสิทธิ์ไม่รับผิดชอบต่อกรณีดังกล่าว</li>
            </ul>
        </figure>
    </div>

</div>
</body>
</html>
