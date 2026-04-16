<?php
global $db;
$loginID = $_SESSION['id'];

$dataDir = __DIR__ . '/../assets/data/';
$currentFile = isset($_GET['file']) ? $_GET['file'] : 'deliveries_last30days.csv';
$csvFile = $dataDir . $currentFile;
$deliveries = [];

if (file_exists($csvFile)) {
    $handle = fopen($csvFile, 'r');
    $headers = fgetcsv($handle);
    
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) >= 3) {
            $restaurantName = trim($row[0]);
            $dateStr = trim($row[1]);
            
            // Skip TOTAL row (can be in any column) or empty restaurant names
            if (empty($restaurantName) || 
                strtoupper($restaurantName) === 'TOTAL' || 
                strtoupper($dateStr) === 'TOTAL') {
                continue;
            }
            
            $formattedDate = $dateStr;
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateStr, $matches)) {
                $formattedDate = $dateStr;
            } elseif ($dateObj = DateTime::createFromFormat('d/m/Y', $dateStr)) {
                $formattedDate = $dateObj->format('d/m/Y');
            }
            
            $deliveries[] = [
                'restaurant_name' => $restaurantName,
                'date_of_last_order' => $formattedDate,
                'total_orders' => (int)$row[2]
            ];
        }
    }
    fclose($handle);
}

$totalRestaurants = count($deliveries);
$totalOrders = array_sum(array_column($deliveries, 'total_orders'));
$avgOrders = $totalRestaurants > 0 ? round($totalOrders / $totalRestaurants, 1) : 0;

$allFiles = [];
if (is_dir($dataDir)) {
    $files = scandir($dataDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'csv') {
            $allFiles[] = [
                'name' => $file,
                'size' => filesize($dataDir . $file),
                'date' => date('Y-m-d H:i:s', filemtime($dataDir . $file))
            ];
        }
    }
}
?>

<!-- Content Header (commented out)
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0"><i class="bi bi-truck"></i> Deliveries Report</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item">Report</li>
                    <li class="breadcrumb-item active">Deliveries</li>
                </ol>
            </div>
        </div>
    </div>
</div>
-->

<!-- Main content -->
<div class="content">
    <div class="container-fluid">

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="rd-summary-card">
                    <div class="rd-summary-icon rd-icon-green">
                        <i class="bi bi-shop"></i>
                    </div>
                    <div class="rd-summary-info">
                        <span class="rd-summary-label">Total Restaurants</span>
                        <span class="rd-summary-number" id="totalRestaurants"><?php echo number_format($totalRestaurants); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="rd-summary-card">
                    <div class="rd-summary-icon rd-icon-green">
                        <i class="bi bi-cart-check"></i>
                    </div>
                    <div class="rd-summary-info">
                        <span class="rd-summary-label">Total Orders</span>
                        <span class="rd-summary-number" id="totalOrders"><?php echo number_format($totalOrders); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="rd-summary-card">
                    <div class="rd-summary-icon rd-icon-green">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="rd-summary-info">
                        <span class="rd-summary-label">Average Orders</span>
                        <span class="rd-summary-number" id="avgOrders"><?php echo number_format($avgOrders, 1); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- File Management Bar -->
        <div class="rd-file-bar mb-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-file-earmark-text mr-2" style="color:#9ca3af;"></i>
                <span class="text-muted mr-2" style="font-size:13px;">Current File:</span>
                <span class="rd-file-badge"><?php echo htmlspecialchars($currentFile); ?></span>
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
                <button type="button" class="rd-btn rd-btn-primary" data-toggle="modal" data-target="#uploadModal">
                    <i class="bi bi-upload mr-1"></i> Upload CSV
                </button>
                <button type="button" class="rd-btn rd-btn-outline" data-toggle="modal" data-target="#filesModal">
                    <i class="bi bi-list-ul mr-1"></i> All Files
                </button>
                <?php if (count($allFiles) > 1): ?>
                <button type="button" class="rd-btn rd-btn-danger" id="btnDeleteFile">
                    <i class="bi bi-trash mr-1"></i> Delete
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Table Card -->
        <div class="rd-table-card">
            <!-- Table Header -->
            <div class="rd-table-header">
                <div>
                    <h5 class="rd-table-title">All Restaurants</h5>
                    <span class="rd-active-label">Active Data</span>
                </div>
                <div class="rd-controls d-flex align-items-center" style="gap:12px;">
                    <div class="rd-search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" id="searchInput" placeholder="Search">
                    </div>
                    <div class="rd-filter-group">
                        <input type="number" id="minOrders" class="rd-filter-input" placeholder="Min" min="0">
                        <span class="text-muted" style="font-size:12px;">-</span>
                        <input type="number" id="maxOrders" class="rd-filter-input" placeholder="Max" min="0">
                    </div>
                    <div class="rd-sort-box">
                        <span style="font-size:13px; color:#6b7280;">Sort by :</span>
                        <select id="sortSelect" class="rd-sort-select">
                            <option value="default">Default</option>
                            <option value="name_asc">Name A-Z</option>
                            <option value="name_desc">Name Z-A</option>
                            <option value="orders_desc">Orders High-Low</option>
                            <option value="orders_asc">Orders Low-High</option>
                            <option value="date_desc">Date Newest</option>
                            <option value="date_asc">Date Oldest</option>
                        </select>
                    </div>
                    <button type="button" class="rd-btn rd-btn-outline rd-btn-sm" id="btnExport" title="Export CSV">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="rd-table-wrap">
                <table class="rd-table" id="deliveriesTable">
                    <thead>
                        <tr>
                            <th style="width:50px;">#</th>
                            <th>Restaurant Name</th>
                            <th>Last Order Date</th>
                            <th>Total Orders</th>
                            <th style="width:110px;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="deliveriesBody">
                        <?php foreach ($deliveries as $idx => $delivery): 
                            $orders = $delivery['total_orders'];
                            $statusClass = $orders > 0 ? 'rd-status-active' : 'rd-status-inactive';
                            $statusText = $orders > 0 ? 'Active' : 'Inactive';
                        ?>
                        <tr data-orders="<?php echo $orders; ?>" data-name="<?php echo strtolower($delivery['restaurant_name']); ?>" data-date="<?php echo $delivery['date_of_last_order']; ?>">
                            <td class="rd-cell-muted"><?php echo $idx + 1; ?></td>
                            <td class="rd-cell-name"><?php echo htmlspecialchars($delivery['restaurant_name']); ?></td>
                            <td><?php echo htmlspecialchars($delivery['date_of_last_order']); ?></td>
                            <td><?php echo number_format($orders); ?></td>
                            <td><span class="rd-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="rd-table-footer">
                <span class="rd-showing-text">
                    Showing data <strong id="pageStart">1</strong> to <strong id="pageEnd"><?php echo min(8, $totalRestaurants); ?></strong> of <strong id="visibleTotal"><?php echo number_format($totalRestaurants); ?></strong> entries
                </span>
                <div class="rd-pagination" id="pagination"></div>
            </div>
        </div>

    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-upload mr-2"></i> Upload CSV File</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select CSV File:</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="csvFileInput" name="csvFile" accept=".csv" required>
                            <label class="custom-file-label" for="csvFileInput">Choose file...</label>
                        </div>
                        <small class="form-text text-muted">Only CSV files are allowed</small>
                    </div>
                    <div id="uploadProgress" class="progress" style="display:none;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div id="uploadMessage" class="alert" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnUpload">
                        <i class="bi bi-upload mr-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Files Modal -->
<div class="modal fade" id="filesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-list-ul mr-2"></i> All CSV Files</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>File Name</th>
                                <th class="text-center">Size</th>
                                <th class="text-center">Modified Date</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allFiles as $file): ?>
                            <tr>
                                <td>
                                    <i class="bi bi-file-earmark-text mr-1"></i>
                                    <strong><?php echo htmlspecialchars($file['name']); ?></strong>
                                    <?php if ($file['name'] === $currentFile): ?>
                                    <span class="badge badge-success ml-2">Current</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?php echo number_format($file['size'] / 1024, 2); ?> KB</td>
                                <td class="text-center"><?php echo $file['date']; ?></td>
                                <td class="text-center">
                                    <?php if ($file['name'] !== $currentFile): ?>
                                    <button class="btn btn-sm btn-primary btnSelectFile" data-file="<?php echo htmlspecialchars($file['name']); ?>">
                                        <i class="bi bi-check-circle mr-1"></i> Select
                                    </button>
                                    <button class="btn btn-sm btn-danger btnDeleteFileModal ml-1" data-file="<?php echo htmlspecialchars($file['name']); ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php else: ?>
                                    <span class="text-success"><i class="bi bi-check-circle-fill"></i> Active</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Summary Cards ── */
.rd-summary-card {
    display: flex;
    align-items: center;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 20px 24px;
    gap: 16px;
    transition: box-shadow .2s;
}
.rd-summary-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,.06);
}
.rd-summary-icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.rd-icon-green {
    background: #dcfce7;
    color: #16a34a;
}
.rd-summary-info {
    display: flex;
    flex-direction: column;
}
.rd-summary-label {
    font-size: 12px;
    color: #9ca3af;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: .4px;
}
.rd-summary-number {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
    line-height: 1.2;
}

/* ── File Bar ── */
.rd-file-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 10px 20px;
}
.rd-file-badge {
    display: inline-block;
    background: #eff6ff;
    color: #2563eb;
    font-size: 12px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 6px;
}
.rd-btn {
    display: inline-flex;
    align-items: center;
    font-size: 13px;
    font-weight: 500;
    padding: 6px 14px;
    border-radius: 8px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all .15s;
    white-space: nowrap;
}
.rd-btn-sm { padding: 5px 10px; }
.rd-btn-primary { background: #16a34a; color: #fff; border-color: #16a34a; }
.rd-btn-primary:hover { background: #15803d; color: #fff; }
.rd-btn-outline { background: #fff; color: #374151; border-color: #d1d5db; }
.rd-btn-outline:hover { background: #f9fafb; }
.rd-btn-danger { background: #fee2e2; color: #dc2626; border-color: #fecaca; }
.rd-btn-danger:hover { background: #fecaca; }

/* ── Table Card ── */
.rd-table-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    overflow: hidden;
}

/* ── Table Header ── */
.rd-table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px 16px;
    flex-wrap: wrap;
    gap: 12px;
}
.rd-table-title {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}
.rd-active-label {
    font-size: 13px;
    color: #16a34a;
    font-weight: 500;
}

/* ── Search / Filter ── */
.rd-search-box {
    position: relative;
    display: flex;
    align-items: center;
}
.rd-search-box i {
    position: absolute;
    left: 10px;
    color: #9ca3af;
    font-size: 14px;
}
.rd-search-box input {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 7px 12px 7px 32px;
    font-size: 13px;
    width: 180px;
    outline: none;
    transition: border .15s;
}
.rd-search-box input:focus {
    border-color: #16a34a;
}
.rd-filter-group {
    display: flex;
    align-items: center;
    gap: 4px;
}
.rd-filter-input {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 7px 8px;
    font-size: 13px;
    width: 70px;
    outline: none;
    text-align: center;
}
.rd-filter-input:focus {
    border-color: #16a34a;
}
.rd-sort-box {
    display: flex;
    align-items: center;
    gap: 6px;
}
.rd-sort-select {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 7px 10px;
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    outline: none;
    cursor: pointer;
    background: #fff;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%236b7280' stroke-width='1.5' fill='none'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 28px;
}

/* ── Table ── */
.rd-table-wrap {
    overflow-x: auto;
}
.rd-table {
    width: 100%;
    border-collapse: collapse;
}
.rd-table thead th {
    font-size: 12px;
    font-weight: 600;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: .4px;
    padding: 12px 24px;
    border-bottom: 1px solid #f3f4f6;
    background: #fff;
    white-space: nowrap;
}
.rd-table tbody tr {
    border-bottom: 1px solid #f3f4f6;
    transition: background .1s;
}
.rd-table tbody tr:last-child {
    border-bottom: none;
}
.rd-table tbody tr:hover {
    background: #f9fafb;
}
.rd-table tbody td {
    padding: 14px 24px;
    font-size: 14px;
    color: #374151;
    vertical-align: middle;
}
.rd-cell-muted {
    color: #9ca3af;
    font-size: 13px;
}
.rd-cell-name {
    font-weight: 600;
    color: #111827;
}

/* ── Status Badge ── */
.rd-status {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
}
.rd-status-active {
    background: #dcfce7;
    color: #16a34a;
    border: 1px solid #bbf7d0;
}
.rd-status-inactive {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

/* ── Pagination Footer ── */
.rd-table-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    border-top: 1px solid #f3f4f6;
    flex-wrap: wrap;
    gap: 12px;
}
.rd-showing-text {
    font-size: 13px;
    color: #16a34a;
}
.rd-pagination {
    display: flex;
    align-items: center;
    gap: 4px;
}
.rd-pagination .rd-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    color: #374151;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all .15s;
    padding: 0 6px;
}
.rd-pagination .rd-page-btn:hover {
    background: #f3f4f6;
}
.rd-pagination .rd-page-btn.active {
    background: #16a34a;
    color: #fff;
    border-color: #16a34a;
}
.rd-pagination .rd-page-btn.disabled {
    opacity: .4;
    cursor: default;
    pointer-events: none;
}
.rd-pagination .rd-page-dots {
    color: #9ca3af;
    font-size: 13px;
    padding: 0 2px;
}

/* ── Fit to screen ── */
.content-wrapper .content {
    padding-bottom: 0;
}
.rd-table-card {
    display: flex;
    flex-direction: column;
}
.rd-table-wrap {
    flex: 1 1 auto;
    overflow-x: auto;
    overflow-y: auto;
}
.rd-table {
    min-width: 600px;
}

/* ── Responsive: Large ── */
@media (max-width: 1200px) {
    .rd-search-box input { width: 140px; }
    .rd-filter-input { width: 60px; }
    .rd-sort-select { font-size: 12px; padding-right: 24px; }
    .rd-table thead th,
    .rd-table tbody td { padding: 10px 14px; font-size: 13px; }
}

/* ── Responsive: Medium ── */
@media (max-width: 992px) {
    .rd-table-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .rd-controls {
        flex-wrap: wrap;
        width: 100%;
    }
    .rd-search-box { flex: 1 1 100%; }
    .rd-search-box input { width: 100%; }
    .rd-file-bar {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
    .rd-summary-number { font-size: 24px; }
    .rd-summary-card { padding: 16px 18px; }
}

/* ── Responsive: Small ── */
@media (max-width: 768px) {
    .rd-summary-card { padding: 14px 14px; gap: 12px; border-radius: 12px; }
    .rd-summary-icon { width: 42px; height: 42px; font-size: 18px; }
    .rd-summary-number { font-size: 20px; }
    .rd-summary-label { font-size: 11px; }
    .rd-table-header { padding: 14px 14px 10px; gap: 8px; }
    .rd-table-title { font-size: 16px; }
    .rd-controls { gap: 8px !important; }
    .rd-filter-group { order: 2; }
    .rd-sort-box { order: 3; flex: 1 1 100%; }
    .rd-sort-box span { display: none; }
    .rd-sort-select { width: 100%; }
    .rd-table thead th,
    .rd-table tbody td { padding: 10px 10px; font-size: 12px; }
    .rd-table-footer { padding: 12px 14px; flex-direction: column; align-items: flex-start; }
    .rd-pagination { width: 100%; justify-content: center; flex-wrap: wrap; }
    .rd-page-btn { min-width: 28px; height: 28px; font-size: 12px; }
    .rd-file-bar { padding: 10px 14px; border-radius: 10px; }
    .rd-btn { font-size: 12px; padding: 5px 10px; }
    .rd-table-card { border-radius: 12px; }
    .rd-table { min-width: 500px; }
}

/* ── Responsive: Extra Small ── */
@media (max-width: 480px) {
    .rd-summary-card { padding: 12px; gap: 10px; }
    .rd-summary-icon { width: 36px; height: 36px; font-size: 16px; }
    .rd-summary-number { font-size: 18px; }
    .rd-table-header { padding: 12px 10px 8px; }
    .rd-table-title { font-size: 14px; }
    .rd-table thead th,
    .rd-table tbody td { padding: 8px 8px; font-size: 11px; }
    .rd-status { padding: 3px 8px; font-size: 10px; }
    .rd-file-bar { padding: 8px 10px; }
    .rd-btn { font-size: 11px; padding: 4px 8px; border-radius: 6px; }
    .rd-table { min-width: 420px; }
    .rd-showing-text { font-size: 11px; }
    .rd-page-btn { min-width: 26px; height: 26px; font-size: 11px; border-radius: 6px; }
}
</style>

<script>
(function() {
    // Wait for jQuery to be available
    function waitForJQuery() {
        if (typeof jQuery === 'undefined') {
            setTimeout(waitForJQuery, 50);
            return;
        }
        
        jQuery(document).ready(function($) {
            var allRows = [];
            var filteredRows = [];
            var currentPage = 1;
            var rowsPerPage = 8;
            
            // Collect all row data
            $('#deliveriesBody tr').each(function() {
                allRows.push(this);
            });
            filteredRows = allRows.slice();
            
            // ── Filter ──
            function filterTable() {
                var searchText = $('#searchInput').val().toLowerCase();
                var minOrders = parseInt($('#minOrders').val()) || 0;
                var maxOrders = parseInt($('#maxOrders').val()) || 999999;
                var totalOrdersSum = 0;
                
                filteredRows = [];
                
                for (var i = 0; i < allRows.length; i++) {
                    var $row = $(allRows[i]);
                    var name = $row.data('name');
                    var orders = $row.data('orders');
                    
                    var matchSearch = searchText === '' || name.indexOf(searchText) !== -1;
                    var matchOrders = orders >= minOrders && orders <= maxOrders;
                    
                    if (matchSearch && matchOrders) {
                        filteredRows.push(allRows[i]);
                        totalOrdersSum += orders;
                    }
                }
                
                currentPage = 1;
                
                // Update summary cards
                var count = filteredRows.length;
                $('#totalRestaurants').text(count.toLocaleString());
                $('#totalOrders').text(totalOrdersSum.toLocaleString());
                $('#avgOrders').text(count > 0 ? (totalOrdersSum / count).toFixed(1) : '0.0');
                
                renderPage();
            }
            
            // ── Sort ──
            function sortRows(type) {
                switch (type) {
                    case 'name_asc':
                        filteredRows.sort(function(a, b) { return $(a).data('name').localeCompare($(b).data('name')); });
                        break;
                    case 'name_desc':
                        filteredRows.sort(function(a, b) { return $(b).data('name').localeCompare($(a).data('name')); });
                        break;
                    case 'orders_desc':
                        filteredRows.sort(function(a, b) { return $(b).data('orders') - $(a).data('orders'); });
                        break;
                    case 'orders_asc':
                        filteredRows.sort(function(a, b) { return $(a).data('orders') - $(b).data('orders'); });
                        break;
                    case 'date_desc':
                        filteredRows.sort(function(a, b) {
                            return parseDateVal($(b).data('date')) - parseDateVal($(a).data('date'));
                        });
                        break;
                    case 'date_asc':
                        filteredRows.sort(function(a, b) {
                            return parseDateVal($(a).data('date')) - parseDateVal($(b).data('date'));
                        });
                        break;
                }
                currentPage = 1;
                renderPage();
            }
            
            function parseDateVal(str) {
                if (!str) return 0;
                var parts = str.split('/');
                if (parts.length === 3) {
                    return new Date(parts[2], parts[1] - 1, parts[0]).getTime();
                }
                return 0;
            }
            
            // ── Pagination ──
            function renderPage() {
                var total = filteredRows.length;
                var totalPages = Math.max(1, Math.ceil(total / rowsPerPage));
                if (currentPage > totalPages) currentPage = totalPages;
                
                var start = (currentPage - 1) * rowsPerPage;
                var end = Math.min(start + rowsPerPage, total);
                
                var $tbody = $('#deliveriesBody');
                $tbody.empty();
                
                for (var i = start; i < end; i++) {
                    var $row = $(filteredRows[i]).clone(true);
                    $row.find('td:first').text(i + 1);
                    $tbody.append($row);
                }
                
                // Update footer text
                if (total === 0) {
                    $('#pageStart').text('0');
                    $('#pageEnd').text('0');
                } else {
                    $('#pageStart').text(start + 1);
                    $('#pageEnd').text(end);
                }
                $('#visibleTotal').text(total.toLocaleString());
                
                renderPagination(totalPages);
            }
            
            function renderPagination(totalPages) {
                var $pag = $('#pagination');
                $pag.empty();
                
                if (totalPages <= 1) return;
                
                // Prev
                $pag.append('<button class="rd-page-btn ' + (currentPage === 1 ? 'disabled' : '') + '" data-page="prev"><i class="bi bi-chevron-left"></i></button>');
                
                var pages = getPaginationRange(currentPage, totalPages);
                for (var i = 0; i < pages.length; i++) {
                    if (pages[i] === '...') {
                        $pag.append('<span class="rd-page-dots">...</span>');
                    } else {
                        $pag.append('<button class="rd-page-btn ' + (pages[i] === currentPage ? 'active' : '') + '" data-page="' + pages[i] + '">' + pages[i] + '</button>');
                    }
                }
                
                // Next
                $pag.append('<button class="rd-page-btn ' + (currentPage === totalPages ? 'disabled' : '') + '" data-page="next"><i class="bi bi-chevron-right"></i></button>');
            }
            
            function getPaginationRange(current, total) {
                var delta = 1;
                var range = [];
                var rangeWithDots = [];
                var l;
                
                range.push(1);
                for (var i = current - delta; i <= current + delta; i++) {
                    if (i > 1 && i < total) range.push(i);
                }
                range.push(total);
                
                // Deduplicate
                var unique = [];
                for (var j = 0; j < range.length; j++) {
                    if (unique.indexOf(range[j]) === -1) unique.push(range[j]);
                }
                unique.sort(function(a, b) { return a - b; });
                
                for (var k = 0; k < unique.length; k++) {
                    if (l) {
                        if (unique[k] - l > 1) {
                            rangeWithDots.push('...');
                        }
                    }
                    rangeWithDots.push(unique[k]);
                    l = unique[k];
                }
                return rangeWithDots;
            }
            
            // ── Events ──
            $('#searchInput, #minOrders, #maxOrders').on('keyup change', filterTable);
            
            $('#sortSelect').on('change', function() {
                var val = $(this).val();
                if (val === 'default') {
                    // Reset to original order then re-filter
                    filteredRows = [];
                    var searchText = $('#searchInput').val().toLowerCase();
                    var minOrders = parseInt($('#minOrders').val()) || 0;
                    var maxOrders = parseInt($('#maxOrders').val()) || 999999;
                    for (var i = 0; i < allRows.length; i++) {
                        var $r = $(allRows[i]);
                        var matchSearch = searchText === '' || $r.data('name').indexOf(searchText) !== -1;
                        var matchOrders = $r.data('orders') >= minOrders && $r.data('orders') <= maxOrders;
                        if (matchSearch && matchOrders) filteredRows.push(allRows[i]);
                    }
                    currentPage = 1;
                    renderPage();
                } else {
                    sortRows(val);
                }
            });
            
            $(document).on('click', '.rd-page-btn', function() {
                var page = $(this).data('page');
                var totalPages = Math.max(1, Math.ceil(filteredRows.length / rowsPerPage));
                if (page === 'prev') {
                    if (currentPage > 1) currentPage--;
                } else if (page === 'next') {
                    if (currentPage < totalPages) currentPage++;
                } else {
                    currentPage = parseInt(page);
                }
                renderPage();
            });
            
            $('#btnExport').on('click', function() {
                window.location.href = 'assets/data/<?php echo htmlspecialchars($currentFile); ?>';
            });
            
            // File upload
            $('#csvFileInput').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName);
            });
            
            $('#uploadForm').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                var fileInput = $('#csvFileInput')[0];
                
                if (!fileInput.files[0]) {
                    alert('Please select a file');
                    return;
                }
                
                $('#uploadProgress').show();
                $('#uploadMessage').hide();
                $('#btnUpload').prop('disabled', true);
                
                $.ajax({
                    url: 'assets/php/uploadDeliveryCSV.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                var percent = Math.round((e.loaded / e.total) * 100);
                                $('#uploadProgress .progress-bar').css('width', percent + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        try {
                            var data = typeof response === 'string' ? JSON.parse(response) : response;
                            $('#uploadProgress').hide();
                            $('#uploadMessage').removeClass('alert-danger alert-success')
                                .addClass(data.success ? 'alert-success' : 'alert-danger')
                                .text(data.message)
                                .show();
                            
                            if (data.success) {
                                setTimeout(function() {
                                    $('#uploadModal').modal('hide');
                                    window.location.href = 'main.php?p=reportDeliveries&file=' + encodeURIComponent(data.filename);
                                }, 1000);
                            } else {
                                $('#btnUpload').prop('disabled', false);
                            }
                        } catch(e) {
                            console.error('Parse error:', e, response);
                            $('#uploadProgress').hide();
                            $('#uploadMessage').removeClass('alert-success')
                                .addClass('alert-danger')
                                .text('Upload failed: Invalid response')
                                .show();
                            $('#btnUpload').prop('disabled', false);
                        }
                    },
                    error: function() {
                        $('#uploadProgress').hide();
                        $('#uploadMessage').removeClass('alert-success')
                            .addClass('alert-danger')
                            .text('Upload failed. Please try again.')
                            .show();
                        $('#btnUpload').prop('disabled', false);
                    }
                });
            });
            
            // Select file from modal
            $('.btnSelectFile').on('click', function() {
                var fileName = $(this).data('file');
                window.location.href = 'main.php?p=reportDeliveries&file=' + encodeURIComponent(fileName);
            });
            
            // Delete file from modal
            $('.btnDeleteFileModal').on('click', function() {
                var fileName = $(this).data('file');
                if (!confirm('Are you sure you want to delete "' + fileName + '"?')) {
                    return;
                }
                
                $.ajax({
                    url: 'assets/php/deleteDeliveryCSV.php',
                    type: 'POST',
                    data: { filename: fileName },
                    success: function(response) {
                        try {
                            var data = typeof response === 'string' ? JSON.parse(response) : response;
                            if (data.success) {
                                alert('File deleted successfully');
                                window.location.href = 'main.php?p=reportDeliveries';
                            } else {
                                alert('Error: ' + data.message);
                            }
                        } catch(e) {
                            alert('Failed to delete file');
                        }
                    },
                    error: function() {
                        alert('Failed to delete file');
                    }
                });
            });
            
            // Delete current file
            $('#btnDeleteFile').on('click', function() {
                if (!confirm('Are you sure you want to delete "<?php echo htmlspecialchars($currentFile); ?>"?')) {
                    return;
                }
                
                $.ajax({
                    url: 'assets/php/deleteDeliveryCSV.php',
                    type: 'POST',
                    data: { filename: '<?php echo htmlspecialchars($currentFile); ?>' },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.success) {
                            alert('File deleted successfully');
                            window.location.href = 'main.php?p=reportDeliveries';
                        } else {
                            alert('Error: ' + data.message);
                        }
                    },
                    error: function() {
                        alert('Failed to delete file');
                    }
                });
            });
            
            // Initial render
            renderPage();
        });
    }
    
    waitForJQuery();
})();
</script>
