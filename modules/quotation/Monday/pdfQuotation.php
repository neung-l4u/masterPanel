<?php
global $db;
include '../assets/db/db.php';
include "../assets/db/initDB.php";

date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$timestamp = date("Y-m-d H:i:s");
$dateThai = date("d/m/Y");

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

// --- ดึงข้อมูลใบเสนอราคา ---
$date = $invoiceData['date'];

// --- ดึงยอดรวม ---
$subtotal   = $summaryData['subtotal'];
$subtotal = number_format($subtotal, 2);
$tax        = $summaryData['tax'];
$tax = number_format($tax, 2);
$grandtotal = $summaryData['grandtotal'];
$grandtotal = number_format($grandtotal, 2);


?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบเสนอราคา</title>
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

        .invoice-container {
            /*width: 800px;*/
            margin: 0 auto;
            padding: 40px 40px;
            background-color: #fff;
            /*box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);*/
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
            margin-top: 150px;
            text-align: left;
            font-size: 10px;
            color: #999999;
        }

        .footer, figure{
            margin-left: 0px;
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
            text-align: center;
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
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>



<div class="invoice-container">
    <div class="header">
        <div class="logo-section">
            <img src="https://report.localforyou.com/modules/signup/assets/img/localforyouwithwording.png" alt="Company logo" height="70" />
        </div>
        <div class="invoice-title-section">
            <div class="print-button-container no-print">
                <button id="printButton">🖨️ พิมพ์ใบเสนอราคา</button>
            </div>
            <h3 class="primary">ใบเสนอราคา</h3>
        </div>
    </div>

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
            <div class="invoice-div"><strong>เลขที่:</strong> <?php echo $quotationNumber;?></div>
            <div class="invoice-div"><strong>วันที่:</strong> <?php echo $dateThai;?></div>
            <div class="invoice-div"><strong>ผู้ขาย:</strong> <?php echo $saleName;?></div>
            <hr style="border-top: 1px solid #ccc; margin-top: 20px;">
        </div>
    </div>


    <div class="customer-info">
        <p><strong>ลูกค้า</strong></p>
        <p><?php echo $companyName;?></p>
        <p><?php echo $address;?></p>
        <p><strong class="black">เลขประจำตัวผู้เสียภาษี:</strong> <?php echo $tax_id;?> </p>
        <p><strong class="black">อีเมล:</strong> <?php echo $email;?></p>
        <p><strong class="black">เบอร์โทร:</strong> <?php echo $phone?></p>
    </div>
    <div class="horizontal-divider"></div>



    <table>
        <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 50%;">รายละเอียด</th>
            <th style="width: 15%;" class="text-right">จำนวน</th>
            <th style="width: 15%;" class="text-right">ราคาต่อหน่วย</th>
            <th style="width: 15%;" class="text-right">ยอดรวม</th>
        </tr>
        </thead>
        <tbody>

        <?php
        foreach ($tableData as $index => $item) {
            $qyt    = $item['qyt'];
            $amount = $item['amount'];
            // ชื่อสินค้าอาจอยู่ในคีย์ที่ต่างกัน (product, setupfee, addon)
            $description = $item['product'] ?? $item['setupfee'] ?? $item['addon'] ?? '';

            echo "<tr>";
            echo "<td>" . ($index + 1) . "</td>";
            echo "<td>" . htmlspecialchars($description) . "</td>";
            echo "<td class='text-right'>" . number_format($qyt) . "</td>";
            echo "<td class='text-right'>" . number_format($amount, 2) . "</td>";
            echo "<td class='text-right'>" . number_format($qyt * $amount, 2) . "</td>";
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

    <!-- <div class="total"> -->

    <table class="totals-table">
        <tbody>
        <tr>
            <td class="text-right"><strong>รวมเป็นเงิน</strong></td>
            <td class="text-right"><?php echo $subtotal;?> บาท</td>
        </tr>
        <tr>
            <td class="text-right"><strong>ภาษีมูลค่าเพิ่ม 7%</strong></td>
            <td class="text-right"><?php echo $tax;?> บาท</td>
        </tr>
        <tr>
            <td class="text-right"><strong>จำนวนเงินรวมทั้งสิ้น</strong></td>
            <td class="text-right"><?php echo $grandtotal;?> บาท</td>
        </tr>
        <tr>
            <td colspan="2">
                <hr style="border-top: 1px dashed #ccc; margin: 10px 0;">
            </td>
        </tr>
        </tbody>
    </table>
    <!-- </div> -->

    <div style="clear:both;"></div>
    <p class="thai-amount">(<?php echo $thaiPrice;?>)</p>

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
<script>
    document.getElementById('printButton').addEventListener('click', function() {
        window.print();
    });
</script>
</body>

</html>