<?php
/**
 * modules/customeruploadslip/index.php
 * หน้า upload slip สำหรับลูกค้า TH — ไม่ต้อง login
 * URL: /modules/customeruploadslip/?invoiceID=KZ8HZ9AV-0011
 */
date_default_timezone_set('Asia/Bangkok');

$root = dirname(__DIR__, 2);
require_once $root . '/assets/db/db.php';
require_once $root . '/assets/db/initDB.php';

$invoiceIDParam = trim($_GET['invoiceID'] ?? '');

$inv = null;
if ($invoiceIDParam !== '') {
    $rows = $db->query(
        'SELECT i.`id`, i.`invoiceID`, i.`amount`, i.`status`, i.`product`, i.`createdAt`,
                c.`name` AS shopName, c.`email`
         FROM `thInvoice` i
         JOIN `thCustomer` c ON c.`id` = i.`customer_id`
         WHERE i.`invoiceID` = ? LIMIT 1',
        $invoiceIDParam
    )->fetchAll();
    $inv = $rows[0] ?? null;
}

$product  = json_decode($inv['product'] ?? '', true) ?: [];
$summary  = $product['summary'] ?? [];
$netPay   = (float)($summary['net_payment'] ?? $inv['amount'] ?? 0);
$invDate  = $product['quotation'][0]['date'] ?? ($inv ? date('d/m/Y', strtotime($inv['createdAt'])) : '');
$alreadySent = ($inv && $inv['status'] === 'sent');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อัปโหลดสลิปชำระเงิน — Local For You</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f0f2f5; font-family: Arial, Helvetica, sans-serif; min-height: 100vh; }
        .brand-bar { background: #1c3652; color: #fff; padding: 16px 0; margin-bottom: 32px; }
        .brand-bar .logo-text { font-size: 18px; font-weight: bold; letter-spacing: 0.5px; }
        .brand-bar .sub-text { font-size: 12px; opacity: 0.7; }
        .main-card { max-width: 960px; margin: 0 auto 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: none; }
        /* single-column fallback (no invoice) */
        .narrow-card { max-width: 560px; margin: 0 auto 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: none; }
        .section-label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .section-value { font-size: 15px; font-weight: 600; color: #222; }
        .amount-big { font-size: 28px; font-weight: 700; color: #00796b; }
        .invoice-chip { display: inline-block; background: #e8f5e9; color: #00796b; border-radius: 20px; padding: 3px 14px; font-size: 13px; font-weight: 700; }
        /* Left panel */
        .panel-left { padding: 28px 24px; border-right: 1px solid #e8e8e8; background: #fafbfc; border-radius: 12px 0 0 12px; }
        .bank-card { border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden; margin-bottom: 18px; background: #fff; }
        .bank-header { background: linear-gradient(135deg, #1a9c4e 0%, #148a42 100%); color: #fff; padding: 14px 18px; display: flex; align-items: center; gap: 12px; }
        .bank-logo-box { width: 40px; height: 40px; background: #fff; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .bank-header-text .bank-name { font-size: 16px; font-weight: 700; line-height: 1.2; }
        .bank-header-text .bank-sub { font-size: 11px; opacity: 0.85; }
        .bank-body { padding: 16px 18px; }
        .bank-field-label { font-size: 11px; color: #999; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.3px; }
        .bank-field-value { font-size: 14px; font-weight: 600; color: #333; margin-bottom: 12px; }
        .acct-row { display: flex; align-items: center; gap: 10px; margin-bottom: 4px; }
        .acct-number { font-size: 22px; font-weight: 700; letter-spacing: 2px; color: #1c3652; font-family: 'Courier New', monospace; }
        .how-to-section { background: #fff; border: 1px solid #e8e8e8; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px; }
        .how-to-title { font-size: 12px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
        .how-to-list { list-style: none; padding: 0; margin: 0; }
        .how-to-list li { display: flex; align-items: flex-start; gap: 8px; padding: 5px 0; font-size: 13px; color: #555; border-bottom: 1px solid #f5f5f5; }
        .how-to-list li:last-child { border-bottom: none; }
        .how-to-step { width: 20px; height: 20px; background: #e8f5e9; color: #1a9c4e; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; flex-shrink: 0; margin-top: 1px; }
        .amount-row { display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%); border-radius: 10px; padding: 14px 18px; border: 1px solid #c8e6c9; }
        .amount-label { font-size: 13px; color: #555; }
        .amount-value { font-size: 26px; font-weight: 700; color: #00796b; }
        /* Right panel */
        .panel-right { padding: 28px 24px; }
        .panel-right-title { font-size: 15px; font-weight: 700; color: #1c3652; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #e8f0fe; }
        .drop-zone { border: 2px dashed #cfd8dc; border-radius: 12px; padding: 32px 16px; text-align: center; cursor: pointer; transition: all 0.2s; background: #fafafa; }
        .drop-zone:hover { border-color: #1a73e8; background: #f0f4ff; }
        .drop-zone.drag-over { border-color: #1a73e8; background: #e8f0fe; }
        .drop-zone.has-file { border-color: #1a9c4e; background: #f1f8f4; border-style: solid; }
        .drop-zone .drop-icon { font-size: 36px; color: #90a4ae; margin-bottom: 8px; }
        .drop-zone.has-file .drop-icon { color: #1a9c4e; }
        #slipPreview { max-height: 180px; border-radius: 10px; border: 1px solid #e0e0e0; margin-top: 12px; display: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .btn-submit { background: linear-gradient(135deg, #1a73e8 0%, #1558b0 100%); border: none; font-size: 16px; padding: 15px; border-radius: 10px; font-weight: 600; width: 100%; color: #fff; letter-spacing: 0.3px; box-shadow: 0 4px 12px rgba(26,115,232,0.3); transition: all 0.2s; }
        .btn-submit:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(26,115,232,0.4); }
        .btn-submit:disabled { background: #b0bec5; box-shadow: none; }
        .invoice-info-bar { background: #fff; border: 1px solid #e8e8e8; border-radius: 10px; padding: 14px 16px; margin-bottom: 20px; }
        .note-label { font-size: 12px; font-weight: 700; color: #555; margin-bottom: 6px; }
        .step-success { text-align: center; padding: 60px 20px; }
        .step-success .check-icon { font-size: 72px; color: #1a9c4e; }
        footer { font-size: 12px; color: #aaa; text-align: center; padding: 20px 0 30px; }
        @media (max-width: 767px) {
            .panel-left { border-right: none; border-bottom: 1px solid #e8e8e8; border-radius: 12px 12px 0 0; }
            .main-card { border-radius: 12px; }
        }
    </style>
</head>
<body>

<div class="brand-bar">
    <div class="container" style="display:flex; align-items:center; gap:14px;">
        <img src="https://report.localforyou.com/modules/signup/assets/img/newL4U-logo-100x100-2.png"
            alt="Local For You" height="40" style="border-radius:8px; flex-shrink:0;">
        <div>
            <div class="logo-text">Local For You</div>
            <div class="sub-text">Invoice Payment — อัปโหลดสลิปชำระเงิน</div>
        </div>
    </div>
</div>

<div class="container">

<?php if ($inv === null): ?>
    <!-- ไม่มี invoiceID ใน URL: ให้ลูกค้ากรอก (single column) -->
    <div class="narrow-card card">
        <div class="card-body p-4">
            <h5 class="mb-1"><i class="bi bi-search mr-1"></i> ค้นหาใบแจ้งหนี้</h5>
            <p class="text-muted" style="font-size:13px; margin-bottom:20px;">กรอกหมายเลขใบแจ้งหนี้ที่ได้รับทางอีเมล</p>
            <form id="formFind">
                <div class="input-group mb-2">
                    <input type="text" id="inputInvoiceID" class="form-control form-control-lg"
                        placeholder="เช่น KZ8HZ9AV-0011" autocomplete="off" required>
                    <div class="input-group-append">
                        <button class="btn btn-primary btn-lg" type="submit">
                            <i class="bi bi-search"></i> ค้นหา
                        </button>
                    </div>
                </div>
                <div id="findError" class="alert alert-warning mt-2" style="display:none;"></div>
            </form>
        </div>
    </div>

<?php elseif ($alreadySent): ?>
    <!-- จ่ายแล้ว (single column) -->
    <div class="narrow-card card">
        <div class="card-body">
            <div class="step-success">
                <div class="check-icon"><i class="bi bi-check-circle-fill"></i></div>
                <h5 class="mt-3">Invoice นี้ได้รับการชำระเงินแล้ว</h5>
                <p class="text-muted">หากมีข้อสงสัย กรุณาติดต่อทีม Local For You</p>
                <span class="invoice-chip"><?= htmlspecialchars($inv['invoiceID']) ?></span>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- 2-column layout: ซ้าย=ข้อมูลธนาคาร, ขวา=form upload -->
    <div class="main-card card">
        <div class="row no-gutters">

            <!-- ===== LEFT: ข้อมูลธนาคาร + วิธีโอน ===== -->
            <div class="col-md-6 panel-left">

                <!-- Invoice info bar -->
                <div class="invoice-info-bar mb-3">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                        <div>
                            <div class="section-label">บริษัท / ร้านค้า</div>
                            <div class="section-value"><?= htmlspecialchars($inv['shopName']) ?></div>
                        </div>
                        <span class="invoice-chip"><?= htmlspecialchars($inv['invoiceID']) ?></span>
                    </div>
                    <div>
                        <div class="section-label">วันที่ออกใบแจ้งหนี้</div>
                        <div style="font-size:13px; font-weight:600; color:#444;"><?= htmlspecialchars($invDate) ?></div>
                    </div>
                </div>

                <!-- Bank card -->
                <div class="bank-card">
                    <div class="bank-header">
                        <div class="bank-logo-box">
                            <img src="/modules/customeruploadslip/assets/img/k-bank.png" alt="KBank" style="width:32px; height:32px; object-fit:contain; border-radius:6px;">
                        </div>
                        <div class="bank-header-text">
                            <div class="bank-name">K PLUS — กสิกรไทย</div>
                            <div class="bank-sub">Kasikorn Bank</div>
                        </div>
                    </div>
                    <div class="bank-body">
                        <div class="bank-field-label">ชื่อบัญชี</div>
                        <div class="bank-field-value">บจก. โลคอล อีทส์ (สาขาใหญ่)</div>

                        <div class="bank-field-label">สาขา</div>
                        <div class="bank-field-value">สาขาสำนักแจ้งวัฒนะเมืองทองธานี</div>

                        <div class="bank-field-label">หมายเลขบัญชี</div>
                        <div class="acct-row">
                            <span class="acct-number" id="acctNumber">056-1-85639-7</span>
                            <button type="button" class="btn btn-sm btn-outline-success" id="btnCopyAcct" style="white-space:nowrap;">
                                <i class="bi bi-clipboard"></i> คัดลอก
                            </button>
                        </div>
                    </div>
                </div>

                <!-- How to -->
                <div class="how-to-section mb-3">
                    <div class="how-to-title"><i class="bi bi-list-ol mr-1"></i>วิธีโอนเงิน</div>
                    <ul class="how-to-list">
                        <li><span class="how-to-step">1</span><span>กดปุ่ม "คัดลอก" เพื่อคัดลอกเลขบัญชี</span></li>
                        <li><span class="how-to-step">2</span><span>เปิดแอปธนาคาร → ไปที่ "โอนเงิน"</span></li>
                        <li><span class="how-to-step">3</span><span>เลือก "บัญชีกสิกรไทย" แล้ววางเลขบัญชี</span></li>
                        <li><span class="how-to-step">4</span><span>กรอกจำนวนเงินและยืนยันการโอน</span></li>
                        <li><span class="how-to-step">5</span><span>ถ่ายรูปสลิปแล้วอัปโหลดทางด้านขวา</span></li>
                    </ul>
                </div>

                <!-- Amount -->
                <div class="amount-row">
                    <div>
                        <div class="amount-label">ยอดที่ต้องชำระ (สุทธิ)</div>
                        <div style="font-size:11px; color:#888;">หลังหักภาษี ณ ที่จ่าย 3%</div>
                    </div>
                    <div class="amount-value">฿<?= number_format($netPay, 2) ?></div>
                </div>

            </div><!-- /panel-left -->

            <!-- ===== RIGHT: form upload slip ===== -->
            <div class="col-md-6 panel-right">

                <div class="panel-right-title">
                    <i class="bi bi-upload mr-1"></i> อัปโหลดสลิปชำระเงิน
                </div>

                <form id="slipForm" enctype="multipart/form-data">
                    <input type="hidden" name="invoice_id" value="<?= (int)$inv['id'] ?>">

                    <div class="form-group mb-3">
                        <div class="drop-zone" id="dropZone">
                            <div class="drop-icon"><i class="bi bi-cloud-upload"></i></div>
                            <div id="dropText" style="font-size:14px; color:#607d8b; line-height:1.6;">
                                ลากไฟล์มาวางที่นี่<br>
                                <span style="color:#1a73e8; font-weight:600;">หรือคลิกเพื่อเลือกไฟล์</span>
                            </div>
                            <div style="font-size:11px; color:#bbb; margin-top:6px;">JPG, PNG, PDF · ไม่เกิน 10MB</div>
                            <input type="file" id="slipFile" name="slip" accept="image/*,application/pdf"
                                style="display:none;" required>
                        </div>
                        <img id="slipPreview" src="" alt="preview" style="max-width:100%; display:none;">
                    </div>

                    <div class="form-group mb-4">
                        <div class="note-label"><i class="bi bi-chat-left-text mr-1"></i>หมายเหตุ (ถ้ามี)</div>
                        <textarea class="form-control" name="note" rows="4"
                            style="font-size:14px; border-radius:8px;"
                            placeholder="เช่น โอนเมื่อวันที่... หรือข้อมูลเพิ่มเติม"></textarea>
                    </div>

                    <button type="submit" class="btn btn-submit" id="btnSubmit">
                        <i class="bi bi-send mr-1"></i> ส่งหลักฐานการชำระเงิน
                    </button>
                </form>

                <!-- Success state -->
                <div id="successBox" class="step-success" style="display:none;">
                    <div class="check-icon"><i class="bi bi-check-circle-fill"></i></div>
                    <h5 class="mt-3" style="color:#1a9c4e;">ส่งหลักฐานเรียบร้อยแล้ว!</h5>
                    <p class="text-muted" style="font-size:14px;">ทีม Billing จะตรวจสอบและส่งใบเสร็จให้ท่านทางอีเมล</p>
                    <span class="invoice-chip" style="font-size:14px; padding:6px 18px;"><?= htmlspecialchars($inv['invoiceID']) ?></span>
                </div>

            </div><!-- /panel-right -->

        </div><!-- /row -->
    </div><!-- /main-card -->

<?php endif; ?>

</div><!-- /container -->

<footer>© <?= date('Y') ?> Local For You Co., Ltd. — All rights reserved.</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
$(function () {

    // --- คัดลอกเลขบัญชี ---
    $('#btnCopyAcct').on('click', function () {
        const acct = $('#acctNumber').text().trim();
        navigator.clipboard.writeText(acct).then(function () {
            const $btn = $('#btnCopyAcct');
            $btn.html('<i class="bi bi-check-lg"></i> คัดลอกแล้ว').addClass('btn-success').removeClass('btn-outline-success');
            setTimeout(function () {
                $btn.html('<i class="bi bi-clipboard"></i> คัดลอก').removeClass('btn-success').addClass('btn-outline-success');
            }, 2000);
        });
    });

    // --- ค้นหาเมื่อไม่มี invoiceID ใน URL ---
    $('#formFind').on('submit', function (e) {
        e.preventDefault();
        const id = $('#inputInvoiceID').val().trim();
        if (!id) return;
        $('#findError').hide();
        window.location.href = '?invoiceID=' + encodeURIComponent(id);
    });

    // --- Drop zone ---
    const $zone = $('#dropZone');
    const $input = $('#slipFile');

    $zone.on('click', function () { $input.trigger('click'); });

    $zone.on('dragover dragenter', function (e) {
        e.preventDefault(); e.stopPropagation();
        $zone.addClass('drag-over');
    }).on('dragleave drop', function (e) {
        e.preventDefault(); e.stopPropagation();
        $zone.removeClass('drag-over');
        if (e.type === 'drop') {
            const files = e.originalEvent.dataTransfer.files;
            if (files.length) handleFile(files[0]);
        }
    });

    $input.on('change', function () {
        if (this.files[0]) handleFile(this.files[0]);
    });

    function handleFile(file) {
        $input[0] && ($input[0]._file = file);
        // Manually assign to file input via DataTransfer
        const dt = new DataTransfer();
        dt.items.add(file);
        $input[0].files = dt.files;

        $zone.addClass('has-file');
        $('#dropText').html('<span style="color:#00796b;font-weight:bold;"><i class="bi bi-file-earmark-check"></i> ' + file.name + '</span>');

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => $('#slipPreview').attr('src', e.target.result).show();
            reader.readAsDataURL(file);
        } else {
            $('#slipPreview').hide();
        }
    }

    // --- Submit ---
    $('#slipForm').on('submit', function (e) {
        e.preventDefault();
        if (!$input[0].files[0]) {
            alert('กรุณาเลือกไฟล์สลิป');
            return;
        }
        const $btn = $('#btnSubmit');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-2"></span>กำลังส่ง...');

        const fd = new FormData(this);
        $.ajax({
            url: 'assets/api/submitSlip.php',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#slipForm').hide();
                    $('#successBox').show();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (res.message || 'ไม่ทราบสาเหตุ'));
                    $btn.prop('disabled', false).html('<i class="bi bi-send mr-1"></i> ส่งหลักฐานการชำระเงิน');
                }
            },
            error: function () {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                $btn.prop('disabled', false).html('<i class="bi bi-send mr-1"></i> ส่งหลักฐานการชำระเงิน');
            }
        });
    });

});
</script>
</body>
</html>
