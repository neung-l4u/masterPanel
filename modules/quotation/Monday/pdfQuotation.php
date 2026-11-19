<?php

date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");
$dateThai = date("d/m/Y");

global $db;
include '../assets/db/db.php';
include "../assets/db/initDB_stripe.php";

$quotationID = !empty($_GET["quotationID"]) ? $_GET["quotationID"] : null;

$row = $db->query('SELECT * FROM `quotation` WHERE id = ?', $quotationID)->fetchArray();

$saleName = $row['sale'];
$thaiPrice = $row['thaiprice'];
$quotationNumber = $row['quotationNumber'];

$data = json_decode($row['data'], true);

// --- แยกข้อมูลแต่ละส่วน ---
$tableData   = $data['table'];           // สินค้าทั้งหมด
$invoiceData = $data['quotation'][0];      // ข้อมูลใบเสนอราคา (array แรก)
$summaryData = $data['summary'];         // ยอดรวมทั้งหมด

// --- ดึงข้อมูลลูกค้า ---
$customer = $invoiceData['detail'][0];
$companyName = $customer['company'];
$address     = $customer['address'];
$tax_id      = $customer['tax_id'];
$email       = $customer['email'];
$phone       = $customer['phone'];
$taxType     = $customer['taxType'] ?? '';
$isCorporate = ($taxType === 'นิติบุคคล');

// --- ดึงข้อมูลใบเสนอราคา ---
$date = $invoiceData['date'];

// --- ดึงยอดรวม ---
$subtotalRaw   = (float)$summaryData['subtotal'];
$taxRaw        = (float)$summaryData['tax'];
$grandtotalRaw = (float)$summaryData['grandtotal'];

// หัก ณ ที่จ่าย 3% คิดจากราคาก่อน VAT (เฉพาะนิติบุคคล)
$withholdingRaw = $isCorporate ? round($subtotalRaw * 0.03, 2) : 0;
$netPayableRaw  = $grandtotalRaw - $withholdingRaw;

$subtotal    = number_format($subtotalRaw, 2);
$tax         = number_format($taxRaw, 2);
$grandtotal  = number_format($grandtotalRaw, 2);
$withholding = number_format($withholdingRaw, 2);
$netPayable  = number_format($netPayableRaw, 2);

// --- ประเทศ: ใบเสนอราคาเก่าไม่มี country = TH ---
$country = $data['country'] ?? 'TH';
$isTH = ($country === 'TH');
$contact = $customer['contact'] ?? '';
$currencySymbol = ['AU' => 'A$', 'NZ' => 'NZ$', 'UK' => '£', 'US' => 'US$', 'CA' => 'CA$', 'TH' => '฿'][$country] ?? '';
$money = fn($n) => ($n < 0 ? '-' : '') . $currencySymbol . number_format(abs($n), 2);

// ผู้ออกใบเสนอราคา ตาม modules/signup/assets/docs/contract_2024_V02.php (CA ใช้ LLC เพราะเก็บเงินผ่าน Stripe US)
$sellerAU = ['name' => 'Manaexito Pty Ltd T/as Local For You Pty Ltd', 'address' => '9/204 Alice Street, Brisbane, QLD 4000, Australia', 'idLabel' => 'ABN', 'id' => '60 606 095 943'];
$sellerUS = ['name' => 'Local For You LLC', 'address' => '8 The Green STE R, Dover, DE 19901 USA', 'idLabel' => 'EIN', 'id' => '35-2789835'];
$seller = in_array($country, ['US', 'CA'], true) ? $sellerUS : $sellerAU;
//แบบใบแจ้งหนี้ Stripe: "GST – Australia (10% on A$149.50)"
$taxLabel = ($country === 'AU') ? 'GST – Australia (10% on ' . $money($subtotalRaw) . ')' : 'Tax';

// ใบเก่าเก็บส่วนลดเป็นสินค้า "Discount (CODE)" -> แปลงเป็นแถวส่วนลด "CODE (50% off)" / "CODE (A$198.00 off)"
// % ดูจาก couponCode.json ของ signup (ไฟล์เดียวกับที่ฟอร์มใช้)
$couponJson = json_decode(@file_get_contents(__DIR__ . '/../../signup/assets/API/couponCode.json'), true);
$couponPercent = function ($code) use ($couponJson, $country) {
    foreach (['Main', 'Addons'] as $group) {
        foreach (($couponJson['Coupon'][$group][$code] ?? []) as $byType) {
            if (is_array($byType) && !empty($byType[$country]['percent'])) {
                return $byType[$country]['percent'];
            }
        }
    }
    return null;
};
foreach ($tableData as &$it) {
    if (isset($it['product']) && preg_match('/^Discount \((.+)\)$/', $it['product'], $m)) {
        $percent = $couponPercent($m[1]);
        $off = $percent ? $percent . '% off' : ($isTH ? number_format(abs((float)$it['amount']), 2) . ' บาท' : $money(abs((float)$it['amount']))) . ' off';
        $it = ['discount' => $m[1] . ' (' . $off . ')', 'qyt' => $it['qyt'], 'amount' => $it['amount']];
    }
}
unset($it);


?>

<!DOCTYPE html>
<html lang="<?php echo $isTH ? 'th' : 'en'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isTH ? 'ใบเสนอราคา' : 'Quotation'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Thai:wght@100..900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: "Noto Serif Thai", serif;
            margin: 0;
            /*padding: 20px;*/
            /*background-color: #f0f2f5;*/
        }

        /* บนจอแสดงเป็นแผ่น A4 (กว้าง 210mm) กลางหน้า เหมือนเอกสารจริง */
        html {
            background-color: #e9ecef;
        }

        .invoice-container {
            box-sizing: border-box;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 15mm;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        @page {
            size: A4;
            margin: 0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
        }

        .logo-section {
            display: flex;
            margin-left: -15px;
        }

        .logo-section h2 {
            font-size: 24px;
            margin: 0;
            color: #007bff;
        }

        .invoice-title-section {
            text-align: right;
        }

        .invoice-title-section h3 {
            font-size: 30px;
            font-weight: normal;
            margin-top: 10px;
        }

        .invoice-title-section p {
            font-size: 18px;
            color: #666;
            margin-top: -30px;
            /* border-bottom: 1px solid #ddd; */
            /*margin-bottom: 20px;*/
            /*padding-bottom: 30px;*/
        }

        .invoice-details {
            display: flex;
            justify-content: space-between;
        }

        .customer-info,
        .seller-info {
            width: 48%;
            margin-bottom: 15px;
            padding-bottom: 20px;
        }

        .customer-info p,
        .seller-info p {
            margin: 0;
            font-size: 11px;
            line-height: 1.5;
        }

        .invoice-info {
            width: 45%;
            /*text-align: right;*/
            line-height: 1.5;

            /*border-bottom: 1px solid #ddd;*/
            /*border-bottom: 1px solid #ddd;*/
        }

        .invoice-div {
            padding-left: 20px;
        }

        .invoice-info div {
            font-size: 11px;
        }

        .invoice-info span {
            display: inline-block;
            width: 80px;
            text-align: left;
        }

        .horizontal-divider {
            border: 0;
            height: 1px;
            background-color: #ddd;
            margin-top: 70px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0px;
        }

        th,
        td {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
            text-align: left;
            font-size: 14px;
        }

        th {
            font-weight: bold;
            color: #555;
        }

        strong,
        .primary {
            color: #045AD1 !important;
        }

        strong.black {
            color: #000000 !important;
        }

        .text-right {
            text-align: right;
        }

        .total{
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .totals-table {
            width: 300px;
            float: right;
            margin-top: 20px;
        }

        .totals-table td {
            border: none;
            padding: 5px 0;
        }

        /* ต่างประเทศ: ตารางรายการ + สรุปยอดแบบใบแจ้งหนี้ Stripe */
        .items-table td {
            vertical-align: top;
        }

        /* เส้นตารางเข้มขึ้น (เดิม #ddd จางเกินไปตอนพิมพ์) */
        .items-table th,
        .items-table td,
        .stripe-totals td {
            border-bottom: 1px solid #888;
        }


        .stripe-totals {
            width: 55%;
            margin-top: 0;
        }

        .stripe-totals td {
            padding: 6px 0;
            color: #333;
        }

        .stripe-totals td.label {
            text-align: left;
        }

        .stripe-totals td.amount {
            width: 110px;
        }

        .stripe-totals tr.amount-due td {
            font-weight: bold;
            border-bottom: none;
        }

        .grand-total {
            font-weight: bold;
        }

        .thai-amount {
            font-style: italic;
            /* font-size: 12px; */
            /* margin-bottom:35px; */
            text-align: right;
        }

        .footer {
            margin-top: 40px;
            text-align: left;
            font-size: 10px;
            color: #999999;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .footer, figure{
            margin-left: 0px;
        }

        .footer figure {
            page-break-inside: avoid;
            break-inside: avoid;
            margin: 0;
        }

        .footer ul.dash,
        .footer ul.dash > li {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        ul.dash {
            list-style: none;
            margin-left: 0;
            padding-left: 1em;
        }

        ul.dash>li:before {
            display: inline-block;
            content: "-";
            width: 1em;
            margin-left: -1em;
        }

        .print-button-container {
            width: 210mm;
            margin: 20px auto 0;
            text-align: right;
        }

        .discount-row td {
            color: #555;
        }

        .discount-label {
            padding-left: 24px !important;
        }

        #printButton {
            padding: 5px 25px;
            font-size: 16px;
            font-family: "Noto Serif Thai", serif;
            cursor: pointer;
            border: 1px solid #045AD1;
            background-color: #045AD1;
            color: white;
            border-radius: 5px;
        }

        @media print {
            html {
                background-color: #fff;
            }

            .invoice-container {
                margin: 0;
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>



<!-- ปุ่มพิมพ์อยู่นอกแผ่นเอกสาร (ไม่ติดไปตอนพิมพ์) -->
<div class="print-button-container no-print">
    <button id="printButton"><?php echo $isTH ? '🖨️ พิมพ์ใบเสนอราคา' : '🖨️ Print quotation'; ?></button>
</div>
<div class="invoice-container">
    <div class="header">
        <div class="logo-section">
            <img src="https://report.localforyou.com/modules/signup/assets/img/localforyouwithwording.png" alt="Company logo" height="70" />
        </div>
        <div class="invoice-title-section">
            <h3 class="primary"><?php echo $isTH ? 'ใบเสนอราคา' : 'Quotation'; ?></h3>
        </div>
    </div>

    <div class="invoice-details">
        <?php if (!$isTH): ?>
        <div class="customer-info">
            <p><strong><?php echo htmlspecialchars($seller['name']); ?></strong></p>
            <p><?php echo htmlspecialchars($seller['address']); ?></p>
            <p><strong class="black"><?php echo $seller['idLabel']; ?>:</strong> <?php echo $seller['id']; ?></p>
            <p><strong class="black">Email:</strong> admin@localforyou.com</p>
        </div>
        <div class="invoice-info">
            <hr style="border-top: 1px solid #ccc; margin-bottom: 20px;">
            <div class="invoice-div"><strong>No.:</strong> <?php echo $quotationNumber;?></div>
            <div class="invoice-div"><strong>Date:</strong> <?php echo date('j M Y');?></div>
            <div class="invoice-div"><strong>Sales:</strong> <?php echo htmlspecialchars($saleName);?></div>
            <hr style="border-top: 1px solid #ccc; margin-top: 20px;">
        </div>
        <?php else: ?>
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
            <div class="invoice-div"><strong>เลขที่:</strong> <?php echo $quotationNumber;?></div>
            <div class="invoice-div"><strong>วันที่:</strong> <?php echo $dateThai;?></div>
            <div class="invoice-div"><strong>ผู้ขาย:</strong> <?php echo $saleName;?></div>
            <hr style="border-top: 1px solid #ccc; margin-top: 20px;">
        </div>
        <?php endif; ?>
    </div>


    <?php if (!$isTH): ?>
    <div class="customer-info">
        <p><strong>Customer</strong></p>
        <p><?php echo htmlspecialchars($companyName);?></p>
        <?php if ($contact !== ''): ?><p>Attn: <?php echo htmlspecialchars($contact);?></p><?php endif; ?>
        <p><?php echo htmlspecialchars($address);?></p>
        <?php if ($tax_id !== ''): ?><p><strong class="black">Tax ID:</strong> <?php echo htmlspecialchars($tax_id);?></p><?php endif; ?>
        <p><strong class="black">Email:</strong> <?php echo htmlspecialchars($email);?></p>
        <p><strong class="black">Phone:</strong> <?php echo htmlspecialchars($phone);?></p>
    </div>
    <?php else: ?>
    <div class="customer-info">
        <p><strong>ลูกค้า</strong></p>
        <p><?php echo $companyName;?></p>
        <p><?php echo $address;?></p>
        <p><strong class="black">เลขประจำตัวผู้เสียภาษี:</strong> <?php echo $tax_id;?> </p>
        <p><strong class="black">อีเมล:</strong> <?php echo $email;?></p>
        <p><strong class="black">เบอร์โทร:</strong> <?php echo $phone?></p>
    </div>
    <?php endif; ?>
    <div class="horizontal-divider"></div>



    <?php if (!$isTH):
        $showTax = ($country === 'AU'); // GST 10% ต่อบรรทัด เฉพาะ AU
    ?>
    <!-- รายการแบบใบแจ้งหนี้ Stripe: ส่วนลดเป็นแถวย่อยใต้สินค้า, AU มีคอลัมน์ Tax ต่อบรรทัด -->
    <table class="items-table">
        <thead>
        <tr>
            <th>Description</th>
            <th class="text-right" style="width: 8%;">Qty</th>
            <th class="text-right" style="width: 16%;">Unit price</th>
            <?php if ($showTax): ?><th class="text-right" style="width: 16%;">Tax</th><?php endif; ?>
            <th class="text-right" style="width: 16%;">Amount</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($tableData as $i => $item) {
            $qyt    = (float)$item['qyt'];
            $amount = (float)$item['amount'];

            if (isset($item['discount'])) {
                echo "<tr class='discount-row'>";
                echo "<td class='discount-label'>" . htmlspecialchars($item['discount']) . "</td>";
                echo "<td></td><td></td>" . ($showTax ? "<td></td>" : "");
                echo "<td class='text-right'>" . $money($amount) . "</td>";
                echo "</tr>";
                continue;
            }

            $description = $item['product'] ?? $item['setupfee'] ?? $item['addon'] ?? '';
            $lineTotal = $qyt * $amount;

            echo "<tr class='item-row'>";
            echo "<td>" . htmlspecialchars($description) . "</td>";
            echo "<td class='text-right'>" . number_format($qyt) . "</td>";
            echo "<td class='text-right'>" . $money($amount) . "</td>";
            if ($showTax) {
                // ฐานภาษี = ราคาบรรทัดนี้ หักส่วนลดที่อยู่ใต้บรรทัดนี้ (แถว discount ที่ตามมาติดกัน)
                $taxBase = $lineTotal;
                for ($j = $i + 1; isset($tableData[$j]['discount']); $j++) {
                    $taxBase += (float)$tableData[$j]['amount'];
                }
                echo "<td class='text-right'>10% (on " . $money($taxBase) . ")</td>";
            }
            echo "<td class='text-right'>" . $money($lineTotal) . "</td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
    <?php else: ?>
    <table>
        <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 50%;"><?php echo $isTH ? 'รายละเอียด' : 'Description'; ?></th>
            <th style="width: 15%;" class="text-right"><?php echo $isTH ? 'จำนวน' : 'Qty'; ?></th>
            <th style="width: 15%;" class="text-right"><?php echo $isTH ? 'ราคาต่อหน่วย' : 'Unit price'; ?></th>
            <th style="width: 15%;" class="text-right"><?php echo $isTH ? 'ยอดรวม' : 'Amount'; ?></th>
        </tr>
        </thead>
        <tbody>

        <?php
        $lineNo = 0;
        foreach ($tableData as $item) {
            $qyt    = $item['qyt'];
            $amount = $item['amount'];

            // ส่วนลด: แถวย่อยใต้สินค้า ไม่มีลำดับ/จำนวน/ราคาต่อหน่วย (แบบใบแจ้งหนี้ Stripe)
            if (isset($item['discount'])) {
                echo "<tr class='discount-row'>";
                echo "<td></td>";
                echo "<td class='discount-label'>" . htmlspecialchars($item['discount']) . "</td>";
                echo "<td></td><td></td>";
                echo "<td class='text-right'>" . ($isTH ? number_format($amount, 2) : $money($amount)) . "</td>";
                echo "</tr>";
                continue;
            }

            // ชื่อสินค้าอาจอยู่ในคีย์ที่ต่างกัน (product, setupfee, addon)
            $description = $item['product'] ?? $item['setupfee'] ?? $item['addon'] ?? '';
            $lineNo++;

            echo "<tr>";
            echo "<td>" . $lineNo . "</td>";
            echo "<td>" . htmlspecialchars($description) . "</td>";
            echo "<td class='text-right'>" . number_format($qyt) . "</td>";
            echo "<td class='text-right'>" . ($isTH ? number_format($amount, 2) : $money($amount)) . "</td>";
            echo "<td class='text-right'>" . ($isTH ? number_format($qyt * $amount, 2) : $money($qyt * $amount)) . "</td>";
            echo "</tr>";
        }
        ?>

        <!--<tr>
            <td>1</td>
            <td>First Month Free - TH</td>
            <td class="text-right">1</td>
            <td class="text-right">2,325.00</td>
            <td class="text-right">2,325.00</td>
        </tr>-->
        </tbody>
    </table>
    <?php endif; ?>

    <!-- <div class="total"> -->

    <?php if (!$isTH): ?>
    <!-- สรุปยอดแบบใบแจ้งหนี้ Stripe -->
    <table class="totals-table stripe-totals">
        <tbody>
        <tr>
            <td class="label">Subtotal</td>
            <td class="text-right amount"><?php echo $money($subtotalRaw);?></td>
        </tr>
        <tr>
            <td class="label">Total excluding tax</td>
            <td class="text-right amount"><?php echo $money($subtotalRaw);?></td>
        </tr>
        <?php if ($taxRaw > 0): ?>
        <tr>
            <td class="label"><?php echo htmlspecialchars($taxLabel); ?></td>
            <td class="text-right amount"><?php echo $money($taxRaw);?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="label">Total</td>
            <td class="text-right amount"><?php echo $money($grandtotalRaw);?></td>
        </tr>
        <tr class="amount-due">
            <td class="label">Amount due</td>
            <td class="text-right amount"><?php echo $money($grandtotalRaw);?></td>
        </tr>
        </tbody>
    </table>
    <?php else: ?>
    <table class="totals-table">
        <tbody>
        <tr>
            <td class="text-right"><strong>รวมเป็นเงิน</strong></td>
            <td class="text-right"><?php echo $grandtotal;?> บาท</td>
        </tr>
        <tr>
            <td class="text-right"><strong>ราคาก่อนภาษีมูลค่าเพิ่ม</strong></td>
            <td class="text-right"><?php echo $subtotal;?> บาท</td>
        </tr>
        <tr>
            <td class="text-right"><strong>ภาษีมูลค่าเพิ่ม 7%</strong></td>
            <td class="text-right"><?php echo $tax;?> บาท</td>
        </tr>
        <?php if ($isCorporate): ?>
        <tr>
            <td class="text-right"><strong>ภาษีเงินได้หัก ณ ที่จ่าย 3%</strong></td>
            <td class="text-right"><?php echo $withholding;?> บาท</td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="text-right"><strong>จำนวนเงินสุทธิที่ต้องชำระ</strong></td>
            <td class="text-right"><?php echo $netPayable;?> บาท</td>
        </tr>
        <tr>
            <td colspan="2">
                <hr style="border-top: 1px dashed #ccc; margin: 10px 0;">
            </td>
        </tr>
        </tbody>
    </table>
    <?php endif; ?>
    <!-- </div> -->

    <div style="clear:both;"></div>
    <?php if ($isTH): ?><p class="thai-amount">(<?php echo $thaiPrice;?>)</p><?php endif; ?>

    <div class="footer">
        <figure>
            <?php if (!$isTH): ?>
            <figcaption>Notes</figcaption>
            <ul class="dash">
                <li><?php echo ($country === 'AU') ? 'All prices are in AUD. GST 10% is added to the subtotal as shown above.' : 'Prices are in ' . htmlspecialchars($data['currency'] ?? '') . ' and exclude any applicable taxes unless shown above.'; ?></li>
                <li>Monthly subscription prices apply for the selected contract period.</li>
            </ul>
            <?php else: ?>
            <figcaption>หมายเหตุ</figcaption>
            <ul class="dash">
                <li>ราคานี้ได้รวมภาษีมูลค่าเพิ่ม 7% แล้ว</li>
                <li>เอกสารฉบับนี้สามารถแก้ไขได้ภายในวันที่ซื้อสินค้าเท่านั้น</li>
                <li>หากมีข้อผิดพลาดประการใดหรือประสงค์ขอคืนเงิน กรุณาแจ้งภายใน 7 วันหลังชำระเงิน มิฉะนั้นบริษัทขอสงวนสิทธิ์ไม่รับผิดชอบต่อกรณีดังกล่าว</li>
            </ul>
            <?php endif; ?>
        </figure>
    </div>
</div>
<script>
    document.getElementById('printButton').addEventListener('click', function() {
        window.print();
    });
</script>
</body>

</html>