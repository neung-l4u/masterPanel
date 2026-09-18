<style>
    /* ตารางนี้มี 11 คอลัมน์ กว้างกว่าหน้าอื่นในระบบ จึงย่อขนาดตัวอักษรเฉพาะหน้านี้
       ไม่ให้ดันทะลุกรอบ card ออกไป */
    #datatable thead th,
    #acctTable thead th {
        font-size: 12px !important;
        padding: 0.5rem 0.4rem !important;
        white-space: nowrap;
    }
    #datatable tbody td,
    #acctTable tbody td {
        font-size: 11px !important;
        padding: 0.5rem 0.4rem !important;
        vertical-align: middle;
    }
    /* user agent ยาวมาก ตัดท้ายด้วย ... แทนการขึ้นบรรทัดใหม่ 6 บรรทัด
       (ข้อความเต็มดูได้จาก tooltip ตอน hover) */
    #datatable tbody td:nth-child(7) {
        max-width: 150px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    /* วันที่และปุ่มไม่ควรถูกตัดขึ้นบรรทัดใหม่ */
    #datatable tbody td:nth-child(2),
    #datatable tbody td:nth-child(9),
    #datatable tbody td:nth-child(10),
    #acctTable tbody td:nth-child(5),
    #acctTable tbody td:nth-child(6),
    #acctTable tbody td:nth-child(7) {
        white-space: nowrap;
    }
    #datatable tbody td .badge,
    #acctTable tbody td .badge { font-size: 10px !important; }
    #datatable tbody td .btn,
    #acctTable tbody td .btn { font-size: 10px !important; padding: 0.2rem 0.4rem; }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <i class="bi bi-key mr-2"></i> Payment Password
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item">Logs</li>
                    <li class="breadcrumb-item active">Payment Password</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <ul class="nav nav-tabs" id="pwTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="tab-forgot" data-toggle="tab" data-bs-toggle="tab"
                   href="#pane-forgot" role="tab">
                    <i class="bi bi-clock-history mr-1"></i> Forgot Password
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="tab-account" data-toggle="tab" data-bs-toggle="tab"
                   href="#pane-account" role="tab">
                    <i class="bi bi-person-lines-fill mr-1"></i> Account
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- ================= แท็บ 1 : ประวัติการขอรีเซ็ต (event log หลายแถวต่อคน) ========= -->
            <div class="tab-pane fade show active" id="pane-forgot" role="tabpanel">
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle mr-1"></i>
                    ประวัติการกด <strong>Forgot Password</strong> ของลูกค้าบนระบบ payments
                    ใช้ตรวจว่าลูกค้าขอรีเซ็ตรหัสผ่านมาจริงไหม และรีเซ็ตสำเร็จหรือยัง
                    ลูกค้าคนเดียวขอได้หลายครั้งจึงมีได้หลายแถว
                    <strong>ถ้าต้องการดูรหัสผ่าน ไปที่แท็บ Account</strong>
                </div>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-table mr-1"></i> Password Reset Requests</h5>
                        <select id="filterStatus" class="form-control form-control-sm" style="width:auto;">
                            <option value="">All Status</option>
                            <option value="Reset Done">Reset Done</option>
                            <option value="Requested Only">Requested Only</option>
                        </select>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="datatable" class="table table-bordered table-striped table-hover" style="width:100%">
                            <thead class="thead-dark">
                            <tr>
                                <th style="width:5%;">#</th>
                                <th style="width:12%;">Requested At</th>
                                <th style="width:16%;">Customer</th>
                                <th style="width:18%;">Email</th>
                                <th style="width:7%;">Country</th>
                                <th style="width:11%;">IP Address</th>
                                <th style="width:12%;">Device</th>
                                <th style="width:9%;">Status</th>
                                <th style="width:12%;">Completed At</th>
                                <th style="width:9%;">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ================= แท็บ 2 : บัญชีลูกค้าปัจจุบัน (หนึ่งแถวต่อคน) ================= -->
            <div class="tab-pane fade" id="pane-account" role="tabpanel">
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-shield-lock mr-1"></i>
                    บัญชีลูกค้าทั้งหมดในระบบ payments — ค้นหาด้วยอีเมลหรือชื่อร้านได้ที่ช่อง Search<br>
                    ช่อง <strong>Password</strong> เปิดดูได้เฉพาะทีม CS, AM, IT, CEO, MK และเฉพาะลูกค้าที่ยังไม่เคยตั้งรหัสใหม่เอง
                    (ถ้าขึ้นว่า "เปลี่ยนแล้ว" ระบบไม่มีรหัสปัจจุบัน ให้ใช้ปุ่ม Resend แทน)
                    <strong>การกดดูรหัสทุกครั้งจะถูกบันทึกว่าใครเป็นคนเปิด</strong>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-people mr-1"></i> Customer Accounts</h5>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="acctTable" class="table table-bordered table-striped table-hover" style="width:100%">
                            <thead class="thead-dark">
                            <tr>
                                <th style="width:22%;">Customer</th>
                                <th style="width:24%;">Email</th>
                                <th style="width:8%;">Country</th>
                                <th style="width:16%;">Stripe ID</th>
                                <th style="width:12%;">Created At</th>
                                <th style="width:9%;">Password</th>
                                <th style="width:9%;">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.addEventListener('load', function() {
    var pwTable, acctTable;

    // ---------- แท็บ 1 : Forgot Password (event log) ----------
    if (jQuery.fn.DataTable.isDataTable('#datatable')) {
        pwTable = jQuery('#datatable').DataTable();
    } else {
        pwTable = jQuery('#datatable').DataTable({
            pagingType: 'full_numbers',
            ajax: {
                url: 'pages/tableRendering/dataPaymentPassword.php',
                dataSrc: 'data'
            },
            pageLength: 25,
            order: [[1, 'desc']],   // วันที่ขอล่าสุดขึ้นก่อน
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'All']
            ],
            columnDefs: [
                { targets: [0, 4, 5, 7, 9], className: 'dt-center' },
                { targets: [9], orderable: false, searchable: false }
            ],
            dom: 'lfrtip'
        });
    }

    jQuery('#filterStatus').on('change', function() {
        pwTable.column(7).search(this.value).draw();
    });

    // ---------- แท็บ 2 : Account (หนึ่งแถวต่อลูกค้าหนึ่งคน) ----------
    if (jQuery.fn.DataTable.isDataTable('#acctTable')) {
        acctTable = jQuery('#acctTable').DataTable();
    } else {
        acctTable = jQuery('#acctTable').DataTable({
            pagingType: 'full_numbers',
            ajax: {
                url: 'pages/tableRendering/dataPaymentAccount.php',
                dataSrc: 'data'
            },
            pageLength: 25,
            order: [[4, 'desc']],   // สมัครล่าสุดขึ้นก่อน
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'All']
            ],
            columnDefs: [
                { targets: [2, 4, 5, 6], className: 'dt-center' },
                { targets: [5, 6], orderable: false, searchable: false }
            ],
            dom: 'lfrtip'
        });
    }

    // DataTables คำนวณความกว้างพลาดตอนตารางยังถูกซ่อนอยู่ในแท็บ
    // พอสลับมาแท็บ Account ครั้งแรกจึงต้องสั่งวาดหัวตารางใหม่
    jQuery('a[data-toggle="tab"], a[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
        jQuery.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    // ---------- ปุ่มในตาราง (ใช้ร่วมกันทั้งสองแท็บ) ----------

    // เปิดดูรหัสผ่านทีละคน (เฉพาะทีมที่มีสิทธิ์) ทุกครั้งที่กดจะถูกบันทึกไว้
    jQuery('#datatable tbody, #acctTable tbody').on('click', '.btn-reveal', function() {
        var btn  = jQuery(this);
        var slot = btn.closest('.pw-slot');
        var email = slot.attr('data-email');

        if (!confirm('เปิดดูรหัสผ่านของ\n' + email + ' ?\n\nการกดดูจะถูกบันทึกว่าใครเป็นคนเปิด')) {
            return;
        }

        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');

        jQuery.post('pages/tableRendering/revealPassword.php', { email: email })
            .done(function(res) {
                if (res.status === 'success') {
                    slot.html('<code class="text-danger">' + jQuery('<div>').text(res.password).html() + '</code>');
                } else {
                    alert('⚠️ ' + res.message);
                    btn.prop('disabled', false).html('<i class="bi bi-eye"></i> ดูรหัส');
                }
            })
            .fail(function(xhr) {
                var msg = 'เปิดดูไม่สำเร็จ (HTTP ' + xhr.status + ')';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
                alert('❌ ' + msg);
                btn.prop('disabled', false).html('<i class="bi bi-eye"></i> ดูรหัส');
            });
    });

    // ส่งลิงก์รีเซ็ตรหัสผ่านซ้ำให้ลูกค้า
    jQuery('#datatable tbody, #acctTable tbody').on('click', '.btn-resend', function() {
        var btn = jQuery(this);
        var email = btn.attr('data-email');
        // กดจากแท็บไหน ให้รีโหลดตารางนั้น
        var table = btn.closest('table').attr('id') === 'acctTable' ? acctTable : pwTable;

        if (!confirm('ส่งลิงก์รีเซ็ตรหัสผ่านใหม่ไปที่\n' + email + ' ?\n\nลิงก์เดิมที่เคยส่งไปจะใช้ไม่ได้อีก')) {
            return;
        }

        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Sending...');

        jQuery.post('pages/tableRendering/resendPasswordReset.php', { email: email })
            .done(function(res) {
                alert(res.status === 'success' ? '✅ ' + res.message : '❌ ' + res.message);
                if (res.status === 'success') {
                    table.ajax.reload(null, false);
                }
            })
            .fail(function(xhr) {
                var msg = 'ส่งไม่สำเร็จ (HTTP ' + xhr.status + ')';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
                alert('❌ ' + msg);
            })
            .always(function() {
                btn.prop('disabled', false).html('<i class="bi bi-envelope-arrow-up"></i> Resend');
            });
    });
});
</script>
