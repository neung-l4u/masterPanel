<?php
// Revenue Report - Stripe Dashboard
?>
<div class="container-fluid stripe-dash">
    <!-- Account Tabs -->
    <ul class="nav stripe-tabs mb-0" id="stripeTabs">
        <li class="nav-item"><a class="nav-link active" href="#" data-account="au">Stripe AU</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-account="us">Stripe US</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-account="th">Stripe TH</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-account="connect">Stripe Connect</a></li>
    </ul>

    <!-- Loading -->
    <div id="stripeLoading" class="text-center py-5">
        <div class="spinner-border" style="width:2.5rem;height:2.5rem;color:#635bff;" role="status"></div>
        <p class="mt-3 text-muted">Loading Stripe data...</p>
    </div>

    <!-- Error -->
    <div id="stripeError" class="alert alert-danger mt-3" style="display:none;"></div>

    <!-- Dashboard -->
    <div id="stripeDashboard" style="display:none;">

        <!-- ========== TODAY ========== -->
        <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
            <h5 class="stripe-section-title">Today</h5>
        </div>
        <div class="row">
            <!-- Left: Gross volume + sparkline -->
            <div class="col-lg-7 mb-3">
                <div class="scard">
                    <div class="d-flex justify-content-between">
                        <div>
                            <span class="sc-label">Gross volume</span>
                            <div class="sc-big" id="todayGross">$0.00</div>
                            <span class="sc-sub" id="todayTime"></span>
                        </div>
                        <div class="text-right">
                            <span class="sc-label">Yesterday</span>
                            <div class="sc-mid" id="yesterdayGross">$0.00</div>
                        </div>
                    </div>
                    <div style="height:70px;margin-top:10px;"><canvas id="chartTodayGross"></canvas></div>
                </div>
            </div>
            <!-- Right: Balance + Payouts -->
            <div class="col-lg-5 mb-3">
                <div class="scard">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="sc-label"><span id="balanceCurrLabel">USD</span> balance</span>
                            <div class="sc-big" id="todayBalance">$0.00</div>
                        </div>
                    </div>
                    <div class="sc-divider"></div>
                    <div>
                        <span class="sc-label">Payouts</span>
                        <div class="sc-mid" id="todayPayouts">$0.00</div>
                        <span class="sc-sub" id="payoutExpected">Expected —</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== YOUR OVERVIEW ========== -->
        <div class="d-flex justify-content-between align-items-center mb-2 mt-2">
            <h5 class="stripe-section-title">Your overview</h5>
            <div class="d-flex align-items-center position-relative">
                <button class="btn btn-sm stripe-refresh-btn" id="btnRefresh" title="Refresh from Stripe (real-time)">
                    <i class="bi bi-arrow-clockwise" id="refreshIcon"></i> <span id="refreshLabel">Refresh</span>
                </button>
                <span class="sc-label mr-2 ml-3">Date range</span>
                <button class="btn btn-sm stripe-dp-toggle" id="dpToggle">
                    <span id="dpLabel">Last 7 days</span> <i class="bi bi-chevron-down ml-1" style="font-size:10px;"></i>
                </button>
                <!-- Datepicker Dropdown -->
                <div class="stripe-dp-dropdown" id="dpDropdown" style="display:none;">
                    <div class="d-flex">
                        <!-- Left: presets -->
                        <div class="stripe-dp-presets">
                            <div class="stripe-dp-preset active" data-days="7">Last 7 days</div>
                            <div class="stripe-dp-preset" data-days="28">Last 4 weeks</div>
                            <div class="stripe-dp-preset" data-days="90">Last 3 months</div>
                            <div class="stripe-dp-preset" data-days="180">Last 6 months</div>
                            <div class="stripe-dp-preset" data-days="365">Last 12 months</div>
                            <div class="stripe-dp-preset" data-days="mtd">Month to date</div>
                            <div class="stripe-dp-preset" data-days="qtd">Quarter to date</div>
                            <div class="stripe-dp-preset" data-days="ytd">Year to date</div>
                            <div class="stripe-dp-preset" data-days="all">All time</div>
                        </div>
                        <!-- Right: custom date inputs -->
                        <div class="stripe-dp-custom">
                            <div class="d-flex align-items-center mb-3">
                                <span class="sc-label mr-2">Start</span>
                                <input type="date" class="form-control form-control-sm stripe-dp-input" id="dpStart">
                                <span class="sc-label mx-2">End</span>
                                <input type="date" class="form-control form-control-sm stripe-dp-input" id="dpEnd">
                            </div>
                            <div class="text-right mt-2">
                                <button class="btn btn-sm btn-link text-muted" id="dpClear">Clear</button>
                                <button class="btn btn-sm btn-primary rounded-pill px-3" id="dpApply">Apply</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 1: Payments -->
        <div class="row">
            <!-- Payments -->
            <div class="col-lg-4 mb-3">
                <div class="scard scard-full">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="sc-card-title">Payments</span>
                    </div>
                    <!-- Stacked bar -->
                    <div class="stripe-stackbar mb-3">
                        <div class="stripe-stackbar-fill" id="payBar" style="width:100%;"></div>
                    </div>
                    <div class="sc-legend">
                        <div class="d-flex justify-content-between mb-1">
                            <span><span class="scdot" style="background:#635bff;"></span>Succeeded</span>
                            <strong id="paySucceeded">$0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span><span class="scdot" style="background:#3b82f6;"></span>Uncaptured</span>
                            <span id="payUncaptured">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span><span class="scdot" style="background:#06b6d4;"></span>Refunded</span>
                            <span id="payRefunded">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span><span class="scdot" style="background:#f59e0b;"></span>Blocked</span>
                            <span id="payBlocked">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span><span class="scdot" style="background:#ef4444;"></span>Failed</span>
                            <span id="payFailed">$0.00</span>
                        </div>
                    </div>
                    <div class="sc-footer" id="payUpdated">Updated —</div>
                </div>
            </div>
            <!-- Active subscriptions -->
            <div class="col-lg-4 mb-3">
                <div class="scard scard-full">
                    <span class="sc-card-title">Active subscriptions</span>
                    <div class="sc-big mt-1" id="activeSubs">0</div>
                    <div class="sc-sub" id="activeSubsPrev">0 previous period</div>
                </div>
            </div>
            <!-- MRR -->
            <div class="col-lg-4 mb-3">
                <div class="scard scard-full">
                    <span class="sc-card-title">MRR</span>
                    <div class="d-flex align-items-baseline">
                        <div class="sc-big mt-1" id="mrrTotal">$0.00</div>
                        <span class="sc-change-up ml-2" id="mrrChange">+0%</span>
                    </div>
                    <div class="sc-sub" id="mrrPrevious">$0.00 previous period</div>
                </div>
            </div>
        </div>

        <!-- Row 2: New customers | Top customers -->
        <div class="row">
            <!-- New customers -->
            <div class="col-lg-4 mb-3">
                <div class="scard scard-full">
                    <span class="sc-card-title">New customers</span>
                    <div class="d-flex align-items-baseline">
                        <div class="sc-big mt-1" id="newCustCount">0</div>
                        <span class="sc-change-up ml-2" id="newCustChange">+0%</span>
                    </div>
                    <div class="sc-sub" id="newCustPrev">0 previous period</div>
                    <div style="height:80px;margin:10px 0 4px;"><canvas id="chartNewCustomers"></canvas></div>
                    <div class="d-flex justify-content-between sc-axis">
                        <span id="custAxisStart"></span><span id="custAxisEnd"></span>
                    </div>
                    <div class="sc-footer" id="newCustUpdated">Updated —</div>
                </div>
            </div>
            <!-- Top customers -->
            <div class="col-lg-4 mb-3">
                <div class="scard scard-full">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="sc-card-title">Top customers by spend</span>
                        <span class="sc-sub">Recent</span>
                    </div>
                    <div id="topCustList">
                        <div class="text-muted" style="font-size:13px;">No data</div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /#stripeDashboard -->
</div>

<style>
.stripe-dash { background:#f6f8fa; margin:-15px; padding:0 15px 15px; }

/* Tabs */
.stripe-tabs { border-bottom:1px solid #e3e8ee; background:#fff; margin:0 -15px; padding:0 15px; }
.stripe-tabs .nav-link {
    border:none; border-radius:0; padding:10px 16px; color:#6c757d; font-weight:500; font-size:13px;
    border-bottom:2px solid transparent; transition:all .15s;
}
.stripe-tabs .nav-link:hover { color:#635bff; }
.stripe-tabs .nav-link.active { color:#635bff; border-bottom-color:#635bff; font-weight:600; }

/* Section titles */
.stripe-section-title { font-size:18px; font-weight:600; color:#1a1a2e; margin:0; }

/* Cards */
.scard { background:#fff; border:1px solid #e3e8ee; border-radius:10px; padding:18px 20px; }
.scard-full { height:100%; display:flex; flex-direction:column; }

/* Typography */
.sc-label { font-size:12px; color:#697386; }
.sc-big { font-size:24px; font-weight:700; color:#1a1a2e; line-height:1.2; }
.sc-mid { font-size:16px; font-weight:600; color:#1a1a2e; }
.sc-sub { font-size:11px; color:#8792a2; }
.sc-link { font-size:12px; color:#635bff; cursor:pointer; font-weight:500; }
.sc-link:hover { text-decoration:underline; }
.sc-card-title { font-size:13px; font-weight:600; color:#1a1a2e; }
.sc-footer { font-size:10px; color:#8792a2; margin-top:auto; padding-top:8px; }
.sc-axis { font-size:10px; color:#8792a2; }
.sc-divider { border-top:1px solid #e3e8ee; margin:12px 0; }
.sc-change-up { font-size:12px; font-weight:600; color:#0e6245; }
.sc-change-down { font-size:12px; font-weight:600; color:#cd3d64; }

/* Legend */
.sc-legend { font-size:13px; color:#3c4257; }
.scdot { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:8px; vertical-align:middle; }

/* Stacked bar */
.stripe-stackbar { height:8px; background:#e3e8ee; border-radius:4px; overflow:hidden; }
.stripe-stackbar-fill { height:100%; background:#635bff; border-radius:4px; transition:width .3s; }

/* Datepicker toggle */
.stripe-refresh-btn {
    font-size:12px; padding:5px 14px; border:1px solid #635bff; color:#635bff; background:#fff;
    font-weight:500; border-radius:6px; cursor:pointer; white-space:nowrap; transition:all .15s;
}
.stripe-refresh-btn:hover { background:#635bff; color:#fff; }
.stripe-refresh-btn:disabled { opacity:0.6; cursor:not-allowed; }
.stripe-refresh-btn .bi-arrow-clockwise.spinning { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }

.stripe-dp-toggle {
    font-size:12px; padding:5px 14px; border:1px solid #e3e8ee; color:#3c4257; background:#fff;
    font-weight:500; border-radius:6px; cursor:pointer; white-space:nowrap;
}
.stripe-dp-toggle:hover { background:#f0f0f5; }

/* Datepicker dropdown */
.stripe-dp-dropdown {
    position:absolute; top:100%; right:0; z-index:999; margin-top:6px;
    background:#fff; border:1px solid #e3e8ee; border-radius:10px;
    box-shadow:0 8px 32px rgba(0,0,0,0.12); min-width:480px;
}
.stripe-dp-presets {
    border-right:1px solid #e3e8ee; padding:12px 0; min-width:160px;
}
.stripe-dp-preset {
    padding:7px 18px; font-size:13px; color:#3c4257; cursor:pointer; white-space:nowrap;
}
.stripe-dp-preset:hover { background:#f6f8fa; }
.stripe-dp-preset.active { background:#f0edff; color:#635bff; font-weight:600; }
.stripe-dp-custom { padding:16px 18px; flex:1; }
.stripe-dp-input { font-size:12px; border:1px solid #e3e8ee; border-radius:6px; padding:4px 8px; max-width:140px; }

/* Failed payment row */
.fp-row { border-bottom:1px solid #f0f0f5; padding:8px 0; }
.fp-row:last-child { border-bottom:none; }
.fp-amount { font-size:14px; font-weight:600; color:#1a1a2e; }
.fp-meta { font-size:11px; color:#8792a2; }
.fp-badge { font-size:10px; padding:2px 8px; border-radius:4px; background:#fee2e2; color:#dc2626; font-weight:600; }

/* Top customer row */
.tc-row { border-bottom:1px solid #f0f0f5; padding:8px 0; }
.tc-row:last-child { border-bottom:none; }
.tc-name { font-size:13px; font-weight:500; color:#1a1a2e; }
.tc-email { font-size:11px; color:#8792a2; }
.tc-amount { font-size:13px; font-weight:600; color:#1a1a2e; text-align:right; white-space:nowrap; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
window.addEventListener('load', function(){
    var currentAccount = 'au';
    var currentDays = 7;
    var currentCur = 'AUD';
    var charts = {};

    function fmtMoney(amount, currency) {
        var sym = currency === 'USD' ? '$' : currency === 'THB' ? '฿' : currency === 'AUD' ? 'A$' : '$';
        return sym + Number(amount).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    var curSym = '$';
    function fmtK(v) {
        if (v >= 1000) return curSym + (v/1000).toFixed(1) + 'K';
        return curSym + v.toFixed(0);
    }
    function pctText(v) {
        var prefix = v >= 0 ? '+' : '';
        return prefix + v + '%';
    }

    function destroyChart(id) { if (charts[id]) { charts[id].destroy(); delete charts[id]; } }

    function makeLineChart(canvasId, labels, data, color) {
        destroyChart(canvasId);
        var ctx = document.getElementById(canvasId);
        if (!ctx) return;
        charts[canvasId] = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    borderColor: color || '#635bff',
                    backgroundColor: 'rgba(99,91,255,0.05)',
                    borderWidth: 2, pointRadius: 0, pointHoverRadius: 4, tension: 0.3, fill: true
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: {
                    backgroundColor: '#1a1a2e', titleFont: { size: 11 }, bodyFont: { size: 11 },
                    padding: 8, cornerRadius: 6, displayColors: false,
                    callbacks: { label: function(c) { return fmtMoney(c.parsed.y, currentCur); } }
                }},
                scales: {
                    x: { display: false },
                    y: { display: true, position: 'right', grid: { drawBorder: false, color: '#f0f0f5' },
                        ticks: { font: { size: 10 }, color: '#8792a2', callback: function(v) { return fmtK(v); }, maxTicksLimit: 4 }
                    }
                }
            }
        });
    }

    function makeBarChart(canvasId, labels, data, color) {
        destroyChart(canvasId);
        var ctx = document.getElementById(canvasId);
        if (!ctx) return;
        charts[canvasId] = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: color || '#635bff',
                    borderRadius: 3, barPercentage: 0.6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: {
                    backgroundColor: '#1a1a2e', titleFont: { size: 11 }, bodyFont: { size: 11 },
                    padding: 8, cornerRadius: 6, displayColors: false
                }},
                scales: {
                    x: { display: false },
                    y: { display: false, beginAtZero: true }
                }
            }
        });
    }

    var customStart = '';
    var customEnd = '';

    function loadStripeData(account, days, start, end) {
        currentAccount = account;
        if (days) currentDays = days;
        if (start) customStart = start;
        if (end) customEnd = end;
        document.getElementById('stripeDashboard').style.display = 'none';
        document.getElementById('stripeError').style.display = 'none';
        document.getElementById('stripeLoading').style.display = '';

        var params = { account: currentAccount };
        if (customStart && customEnd) {
            params.start = customStart;
            params.end = customEnd;
        } else {
            params.days = currentDays;
        }

        $.ajax({
            url: 'api/stripe/getData.php',
            data: params,
            dataType: 'json',
            timeout: 300000
        })
        .done(function(d) {
            document.getElementById('stripeLoading').style.display = 'none';
            if (d.error) {
                document.getElementById('stripeError').textContent = d.error;
                document.getElementById('stripeError').style.display = '';
                return;
            }
            document.getElementById('stripeDashboard').style.display = '';
            renderStripeData(d);
        })
        .fail(function() {
            document.getElementById('stripeLoading').style.display = 'none';
            document.getElementById('stripeError').textContent = 'Failed to connect to Stripe API.';
            document.getElementById('stripeError').style.display = '';
        });
    }

    function renderStripeData(d) {
        var cur = d.balance.currency;
        currentCur = cur;
        curSym = cur === 'USD' ? '$' : cur === 'THB' ? '฿' : cur === 'AUD' ? 'A$' : '$';

        // ---- TODAY ----
        document.getElementById('todayGross').textContent = fmtMoney(d.today.gross, cur);
        document.getElementById('todayTime').textContent = d.today.time;
        document.getElementById('yesterdayGross').textContent = fmtMoney(d.today.yesterday, cur);
        document.getElementById('todayBalance').textContent = fmtMoney(d.balance.pending, cur);
        document.getElementById('balanceCurrLabel').textContent = cur;
        document.getElementById('todayPayouts').textContent = fmtMoney(d.nextPayout || 0, cur);
        document.getElementById('payoutExpected').textContent = d.payoutExpectedDate ? ('Expected ' + d.payoutExpectedDate) : '';

        // Today sparkline (hourly mock from gross)
        var tg = d.today.gross;
        var sparkData = [0, tg*0.1, tg*0.2, tg*0.25, tg*0.3, tg*0.4, tg*0.5, tg*0.55, tg*0.6, tg*0.7, tg*0.8, tg*0.9, tg];
        var sparkLabels = ['12AM','','','','','','6AM','','','','','','12PM'];
        makeLineChart('chartTodayGross', sparkLabels, sparkData, '#aaa');

        // ---- PAYMENTS ----
        var pay = d.payments;
        var payTotal = pay.succeeded + pay.failed + pay.blocked + pay.uncaptured + pay.refunded;
        var barW = payTotal > 0 ? Math.max(5, (pay.succeeded / payTotal) * 100) : 100;
        document.getElementById('payBar').style.width = barW + '%';
        document.getElementById('paySucceeded').textContent = fmtMoney(pay.succeeded, cur);
        document.getElementById('payUncaptured').textContent = fmtMoney(pay.uncaptured, cur);
        document.getElementById('payRefunded').textContent = fmtMoney(pay.refunded, cur);
        document.getElementById('payBlocked').textContent = fmtMoney(pay.blocked, cur);
        document.getElementById('payFailed').textContent = fmtMoney(pay.failed, cur);
        var updatedText = 'Updated —';
        if (d.cached_at) {
            updatedText = 'Updated ' + d.cached_at;
            if (d.debug && d.debug.source) {
                updatedText += ' (' + d.debug.source + ')';
            }
        }
        document.getElementById('payUpdated').textContent = updatedText;

        // ---- SUBS / MRR ----
        document.getElementById('activeSubs').textContent = d.subscriptions.active_count;
        document.getElementById('mrrTotal').textContent = fmtMoney(d.mrr.total, cur);
        var mEl = document.getElementById('mrrChange');
        mEl.textContent = pctText(d.mrr.change);
        mEl.className = d.mrr.change >= 0 ? 'sc-change-up ml-2' : 'sc-change-down ml-2';
        document.getElementById('mrrPrevious').textContent = fmtMoney(d.mrr.previous, cur) + ' previous period';

        // ---- NEW CUSTOMERS ----
        var nc = d.newCustomers;
        document.getElementById('newCustCount').textContent = nc.count;
        var ncEl = document.getElementById('newCustChange');
        ncEl.textContent = pctText(nc.change);
        ncEl.className = nc.change >= 0 ? 'sc-change-up ml-2' : 'sc-change-down ml-2';
        document.getElementById('newCustPrev').textContent = nc.previous + ' previous period';
        document.getElementById('newCustUpdated').textContent = updatedText;
        var cLabels = Object.keys(nc.daily);
        var cData = Object.values(nc.daily);
        document.getElementById('custAxisStart').textContent = cLabels[0] || '';
        document.getElementById('custAxisEnd').textContent = cLabels[cLabels.length-1] || '';
        makeBarChart('chartNewCustomers', cLabels, cData, '#635bff');

        // ---- TOP CUSTOMERS ----
        var tcHtml = '';
        if (d.topCustomers.length === 0) {
            tcHtml = '<div class="text-muted py-2" style="font-size:13px;">No customer data</div>';
        } else {
            d.topCustomers.forEach(function(tc) {
                tcHtml += '<div class="tc-row d-flex justify-content-between align-items-center">'
                    + '<div><div class="tc-name">' + tc.name + '</div>'
                    + '<div class="tc-email">' + tc.email + '</div></div>'
                    + '<div class="tc-amount">' + fmtMoney(tc.amount, cur) + '</div></div>';
            });
        }
        document.getElementById('topCustList').innerHTML = tcHtml;
    }

    // Tab clicks
    document.querySelectorAll('#stripeTabs .nav-link').forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('#stripeTabs .nav-link').forEach(function(t) { t.classList.remove('active'); });
            this.classList.add('active');
            loadStripeData(this.getAttribute('data-account'));
        });
    });

    // ===== DATEPICKER =====
    var dpDropdown = document.getElementById('dpDropdown');
    var dpToggle = document.getElementById('dpToggle');
    var dpLabel = document.getElementById('dpLabel');
    var dpStartInput = document.getElementById('dpStart');
    var dpEndInput = document.getElementById('dpEnd');

    // Toggle dropdown
    dpToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        dpDropdown.style.display = dpDropdown.style.display === 'none' ? '' : 'none';
    });

    // Close on outside click
    document.addEventListener('click', function(e) {
        if (!dpDropdown.contains(e.target) && e.target !== dpToggle) {
            dpDropdown.style.display = 'none';
        }
    });

    // Helper: format date as YYYY-MM-DD
    function fmtDate(d) {
        return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
    }

    // Calculate start date from preset
    function getPresetDates(val) {
        var now = new Date();
        var start, end = fmtDate(now);
        if (val === 'mtd') {
            start = fmtDate(new Date(now.getFullYear(), now.getMonth(), 1));
        } else if (val === 'qtd') {
            var qMonth = Math.floor(now.getMonth() / 3) * 3;
            start = fmtDate(new Date(now.getFullYear(), qMonth, 1));
        } else if (val === 'ytd') {
            start = fmtDate(new Date(now.getFullYear(), 0, 1));
        } else if (val === 'all') {
            start = '2020-01-01';
        } else {
            var d = new Date();
            d.setDate(d.getDate() - parseInt(val));
            start = fmtDate(d);
        }
        return { start: start, end: end };
    }

    // Preset clicks
    document.querySelectorAll('.stripe-dp-preset').forEach(function(el) {
        el.addEventListener('click', function() {
            document.querySelectorAll('.stripe-dp-preset').forEach(function(p) { p.classList.remove('active'); });
            this.classList.add('active');
            var val = this.getAttribute('data-days');
            var label = this.textContent;
            dpLabel.textContent = label;

            var dates = getPresetDates(val);
            dpStartInput.value = dates.start;
            dpEndInput.value = dates.end;

            // Clear custom and use days for numeric presets
            if (['mtd','qtd','ytd','all'].indexOf(val) >= 0) {
                customStart = dates.start;
                customEnd = dates.end;
                currentDays = 0;
            } else {
                customStart = '';
                customEnd = '';
                currentDays = parseInt(val);
            }

            dpDropdown.style.display = 'none';
            loadStripeData(currentAccount);
        });
    });

    // Apply custom dates
    document.getElementById('dpApply').addEventListener('click', function() {
        var s = dpStartInput.value;
        var e = dpEndInput.value;
        if (!s || !e) return;
        customStart = s;
        customEnd = e;
        currentDays = 0;
        // Update label
        var sd = new Date(s + 'T00:00:00');
        var ed = new Date(e + 'T00:00:00');
        dpLabel.textContent = sd.toLocaleDateString('en', {month:'short',day:'numeric'}) + ' – ' + ed.toLocaleDateString('en', {month:'short',day:'numeric',year:'numeric'});
        // Deselect presets
        document.querySelectorAll('.stripe-dp-preset').forEach(function(p) { p.classList.remove('active'); });
        dpDropdown.style.display = 'none';
        loadStripeData(currentAccount);
    });

    // Clear
    document.getElementById('dpClear').addEventListener('click', function() {
        dpStartInput.value = '';
        dpEndInput.value = '';
        customStart = '';
        customEnd = '';
        currentDays = 7;
        dpLabel.textContent = 'Last 7 days';
        document.querySelectorAll('.stripe-dp-preset').forEach(function(p) { p.classList.remove('active'); });
        document.querySelector('.stripe-dp-preset[data-days="7"]').classList.add('active');
        dpDropdown.style.display = 'none';
        loadStripeData(currentAccount);
    });

    // Set initial date inputs
    var initDates = getPresetDates('7');
    dpStartInput.value = initDates.start;
    dpEndInput.value = initDates.end;

    // ===== REFRESH BUTTON =====
    function getCurrentPeriodName() {
        var daysMap = {7:'7days', 28:'28days', 90:'90days', 180:'180days', 365:'365days'};
        if (customStart && customEnd) return 'alltime'; // custom range -> refresh alltime
        if (currentDays > 365) return 'alltime';
        return daysMap[currentDays] || '7days';
    }

    document.getElementById('btnRefresh').addEventListener('click', function() {
        var btn = this;
        var icon = document.getElementById('refreshIcon');
        var label = document.getElementById('refreshLabel');
        var period = getCurrentPeriodName();

        btn.disabled = true;
        icon.classList.add('spinning');
        label.textContent = 'Refreshing...';

        $.getJSON('api/stripe/refreshCache.php', { account: currentAccount, period: period })
        .done(function(d) {
            if (d.error) {
                alert('Refresh error: ' + d.error);
            } else {
                // Render the fresh data directly
                renderStripeData(d);
                var sec = d.debug ? d.debug.time_seconds : '?';
                label.textContent = 'Done (' + sec + 's)';
                setTimeout(function() { label.textContent = 'Refresh'; }, 3000);
            }
        })
        .fail(function(xhr) {
            alert('Refresh failed: ' + (xhr.statusText || 'timeout'));
        })
        .always(function() {
            btn.disabled = false;
            icon.classList.remove('spinning');
        });
    });

    // Auto-load
    loadStripeData('au', 7);
});
</script>
