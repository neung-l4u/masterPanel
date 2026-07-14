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

                <!-- Step 1: ค้นหาร้าน -->
                <div class="card" id="cardSearch">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-search mr-2"></i>ขั้นตอนที่ 1 — ค้นหาร้านค้า</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted" style="font-size:13px;">
                            กรอกชื่อร้านเพื่อดึงข้อมูล Invoice รอบบิลล่าสุด
                        </p>
                        <div class="position-relative">
                            <div class="input-group">
                                <input type="text" id="shopSearchInput" class="form-control"
                                    placeholder="ชื่อร้าน เช่น Great Cafe, Face Holistic..." autocomplete="off">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" id="btnSearchShop" type="button">
                                        <i class="bi bi-search"></i> ค้นหา
                                    </button>
                                </div>
                            </div>
                            <ul id="shopSuggestList" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:9999;list-style:none;margin:0;padding:0;background:#fff;border:1px solid #dee2e6;border-top:none;border-radius:0 0 6px 6px;box-shadow:0 4px 12px rgba(0,0,0,0.1);max-height:240px;overflow-y:auto;"></ul>
                        </div>
                        <div id="searchError" class="alert alert-warning mt-3" style="display:none;"></div>
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
                        <div id="countdownBox" class="alert alert-info mb-3" style="display:none; padding:10px 14px; border-radius:8px;">
                            <div class="d-flex align-items-center justify-content-between">
                                <span style="font-size:13px;">
                                    <i class="bi bi-clock-history mr-1"></i>
                                    รายการชำระเงินจะหมดอายุในอีก
                                </span>
                                <span id="countdownTimer" style="font-family:'Courier New',monospace; font-size:16px; font-weight:700;">10:00</span>
                            </div>
                        </div>

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
    let countdownInterval = null;

    // --- Countdown timer helpers ---
    function pad(n) { return n < 10 ? '0' + n : n; }
    function stopCountdown() {
        if (countdownInterval) { clearInterval(countdownInterval); countdownInterval = null; }
    }
    function startCountdown(expireAt) {
        stopCountdown();
        const $box = $('#countdownBox');
        const $timer = $('#countdownTimer');
        $box.show().removeClass('alert-danger').addClass('alert-info');

        function tick() {
            const now = Date.now();
            const diff = expireAt - now;
            if (diff <= 0) {
                $timer.text('00:00');
                $box.removeClass('alert-info').addClass('alert-danger');
                $box.find('span:first').html('<i class="bi bi-exclamation-circle-fill mr-1"></i> รายการชำระเงินหมดอายุแล้ว');
                $('#btnSubmitSlip').prop('disabled', true);
                stopCountdown();
                return;
            }
            const m = Math.floor(diff / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            $timer.text(pad(m) + ':' + pad(s));
        }
        tick();
        countdownInterval = setInterval(tick, 1000);
    }

    // --- Autocomplete suggest ---
    let suggestTimeout = null;

    $('#shopSearchInput').on('input', function() {
        const q = $(this).val().trim();
        clearTimeout(suggestTimeout);
        $('#shopSuggestList').hide().empty();
        if (q.length < 1) return;
        suggestTimeout = setTimeout(function() {
            $.ajax({
                url: 'pages/tableRendering/searchInvoiceTH.php',
                type: 'POST',
                dataType: 'json',
                data: { q: q, mode: 'suggest' },
                success: function(res) {
                    if (!res.success || !res.suggestions.length) return;
                    const $list = $('#shopSuggestList').empty();
                    res.suggestions.forEach(function(name) {
                        $('<li>').text(name)
                            .css({'padding':'9px 14px','cursor':'pointer','font-size':'13px','border-bottom':'1px solid #f1f1f1'})
                            .on('mouseenter', function() { $(this).css('background','#f0f4ff'); })
                            .on('mouseleave', function() { $(this).css('background','#fff'); })
                            .on('click', function() {
                                $('#shopSearchInput').val(name);
                                $('#shopSuggestList').hide().empty();
                                doSearch();
                            })
                            .appendTo($list);
                    });
                    $list.show();
                }
            });
        }, 250);
    });

    // ปิด dropdown เมื่อคลิกที่อื่น
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#shopSearchInput, #shopSuggestList').length) {
            $('#shopSuggestList').hide();
        }
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

    // Search shop
    function doSearch() {
        const q = $('#shopSearchInput').val().trim();
        if (!q) return;
        const $btn = $('#btnSearchShop');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        $('#searchError').hide();

        $.ajax({
            url: 'pages/tableRendering/searchInvoiceTH.php',
            type: 'POST',
            dataType: 'json',
            data: { q: q },
            success: function(res) {
                if (res.success) {
                    const r = res.data;
                    currentInvoiceID = r.invoiceID;
                    $('#invoiceIdHidden').val(r.id);
                    $('#labelShopName').text(r.shopName);
                    $('#labelInvoiceID').text(r.invoiceID);
                    $('#labelAmount').text('฿' + parseFloat(r.amount || 0).toLocaleString('th-TH', { minimumFractionDigits: 2 }));
                    const d = r.createdAt ? new Date(r.createdAt).toLocaleDateString('th-TH', { year: 'numeric', month: 'long', day: 'numeric' }) : '-';
                    $('#labelDate').text(d);
                    $('#cardSearch').hide();
                    $('#cardInvoice').show();

                    // Start 10-minute expiry countdown from invoice createdAt
                    const createdAt = r.createdAt ? new Date(r.createdAt).getTime() : Date.now();
                    startCountdown(createdAt + 10 * 60 * 1000);
                } else {
                    $('#searchError').text(res.message || 'ไม่พบข้อมูลร้าน กรุณาตรวจสอบชื่อร้าน').show();
                }
            },
            error: function() {
                $('#searchError').text('เกิดข้อผิดพลาดในการเชื่อมต่อ').show();
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="bi bi-search"></i> ค้นหา');
            }
        });
    }

    $('#btnSearchShop').on('click', doSearch);
    $('#shopSearchInput').on('keydown', function(e) {
        if (e.key === 'Enter') doSearch();
    });

    // Reset to search
    $('#btnResetSearch').on('click', function() {
        stopCountdown();
        $('#countdownBox').hide();
        $('#cardInvoice').hide();
        $('#cardSearch').show();
        $('#slipForm')[0].reset();
        $('#slipPreviewWrap').hide();
        $('#slipFile').next('.custom-file-label').text('เลือกไฟล์ (JPG, PNG, PDF, max 10MB)');
        $('#btnSubmitSlip').prop('disabled', false);
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
                    stopCountdown();
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
        stopCountdown();
        $('#countdownBox').hide();
        $('#cardSuccess').hide();
        $('#cardSearch').show();
        $('#shopSearchInput').val('').trigger('focus');
        $('#slipForm')[0].reset();
        $('#slipPreviewWrap').hide();
        $('#slipFile').next('.custom-file-label').text('เลือกไฟล์ (JPG, PNG, PDF, max 10MB)');
        currentInvoiceID = '';
    });

});
</script>
