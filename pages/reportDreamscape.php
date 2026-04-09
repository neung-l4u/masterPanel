<?php
global $db;
$loginID = $_SESSION['id'];

$period = isset($_GET['period']) ? $_GET['period'] : 'month';

if ($period === 'custom') {
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
} elseif ($period === 'week') {
    $startDate = date('Y-m-d', strtotime('monday this week'));
    $endDate = date('Y-m-d');
} elseif ($period === 'month') {
    $startDate = date('Y-m-01');
    $endDate = date('Y-m-d');
} elseif ($period === 'year') {
    $startDate = date('Y-01-01');
    $endDate = date('Y-m-d');
} else {
    $startDate = date('Y-m-01');
    $endDate = date('Y-m-d');
}
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="m-0"><i class="bi bi-graph-up-arrow"></i> Dreamscape Report</h1>
                </div>
                <ol class="breadcrumb mt-2">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item">Report</li>
                    <li class="breadcrumb-item active">Dreamscape</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="dreamscapeLoading" class="dreamscape-loading-overlay">
    <div class="dreamscape-loading-content">
        <div class="spinner-border text-info" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">Loading...</span>
        </div>
        <p class="mt-3 text-muted font-weight-bold">Loading Dreamscape Data...</p>
        <small class="text-muted">Fetching domains, invoices & sales data</small>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Summary Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold mb-0">Summary</h3>
                        <small class="text-muted" id="summaryDateRange">
                            <?php 
                            if ($period === 'custom') {
                                echo date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate));
                            } else {
                                echo ucfirst($period) . ': ' . date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate));
                            }
                            ?>
                        </small>
                    </div>
                    <div class="card-body">
                        <!-- Summary Cards Row 1 -->
                        <div class="row mb-3 justify-content-center">
                            <!-- Domains Card - Centered 60% Width -->
                            <div class="col-lg-7 col-12 mb-3">
                                <div class="summary-card bg-info">
                                    <div class="row">
                                        <div class="col-lg-4 col-12">
                                            <div class="text-center">
                                                <h1 class="display-3 font-weight-bold" id="domainTotal"><span class="skeleton-text">---</span></h1>
                                                <h5 class="text-uppercase">DOMAINS</h5>
                                            </div>
                                        </div>
                                        <div class="col-lg-8 col-12">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Pending Approval</span>
                                                <span class="font-weight-bold" id="domainPending"><span class="skeleton-text">--</span></span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Transfers</span>
                                                <span class="font-weight-bold" id="domainTransfers"><span class="skeleton-text">--</span></span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Renewal Due</span>
                                                <span class="font-weight-bold" id="domainRenewal"><span class="skeleton-text">--</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sales Summary with Chart -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">Sales Summary</h5>
                                    <!-- <select class="form-control" style="width: 200px;" id="chartPeriod">
                                        <option value="monthly">Monthly Sales</option>
                                        <option value="weekly">Weekly Sales</option>
                                        <option value="daily">Daily Sales</option>
                                    </select> -->
                                </div>
                                
                                <div class="row">
                                    <!-- Sales Info -->
                                    <div class="col-lg-4 col-12">
                                        <div class="text-center mb-4">
                                            <h1 class="display-4 font-weight-bold text-info" id="salesToday"><span class="skeleton-text-dark">$---.--</span></h1>
                                            <p class="text-info text-uppercase font-weight-bold">TODAY SALES</p>
                                        </div>
                                        <table class="table table-borderless">
                                            <tr>
                                                <td>This Week</td>
                                                <td class="text-right font-weight-bold" id="salesWeek"><span class="skeleton-text-dark">$---.--</span></td>
                                            </tr>
                                            <tr>
                                                <td>This Month</td>
                                                <td class="text-right font-weight-bold" id="salesMonth"><span class="skeleton-text-dark">$---.--</span></td>
                                            </tr>
                                            <tr>
                                                <td>Account Balance</td>
                                                <td class="text-right font-weight-bold" id="salesBalance"><span class="skeleton-text-dark">$---.--</span></td>
                                            </tr>
                                            <tr>
                                                <td>Withdrawal Pending</td>
                                                <td class="text-right font-weight-bold" id="salesWithdrawal"><span class="skeleton-text-dark">---</span></td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <!-- Chart -->
                                    <div class="col-lg-8 col-12">
                                        <canvas id="salesChart" height="100"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Orders Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">New Orders</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="30"></th>
                                    <th>Date</th>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <div class="spinner-border spinner-border-sm text-info mr-2" role="status"></div>
                                        Loading orders...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- <div class="card-footer text-right">
                        <a href="#" class="text-info font-weight-bold">VIEW ALL</a>
                    </div> -->
                </div>
            </div>
        </div>

    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
(function() {
    var currentPeriod = '<?php echo $period; ?>';
    var currentStartDate = '<?php echo $startDate; ?>';
    var currentEndDate = '<?php echo $endDate; ?>';
    var salesChart = null;

    function formatNumber(num) {
        return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        var d = new Date(dateStr);
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return ('0' + d.getDate()).slice(-2) + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    }

    function populateData(data, periodSales) {
        // Domains
        document.getElementById('domainTotal').textContent = data.domains.total;
        document.getElementById('domainPending').textContent = data.domains.pending_approval;
        document.getElementById('domainTransfers').textContent = data.domains.transfers;
        document.getElementById('domainRenewal').textContent = data.domains.renewal_due;

        // Sales
        document.getElementById('salesToday').textContent = '$' + formatNumber(data.sales.today);
        document.getElementById('salesWeek').textContent = '$' + formatNumber(data.sales.this_week);
        document.getElementById('salesMonth').textContent = '$' + formatNumber(periodSales);
        document.getElementById('salesBalance').textContent = '$' + formatNumber(data.sales.account_balance);
        document.getElementById('salesWithdrawal').textContent = data.sales.withdrawal_pending;

        // Orders table
        var tbody = document.getElementById('ordersTableBody');
        var orders = data.orders || [];
        var rows = '';

        if (orders.length > 0) {
            var showOrders = orders.slice(0, 10);
            for (var i = 0; i < showOrders.length; i++) {
                var o = showOrders[i];
                rows += '<tr>' +
                    '<td><i class="bi bi-plus-circle text-muted"></i></td>' +
                    '<td>' + formatDate(o.date) + '</td>' +
                    '<td>#' + (o.order_id || '-') + '</td>' +
                    '<td><a href="#" class="text-info">' + (o.product_name || '-') + '</a></td>' +
                    '<td class="text-right">$' + formatNumber(o.amount || 0) + '</td>' +
                    '</tr>';
            }
        } else {
            rows = '<tr><td colspan="5" class="text-center text-muted">No orders available</td></tr>';
        }
        tbody.innerHTML = rows;
    }

    function showError() {
        document.getElementById('domainTotal').textContent = '0';
        document.getElementById('domainPending').textContent = '0';
        document.getElementById('domainTransfers').textContent = '0';
        document.getElementById('domainRenewal').textContent = '0';
        document.getElementById('salesToday').textContent = '$0.00';
        document.getElementById('salesWeek').textContent = '$0.00';
        document.getElementById('salesMonth').textContent = '$0.00';
        document.getElementById('salesBalance').textContent = '$0.00';
        document.getElementById('salesWithdrawal').textContent = 'NONE';
        document.getElementById('ordersTableBody').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to load data. Please try refreshing the page.</td></tr>';
    }

    function hideLoading() {
        var overlay = document.getElementById('dreamscapeLoading');
        if (overlay) {
            overlay.style.opacity = '0';
            setTimeout(function() { overlay.style.display = 'none'; }, 300);
        }
    }

    function initChart() {
        var ctx = document.getElementById('salesChart');
        if (ctx) {
            ctx = ctx.getContext('2d');
            var monthlyData = {
                labels: ['Jan', 'Feb', 'Mar', 'Apr'],
                datasets: [{
                    label: 'Sales',
                    data: [2800, 1900, 2900, 524.17],
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: '#17a2b8',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            };
            salesChart = new Chart(ctx, {
                type: 'line',
                data: monthlyData,
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) { return '$' + value.toLocaleString(); }
                            }
                        }
                    }
                }
            });
        }
    }

    function loadDashboard() {
        var url = 'api/dreamscape/getDashboard.php?period=' + encodeURIComponent(currentPeriod);
        if (currentPeriod === 'custom') {
            url += '&start_date=' + encodeURIComponent(currentStartDate) + '&end_date=' + encodeURIComponent(currentEndDate);
        }

        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                hideLoading();
                if (xhr.status === 200) {
                    try {
                        var resp = JSON.parse(xhr.responseText);
                        if (resp.success) {
                            populateData(resp.data, resp.periodSales);
                        } else {
                            console.error('Dreamscape API error:', resp.error);
                            showError();
                        }
                    } catch (e) {
                        console.error('Parse error:', e);
                        showError();
                    }
                } else {
                    console.error('HTTP error:', xhr.status);
                    showError();
                }
            }
        };
        xhr.send();
    }

    function initDreamscapeReport() {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded');
            return;
        }
        
        jQuery(document).ready(function($) {
            initChart();
            loadDashboard();

            // Period selector change handler
            $('#periodSelector').on('change', function() {
                var period = $(this).val();
                if (period === 'custom') {
                    $('#dateRangeContainer').show();
                } else {
                    $('#dateRangeContainer').hide();
                    window.location.href = 'main.php?p=reportDreamscape&period=' + period;
                }
            });
            
            // Apply custom date range
            $('#applyDateRange').on('click', function() {
                var startDate = $('#startDate').val();
                var endDate = $('#endDate').val();
                if (!startDate || !endDate) { alert('Please select both start and end dates'); return; }
                if (new Date(startDate) > new Date(endDate)) { alert('Start date must be before end date'); return; }
                window.location.href = 'main.php?p=reportDreamscape&period=custom&start_date=' + startDate + '&end_date=' + endDate;
            });

            console.log('Current Period:', currentPeriod);
            console.log('Date Range:', currentStartDate, 'to', currentEndDate);
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDreamscapeReport);
    } else {
        initDreamscapeReport();
    }
})();
</script>

<style>
.summary-card {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 1.5rem;
}

.summary-card h1 {
    margin: 0;
    line-height: 1;
}

.summary-card h5 {
    margin-top: 0.5rem;
    font-weight: 600;
    letter-spacing: 1px;
}

.display-3 {
    font-size: 3.5rem;
}

.table-borderless td {
    border: none;
    padding: 0.5rem 0;
}

/* Loading overlay */
.dreamscape-loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.85);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.3s ease;
}

.dreamscape-loading-content {
    text-align: center;
}

/* Skeleton placeholder animation */
.skeleton-text,
.skeleton-text-dark {
    display: inline-block;
    animation: skeletonPulse 1.2s ease-in-out infinite;
}

.skeleton-text {
    color: rgba(255, 255, 255, 0.5);
}

.skeleton-text-dark {
    color: rgba(23, 162, 184, 0.3);
}

@keyframes skeletonPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
</style>
