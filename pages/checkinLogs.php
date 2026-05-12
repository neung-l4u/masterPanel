<?php
global $db;
?>
<style>
    .filter-row {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .filter-row .col-md-2,
        .filter-row .col-md-3 {
            margin-bottom: 10px;
        }
    }
</style>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <i class="nav-icon mr-2 bi bi-clock-history"></i>
                    Check-in Logs
                </h4>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item active">Check-in Logs</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-funnel mr-2"></i> Filter
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="filter-row">
                            <div class="row mb-2">
                                <div class="col-6 col-md-3 mb-2 mb-md-0">
                                    <label for="filterDepartment" class="form-label small">Department</label>
                                    <select id="filterDepartment" class="form-control form-control-sm">
                                        <option value="">All</option>
                                        <?php
                                        $departments = $db->query('SELECT DISTINCT `department` FROM `checkin` WHERE `department` IS NOT NULL AND `department` != "" ORDER BY `department`;')->fetchAll();
                                        foreach ($departments as $dept) {
                                            echo '<option value="' . $dept['department'] . '">' . $dept['department'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3 mb-2 mb-md-0">
                                    <label for="filterEmployee" class="form-label small">Employee</label>
                                    <select id="filterEmployee" class="form-control form-control-sm">
                                        <option value="">All</option>
                                        <?php
                                        $employees = $db->query('SELECT DISTINCT `employee` FROM `checkin` WHERE `employee` IS NOT NULL AND `employee` != "" ORDER BY `employee`;')->fetchAll();
                                        foreach ($employees as $emp) {
                                            echo '<option value="' . $emp['employee'] . '">' . $emp['employee'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3 mb-2 mb-md-0">
                                    <label for="filterStatus" class="form-label small">Status</label>
                                    <select id="filterStatus" class="form-control form-control-sm">
                                        <option value="">All</option>
                                        <option value="Clock In">Clock In</option>
                                        <option value="Clock Out">Clock Out</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3 mb-2 mb-md-0">
                                    <label for="filterDateFrom" class="form-label small">From Date</label>
                                    <input type="date" id="filterDateFrom" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 col-md-3 mb-2 mb-md-0">
                                    <label for="filterDateTo" class="form-label small">To Date</label>
                                    <input type="date" id="filterDateTo" class="form-control form-control-sm">
                                </div>
                                <div class="col-6 col-md-3 mb-2 mb-md-0 d-flex align-items-end">
                                    <button id="btnFilter" class="btn btn-primary btn-sm w-100" data-ga="click_filter_checkin" data-ga-label="Filter Check-in">
                                        <i class="bi bi-search"></i> Filter
                                    </button>
                                </div>
                                <div class="col-6 col-md-3 mb-2 mb-md-0 d-flex align-items-end">
                                    <button id="btnReset" class="btn btn-secondary btn-sm w-100" data-ga="click_reset_checkin_filter" data-ga-label="Reset Filter">
                                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                                    </button>
                                </div>
                                <div class="col-6 col-md-3 mb-2 mb-md-0 d-flex align-items-end">
                                    <div class="btn-group w-100" role="group">
                                        <button id="btnExportCSV" class="btn btn-success btn-sm" data-ga="click_export_csv_checkin" data-ga-label="Export CSV">
                                            <i class="bi bi-filetype-csv"></i> CSV
                                        </button>
                                        <button id="btnExportXLSX" class="btn btn-info btn-sm text-white" data-ga="click_export_xlsx_checkin" data-ga-label="Export XLSX">
                                            <i class="bi bi-filetype-xlsx"></i> XLSX
                                        </button>
                                        <button id="btnPrint" class="btn btn-dark btn-sm" data-ga="click_print_checkin" data-ga-label="Print Check-in">
                                            <i class="bi bi-printer"></i> <i class="bi bi-filetype-csv"></i>
                                        </button>
                                        <button id="btnPrintXLSX" class="btn btn-warning btn-sm" data-ga="click_print_xlsx_checkin" data-ga-label="Print XLSX">
                                            <i class="bi bi-printer"></i> <i class="bi bi-filetype-xlsx"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered table-striped table-hover" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th style="width:4%">#</th>
                                        <th style="width:5%">Status</th>
                                        <th style="width:14%">Employee</th>
                                        <th style="width:10%">Department</th>
                                        <th style="width:8%">Type</th>
                                        <th style="width:10%">Check In</th>
                                        <th style="width:10%">Check Out</th>
                                        <th style="width:8%">Total</th>
                                        <th style="width:5%">Attach</th>
                                        <th style="width:20%">Notes</th>
                                    </tr>
                                </thead>
                                <tfoot class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Status</th>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th>Type</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Total</th>
                                        <th>Attach</th>
                                        <th>Notes</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div><!-- /.card-body -->
                </div><!-- /.card -->
            </div><!-- /.col-12 -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content -->

<div id="formModal"></div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for jQuery to be available
    var checkJQuery = setInterval(function() {
        if (typeof $ !== 'undefined') {
            clearInterval(checkJQuery);
            initDataTable();
        }
    }, 100);
});

function initDataTable() {
    // Destroy existing DataTable if exists
    if ($.fn.DataTable.isDataTable('#datatable')) {
        $('#datatable').DataTable().destroy();
    }
    
    // Initialize DataTable
    var table = $('#datatable').DataTable({
        "ajax": {
            "url": "pages/tableRendering/dataCheckinLogs.php",
            "type": "POST",
            "dataSrc": "data",
            "data": function(d) {
                d.department = $('#filterDepartment').val();
                d.employee = $('#filterEmployee').val();
                d.status = $('#filterStatus').val();
                d.dateFrom = $('#filterDateFrom').val();
                d.dateTo = $('#filterDateTo').val();
            }
        },
        "columns": [
            { "data": 0, "className": "text-center" },
            { "data": 1, "className": "text-center" },
            { "data": 2 },
            { "data": 3 },
            { "data": 4 },
            { "data": 5 },
            { "data": 6 },
            { "data": 7 },
            { "data": 8, "className": "text-center" },
            { "data": 9 }
        ],
        "columnDefs": [
            { "targets": [0, 1, 8], "orderable": false, "searchable": false }
        ],
        "order": [],
        "pageLength": 10,
        "responsive": true,
        "language": {
            "emptyTable": "No check-in logs found"
        }
    });

    // Filter button click
    $('#btnFilter').on('click', function() {
        table.ajax.reload();
    });

    // Reset button click
    $('#btnReset').on('click', function() {
        $('#filterDepartment').val('');
        $('#filterEmployee').val('');
        $('#filterStatus').val('');
        $('#filterDateFrom').val('');
        $('#filterDateTo').val('');
        table.ajax.reload();
    });

    // Export function
    function exportData(format) {
        var params = new URLSearchParams({
            format: format,
            department: $('#filterDepartment').val() || '',
            employee: $('#filterEmployee').val() || '',
            status: $('#filterStatus').val() || '',
            dateFrom: $('#filterDateFrom').val() || '',
            dateTo: $('#filterDateTo').val() || ''
        });
        window.location.href = 'pages/tableRendering/exportCheckinLogs.php?' + params.toString();
    }

    // Export CSV
    $('#btnExportCSV').on('click', function() {
        exportData('csv');
    });

    // Export XLSX
    $('#btnExportXLSX').on('click', function() {
        exportData('xlsx');
    });

    // Print function
    function printData(format) {
        var params = new URLSearchParams({
            format: format,
            department: $('#filterDepartment').val() || '',
            employee: $('#filterEmployee').val() || '',
            status: $('#filterStatus').val() || '',
            dateFrom: $('#filterDateFrom').val() || '',
            dateTo: $('#filterDateTo').val() || ''
        });
        window.open('pages/tableRendering/printCheckinLogs.php?' + params.toString(), '_blank');
    }

    // Print CSV
    $('#btnPrint').on('click', function() {
        printData('csv');
    });

    // Print XLSX
    $('#btnPrintXLSX').on('click', function() {
        printData('xlsx');
    });
}
</script>
