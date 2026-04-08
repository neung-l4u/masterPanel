<?php
global $db;
$loginID = $_SESSION['id'];
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0"><i class="bi bi-google"></i> Google Analytics</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item"><a href="main.php?p=reportWeekly">Report</a></li>
                    <li class="breadcrumb-item active">Google Analytics</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container-fluid">

        <!-- Period Filter -->
        <div class="card mb-3">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-graph-up mr-1"></i> Analytics Overview</h5>
                <div class="d-inline-flex align-items-center">
                    <label class="mr-2 mb-0 text-sm">Period:</label>
                    <select id="gaPeriod" class="form-control form-control-sm" style="width:150px;">
                        <option value="7days">Last 7 Days</option>
                        <option value="14days">Last 14 Days</option>
                        <option value="30days" selected>Last 30 Days</option>
                        <option value="90days">Last 90 Days</option>
                    </select>
                    <button class="btn btn-primary btn-sm ml-2 px-3" id="btnGaLoad">
                        <i class="bi bi-arrow-clockwise mr-1"></i> Load
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div id="gaLoading" class="text-center py-5" style="display:none;">
            <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status"></div>
            <p class="mt-3 text-muted">Loading analytics data...</p>
        </div>

        <!-- Error -->
        <div id="gaError" class="alert alert-danger" style="display:none;"></div>

        <!-- Dashboard -->
        <div id="gaDashboard" style="display:none;">

            <!-- Summary Cards -->
            <div class="row mb-3">
                <div class="col-6 col-lg-2">
                    <div class="ga-card ga-card-blue">
                        <div class="ga-card-val" id="gaUsers">0</div>
                        <div class="ga-card-label">Active Users</div>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="ga-card ga-card-green">
                        <div class="ga-card-val" id="gaNewUsers">0</div>
                        <div class="ga-card-label">New Users</div>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="ga-card ga-card-purple">
                        <div class="ga-card-val" id="gaSessions">0</div>
                        <div class="ga-card-label">Sessions</div>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="ga-card ga-card-cyan">
                        <div class="ga-card-val" id="gaPageViews">0</div>
                        <div class="ga-card-label">Page Views</div>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="ga-card ga-card-orange">
                        <div class="ga-card-val" id="gaAvgDuration">0s</div>
                        <div class="ga-card-label">Avg Duration</div>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="ga-card ga-card-red">
                        <div class="ga-card-val" id="gaBounceRate">0%</div>
                        <div class="ga-card-label">Bounce Rate</div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 1: Daily Trend -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title"><i class="bi bi-graph-up mr-1"></i> Users & Sessions Over Time</h3></div>
                        <div class="card-body" style="height:300px;">
                            <canvas id="chartDaily"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2: Country + Traffic + Devices -->
            <div class="row mb-3">
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title"><i class="bi bi-globe mr-1"></i> Users by Country</h3></div>
                        <div class="card-body" style="height:300px;">
                            <canvas id="chartCountryGA"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title"><i class="bi bi-signpost-2 mr-1"></i> Traffic Sources</h3></div>
                        <div class="card-body" style="height:300px;">
                            <canvas id="chartTraffic"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title"><i class="bi bi-phone mr-1"></i> Devices</h3></div>
                        <div class="card-body" style="height:300px;">
                            <canvas id="chartDevices"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Pages Table -->
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title"><i class="bi bi-file-earmark-text mr-1"></i> Top Pages</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0" id="gaTopPages">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>Page Path</th>
                                <th class="text-center" style="width:120px;">Page Views</th>
                                <th class="text-center" style="width:120px;">Users</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div><!-- /#gaDashboard -->
    </div>
</div>

<style>
.ga-card { border-radius:10px; padding:16px 18px; color:#fff; margin-bottom:10px; }
.ga-card-val { font-size:1.6rem; font-weight:700; line-height:1.2; }
.ga-card-label { font-size:0.75rem; opacity:0.85; margin-top:3px; }
.ga-card-blue   { background:linear-gradient(135deg,#0361D1,#00BCF4); }
.ga-card-green  { background:linear-gradient(135deg,#10b981,#34d399); }
.ga-card-purple { background:linear-gradient(135deg,#8b5cf6,#a78bfa); }
.ga-card-cyan   { background:linear-gradient(135deg,#06b6d4,#22d3ee); }
.ga-card-orange { background:linear-gradient(135deg,#f59e0b,#fbbf24); }
.ga-card-red    { background:linear-gradient(135deg,#ef4444,#f87171); }

#gaTopPages thead th { background:#1e293b; color:#fff; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; padding:10px 14px; }
#gaTopPages tbody td { font-size:13px; padding:8px 14px; }
#gaTopPages tbody tr:hover td { background:#f0f7ff; }
</style>

<script>
window.addEventListener('load', function(){
    var COLORS = ['#0361D1','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16','#f97316','#6366f1'];
    var chartDaily = null, chartCountry = null, chartTraffic = null, chartDevices = null;

    function fmtNum(n) {
        return Number(n).toLocaleString();
    }

    function fmtDuration(sec) {
        sec = parseInt(sec);
        if (sec < 60) return sec + 's';
        var m = Math.floor(sec / 60);
        var s = sec % 60;
        return m + 'm ' + s + 's';
    }

    function destroyChart(c) { if (c) c.destroy(); return null; }

    function escHtml(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

    function loadGA() {
        var period = document.getElementById('gaPeriod').value;
        document.getElementById('gaLoading').style.display = '';
        document.getElementById('gaDashboard').style.display = 'none';
        document.getElementById('gaError').style.display = 'none';

        $.getJSON('api/ga/getData.php', { period: period })
        .done(function(data) {
            document.getElementById('gaLoading').style.display = 'none';

            if (data.error) {
                var errEl = document.getElementById('gaError');
                errEl.textContent = data.error;
                errEl.style.display = '';
                return;
            }

            document.getElementById('gaDashboard').style.display = '';

            // Summary
            var s = data.summary;
            document.getElementById('gaUsers').textContent = fmtNum(s.activeUsers);
            document.getElementById('gaNewUsers').textContent = fmtNum(s.newUsers);
            document.getElementById('gaSessions').textContent = fmtNum(s.sessions);
            document.getElementById('gaPageViews').textContent = fmtNum(s.pageViews);
            document.getElementById('gaAvgDuration').textContent = fmtDuration(s.avgDuration);
            document.getElementById('gaBounceRate').textContent = s.bounceRate + '%';

            // Daily chart
            var dailyLabels = [], dailyUsers = [], dailySessions = [];
            for (var i = 0; i < data.daily.length; i++) {
                dailyLabels.push(data.daily[i].date);
                dailyUsers.push(data.daily[i].users);
                dailySessions.push(data.daily[i].sessions);
            }
            chartDaily = destroyChart(chartDaily);
            chartDaily = new Chart(document.getElementById('chartDaily').getContext('2d'), {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: [
                        { label: 'Users', data: dailyUsers, borderColor: '#0361D1', backgroundColor: 'rgba(3,97,209,0.1)', fill: true, tension: 0.3 },
                        { label: 'Sessions', data: dailySessions, borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.1)', fill: true, tension: 0.3 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' } },
                    scales: { x: { ticks: { maxRotation: 45, font: { size: 10 } } }, y: { beginAtZero: true } }
                }
            });

            // Country chart (horizontal bar)
            var cLabels = [], cValues = [];
            for (var i = 0; i < data.byCountry.length; i++) { cLabels.push(data.byCountry[i].country); cValues.push(data.byCountry[i].users); }
            chartCountry = destroyChart(chartCountry);
            chartCountry = new Chart(document.getElementById('chartCountryGA').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: cLabels,
                    datasets: [{ label: 'Users', data: cValues, backgroundColor: COLORS.slice(0, cLabels.length) }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true } }
                }
            });

            // Traffic sources (doughnut)
            var tLabels = [], tValues = [];
            for (var i = 0; i < data.trafficSources.length; i++) { tLabels.push(data.trafficSources[i].source); tValues.push(data.trafficSources[i].sessions); }
            chartTraffic = destroyChart(chartTraffic);
            chartTraffic = new Chart(document.getElementById('chartTraffic').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: tLabels,
                    datasets: [{ data: tValues, backgroundColor: COLORS.slice(0, tLabels.length), borderWidth: 2, borderColor: '#fff' }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '50%',
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 6, font: { size: 10 } } } }
                }
            });

            // Devices (doughnut)
            var dLabels = [], dValues = [];
            for (var i = 0; i < data.devices.length; i++) { dLabels.push(data.devices[i].device); dValues.push(data.devices[i].users); }
            chartDevices = destroyChart(chartDevices);
            chartDevices = new Chart(document.getElementById('chartDevices').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: dLabels,
                    datasets: [{ data: dValues, backgroundColor: ['#0361D1','#10b981','#f59e0b'], borderWidth: 2, borderColor: '#fff' }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '50%',
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 6, font: { size: 10 } } } }
                }
            });

            // Top pages table
            var tbody = '';
            for (var i = 0; i < data.topPages.length; i++) {
                var p = data.topPages[i];
                tbody += '<tr><td>' + (i+1) + '</td><td><code>' + escHtml(p.page) + '</code></td><td class="text-center">' + fmtNum(p.pageViews) + '</td><td class="text-center">' + fmtNum(p.users) + '</td></tr>';
            }
            document.getElementById('gaTopPages').querySelector('tbody').innerHTML = tbody;
        })
        .fail(function() {
            document.getElementById('gaLoading').style.display = 'none';
            var errEl = document.getElementById('gaError');
            errEl.textContent = 'Failed to connect to analytics API.';
            errEl.style.display = '';
        });
    }

    document.getElementById('btnGaLoad').addEventListener('click', loadGA);

    // Auto-load
    loadGA();
});
</script>
