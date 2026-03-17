<?php
global $db, $activeMenu;
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0"><i class="bi bi-speedometer2"></i> Report Overview</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item"><a href="main.php?p=report">Report</a></li>
                    <li class="breadcrumb-item active">Overview</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container-fluid">

        <!-- ========================================
             SECTION 1 : ACTIVE SUBSCRIPTIONS
             ======================================== -->
        <div class="card mb-4 ov-section-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title mb-0">
                    <i class="bi bi-people-fill mr-2 text-primary"></i>
                    Active Subscriptions
                </h3>
                <span class="badge badge-light text-muted" id="activeFilename" style="font-size:11px;font-weight:400;"></span>
            </div>
            <div class="card-body">

                <!-- Active summary card -->
                <div class="row mb-3">
                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="ov-stat-card ov-stat-blue">
                            <div class="ov-stat-icon"><i class="bi bi-people"></i></div>
                            <div class="ov-stat-value" id="activeTotalCount">—</div>
                            <div class="ov-stat-label">Total Active</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-8 col-lg-9 d-flex align-items-center" id="activeCountryBadges"></div>
                </div>

                <!-- Active charts -->
                <div class="row" id="activeChartsRow" style="display:none!important;">
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-header py-2"><h6 class="mb-0"><i class="bi bi-bar-chart mr-1"></i> By Country</h6></div>
                            <div class="card-body" style="height:260px;"><canvas id="activeCountryChart"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-header py-2"><h6 class="mb-0"><i class="bi bi-pie-chart mr-1"></i> By Customer Type</h6></div>
                            <div class="card-body" style="height:260px;"><canvas id="activeTypeChart"></canvas></div>
                        </div>
                    </div>
                </div>

                <!-- Active table -->
                <div class="row mt-3">
                    <div class="col-lg-6">
                        <p class="ov-table-label">Active by Country</p>
                        <div class="table-responsive">
                            <table class="ov-table">
                                <thead><tr><th>Country</th><th>Active</th><th>Share</th></tr></thead>
                                <tbody id="activeCountryTbody"><tr><td colspan="3" class="text-center text-muted py-3">Loading…</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <p class="ov-table-label">Active by Customer Type</p>
                        <div class="table-responsive">
                            <table class="ov-table">
                                <thead><tr><th>Type</th><th>Active</th><th>Share</th></tr></thead>
                                <tbody id="activeTypeTbody"><tr><td colspan="3" class="text-center text-muted py-3">Loading…</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div><!-- /card-body -->
        </div><!-- /active card -->

        <!-- ========================================
             SECTION 2 : SIGNUP & UNSUBSCRIBE
             ======================================== -->
        <div class="card mb-4 ov-section-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="bi bi-person-plus-fill mr-2 text-success"></i>
                    Signup &amp; Unsubscribe
                </h3>
            </div>
            <div class="card-body">

                <!-- Filter bar -->
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3 ov-filter-bar">
                    <label class="mb-0 font-weight-bold" style="min-width:55px;">Period:</label>
                    <select id="suPeriod" class="form-control form-control-sm" style="width:110px;" onchange="onPeriodChange()">
                        <option value="week" selected>Week</option>
                        <option value="month">Month</option>
                    </select>
                    <div id="suDateWrap">
                        <input type="date" id="suDate" class="form-control form-control-sm" style="width:165px;" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div id="suMonthWrap" style="display:none;">
                        <input type="month" id="suMonth" class="form-control form-control-sm" style="width:165px;" value="<?php echo date('Y-m'); ?>">
                    </div>
                    <button class="btn btn-primary btn-sm px-3" onclick="loadSignupUnsub()">
                        <i class="bi bi-search mr-1"></i> Generate
                    </button>
                    <span class="text-muted ml-2" id="suDateRangeLabel" style="font-size:13px;"></span>
                </div>

                <!-- Loading -->
                <div id="suLoading" class="text-center py-4" style="display:none;">
                    <div class="spinner-border text-primary" style="width:2.5rem;height:2.5rem;" role="status"></div>
                    <p class="mt-2 text-muted mb-0">Fetching data…</p>
                </div>

                <!-- Content (hidden until loaded) -->
                <div id="suContent" style="display:none;">

                    <!-- Summary cards -->
                    <div class="row mb-3">
                        <div class="col-6 col-lg-3">
                            <div class="ov-stat-card ov-stat-green">
                                <div class="ov-stat-icon"><i class="bi bi-person-plus"></i></div>
                                <div class="ov-stat-value" id="suSignupTotal">0</div>
                                <div class="ov-stat-label">New Signups</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="ov-stat-card ov-stat-red">
                                <div class="ov-stat-icon"><i class="bi bi-person-dash"></i></div>
                                <div class="ov-stat-value" id="suUnsubTotal">0</div>
                                <div class="ov-stat-label">Unsubscribes</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="ov-stat-card" id="suNetCard">
                                <div class="ov-stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
                                <div class="ov-stat-value" id="suNet">0</div>
                                <div class="ov-stat-label">Net Change</div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts -->
                    <div class="row mb-3">
                        <div class="col-lg-7">
                            <div class="card shadow-sm">
                                <div class="card-header py-2"><h6 class="mb-0"><i class="bi bi-bar-chart mr-1"></i> Signup vs Unsubscribe by Country</h6></div>
                                <div class="card-body" style="height:280px;"><canvas id="suCountryChart"></canvas></div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="card shadow-sm">
                                <div class="card-header py-2"><h6 class="mb-0"><i class="bi bi-pie-chart mr-1"></i> Signup by Type</h6></div>
                                <div class="card-body" style="height:280px;"><canvas id="suTypeChart"></canvas></div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Tables -->
                    <div class="row">
                        <div class="col-lg-6">
                            <p class="ov-table-label">Signup by Country</p>
                            <div class="table-responsive">
                                <table class="ov-table">
                                    <thead><tr><th>Country</th><th>Signup</th><th>Unsub</th><th>Net</th></tr></thead>
                                    <tbody id="suCountryTbody"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <p class="ov-table-label">Signup by Customer Type</p>
                            <div class="table-responsive">
                                <table class="ov-table">
                                    <thead><tr><th>Type</th><th>Signup</th><th>Unsub</th><th>Net</th></tr></thead>
                                    <tbody id="suTypeTbody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Signup detail list -->
                    <div class="mt-3">
                        <p class="ov-table-label">Signup Detail <small class="text-muted font-weight-normal" id="suDetailCount"></small></p>
                        <div class="table-responsive">
                            <table class="ov-table">
                                <thead><tr><th>#</th><th>Shop Name</th><th>Country</th><th>Type</th><th>Product</th></tr></thead>
                                <tbody id="suDetailTbody"></tbody>
                            </table>
                        </div>
                    </div>

                </div><!-- /suContent -->

                <!-- Placeholder -->
                <div id="suPlaceholder" class="text-center text-muted py-5">
                    <i class="bi bi-bar-chart-line" style="font-size:3rem;opacity:0.25;"></i>
                    <p class="mt-2 mb-0">Select a period and click <b>Generate</b></p>
                </div>

            </div><!-- /card-body -->
        </div><!-- /signup-unsub card -->

    </div>
</div>

<!-- ===== Styles ===== -->
<style>
.ov-section-card { border-radius:10px; border:1px solid #e2e8f0; }
.ov-section-card .card-header { background:#f8fafc; border-bottom:1px solid #e2e8f0; }

.ov-stat-card { border-radius:12px; padding:16px 20px; color:#fff; position:relative; overflow:hidden; margin-bottom:16px; }
.ov-stat-card .ov-stat-icon { position:absolute; top:10px; right:14px; font-size:2.8rem; opacity:0.15; }
.ov-stat-card .ov-stat-value { font-size:2rem; font-weight:700; line-height:1; }
.ov-stat-card .ov-stat-label { font-size:0.78rem; opacity:0.85; margin-top:4px; }
.ov-stat-blue   { background:linear-gradient(135deg,#0361D1,#00BCF4); }
.ov-stat-green  { background:linear-gradient(135deg,#10b981,#34d399); }
.ov-stat-red    { background:linear-gradient(135deg,#ef4444,#f87171); }
.ov-stat-warn   { background:linear-gradient(135deg,#f59e0b,#fbbf24); }
.ov-stat-gray   { background:linear-gradient(135deg,#64748b,#94a3b8); }

.ov-country-badge { display:inline-flex; flex-direction:column; align-items:center; background:#f0f4ff; border:1px solid #c7d8f8; border-radius:8px; padding:8px 14px; margin:4px; min-width:72px; }
.ov-country-badge .badge-code { font-weight:700; font-size:1rem; color:#0361D1; }
.ov-country-badge .badge-num  { font-size:0.85rem; color:#334155; font-weight:600; }

.ov-table { width:100%; border-collapse:collapse; margin-bottom:0; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; }
.ov-table th { background:#1e293b; color:#fff; padding:9px 14px; font-size:11.5px; text-transform:uppercase; letter-spacing:0.4px; text-align:center; border:1px solid #475569; }
.ov-table td { padding:8px 14px; font-size:13px; border:1px solid #e2e8f0; }
.ov-table tbody tr:hover td { background:#f0f7ff; }
.ov-table tbody tr:last-child td { background:#f1f5f9; font-weight:600; }
.ov-table-label { font-size:13.5px; font-weight:600; margin:0 0 6px; color:#1e293b; }

.ov-filter-bar { gap:8px; }
.ov-filter-bar .gap-2 { gap:8px; }
</style>

<!-- ===== Scripts ===== -->
<script>
let chartActiveCountry = null;
let chartActiveType    = null;
let chartSuCountry     = null;
let chartSuType        = null;

const COLORS = ['#0361D1','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16','#f97316','#6366f1'];

// ─── Period toggle ───────────────────────────────────────────
function onPeriodChange() {
    const p = document.getElementById('suPeriod').value;
    document.getElementById('suDateWrap').style.display  = (p === 'week')  ? '' : 'none';
    document.getElementById('suMonthWrap').style.display = (p === 'month') ? '' : 'none';
}

// ─── Format date helpers ─────────────────────────────────────
function fmtDate(str) {
    if (!str) return str;
    const d = new Date(str.replace(' ', 'T'));
    return d.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
}

// ─── ACTIVE: load on page ready ──────────────────────────────
function loadActive() {
    $.getJSON('api/monday/reportOverview/getOverviewData.php?type=active')
        .done(function(data) {
            if (data.error) { console.warn(data.error); return; }
            renderActive(data);
        })
        .fail(function(xhr) { console.error('loadActive fail', xhr.responseText); });
}

function renderActive(data) {
    // summary card
    document.getElementById('activeTotalCount').textContent = Number(data.total).toLocaleString();
    document.getElementById('activeFilename').textContent   = data.filename ? '📄 ' + data.filename : '';

    // country badges
    const badges = document.getElementById('activeCountryBadges');
    badges.innerHTML = '';
    for (const [code, cnt] of Object.entries(data.byCountry)) {
        badges.innerHTML += `<div class="ov-country-badge"><span class="badge-code">${code}</span><span class="badge-num">${cnt}</span></div>`;
    }

    // charts
    renderActiveCountryChart(data.byCountry, data.total);
    renderActiveTypeChart(data.byType);

    // tables
    buildSimpleTable('activeCountryTbody', data.byCountry, data.total);
    buildSimpleTable('activeTypeTbody',    data.byType,    data.total);
}

function renderActiveCountryChart(byCountry, total) {
    const ctx = document.getElementById('activeCountryChart').getContext('2d');
    if (chartActiveCountry) chartActiveCountry.destroy();
    const labels = Object.keys(byCountry);
    const vals   = Object.values(byCountry);
    chartActiveCountry = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{ label:'Active', data:vals, backgroundColor:'rgba(3,97,209,0.75)', borderRadius:4 }]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{display:false} },
            scales:{ y:{beginAtZero:true, grid:{color:'rgba(0,0,0,0.05)'}}, x:{grid:{display:false}} }
        }
    });
}

function renderActiveTypeChart(byType) {
    const ctx = document.getElementById('activeTypeChart').getContext('2d');
    if (chartActiveType) chartActiveType.destroy();
    const labels = Object.keys(byType).filter(k => k !== 'Unknown');
    const vals   = labels.map(k => byType[k]);
    if (labels.length === 0) return;
    chartActiveType = new Chart(ctx, {
        type: 'doughnut',
        data: { labels, datasets:[{ data:vals, backgroundColor:COLORS.slice(0,labels.length), borderWidth:2, borderColor:'#fff' }] },
        options: { responsive:true, maintainAspectRatio:false, cutout:'55%', plugins:{ legend:{ position:'right', labels:{usePointStyle:true,padding:10,font:{size:11}} } } }
    });
}

function buildSimpleTable(tbodyId, obj, total) {
    const tbody = document.getElementById(tbodyId);
    let html = '';
    let sum = 0;
    for (const [label, cnt] of Object.entries(obj)) {
        const pct = total > 0 ? ((cnt/total)*100).toFixed(1) : 0;
        html += `<tr><td>${label}</td><td class="text-center font-weight-bold">${cnt}</td><td class="text-center text-muted">${pct}%</td></tr>`;
        sum += cnt;
    }
    html += `<tr><td><b>Total</b></td><td class="text-center"><b>${sum}</b></td><td class="text-center"><b>100%</b></td></tr>`;
    tbody.innerHTML = html;
}

// ─── SIGNUP / UNSUB ──────────────────────────────────────────
function loadSignupUnsub() {
    const period = document.getElementById('suPeriod').value;
    const date   = period === 'week'
                    ? document.getElementById('suDate').value
                    : document.getElementById('suMonth').value;

    if (!date) return;

    document.getElementById('suPlaceholder').style.display = 'none';
    document.getElementById('suContent').style.display     = 'none';
    document.getElementById('suLoading').style.display     = 'block';

    $.getJSON('api/monday/reportOverview/getOverviewData.php', { type:'signupunsub', period, date })
        .done(function(data) {
            document.getElementById('suLoading').style.display = 'none';
            document.getElementById('suContent').style.display = 'block';
            renderSignupUnsub(data);
        })
        .fail(function(xhr) {
            document.getElementById('suLoading').style.display = 'none';
            document.getElementById('suPlaceholder').style.display = 'block';
            document.getElementById('suPlaceholder').innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle mr-1"></i> Failed to load: ' + (xhr.responseJSON?.error || xhr.statusText) + '</div>';
        });
}

function renderSignupUnsub(data) {
    // Date range label
    document.getElementById('suDateRangeLabel').textContent =
        fmtDate(data.period.start) + ' → ' + fmtDate(data.period.end);

    // Summary cards
    const ns  = data.signup.total;
    const nu  = data.unsub.total;
    const net = ns - nu;
    document.getElementById('suSignupTotal').textContent = '+' + ns;
    document.getElementById('suUnsubTotal').textContent  = '-' + nu;
    document.getElementById('suNet').textContent         = (net >= 0 ? '+' : '') + net;

    const netCard = document.getElementById('suNetCard');
    netCard.className = 'ov-stat-card ' + (net >= 0 ? 'ov-stat-warn' : 'ov-stat-gray');

    // Country bar chart
    renderSuCountryChart(data.signup.byCountry, data.unsub.byCountry);

    // Type doughnut
    renderSuTypeChart(data.signup.byType);

    // Country table
    buildSuCountryTable(data.signup.byCountry, data.unsub.byCountry);

    // Type table
    buildSuTypeTable(data.signup.byType, data.unsub.byType);

    // Detail table
    const detail = data.signup.detail || [];
    document.getElementById('suDetailCount').textContent = '(' + detail.length + ' records)';
    let dHtml = '';
    detail.forEach((r, i) => {
        dHtml += `<tr>
            <td class="text-center text-muted">${i+1}</td>
            <td>${r.shop || '—'}</td>
            <td class="text-center">${r.country}</td>
            <td>${r.type}</td>
            <td style="font-size:12px;">${r.product || '—'}</td>
        </tr>`;
    });
    if (!dHtml) dHtml = '<tr><td colspan="5" class="text-center text-muted py-3">No signup in this period</td></tr>';
    document.getElementById('suDetailTbody').innerHTML = dHtml;
}

function renderSuCountryChart(signupByCountry, unsubByCountry) {
    const ctx = document.getElementById('suCountryChart').getContext('2d');
    if (chartSuCountry) chartSuCountry.destroy();

    const allCountries = [...new Set([...Object.keys(signupByCountry), ...Object.keys(unsubByCountry)])].sort();
    const signupVals   = allCountries.map(c => signupByCountry[c] || 0);
    const unsubVals    = allCountries.map(c => unsubByCountry[c]  || 0);

    chartSuCountry = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: allCountries,
            datasets: [
                { label:'Signup', data:signupVals, backgroundColor:'rgba(16,185,129,0.75)', borderRadius:4 },
                { label:'Unsub',  data:unsubVals,  backgroundColor:'rgba(239,68,68,0.75)',  borderRadius:4 }
            ]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ position:'top', labels:{usePointStyle:true,padding:12,font:{size:11}} } },
            scales:{ y:{beginAtZero:true, grid:{color:'rgba(0,0,0,0.05)'}}, x:{grid:{display:false}} }
        }
    });
}

function renderSuTypeChart(signupByType) {
    const ctx = document.getElementById('suTypeChart').getContext('2d');
    if (chartSuType) chartSuType.destroy();
    const labels = Object.keys(signupByType).filter(k => k !== 'Unknown');
    const vals   = labels.map(k => signupByType[k]);
    if (labels.length === 0) return;
    chartSuType = new Chart(ctx, {
        type: 'doughnut',
        data: { labels, datasets:[{ data:vals, backgroundColor:COLORS.slice(0,labels.length), borderWidth:2, borderColor:'#fff' }] },
        options: { responsive:true, maintainAspectRatio:false, cutout:'55%', plugins:{ legend:{ position:'right', labels:{usePointStyle:true,padding:10,font:{size:11}} } } }
    });
}

function buildSuCountryTable(signupByCountry, unsubByCountry) {
    const all = [...new Set([...Object.keys(signupByCountry), ...Object.keys(unsubByCountry)])].sort();
    let html = '', ts = 0, tu = 0;
    all.forEach(c => {
        const s = signupByCountry[c] || 0;
        const u = unsubByCountry[c]  || 0;
        const n = s - u;
        html += `<tr>
            <td><b>${c}</b></td>
            <td class="text-center" style="color:#10b981;font-weight:600;">${s > 0 ? '+'+s : 0}</td>
            <td class="text-center" style="color:#ef4444;font-weight:600;">${u > 0 ? '-'+u : 0}</td>
            <td class="text-center" style="font-weight:600;color:${n>=0?'#f59e0b':'#64748b'}">${n>=0?'+'+n:n}</td>
        </tr>`;
        ts += s; tu += u;
    });
    const tn = ts - tu;
    html += `<tr>
        <td><b>Total</b></td>
        <td class="text-center" style="color:#10b981;font-weight:700;">${ts > 0 ? '+'+ts : 0}</td>
        <td class="text-center" style="color:#ef4444;font-weight:700;">${tu > 0 ? '-'+tu : 0}</td>
        <td class="text-center font-weight-700" style="color:${tn>=0?'#f59e0b':'#64748b'};font-weight:700;">${tn>=0?'+'+tn:tn}</td>
    </tr>`;
    document.getElementById('suCountryTbody').innerHTML = html || '<tr><td colspan="4" class="text-center text-muted py-3">No data</td></tr>';
}

function buildSuTypeTable(signupByType, unsubByType) {
    const all = [...new Set([...Object.keys(signupByType), ...Object.keys(unsubByType)])].sort();
    let html = '', ts = 0, tu = 0;
    all.forEach(t => {
        const s = signupByType[t] || 0;
        const u = unsubByType[t]  || 0;
        const n = s - u;
        html += `<tr>
            <td>${t}</td>
            <td class="text-center" style="color:#10b981;font-weight:600;">${s > 0 ? '+'+s : 0}</td>
            <td class="text-center" style="color:#ef4444;font-weight:600;">${u > 0 ? '-'+u : 0}</td>
            <td class="text-center" style="font-weight:600;color:${n>=0?'#f59e0b':'#64748b'}">${n>=0?'+'+n:n}</td>
        </tr>`;
        ts += s; tu += u;
    });
    const tn = ts - tu;
    html += `<tr>
        <td><b>Total</b></td>
        <td class="text-center" style="color:#10b981;font-weight:700;">${ts > 0 ? '+'+ts : 0}</td>
        <td class="text-center" style="color:#ef4444;font-weight:700;">${tu > 0 ? '-'+tu : 0}</td>
        <td class="text-center font-weight-700" style="color:${tn>=0?'#f59e0b':'#64748b'};font-weight:700;">${tn>=0?'+'+tn:tn}</td>
    </tr>`;
    document.getElementById('suTypeTbody').innerHTML = html || '<tr><td colspan="4" class="text-center text-muted py-3">No data</td></tr>';
}

// ─── Init ────────────────────────────────────────────────────
$(function() {
    loadActive();
    // Auto-load signup/unsub for current week
    loadSignupUnsub();
});
</script>
