<?php
session_start();
// Customer data: reuse the panel's central session (same guard as viewcreditmake). No second login.
if (empty($_SESSION['id']) || ($_SESSION['level'] ?? 99) >= 4) { // same rule as the sideBar link
    header('Location: ../../../index.php');
    exit;
}
$staffName = $_SESSION['nickName'] ?? $_SESSION['name'] ?? '';
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../../../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <title>Lost Customer Tracking</title>
    <style>
        body { background:#f6f7f9; }
        .breadcrumb li a { text-decoration:none; }
        th.sortable { cursor:pointer; user-select:none; white-space:nowrap; }
        th.sortable .bi { opacity:.35; font-size:.7rem; }
        th.sortable.active .bi { opacity:1; }
        .sticky-head th { position:sticky; top:0; background:#fff; z-index:2; }
        .tbl-scroll { max-height:72vh; overflow:auto; }
        #tbl td { font-size:.84rem; vertical-align:top; }
        #tbl tbody tr { cursor:pointer; }
        .st { font-size:.7rem; }
        .kpi-value { font-size:1.4rem; font-weight:600; font-variant-numeric:tabular-nums; }
        .kpi-label { font-size:.75rem; color:#6c757d; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="../../../main.php">Local For You</a>
        <span class="navbar-text ms-auto">
            <?= htmlspecialchars($staffName) ?>
            &nbsp;<a class="link-light" href="../../../chkLogin.php?act=logout">ออกจากระบบ</a>
        </span>
    </div>
</nav>

<div class="container-fluid px-lg-4 py-4" style="max-width:1800px;">
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <i class="bi bi-house-fill"></i>&nbsp;
            <li class="breadcrumb-item"><a href="../../../main.php">หน้าหลัก</a></li>
            <li class="breadcrumb-item active" aria-current="page">Competitor Scan – Lost Customer Tracking</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <div>
            <h1 class="h4 mb-1">Competitor Scan – Lost Customer Tracking</h1>
            <div class="text-secondary small" id="lastRun">—</div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" id="btnCsv"><i class="bi bi-download"></i> Export CSV</button>
            <button class="btn btn-primary btn-sm" id="btnScan"><i class="bi bi-arrow-repeat"></i> Scan now</button>
        </div>
    </div>

    <div id="loadBanner" class="alert alert-info d-flex align-items-center gap-2 d-none" role="status">
        <span class="spinner-border spinner-border-sm flex-shrink-0"></span>
        <div class="flex-grow-1">
            <div id="loadMsg"></div>
            <div class="progress mt-2" style="height:5px;"><div class="progress-bar" id="loadProg" style="width:0%"></div></div>
            <div class="small mt-1">ปิดหน้านี้ได้ — การ scan ทำต่อจากจุดเดิม (cron จะทำต่อให้ถ้าไม่มีใครเปิดหน้านี้)</div>
        </div>
    </div>
    <div id="errBanner" class="alert alert-danger d-none" role="alert"></div>

    <div class="row g-3 mb-3" id="kpis"></div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-2"><label class="form-label small mb-1">ค้นหา</label>
                    <input class="form-control form-control-sm" id="fText" placeholder="ชื่อร้าน / โดเมน / agency"></div>
                <div class="col-6 col-md-1"><label class="form-label small mb-1">ประเทศ</label>
                    <select class="form-select form-select-sm" id="fCc"><option value="">ทั้งหมด</option></select></div>
                <div class="col-6 col-md-1"><label class="form-label small mb-1">ประเภทร้าน</label>
                    <select class="form-select form-select-sm" id="fType"><option value="">ทั้งหมด</option></select></div>
                <div class="col-6 col-md-2"><label class="form-label small mb-1">Package</label>
                    <select class="form-select form-select-sm" id="fPkg">
                        <option value="">ทั้งหมด</option><option value="marketing">มี Marketing</option><option value="system">มี System</option>
                        <option value="addon">Add-on only</option><option value="unknown">ไม่ทราบ package</option><option value="partial">ยกเลิกบางส่วน</option>
                    </select></div>
                <div class="col-6 col-md-2"><label class="form-label small mb-1">วันที่ยกเลิก</label>
                    <div class="input-group input-group-sm">
                        <input type="date" class="form-control" id="fFrom"><input type="date" class="form-control" id="fTo">
                    </div></div>
                <div class="col-6 col-md-1"><label class="form-label small mb-1">Marketing</label>
                    <select class="form-select form-select-sm" id="fMkt">
                        <option value="">ทั้งหมด</option><option value="Google Ads">Google Ads</option>
                        <option value="Meta (Facebook / Instagram)">Meta</option><option value="none">ไม่พบ</option>
                    </select></div>
                <div class="col-6 col-md-2"><label class="form-label small mb-1">Competitor / System</label>
                    <select class="form-select form-select-sm" id="fComp"><option value="">ทั้งหมด</option></select></div>
                <div class="col-6 col-md-1"><label class="form-label small mb-1">สถานะเว็บ</label>
                    <select class="form-select form-select-sm" id="fSite"><option value="">ทั้งหมด</option></select></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between small text-secondary mb-2">
                <span id="countInfo"></span>
                <span>Likely = เจอร่องรอย (tag / pixel / ลิงก์) แต่ยังไม่ยืนยัน · Not Detected = ไม่พบหลักฐาน (ไม่ได้แปลว่าหยุดใช้) · Unknown = ตรวจไม่ได้</span>
            </div>
            <div class="tbl-scroll table-responsive">
                <table class="table table-sm table-hover mb-0" id="tbl">
                    <thead class="table-light sticky-head">
                    <tr>
                        <th class="sortable" data-key="name">ร้าน <i class="bi bi-arrow-down-up"></i></th>
                        <th class="sortable" data-key="cc">ประเทศ <i class="bi bi-arrow-down-up"></i></th>
                        <th>Package ก่อนยกเลิก</th>
                        <th class="sortable active" data-key="cancel_date">ยกเลิก <i class="bi bi-arrow-down"></i></th>
                        <th>Reason</th>
                        <th>Marketing ที่ยัง active</th>
                        <th>Agency</th>
                        <th>System Provider</th>
                        <th>เว็บ / Evidence</th>
                        <th class="sortable" data-key="scanned_at">Scan ล่าสุด <i class="bi bi-arrow-down-up"></i></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-3 d-none" id="errCard">
        <div class="card-body">
            <h2 class="h6">Scan ไม่สำเร็จ (รอบล่าสุด)</h2>
            <ul class="small mb-0" id="errList"></ul>
        </div>
    </div>

    <footer class="text-secondary small mt-4 pt-3 border-top">
        ข้อมูลจาก monday.com + ตาราง Cancellation + หน้าเว็บสาธารณะของร้านเท่านั้น (ไม่เข้าบัญชีของลูกค้า)
    </footer>
</div>

<div class="modal fade" id="histModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="histTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body small"><div id="histReason" class="mb-3"></div><div id="histBody"></div></div>
        </div>
    </div>
</div>

<script src="../../../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../../../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../controllers/index.js?v=1.0.2"></script>
</body>
</html>
