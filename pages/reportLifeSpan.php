<?php
global $db, $activeMenu;
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0"><i class="bi bi-hourglass-split"></i> Report Life Span</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item"><a href="main.php?p=report">Report</a></li>
                    <li class="breadcrumb-item active">Life Span</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container-fluid">

        <!-- Load Data -->
        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="row align-items-end g-2">
                    <div class="col-auto">
                        <label class="form-label mb-1 small fw-bold">Country</label>
                        <select id="filterCountry" class="form-control form-control-sm" style="min-width:140px;">
                            <option value="ALL">All Countries</option>
                            <option value="AU">AU</option>
                            <option value="TH">TH</option>
                            <option value="CA">CA</option>
                            <option value="UK">UK</option>
                            <option value="US">US</option>
                            <option value="NZ">NZ</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="button" id="btnLoad" class="btn btn-primary btn-sm">
                            <i class="bi bi-search mr-1"></i> Load Data
                        </button>
                    </div>
                    <div class="col-auto" id="loadingWrap" style="display:none;">
                        <span class="text-muted small"><i class="bi bi-arrow-repeat spin mr-1"></i> Loading...</span>
                    </div>
                    <div class="col-auto ml-auto">
                        <label class="form-label mb-1 small fw-bold">Search</label>
                        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search name, group, status..." style="min-width:220px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Condition Filters -->
        <div class="card mb-3" id="conditionFilterCard" style="display:none;">
            <div class="card-header py-2">
                <h3 class="card-title mb-0"><i class="bi bi-funnel mr-1"></i> Condition Filters</h3>
            </div>
            <div class="card-body py-3">
                <div class="row align-items-end g-2">
                    <div class="col-auto">
                        <label class="form-label mb-1 small fw-bold">Life Span</label>
                        <select id="cfLifeSpan" class="form-control form-control-sm" style="min-width:130px;">
                            <option value="ALL">All Data</option>
                            <option value="7D">7 Day</option>
                            <option value="30D">30 Day</option>
                            <option value="60D">60 Day</option>
                            <option value="90D">90 Day</option>
                            <option value="3M">Over 3M</option>
                            <option value="6M">Over 6M</option>
                            <option value="9M">Over 9M</option>
                            <option value="1Y">Over 1Y</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 small fw-bold">Country</label>
                        <select id="cfCountry" class="form-control form-control-sm" style="min-width:120px;">
                            <option value="ALL">All</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 small fw-bold">System / Marketing</label>
                        <select id="cfSysMkt" class="form-control form-control-sm" style="min-width:140px;">
                            <option value="ALL">All</option>
                            <option value="system">System</option>
                            <option value="marketing">Marketing</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 small fw-bold">Group</label>
                        <select id="cfGroup" class="form-control form-control-sm" style="min-width:140px;">
                            <option value="ALL">All</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 small fw-bold">Type Shop</label>
                        <select id="cfTypeShop" class="form-control form-control-sm" style="min-width:140px;">
                            <option value="ALL">All</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 small fw-bold">Status</label>
                        <select id="cfStatus" class="form-control form-control-sm" style="min-width:120px;">
                            <option value="ALL">All</option>
                            <option value="Live">Live</option>
                            <option value="Cancel">Cancel</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 small fw-bold">Active Subscription</label>
                        <select id="cfActiveSubs" class="form-control form-control-sm" style="max-width:180px;">
                            <option value="ALL">All</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 small fw-bold">Marketing Phase</label>
                        <select id="cfPhase" class="form-control form-control-sm" style="min-width:140px;">
                            <option value="ALL">All</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="button" id="btnResetFilter" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle mr-1"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0"><i class="bi bi-table mr-2"></i> Life Span Data</h3>
                <button class="btn btn-outline-success btn-sm ml-auto" id="btnExport" style="display:none;">
                    <i class="bi bi-file-earmark-excel mr-1"></i> Export CSV
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:70vh; overflow-y:auto;">
                    <table class="table table-bordered table-striped table-hover mb-0" id="lifeSpanTable">
                        <thead class="bg-light" style="position:sticky; top:0; z-index:2;">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Type Shop</th>
                                <th>Group</th>
                                <th>Status</th>
                                <th>System Live Date</th>
                                <th>System Cancel Date</th>
                                <th>Marketing Live Date</th>
                                <th>Marketing Cancel Date</th>
                                <th>Marketing Phase</th>
                                <th>Active Subscriptions</th>
                                <th>Life Span</th>
                            </tr>
                        </thead>
                        <tbody id="lifeSpanTbody">
                            <tr><td colspan="12" class="text-center text-muted py-4">Click <strong>Load Data</strong> to fetch life span data.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="card mt-3" id="summarySection" style="display:none;">
            <div class="card-header py-2">
                <h3 class="card-title mb-0"><i class="bi bi-bar-chart mr-1"></i> Summary (after filter)</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Total Shops -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card border-left-primary shadow-sm h-100">
                            <div class="card-body py-2 px-3">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Shops</div>
                                <div class="h5 mb-0 font-weight-bold" id="sumTotal">0</div>
                            </div>
                        </div>
                    </div>
                    <!-- Total Live -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card border-left-success shadow-sm h-100">
                            <div class="card-body py-2 px-3">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Live</div>
                                <div class="h5 mb-0 font-weight-bold" id="sumLive">0</div>
                            </div>
                        </div>
                    </div>
                    <!-- Total Cancel -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card border-left-danger shadow-sm h-100">
                            <div class="card-body py-2 px-3">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Cancel</div>
                                <div class="h5 mb-0 font-weight-bold" id="sumCancel">0</div>
                            </div>
                        </div>
                    </div>
                    <!-- Avg Life Span -->
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card border-left-warning shadow-sm h-100">
                            <div class="card-body py-2 px-3">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Avg Life Span</div>
                                <div class="h5 mb-0 font-weight-bold" id="sumAvg">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail tables row -->
                <div class="row">
                    <!-- By Type Shop -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm h-100">
                            <div class="card-header py-2"><strong><i class="bi bi-shop mr-1"></i> By Type Shop</strong></div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="bg-light"><tr><th>Type Shop</th><th class="text-center">Count</th></tr></thead>
                                    <tbody id="sumTypeShop"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- By Country -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm h-100">
                            <div class="card-header py-2"><strong><i class="bi bi-globe mr-1"></i> By Country</strong></div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="bg-light"><tr><th>Country</th><th class="text-center">Count</th></tr></thead>
                                    <tbody id="sumCountry"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- System / Marketing Detail -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm h-100">
                            <div class="card-header py-2"><strong><i class="bi bi-diagram-3 mr-1"></i> System / Marketing Detail</strong></div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="bg-light"><tr><th>Category</th><th class="text-center">Total</th><th class="text-center text-success">Live</th><th class="text-center text-danger">Cancel</th></tr></thead>
                                    <tbody id="sumSysMkt"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.spin { animation: spin 1s linear infinite; display: inline-block; }
@keyframes spin { 100% { transform: rotate(360deg); } }
#lifeSpanTable {
    font-size: 11px;
    table-layout: fixed;
    width: 100%;
}
#lifeSpanTable thead th {
    background: #f4f6f9 !important;
    position: sticky;
    top: 0;
    z-index: 2;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    font-size: 11px;
    padding: 4px 6px;
    text-align: center;
}
#lifeSpanTable tbody td {
    padding: 3px 6px;
    vertical-align: middle;
    overflow-x: auto;
    overflow-y: hidden;
    white-space: nowrap;
}
/* Fix column widths */
/* # */
#lifeSpanTable td:nth-child(1),
#lifeSpanTable th:nth-child(1) {
    width: 30px;
    min-width: 30px;
}

/* Name */
#lifeSpanTable td:nth-child(2),
#lifeSpanTable th:nth-child(2) {
    width: 140px;
    min-width: 110px;
    max-width: 220px;
}

/* Type Shop */
#lifeSpanTable td:nth-child(3),
#lifeSpanTable th:nth-child(3) {
    width: 120px;
    min-width: 120px;
}

/* Group */
#lifeSpanTable td:nth-child(4),
#lifeSpanTable th:nth-child(4) {
    
    width: 90px;
    min-width: 130px;
}

/* Status */
#lifeSpanTable td:nth-child(5),
#lifeSpanTable th:nth-child(5) {
    width: 50px;
    min-width: 80px;
}

/* System Live Date */
#lifeSpanTable td:nth-child(6),
#lifeSpanTable th:nth-child(6) {
    width: 70px;
    min-width: 100px;
}

/* System Cancel Date */
#lifeSpanTable td:nth-child(7),
#lifeSpanTable th:nth-child(7) {
    width: 70px;
    min-width: 110px;
}


#lifeSpanTable td:nth-child(8),
#lifeSpanTable th:nth-child(8) {
    width: 70px;
    min-width: 110px;
}
#lifeSpanTable td:nth-child(9),
#lifeSpanTable th:nth-child(9) {
    width: 70px;
    min-width: 110px;
}
#lifeSpanTable td:nth-child(10),
#lifeSpanTable th:nth-child(10) {
    width: 110px;
    min-width: 110px;
}
#lifeSpanTable td:nth-child(11),
#lifeSpanTable th:nth-child(11) {
    width: 180px;
    min-width: 140px;
    max-width: 180px;
}
#lifeSpanTable td:nth-child(12),
#lifeSpanTable th:nth-child(12) {
    width: 50px;
    min-width: 90px;
}
.lifespan-badge { font-size: 11px; white-space: nowrap; }
.lifespan-badge .ls-y { color: #dc3545; font-weight: 600; }
.lifespan-badge .ls-m { color: #fd7e14; font-weight: 600; }
.lifespan-badge .ls-d { color: #007bff; font-weight: 600; }
.status-live { color: #28a745; font-weight: 600; }
.status-cancel { color: #dc3545; font-weight: 600; }
.phase-active { color: #fd7e14; font-weight: 600; white-space: nowrap; }
.phase-active img { width: 16px; height: 16px; vertical-align: middle; margin-right: 3px; }
.phase-completed { color: #28a745; font-weight: 600; white-space: nowrap; }
.phase-completed i { vertical-align: middle; margin-right: 3px; }
.border-left-primary { border-left: 4px solid #007bff !important; }
.border-left-success { border-left: 4px solid #28a745 !important; }
.border-left-danger  { border-left: 4px solid #dc3545 !important; }
.border-left-warning { border-left: 4px solid #ffc107 !important; }
.text-xs { font-size: 0.7rem; }
.active-subs-cell {
    max-width: 180px;
    min-width: 140px;
    overflow-x: visible;
    overflow-y: visible;
    white-space: normal;
}
.active-subs-cell .subs-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
}
.active-subs-cell .subs-item {
    display: inline-block;
    font-size: 10px;
    line-height: 1.2;
    background: #eef3ff;
    color: #2d4a7a;
    border: 1px solid #c5d5f0;
    border-radius: 3px;
    padding: 1px 5px;
    white-space: nowrap;
    flex-shrink: 0;
}
.active-subs-cell .subs-item:nth-child(odd) {
    background: #f0f7f0;
    color: #2a6a3a;
    border-color: #b8dcc5;
}
</style>

<script>
(function() {
    // Column ID mappings per country for life span dates
    var lifeSpanCols = {
        AU: { sysLive: 'live_date', sysCancel: 'date_mm26gw5p', mktLive: 'date_mkzs3896', mktCancel: 'date_mm26kg00', activeSubs: 'connect_boards06' },
        TH: { sysLive: 'live_date', sysCancel: 'date_mm267zrn', mktLive: 'date_mkzsde4j', mktCancel: 'date_mm26989b', activeSubs: 'connect_boards06' },
        CA: { sysLive: 'live_date', sysCancel: 'date_mm26scn7', mktLive: 'date_mkzs7czr', mktCancel: 'date_mm26zjma', activeSubs: 'connect_boards06' },
        UK: { sysLive: 'live_date', sysCancel: 'date_mm26rep1', mktLive: 'date_mkzswq7q', mktCancel: 'date_mm26d16s', activeSubs: 'connect_boards06' },
        US: { sysLive: 'live_date', sysCancel: 'date_mm26ntpg', mktLive: 'date_mkzs790g', mktCancel: 'date_mm26jsve', activeSubs: 'connect_boards06' },
        NZ: { sysLive: 'live_date', sysCancel: 'date_mm26gdzw', mktLive: 'date_mkzs82rp', mktCancel: 'date_mm26tp5b', activeSubs: 'connect_boards06' }
    };

    var allRows = [];
    var filteredRows = [];

    // === Utility functions ===
    function detectCountry(boardName) {
        if (!boardName) return null;
        if (boardName.indexOf('| TH') !== -1) return 'TH';
        if (boardName.indexOf('| CA') !== -1) return 'CA';
        if (boardName.indexOf('| UK') !== -1) return 'UK';
        if (boardName.indexOf('| USA') !== -1 || boardName.indexOf('| US') !== -1) return 'US';
        if (boardName.indexOf('| NZ') !== -1) return 'NZ';
        if (boardName.indexOf('| AU') !== -1) return 'AU';
        return null;
    }

    function getColVal(item, colId) {
        if (!item.column_values) return '';
        for (var i = 0; i < item.column_values.length; i++) {
            if (item.column_values[i].id === colId) return item.column_values[i].text || '';
        }
        return '';
    }

    function daysBetween(dateA, dateB) {
        if (!dateA || !dateB) return null;
        var a = new Date(dateA), b = new Date(dateB);
        if (isNaN(a) || isNaN(b)) return null;
        return Math.round(Math.abs(b - a) / (1000 * 60 * 60 * 24));
    }

    function calculateLifeSpan(group, sysLive, mktLive, sysCancel, mktCancel) {
        var isCancel = group.toLowerCase().indexOf('cancel') !== -1;
        if (isCancel) {
            var liveDates = [], cancelDates = [];
            if (sysLive) liveDates.push(new Date(sysLive));
            if (mktLive) liveDates.push(new Date(mktLive));
            if (sysCancel) cancelDates.push(new Date(sysCancel));
            if (mktCancel) cancelDates.push(new Date(mktCancel));
            if (liveDates.length === 0 || cancelDates.length === 0) return null;
            var earliest = new Date(Math.min.apply(null, liveDates));
            var latest = new Date(Math.max.apply(null, cancelDates));
            if (isNaN(earliest) || isNaN(latest)) return null;
            return Math.round(Math.abs(latest - earliest) / (1000 * 60 * 60 * 24));
        } else {
            var spans = [];
            if (sysLive && sysCancel) { var d = daysBetween(sysLive, sysCancel); if (d !== null) spans.push(d); }
            if (mktLive && mktCancel) { var d2 = daysBetween(mktLive, mktCancel); if (d2 !== null) spans.push(d2); }
            if (spans.length === 0) return null;
            return Math.max.apply(null, spans);
        }
    }

    function getStatus(group, sysLive, mktLive) {
        if (group.toLowerCase().indexOf('cancel') !== -1) return 'Cancel';
        if (sysLive || mktLive) return 'Live';
        return '';
    }

    function getMarketingPhase(group, sysLive, sysCancel, mktLive, mktCancel) {
        var isCancel = group.toLowerCase().indexOf('cancel') !== -1;
        if (isCancel) {
            if (!sysLive && !sysCancel && !mktLive && !mktCancel) return '';
            if ((sysLive && sysCancel) || (mktLive && mktCancel)) return 'completed';
            return 'active';
        }
        if ((sysLive && sysCancel) && (mktLive && mktCancel)) return 'completed';
        if (!sysLive && !sysCancel && !mktLive && !mktCancel) return '';
        return 'active';
    }

    function formatLifeSpan(totalDays) {
        if (totalDays === null || totalDays === undefined) return '<span class="text-muted">—</span>';
        return '<span class="lifespan-badge"><span class="ls-d">' + totalDays.toLocaleString() + '</span> Day</span>';
    }

    function formatActiveSubs(val) {
        if (!val) return '<span class="text-muted">—</span>';
        var parts = val.split(',');
        var html = '';
        for (var i = 0; i < parts.length; i++) {
            var t = parts[i].trim();
            if (t) html += '<span class="subs-item">' + escHtml(t) + '</span>';
        }
        if (!html) return '<span class="text-muted">—</span>';
        return '<div class="subs-wrap">' + html + '</div>';
    }

    function escHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // === Load Data ===
    function loadData() {
        var country = document.getElementById('filterCountry').value;
        var url = 'api/monday/reportLifespan/selectProjectAllCountry.php?country=' + (country === 'ALL' ? 'AU,TH,CA,UK,US,NZ' : country);

        document.getElementById('loadingWrap').style.display = '';
        document.getElementById('btnLoad').disabled = true;
        document.getElementById('lifeSpanTbody').innerHTML = '<tr><td colspan="12" class="text-center py-4"><i class="bi bi-arrow-repeat spin mr-1"></i> Loading data from Monday.com...</td></tr>';
        document.getElementById('btnExport').style.display = 'none';
        document.getElementById('conditionFilterCard').style.display = 'none';
        document.getElementById('summarySection').style.display = 'none';

        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(json) {
                if (json.error) {
                    document.getElementById('lifeSpanTbody').innerHTML = '<tr><td colspan="12" class="text-center text-danger py-4">Error: ' + json.error + '</td></tr>';
                    return;
                }
                allRows = [];
                var countries = json.data ? Object.keys(json.data) : [];
                countries.forEach(function(cc) {
                    var items = json.data[cc].items || [];
                    var cols = lifeSpanCols[cc];
                    if (!cols) return;
                    items.forEach(function(item) {
                        var detectedCC = detectCountry(item.board_name) || cc;
                        var c = lifeSpanCols[detectedCC] || cols;
                        var sysLive   = getColVal(item, c.sysLive);
                        var sysCancel = getColVal(item, c.sysCancel);
                        var mktLive   = getColVal(item, c.mktLive);
                        var mktCancel = getColVal(item, c.mktCancel);
                        var typeShop    = getColVal(item, 'color1');
                        var activeSubs  = getColVal(item, c.activeSubs);
                        var group       = item.group_title || '';
                        allRows.push({
                            name: item.name,
                            country: detectedCC,
                            typeShop: typeShop,
                            group: group,
                            rowStatus: getStatus(group, sysLive, mktLive),
                            sysLive: sysLive, sysCancel: sysCancel,
                            mktLive: mktLive, mktCancel: mktCancel,
                            activeSubs: activeSubs,
                            mktPhase: getMarketingPhase(group, sysLive, sysCancel, mktLive, mktCancel),
                            lifeSpan: calculateLifeSpan(group, sysLive, mktLive, sysCancel, mktCancel),
                            hasSysPair: !!(sysLive && sysCancel),
                            hasMktPair: !!(mktLive && mktCancel)
                        });
                    });
                });
                // Sort by lifespan desc
                allRows.sort(function(a, b) {
                    var av = a.lifeSpan !== null ? a.lifeSpan : -1;
                    var bv = b.lifeSpan !== null ? b.lifeSpan : -1;
                    return bv - av;
                });
                populateFilterDropdowns();
                document.getElementById('conditionFilterCard').style.display = '';
                applyAllFilters();
            })
            .catch(function(err) {
                document.getElementById('lifeSpanTbody').innerHTML = '<tr><td colspan="12" class="text-center text-danger py-4">Fetch error: ' + err + '</td></tr>';
            })
            .finally(function() {
                document.getElementById('loadingWrap').style.display = 'none';
                document.getElementById('btnLoad').disabled = false;
            });
    }

    // === Populate dynamic filter dropdowns ===
    function populateFilterDropdowns() {
        var countries = {}, groups = {}, types = {}, subs = {};
        allRows.forEach(function(r) {
            if (r.country) countries[r.country] = true;
            if (r.group) groups[r.group] = true;
            if (r.typeShop) types[r.typeShop] = true;
            if (r.activeSubs) {
                r.activeSubs.split(',').forEach(function(s) {
                    var t = s.trim();
                    if (t) subs[t] = true;
                });
            }
        });
        fillSelect('cfCountry', countries);
        fillSelect('cfGroup', groups);
        fillSelect('cfTypeShop', types);
        fillSelect('cfActiveSubs', subs);
    }

    function fillSelect(id, obj) {
        var sel = document.getElementById(id);
        var val = sel.value;
        sel.innerHTML = '<option value="ALL">All</option>';
        Object.keys(obj).sort().forEach(function(k) {
            sel.innerHTML += '<option value="' + escHtml(k) + '">' + escHtml(k) + '</option>';
        });
        sel.value = val || 'ALL';
    }

    // === Apply all filters + search + sort ===
    function applyAllFilters() {
        var cfLifeSpan = document.getElementById('cfLifeSpan').value;
        var cfCountry  = document.getElementById('cfCountry').value;
        var cfSysMkt   = document.getElementById('cfSysMkt').value;
        var cfGroup    = document.getElementById('cfGroup').value;
        var cfTypeShop = document.getElementById('cfTypeShop').value;
        var search     = document.getElementById('searchInput').value.toLowerCase().trim();

        filteredRows = allRows.filter(function(r) {
            // Life span filter
            if (cfLifeSpan !== 'ALL') {
                if (r.lifeSpan === null) return false;
                var ls = r.lifeSpan;
                if (cfLifeSpan === '7D' && ls > 7) return false;
                if (cfLifeSpan === '30D' && ls > 30) return false;
                if (cfLifeSpan === '60D' && ls > 60) return false;
                if (cfLifeSpan === '90D' && ls > 90) return false;
                if (cfLifeSpan === '3M' && ls <= 90) return false;
                if (cfLifeSpan === '6M' && ls <= 180) return false;
                if (cfLifeSpan === '9M' && ls <= 270) return false;
                if (cfLifeSpan === '1Y' && ls <= 365) return false;
            }
            // Country
            if (cfCountry !== 'ALL' && r.country !== cfCountry) return false;
            // System / Marketing
            if (cfSysMkt === 'system') {
                var hasSystem = false;
                if (!r.activeSubs || r.activeSubs.trim() === '') {
                    if (r.hasSysPair) hasSystem = true;
                } else {
                    var sysKeywords = ['System', 'Bundle', 'Website', 'Hosting'];
                    var sysLower = r.activeSubs.toLowerCase();
                    for (var sk = 0; sk < sysKeywords.length; sk++) {
                        if (sysLower.indexOf(sysKeywords[sk].toLowerCase()) !== -1) { hasSystem = true; break; }
                    }
                }
                if (!hasSystem) return false;
            }
            if (cfSysMkt === 'marketing') {
                var hasMarketing = false;
                if (!r.activeSubs || r.activeSubs.trim() === '') {
                    // ถ้า Active Subscription ว่าง ให้ดูจาก Marketing Live/Cancel Date
                    if (r.hasMktPair) hasMarketing = true;
                } else {
                    // ถ้ามี Active Subscription ให้เช็คคำสำคัญ
                    var keywords = ['Ad', 'Solo', 'Marketing', 'Social', 'Bundle'];
                    var subs = r.activeSubs.toLowerCase();
                    for (var k = 0; k < keywords.length; k++) {
                        if (subs.indexOf(keywords[k].toLowerCase()) !== -1) {
                            hasMarketing = true;
                            break;
                        }
                    }
                }
                if (!hasMarketing) return false;
            }
            // Group
            if (cfGroup !== 'ALL' && r.group !== cfGroup) return false;
            // Type Shop
            if (cfTypeShop !== 'ALL' && r.typeShop !== cfTypeShop) return false;
            // Active Subscription
            var cfActiveSubs = document.getElementById('cfActiveSubs').value;
            if (cfActiveSubs !== 'ALL') {
                if (!r.activeSubs || r.activeSubs.indexOf(cfActiveSubs) === -1) return false;
            }
            // Status
            var cfStatus = document.getElementById('cfStatus').value;
            if (cfStatus !== 'ALL' && r.rowStatus !== cfStatus) return false;
            // Marketing Phase
            var cfPhase = document.getElementById('cfPhase').value;
            if (cfPhase !== 'ALL' && r.mktPhase !== cfPhase) return false;
            // Search
            if (search) {
                var found = (r.name && r.name.toLowerCase().indexOf(search) !== -1) ||
                    (r.typeShop && r.typeShop.toLowerCase().indexOf(search) !== -1) ||
                    (r.group && r.group.toLowerCase().indexOf(search) !== -1) ||
                    (r.rowStatus && r.rowStatus.toLowerCase().indexOf(search) !== -1) ||
                    (r.country && r.country.toLowerCase().indexOf(search) !== -1) ||
                    (r.sysLive && r.sysLive.indexOf(search) !== -1) ||
                    (r.sysCancel && r.sysCancel.indexOf(search) !== -1) ||
                    (r.mktLive && r.mktLive.indexOf(search) !== -1) ||
                    (r.mktCancel && r.mktCancel.indexOf(search) !== -1);
                if (!found) return false;
            }
            return true;
        });

        renderTable(filteredRows);
        renderSummary(filteredRows);
    }

    // === Render Table ===
    function renderTable(rows) {
        var tbody = document.getElementById('lifeSpanTbody');
        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="12" class="text-center text-muted py-4">No data found.</td></tr>';
            document.getElementById('btnExport').style.display = 'none';
            return;
        }
        var html = '';
        rows.forEach(function(r, i) {
            var statusHtml = r.rowStatus === 'Live' ? '<span class="status-live"><i class="bi bi-circle-fill" style="font-size:8px;"></i> Live</span>' :
                             r.rowStatus === 'Cancel' ? '<span class="status-cancel"><i class="bi bi-circle-fill" style="font-size:8px;"></i> Cancel</span>' : '';
            var phaseHtml = r.mktPhase === 'active' ? '<span class="phase-active"><img src="assets/img/loadingSpin.gif"> Active</span>' :
                            r.mktPhase === 'completed' ? '<span class="phase-completed"><i class="bi bi-check-circle-fill"></i> Completed</span>' :
                            '<span class="text-muted">—</span>';
            html += '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + escHtml(r.name) + '</td>' +
                '<td>' + escHtml(r.typeShop) + '</td>' +
                '<td>' + escHtml(r.group) + '</td>' +
                '<td>' + statusHtml + '</td>' +
                '<td>' + escHtml(r.sysLive) + '</td>' +
                '<td>' + escHtml(r.sysCancel) + '</td>' +
                '<td>' + escHtml(r.mktLive) + '</td>' +
                '<td>' + escHtml(r.mktCancel) + '</td>' +
                '<td class="text-center">' + phaseHtml + '</td>' +
                '<td class="active-subs-cell">' + formatActiveSubs(r.activeSubs) + '</td>' +
                '<td class="text-center">' + formatLifeSpan(r.lifeSpan) + '</td>' +
                '</tr>';
        });
        tbody.innerHTML = html;
        document.getElementById('btnExport').style.display = '';
    }

    // === Render Summary ===
    function renderSummary(rows) {
        var el = document.getElementById('summarySection');
        if (rows.length === 0) { el.style.display = 'none'; return; }
        el.style.display = '';

        var totalLive = 0, totalCancel = 0, totalSpan = 0, spanCount = 0;
        var byType = {}, byCountry = {};
        var sysTotal = 0, sysLive = 0, sysCancel = 0;
        var mktTotal = 0, mktLive = 0, mktCancel = 0;

        rows.forEach(function(r) {
            if (r.rowStatus === 'Live') totalLive++;
            if (r.rowStatus === 'Cancel') totalCancel++;
            if (r.lifeSpan !== null) { totalSpan += r.lifeSpan; spanCount++; }

            // Type Shop
            var ts = r.typeShop || '(empty)';
            byType[ts] = (byType[ts] || 0) + 1;

            // Country
            var cc = r.country || '(unknown)';
            byCountry[cc] = (byCountry[cc] || 0) + 1;

            // System detail (same logic as filter)
            var isSys = false;
            if (!r.activeSubs || r.activeSubs.trim() === '') {
                if (r.sysLive || r.sysCancel) isSys = true;
            } else {
                var sysKw = ['system', 'bundle', 'website', 'hosting'];
                var sysLw = r.activeSubs.toLowerCase();
                for (var sk2 = 0; sk2 < sysKw.length; sk2++) {
                    if (sysLw.indexOf(sysKw[sk2]) !== -1) { isSys = true; break; }
                }
            }
            if (isSys) {
                sysTotal++;
                if (r.rowStatus === 'Live') sysLive++;
                if (r.rowStatus === 'Cancel') sysCancel++;
            }
            // Marketing detail (same logic as filter)
            var isMkt = false;
            if (!r.activeSubs || r.activeSubs.trim() === '') {
                if (r.mktLive || r.mktCancel) isMkt = true;
            } else {
                var mktKeywords = ['ad', 'solo', 'marketing', 'social', 'bundle'];
                var subsLower = r.activeSubs.toLowerCase();
                for (var mk = 0; mk < mktKeywords.length; mk++) {
                    if (subsLower.indexOf(mktKeywords[mk]) !== -1) { isMkt = true; break; }
                }
            }
            if (isMkt) {
                mktTotal++;
                if (r.rowStatus === 'Live') mktLive++;
                if (r.rowStatus === 'Cancel') mktCancel++;
            }
        });

        // Top cards
        document.getElementById('sumTotal').textContent = rows.length.toLocaleString();
        document.getElementById('sumLive').textContent = totalLive.toLocaleString();
        document.getElementById('sumCancel').textContent = totalCancel.toLocaleString();
        var avg = spanCount > 0 ? Math.round(totalSpan / spanCount) : null;
        document.getElementById('sumAvg').textContent = avg !== null ? (avg.toLocaleString() + ' Day') : '—';

        // By Type Shop
        var html = '';
        Object.keys(byType).sort().forEach(function(k) {
            html += '<tr><td>' + escHtml(k) + '</td><td class="text-center">' + byType[k] + '</td></tr>';
        });
        html += '<tr class="font-weight-bold bg-light"><td>Total</td><td class="text-center">' + rows.length + '</td></tr>';
        document.getElementById('sumTypeShop').innerHTML = html;

        // By Country
        html = '';
        Object.keys(byCountry).sort().forEach(function(k) {
            html += '<tr><td>' + escHtml(k) + '</td><td class="text-center">' + byCountry[k] + '</td></tr>';
        });
        html += '<tr class="font-weight-bold bg-light"><td>Total</td><td class="text-center">' + rows.length + '</td></tr>';
        document.getElementById('sumCountry').innerHTML = html;

        // System / Marketing Detail
        html = '<tr><td>All</td><td class="text-center">' + rows.length + '</td><td class="text-center text-success">' + totalLive + '</td><td class="text-center text-danger">' + totalCancel + '</td></tr>';
        html += '<tr><td>System</td><td class="text-center">' + sysTotal + '</td><td class="text-center text-success">' + sysLive + '</td><td class="text-center text-danger">' + sysCancel + '</td></tr>';
        html += '<tr><td>Marketing</td><td class="text-center">' + mktTotal + '</td><td class="text-center text-success">' + mktLive + '</td><td class="text-center text-danger">' + mktCancel + '</td></tr>';
        document.getElementById('sumSysMkt').innerHTML = html;
    }

    // === Export CSV (uses filteredRows) ===
    function exportCSV() {
        var rows = filteredRows.length > 0 ? filteredRows : allRows;
        if (rows.length === 0) return;
        var headers = ['#','Name','Type Shop','Group','Status','System Live Date','System Cancel Date','Marketing Live Date','Marketing Cancel Date','Marketing Phase','Active Subscriptions','Life Span (days)'];
        var csvRows = [headers.join(',')];
        rows.forEach(function(r, i) {
            csvRows.push([
                i + 1,
                '"' + (r.name || '').replace(/"/g, '""') + '"',
                '"' + (r.typeShop || '').replace(/"/g, '""') + '"',
                '"' + (r.group || '').replace(/"/g, '""') + '"',
                r.rowStatus,
                r.sysLive, r.sysCancel, r.mktLive, r.mktCancel,
                r.mktPhase === 'active' ? 'Active' : r.mktPhase === 'completed' ? 'Completed' : '',
                '"' + (r.activeSubs || '').replace(/"/g, '""') + '"',
                r.lifeSpan !== null ? r.lifeSpan : ''
            ].join(','));
        });
        var blob = new Blob([csvRows.join('\n')], { type: 'text/csv' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'life_span_report_' + new Date().toISOString().slice(0, 10) + '.csv';
        a.click();
    }

    // === Reset filters ===
    function resetFilters() {
        document.getElementById('cfLifeSpan').value = 'ALL';
        document.getElementById('cfCountry').value = 'ALL';
        document.getElementById('cfSysMkt').value = 'ALL';
        document.getElementById('cfGroup').value = 'ALL';
        document.getElementById('cfTypeShop').value = 'ALL';
        document.getElementById('cfStatus').value = 'ALL';
        document.getElementById('cfPhase').value = 'ALL';
        document.getElementById('cfActiveSubs').value = 'ALL';
        document.getElementById('searchInput').value = '';
        applyAllFilters();
    }

    // === Event Listeners ===
    var searchTimer = null;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyAllFilters, 300);
    });

    ['cfLifeSpan','cfCountry','cfSysMkt','cfGroup','cfTypeShop','cfStatus','cfPhase','cfActiveSubs'].forEach(function(id) {
        document.getElementById(id).addEventListener('change', applyAllFilters);
    });

    document.getElementById('btnLoad').addEventListener('click', loadData);
    document.getElementById('btnExport').addEventListener('click', exportCSV);
    document.getElementById('btnResetFilter').addEventListener('click', resetFilters);
})();
</script>
