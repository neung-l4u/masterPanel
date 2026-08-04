<?php
/**
 * หน้าส่งหลักฐานการชำระเงินสำหรับลูกค้า (Customer Upload Slip)
 * URL: modules/customerUpSlip/?invoice_id=123
 */

date_default_timezone_set("Asia/Bangkok");

// DB
include __DIR__ . '/assets/db/db.php';

$invoiceId = isset($_GET['invoice_id']) ? (int)$_GET['invoice_id'] : 0;

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl  = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'report.localforyou.com');
$receiptUrl = $baseUrl . '/pages/receiptTH.php?invoice_id=' . $invoiceId;

$errorMsg = '';
$invoice  = null;

if (!$invoiceId) {
    $errorMsg = 'ไม่พบข้อมูล Invoice (กรุณาระบุ invoice_id)';
} else {
    $rows = $db->query(
        'SELECT i.`id`, i.`invoiceID`, i.`product`, i.`amount`, i.`status`, i.`createdAt`, i.`billingDate`,
                c.`name`, c.`address`, c.`taxNumber`, c.`type`,
                c.`email` AS customerEmail, c.`phone` AS customerPhone,
                c.`bankName`, c.`bankNumber` AS bankThaiNumber, c.`bankAccName` AS bankThaiName,
                r.`slip`, r.`status` AS receiptStatus
         FROM `thInvoice` i
         JOIN `thCustomer` c ON c.`id` = i.`customer_id`
         LEFT JOIN `thReceipt` r ON r.`invoice_id` = i.`id`
         WHERE i.`id` = ? LIMIT 1',
        $invoiceId
    )->fetchAll();

    if (empty($rows[0])) {
        $errorMsg = 'ไม่พบข้อมูล Invoice หมายเลข ' . $invoiceId;
    } else {
        $invoice = $rows[0];
    }
}

function fmt($n) {
    return number_format((float)$n, 2, '.', ',');
}

$productJson = [];
$summary     = [];
$tableItems  = [];
$quotation   = [];
$detail      = [];
$invoiceDate = '-';

if ($invoice) {
    $productJson = json_decode($invoice['product'] ?? '', true) ?: [];
    $summary     = $productJson['summary']   ?? [];
    $tableItems  = $productJson['table']     ?? [];
    $quotation   = $productJson['quotation'][0] ?? [];
    $detail      = $quotation['detail'][0]   ?? [];
    $invoiceDate = $quotation['date'] ?? ($invoice['billingDate'] ? date('d/m/Y', strtotime($invoice['billingDate'])) : date('d/m/Y', strtotime($invoice['createdAt'])));
}

$netPayment    = (float)($summary['net_payment'] ?? $invoice['amount'] ?? 0);
$subtotal      = (float)($summary['subtotal']   ?? 0);
$vat           = (float)($summary['vat']        ?? 0);
$grandtotal    = (float)($summary['grandtotal_inc_vat'] ?? 0);
$withholdingTax= (float)($summary['withholdingTax']     ?? 0);
$isJuristic    = ($invoice['type'] ?? '') === 'นิติบุคคล';

$isPaid        = ($invoice['status'] ?? '') === 'sent' || ($invoice['receiptStatus'] ?? '') === 'confirmed';
$hasSlip       = !empty($invoice['slip']);

?><!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระค่าบริการ - <?= htmlspecialchars($invoice['invoiceID'] ?? '') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; background-color: #1588e6; font-family: 'Sarabun', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        .wrapper { padding: 40px 15px; }
        .container { max-width: 600px; width: 100%; margin: 0 auto; }
        .brand-header { padding-bottom: 20px; color: #ffffff; font-size: 16px; font-weight: 500; }
        .brand-header img { height: 36px; vertical-align: middle; margin-right: 10px; }
        .card { background-color: #ffffff; border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 16px; }
        .card-title { font-size: 16px; font-weight: 500; margin-bottom: 15px; color: #111; }
        h1.amount { margin: 8px 0 4px 0; font-size: 34px; color: #111111; font-weight: 700; }
        .muted { color: #6b7280; font-size: 14px; margin: 0; }
        .divider { border: 0; border-top: 1px solid #e5e7eb; margin: 15px 0; }
        .info-table { width: 100%; font-size: 14px; line-height: 1.6; }
        .info-table td { vertical-align: top; padding: 2px 0; }
        .label-gray { color: #6b7280; width: 15%; }
        .value-dark { color: #111111; }
        .bank-box { background-color: #f8f9fa; border-radius: 6px; font-size: 13px; color: #374151; padding: 10px; }
        .bank-box td { padding: 4px 0; }
        .bank-label { color: #6b7280; width: 35%; }
        .bank-value { color: #111111; font-weight: 500; }
        .btn { display: inline-block; box-sizing: border-box; background-color: #0073e6; color: #ffffff; text-align: center; padding: 14px 0; border-radius: 6px; text-decoration: none; font-size: 16px; font-weight: 600; border: none; cursor: pointer; width: 100%; }
        .btn:hover { background-color: #005bb5; }
        .btn-success { background-color: #1a6b2f; }
        .btn-success:hover { background-color: #145224; }
        .btn:disabled { opacity: .65; cursor: not-allowed; }
        .items-table { width: 100%; border-collapse: collapse; margin: 0; table-layout: fixed; font-size: 13px; }
        .items-table thead tr { border-bottom: 2px solid #e5e7eb; }
        .items-table th { color: #6b7280; padding: 8px 4px; font-weight: normal; }
        .items-table td { padding: 8px 4px; border-bottom: 1px solid #f3f4f6; color: #111; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        .total-row td { padding-top: 12px; }
        .net-amount { font-size: 18px; color: #0073e6; }
        .footer { text-align: center; padding-top: 25px; color: #a4d0f9; font-size: 13px; }
        .upload-area { border: 2px dashed #cbd5e1; border-radius: 8px; padding: 30px; text-align: center; background: #f8fafc; cursor: pointer; transition: all .2s; }
        .upload-area:hover { border-color: #0073e6; background: #f0f7ff; }
        .upload-area.has-file { border-color: #1a6b2f; background: #f0fdf4; }
        .upload-area input[type=file] { display: none; }
        .upload-icon { font-size: 36px; margin-bottom: 8px; }
        .upload-text { color: #6b7280; font-size: 14px; }
        .preview-img { max-height: 200px; border-radius: 6px; border: 1px solid #dee2e6; margin-top: 12px; }
        .note-box { width: 100%; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px; font-family: inherit; font-size: 14px; resize: vertical; box-sizing: border-box; }
        .message { padding: 16px; border-radius: 6px; text-align: center; font-size: 14px; }
        .message.success { background: #d1fae5; color: #065f46; }
        .message.error { background: #fee2e2; color: #991b1b; }
        .hidden { display: none; }
        .spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 1s linear infinite; vertical-align: middle; margin-right: 6px; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="container">

        <div class="brand-header">
            <img src="https://report.localforyou.com/modules/signup/assets/img/localforyouwithwording.png" alt="Local For You">
        </div>

        <?php if ($errorMsg): ?>
            <div class="card">
                <div class="message error"><?= htmlspecialchars($errorMsg) ?></div>
            </div>
        <?php else: ?>

            <?php if ($isPaid): ?>
            <div class="card" style="text-align:center;">
                <div style="font-size:60px;">✅</div>
                <h2 style="color:#1a6b2f; margin-top:10px;">ชำระเงินเรียบร้อยแล้ว</h2>
                <p class="muted">Invoice <?= htmlspecialchars($invoice['invoiceID']) ?> ได้รับการยืนยันแล้ว</p>
                <a href="<?= $receiptUrl ?>" class="btn" target="_blank" style="margin-top:16px;">ดูใบเสร็จรับเงิน</a>
            </div>
            <?php elseif ($hasSlip): ?>
            <div class="card" style="text-align:center;">
                <div style="font-size:60px;">🕐</div>
                <h2 style="color:#b45309; margin-top:10px;">รอตรวจสอบหลักฐาน</h2>
                <p class="muted">Invoice <?= htmlspecialchars($invoice['invoiceID']) ?> ส่งสลิปแล้ว อยู่ระหว่างตรวจสอบ</p>
            </div>
            <?php else: ?>

            <!-- Card 1: Amount + CTA -->
            <div class="card">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="75%" valign="top">
                            <p class="muted">Invoice from Local Eats CO., LTD.</p>
                            <h1 class="amount">฿<?= fmt($netPayment) ?></h1>
                            <p class="muted">วันที่ <?= htmlspecialchars($invoiceDate) ?></p>
                        </td>
                        <td width="25%" align="right" valign="top">
                            <img src="https://img.icons8.com/ios/50/cccccc/invoice.png" width="45" alt="Invoice Icon">
                        </td>
                    </tr>
                </table>

                <hr class="divider">

                <div style="padding-bottom: 20px;">
                    <a href="<?= $receiptUrl ?>?invoice_id=<?= $invoiceId ?>" target="_blank" style="color: #6b7280; text-decoration: none; font-size: 14px; display: inline-block;">
                        <span style="font-size: 16px; margin-right: 4px;">&#8595;</span> ดู Invoice
                    </a>
                </div>

                <table class="info-table">
                    <tr>
                        <td class="label-gray">To</td>
                        <td class="value-dark"><?= htmlspecialchars($invoice['name'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="label-gray">From</td>
                        <td class="value-dark">บจก. โลคอล อีทส์</td>
                    </tr>
                </table>

                <div style="padding-top: 20px;">
                    <table class="bank-box" width="100%" cellpadding="10" cellspacing="0">
                        <tr>
                            <td class="font-bold" style="color: #6b7280; padding-bottom: 4px;" colspan="2">ชำระเงินผ่านการโอนบัญชี</td>
                        </tr>
                        <tr>
                            <td class="bank-label">ธนาคาร:</td>
                            <td class="bank-value">ธนาคารกสิกรไทย</td>
                        </tr>
                        <tr>
                            <td class="bank-label">เลขบัญชี:</td>
                            <td class="bank-value">056-1-85639-7</td>
                        </tr>
                        <tr>
                            <td class="bank-label">ชื่อบัญชี:</td>
                            <td class="bank-value">บจก. โลคอล อีทส์</td>
                        </tr>
                    </table>
                </div>

                <div style="padding-top: 20px;">
                    <a href="<?= $receiptUrl ?>?invoice_id=<?= $invoiceId ?>" target="_blank" class="btn">ดู Invoice</a>
                </div>
            </div>

            <!-- Card 2: Invoice Detail -->
            <div class="card">
                <div style="padding-bottom: 15px; font-weight: 600; font-size: 16px;">
                    Invoice <?= htmlspecialchars($invoice['invoiceID']) ?>
                </div>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width:5%; font-size:11px; text-align:left;">#</th>
                            <th style="width:48%; font-size:11px; text-align:left;">รายการ</th>
                            <th style="width:12%; font-size:11px; text-align:right;">จำนวน</th>
                            <th style="width:17%; font-size:11px; text-align:right;">ราคาต่อหน่วย</th>
                            <th style="width:18%; font-size:11px; text-align:right;">ยอดรวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tableItems as $idx => $item):
                            $label = trim($item['product'] ?? $item['setupfee'] ?? $item['addon'] ?? '-');
                            $qty   = (float)($item['qyt'] ?? 1);
                            $unit  = (float)($item['amount'] ?? 0);
                            $line  = $unit * $qty;
                        ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td><?= htmlspecialchars($label) ?></td>
                            <td class="text-right"><?= fmt($qty) ?></td>
                            <td class="text-right"><?= fmt($unit) ?></td>
                            <td class="text-right"><?= fmt($line) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <hr class="divider">

                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: 13px;">
                    <tr>
                        <td width="70%" style="color: #6b7280; padding: 3px 0;">ราคาก่อนภาษีมูลค่าเพิ่ม</td>
                        <td width="30%" class="text-right" style="color: #6b7280; padding: 3px 0;">฿<?= fmt($subtotal) ?></td>
                    </tr>
                    <tr>
                        <td style="color: #6b7280; padding: 3px 0;">ภาษีมูลค่าเพิ่ม 7%</td>
                        <td class="text-right" style="color: #6b7280; padding: 3px 0;">฿<?= fmt($vat) ?></td>
                    </tr>
                    <tr>
                        <td style="color: #6b7280; padding: 3px 0;">รวมเป็นเงิน (inc. VAT)</td>
                        <td class="text-right" style="color: #6b7280; padding: 3px 0;">฿<?= fmt($grandtotal) ?></td>
                    </tr>
                    <?php if ($isJuristic): ?>
                    <tr>
                        <td style="color: #6b7280; padding: 3px 0;">ภาษีเงินได้หัก ณ ที่จ่าย 3%</td>
                        <td class="text-right" style="color: #6b7280; padding: 3px 0;">฿<?= fmt($withholdingTax) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>

                <hr class="divider">

                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr class="total-row">
                        <td width="70%" class="font-bold" style="padding: 3px 0;">จำนวนเงินสุทธิที่ต้องชำระ</td>
                        <td width="30%" class="text-right font-bold net-amount" style="padding: 3px 0;">฿<?= fmt($netPayment) ?></td>
                    </tr>
                </table>
            </div>

            <!-- Card 3: Upload Slip -->
            <div class="card">
                <div class="card-title"><i style="margin-right:6px;">⬆️</i> อัปโหลดสลิปการโอนเงิน</div>

                <form id="slipUploadForm" action="assets/php/upload.php" enctype="multipart/form-data">
                    <input type="hidden" name="invoice_id" value="<?= $invoiceId ?>">

                    <div class="upload-area" id="uploadArea">
                        <div class="upload-icon">📎</div>
                        <div class="upload-text">คลิกเพื่อเลือกไฟล์สลิป (JPG, PNG, PDF, สูงสุด 10MB)</div>
                        <div id="fileName" style="margin-top:8px; color:#111; font-weight:500;"></div>
                        <img id="previewImg" class="preview-img hidden" alt="Preview">
                        <input type="file" id="slipFile" name="slip" accept="image/*,application/pdf" required>
                    </div>

                    <div style="margin-top: 16px;">
                        <label style="display:block; font-size:13px; color:#374151; margin-bottom:6px; font-weight:500;">หมายเหตุ (ถ้ามี)</label>
                        <textarea class="note-box" name="note" rows="2" placeholder="เช่น โอนวันที่... หรือข้อมูลเพิ่มเติม"></textarea>
                    </div>

                    <div style="margin-top: 20px;">
                        <button type="submit" class="btn btn-success" id="btnSubmit">
                            <i style="margin-right:6px;">📤</i> ส่งหลักฐานการชำระเงิน
                        </button>
                    </div>
                </form>

                <div id="uploadMessage" class="message hidden" style="margin-top:16px;"></div>
            </div>

            <?php endif; ?>

        <?php endif; ?>

        <div class="footer">
            Powered by <span style="font-weight: 700; color: #ffffff; font-size: 14px;">Local For You</span>
        </div>

    </div>
</div>

<script src="assets/js/main.js"></script>

</body>
</html>
