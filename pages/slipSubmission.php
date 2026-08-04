<?php
session_start();
if (empty($_SESSION['id'])) {
    header('Location: ../index.php');
    exit;
}
global $db;
$submittedBy = $_SESSION['name'] ?? $_SESSION['nickName'] ?? 'Unknown';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <i class="bi bi-upload mr-2"></i> ส่งหลักฐานการชำระเงิน
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item active">ส่งหลักฐานการชำระเงิน</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-9 col-sm-12">

                <!-- Step 1: รายการรอส่งสลิป -->
                <div class="card" id="cardSearch" style="max-width:100%;">
                    <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><i class="bi bi-list-check mr-2"></i>รายการรอส่งหลักฐาน</h5>
                        <span class="badge badge-light text-primary" id="pendingCount">-</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="input-group mb-3" style="max-width:340px;">
                            <input type="text" id="pendingSearch" class="form-control form-control-sm" placeholder="ค้นหาชื่อร้าน / Invoice ID...">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-outline-secondary" id="btnPendingSearch" type="button"><i class="bi bi-search"></i></button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="pendingSlipTable" class="table table-hover table-sm table-borderless" style="width:100%;font-size:13px;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ชื่อร้าน</th>
                                        <th>Invoice No.</th>
                                        <th class="text-right">ยอด (฿)</th>
                                        <th>วันที่บิล</th>
                                        <th class="text-center"></th>
                                    </tr>
                                </thead>
                                <tbody id="pendingSlipBody">
                                    <tr><td colspan="5" class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm"></span> กำลังโหลด...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Step 2: ข้อมูล Invoice + Upload สลิป -->
                <div class="card" id="cardInvoice" style="display:none;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-receipt mr-2"></i>ขั้นตอนที่ 2 — ยืนยันและส่งหลักฐาน</h5>
                    </div>
                    <div class="card-body">

                        <!-- Invoice Info Labels -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="text-muted" style="font-size:11px;">ชื่อร้าน</label>
                                <div id="labelShopName" class="font-weight-bold" style="font-size:15px;"></div>
                            </div>
                            <div class="col-6">
                                <label class="text-muted" style="font-size:11px;">Invoice No.</label>
                                <div id="labelInvoiceID" class="font-weight-bold text-primary" style="font-size:15px;"></div>
                            </div>
                        </div>
                        <!-- Countdown timer -->
                        <!-- <div id="countdownBox" class="alert alert-info mb-3" style="display:none; padding:10px 14px; border-radius:8px;">
                            <div class="d-flex align-items-center justify-content-between">
                                <span style="font-size:13px;">
                                    <i class="bi bi-clock-history mr-1"></i>
                                    รายการชำระเงินจะหมดอายุในอีก
                                </span>
                                <span id="countdownTimer" style="font-family:'Courier New',monospace; font-size:16px; font-weight:700;">10:00</span>
                            </div>
                        </div> -->

                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="text-muted" style="font-size:11px;">ยอดที่ต้องชำระ</label>
                                <div id="labelAmount" class="font-weight-bold text-success" style="font-size:18px;"></div>
                            </div>
                            <div class="col-6">
                                <label class="text-muted" style="font-size:11px;">วันที่ออก Invoice</label>
                                <div id="labelDate" class="font-weight-bold" style="font-size:14px;"></div>
                            </div>
                        </div>

                        <!-- Bank info card -->
                        <div style="border:1px solid #e0e0e0; border-radius:12px; overflow:hidden; margin-bottom:16px;">
                            <div style="background:#1a6b2f; padding:10px 16px; display:flex; align-items:center;">
                                <span style="font-size:14px; font-weight:700; color:#fff;">K PLUS</span>
                                <span style="font-size:11px; color:rgba(255,255,255,.7); margin-left:8px;">ธนาคารกสิกรไทย</span>
                            </div>
                            <div class="d-flex" style="min-height:130px;">
                                <div style="flex:1; padding:14px; border-right:1px solid #f0f0f0;">
                                    <div class="mb-1">
                                        <span style="font-size:11px; color:#999;">ชื่อบัญชี</span><br>
                                        <strong style="font-size:13px;">บจก. โลคอล อีทส์</strong>
                                    </div>
                                    <div class="mb-1">
                                        <span style="font-size:11px; color:#999;">สาขา</span><br>
                                        <strong style="font-size:13px;">สาขาสำนักแจ้งวัฒนะเมืองทองธานี</strong>
                                    </div>
                                    <div class="mb-2">
                                        <span style="font-size:11px; color:#999;">หมายเลขบัญชี</span><br>
                                        <strong style="font-size:16px; color:#1a6b2f;" id="slipBankNumber">056-1-85639-7</strong>
                                    </div>
                                    <button type="button" class="btn btn-sm"
                                        style="background:#1a6b2f; color:#fff; border:none; border-radius:6px; padding:4px 14px; font-size:12px;"
                                        onclick="navigator.clipboard.writeText('ชื่อบัญชี : บจก. โลคอล อีทส์\nสาขา : สาขาสำนักแจ้งวัฒนะเมืองทองธานี\nหมายเลขบัญชี : 056-1-85639-7'); this.innerText='✓ คัดลอกแล้ว'; var b=this; setTimeout(function(){ b.innerText='คัดลอก'; }, 1500);">คัดลอก</button>
                                </div>
                                <div style="flex:1; padding:14px; background:#fafafa;">
                                    <strong style="font-size:12px; color:#555;">วิธีโอนเงิน</strong>
                                    <ol style="list-style:decimal; font-size:12px; color:#666; padding-left:16px; line-height:1.9; margin-top:8px; margin-bottom:0;">
                                        <li>คัดลอกหมายเลขบัญชีโดยกดปุ่ม 'คัดลอก'</li>
                                        <li>ไปที่ 'โอนเงิน' → 'บัญชีกสิกรไทย'</li>
                                        <li>วางเลขบัญชีและกรอกจำนวนเงิน</li>
                                        <li>ถ่ายสลิปแล้วอัปโหลดด้านล่าง</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Upload Form -->
                        <form id="slipForm" enctype="multipart/form-data">
                            <input type="hidden" id="invoiceIdHidden" name="invoice_id" value="">

                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="bi bi-image mr-1"></i> แนบสลิปการโอนเงิน
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="slipFile" name="slip"
                                        accept="image/*,application/pdf" required>
                                    <label class="custom-file-label" for="slipFile">เลือกไฟล์ (JPG, PNG, PDF, max 10MB)</label>
                                </div>
                                <div id="slipPreviewWrap" class="mt-2" style="display:none;">
                                    <img id="slipPreview" src="" alt="preview"
                                        style="max-height:200px;border-radius:6px;border:1px solid #dee2e6;">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="bi bi-chat-left-text mr-1"></i> หมายเหตุ (ถ้ามี)
                                </label>
                                <textarea class="form-control" id="slipNote" name="note" rows="2"
                                    placeholder="เช่น โอนวันที่... หรือข้อมูลเพิ่มเติม"></textarea>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <button type="button" class="btn btn-outline-secondary" id="btnResetSearch">
                                    <i class="bi bi-arrow-left"></i> ค้นหาใหม่
                                </button>
                                <button type="submit" class="btn btn-success btn-lg" id="btnSubmitSlip">
                                    <i class="bi bi-send"></i> ส่งหลักฐาน
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Step 3: Success -->
                <div class="card border-success" id="cardSuccess" style="display:none;">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-check-circle-fill text-success" style="font-size:60px;"></i>
                        <h4 class="mt-3 text-success">ส่งหลักฐานเรียบร้อยแล้ว!</h4>
                        <p class="text-muted">ทีม Billing จะตรวจสอบและส่ง Receipt ให้ลูกค้าต่อไป</p>
                        <div class="mt-2">
                            <span class="badge badge-light border" style="font-size:13px;padding:8px 16px;">
                                Invoice: <strong id="successInvoiceID"></strong>
                            </span>
                        </div>
                        <button class="btn btn-outline-primary mt-4" id="btnSubmitAnother">
                            <i class="bi bi-plus-circle mr-1"></i> ส่งหลักฐานร้านอื่น
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
window.addEventListener('load', function() {
    const submittedBy = <?php echo json_encode($submittedBy); ?>;
    let currentInvoiceID = '';

    // --- Load pending list ---
    function loadPendingList(search) {
        search = search || '';
        $('#pendingSlipBody').html('<tr><td colspan="5" class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm"></span> กำลังโหลด...</td></tr>');
        $.ajax({
            url: 'pages/tableRendering/pendingSlipTH.php',
            type: 'POST',
            dataType: 'json',
            data: { search: search },
            success: function(res) {
                if (!res.success || !res.data.length) {
                    $('#pendingSlipBody').html('<tr><td colspan="5" class="text-center text-muted py-3">ไม่มีรายการรอส่งหลักฐาน</td></tr>');
                    $('#pendingCount').text('0');
                    return;
                }
                $('#pendingCount').text(res.data.length + ' รายการ');
                let html = '';
                res.data.forEach(function(r) {
                    const dateStr = r.billingDate || r.createdAt || '';
                    const displayDate = dateStr ? dateStr.substring(0, 10) : '-';
                    const amount = parseFloat(r.amount || 0).toLocaleString('th-TH', { minimumFractionDigits: 2 });
                    html += '<tr>'
                        + '<td class="font-weight-bold">' + $('<span>').text(r.shopName).html() + '</td>'
                        + '<td class="text-primary">' + $('<span>').text(r.invoiceID).html() + '</td>'
                        + '<td class="text-right">฿' + amount + '</td>'
                        + '<td class="text-muted">' + displayDate + '</td>'
                        + '<td class="text-center"><button class="btn btn-sm btn-success btnSelectRow" '
                        +   'data-id="' + r.id + '" data-invoiceid="' + $('<span>').text(r.invoiceID).html() + '" '
                        +   'data-shop="' + $('<span>').text(r.shopName).html() + '" '
                        +   'data-amount="' + r.amount + '" data-date="' + displayDate + '">'
                        +   '<i class="bi bi-upload mr-1"></i>ส่งหลักฐาน</button></td>'
                        + '</tr>';
                });
                $('#pendingSlipBody').html(html);
            },
            error: function() {
                $('#pendingSlipBody').html('<tr><td colspan="5" class="text-center text-danger py-3">เกิดข้อผิดพลาดในการโหลด</td></tr>');
            }
        });
    }

    loadPendingList();

    // Search
    $('#btnPendingSearch').on('click', function() {
        loadPendingList($('#pendingSearch').val().trim());
    });
    $('#pendingSearch').on('keydown', function(e) {
        if (e.key === 'Enter') loadPendingList($(this).val().trim());
    });

    // Click ปุ่มส่งหลักฐาน
    $(document).on('click', '.btnSelectRow', function() {
        const $btn = $(this);
        currentInvoiceID = $btn.data('invoiceid');
        $('#invoiceIdHidden').val($btn.data('id'));
        $('#labelShopName').text($btn.data('shop'));
        $('#labelInvoiceID').text(currentInvoiceID);
        $('#labelAmount').text('฿' + parseFloat($btn.data('amount') || 0).toLocaleString('th-TH', { minimumFractionDigits: 2 }));
        $('#labelDate').text($btn.data('date'));
        $('#cardSearch').hide();
        $('#cardInvoice').show();
        window.scrollTo(0, 0);
    });

    // File input label update + preview
    $('#slipFile').on('change', function() {
        const file = this.files[0];
        if (!file) return;
        $(this).next('.custom-file-label').text(file.name);
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => {
                $('#slipPreview').attr('src', e.target.result);
                $('#slipPreviewWrap').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#slipPreviewWrap').hide();
        }
    });

    // Reset to list
    $('#btnResetSearch').on('click', function() {
        $('#cardInvoice').hide();
        $('#cardSearch').show();
        $('#slipForm')[0].reset();
        $('#slipPreviewWrap').hide();
        $('#slipFile').next('.custom-file-label').text('เลือกไฟล์ (JPG, PNG, PDF, max 10MB)');
        $('#btnSubmitSlip').prop('disabled', false);
        loadPendingList($('#pendingSearch').val().trim());
    });

    // Submit slip form
    $('#slipForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#btnSubmitSlip');
        if (!$('#slipFile')[0].files[0]) {
            alert('กรุณาเลือกไฟล์สลิป');
            return;
        }
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังส่ง...');

        const fd = new FormData(this);
        fd.append('submittedBy', submittedBy);

        $.ajax({
            url: 'pages/tableRendering/submitSlipTH.php',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#successInvoiceID').text(currentInvoiceID);
                    $('#cardInvoice').hide();
                    $('#cardSuccess').show();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (res.message || 'ไม่ทราบสาเหตุ'));
                    $btn.prop('disabled', false).html('<i class="bi bi-send"></i> ส่งหลักฐาน');
                }
            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                $btn.prop('disabled', false).html('<i class="bi bi-send"></i> ส่งหลักฐาน');
            }
        });
    });

    // Submit another
    $('#btnSubmitAnother').on('click', function() {
        $('#cardSuccess').hide();
        $('#cardSearch').show();
        $('#slipForm')[0].reset();
        $('#slipPreviewWrap').hide();
        $('#slipFile').next('.custom-file-label').text('เลือกไฟล์ (JPG, PNG, PDF, max 10MB)');
        currentInvoiceID = '';
        loadPendingList($('#pendingSearch').val().trim());
    });

});
</script>
