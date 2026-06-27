<?php
global $db;
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl  = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
?>
<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <i class="bi bi-clipboard2-check mr-2"></i> Billing TH — ตรวจสลิป
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item active">Billing TH</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label mb-1"><i class="bi bi-search"></i> Search</label>
                                <input type="text" id="billingSearch" class="form-control form-control-sm" placeholder="ชื่อร้าน / Invoice ID">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1"><i class="bi bi-funnel"></i> สถานะสลิป</label>
                                <select id="billingStatusFilter" class="form-control form-control-sm">
                                    <option value="">ทั้งหมด</option>
                                    <option value="pending" selected>รอตรวจ (pending)</option>
                                    <option value="reviewed">ตรวจแล้ว (reviewed)</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">&nbsp;</label>
                                <div class="d-flex" style="gap:6px;">
                                    <button class="btn btn-sm btn-primary w-100" id="btnBillingFilter"><i class="bi bi-funnel"></i> Filter</button>
                                    <button class="btn btn-sm btn-secondary" id="btnBillingReset" title="Reset"><i class="bi bi-arrow-clockwise"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="card">
                            <div class="card-body table-responsive p-3" style="min-height:500px;">
                                <table id="billingThTable" class="table table-borderless table-striped table-hover" style="width:100%">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>วันที่ส่งสลิป</th>
                                            <th>Invoice No.</th>
                                            <th>ชื่อร้าน</th>
                                            <th>ยอด</th>
                                            <th>ส่งโดย</th>
                                            <th>สถานะสลิป</th>
                                            <th>สลิป</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-clipboard2-check mr-2"></i>ตรวจสอบสลิป</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- ซ้าย: ข้อมูล invoice -->
                    <div class="col-md-5">
                        <h6 class="font-weight-bold border-bottom pb-2 mb-3">ข้อมูล Invoice</h6>
                        <table class="table table-sm table-borderless" style="font-size:13px;">
                            <tr><td class="text-muted" style="width:40%">Invoice No.</td><td><strong id="rv_invoiceID"></strong></td></tr>
                            <tr><td class="text-muted">ชื่อร้าน</td><td id="rv_shopName"></td></tr>
                            <tr><td class="text-muted">ยอด</td><td id="rv_amount" class="text-success font-weight-bold"></td></tr>
                            <tr><td class="text-muted">วันที่ออก</td><td id="rv_createdAt"></td></tr>
                            <tr><td class="text-muted">ส่งสลิปโดย</td><td id="rv_submittedBy"></td></tr>
                            <tr><td class="text-muted">หมายเหตุ</td><td id="rv_note" class="text-muted" style="font-size:12px;"></td></tr>
                        </table>

                        <h6 class="font-weight-bold border-bottom pb-2 mb-3 mt-3">ผลการตรวจ</h6>
                        <div class="form-group">
                            <label style="font-size:13px;">Note สำหรับ Billing (ถ้ามี)</label>
                            <textarea class="form-control form-control-sm" id="rv_reviewNote" rows="3" placeholder="เช่น ยอดไม่ตรง, โอนผิดบัญชี..."></textarea>
                        </div>
                    </div>
                    <!-- ขวา: รูปสลิป -->
                    <div class="col-md-7 text-center">
                        <h6 class="font-weight-bold border-bottom pb-2 mb-3">สลิปการโอนเงิน</h6>
                        <div id="rv_slipWrap" style="min-height:300px;display:flex;align-items:center;justify-content:center;">
                            <span class="text-muted">กำลังโหลด...</span>
                        </div>
                        <a id="rv_slipLink" href="#" target="_blank" class="btn btn-sm btn-outline-secondary mt-2" style="display:none;">
                            <i class="bi bi-box-arrow-up-right mr-1"></i>เปิดในแท็บใหม่
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                <div style="display:flex;gap:8px;">
                    <button type="button" class="btn btn-danger" id="btnRejectSlip">
                        <i class="bi bi-x-circle mr-1"></i>Reject
                    </button>
                    <button type="button" class="btn btn-success" id="btnConfirmSlip">
                        <i class="bi bi-check-circle mr-1"></i>Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.addEventListener('load', function() {
    const baseUrl = <?php echo json_encode($baseUrl); ?>;
    let currentSubmissionId = null;
    let currentInvoiceId    = null;

    // --- DataTable ---
    const billingThTable = $('#billingThTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: 'pages/tableRendering/dataBillingTH.php',
            type: 'POST',
            data: function(d) {
                d.search_text  = $('#billingSearch').val();
                d.slip_status  = $('#billingStatusFilter').val();
            }
        },
        columns: [
            { data: 'submittedAt', render: d => d ? new Date(d).toLocaleDateString('th-TH', {year:'numeric',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}) : '-' },
            { data: 'invoiceID' },
            { data: 'shopName' },
            { data: 'amount', render: d => '฿' + parseFloat(d||0).toLocaleString('th-TH',{minimumFractionDigits:2}) },
            { data: 'submittedBy' },
            { data: 'slip_status', render: function(d) {
                const map = { pending: '<span class="badge badge-warning">รอตรวจ</span>', reviewed: '<span class="badge badge-success">ตรวจแล้ว</span>' };
                return map[d] || '<span class="badge badge-secondary">' + d + '</span>';
            }},
            { data: 'slip', render: function(d, t, row) {
                if (!d) return '<span class="text-muted">-</span>';
                const url = baseUrl + '/modules/signup/assets/uploads/' + d;
                return '<a href="' + url + '" target="_blank" class="btn btn-xs btn-outline-info py-0 px-1" style="font-size:11px;"><i class="bi bi-image"></i> ดูสลิป</a>';
            }},
            { data: null, orderable: false, render: function(d, t, row) {
                return '<div class="d-flex" style="gap:4px;">'
                     + '<button class="btn btn-sm btn-primary btnReview py-1 px-2" data-id="' + row.submission_id + '" data-invoice="' + row.invoice_id + '">'
                     + '<i class="bi bi-eye mr-1"></i>ตรวจ</button>'
                     + '<button class="btn btn-sm btn-outline-secondary btnResend py-1 px-2" data-invoice="' + row.invoice_id + '" title="ส่ง Invoice ซ้ำ">'
                     + '<i class="bi bi-send"></i></button>'
                     + '</div>';
            }}
        ],
        order: [[0, 'desc']],
        pageLength: 15,
        language: { processing: 'กำลังโหลด...', emptyTable: 'ไม่พบข้อมูล', zeroRecords: 'ไม่พบรายการที่ค้นหา' }
    });

    // Filter
    $('#btnBillingFilter').on('click', function() { billingThTable.ajax.reload(); });
    $('#btnBillingReset').on('click', function() {
        $('#billingSearch').val('');
        $('#billingStatusFilter').val('pending');
        billingThTable.ajax.reload();
    });
    $('#billingSearch').on('keydown', function(e) { if (e.key === 'Enter') billingThTable.ajax.reload(); });

    // --- Open Review Modal ---
    $('#billingThTable').on('click', '.btnReview', function() {
        const submissionId = $(this).data('id');
        const invoiceId    = $(this).data('invoice');
        currentSubmissionId = submissionId;
        currentInvoiceId    = invoiceId;

        // Reset modal
        $('#rv_invoiceID, #rv_shopName, #rv_amount, #rv_createdAt, #rv_submittedBy, #rv_note').text('');
        $('#rv_reviewNote').val('');
        $('#rv_slipWrap').html('<span class="text-muted">กำลังโหลด...</span>');
        $('#rv_slipLink').hide();
        $('#btnConfirmSlip, #btnRejectSlip').prop('disabled', false);

        $.ajax({
            url: 'pages/tableRendering/dataBillingTH.php',
            type: 'POST',
            dataType: 'json',
            data: { act: 'getOne', submission_id: submissionId },
            success: function(res) {
                if (!res.success) { alert(res.message); return; }
                const r = res.data;
                $('#rv_invoiceID').text(r.invoiceID);
                $('#rv_shopName').text(r.shopName);
                $('#rv_amount').text('฿' + parseFloat(r.amount||0).toLocaleString('th-TH',{minimumFractionDigits:2}));
                $('#rv_createdAt').text(r.createdAt || '-');
                $('#rv_submittedBy').text(r.submittedBy || '-');
                $('#rv_note').text(r.note || '-');

                if (r.slip) {
                    const slipUrl = baseUrl + '/modules/signup/assets/uploads/' + r.slip;
                    const isPdf   = r.slip.toLowerCase().endsWith('.pdf');
                    if (isPdf) {
                        $('#rv_slipWrap').html('<a href="' + slipUrl + '" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-file-pdf mr-1"></i>เปิด PDF</a>');
                    } else {
                        $('#rv_slipWrap').html('<img src="' + slipUrl + '" style="max-width:100%;max-height:380px;border-radius:8px;border:1px solid #dee2e6;">');
                    }
                    $('#rv_slipLink').attr('href', slipUrl).show();
                } else {
                    $('#rv_slipWrap').html('<span class="text-muted">ไม่มีสลิป</span>');
                }
            }
        });

        $('#reviewModal').modal('show');
    });

    // --- Confirm ---
    $('#btnConfirmSlip').on('click', function() {
        if (!currentSubmissionId) return;
        if (!confirm('ยืนยันการ Confirm สลิปนี้?')) return;
        doReview('confirm');
    });

    // --- Reject ---
    $('#btnRejectSlip').on('click', function() {
        if (!currentSubmissionId) return;
        if (!confirm('ต้องการ Reject สลิปนี้?')) return;
        doReview('reject');
    });

    // --- Re-send Invoice ---
    $('#billingThTable').on('click', '.btnResend', function() {
        const invoiceId = $(this).data('invoice');
        if (!confirm('ส่ง Invoice ซ้ำไปลูกค้าอีกครั้ง?')) return;
        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        $.ajax({
            url: 'pages/tableRendering/sendInvoiceTH.php',
            type: 'POST',
            dataType: 'json',
            data: { id: invoiceId, action: 'confirmed' },
            success: function(res) {
                if (res.success) {
                    billingThTable.ajax.reload(null, false);
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (res.message || ''));
                }
            },
            error: function() { alert('เกิดข้อผิดพลาดในการเชื่อมต่อ'); },
            complete: function() { $btn.prop('disabled', false).html('<i class="bi bi-send"></i>'); }
        });
    });

    function doReview(action) {
        const $confirm = $('#btnConfirmSlip');
        const $reject  = $('#btnRejectSlip');
        $confirm.prop('disabled', true);
        $reject.prop('disabled', true);

        $.ajax({
            url: 'pages/tableRendering/reviewSlipTH.php',
            type: 'POST',
            dataType: 'json',
            data: {
                submission_id: currentSubmissionId,
                invoice_id:    currentInvoiceId,
                action:        action,
                note:          $('#rv_reviewNote').val()
            },
            success: function(res) {
                $('#reviewModal').modal('hide');
                if (res.success) {
                    billingThTable.ajax.reload(null, false);
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (res.message || ''));
                }
            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            },
            complete: function() {
                $confirm.prop('disabled', false);
                $reject.prop('disabled', false);
            }
        });
    }
});
</script>
