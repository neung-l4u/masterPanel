<?php
session_start();
/**
 * Internal data (all scenario names + org credit spend), so it reuses the panel's
 * central session — same guard as assets/api/checkSession.php. No second login.
 */
if (empty($_SESSION['id'])) {
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
    <title>การใช้เครดิต Make.com</title>
    <style>
        body { background:#f6f7f9; }
        .breadcrumb li a { text-decoration:none; }
        .kpi-label { font-size:.78rem; color:#6c757d; }
        .kpi-value { font-size:1.55rem; font-weight:600; font-variant-numeric:tabular-nums; line-height:1.2; }
        .kpi-note { font-size:.72rem; color:#8a9199; }
        .num { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
        th.sortable { cursor:pointer; user-select:none; white-space:nowrap; }
        th.sortable .bi { opacity:.35; font-size:.7rem; }
        th.sortable.active .bi { opacity:1; }
        #tblScenarios tbody tr { cursor:pointer; }
        #tblScenarios tbody tr.table-active td { font-weight:500; }
        .unacc td { font-style:italic; color:#6c757d; }
        .chart-box { position:relative; height:420px; }
        .chart-box-sm { position:relative; height:300px; }
        .tab-pane { padding-top:1.25rem; }
        .table-sm > :not(caption) > * > * { padding:.4rem .5rem; }
        .sticky-head th { position:sticky; top:0; background:#fff; z-index:2; }
        .log-scroll { max-height:70vh; overflow:auto; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="../../../main.php">Local For You</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link active" href="index.php">การใช้เครดิต Make</a></li>
            </ul>
            <span class="navbar-text">
                <?= htmlspecialchars($staffName) ?>
                &nbsp;<a class="link-light" href="../../../chkLogin.php?act=logout">ออกจากระบบ</a>
            </span>
        </div>
    </div>
</nav>

<div class="container-fluid px-lg-4 py-4" style="max-width:1600px;">

    <header>
        <nav class="mb-3" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <i class="bi bi-house-fill"></i>&nbsp;
                <li class="breadcrumb-item"><a href="../../../main.php">หน้าหลัก</a></li>
                <li class="breadcrumb-item active" aria-current="page">การใช้เครดิต Make.com</li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
            <div>
                <h1 class="h4 mb-1">การใช้เครดิต Make.com</h1>
                <div class="text-secondary small">
                    องค์กร Local For You ·
                    เวลาทั้งหมดแสดงตามเขตเวลาขององค์กร
                    <span class="badge text-bg-light border" id="tzBadge">กำลังตรวจสอบ…</span>
                </div>
            </div>
            <div class="text-secondary small text-lg-end" id="fetchedAt"></div>
        </div>
    </header>

    <!-- loading / error / partial-failure banners -->
    <div id="loadBanner" class="alert alert-info d-flex align-items-center gap-2 d-none" role="status">
        <span class="spinner-border spinner-border-sm flex-shrink-0"></span>
        <div class="flex-grow-1">
            <div id="loadMsg">กำลังโหลดข้อมูลจาก Make…</div>
            <div class="progress mt-2 d-none" id="loadProgWrap" style="height:5px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" id="loadProg" style="width:0%"></div>
            </div>
            <div class="small mt-1" id="loadHint"></div>
        </div>
    </div>
    <div id="errBanner" class="alert alert-danger d-none" role="alert"></div>
    <div id="failBanner" class="alert alert-warning d-none" role="alert"></div>

    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tabSummaryBtn" data-bs-toggle="tab"
                    data-bs-target="#tabSummary" type="button" role="tab">
                <i class="bi bi-bar-chart-line"></i> สรุปต่อ Scenario
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tabLogsBtn" data-bs-toggle="tab"
                    data-bs-target="#tabLogs" type="button" role="tab">
                <i class="bi bi-list-ul"></i> Credit Usage
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- ============ TAB 1 : summary ============ -->
        <div class="tab-pane fade show active" id="tabSummary" role="tabpanel">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-auto">
                            <label class="form-label kpi-label mb-1">โหมด</label>
                            <select class="form-select form-select-sm" id="sumMode" style="width:auto;">
                                <option value="range" selected>ช่วงวันที่</option>
                                <option value="single">วันเดียว</option>
                            </select>
                        </div>
                        <div class="col-auto" id="sumFromWrap">
                            <label class="form-label kpi-label mb-1">ตั้งแต่</label>
                            <input type="date" class="form-control form-control-sm" id="sumFrom">
                        </div>
                        <div class="col-auto">
                            <label class="form-label kpi-label mb-1" id="sumToLabel">ถึง</label>
                            <input type="date" class="form-control form-control-sm" id="sumTo">
                        </div>
                        <div class="col-auto">
                            <label class="form-label kpi-label mb-1">เครดิตคงเหลือ</label>
                            <input type="number" min="0" step="1" class="form-control form-control-sm"
                                   id="fcRemaining" value="10000" style="width:130px;">
                        </div>
                        <div class="col-auto">
                            <label class="form-label kpi-label mb-1">วันต่ออายุ</label>
                            <input type="date" class="form-control form-control-sm" id="fcRenewal">
                        </div>
                        <div class="col-auto">
                            <label class="form-label kpi-label mb-1">buffer</label>
                            <input type="number" min="0.1" step="0.1" class="form-control form-control-sm"
                                   id="fcBuffer" value="1.2" style="width:90px;">
                        </div>
                        <div class="col-auto ms-auto d-flex gap-2">
                            <button class="btn btn-sm btn-primary" id="btnApply">
                                <i class="bi bi-arrow-repeat"></i> ดูข้อมูล
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" id="btnRefresh"
                                    title="ดึงข้อมูลใหม่จาก Make (ข้าม cache)">
                                <i class="bi bi-cloud-arrow-down"></i> ล้าง cache
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3" id="kpiRow">
                <div class="col-6 col-lg">
                    <div class="card h-100"><div class="card-body">
                        <div class="kpi-label">เครดิตรวมในช่วง</div>
                        <div class="kpi-value" id="kpiCredits">–</div>
                        <div class="kpi-note" id="kpiCreditsNote"></div>
                    </div></div>
                </div>
                <div class="col-6 col-lg">
                    <div class="card h-100"><div class="card-body">
                        <div class="kpi-label">เฉลี่ยต่อวัน</div>
                        <div class="kpi-value" id="kpiAvg">–</div>
                        <div class="kpi-note" id="kpiAvgNote"></div>
                    </div></div>
                </div>
                <div class="col-6 col-lg">
                    <div class="card h-100"><div class="card-body">
                        <div class="kpi-label">เครดิตคงเหลือ</div>
                        <div class="kpi-value" id="kpiRemaining">–</div>
                        <div class="kpi-note" id="kpiRemainingNote"></div>
                    </div></div>
                </div>
                <div class="col-6 col-lg">
                    <div class="card h-100"><div class="card-body">
                        <div class="kpi-label">เครดิตที่ต้องใช้ (คาดการณ์)</div>
                        <div class="kpi-value" id="kpiRequired">–</div>
                        <div class="kpi-note" id="kpiRequiredNote"></div>
                    </div></div>
                </div>
                <div class="col-12 col-lg">
                    <div class="card h-100"><div class="card-body">
                        <div class="kpi-label">ส่วนเกิน / ขาด</div>
                        <div class="kpi-value" id="kpiBalance">–</div>
                        <div class="kpi-note" id="kpiBalanceNote"></div>
                    </div></div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-xl-6">
                    <div class="card h-100"><div class="card-body">
                        <h2 class="h6 mb-3">Top 15 scenario ตามเครดิต</h2>
                        <div class="chart-box"><canvas id="chartTop"></canvas></div>
                    </div></div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="card h-100"><div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h2 class="h6 mb-0" id="dailyTitle">เครดิตรายวัน · top 5 + อื่น ๆ</h2>
                            <button class="btn btn-sm btn-outline-secondary d-none" id="btnClearPick">
                                <i class="bi bi-x-lg"></i> ดูรวมทุก scenario
                            </button>
                        </div>
                        <div class="chart-box"><canvas id="chartDaily"></canvas></div>
                    </div></div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h2 class="h6 mb-0">รายละเอียดต่อ scenario
                            <span class="text-secondary fw-normal small" id="sumCount"></span>
                        </h2>
                        <input type="search" class="form-control form-control-sm" id="sumSearch"
                               placeholder="ค้นหาชื่อ scenario…" style="max-width:260px;">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" id="tblScenarios">
                            <thead class="table-light">
                            <tr>
                                <th class="sortable" data-key="name">ชื่อ <i class="bi bi-arrow-down-up"></i></th>
                                <th class="sortable" data-key="isActive">สถานะ <i class="bi bi-arrow-down-up"></i></th>
                                <th class="sortable" data-key="schedulingSummary">ตารางเวลา <i class="bi bi-arrow-down-up"></i></th>
                                <th class="sortable num active" data-key="totalCredits">เครดิต <i class="bi bi-arrow-down"></i></th>
                                <th class="sortable num" data-key="totalOperations">operations <i class="bi bi-arrow-down-up"></i></th>
                                <th class="sortable num" data-key="avgCreditsPerDay">เฉลี่ย/วัน <i class="bi bi-arrow-down-up"></i></th>
                                <th class="sortable num" data-key="pct">% ของทั้งหมด <i class="bi bi-arrow-down-up"></i></th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot class="table-light"></tfoot>
                        </table>
                    </div>
                    <div class="text-secondary small mt-2">
                        <i class="bi bi-info-circle"></i>
                        คลิกแถวเพื่อดูกราฟรายวันของ scenario นั้น
                    </div>
                </div>
            </div>
        </div>

        <!-- ============ TAB 2 : credit usage log ============ -->
        <div class="tab-pane fade" id="tabLogs" role="tabpanel">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-auto">
                            <label class="form-label kpi-label mb-1">โหมด</label>
                            <select class="form-select form-select-sm" id="logMode" style="width:auto;">
                                <option value="single" selected>วันเดียว</option>
                                <option value="range">ช่วงวันที่</option>
                            </select>
                        </div>
                        <div class="col-auto d-none" id="logFromWrap">
                            <label class="form-label kpi-label mb-1">ตั้งแต่</label>
                            <input type="date" class="form-control form-control-sm" id="logFrom">
                        </div>
                        <div class="col-auto">
                            <label class="form-label kpi-label mb-1" id="logToLabel">วันที่</label>
                            <input type="date" class="form-control form-control-sm" id="logTo">
                        </div>
                        <div class="col-auto">
                            <label class="form-label kpi-label mb-1">ค้นหา scenario</label>
                            <input type="search" class="form-control form-control-sm" id="logSearch"
                                   placeholder="ชื่อ scenario…" style="max-width:240px;">
                        </div>
                        <div class="col-auto">
                            <label class="form-label kpi-label mb-1">แถว/หน้า</label>
                            <select class="form-select form-select-sm" id="logPageSize" style="width:auto;">
                                <option>50</option><option selected>100</option><option>250</option>
                            </select>
                        </div>
                        <div class="col-auto ms-auto d-flex gap-2">
                            <button class="btn btn-sm btn-primary" id="btnLogLoad">
                                <i class="bi bi-arrow-repeat"></i> ดูข้อมูล
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" id="btnLogRefresh">
                                <i class="bi bi-cloud-arrow-down"></i> ล้าง cache
                            </button>
                        </div>
                    </div>
                    <div class="small text-secondary mt-2">
                        <i class="bi bi-exclamation-triangle"></i>
                        โหมดช่วงวันที่ต้องดึง log ทีละ scenario ใช้เวลานาน แนะนำเลือกวันเดียว
                        · Make เก็บประวัติย้อนหลังประมาณ 30 วัน
                    </div>
                </div>
            </div>

            <div id="logTruncBanner" class="alert alert-warning d-none" role="alert"></div>
            <div id="logFailBanner" class="alert alert-warning d-none" role="alert"></div>

            <div class="row g-3 mb-3">
                <div class="col-6 col-lg-3">
                    <div class="card h-100"><div class="card-body">
                        <div class="kpi-label">จำนวน event</div>
                        <div class="kpi-value" id="logKpiEvents">–</div>
                    </div></div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card h-100"><div class="card-body">
                        <div class="kpi-label">เครดิตรวม</div>
                        <div class="kpi-value" id="logKpiCredits">–</div>
                    </div></div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card h-100"><div class="card-body">
                        <div class="kpi-label">operations รวม</div>
                        <div class="kpi-value" id="logKpiOps">–</div>
                    </div></div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card h-100"><div class="card-body">
                        <div class="kpi-label">scenario ที่มี event</div>
                        <div class="kpi-value" id="logKpiScenarios">–</div>
                    </div></div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="log-scroll table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" id="tblLogs">
                            <thead class="table-light sticky-head">
                            <tr>
                                <th class="sortable" data-key="scenarioName">Name <i class="bi bi-arrow-down-up"></i></th>
                                <th class="sortable" data-key="type">Type <i class="bi bi-arrow-down-up"></i></th>
                                <th class="sortable num" data-key="credits">Credits <i class="bi bi-arrow-down-up"></i></th>
                                <th class="sortable num" data-key="operations">Operations <i class="bi bi-arrow-down-up"></i></th>
                                <th class="sortable num" data-key="transfer">Data transfer <i class="bi bi-arrow-down-up"></i></th>
                                <th class="sortable num active" data-key="timestamp">Usage recorded <i class="bi bi-arrow-down"></i></th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                        <div class="text-secondary small" id="logPageInfo"></div>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary" id="logPrev"><i class="bi bi-chevron-left"></i> ก่อนหน้า</button>
                            <button class="btn btn-outline-secondary" id="logNext">ถัดไป <i class="bi bi-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-secondary small mt-4 pt-3 border-top">
        copyright © 2024 by localforyou.com · ข้อมูลจาก Make.com API (อ่านอย่างเดียว)
    </footer>
</div>

<script src="../../../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../../../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../../../plugins/chart.js/Chart.min.js"></script>
<script src="../controllers/index.js?v=1.0.0"></script>
</body>
</html>
