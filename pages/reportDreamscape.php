<?php
global $db;
$loginID = $_SESSION['id'];

// Load Dreamscape API
require_once __DIR__ . '/../assets/php/DreamscapeAPI.php';

// Initialize API
$api = new DreamscapeAPI();

// Get date range from request or use defaults
$period = isset($_GET['period']) ? $_GET['period'] : 'month';

// Calculate date range based on period
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
    // Default to month
    $startDate = date('Y-m-01');
    $endDate = date('Y-m-d');
}

// Fetch real-time data from Dreamscape API with date range
try {
    $dashboardData = $api->getDashboardSummary($startDate, $endDate);
    
    // Debug: Log the response
    error_log('Dashboard Data Response: ' . print_r($dashboardData, true));
    
    // If API call fails, use fallback mock data
    if (!$dashboardData['success']) {
        throw new Exception('API call failed: ' . print_r($dashboardData, true));
    }
    
    $dashboardData = $dashboardData['data'];
    
    // Use period_total for the selected date range
    $periodSales = $dashboardData['sales']['period_total'];
    
} catch (Exception $e) {
    // Fallback to mock data if API fails
    error_log('Dreamscape API Error: ' . $e->getMessage());
    
    $periodSales = 0.00;
    
    $dashboardData = [
        'domains' => [
            'total' => 0,
            'pending_approval' => 0,
            'transfers' => 0,
            'renewal_due' => 0
        ],
        'hosting' => [
            'total' => 0,
            'pending_setup' => 0,
            'renewal_due' => 0
        ],
        'products' => [
            'total' => 0,
            'pending_setup' => 0,
            'renewal_due' => 0
        ],
        'packages' => [
            'total' => 0,
            'pending_setup' => 0,
            'renewal_due' => 0
        ],
        'sales' => [
            'today' => 0.00,
            'this_week' => 0.00,
            'this_month' => 0.00,
            'account_balance' => 0.00,
            'withdrawal_pending' => 'NONE'
        ],
        'orders' => []
    ];
}

// Ensure new_orders key exists
if (!isset($dashboardData['orders'])) {
    $dashboardData['orders'] = [];
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

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Summary Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold mb-0">Summary</h3>
                        <small class="text-muted">
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
                                                <h1 class="display-3 font-weight-bold"><?php echo $dashboardData['domains']['total']; ?></h1>
                                                <h5 class="text-uppercase">DOMAINS</h5>
                                            </div>
                                        </div>
                                        <div class="col-lg-8 col-12">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Pending Approval</span>
                                                <span class="font-weight-bold"><?php echo $dashboardData['domains']['pending_approval']; ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Transfers</span>
                                                <span class="font-weight-bold"><?php echo $dashboardData['domains']['transfers']; ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Renewal Due</span>
                                                <span class="font-weight-bold"><?php echo $dashboardData['domains']['renewal_due']; ?></span>
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
                                            <h1 class="display-4 font-weight-bold text-info">$<?php echo number_format($dashboardData['sales']['today'], 2); ?></h1>
                                            <p class="text-info text-uppercase font-weight-bold">TODAY SALES</p>
                                        </div>
                                        <table class="table table-borderless">
                                            <tr>
                                                <td>This Week</td>
                                                <td class="text-right font-weight-bold">$<?php echo number_format($dashboardData['sales']['this_week'], 2); ?></td>
                                            </tr>
                                            <tr>
                                                <td>This Month</td>
                                                <td class="text-right font-weight-bold">$<?php echo number_format($periodSales, 2); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Account Balance</td>
                                                <td class="text-right font-weight-bold">$<?php echo number_format($dashboardData['sales']['account_balance'], 2); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Withdrawal Pending</td>
                                                <td class="text-right font-weight-bold"><?php echo $dashboardData['sales']['withdrawal_pending']; ?></td>
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
                            <tbody>
                                <?php if (!empty($dashboardData['orders'])): ?>
                                    <?php foreach (array_slice($dashboardData['orders'], 0, 10) as $order): ?>
                                    <tr>
                                        <td><i class="bi bi-plus-circle text-muted"></i></td>
                                        <td><?php echo isset($order['date']) ? date('d M Y', strtotime($order['date'])) : '-'; ?></td>
                                        <td>#<?php echo isset($order['order_id']) ? $order['order_id'] : '-'; ?></td>
                                        <td><a href="#" class="text-info"><?php echo isset($order['product_name']) ? $order['product_name'] : '-'; ?></a></td>
                                        <td class="text-right">$<?php echo isset($order['amount']) ? number_format($order['amount'], 2) : '0.00'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No orders available</td>
                                    </tr>
                                <?php endif; ?>
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
// Wait for jQuery to be loaded
(function() {
    function initDreamscapeReport() {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded');
            return;
        }
        
        jQuery(document).ready(function($) {
            // Initialize Sales Chart
            var ctx = document.getElementById('salesChart');
            if (ctx) {
                ctx = ctx.getContext('2d');
                
                // Mock monthly sales data (TODO: Get from API when available)
                var monthlyData = {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr'],
                    datasets: [{
                        label: 'Sales',
                        data: [2800, 1900, 2900, 524.17], // Last value is current month from API
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
                
                var salesChart = new Chart(ctx, {
                    type: 'line',
                    data: monthlyData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
                
                // Chart period selector
                $('#chartPeriod').on('change', function() {
                    // TODO: Update chart data based on selected period
                    console.log('Chart period changed to:', $(this).val());
                });
            }
            
            // Period selector change handler
            $('#periodSelector').on('change', function() {
                var period = $(this).val();
                
                if (period === 'custom') {
                    $('#dateRangeContainer').show();
                } else {
                    $('#dateRangeContainer').hide();
                    // Auto-apply for preset periods
                    window.location.href = 'main.php?p=reportDreamscape&period=' + period;
                }
            });
            
            // Apply custom date range
            $('#applyDateRange').on('click', function() {
                var startDate = $('#startDate').val();
                var endDate = $('#endDate').val();
                
                if (!startDate || !endDate) {
                    alert('Please select both start and end dates');
                    return;
                }
                
                if (new Date(startDate) > new Date(endDate)) {
                    alert('Start date must be before end date');
                    return;
                }
                
                window.location.href = 'main.php?p=reportDreamscape&period=custom&start_date=' + startDate + '&end_date=' + endDate;
            });
            
            // Show current period info
            var period = '<?php echo $period; ?>';
            var startDate = '<?php echo $startDate; ?>';
            var endDate = '<?php echo $endDate; ?>';
            
            console.log('Current Period:', period);
            console.log('Date Range:', startDate, 'to', endDate);
        });
    }
    
    // Initialize when DOM is ready
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
</style>
