<?php global $db; ?>
<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <i class="bi bi-receipt mr-2"></i>
                    Receipt Thailand
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item active">Receipt Thailand</li>
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
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label mb-1"><i class="bi bi-calendar-range"></i> Date Range</label>
                                <div class="d-flex align-items-center" style="gap:6px;">
                                    <input type="date" id="filterDateStart" class="form-control form-control-sm">
                                    <span style="font-size:12px;">to</span>
                                    <input type="date" id="filterDateEnd" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1"><i class="bi bi-search"></i> Search</label>
                                <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Name / Email / Invoice ID">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">&nbsp;</label>
                                <div class="d-flex" style="gap:6px;">
                                    <button class="btn btn-sm btn-primary w-100" id="btnApplyFilter"><i class="bi bi-funnel"></i> Filter</button>
                                    <button class="btn btn-sm btn-secondary" id="btnResetFilter" title="Reset"><i class="bi bi-arrow-clockwise"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="card">
                            <div class="card-body table-responsive p-4" style="height: 630px;">
                                <table id="invoiceThTable" class="table table-borderless table-striped table-hover" style="width:100%">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th>Receipt ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Client</th>
                                            <th>Amount</th>
                                            <th>Status</th>
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

<!-- Slip Viewer Modal -->
<div class="modal fade" id="slipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-image"></i> สลิปโอนเงิน</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center p-2">
                <img id="slipImg" src="" alt="slip" style="max-width:100%; border-radius:8px;">
            </div>
            <div class="modal-footer">
                <a id="slipDownloadBtn" href="" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> เปิดในแท็บใหม่</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Preview Modal -->
<div class="modal fade" id="receiptPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt mr-2"></i>ตัวอย่าง Receipt</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="receiptPreviewBody">
                <div class="text-center py-4"><span class="spinner-border text-info"></span></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-outline-primary" onclick="printReceipt()"><i class="bi bi-printer"></i> Print</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Status Modal -->
<div class="modal fade" id="editStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square mr-2"></i>แก้ไขสถานะ</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="mb-1 text-muted" style="font-size:13px;">Invoice ID: <strong id="editStatusInvoiceLabel"></strong></p>
                <div class="form-group mt-3 mb-0">
                    <label class="font-weight-bold">สถานะ</label>
                    <select id="editStatusSelect" class="form-control">
                        <option value="pending">⏳ รอยืนยันหลักฐาน</option>
                        <option value="confirmed">✅ ยืนยันเรียบร้อย</option>
                        <option value="rejected">❌ แก้ไขข้อมูล</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btnSaveStatus"><i class="bi bi-check2"></i> บันทึก</button>
            </div>
        </div>
    </div>
</div>

<!-- Send Preview Modal -->
<div class="modal fade" id="sendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-earmark-text mr-2"></i>ตรวจสอบข้อมูลก่อนส่ง</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="sendModalBody">
                <div class="text-center py-4"><span class="spinner-border text-primary"></span></div>
            </div>
            <div class="modal-footer flex-column align-items-stretch" style="gap:8px;">
                <div class="d-flex align-items-center" style="gap:8px;">
                    <label class="mb-0 font-weight-bold text-nowrap" style="font-size:13px;">สถานะ:</label>
                    <select id="sendModalStatusSelect" class="form-control form-control-sm" style="min-width:200px;">
                        <option value="pending">⏳ รอยืนยันหลักฐาน</option>
                        <option value="confirmed">✅ ยืนยันเรียบร้อย</option>
                        <option value="rejected">❌ แก้ไขข้อมูล</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-success" id="btnSaveStatusOnly">
                        <i class="bi bi-check2"></i> บันทึกสถานะ
                    </button>
                </div>
                <div id="needfixRow" style="display:none;">
                    <div class="d-flex align-items-start" style="gap:6px;">
                        <label class="mb-0 font-weight-bold text-nowrap text-danger" style="font-size:13px;margin-top:6px;"><i class="bi bi-exclamation-circle-fill"></i> สาเหตุ:</label>
                        <textarea id="needfixText" class="form-control form-control-sm" rows="2" placeholder="ระบุสาเหตุที่ต้องแก้ไข..." style="flex:1;resize:vertical;"></textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-end" style="gap:8px;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-outline-info" id="btnPreviewReceipt">
                        <i class="bi bi-receipt"></i> ดูตัวอย่าง Receipt
                    </button>
                    <button type="button" class="btn btn-primary" id="btnSendReceipt" style="display:none;">
                        <i class="bi bi-send"></i> ส่ง Receipt
                    </button>
                    <button type="button" class="btn btn-danger" id="btnSendReceiptFix" style="display:none;">
                        <i class="bi bi-send"></i> ส่ง Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.addEventListener('load', function() {
let invoiceThTable = $('#invoiceThTable').DataTable({
    pagingType: 'full_numbers',
    ajax: {
        url: 'pages/tableRendering/dataInvoiceThailand.php',
        type: 'POST',
        data: function(d) {
            d.dateStart  = $('#filterDateStart').val();
            d.dateEnd    = $('#filterDateEnd').val();
            d.search_val = $('#filterSearch').val();
        },
        dataSrc: 'data'
    },
    pageLength: 25,
    order: [[0, 'desc']],
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
    columnDefs: [
        { targets: [0,1,2,3], className: 'dt-left' },
        { targets: [4,5,6,7], className: 'dt-center', orderable: false }
    ]
});

$('#btnApplyFilter').on('click', function() { invoiceThTable.ajax.reload(); });
$('#btnResetFilter').on('click', function() {
    $('#filterDateStart, #filterDateEnd, #filterSearch').val('');
    invoiceThTable.ajax.reload();
});

let pendingInvoiceId = null;

window.viewSlip = function(url) {
    $('#slipImg').attr('src', url);
    $('#slipDownloadBtn').attr('href', url);
    $('#slipModal').modal('show');
    return false;
};

window.openSendModal = function(invoiceId) {
    pendingInvoiceId = invoiceId;
    $('#sendModalBody').html('<div class="text-center py-4"><span class="spinner-border text-primary"></span></div>');
    $('#sendModal').modal('show');

    $.ajax({
        url: 'pages/tableRendering/getInvoiceTH.php',
        type: 'POST',
        dataType: 'json',
        data: { id: invoiceId },
        success: function(d) {
            if (!d.success) { $('#sendModalBody').html('<p class="text-danger">โหลดข้อมูลไม่สำเร็จ</p>'); return; }
            currentInvoiceData = d.data;
            var r = d.data;
            var currentStatus = r.receiptStatus || 'pending';
            var isRejected = currentStatus === 'rejected';
            $('#sendModalStatusSelect').val(currentStatus);
            $('#needfixText').val(r.needfix || '');
            if (isRejected) { $('#needfixRow').show(); } else { $('#needfixRow').hide(); }
            updateSendReceiptBtn();
            var p = r.product || {};
            var summary = p.summary || {};
            var items = p.table || [];
            var detail = (p.quotation && p.quotation[0] && p.quotation[0].detail) ? p.quotation[0].detail[0] : {};

            var itemsHtml = '';
            items.forEach(function(item, idx) {
                itemsHtml += buildItemRow(item);
            });
            if (!itemsHtml) { itemsHtml = buildItemRow({}); }

            var slipViewBtn = r.slip
                ? '<a href="modules/signup/assets/uploads/' + r.slip + '" target="_blank" class="btn btn-sm btn-outline-success mr-1"><i class="bi bi-image"></i> ดูสลิป</a>'
                : '<span class="badge badge-secondary mr-1">ไม่มีสลิป</span>';
            var slipUploadBtn = isRejected
                ? '<label class="btn btn-sm btn-outline-warning mb-0" style="cursor:pointer;"><i class="bi bi-upload"></i> แก้ไขสลิป<input type="file" id="slipReplaceInput" data-invoice="'+r.id+'" data-shop="'+r.name+'" accept="image/*,application/pdf" style="display:none;"></label>'
                : '';
            var slipHtml = slipViewBtn + slipUploadBtn;

            var whtRow = r.type === 'นิติบุคคล'
                ? '<tr><td class="text-muted">หัก ณ ที่จ่าย 3%</td><td class="text-right text-danger">- ฿' + parseFloat(summary.withholdingTax||0).toLocaleString('th-TH',{minimumFractionDigits:2}) + '</td></tr>'
                : '';

            var dateStr = r.createdAt ? new Date(r.createdAt).toLocaleDateString('th-TH',{year:'numeric',month:'long',day:'numeric'}) : '-';

            function inlineField(invoiceId, field, value, opts) {
                opts = opts || {};
                var tag = opts.textarea ? 'textarea' : 'input';
                var inputAttr = opts.textarea ? '' : ' type="text"';
                var uid = 'ief_' + field;
                var html = '<span class="ief-wrap" data-invoice="'+invoiceId+'" data-field="'+field+'" style="display:inline-flex;align-items:center;gap:4px;width:100%;">'
                    + '<span class="ief-display" style="flex:1;">' + (value||'-') + '</span>'
                    + (opts.textarea
                        ? '<textarea class="ief-input form-control form-control-sm" style="display:none;flex:1;font-size:12px;" rows="2">'+($('<div>').text(value).html())+'</textarea>'
                        : '<input class="ief-input form-control form-control-sm" style="display:none;flex:1;font-size:12px;" type="text" value="'+($('<div>').text(value).html())+'">'
                    )
                    + (opts.editable ? '<button class="ief-btn-edit btn btn-xs" style="background:none;border:none;color:'+(opts.dark?'rgba(255,255,255,0.6)':'#94a3b8')+';padding:0 2px;line-height:1;" title="แก้ไข"><i class="bi bi-pencil" style="font-size:11px;"></i></button>' : '')
                    + (opts.editable ? '<button class="ief-btn-ok btn btn-xs" style="display:none;background:none;border:none;color:'+(opts.dark?'#86efac':'#16a34a')+';padding:0 2px;line-height:1;" title="บันทึก"><i class="bi bi-check-lg" style="font-size:13px;"></i></button>' : '')
                    + (opts.editable ? '<button class="ief-btn-cancel btn btn-xs" style="display:none;background:none;border:none;color:'+(opts.dark?'#fca5a5':'#dc2626')+';padding:0 2px;line-height:1;" title="ยกเลิก"><i class="bi bi-x-lg" style="font-size:13px;"></i></button>' : '')
                    + '</span>';
                return html;
            }

            $('#sendModalBody').html(
                // Header strip
                '<div style="background:linear-gradient(135deg,#1e3a8a,#2563eb);border-radius:8px;padding:16px 20px;margin-bottom:16px;color:#fff;">'
                +'<div class="d-flex justify-content-between align-items-center">'
                +'<div>'
                +'<div style="font-size:11px;opacity:.75;letter-spacing:1px;text-transform:uppercase;">Receipt</div>'
                +'<div style="font-size:18px;font-weight:700;">' + inlineField(r.id,'invoiceID',r.invoiceID,{editable:isRejected,dark:true}) + '</div>'
                // +'<div style="font-size:12px;opacity:.8;margin-top:2px;">' + dateStr + '</div>'
                +'</div>'
                +'<div class="text-right">'
                +'<div style="font-size:11px;opacity:.75;">ยอดสุทธิ</div>'
                +'<div style="font-size:22px;font-weight:800;">฿' + parseFloat(summary.net_payment||0).toLocaleString('th-TH',{minimumFractionDigits:2}) + '</div>'
                +'<div style="margin-top:4px;">' + slipHtml + '</div>'
                +'</div>'
                +'</div>'
                +'</div>'

                // Customer + Bank 2 col
                +'<div class="row" style="font-size:13px;margin-bottom:12px;">'
                +'<div class="col-md-6">'
                +'<div style="background:#f8fafc;border-radius:8px;padding:12px 14px;height:100%;">'
                +'<div style="font-size:11px;font-weight:700;color:#64748b;letter-spacing:.5px;margin-bottom:8px;text-transform:uppercase;">ข้อมูลลูกค้า</div>'
                +'<div style="font-weight:700;font-size:14px;margin-bottom:4px;">' + inlineField(r.id,'name',r.name,{editable:isRejected}) + '</div>'
                +'<div style="color:#555;margin-bottom:2px;">' + inlineField(r.id,'address',r.address,{textarea:true,editable:isRejected}) + '</div>'
                +'<div style="color:#555;">เลขภาษี: ' + inlineField(r.id,'taxNumber',r.taxNumber,{editable:isRejected}) + '</div>'
                +'<div style="margin-top:6px;color:#555;"><i class="bi bi-envelope" style="color:#2563eb;"></i> ' + inlineField(r.id,'email',r.customerEmail,{editable:isRejected}) + '</div>'
                +'<div style="color:#555;"><i class="bi bi-telephone" style="color:#2563eb;"></i> ' + inlineField(r.id,'phone',r.customerPhone,{editable:isRejected}) + '</div>'
                +'</div>'
                +'</div>'
                +'<div class="col-md-6">'
                +'<div style="background:#f8fafc;border-radius:8px;padding:12px 14px;height:100%;">'
                +'<div style="font-size:11px;font-weight:700;color:#64748b;letter-spacing:.5px;margin-bottom:8px;text-transform:uppercase;">ชำระเงินผ่าน</div>'
                +'<div style="font-weight:700;font-size:14px;margin-bottom:4px;"><i class="bi bi-bank" style="color:#2563eb;"></i> ' + inlineField(r.id,'bankName',r.bankName,{editable:isRejected}) + '</div>'
                +'<div style="color:#555;">เลขบัญชี: ' + inlineField(r.id,'bankNumber',r.bankThaiNumber,{editable:isRejected}) + '</div>'
                +'<div style="color:#555;">ชื่อบัญชี: ' + inlineField(r.id,'bankAccName',r.bankThaiName,{editable:isRejected}) + '</div>'
                +'</div>'
                +'</div>'
                +'</div>'

                // Items table (แก้ไขได้ในตัว)
                +'<div class="d-flex justify-content-between align-items-center mb-1 flex-wrap" style="gap:8px;">'
                +'<div style="font-size:11px;font-weight:700;color:#64748b;letter-spacing:.5px;text-transform:uppercase;">รายการสินค้า</div>'
                +'<div class="d-flex align-items-center" style="gap:6px;">'
                +'<label class="mb-0" style="font-size:11.5px;color:#64748b;">วันที่บนใบ</label>'
                +'<input type="date" id="invDate" class="form-control form-control-sm" style="width:150px;font-size:12px;" value="' + itemEsc(thaiDateToISO((p.quotation && p.quotation[0] && p.quotation[0].date) || '')) + '">'
                +'<button type="button" class="btn btn-xs btn-outline-primary" id="btnAddItem" style="padding:2px 8px;font-size:11px;"><i class="bi bi-plus-lg"></i> เพิ่มรายการ</button>'
                +'</div>'
                +'</div>'
                +'<table class="table table-sm" style="font-size:13px;border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;">'
                +'<thead style="background:#1e3a8a;color:#fff;"><tr><th style="width:110px;">ชนิด</th><th>รายการ</th><th class="text-center" style="width:70px;">จำนวน</th><th class="text-right" style="width:110px;">ราคา (ก่อน VAT)</th><th class="text-center" style="width:44px;"></th></tr></thead>'
                +'<tbody id="itemsTbody">' + itemsHtml + '</tbody>'
                +'</table>'

                // Summary (คำนวณสดตามรายการด้านบน)
                +'<div style="background:#f8fafc;border-radius:8px;padding:12px 16px;font-size:13px;">'
                +'<table style="width:100%;">'
                +'<tr><td style="color:#64748b;">ราคาก่อน VAT</td><td class="text-right" id="sumSubtotal">฿0.00</td></tr>'
                +'<tr><td style="color:#64748b;">VAT 7%</td><td class="text-right" id="sumVat">฿0.00</td></tr>'
                +'<tr><td style="color:#64748b;">รวม (inc. VAT)</td><td class="text-right" id="sumGross">฿0.00</td></tr>'
                +'<tr id="sumWhtRow"><td style="color:#64748b;">หัก ณ ที่จ่าย 3%</td><td class="text-right text-danger" id="sumWht">- ฿0.00</td></tr>'
                +'<tr><td colspan="2"><hr style="margin:6px 0;"></td></tr>'
                +'<tr><td style="font-weight:700;font-size:14px;">ยอดสุทธิที่ต้องชำระ</td><td class="text-right" style="font-weight:800;font-size:16px;color:#2563eb;" id="sumNet">฿0.00</td></tr>'
                +'</table>'
                +'<div class="d-flex justify-content-between align-items-center mt-2 pt-2" style="border-top:1px dashed #cbd5e1;">'
                +'<label class="mb-0" style="font-size:11.5px;color:#64748b;cursor:pointer;">'
                +'<input type="checkbox" id="chkSyncAmount" checked> อัปเดตยอดเงินในระบบตามรายการนี้'
                +'</label>'
                +'<button type="button" class="btn btn-sm btn-success" id="btnSaveItems"><i class="bi bi-save mr-1"></i>บันทึกรายการ</button>'
                +'</div>'
                +'</div>'
            );

            // เก็บประเภทลูกค้าไว้ให้ตัวคำนวณใช้ แล้วคำนวณยอดครั้งแรก
            $('#itemsTbody').data('custType', r.type || '');
            recalcItems();
        },
        error: function() {
            $('#sendModalBody').html('<p class="text-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>');
        }
    });
};

    function updateSendReceiptBtn() {
        const status = $('#sendModalStatusSelect').val();
        const $btnConfirmed = $('#btnSendReceipt');
        const $btnFix = $('#btnSendReceiptFix');
        
        if (status === 'confirmed') {
            $btnConfirmed.show();
            $btnFix.hide();
        } else if (status === 'rejected') {
            $btnConfirmed.hide();
            $btnFix.show();
        } else {
            $btnConfirmed.hide();
            $btnFix.hide();
        }
    }

    function showNotify(type, msg) {
        if (typeof toastr !== 'undefined') {
            type === 'success' ? toastr.success(msg) : toastr.error(msg);
        } else {
            alert(msg);
        }
    }

// Slip replace upload
$(document).on('change', '#slipReplaceInput', function() {
    var file = this.files[0];
    if (!file) return;
    var invoiceId = $(this).data('invoice');
    var shopName  = $(this).data('shop');
    var $label    = $(this).closest('label');
    var origHtml  = $label.html();
    $label.html('<span class="spinner-border spinner-border-sm" style="width:12px;height:12px;"></span> กำลังอัปโหลด...');

    var fd = new FormData();
    fd.append('slip', file);
    fd.append('shopName', shopName);
    fd.append('country', 'TH');
    fd.append('quotationID', invoiceId);

    $.ajax({
        url: 'modules/signup/assets/API/upload_slip.php',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.success) {
                showNotify('success', 'อัปโหลดสลิปเรียบร้อย');
                var newUrl = 'modules/signup/assets/uploads/' + res.slipPath;
                var $viewBtn = $label.closest('div').find('a.btn-outline-success');
                if ($viewBtn.length) {
                    $viewBtn.attr('href', newUrl);
                } else {
                    $label.before('<a href="'+newUrl+'" target="_blank" class="btn btn-sm btn-outline-success mr-1"><i class="bi bi-image"></i> ดูสลิป</a>');
                    $label.closest('div').find('.badge-secondary').remove();
                }
                $label.html('<i class="bi bi-upload"></i> แก้ไขสลิป<input type="file" id="slipReplaceInput" data-invoice="'+invoiceId+'" data-shop="'+shopName+'" accept="image/*,application/pdf" style="display:none;">');
            } else {
                showNotify('error', res.message || 'อัปโหลดไม่สำเร็จ');
                $label.html(origHtml);
            }
        },
        error: function() {
            showNotify('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ');
            $label.html(origHtml);
        }
    });
});

// Inline edit: pencil click → show input + ok/cancel
$(document).on('click', '.ief-btn-edit', function() {
    var $wrap = $(this).closest('.ief-wrap');
    var origVal = $wrap.find('.ief-display').text();
    $wrap.find('.ief-input').val(origVal).show().trigger('focus');
    $wrap.find('.ief-display').hide();
    $(this).hide();
    $wrap.find('.ief-btn-ok, .ief-btn-cancel').show();
});

// Inline edit: cancel → restore
$(document).on('click', '.ief-btn-cancel', function() {
    var $wrap = $(this).closest('.ief-wrap');
    $wrap.find('.ief-input').hide();
    $wrap.find('.ief-display').show();
    $wrap.find('.ief-btn-edit').show();
    $(this).hide();
    $wrap.find('.ief-btn-ok').hide();
});

// Inline edit: ok → AJAX save
$(document).on('click', '.ief-btn-ok', function() {
    var $wrap    = $(this).closest('.ief-wrap');
    var $btn     = $(this);
    var invoiceId = $wrap.data('invoice');
    var field    = $wrap.data('field');
    var newVal   = $wrap.find('.ief-input').val().trim();

    $btn.html('<span class="spinner-border spinner-border-sm" style="width:10px;height:10px;"></span>').prop('disabled', true);

    $.ajax({
        url: 'pages/tableRendering/updateCustomerTH.php',
        type: 'POST',
        dataType: 'json',
        data: { invoice_id: invoiceId, field: field, value: newVal },
        success: function(res) {
            if (res.success) {
                $wrap.find('.ief-display').text(newVal).show();
                $wrap.find('.ief-input').hide();
                $wrap.find('.ief-btn-edit').show();
                $btn.html('<i class="bi bi-check-lg" style="font-size:13px;"></i>').prop('disabled', false).hide();
                $wrap.find('.ief-btn-cancel').hide();
                showNotify('success', 'อัปเดตเรียบร้อย');
            } else {
                showNotify('error', res.message || 'เกิดข้อผิดพลาด');
                $btn.html('<i class="bi bi-check-lg" style="font-size:13px;"></i>').prop('disabled', false);
            }
        },
        error: function() {
            showNotify('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ');
            $btn.html('<i class="bi bi-check-lg" style="font-size:13px;"></i>').prop('disabled', false);
        }
    });
});

    $(document).on('click', '#btnSendReceipt', function() {
        if (!pendingInvoiceId) return;
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังส่ง...');

        $.ajax({
            url: 'pages/tableRendering/sendReceiptTH.php',
            type: 'POST',
            dataType: 'json',
            data: { id: pendingInvoiceId },
            success: function(res) {
                $('#sendModal').modal('hide');
                if (res.success) {
                    showNotify('success', 'ส่ง Receipt สำเร็จแล้ว');
                    invoiceThTable.ajax.reload(null, false);
                } else {
                    showNotify('error', 'เกิดข้อผิดพลาด: ' + res.message);
                }
            },
            error: function() {
                showNotify('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="bi bi-send"></i> ส่ง Receipt');
            }
        });
    });

    $(document).on('click', '#btnSendReceiptFix', function() {
        if (!pendingInvoiceId) return;
        const btn = $(this);
        const needfix = $('#needfixText').val().trim();
        if (!needfix) {
            showNotify('error', 'กรุณาระบุสาเหตุที่ต้องแก้ไข');
            return;
        }
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังส่ง...');

        $.ajax({
            url: 'pages/tableRendering/sendReceiptTH.php',
            type: 'POST',
            dataType: 'json',
            data: { id: pendingInvoiceId },
            success: function(res) {
                $('#sendModal').modal('hide');
                if (res.success) {
                    showNotify('success', 'ส่ง Receipt (แก้ไข) สำเร็จแล้ว');
                    invoiceThTable.ajax.reload(null, false);
                } else {
                    showNotify('error', 'เกิดข้อผิดพลาด: ' + res.message);
                }
            },
            error: function() {
                showNotify('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="bi bi-send"></i> ส่ง Receipt (แก้ไข)');
            }
        });
    });

    let currentInvoiceData = null;

    $(document).on('click', '#btnPreviewReceipt', function() {
        if (!pendingInvoiceId) return;
        window.open('pages/receiptTH.php?invoice_id=' + pendingInvoiceId, '_blank');
        return;
        if (!currentInvoiceData) return;
        var r = currentInvoiceData;
        var p = r.product || {};
        var summary = p.summary || {};
        var items = p.table || [];
        var quotation = (p.quotation && p.quotation[0]) ? p.quotation[0] : {};
        var invDate = quotation.date || '-';

        var itemsHtml = '';
        items.forEach(function(item) {
            var label = item.product || item.setupfee || item.addon || '-';
            var full = item.fullamount ? parseFloat(item.fullamount).toLocaleString('th-TH',{minimumFractionDigits:2}) : '-';
            itemsHtml += '<tr>'
                + '<td>' + label.trim() + '</td>'
                + '<td class="text-center">' + (item.qyt||1) + '</td>'
                + '<td class="text-right">' + parseFloat(item.amount||0).toLocaleString('th-TH',{minimumFractionDigits:2}) + '</td>'
                + '<td class="text-right">' + full + '</td>'
                + '</tr>';
        });

        var whtRow = parseFloat(summary.withholdingTax||0) > 0
            ? '<tr><td style="color:#555;">หัก ณ ที่จ่าย 3%</td><td class="text-right">- ' + parseFloat(summary.withholdingTax).toLocaleString('th-TH',{minimumFractionDigits:2}) + ' ฿</td></tr>'
            : '';

        var bankHtml = r.bankName
            ? '<div style="background:#f8fafc;border-radius:8px;padding:12px 14px;font-size:13px;">'
              + '<p class="mb-1" style="color:#888;">ชำระเงินผ่าน</p>'
              + '<p class="mb-1"><strong>' + (r.bankName||'-') + '</strong></p>'
              + (r.bankThaiNumber ? '<p class="mb-0">เลขบัญชี: ' + r.bankThaiNumber + '</p>' : '')
              + (r.bankThaiName   ? '<p class="mb-0">ชื่อบัญชี: ' + r.bankThaiName + '</p>' : '')
              + '</div>'
            : '';

        $('#receiptPreviewBody').html(
            '<div style="font-family:sans-serif; font-size:13px;" id="receiptPrintArea">'
            + '<div class="d-flex justify-content-between align-items-start mb-3">'
            + '<div><h4 style="font-weight:700;color:#1a1a2e;margin:0;">LOCAL FOR YOU</h4>'
            + '<p class="text-muted mb-0">บริษัท โลคอล อีทส์ จำกัด</p></div>'
            + '<div class="text-right"><h4 style="font-weight:700;color:#2563eb;margin:0;">ใบเสร็จรับเงิน</h4>'
            + '<p class="mb-0 text-muted">RECEIPT (ตัวอย่าง)</p></div>'
            + '</div><hr>'
            + '<div class="row mb-3">'
            + '<div class="col-md-6">'
            + '<p class="mb-1"><strong>' + (r.name||'-') + '</strong></p>'
            + '<p class="mb-1">' + (r.address||'') + '</p>'
            + (r.taxNumber ? '<p class="mb-1">เลขภาษี: ' + r.taxNumber + '</p>' : '')
            + '<p class="mb-1">' + (r.customerEmail||'') + '</p>'
            + '<p class="mb-0">' + (r.customerPhone||'') + '</p>'
            + '</div>'
            + '<div class="col-md-6 text-right">'
            + '<p class="mb-1"><span style="color:#888;">Invoice ID:</span> <strong>' + (r.invoiceID||'-') + '</strong></p>'
            + '<p class="mb-1"><span style="color:#888;">Receipt ID:</span> <strong style="color:#2563eb;">(จะสร้างหลังกดส่ง)</strong></p>'
            + '<p class="mb-1"><span style="color:#888;">วันที่:</span> ' + invDate + '</p>'
            + (r.sale ? '<p class="mb-0"><span style="color:#888;">Sales:</span> ' + r.sale + '</p>' : '')
            + '</div>'
            + '</div>'
            + '<table class="table table-bordered table-sm">'
            + '<thead style="background:#f1f5f9;"><tr><th>รายการ</th><th class="text-center">จำนวน</th><th class="text-right">ก่อน VAT</th><th class="text-right">รวม VAT</th></tr></thead>'
            + '<tbody>' + itemsHtml + '</tbody>'
            + '</table>'
            + '<div class="row">'
            + '<div class="col-md-6">' + bankHtml + '</div>'
            + '<div class="col-md-6">'
            + '<table class="w-100">'
            + '<tr><td style="color:#555;">Subtotal</td><td class="text-right">' + parseFloat(summary.subtotal||0).toLocaleString('th-TH',{minimumFractionDigits:2}) + ' ฿</td></tr>'
            + '<tr><td style="color:#555;">VAT 7%</td><td class="text-right">' + parseFloat(summary.vat||0).toLocaleString('th-TH',{minimumFractionDigits:2}) + ' ฿</td></tr>'
            + '<tr><td style="color:#555;">Grand Total</td><td class="text-right">' + parseFloat(summary.grandtotal_inc_vat||0).toLocaleString('th-TH',{minimumFractionDigits:2}) + ' ฿</td></tr>'
            + whtRow
            + '<tr><td colspan="2"><hr style="margin:4px 0;"></td></tr>'
            + '<tr><td style="font-weight:700;font-size:15px;">ยอดสุทธิ</td><td class="text-right" style="font-weight:700;font-size:16px;color:#2563eb;">' + parseFloat(summary.net_payment||0).toLocaleString('th-TH',{minimumFractionDigits:2}) + ' ฿</td></tr>'
            + '</table>'
            + '</div></div>'
            + '<hr><p class="text-center text-muted mb-0" style="font-size:11px;">ขอบคุณที่ไว้วางใจ Local For You</p>'
            + '</div>'
        );

        $('#sendModal').modal('hide');
        $('#receiptPreviewModal').modal('show');
    });

    window.printReceipt = function() {
        var content = document.getElementById('receiptPrintArea');
        if (!content) return;
        var win = window.open('', '_blank', 'width=800,height=900');
        win.document.write('<html><head><title>Receipt</title>'
            + '<link rel="stylesheet" href="plugins/bootstrap/css/bootstrap.min.css">'
            + '<style>body{padding:30px;font-family:sans-serif;font-size:13px;} @media print{body{padding:0;}}</style>'
            + '</head><body>' + content.innerHTML + '<script>window.onload=function(){window.print();window.close();}<\/script></body></html>');
        win.document.close();
    };

    $(document).on('click', '#btnPendingSlip', function() {
        if (!pendingInvoiceId) return;
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังบันทึก...');

        $.ajax({
            url: 'pages/tableRendering/sendInvoiceTH.php',
            type: 'POST',
            dataType: 'json',
            data: { id: pendingInvoiceId, action: 'pending' },
            success: function(res) {
                $('#sendModal').modal('hide');
                if (res.success) {
                    showNotify('success', res.message + ' (' + (res.receiptID||'') + ')');
                    invoiceThTable.ajax.reload(null, false);
                } else {
                    showNotify('error', 'เกิดข้อผิดพลาด: ' + res.message);
                }
            },
            error: function() {
                showNotify('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="bi bi-clock-history"></i> รอยืนยันหลักฐาน');
            }
        });
    });

// Toggle needfix row + receipt button when status changes
$(document).on('change', '#sendModalStatusSelect', function() {
    if ($(this).val() === 'rejected') {
        $('#needfixRow').show();
    } else {
        $('#needfixRow').hide();
    }
    updateSendReceiptBtn();
});

// Save status only (from sendModal selectbox)
$(document).on('click', '#btnSaveStatusOnly', function() {
    if (!pendingInvoiceId) return;
    const btn = $(this);
    const newStatus = $('#sendModalStatusSelect').val();
    const needfix = newStatus === 'rejected' ? $('#needfixText').val().trim() : '';
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

    $.ajax({
        url: 'pages/tableRendering/updateStatusInvoiceTH.php',
        type: 'POST',
        dataType: 'json',
        data: { invoice_id: pendingInvoiceId, status: newStatus, needfix: needfix },
        success: function(res) {
            $('#sendModal').modal('hide');
            if (res.success) {
                showNotify('success', 'อัปเดทสถานะเรียบร้อย');
                invoiceThTable.ajax.reload(null, false);
            } else {
                showNotify('error', 'เกิดข้อผิดพลาด: ' + res.message);
            }
        },
        error: function() { showNotify('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ'); },
        complete: function() { btn.prop('disabled', false).html('<i class="bi bi-check2"></i> บันทึกสถานะ'); }
    });
});

// Edit Status
let editStatusInvoiceId = null;

window.openEditStatus = function(invoiceId, invoiceLabel, currentStatus) {
    editStatusInvoiceId = invoiceId;
    $('#editStatusInvoiceLabel').text(invoiceLabel);
    $('#editStatusSelect').val(currentStatus || 'pending');
    $('#editStatusModal').modal('show');
};

$(document).on('click', '#btnSaveStatus', function() {
    if (!editStatusInvoiceId) return;
    const btn = $(this);
    const newStatus = $('#editStatusSelect').val();
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

    $.ajax({
        url: 'pages/tableRendering/updateStatusInvoiceTH.php',
        type: 'POST',
        dataType: 'json',
        data: { invoice_id: editStatusInvoiceId, status: newStatus },
        success: function(res) {
            $('#editStatusModal').modal('hide');
            if (res.success) {
                showNotify('success', 'อัปเดทสถานะเป็น ' + newStatus + ' เรียบร้อย');
                invoiceThTable.ajax.reload(null, false);
            } else {
                showNotify('error', 'เกิดข้อผิดพลาด: ' + res.message);
            }
        },
        error: function() { showNotify('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ'); },
        complete: function() { btn.prop('disabled', false).html('<i class="bi bi-check2"></i> บันทึก'); }
    });
});

// ===== Items Editor =====
// แก้ไขรายการสินค้าได้ในตัว modal แล้วบันทึกผ่าน api/invoice/updateProductJson.php
// ซึ่งจะรักษา quotation (ข้อมูลลูกค้า + วันที่บนใบ) ไว้ ไม่สร้างทับ

function itemNum(v) {
    const n = parseFloat(String(v == null ? '' : v).replace(/[^0-9.\-]/g, ''));
    return isNaN(n) ? 0 : n;
}
function itemEsc(s) { return $('<div>').text(s == null ? '' : s).html(); }

// ฐานข้อมูลเก็บวันที่เป็น dd/mm/yyyy แต่ <input type="date"> ใช้ yyyy-mm-dd
// จึงต้องแปลงกลับไปมาตอนแสดงและตอนบันทึก
function thaiDateToISO(s) {
    const m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(String(s || '').trim());
    return m ? (m[3] + '-' + m[2] + '-' + m[1]) : '';
}
function isoToThaiDate(s) {
    const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(s || '').trim());
    return m ? (m[3] + '/' + m[2] + '/' + m[1]) : '';
}

// อ่านชนิด/ชื่อออกจาก item หนึ่งรายการ (product / setupfee / addon)
function readItemKind(item) {
    const kinds = ['product', 'setupfee', 'addon'];
    for (const k of kinds) {
        if (item[k] !== undefined && item[k] !== null && String(item[k]).trim() !== '') {
            return { kind: k, name: String(item[k]).trim() };
        }
    }
    return { kind: 'product', name: '' };
}

function buildItemRow(item) {
    item = item || {};
    const r = readItemKind(item);
    const qyt = item.qyt || '1';
    const amount = itemNum(item.amount);
    const full = (item.fullamount === undefined || item.fullamount === null) ? '' : item.fullamount;
    const opt = function (k, label) {
        return '<option value="' + k + '"' + (k === r.kind ? ' selected' : '') + '>' + label + '</option>';
    };
    return '<tr class="item-row" data-fullamount="' + itemEsc(full) + '">'
        + '<td><select class="form-control form-control-sm it-kind" style="font-size:12px;padding:2px 4px;height:auto;">'
        +   opt('product', 'Product') + opt('setupfee', 'Setup Fee') + opt('addon', 'Add-on')
        + '</select></td>'
        + '<td><input type="text" class="form-control form-control-sm it-name" style="font-size:12px;" value="' + itemEsc(r.name) + '" placeholder="ชื่อรายการ"></td>'
        + '<td><input type="text" class="form-control form-control-sm it-qyt text-center" style="font-size:12px;" value="' + itemEsc(qyt) + '"></td>'
        + '<td><input type="text" class="form-control form-control-sm it-amount text-right" style="font-size:12px;" value="' + amount.toFixed(2) + '"></td>'
        + '<td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger it-del" style="padding:2px 6px;font-size:11px;" title="ลบรายการ"><i class="bi bi-x-lg"></i></button></td>'
        + '</tr>';
}

// คำนวณยอดสดตามรายการที่กรอก (สูตรเดียวกับที่ใบเดิมในฐานข้อมูลใช้)
function recalcItems() {
    const $body = $('#itemsTbody');
    if (!$body.length) return;

    let sub = 0;
    $body.find('tr.item-row').each(function () {
        const q = itemNum($(this).find('.it-qyt').val()) || 1;
        sub += itemNum($(this).find('.it-amount').val()) * q;
    });
    sub = Math.round(sub * 100) / 100;

    const vat   = Math.round(sub * 0.07 * 100) / 100;
    const gross = Math.round((sub + vat) * 100) / 100;
    // หัก ณ ที่จ่าย 3% ของฐานก่อน VAT เฉพาะนิติบุคคล
    const isJur = $body.data('custType') === 'นิติบุคคล';
    const wht   = isJur ? Math.round(sub * 0.03 * 100) / 100 : 0;
    const net   = Math.round((gross - wht) * 100) / 100;

    const f = n => '฿' + n.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    $('#sumSubtotal').text(f(sub));
    $('#sumVat').text(f(vat));
    $('#sumGross').text(f(gross));
    $('#sumWht').text('- ' + f(wht));
    $('#sumNet').text(f(net));
    $('#sumWhtRow').toggle(isJur);
}

$(document).on('input change', '.it-amount, .it-qyt', recalcItems);

$(document).on('click', '#btnAddItem', function () {
    $('#itemsTbody').append(buildItemRow({}));
    recalcItems();
});

$(document).on('click', '.it-del', function () {
    if ($('#itemsTbody tr.item-row').length <= 1) {
        showNotify('error', 'ต้องมีอย่างน้อย 1 รายการ');
        return;
    }
    $(this).closest('tr').remove();
    recalcItems();
});

$(document).on('click', '#btnSaveItems', function () {
    if (!pendingInvoiceId) return;

    const items = [];
    let missingName = false;
    $('#itemsTbody tr.item-row').each(function () {
        const $row = $(this);
        const name = ($row.find('.it-name').val() || '').trim();
        if (!name) { missingName = true; return false; }
        const full = $row.data('fullamount');
        items.push({
            kind:   $row.find('.it-kind').val(),
            name:   name,
            qyt:    ($row.find('.it-qyt').val() || '1').trim(),
            amount: itemNum($row.find('.it-amount').val()),
            fullamount: (full === '' || full === undefined || full === null) ? null : itemNum(full)
        });
    });

    if (missingName) { showNotify('error', 'กรุณาใส่ชื่อให้ครบทุกรายการ'); return; }
    if (!items.length) { showNotify('error', 'ต้องมีอย่างน้อย 1 รายการ'); return; }

    // datepicker คืนค่าเป็น yyyy-mm-dd แปลงกลับเป็น dd/mm/yyyy ก่อนส่ง
    // (เว้นว่างได้ = ใช้ค่าเดิมในฐานข้อมูล)
    const invDate = isoToThaiDate($('#invDate').val());

    const $btn = $(this).prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm mr-1"></span>กำลังบันทึก');

    $.ajax({
        url: 'api/invoice/updateProductJson.php',
        type: 'POST',
        dataType: 'json',
        data: {
            invoice_id: pendingInvoiceId,
            payload: JSON.stringify(Object.assign(
                {
                    items: items,
                    withholding_mode: 'auto',
                    sync_amount: $('#chkSyncAmount').is(':checked') ? 1 : 0
                },
                // ส่ง date เฉพาะตอนกรอก เพื่อไม่ให้ไปทับค่าเดิมด้วยค่าว่าง
                invDate !== '' ? { date: invDate } : {}
            ))
        },
        success: function (res) {
            if (res.success) {
                showNotify('success', 'บันทึกรายการเรียบร้อย');
                openSendModal(pendingInvoiceId);
                invoiceThTable.ajax.reload(null, false);
            } else {
                showNotify('error', 'บันทึกไม่สำเร็จ: ' + res.message);
            }
        },
        error: function (xhr) {
            let m = 'เกิดข้อผิดพลาดในการบันทึก';
            try { m = JSON.parse(xhr.responseText).message || m; } catch (e) {}
            showNotify('error', m);
        },
        complete: function () {
            $btn.prop('disabled', false).html('<i class="bi bi-save mr-1"></i>บันทึกรายการ');
        }
    });
});

}); // end window load
</script>
