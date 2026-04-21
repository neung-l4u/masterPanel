<?php
global $db, $activeMenu;
$loginID = $_SESSION['id'];
$reportType = $activeMenu["lv2"];
if (empty($reportType)) { $reportType = "reportWeekly"; }
$currentYear = date('Y');

$periodLabels = [
    'reportWeekly'  => ['icon' => 'bi-calendar-week',  'title' => 'Weekly Report',  'label' => 'Weekly'],
    'reportMonthly' => ['icon' => 'bi-calendar-month', 'title' => 'Monthly Report', 'label' => 'Monthly'],
    'reportYearly'  => ['icon' => 'bi-calendar3',      'title' => 'Yearly Report',  'label' => 'Yearly'],
    'reportDate'    => ['icon' => 'bi-calendar-date',  'title' => 'Date Range Report', 'label' => 'Date'],
];
$cur = $periodLabels[$reportType];
$isSingle = ($reportType === 'reportDate');
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0"><i class="bi bi-bar-chart-line"></i> Report</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item active">Report</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container-fluid">

        <!-- Filter Tabs -->
        <div class="card mb-3 rpt-tab-card">
            <div class="card-header p-0">
                <ul class="nav nav-pills nav-fill" id="reportTabs">
                    <?php foreach ($periodLabels as $key => $info) { ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($reportType == $key) ? 'active' : ''; ?>" href="main.php?p=<?php echo $key; ?>">
                            <i class="bi <?php echo $info['icon']; ?> mr-1"></i> <?php echo $info['label']; ?>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:6px;">
                    <h5 class="mb-0"><i class="bi <?php echo $cur['icon']; ?> mr-1"></i> <?php echo $cur['title']; ?></h5>
                    <div class="d-inline-flex align-items-center flex-wrap" style="gap:6px;">
                        <?php if ($reportType == 'reportWeekly') { ?>
                            <span class="badge badge-primary px-2 py-1">A</span>
                            <input type="date" id="filterA" class="form-control form-control-sm" style="width:150px;" value="<?php echo date('Y-m-d', strtotime('-7 days')); ?>">
                            <span class="text-muted" style="font-size:13px;">vs</span>
                            <span class="badge badge-success px-2 py-1">B</span>
                            <input type="date" id="filterB" class="form-control form-control-sm" style="width:150px;" value="<?php echo date('Y-m-d'); ?>">
                        <?php } elseif ($reportType == 'reportMonthly') { ?>
                            <span class="badge badge-primary px-2 py-1">A</span>
                            <input type="month" id="filterA" class="form-control form-control-sm" style="width:150px;" value="<?php echo date('Y-m', strtotime('-1 month')); ?>">
                            <span class="text-muted" style="font-size:13px;">vs</span>
                            <span class="badge badge-success px-2 py-1">B</span>
                            <input type="month" id="filterB" class="form-control form-control-sm" style="width:150px;" value="<?php echo date('Y-m'); ?>">
                        <?php } elseif ($reportType == 'reportDate') { ?>
                            <span class="badge badge-primary px-2 py-1">From</span>
                            <input type="date" id="filterA" class="form-control form-control-sm" style="width:150px;" value="<?php echo date('Y-m-d', strtotime('-7 days')); ?>">
                            <span class="text-muted" style="font-size:13px;">to</span>
                            <span class="badge badge-success px-2 py-1">To</span>
                            <input type="date" id="filterB" class="form-control form-control-sm" style="width:150px;" value="<?php echo date('Y-m-d'); ?>">
                        <?php } elseif ($reportType == 'reportYearly') { ?>
                            <span class="badge badge-primary px-2 py-1">A</span>
                            <select id="filterA" class="form-control form-control-sm" style="width:100px;">
                                <?php for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                    echo "<option value=\"$y\"" . ($y == $currentYear - 1 ? ' selected' : '') . ">$y</option>";
                                } ?>
                            </select>
                            <span class="text-muted" style="font-size:13px;">vs</span>
                            <span class="badge badge-success px-2 py-1">B</span>
                            <select id="filterB" class="form-control form-control-sm" style="width:100px;">
                                <?php for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                    echo "<option value=\"$y\"" . ($y == $currentYear ? ' selected' : '') . ">$y</option>";
                                } ?>
                            </select>
                        <?php } ?>
                        <button class="btn btn-primary btn-sm px-3" onclick="loadReport()">
                            <i class="bi bi-arrow-clockwise mr-1"></i> Compare
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div id="reportLoading" class="text-center py-5" style="display:none;">
            <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status"></div>
            <p class="mt-3 text-muted">Generating report, please wait...</p>
        </div>

        <!-- Placeholder -->
        <div id="reportPlaceholder" class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-bar-chart-line" style="font-size:4rem;opacity:0.3;"></i>
                <p class="mt-3 mb-0">Select a period and click <b>Generate</b> to view the report.</p>
            </div>
        </div>

        <!-- ===== Dynamic Report Content ===== -->
        <div id="reportDashboard" style="display:none;">

            <!-- Period Labels -->
            <div class="row mb-3">
                <?php if ($isSingle) { ?>
                    <div class="col-12"><div class="rpt-period-label rpt-period-a"><i class="bi bi-calendar3 mr-1"></i> <span id="periodLabelA">Period</span></div></div>
                <?php } else { ?>
                    <div class="col-6"><div class="rpt-period-label rpt-period-a"><i class="bi bi-calendar3 mr-1"></i> <span id="periodLabelA">Period A</span></div></div>
                    <div class="col-6"><div class="rpt-period-label rpt-period-b"><i class="bi bi-calendar3 mr-1"></i> <span id="periodLabelB">Period B</span></div></div>
                <?php } ?>
            </div>

            <!-- Comparison Summary Cards -->
            <div class="row mb-3">
                <div class="col-6 col-lg-3">
                    <div class="rpt-cmp-card">
                        <div class="rpt-cmp-title"><i class="bi bi-people mr-1"></i> Active</div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="rpt-cmp-val"><small class="badge badge-primary">A</small> <span id="cardActiveA">0</span></div>
                            <div class="rpt-cmp-val"><small class="badge badge-success">B</small> <span id="cardActiveB">0</span></div>
                            <div class="rpt-cmp-diff" id="cardActiveDiff">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="rpt-cmp-card">
                        <div class="rpt-cmp-title"><i class="bi bi-person-plus mr-1 text-success"></i> Signup</div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="rpt-cmp-val"><small class="badge badge-primary">A</small> <span id="cardSignupA">0</span></div>
                            <div class="rpt-cmp-val"><small class="badge badge-success">B</small> <span id="cardSignupB">0</span></div>
                            <div class="rpt-cmp-diff" id="cardSignupDiff">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="rpt-cmp-card">
                        <div class="rpt-cmp-title"><i class="bi bi-person-dash mr-1 text-danger"></i> Unsub</div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="rpt-cmp-val"><small class="badge badge-primary">A</small> <span id="cardUnsubA">0</span></div>
                            <div class="rpt-cmp-val"><small class="badge badge-success">B</small> <span id="cardUnsubB">0</span></div>
                            <div class="rpt-cmp-diff" id="cardUnsubDiff">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="rpt-cmp-card">
                        <div class="rpt-cmp-title"><i class="bi bi-graph-up-arrow mr-1"></i> Net Change</div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="rpt-cmp-val"><small class="badge badge-primary">A</small> <span id="cardNetA">0%</span></div>
                            <div class="rpt-cmp-val"><small class="badge badge-success">B</small> <span id="cardNetB">0%</span></div>
                            <div class="rpt-cmp-diff" id="cardNetDiff">0%</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-3">
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title"><i class="bi bi-bar-chart mr-1"></i> Signup & Unsub by Country</h3></div>
                        <div class="card-body" style="height:340px;">
                            <canvas id="chartCountry"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title"><i class="bi bi-pie-chart mr-1"></i> Customer Type (Active)</h3></div>
                        <div class="card-body" style="height:340px;">
                            <canvas id="chartType"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comparison Tables -->
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="bi bi-table mr-1"></i> Country Comparison</h3></div>
                <div class="card-body p-0" style="overflow-x:auto;">
                    <table class="table table-sm table-hover mb-0" id="tblCountry">
                        <?php if ($isSingle) { ?>
                        <thead class="thead-dark"><tr>
                            <th>Country</th>
                            <th class="text-center">Active</th>
                            <th class="text-center">Signup</th>
                            <th class="text-center">Unsub</th>
                        </tr></thead>
                        <?php } else { ?>
                        <thead class="thead-dark"><tr>
                            <th>Country</th>
                            <th class="text-center" colspan="3">Period A</th>
                            <th class="text-center" colspan="3">Period B</th>
                            <th class="text-center" colspan="2">Change</th>
                        </tr><tr class="bg-light">
                            <th></th>
                            <th class="text-center">Active</th><th class="text-center">Signup</th><th class="text-center">Unsub</th>
                            <th class="text-center">Active</th><th class="text-center">Signup</th><th class="text-center">Unsub</th>
                            <th class="text-center">Signup</th><th class="text-center">Unsub</th>
                        </tr></thead>
                        <?php } ?>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="bi bi-table mr-1"></i> Customer Type Comparison</h3></div>
                <div class="card-body p-0" style="overflow-x:auto;">
                    <table class="table table-sm table-hover mb-0" id="tblType">
                        <?php if ($isSingle) { ?>
                        <thead class="thead-dark"><tr>
                            <th>Type</th>
                            <th class="text-center">Signup</th>
                            <th class="text-center">Unsub</th>
                        </tr></thead>
                        <?php } else { ?>
                        <thead class="thead-dark"><tr>
                            <th>Type</th>
                            <th class="text-center" colspan="2">Period A</th>
                            <th class="text-center" colspan="2">Period B</th>
                            <th class="text-center" colspan="2">Change</th>
                        </tr><tr class="bg-light">
                            <th></th>
                            <th class="text-center">Signup</th><th class="text-center">Unsub</th>
                            <th class="text-center">Signup</th><th class="text-center">Unsub</th>
                            <th class="text-center">Signup</th><th class="text-center">Unsub</th>
                        </tr></thead>
                        <?php } ?>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>

<style>
    /* Tabs */
    #reportTabs .nav-link { border-radius:0; padding:0.75rem 1rem; color:#6c757d; font-weight:500; transition:all 0.2s; }
    #reportTabs .nav-link:hover { background:#f0f4ff; color:#0361D1; }
    #reportTabs .nav-link.active { background:#0361D1 !important; color:#fff !important; }

    /* Period labels */
    .rpt-period-label { border-radius:8px; padding:10px 16px; font-size:13px; font-weight:600; text-align:center; }
    .rpt-period-a { background:#e0ecff; color:#0361D1; border:1px solid #b3d1ff; }
    .rpt-period-b { background:#d1fae5; color:#059669; border:1px solid #6ee7b7; }

    /* Comparison cards */
    .rpt-cmp-card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:14px 16px; margin-bottom:10px; box-shadow:0 1px 3px rgba(0,0,0,0.06); }
    .rpt-cmp-title { font-size:0.8rem; font-weight:600; color:#475569; margin-bottom:8px; }
    .rpt-cmp-val { font-size:1.1rem; font-weight:700; color:#1e293b; }
    .rpt-cmp-val small { font-size:0.65rem; vertical-align:middle; margin-right:2px; }
    .rpt-cmp-diff { font-size:0.85rem; font-weight:700; padding:2px 8px; border-radius:6px; }
    .rpt-cmp-diff.up { background:#d1fae5; color:#059669; }
    .rpt-cmp-diff.down { background:#fee2e2; color:#dc2626; }
    .rpt-cmp-diff.neutral { background:#f1f5f9; color:#64748b; }

    /* Comparison tables */
    #tblCountry th, #tblType th { font-size:11px; text-transform:uppercase; letter-spacing:0.5px; padding:8px 10px; white-space:nowrap; }
    #tblCountry td, #tblType td { font-size:13px; padding:7px 10px; }
    .diff-up { color:#059669; font-weight:600; }
    .diff-down { color:#dc2626; font-weight:600; }
    .diff-zero { color:#94a3b8; }

<?php if ($isSingle) { ?>
    /* Date Range mode: show single period only, hide B column & diff */
    #reportDashboard .rpt-cmp-card .d-flex > div:not(:first-child) { display:none !important; }
    #reportDashboard .rpt-cmp-val small.badge { display:none; }
<?php } ?>
</style>

<script>
    const reportType = '<?php echo $reportType; ?>';
    const isSinglePeriod = <?php echo $isSingle ? 'true' : 'false'; ?>;
    const COLORS = ['#0361D1','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16','#f97316','#6366f1'];
    let chartCountry = null;
    let chartType = null;

    function getDateParams() {
        var a = document.getElementById('filterA').value;
        var b = document.getElementById('filterB').value;
        if (reportType === 'reportMonthly') { a += '-01'; b += '-01'; }
        if (reportType === 'reportYearly') { a += '-06-15'; b += '-06-15'; }
        return { a: a, b: b };
    }

    function getBaseUrl() {
        if (reportType === 'reportWeekly') return 'api/monday/selectWeek/table/makeTableReport.php';
        if (reportType === 'reportMonthly') return 'api/monday/selectMonth/table/makeTableReportMonth.php';
        if (reportType === 'reportYearly') return 'api/monday/selectYear/table/makeTableReportYear.php';
        if (reportType === 'reportDate') return 'api/monday/selectDate/table/makeTableReportDate.php';
        return '';
    }

    function fmtN(n) { return Number(n).toLocaleString(); }

    function diffEl(val, inverse) {
        var cls = val > 0 ? (inverse ? 'diff-down' : 'diff-up') : val < 0 ? (inverse ? 'diff-up' : 'diff-down') : 'diff-zero';
        var sign = val > 0 ? '+' : '';
        return '<span class="' + cls + '">' + sign + val + '</span>';
    }

    function setDiffCard(id, val, inverse) {
        var el = document.getElementById(id);
        el.textContent = (val > 0 ? '+' : '') + val;
        el.className = 'rpt-cmp-diff ' + (val > 0 ? (inverse ? 'down' : 'up') : val < 0 ? (inverse ? 'up' : 'down') : 'neutral');
    }

    function loadReport() {
        var params = getDateParams();
        var base = getBaseUrl();
        if (!base || !params.a || !params.b) return;

        document.getElementById('reportPlaceholder').style.display = 'none';
        document.getElementById('reportDashboard').style.display = 'none';
        document.getElementById('reportLoading').style.display = 'block';

        if (isSinglePeriod) {
            $.ajax({
                url: base + '?start=' + params.a + '&end=' + params.b + '&format=json',
                method: 'GET', dataType: 'json'
            }).done(function(d) {
                document.getElementById('reportLoading').style.display = 'none';
                document.getElementById('reportDashboard').style.display = 'block';
                renderSingleRange(d);
            }).fail(function(xhr, status, error) {
                document.getElementById('reportLoading').style.display = 'none';
                document.getElementById('reportDashboard').style.display = 'block';
                document.getElementById('tblCountry').querySelector('tbody').innerHTML = '<tr><td colspan="4" class="text-center text-danger py-3">Failed to load: ' + error + '</td></tr>';
            });
            return;
        }

        var reqA = $.ajax({ url: base + '?day=' + params.a + '&format=json', method:'GET', dataType:'json' });
        var reqB = $.ajax({ url: base + '?day=' + params.b + '&format=json', method:'GET', dataType:'json' });

        $.when(reqA, reqB).done(function(resA, resB) {
            var dA = resA[0], dB = resB[0];

            document.getElementById('reportLoading').style.display = 'none';
            document.getElementById('reportDashboard').style.display = 'block';

            // Period labels
            document.getElementById('periodLabelA').textContent = 'A: ' + dA.period.start.substring(0,10) + ' → ' + dA.period.end.substring(0,10);
            document.getElementById('periodLabelB').textContent = 'B: ' + dB.period.start.substring(0,10) + ' → ' + dB.period.end.substring(0,10);

            // Summary cards
            var tA = dA.totals, tB = dB.totals;
            document.getElementById('cardActiveA').textContent = fmtN(tA.active);
            document.getElementById('cardActiveB').textContent = fmtN(tB.active);
            setDiffCard('cardActiveDiff', tB.active - tA.active, false);

            document.getElementById('cardSignupA').textContent = fmtN(tA.signup);
            document.getElementById('cardSignupB').textContent = fmtN(tB.signup);
            setDiffCard('cardSignupDiff', tB.signup - tA.signup, false);

            document.getElementById('cardUnsubA').textContent = fmtN(tA.drop);
            document.getElementById('cardUnsubB').textContent = fmtN(tB.drop);
            setDiffCard('cardUnsubDiff', tB.drop - tA.drop, true);

            document.getElementById('cardNetA').textContent = (tA.percentChange >= 0 ? '+' : '') + tA.percentChange + '%';
            document.getElementById('cardNetB').textContent = (tB.percentChange >= 0 ? '+' : '') + tB.percentChange + '%';
            var netDiff = Math.round((tB.percentChange - tA.percentChange) * 100) / 100;
            setDiffCard('cardNetDiff', (netDiff >= 0 ? '+' : '') + netDiff + '%', false);

            // Country chart (grouped bar: A-Signup, B-Signup, A-Unsub, B-Unsub)
            renderCountryChart(dA.reportData, dB.reportData);

            // Customer Type chart (side-by-side doughnut replaced with grouped bar)
            renderTypeChart(dA, dB);

            // Country comparison table
            renderCountryTable(dA.reportData, dB.reportData);

            // Customer Type comparison table
            renderTypeTable(dA, dB);

        }).fail(function(xhr, status, error) {
            document.getElementById('reportLoading').style.display = 'none';
            document.getElementById('reportDashboard').style.display = 'block';
            document.getElementById('tblCountry').querySelector('tbody').innerHTML = '<tr><td colspan="9" class="text-center text-danger py-3">Failed to load: ' + error + '</td></tr>';
        });
    }

    function renderCountryChart(rdA, rdB) {
        var ctx = document.getElementById('chartCountry').getContext('2d');
        if (chartCountry) chartCountry.destroy();

        // Merge country labels
        var cMap = {};
        rdA.forEach(function(r) { cMap[r.country] = true; });
        rdB.forEach(function(r) { cMap[r.country] = true; });
        var labels = Object.keys(cMap).sort();

        function getVal(arr, country, key) {
            for (var i = 0; i < arr.length; i++) { if (arr[i].country === country) return arr[i][key]; }
            return 0;
        }

        chartCountry = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label:'A Signup', data: labels.map(function(c){ return getVal(rdA, c, 'signup'); }), backgroundColor:'rgba(3,97,209,0.7)', borderRadius:4 },
                    { label:'B Signup', data: labels.map(function(c){ return getVal(rdB, c, 'signup'); }), backgroundColor:'rgba(16,185,129,0.7)', borderRadius:4 },
                    { label:'A Unsub', data: labels.map(function(c){ return getVal(rdA, c, 'drop'); }), backgroundColor:'rgba(239,68,68,0.5)', borderRadius:4 },
                    { label:'B Unsub', data: labels.map(function(c){ return getVal(rdB, c, 'drop'); }), backgroundColor:'rgba(249,115,22,0.5)', borderRadius:4 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position:'top', labels:{ usePointStyle:true, padding:12, font:{size:10} } } },
                scales: { y: { beginAtZero:true, grid:{ color:'rgba(0,0,0,0.05)' } }, x: { grid:{ display:false } } }
            }
        });
    }

    function renderTypeChart(dA, dB) {
        var ctx = document.getElementById('chartType').getContext('2d');
        if (chartType) chartType.destroy();

        // Merge types from both periods
        var typeMap = {};
        var ctA = dA.customerType || {};
        var ctB = dB.customerType || {};
        Object.keys(ctA).forEach(function(k){ if(k!=='Unknown') typeMap[k]=true; });
        Object.keys(ctB).forEach(function(k){ if(k!=='Unknown') typeMap[k]=true; });
        var labels = Object.keys(typeMap).sort();

        if (labels.length === 0) {
            ctx.canvas.parentElement.innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-pie-chart" style="font-size:2rem;opacity:0.3;"></i><p class="mt-2">No data</p></div>';
            return;
        }

        chartType = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label:'A Active', data: labels.map(function(k){ return ctA[k]||0; }), backgroundColor:'rgba(3,97,209,0.7)', borderRadius:4 },
                    { label:'B Active', data: labels.map(function(k){ return ctB[k]||0; }), backgroundColor:'rgba(16,185,129,0.7)', borderRadius:4 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                plugins: { legend: { position:'top', labels:{ usePointStyle:true, padding:12, font:{size:10} } } },
                scales: { x: { beginAtZero:true } }
            }
        });
    }

    function renderCountryTable(rdA, rdB) {
        var cMap = {};
        rdA.forEach(function(r) { cMap[r.country] = { aActive:r.active, aSignup:r.signup, aDrop:r.drop }; });
        rdB.forEach(function(r) {
            if (!cMap[r.country]) cMap[r.country] = { aActive:0, aSignup:0, aDrop:0 };
            cMap[r.country].bActive = r.active;
            cMap[r.country].bSignup = r.signup;
            cMap[r.country].bDrop = r.drop;
        });

        var countries = Object.keys(cMap).sort();
        var html = '';
        var totA = {active:0,signup:0,drop:0}, totB = {active:0,signup:0,drop:0};

        countries.forEach(function(c) {
            var d = cMap[c];
            d.bActive = d.bActive || 0; d.bSignup = d.bSignup || 0; d.bDrop = d.bDrop || 0;
            totA.active += d.aActive; totA.signup += d.aSignup; totA.drop += d.aDrop;
            totB.active += d.bActive; totB.signup += d.bSignup; totB.drop += d.bDrop;
            var sDiff = d.bSignup - d.aSignup;
            var uDiff = d.bDrop - d.aDrop;
            html += '<tr><td><b>' + c + '</b></td>'
                + '<td class="text-center">' + fmtN(d.aActive) + '</td><td class="text-center">' + fmtN(d.aSignup) + '</td><td class="text-center">' + fmtN(d.aDrop) + '</td>'
                + '<td class="text-center">' + fmtN(d.bActive) + '</td><td class="text-center">' + fmtN(d.bSignup) + '</td><td class="text-center">' + fmtN(d.bDrop) + '</td>'
                + '<td class="text-center">' + diffEl(sDiff, false) + '</td><td class="text-center">' + diffEl(uDiff, true) + '</td></tr>';
        });

        // Total row
        var tsDiff = totB.signup - totA.signup, tuDiff = totB.drop - totA.drop;
        html += '<tr class="font-weight-bold" style="background:#f1f5f9;"><td>Total</td>'
            + '<td class="text-center">' + fmtN(totA.active) + '</td><td class="text-center">' + fmtN(totA.signup) + '</td><td class="text-center">' + fmtN(totA.drop) + '</td>'
            + '<td class="text-center">' + fmtN(totB.active) + '</td><td class="text-center">' + fmtN(totB.signup) + '</td><td class="text-center">' + fmtN(totB.drop) + '</td>'
            + '<td class="text-center">' + diffEl(tsDiff, false) + '</td><td class="text-center">' + diffEl(tuDiff, true) + '</td></tr>';

        document.getElementById('tblCountry').querySelector('tbody').innerHTML = html;
    }

    function renderTypeTable(dA, dB) {
        var sA = dA.signupByType || {}, uA = dA.unsubByType || {};
        var sB = dB.signupByType || {}, uB = dB.unsubByType || {};
        var typeMap = {};
        [sA, uA, sB, uB].forEach(function(obj) { Object.keys(obj).forEach(function(k){ if(k!=='Unknown') typeMap[k]=true; }); });
        var types = Object.keys(typeMap).sort();

        var html = '';
        var totSA=0, totUA=0, totSB=0, totUB=0;
        types.forEach(function(t) {
            var sa = sA[t]||0, ua = uA[t]||0, sb = sB[t]||0, ub = uB[t]||0;
            totSA += sa; totUA += ua; totSB += sb; totUB += ub;
            html += '<tr><td><b>' + t + '</b></td>'
                + '<td class="text-center">' + sa + '</td><td class="text-center">' + ua + '</td>'
                + '<td class="text-center">' + sb + '</td><td class="text-center">' + ub + '</td>'
                + '<td class="text-center">' + diffEl(sb-sa, false) + '</td><td class="text-center">' + diffEl(ub-ua, true) + '</td></tr>';
        });
        html += '<tr class="font-weight-bold" style="background:#f1f5f9;"><td>Total</td>'
            + '<td class="text-center">' + totSA + '</td><td class="text-center">' + totUA + '</td>'
            + '<td class="text-center">' + totSB + '</td><td class="text-center">' + totUB + '</td>'
            + '<td class="text-center">' + diffEl(totSB-totSA, false) + '</td><td class="text-center">' + diffEl(totUB-totUA, true) + '</td></tr>';

        document.getElementById('tblType').querySelector('tbody').innerHTML = html;
    }

    // ===== Single-range renderer (Date Range mode) =====
    function renderSingleRange(d) {
        // Period label
        document.getElementById('periodLabelA').textContent =
            d.period.start.substring(0,10) + ' → ' + d.period.end.substring(0,10);

        // Summary cards (only A values shown; B + diff hidden by CSS)
        var t = d.totals;
        document.getElementById('cardActiveA').textContent = fmtN(t.active);
        document.getElementById('cardSignupA').textContent = fmtN(t.signup);
        document.getElementById('cardUnsubA').textContent  = fmtN(t.drop);
        document.getElementById('cardNetA').textContent    = (t.percentChange >= 0 ? '+' : '') + t.percentChange + '%';

        // Country chart: Signup + Unsub per country
        var ctx = document.getElementById('chartCountry').getContext('2d');
        if (chartCountry) chartCountry.destroy();
        var rd = d.reportData || [];
        var labels = rd.map(function(r){ return r.country; });
        chartCountry = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label:'Signup', data: rd.map(function(r){ return r.signup; }), backgroundColor:'rgba(3,97,209,0.7)', borderRadius:4 },
                    { label:'Unsub',  data: rd.map(function(r){ return r.drop; }),   backgroundColor:'rgba(239,68,68,0.7)', borderRadius:4 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position:'top', labels:{ usePointStyle:true, padding:12, font:{size:10} } } },
                scales: { y: { beginAtZero:true, grid:{ color:'rgba(0,0,0,0.05)' } }, x: { grid:{ display:false } } }
            }
        });

        // Customer Type chart: Active by type
        var ctxT = document.getElementById('chartType').getContext('2d');
        if (chartType) chartType.destroy();
        var ct = d.customerType || {};
        var tLabels = Object.keys(ct).filter(function(k){ return k !== 'Unknown'; }).sort();
        if (tLabels.length === 0) {
            ctxT.canvas.parentElement.innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-pie-chart" style="font-size:2rem;opacity:0.3;"></i><p class="mt-2">No data</p></div>';
        } else {
            chartType = new Chart(ctxT, {
                type: 'bar',
                data: {
                    labels: tLabels,
                    datasets: [{ label:'Active', data: tLabels.map(function(k){ return ct[k]||0; }), backgroundColor:'rgba(16,185,129,0.7)', borderRadius:4 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                    plugins: { legend: { position:'top', labels:{ usePointStyle:true, padding:12, font:{size:10} } } },
                    scales: { x: { beginAtZero:true } }
                }
            });
        }

        // Country table: Country | Active | Signup | Unsub
        var html = '';
        var tot = { active:0, signup:0, drop:0 };
        rd.forEach(function(r) {
            tot.active += r.active; tot.signup += r.signup; tot.drop += r.drop;
            html += '<tr><td><b>' + r.country + '</b></td>'
                + '<td class="text-center">' + fmtN(r.active) + '</td>'
                + '<td class="text-center">' + fmtN(r.signup) + '</td>'
                + '<td class="text-center">' + fmtN(r.drop)   + '</td></tr>';
        });
        html += '<tr class="font-weight-bold" style="background:#f1f5f9;"><td>Total</td>'
            + '<td class="text-center">' + fmtN(tot.active) + '</td>'
            + '<td class="text-center">' + fmtN(tot.signup) + '</td>'
            + '<td class="text-center">' + fmtN(tot.drop)   + '</td></tr>';
        document.getElementById('tblCountry').querySelector('tbody').innerHTML = html;

        // Customer Type table: Type | Signup | Unsub
        var s = d.signupByType || {}, u = d.unsubByType || {};
        var typeMap = {};
        Object.keys(s).forEach(function(k){ if(k!=='Unknown') typeMap[k] = true; });
        Object.keys(u).forEach(function(k){ if(k!=='Unknown') typeMap[k] = true; });
        var types = Object.keys(typeMap).sort();
        var html2 = '', ts = 0, tu = 0;
        types.forEach(function(tn) {
            var sv = s[tn]||0, uv = u[tn]||0;
            ts += sv; tu += uv;
            html2 += '<tr><td><b>' + tn + '</b></td>'
                + '<td class="text-center">' + sv + '</td>'
                + '<td class="text-center">' + uv + '</td></tr>';
        });
        html2 += '<tr class="font-weight-bold" style="background:#f1f5f9;"><td>Total</td>'
            + '<td class="text-center">' + ts + '</td>'
            + '<td class="text-center">' + tu + '</td></tr>';
        document.getElementById('tblType').querySelector('tbody').innerHTML = html2;
    }
</script>
