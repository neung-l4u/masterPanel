<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-LGKDYHL23T');
</script>
<?php
global $db;

// Auto-import from websiteList on page load
$toImport = $db->query(
    "SELECT wID, wProject, wDomain FROM websiteList
     WHERE delete_at IS NULL AND wLiveStatus = 'Live'
       AND wID NOT IN (SELECT source_wID FROM monitors WHERE source_wID IS NOT NULL)"
)->fetchAll();

foreach ($toImport as $w) {
    $url = trim($w['wDomain']);
    if (empty($url)) continue;
    if (!preg_match('#^https?://#i', $url)) $url = 'https://' . $url;
    if (!filter_var($url, FILTER_VALIDATE_URL)) continue;
    $db->query(
        "INSERT INTO monitors (name, url, category, source_wID, check_interval) VALUES (?, ?, 'client', ?, 5)",
        $w['wProject'], $url, $w['wID']
    );
}
?>
<link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/css/bootstrap.css">
<link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

/* ── Base ────────────────────────────────────────────── */
.monitor-page * { font-family: 'Inter', system-ui, sans-serif; box-sizing: border-box; -webkit-font-smoothing: antialiased; }
.monitor-page { padding: 0 0 48px; }

/* ── Border trail animation ──────────────────────────── */
@property --a { syntax: '<angle>'; initial-value: 0deg; inherits: false; }
@keyframes spin-trail { to { --a: 360deg; } }

.bt {
    position: relative;
    border-radius: 16px;
    padding: 1.5px;
    background: conic-gradient(
        from var(--a),
        transparent 0%, transparent 60%,
        #a5f3fc 70%, #38bdf8 78%, #818cf8 84%,
        #c084fc 88%, #38bdf8 92%, transparent 100%
    );
    animation: spin-trail 3.5s linear infinite;
}
.bt::before {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 20px;
    background: conic-gradient(
        from var(--a),
        transparent 0%, transparent 55%,
        rgba(56,189,248,.4) 72%, rgba(129,140,248,.3) 82%,
        rgba(192,132,252,.25) 88%, transparent 100%
    );
    filter: blur(12px);
    z-index: -1;
    animation: spin-trail 3.5s linear infinite;
}
.bt-inner {
    background: #fff;
    border-radius: 14.5px;
    position: relative;
    overflow: hidden;
    box-shadow:
        0 1px 2px rgba(15,23,42,.04),
        0 4px 8px rgba(15,23,42,.06),
        0 12px 24px rgba(15,23,42,.06),
        0 0 0 1px rgba(15,23,42,.03);
}

/* ── Stat cards ──────────────────────────────────────── */
.stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
@media (max-width: 768px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }

.stat-inner { padding: 18px 20px; position: relative; }
.stat-lbl { font-size: .68rem; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; font-weight: 500; margin-bottom: 6px; }
.stat-val { font-size: 2rem; font-weight: 700; letter-spacing: -.03em; line-height: 1; }
.stat-sub { font-size: .72rem; color: #cbd5e1; margin-top: 4px; }
.stat-dot { width: 7px; height: 7px; border-radius: 50%; position: absolute; top: 18px; right: 20px; }
.v-up    { color: #059669; }
.v-down  { color: #dc2626; }
.v-ssl   { color: #ea580c; }
.v-total { color: #0284c7; }
.bt-up   { animation-duration: 4s; animation-delay: 0s; }
.bt-down { animation-duration: 4s; animation-delay: -1s; }
.bt-ssl  { animation-duration: 4s; animation-delay: -2s; }
.bt-tot  { animation-duration: 4s; animation-delay: -3s; }

/* ── Main card ───────────────────────────────────────── */
.main-bt { animation-duration: 5s; }

/* ── Toolbar ─────────────────────────────────────────── */
.mon-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid #f1f5f9;
    flex-wrap: wrap;
    gap: 10px;
}
.filter-pills { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
.pill {
    font-size: .72rem; padding: 4px 12px; border-radius: 20px;
    border: 1px solid #e2e8f0; color: #64748b; cursor: pointer;
    background: #fff; font-weight: 450; transition: all .12s; letter-spacing: -.01em;
    user-select: none;
}
.pill:hover { border-color: #cbd5e1; color: #334155; background: #f8fafc; }
.pill.active { background: #0f172a; color: #fff; border-color: #0f172a; font-weight: 500; }
.pill.p-down { background: rgba(239,68,68,.05); color: #ef4444; border-color: rgba(239,68,68,.2); }
.pill.p-down.active { background: #ef4444; color: #fff; border-color: #ef4444; }

.btn-sync {
    font-size: .75rem; padding: 6px 14px; border-radius: 8px;
    border: 1px solid #e2e8f0; background: #fff; color: #334155;
    cursor: pointer; font-weight: 500; transition: all .12s;
    box-shadow: 0 1px 2px rgba(15,23,42,.06), 0 1px 4px rgba(15,23,42,.04);
}
.btn-sync:hover { background: #f8fafc; }
.btn-add {
    font-size: .75rem; padding: 6px 14px; border-radius: 8px; border: none;
    background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
    color: #fff; cursor: pointer; font-weight: 500; letter-spacing: -.01em;
    box-shadow: 0 1px 3px rgba(14,165,233,.3), 0 4px 12px rgba(99,102,241,.25);
    transition: opacity .12s;
}
.btn-add:hover { opacity: .88; }

/* ── Split layout ────────────────────────────────────── */
.split-layout { display: grid; grid-template-columns: 260px 1fr; min-height: 420px; }
@media (max-width: 768px) { .split-layout { grid-template-columns: 1fr; } }

/* ── Monitor list ────────────────────────────────────── */
.mon-list {
    border-right: 1px solid #f1f5f9;
    padding: 8px 0;
    overflow-y: auto;
    max-height: 520px;
}
.mon-list-hdr {
    font-size: .62rem; letter-spacing: .1em; text-transform: uppercase;
    color: #cbd5e1; padding: 4px 16px 8px; font-weight: 600;
    display: flex; justify-content: space-between; align-items: center;
}
.mon-list-search {
    font-size: .68rem; border: 1px solid #e2e8f0; border-radius: 6px;
    padding: 3px 8px; background: #f8fafc; color: #334155; outline: none;
    width: 120px; transition: border-color .12s;
}
.mon-list-search:focus { border-color: #38bdf8; }

.mon-row {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 16px; cursor: pointer; transition: background .1s;
    border-left: 2px solid transparent;
}
.mon-row:hover { background: #f8fafc; }
.mon-row.active { background: #f8fafc; border-left-color: #0f172a; }
.mon-row.active .mon-name { color: #0f172a; font-weight: 600; }

.s-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.s-up      { background: #10b981; box-shadow: 0 0 0 2px rgba(16,185,129,.2); }
.s-down    { background: #ef4444; box-shadow: 0 0 0 2px rgba(239,68,68,.2); }
.s-warn    { background: #f59e0b; box-shadow: 0 0 0 2px rgba(245,158,11,.2); }
.s-unknown { background: #cbd5e1; }

.mon-name {
    font-size: .8rem; color: #334155; flex: 1; min-width: 0;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 450;
}
.mon-tag {
    font-size: .58rem; padding: 1px 6px; border-radius: 4px;
    background: #f8fafc; color: #94a3b8; flex-shrink: 0;
    border: 1px solid #f1f5f9;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.8), 0 1px 2px rgba(15,23,42,.06);
}
.mon-empty { font-size: .8rem; color: #94a3b8; padding: 24px 16px; text-align: center; }

/* ── Detail panel ────────────────────────────────────── */
.detail-panel { padding: 20px 24px; }
.detail-empty { display: flex; align-items: center; justify-content: center; min-height: 300px; color: #cbd5e1; font-size: .85rem; }

.detail-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 18px; }
.detail-name { font-size: 1rem; font-weight: 600; color: #0f172a; letter-spacing: -.02em; margin-bottom: 2px; }
.detail-url  { font-size: .75rem; color: #38bdf8; font-weight: 400; text-decoration: none; }
.detail-url:hover { text-decoration: underline; }
.status-badge-up   { font-size: .7rem; padding: 3px 10px; border-radius: 6px; background: rgba(16,185,129,.08); color: #059669; border: 1px solid rgba(16,185,129,.2); font-weight: 500; }
.status-badge-down { font-size: .7rem; padding: 3px 10px; border-radius: 6px; background: rgba(239,68,68,.08); color: #dc2626; border: 1px solid rgba(239,68,68,.2); font-weight: 500; }
.status-badge-unknown { font-size: .7rem; padding: 3px 10px; border-radius: 6px; background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; font-weight: 500; }

.metrics-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 18px; }
.metric {
    background: #fff; border: 1px solid #f1f5f9; border-radius: 10px; padding: 12px 14px;
    box-shadow: 0 1px 2px rgba(15,23,42,.04), 0 3px 8px rgba(15,23,42,.05), 0 0 0 1px rgba(15,23,42,.02);
}
.metric-val { font-size: 1.15rem; font-weight: 600; color: #0f172a; letter-spacing: -.02em; margin-bottom: 2px; }
.metric-lbl { font-size: .62rem; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; font-weight: 500; }

.sec-lbl { font-size: .65rem; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; font-weight: 600; margin-bottom: 6px; }
.uptime-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }
.uptime-pct { font-size: .82rem; font-weight: 600; color: #10b981; }
.uptime-bg {
    background: #f1f5f9; border-radius: 20px; height: 4px; overflow: hidden; margin-bottom: 16px;
    box-shadow: inset 0 1px 2px rgba(15,23,42,.08);
}
.uptime-fill { height: 4px; border-radius: 20px; background: #10b981; transition: width .6s; }

.log-tbl { width: 100%; border-collapse: collapse; }
.log-tbl th { font-size: .62rem; letter-spacing: .08em; text-transform: uppercase; color: #cbd5e1; font-weight: 600; padding: 0 0 8px; text-align: left; }
.log-tbl td { font-size: .775rem; color: #475569; padding: 5px 0; border-bottom: 1px solid #f8fafc; }
.log-tbl tr:last-child td { border-bottom: none; }
.b-up   { font-size:.65rem; padding:1px 7px; border-radius:4px; background:rgba(16,185,129,.08); color:#059669; font-weight:600; }
.b-down { font-size:.65rem; padding:1px 7px; border-radius:4px; background:rgba(239,68,68,.08); color:#dc2626; font-weight:600; }
.b-unk  { font-size:.65rem; padding:1px 7px; border-radius:4px; background:#f1f5f9; color:#94a3b8; font-weight:600; }

.action-row { display: flex; gap: 7px; margin-top: 14px; padding-top: 14px; border-top: 1px solid #f1f5f9; flex-wrap: wrap; }
.a-btn {
    font-size: .73rem; padding: 5px 13px; border-radius: 7px;
    border: 1px solid #e2e8f0; background: #fff; color: #334155;
    cursor: pointer; font-weight: 500; transition: all .12s;
    box-shadow: 0 1px 2px rgba(15,23,42,.06), 0 1px 4px rgba(15,23,42,.04);
}
.a-btn:hover { background: #f8fafc; }
.a-btn.grad {
    background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
    color: #fff; border-color: transparent;
    box-shadow: 0 1px 3px rgba(14,165,233,.3), 0 4px 10px rgba(99,102,241,.2);
}
.a-btn.grad:hover { opacity: .88; }
.a-btn.danger { color: #ef4444; border-color: rgba(239,68,68,.2); }
.a-btn.danger:hover { background: rgba(239,68,68,.05); }

/* ── Modal ───────────────────────────────────────────── */
.modal-content { border-radius: 16px; border: none; box-shadow: 0 8px 40px rgba(0,0,0,.16); font-family: 'Inter', system-ui, sans-serif; }
.modal-header { border-bottom: 1px solid #f1f5f9; padding: 18px 24px; }
.modal-title  { font-size: 1rem; font-weight: 600; color: #0f172a; letter-spacing: -.01em; }
.modal-footer { border-top: 1px solid #f1f5f9; }
.modal-body label { font-size: .75rem; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: .06em; }
.modal-body .form-control, .modal-body .form-select {
    border-radius: 9px; border: 1.5px solid #e2e8f0; font-size: .875rem; background: #f8fafc;
}
.modal-body .form-control:focus, .modal-body .form-select:focus {
    border-color: #38bdf8; box-shadow: 0 0 0 3px rgba(56,189,248,.12);
}
.notif-lbl { font-size: .7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .1em; margin: 14px 0 10px; }
.btn-save {
    background: linear-gradient(135deg, #0ea5e9, #6366f1); color: #fff;
    border: none; border-radius: 9px; padding: 7px 24px; font-weight: 500;
    box-shadow: 0 2px 8px rgba(14,165,233,.3);
}
.btn-save:hover { color:#fff; opacity: .9; }
.btn-cancel { border-radius: 9px; }

/* ── Spinner overlay ─────────────────────────────────── */
.checking-overlay {
    position: absolute; inset: 0; background: rgba(255,255,255,.7);
    display: flex; align-items: center; justify-content: center;
    border-radius: 14.5px; z-index: 10; backdrop-filter: blur(2px);
    font-size: .8rem; color: #64748b; gap: 8px;
}
</style>

<!-- Page Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0 d-flex align-items-center gap-2">
                    <i class="bi bi-activity text-primary"></i> Domain Monitor
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php">Home</a></li>
                    <li class="breadcrumb-item active">Monitor</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content monitor-page">
<div class="container-fluid">

<!-- ── Stat Cards ────────────────────────────────────── -->
<div class="stat-grid" id="statsCards">
    <div class="bt bt-up">
        <div class="bt-inner stat-inner">
            <div class="stat-dot" style="background:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,.15)"></div>
            <div class="stat-lbl">Online</div>
            <div class="stat-val v-up" id="statUp">-</div>
            <div class="stat-sub">Sites up</div>
        </div>
    </div>
    <div class="bt bt-down">
        <div class="bt-inner stat-inner">
            <div class="stat-dot" style="background:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.15)"></div>
            <div class="stat-lbl">Down</div>
            <div class="stat-val v-down" id="statDown">-</div>
            <div class="stat-sub">Unreachable</div>
        </div>
    </div>
    <div class="bt bt-ssl">
        <div class="bt-inner stat-inner">
            <div class="stat-dot" style="background:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,.15)"></div>
            <div class="stat-lbl">SSL ≤ 30d</div>
            <div class="stat-val v-ssl" id="statSsl">-</div>
            <div class="stat-sub">Expiring soon</div>
        </div>
    </div>
    <div class="bt bt-tot">
        <div class="bt-inner stat-inner">
            <div class="stat-dot" style="background:#0284c7;box-shadow:0 0 0 3px rgba(2,132,199,.15)"></div>
            <div class="stat-lbl">Total</div>
            <div class="stat-val v-total" id="statTotal">-</div>
            <div class="stat-sub">Active monitors</div>
        </div>
    </div>
</div>

<!-- ── Main Card ─────────────────────────────────────── -->
<div class="bt main-bt">
    <div class="bt-inner" id="mainCard">

        <!-- Toolbar -->
        <div class="mon-toolbar">
            <div class="filter-pills">
                <span class="pill active" data-cat="" onclick="setCatFilter(this)">All</span>
                <span class="pill" data-cat="internal" onclick="setCatFilter(this)">Internal</span>
                <span class="pill" data-cat="client" onclick="setCatFilter(this)">Client</span>
                <span class="pill" data-cat="competitor" onclick="setCatFilter(this)">Competitor</span>
                <span class="pill" data-cat="payment_gateway" onclick="setCatFilter(this)">Payment</span>
                <span class="pill" data-cat="api_endpoint" onclick="setCatFilter(this)">API</span>
                <span class="pill" data-cat="third_party" onclick="setCatFilter(this)">3rd Party</span>
                <span class="pill p-down" data-status="down" onclick="setStatusFilter(this)">● Down <span id="downCount">0</span></span>
            </div>
            <div style="display:flex;gap:8px">
                <button class="btn-sync" onclick="syncWebsiteList()"><i class="bi bi-arrow-repeat me-1"></i>Sync</button>
                <button class="btn-add" onclick="openFormModal()"><i class="bi bi-plus-lg me-1"></i>Add Monitor</button>
            </div>
        </div>

        <!-- Split -->
        <div class="split-layout">

            <!-- List -->
            <div class="mon-list" id="monList">
                <div class="mon-list-hdr">
                    <span>Monitors</span>
                    <input class="mon-list-search" type="text" placeholder="Search…" id="monSearch" oninput="filterList()">
                </div>
                <div id="monListItems"><div class="mon-empty">Loading…</div></div>
            </div>

            <!-- Detail -->
            <div class="detail-panel" id="detailPanel">
                <div class="detail-empty">Select a monitor to view details</div>
            </div>

        </div>
    </div>
</div>

</div>
</div>

<!-- ── FORM MODAL ────────────────────────────────────── -->
<div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-earmark-plus me-2" style="color:#38bdf8"></i><span id="formModalTitle">Add Monitor</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label>Name</label>
                        <input type="text" class="form-control" id="inputName" placeholder="e.g. True Thai Cairns">
                    </div>
                    <div class="col-md-4">
                        <label>Category</label>
                        <select class="form-select" id="inputCategory">
                            <option value="internal">Internal</option>
                            <option value="client">Client</option>
                            <option value="competitor">Competitor</option>
                            <option value="third_party">Third Party</option>
                            <option value="payment_gateway">Payment Gateway</option>
                            <option value="api_endpoint">API Endpoint</option>
                            <option value="supplier">Supplier</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label>URL</label>
                        <input type="text" class="form-control" id="inputUrl" placeholder="https://www.example.com">
                    </div>
                    <div class="col-md-4">
                        <label>Check Interval (min)</label>
                        <input type="number" class="form-control" id="inputInterval" value="5" min="1" max="1440">
                    </div>
                    <div class="col-md-4 d-flex align-items-end pb-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="inputActive" checked>
                            <label class="form-check-label" for="inputActive" style="text-transform:none;font-size:.875rem">Active</label>
                        </div>
                    </div>
                </div>
                <div class="notif-lbl"><i class="bi bi-bell me-1"></i>Notifications</div>
                <div class="row g-3">
                    <div class="col-12">
                        <label>Email (comma-separated)</label>
                        <input type="text" class="form-control" id="inputEmail" placeholder="alert@company.com">
                    </div>
                    <div class="col-md-6">
                        <label>Line Notify Token</label>
                        <input type="text" class="form-control" id="inputLine" placeholder="Line Notify token">
                    </div>
                    <div class="col-md-6">
                        <label>Webhook URL</label>
                        <input type="text" class="form-control" id="inputWebhook" placeholder="https://hooks.example.com/...">
                    </div>
                </div>
                <input type="hidden" id="editID" value="">
                <input type="hidden" id="formAction" value="add">
            </div>
            <div class="modal-footer px-4">
                <button class="btn btn-light btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-save" onclick="formSave()"><i class="bi bi-check2 me-1"></i>Save</button>
            </div>
        </div>
    </div>
</div>

<!-- ── LOG MODAL ─────────────────────────────────────── -->
<div class="modal fade" id="logModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-journal-text me-2 text-secondary"></i>Full Check Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <table class="table table-sm table-hover">
                    <thead><tr><th>Checked At</th><th>Status</th><th>HTTP</th><th>Response</th><th>SSL Days</th><th>Type</th><th>Error</th></tr></thead>
                    <tbody id="logTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ── DOWNTIME MODAL ────────────────────────────────── -->
<div class="modal fade" id="downtimeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-graph-down me-2 text-warning"></i>Downtime Summary <small class="text-muted fw-normal">(Last 30 Days)</small></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span style="font-weight:600;font-size:.85rem">Uptime</span>
                        <span id="uptimePct" class="fw-bold fs-5" style="color:#10b981">-</span>
                    </div>
                    <div class="uptime-bg"><div class="uptime-fill" id="uptimeBar" style="width:0%"></div></div>
                </div>
                <table class="table table-sm table-bordered">
                    <thead class="table-light"><tr><th>Start</th><th>End</th><th>Duration (min)</th></tr></thead>
                    <tbody id="downtimeTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.js"></script>

<script>
const h = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
// Dispose any stale instance from previous page loads (SPA navigation)
['formModal','logModal','downtimeModal'].forEach(id => {
    const el = document.getElementById(id);
    const existing = bootstrap.Modal.getInstance(el);
    if (existing) existing.dispose();
});
const formModal     = new bootstrap.Modal(document.getElementById('formModal'));
const logModal      = new bootstrap.Modal(document.getElementById('logModal'));
const downtimeModal = new bootstrap.Modal(document.getElementById('downtimeModal'));

let allMonitors = [];
let selectedId  = null;
let catFilter   = '';
let statusFilter = '';

// ── Stats ──────────────────────────────────────────────
function loadStats() {
    $.post('assets/php/actionMonitor.php', { act: 'getStats' }, function(res) {
        $('#statUp').text(res.cnt_up ?? 0);
        $('#statDown').text(res.cnt_down ?? 0);
        $('#statSsl').text(res.cnt_ssl_warn ?? 0);
        $('#statTotal').text(res.cnt_total ?? 0);
        $('#downCount').text(res.cnt_down ?? 0);
    }, 'json');
}

// ── Load monitor list ──────────────────────────────────
function loadList() {
    $.post('assets/php/actionMonitor.php', { act: 'getList', category: catFilter, status: statusFilter }, function(res) {
        allMonitors = res.data || [];
        renderList();
        if (selectedId) {
            const still = allMonitors.find(m => m.id == selectedId);
            if (still) renderDetail(still); else clearDetail();
        }
    }, 'json');
}

function renderList() {
    const q = ($('#monSearch').val() || '').toLowerCase();
    const items = allMonitors.filter(m => !q || m.name.toLowerCase().includes(q) || m.url.toLowerCase().includes(q));
    if (!items.length) {
        $('#monListItems').html('<div class="mon-empty">No monitors found</div>');
        return;
    }
    const html = items.map(m => {
        const dotCls = m.last_status === 'up' ? 's-up' : m.last_status === 'down' ? 's-down' : 's-unknown';
        const active = m.id == selectedId ? 'active' : '';
        return `<div class="mon-row ${active}" onclick="selectMonitor(${m.id})" data-id="${m.id}">
            <span class="s-dot ${dotCls}"></span>
            <span class="mon-name">${h(m.name)}</span>
            <span class="mon-tag">${h(m.category)}</span>
        </div>`;
    }).join('');
    $('#monListItems').html(html);
}

function filterList() { renderList(); }

function selectMonitor(id) {
    selectedId = id;
    const m = allMonitors.find(x => x.id == id);
    if (m) renderDetail(m);
    renderList();
}

function clearDetail() {
    selectedId = null;
    $('#detailPanel').html('<div class="detail-empty">Select a monitor to view details</div>');
}

// ── Detail panel ───────────────────────────────────────
function renderDetail(m) {
    const dotCls   = m.last_status === 'up' ? 's-up' : m.last_status === 'down' ? 's-down' : 's-unknown';
    const badgeCls = m.last_status === 'up' ? 'status-badge-up' : m.last_status === 'down' ? 'status-badge-down' : 'status-badge-unknown';
    const statusTxt = m.last_status === 'up' ? '● Online' : m.last_status === 'down' ? '● Down' : '● Unknown';

    const sslVal = m.ssl_days_left != null
        ? (m.ssl_days_left < 0 ? '<span style="color:#ef4444">Expired</span>' : h(m.ssl_days_left) + 'd')
        : '—';
    const respVal = m.last_response_ms ? h(m.last_response_ms) + 'ms' : '—';
    const lastChk = m.last_checked_at || '—';

    $('#detailPanel').html(`
        <div class="detail-top">
            <div>
                <div class="detail-name">${h(m.name)}</div>
                <a href="${h(m.url)}" target="_blank" class="detail-url">${h(m.url)}</a>
            </div>
            <span class="${badgeCls}">${statusTxt}</span>
        </div>
        <div class="metrics-row">
            <div class="metric"><div class="metric-val">${respVal}</div><div class="metric-lbl">Response</div></div>
            <div class="metric"><div class="metric-val">${sslVal}</div><div class="metric-lbl">SSL Left</div></div>
            <div class="metric"><div class="metric-val">${h(m.check_interval)} min</div><div class="metric-lbl">Interval</div></div>
        </div>
        <div class="sec-lbl">
            <div class="uptime-row"><span>30-Day Uptime</span><span class="uptime-pct" id="dp-uptime">—</span></div>
        </div>
        <div class="uptime-bg"><div class="uptime-fill" id="dp-uptime-bar" style="width:0%"></div></div>
        <div class="sec-lbl">Recent Checks</div>
        <table class="log-tbl" id="dp-log-tbl">
            <thead><tr><th>Time</th><th>Status</th><th>HTTP</th><th>Response</th><th>SSL</th></tr></thead>
            <tbody id="dp-log-body"><tr><td colspan="5" style="color:#cbd5e1;font-size:.75rem;padding:8px 0">Loading…</td></tr></tbody>
        </table>
        <div class="action-row">
            <button class="a-btn grad" onclick="manualCheck(${m.id})"><i class="bi bi-arrow-clockwise me-1"></i>Check Now</button>
            <button class="a-btn grad" onclick="setEdit(${m.id})"><i class="bi bi-pencil-square me-1"></i>Edit</button>
            <button class="a-btn grad" onclick="openFullLogs(${m.id})"><i class="bi bi-journal-text me-1"></i>Full Logs</button>
            <button class="a-btn grad" onclick="viewDowntime(${m.id})"><i class="bi bi-graph-down me-1"></i>Downtime</button>
            <button class="a-btn danger" style="margin-left:auto" onclick="setDel(${m.id})"><i class="bi bi-x-square me-1"></i>Delete</button>
        </div>
        <div style="font-size:.65rem;color:#cbd5e1;margin-top:10px">Last checked: ${h(lastChk)}</div>
    `);

    // load inline uptime + recent logs
    loadDetailExtras(m.id);
}

function loadDetailExtras(id) {
    $.post('assets/php/actionMonitor.php', { act: 'getDowntime', id }, function(res) {
        const pct = res.uptime_pct;
        $('#dp-uptime').text(pct !== null ? pct + '%' : 'No data');
        $('#dp-uptime-bar').css('width', pct !== null ? pct + '%' : '0%');
    }, 'json');

    $.post('assets/php/actionMonitor.php', { act: 'getLogs', id }, function(res) {
        const rows = (res.data || []).slice(0, 5).map(r => {
            const bc = r.status === 'up' ? 'b-up' : r.status === 'down' ? 'b-down' : 'b-unk';
            return `<tr>
                <td>${h(r.checked_at ? r.checked_at.slice(11,19) : '—')}</td>
                <td><span class="${bc}">${h(r.status)}</span></td>
                <td>${h(r.http_code ?? '—')}</td>
                <td>${r.response_ms != null ? h(r.response_ms)+'ms' : '—'}</td>
                <td>${h(r.ssl_days_left ?? '—')}</td>
            </tr>`;
        }).join('');
        $('#dp-log-body').html(rows || '<tr><td colspan="5" style="color:#cbd5e1;font-size:.75rem;padding:8px 0">No logs yet</td></tr>');
    }, 'json');
}

// ── Filters ────────────────────────────────────────────
function setCatFilter(el) {
    catFilter = el.dataset.cat;
    statusFilter = '';
    $('.pill:not(.p-down)').removeClass('active');
    $('.p-down').removeClass('active');
    $(el).addClass('active');
    loadList();
}

function setStatusFilter(el) {
    if ($(el).hasClass('active')) {
        statusFilter = '';
        $(el).removeClass('active');
    } else {
        statusFilter = el.dataset.status;
        $(el).addClass('active');
    }
    loadList();
}

// ── Form modal ─────────────────────────────────────────
function openFormModal() {
    $('#inputName').val(''); $('#inputUrl').val('');
    $('#inputCategory').val('client'); $('#inputInterval').val(5);
    $('#inputActive').prop('checked', true);
    $('#inputEmail').val(''); $('#inputLine').val(''); $('#inputWebhook').val('');
    $('#editID').val(''); $('#formAction').val('add');
    $('#formModalTitle').text('Add Monitor');
    formModal.show();
}

function closeFormModal() { formModal.hide(); }

function formSave() {
    $.post('assets/php/actionMonitor.php', {
        act: 'save', formAction: $('#formAction').val(),
        inputName: $('#inputName').val(), inputUrl: $('#inputUrl').val(),
        inputCategory: $('#inputCategory').val(), inputInterval: $('#inputInterval').val(),
        inputActive: $('#inputActive').prop('checked') ? 1 : 0,
        inputEmail: $('#inputEmail').val(), inputLine: $('#inputLine').val(),
        inputWebhook: $('#inputWebhook').val(), editID: $('#editID').val(),
    }, function() { closeFormModal(); refresh(); }, 'json');
}

function setEdit(id) {
    $.post('assets/php/actionMonitor.php', { act: 'loadUpdate', id }, function(res) {
        $('#inputName').val(res.name); $('#inputUrl').val(res.url);
        $('#inputCategory').val(res.category); $('#inputInterval').val(res.check_interval);
        $('#inputActive').prop('checked', res.is_active == 1);
        $('#inputEmail').val(res.notify_email); $('#inputLine').val(res.notify_line);
        $('#inputWebhook').val(res.notify_webhook);
        $('#editID').val(res.id); $('#formAction').val('edit');
        $('#formModalTitle').text('Edit Monitor');
        formModal.show();
    }, 'json');
}

function setDel(id) {
    if (!confirm('Delete this monitor?')) return;
    $.post('assets/php/actionMonitor.php', { act: 'setDelete', id }, function() {
        if (selectedId == id) clearDetail();
        refresh();
    }, 'json');
}

// ── Actions ────────────────────────────────────────────
function manualCheck(id) {
    const orig = $('#detailPanel').find('.action-row').first();
    $('#detailPanel').css('position','relative');
    $('#detailPanel').append('<div class="checking-overlay" id="checkOverlay"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div> Checking…</div>');
    $.post('assets/php/actionMonitor.php', { act: 'manualCheck', id }, function(res) {
        $('#checkOverlay').remove();
        refresh();
    }, 'json');
}

function openFullLogs(id) {
    $.post('assets/php/actionMonitor.php', { act: 'getLogs', id }, function(res) {
        const rows = (res.data || []).map(r => `
            <tr>
                <td>${h(r.checked_at)}</td>
                <td><span class="${r.status==='up'?'b-up':r.status==='down'?'b-down':'b-unk'}">${h(r.status)}</span></td>
                <td>${h(r.http_code ?? '—')}</td>
                <td>${r.response_ms != null ? h(r.response_ms)+'ms' : '—'}</td>
                <td>${h(r.ssl_days_left ?? '—')}</td>
                <td><span class="badge bg-secondary">${h(r.check_type)}</span></td>
                <td><small class="text-danger">${h(r.error_msg)}</small></td>
            </tr>`).join('');
        $('#logTableBody').html(rows || '<tr><td colspan="7" class="text-center text-muted py-3">No logs yet</td></tr>');
        logModal.show();
    }, 'json');
}

function viewDowntime(id) {
    $.post('assets/php/actionMonitor.php', { act: 'getDowntime', id }, function(res) {
        const pct = res.uptime_pct;
        $('#uptimePct').text(pct !== null ? pct + '%' : 'No data');
        $('#uptimeBar').css('width', pct !== null ? pct + '%' : '0%');
        const rows = (res.incidents || []).map(inc => `
            <tr>
                <td>${h(inc.start)}</td>
                <td>${inc.end ? h(inc.end) : '<span class="badge bg-danger">Still down</span>'}</td>
                <td class="text-center">${h(inc.duration_min)} min</td>
            </tr>`).join('');
        $('#downtimeTableBody').html(rows || '<tr><td colspan="3" class="text-center text-muted py-3">No downtime incidents</td></tr>');
        downtimeModal.show();
    }, 'json');
}

function syncWebsiteList() {
    $.post('assets/php/actionMonitor.php', { act: 'syncWebsiteList' }, function(res) {
        alert('Synced: ' + res.inserted + ' new monitors added.');
        refresh();
    }, 'json');
}

function refresh() { loadStats(); loadList(); }

function resetForm() {}

// ── Init ───────────────────────────────────────────────
$(() => { refresh(); });
</script>
