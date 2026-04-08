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

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0"><i class="bi bi-truck"></i> Deliveries Report (Last 30 Days)</h4>
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

<!-- Main content -->
<div class="content">
    <div class="container-fluid">

        <!-- Summary Cards -->
        <div class="row mb-3">
            <div class="col-lg-4 col-md-6">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="bi bi-shop"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-number" id="totalRestaurants"><?php echo number_format($totalRestaurants); ?></span>
                        <span class="info-box-text">Total Restaurants</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="bi bi-cart-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-number" id="totalOrders"><?php echo number_format($totalOrders); ?></span>
                        <span class="info-box-text">Total Orders</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="bi bi-graph-up"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-number" id="avgOrders"><?php echo number_format($avgOrders, 1); ?></span>
                        <span class="info-box-text">Average Orders per Restaurant</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- File Management -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="text-sm"><i class="bi bi-file-earmark-text mr-1"></i> Current File:</strong>
                        <span class="badge badge-info ml-2"><?php echo htmlspecialchars($currentFile); ?></span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#uploadModal">
                            <i class="bi bi-upload mr-1"></i> Upload CSV
                        </button>
                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#filesModal">
                            <i class="bi bi-list-ul mr-1"></i> Show All Data
                        </button>
                        <?php if (count($allFiles) > 1): ?>
                        <button type="button" class="btn btn-sm btn-danger" id="btnDeleteFile">
                            <i class="bi bi-trash mr-1"></i> Delete File
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deliveries Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-table mr-1"></i> Restaurant Deliveries</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" id="btnExport">
                        <i class="bi bi-download mr-1"></i> Export CSV
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="p-3 bg-light">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label class="mb-1 text-sm font-weight-bold">Search Restaurant:</label>
                                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Type restaurant name...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-2">
                                <label class="mb-1 text-sm font-weight-bold">Min Orders:</label>
                                <input type="number" id="minOrders" class="form-control form-control-sm" placeholder="0" min="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-2">
                                <label class="mb-1 text-sm font-weight-bold">Max Orders:</label>
                                <input type="number" id="maxOrders" class="form-control form-control-sm" placeholder="999" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label class="mb-1 text-sm font-weight-bold">Date Range:</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" id="dateRange" class="form-control" placeholder="Select date range" readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="clearDateRange">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive" style="max-height: 600px;">
                    <table class="table table-sm table-hover mb-0" id="deliveriesTable">
                        <thead class="thead-dark sticky-top">
                            <tr>
                                <th style="width:60px;">#</th>
                                <th>Restaurant Name</th>
                                <th class="text-center" style="width:150px;">Last Order Date</th>
                                <th class="text-center sortable" style="width:150px; cursor:pointer;" data-sort="orders">
                                    Total Orders <i class="bi bi-arrow-down-up ml-1"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="deliveriesBody">
                            <?php foreach ($deliveries as $idx => $delivery): ?>
                            <tr data-orders="<?php echo $delivery['total_orders']; ?>" data-name="<?php echo strtolower($delivery['restaurant_name']); ?>">
                                <td class="text-muted"><?php echo $idx + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($delivery['restaurant_name']); ?></strong></td>
                                <td class="text-center"><?php echo htmlspecialchars($delivery['date_of_last_order']); ?></td>
                                <td class="text-center">
                                    <span class="badge badge-primary badge-pill"><?php echo number_format($delivery['total_orders']); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="p-3 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted text-sm">
                            Showing <strong id="visibleCount"><?php echo $totalRestaurants; ?></strong> of <strong><?php echo $totalRestaurants; ?></strong> restaurants
                        </span>
                        <button class="btn btn-sm btn-outline-secondary" id="btnReset">
                            <i class="bi bi-arrow-clockwise mr-1"></i> Reset Filters
                        </button>
                    </div>
                </div>
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
                        <thead class="thead-dark">
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
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #1e293b;
}
.thead-dark th {
    background: #1e293b;
    color: #fff;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 14px;
    border: none;
}
#deliveriesTable tbody td {
    font-size: 13px;
    padding: 10px 14px;
    vertical-align: middle;
}
#deliveriesTable tbody tr:hover {
    background: #f0f7ff;
}
.sortable:hover {
    background: #334155 !important;
}
.info-box {
    display: flex;
    align-items: center;
    padding: 15px;
    border-radius: 5px;
    color: #fff;
    min-height: 90px;
}
.info-box-icon {
    font-size: 50px;
    width: 90px;
    text-align: center;
    opacity: 0.3;
}
.info-box-content {
    flex: 1;
    padding-left: 10px;
}
.info-box-number {
    display: block;
    font-size: 28px;
    font-weight: bold;
    line-height: 1;
}
.info-box-text {
    display: block;
    font-size: 14px;
    margin-top: 5px;
    opacity: 0.9;
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
            var allRows = $('#deliveriesBody tr');
            var sortOrder = 'desc';
            var startDate = null;
            var endDate = null;
            
            // Poll for libraries to be ready
            var checkLibraries = setInterval(function() {
                if (typeof moment !== 'undefined' && typeof $.fn.daterangepicker !== 'undefined' && $('#dateRange').length) {
                    clearInterval(checkLibraries);
            
            // Initialize date range picker
            try {
                $('#dateRange').daterangepicker({
                    autoUpdateInput: false,
                    locale: {
                        cancelLabel: 'Clear',
                        format: 'DD/MM/YYYY'
                    },
                    opens: 'left',
                    showDropdowns: true,
                    minYear: 2020,
                    maxYear: parseInt(moment().format('YYYY'), 10)
                });
                
                $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
                    startDate = picker.startDate;
                    endDate = picker.endDate;
                    filterTable();
                });
                
                $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    startDate = null;
                    endDate = null;
                    filterTable();
                });
                
                $('#clearDateRange').on('click', function() {
                    $('#dateRange').val('');
                    startDate = null;
                    endDate = null;
                    filterTable();
                });
                
                console.log('Daterangepicker initialized successfully');
            } catch(e) {
                console.error('Error initializing daterangepicker:', e);
            }
        }
    }, 100);
    
    function filterTable() {
        var searchText = $('#searchInput').val().toLowerCase();
        var minOrders = parseInt($('#minOrders').val()) || 0;
        var maxOrders = parseInt($('#maxOrders').val()) || 999999;
        var visibleCount = 0;
        var totalOrdersSum = 0;
        
        allRows.each(function() {
            var row = $(this);
            var name = row.data('name');
            var orders = row.data('orders');
            var dateStr = row.find('td:eq(2)').text().trim();
            
            var matchSearch = searchText === '' || name.indexOf(searchText) !== -1;
            var matchOrders = orders >= minOrders && orders <= maxOrders;
            var matchDate = true;
            
            if (startDate && endDate && dateStr) {
                var rowDate = moment(dateStr, 'DD/MM/YYYY');
                if (rowDate.isValid()) {
                    matchDate = rowDate.isBetween(startDate, endDate, 'day', '[]');
                }
            }
            
            if (matchSearch && matchOrders && matchDate) {
                row.show();
                visibleCount++;
                totalOrdersSum += orders;
            } else {
                row.hide();
            }
        });
        
        $('#visibleCount').text(visibleCount);
        $('#totalRestaurants').text(visibleCount.toLocaleString());
        $('#totalOrders').text(totalOrdersSum.toLocaleString());
        $('#avgOrders').text(visibleCount > 0 ? (totalOrdersSum / visibleCount).toFixed(1) : '0.0');
        updateRowNumbers();
    }
    
    function updateRowNumbers() {
        var num = 1;
        allRows.filter(':visible').each(function() {
            $(this).find('td:first').text(num++);
        });
    }
    
    function sortByOrders() {
        var rows = allRows.toArray();
        
        rows.sort(function(a, b) {
            var aVal = $(a).data('orders');
            var bVal = $(b).data('orders');
            return sortOrder === 'desc' ? bVal - aVal : aVal - bVal;
        });
        
        $('#deliveriesBody').html(rows);
        allRows = $('#deliveriesBody tr');
        sortOrder = sortOrder === 'desc' ? 'asc' : 'desc';
        
        var icon = sortOrder === 'desc' ? 'bi-arrow-down-up' : (sortOrder === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down');
        $('.sortable i').attr('class', 'bi ' + icon + ' ml-1');
        
        filterTable();
    }
    
    $('#searchInput, #minOrders, #maxOrders').on('keyup change', filterTable);
    
    $('.sortable').on('click', sortByOrders);
    
    $('#btnReset').on('click', function() {
        $('#searchInput').val('');
        $('#minOrders').val('');
        $('#maxOrders').val('');
        $('#dateRange').val('');
        startDate = null;
        endDate = null;
        filterTable();
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
        });
    }
    
    waitForJQuery();
})();
</script>
