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

        <!-- Filter -->
        <div class="card mb-3">
            <div class="card-body py-3">
                <form id="filterForm" class="row align-items-end g-2">
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
                </form>
            </div>
        </div>

        <!-- Summary cards -->
        <!-- <div class="row mb-3" id="summaryRow" style="display:none;">
            <div class="col-12 col-md-3 mb-2">
                <div class="small-box bg-info" style="min-height:90px;">
                    <div class="inner">
                        <h3 id="totalItems">0</h3>
                        <p>Total Items</p>
                    </div>
                    <div class="icon"><i class="bi bi-people"></i></div>
                </div>
            </div>
            <div class="col-12 col-md-3 mb-2">
                <div class="small-box bg-success" style="min-height:90px;">
                    <div class="inner">
                        <h3 id="totalLive">0</h3>
                        <p>Has Live Date</p>
                    </div>
                    <div class="icon"><i class="bi bi-calendar-check"></i></div>
                </div>
            </div>
            <div class="col-12 col-md-3 mb-2">
                <div class="small-box bg-danger" style="min-height:90px;">
                    <div class="inner">
                        <h3 id="totalCancelled">0</h3>
                        <p>Has Cancel Date</p>
                    </div>
                    <div class="icon"><i class="bi bi-calendar-x"></i></div>
                </div>
            </div>
            <div class="col-12 col-md-3 mb-2">
                <div class="small-box bg-warning" style="min-height:90px;">
                    <div class="inner">
                        <h3 id="avgLifeSpan">—</h3>
                        <p>Avg Life Span</p>
                    </div>
                    <div class="icon"><i class="bi bi-hourglass-split"></i></div>
                </div>
            </div>
        </div> -->

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
                                <th>Status</th>
                                <th>Group</th>
                                <th>System Live Date</th>
                                <th>Marketing Live Date</th>
                                <th>System Cancel Date</th>
                                <th>Marketing Cancel Date</th>
                                <th>Life Span</th>
                            </tr>
                        </thead>
                        <tbody id="lifeSpanTbody">
                            <tr><td colspan="9" class="text-center text-muted py-4">Click <strong>Load Data</strong> to fetch life span data.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.spin { animation: spin 1s linear infinite; display: inline-block; }
@keyframes spin { 100% { transform: rotate(360deg); } }
#lifeSpanTable thead th {
    background: #f4f6f9 !important;
    position: sticky;
    top: 0;
    z-index: 2;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.lifespan-badge { font-size: 12px; white-space: nowrap; }
.lifespan-badge .ls-y { color: #dc3545; font-weight: 600; }
.lifespan-badge .ls-m { color: #fd7e14; font-weight: 600; }
.lifespan-badge .ls-d { color: #007bff; font-weight: 600; }
</style>

<script>
(function() {
    // Column ID mappings per country for life span dates
    var lifeSpanCols = {
        AU: { sysLive: 'live_date', sysCancel: 'date_mm26gw5p', mktLive: 'date_mkzs3896', mktCancel: 'date_mm26kg00' },
        TH: { sysLive: 'live_date', sysCancel: 'date_mm267zrn', mktLive: 'date_mkzsde4j', mktCancel: 'date_mm26989b' },
        CA: { sysLive: 'live_date', sysCancel: 'date_mm26scn7', mktLive: 'date_mkzs7czr', mktCancel: 'date_mm26zjma' },
        UK: { sysLive: 'live_date', sysCancel: 'date_mm26rep1', mktLive: 'date_mkzswq7q', mktCancel: 'date_mm26d16s' },
        US: { sysLive: 'live_date', sysCancel: 'date_mm26ntpg', mktLive: 'date_mkzs790g', mktCancel: 'date_mm26jsve' },
        NZ: { sysLive: 'live_date', sysCancel: 'date_mm26gdzw', mktLive: 'date_mkzs82rp', mktCancel: 'date_mm26tp5b' }
    };

    var allRows = [];

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
        var diff = Math.abs(b - a);
        return Math.round(diff / (1000 * 60 * 60 * 24));
    }

    function calculateLifeSpan(sysLive, mktLive, sysCancel, mktCancel) {
        var liveDates = [];
        var cancelDates = [];
        
        if (sysLive) liveDates.push(new Date(sysLive));
        if (mktLive) liveDates.push(new Date(mktLive));
        if (sysCancel) cancelDates.push(new Date(sysCancel));
        if (mktCancel) cancelDates.push(new Date(mktCancel));
        
        if (liveDates.length === 0 || cancelDates.length === 0) return null;
        
        var earliestLive = new Date(Math.min.apply(null, liveDates));
        var latestCancel = new Date(Math.max.apply(null, cancelDates));
        
        if (isNaN(earliestLive) || isNaN(latestCancel)) return null;
        
        var diff = Math.abs(latestCancel - earliestLive);
        return Math.round(diff / (1000 * 60 * 60 * 24));
    }

    function formatLifeSpan(totalDays) {
        if (totalDays === null || totalDays === undefined) return '<span class="text-muted">—</span>';
        return '<span class="lifespan-badge"><span class="ls-d">' + totalDays.toLocaleString() + '</span> Day</span>';
    }

    function formatLifeSpanText(totalDays) {
        if (totalDays === null || totalDays === undefined) return '—';
        return totalDays.toLocaleString() + ' วัน';
    }

    function loadData() {
        var country = document.getElementById('filterCountry').value;
        var url = 'api/monday/reportLifespan/selectProjectAllCountry.php?country=' + (country === 'ALL' ? 'AU,TH,CA,UK,US,NZ' : country);
        
        document.getElementById('loadingWrap').style.display = '';
        document.getElementById('btnLoad').disabled = true;
        document.getElementById('lifeSpanTbody').innerHTML = '<tr><td colspan="9" class="text-center py-4"><i class="bi bi-arrow-repeat spin mr-1"></i> Loading data from Monday.com...</td></tr>';
        var sr = document.getElementById('summaryRow'); if (sr) sr.style.display = 'none';
        document.getElementById('btnExport').style.display = 'none';

        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(json) {
                if (json.error) {
                    document.getElementById('lifeSpanTbody').innerHTML = '<tr><td colspan="9" class="text-center text-danger py-4">Error: ' + json.error + '</td></tr>';
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
                        
                        var sysLive    = getColVal(item, c.sysLive);
                        var sysCancel  = getColVal(item, c.sysCancel);
                        var mktLive    = getColVal(item, c.mktLive);
                        var mktCancel  = getColVal(item, c.mktCancel);
                        var status     = getColVal(item, 'color1');
                        
                        var lifeSpan = calculateLifeSpan(sysLive, mktLive, sysCancel, mktCancel);
                        
                        allRows.push({
                            name: item.name,
                            country: detectedCC,
                            status: status,
                            group: item.group_title || '',
                            sysLive: sysLive,
                            sysCancel: sysCancel,
                            mktLive: mktLive,
                            mktCancel: mktCancel,
                            lifeSpan: lifeSpan
                        });
                    });
                });
                
                renderTable(allRows);
                renderSummary(allRows);
            })
            .catch(function(err) {
                document.getElementById('lifeSpanTbody').innerHTML = '<tr><td colspan="9" class="text-center text-danger py-4">Fetch error: ' + err + '</td></tr>';
            })
            .finally(function() {
                document.getElementById('loadingWrap').style.display = 'none';
                document.getElementById('btnLoad').disabled = false;
            });
    }

    function renderTable(rows) {
        var tbody = document.getElementById('lifeSpanTbody');
        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">No data found.</td></tr>';
            return;
        }
        var html = '';
        rows.forEach(function(r, i) {
            html += '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + escHtml(r.name) + '</td>' +
                '<td>' + escHtml(r.status) + '</td>' +
                '<td>' + escHtml(r.group) + '</td>' +
                '<td>' + escHtml(r.sysLive) + '</td>' +
                '<td>' + escHtml(r.mktLive) + '</td>' +
                '<td>' + escHtml(r.sysCancel) + '</td>' +
                '<td>' + escHtml(r.mktCancel) + '</td>' +
                '<td class="text-center">' + formatLifeSpan(r.lifeSpan) + '</td>' +
                '</tr>';
        });
        tbody.innerHTML = html;
        document.getElementById('btnExport').style.display = '';
    }

    function renderSummary(rows) {
        var el = document.getElementById('summaryRow');
        if (!el) return;
        el.style.display = '';
        el.style.setProperty('display', 'flex', 'important');
        el.classList.add('d-flex', 'flex-wrap');
        document.getElementById('totalItems').textContent = Number(rows.length).toLocaleString();
        
        var hasLive = 0, hasCancelled = 0, totalSpan = 0, spanCount = 0;
        rows.forEach(function(r) {
            if (r.sysLive || r.mktLive) hasLive++;
            if (r.sysCancel || r.mktCancel) hasCancelled++;
            if (r.lifeSpan !== null) { totalSpan += r.lifeSpan; spanCount++; }
        });
        
        document.getElementById('totalLive').textContent = Number(hasLive).toLocaleString();
        document.getElementById('totalCancelled').textContent = Number(hasCancelled).toLocaleString();
        var avgDays = spanCount > 0 ? Math.round(totalSpan / spanCount) : null;
        document.getElementById('avgLifeSpan').textContent = avgDays !== null ? formatLifeSpanText(avgDays) : '—';
    }

    function escHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function exportCSV() {
        if (allRows.length === 0) return;
        var headers = ['#','Name','Status','Group','System Live Date','Marketing Live Date','System Cancel Date','Marketing Cancel Date','Life Span (days)'];
        var csvRows = [headers.join(',')];
        allRows.forEach(function(r, i) {
            csvRows.push([
                i + 1,
                '"' + (r.name || '').replace(/"/g, '""') + '"',
                '"' + (r.status || '').replace(/"/g, '""') + '"',
                '"' + (r.group || '').replace(/"/g, '""') + '"',
                r.sysLive,
                r.mktLive,
                r.sysCancel,
                r.mktCancel,
                r.lifeSpan !== null ? r.lifeSpan : ''
            ].join(','));
        });
        var blob = new Blob([csvRows.join('\n')], { type: 'text/csv' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'life_span_report_' + new Date().toISOString().slice(0, 10) + '.csv';
        a.click();
    }

    function applySearch() {
        var term = document.getElementById('searchInput').value.toLowerCase().trim();
        if (!term) {
            renderTable(allRows);
            return;
        }
        var filtered = allRows.filter(function(r) {
            return (r.name && r.name.toLowerCase().indexOf(term) !== -1) ||
                   (r.country && r.country.toLowerCase().indexOf(term) !== -1) ||
                   (r.status && r.status.toLowerCase().indexOf(term) !== -1) ||
                   (r.group && r.group.toLowerCase().indexOf(term) !== -1) ||
                   (r.sysLive && r.sysLive.indexOf(term) !== -1) ||
                   (r.sysCancel && r.sysCancel.indexOf(term) !== -1) ||
                   (r.mktLive && r.mktLive.indexOf(term) !== -1) ||
                   (r.mktCancel && r.mktCancel.indexOf(term) !== -1);
        });
        renderTable(filtered);
    }

    var searchTimer = null;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applySearch, 300);
    });

    document.getElementById('btnLoad').addEventListener('click', loadData);
    document.getElementById('btnExport').addEventListener('click', exportCSV);
})();
</script>
