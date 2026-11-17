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
        #tbl th { font-size:.78rem; font-weight:600; color:#6c757d; border-bottom-color:#dee2e6; white-space:nowrap; }
        #tbl td { font-size:.86rem; white-space:nowrap; padding-top:.6rem; padding-bottom:.6rem; }
        #tbl tbody tr { cursor:pointer; }
        #presets button { border:1px solid #dee2e6; background:#fff; border-radius:8px; padding:.35rem .8rem; font-size:.86rem; font-weight:600; color:#30363d; }
        #presets button.on { border:2px solid #212529; padding:calc(.35rem - 1px) calc(.8rem - 1px); }
        #fCount:empty { display:none; }
        #pager .pg { min-width:2rem; height:2rem; border:1px solid #dee2e6; background:#fff; border-radius:8px; font-size:.86rem; color:#30363d; }
        #pager .pg.on { border:2px solid #212529; font-weight:600; }
        #pager .pg:disabled { opacity:.4; }
        #shopSheet.offcanvas-bottom { max-height:88vh; border-radius:16px 16px 0 0; }
        #shopSheet.offcanvas-end { width:440px; }
        .shop-row { display:flex; align-items:center; gap:.75rem; padding:.8rem .25rem; border-bottom:1px solid #eceef1; cursor:pointer; }
        .shop-row:active { background:#f6f7f9; }
        .shop-row .nm { font-weight:600; font-size:.98rem; }
        .shop-row .sub { color:#6c757d; font-size:.86rem; }
        /* summary dashboard: light, Stripe-like cards (restored after the table rewrite dropped this block) */
        .dash { color:#212529; }
        .dash .tile { background:#fff; border:1px solid #e3e5e8; border-radius:8px; padding:14px 16px; height:100%; }
        .dash .tile h3 { font-size:.85rem; font-weight:600; color:#30363d; margin-bottom:.6rem; }
        .dash .muted { color:#6c757d !important; }
        .dash .big { font-size:2rem; font-weight:700; line-height:1.1; color:#1a1c20; font-variant-numeric:tabular-nums; }
        .dash .big .unit { font-size:1rem; font-weight:600; color:#6c757d; }
        .dash .spark { width:100%; height:44px; display:block; margin-top:.5rem; }
        .dash .ax { fill:#6c757d; font-size:10px; }
        .dash .meter { height:10px; background:#eceef1; border-radius:5px; overflow:hidden; }
        .dash .meter div { height:100%; background:#199e70; border-radius:5px; }
        .dash .hb { display:grid; grid-template-columns:minmax(7rem, 34%) 1fr 2.5rem; gap:.6rem; align-items:center; font-size:.82rem; margin-bottom:.35rem; }
        .dash .hb-l { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .dash .hb-t { height:.75rem; } .dash .hb-t div { height:100%; min-width:2px; border-radius:0 4px 4px 0; }
        .dash .hb-v { text-align:right; font-variant-numeric:tabular-nums; color:#495057; }
        .dash .hb-wide { grid-template-columns:minmax(8rem, 26%) 1fr 5.5rem; }
        .dash .hb-t.stack { display:flex; gap:2px; } .dash .hb-t.stack div { border-radius:0; } .dash .hb-t.stack div:last-child { border-radius:0 4px 4px 0; }
        .dash .scroll { overflow:auto; }
        .dash .table { --bs-table-bg:transparent; font-size:.82rem; }
        .dash .table th { color:#6c757d; font-weight:600; position:sticky; top:0; background:#fff; }
        .dash .shop-link { cursor:pointer; color:#0d6efd; }
        .dash .shop-link:hover { text-decoration:underline; }
        .dash .seg { display:inline-flex; background:#eceef1; border-radius:8px; padding:2px; gap:2px; }
        .dash .seg button { border:0; background:transparent; color:#495057; font-size:.8rem; padding:.3rem .8rem; border-radius:6px; }
        .dash .seg button.on { background:#fff; color:#212529; font-weight:600; box-shadow:0 1px 2px rgba(0,0,0,.12); }
        /* phone: iOS-like cards, one section at a time, swipeable donuts, list rows */
        .dash .ios-row summary { list-style:none; display:flex; align-items:center; gap:.7rem; padding:.55rem 0; border-bottom:1px solid #eceef1; cursor:pointer; }
        .dash .ios-row summary::-webkit-details-marker { display:none; }
        .dash .ios-row[open] .bi-chevron-right { transform:rotate(90deg); }
        .dash .ios-ico { width:2.2rem; height:2.2rem; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:700; color:#fff; flex-shrink:0; }
        #bottomNav { position:fixed; left:0; right:0; bottom:0; z-index:1040; display:flex; background:rgba(255,255,255,.96);
            backdrop-filter:blur(10px); border-top:1px solid #e3e5e8; padding:6px 4px calc(6px + env(safe-area-inset-bottom)); }
        #bottomNav button { flex:1; border:0; background:transparent; color:#6c757d; display:flex; flex-direction:column; align-items:center; gap:2px; font-size:.68rem; padding:4px 0; }
        #bottomNav button i { font-size:1.25rem; line-height:1; }
        #bottomNav button.on { color:#212529; font-weight:600; }
        .dot { display:inline-block; width:.5rem; height:.5rem; border-radius:50%; margin-right:.25rem; vertical-align:middle; }
        #sheetBody .lbl { color:#6c757d; font-size:.8rem; }
        #sheetBody .v { font-weight:600; font-size:.92rem; word-break:break-word; }
        @media (max-width:767.98px) {
            body { padding-bottom:calc(72px + env(safe-area-inset-bottom)); }
            .container-fluid { padding-left:12px; padding-right:12px; }
            .dash { padding:0; }
            .dash .tile { border-radius:12px; }
            .dash .big { font-size:2.4rem; }
            .dash .seg { display:flex; width:100%; } .dash .seg button { flex:1; padding:.4rem .2rem; }
            .dash[data-sec] .sec { display:none; }
            .dash[data-sec="overview"] .sec-overview, .dash[data-sec="competitors"] .sec-competitors,
            .dash[data-sec="countries"] .sec-countries, .dash[data-sec="shops"] .sec-shops { display:block; }
            .dash[data-sec="overview"] .row.sec-overview, .dash[data-sec="competitors"] .row.sec-competitors { display:flex; }
            .dash .donuts { flex-wrap:nowrap; overflow-x:auto; scroll-snap-type:x mandatory; padding-bottom:6px; }
            .dash .donuts > div { flex:0 0 86%; width:86%; scroll-snap-align:center; }
            #kpis { display:none !important; }
            #viewTable { border:0; background:transparent; } #viewTable > .card-body { padding:0; }
        }
        .legend-dot { display:inline-block; width:.7rem; height:.7rem; border-radius:2px; vertical-align:middle; }
        .dash details.more summary { cursor:pointer; list-style:none; color:#0d6efd !important; } .dash details.more summary::-webkit-details-marker { display:none; } .dash details.more[open] summary { margin-bottom:.15rem; }
    </style>
</head>
<body>

<nav class="navbar bg-white border-bottom shadow-sm py-1">
    <div class="container-fluid">
        <a class="navbar-brand py-0" href="../../../main.php"><img src="../../../assets/img/Logo-Local-For-You.png" alt="Local For You" style="height:52px;width:auto"></a>
        <span class="d-flex align-items-center gap-3 ms-auto">
            <span class="text-secondary small d-none d-sm-inline"><?= htmlspecialchars($staffName) ?></span>
            <a class="btn btn-light border-0 text-body rounded-circle d-inline-flex align-items-center justify-content-center" style="width:2.25rem;height:2.25rem"
               href="../../../main.php" title="Back to Master Panel" aria-label="Back to Master Panel"><i class="bi bi-arrow-left fs-5"></i></a>
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

    <!-- quick views + search + filter button (Stripe-style); the full filters open from the button -->
    <div class="d-none d-md-flex flex-wrap gap-2 mb-3" id="presets">
        <button class="on" data-p="all">All</button><button data-p="moved">Moved to competitor</button><button data-p="lfy">Still on LFY</button>
        <button data-p="none">No competitor found</button><button data-p="unknown">Could not check</button><button data-p="recent">Last 90 days</button>
    </div>
    <select class="form-select mb-2 d-md-none" id="presetSel" aria-label="View">
        <option value="all">All</option><option value="moved">Moved to competitor</option><option value="lfy">Still on LFY</option>
        <option value="none">No competitor found</option><option value="unknown">Could not check</option><option value="recent">Last 90 days</option>
    </select>
    <div class="d-flex gap-2 mb-3">
        <div class="position-relative flex-grow-1"><i class="bi bi-search position-absolute text-secondary" style="left:.8rem;top:50%;transform:translateY(-50%)"></i>
            <input class="form-control" style="padding-left:2.3rem" id="fText" placeholder="Search shop, domain, agency…"></div>
        <button class="btn btn-outline-secondary bg-white text-body" id="btnFilter" aria-label="Filters"><i class="bi bi-sliders"></i><span class="d-none d-md-inline"> Filter</span>
            <span class="badge rounded-pill text-bg-primary" id="fCount"></span></button>
    </div>
    <div class="card mb-3 collapse" id="filterCard">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-1"><label class="form-label small mb-1">Country</label>
                    <select class="form-select form-select-sm" id="fCc"><option value="">All</option></select></div>
                <div class="col-6 col-md-1"><label class="form-label small mb-1">Shop type</label>
                    <select class="form-select form-select-sm" id="fType"><option value="">All</option></select></div>
                <div class="col-6 col-md-2"><label class="form-label small mb-1">Package</label>
                    <select class="form-select form-select-sm" id="fPkg">
                        <option value="">All</option><option value="marketing">Has Marketing</option><option value="system">Has System</option>
                        <option value="addon">Add-on only</option><option value="unknown">Unknown package</option><option value="partial">Partial cancel</option><option value="downgrade">Marketing downgrade / pause (log)</option><option value="cbl">Marketing cancelled before live (log)</option>
                    </select></div>
                <div class="col-6 col-md-2"><label class="form-label small mb-1">Cancel date</label>
                    <div class="input-group input-group-sm">
                        <input type="date" class="form-control" id="fFrom"><input type="date" class="form-control" id="fTo">
                    </div></div>
                <div class="col-6 col-md-1"><label class="form-label small mb-1">Marketing</label>
                    <select class="form-select form-select-sm" id="fMkt">
                        <option value="">All</option><option value="Google Ads">Google Ads</option>
                        <option value="Meta (Facebook / Instagram)">Meta</option><option value="none">None found</option>
                    </select></div>
                <div class="col-6 col-md-2"><label class="form-label small mb-1">Competitor / System</label>
                    <select class="form-select form-select-sm" id="fComp"><option value="">All</option></select></div>
                <div class="col-6 col-md-1"><label class="form-label small mb-1">Site status</label>
                    <select class="form-select form-select-sm" id="fSite"><option value="">All</option></select></div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3 d-none d-md-flex" id="viewTabs">
        <li class="nav-item d-none d-md-block"><button class="nav-link active" data-view="Table"><i class="bi bi-table"></i> Table</button></li>
        <li class="nav-item d-none d-md-block"><button class="nav-link" data-view="Summary"><i class="bi bi-bar-chart"></i> Summary</button></li>
    </ul>
    <div class="offcanvas offcanvas-bottom h-auto" tabindex="-1" id="filterSheet" style="max-height:85vh">
        <div class="offcanvas-header py-2"><h5 class="offcanvas-title h6">Filter</h5><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
        <div class="offcanvas-body pt-0"></div>
    </div>

    <div id="viewSummary" class="d-none"></div>

    <div class="card" id="viewTable">
        <div class="card-body">
            <div id="cardList" class="d-md-none"></div>
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0" id="tbl">
                    <thead>
                    <tr>
                        <th class="sortable" data-key="name">Shop <i class="bi bi-arrow-down-up"></i></th>
                        <th>Package before cancel</th>
                        <th class="sortable active" data-key="cancel_date">Cancelled <i class="bi bi-arrow-down"></i></th>
                        <th>Moved to</th>
                        <th>Agency</th>
                        <th>Reason</th>
                        <th class="sortable text-end" data-key="balance_total" title="Price of the subscriptions the shop cancelled (monday Active Subscriptions), in the shop's currency">Subscription price <i class="bi bi-arrow-down-up"></i></th>
                        <th style="width:2.5rem"></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3" id="pager"></div>
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

<!-- one shop's details: bottom sheet on phone, side panel on desktop -->
<div class="offcanvas offcanvas-bottom h-auto" tabindex="-1" id="shopSheet">
    <div class="offcanvas-header align-items-start border-bottom">
        <div style="min-width:0"><h5 class="offcanvas-title fw-bold mb-0" id="sheetTitle"></h5><div class="text-secondary small" id="sheetSub"></div></div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="sheetBody"></div>
</div>

<!-- phone: app-style bottom navigation (replaces the Table / Summary tabs) -->
<nav id="bottomNav" class="d-md-none">
    <button data-go="overview" class="on"><i class="bi bi-grid"></i><span>Overview</span></button>
    <button data-go="competitors"><i class="bi bi-trophy"></i><span>Competitors</span></button>
    <button data-go="countries"><i class="bi bi-globe"></i><span>Countries</span></button>
    <button data-go="shops"><i class="bi bi-shop"></i><span>Lost shops</span></button>
    <button data-go="table"><i class="bi bi-list-ul"></i><span>Shops list</span></button>
</nav>

<script src="../../../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../../../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../controllers/index.js?v=1.9.0"></script>
</body>
</html>
