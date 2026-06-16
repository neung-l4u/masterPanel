<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-LGKDYHL23T');
</script>
<?php
global $db, $date;
?>
<style>
    .modal-body {font-size:0.9rem;}
    small{font-size:0.7rem;}
    .table .thead-dark th {background-color:#212529!important;}
    ::placeholder {color:#DDDDDD!important;opacity:1;}
    .nav-tabs .nav-link {color:#495057;font-weight:500;}
    .nav-tabs .nav-link.active {color:#0d6efd;font-weight:600;}
    .nav-tabs .nav-link i {margin-right:5px;}
    .badge-show {background-color:#28a745;}
    .badge-hide {background-color:#6c757d;}
    /* Table responsive like reportLifeSpan */
    .table-responsive {max-height:70vh; overflow-y:auto;}
    #aiLogsTable {font-size:0.8rem;}
    #aiLogsTable thead {position:sticky; top:0; z-index:2; background:#212529; color:#fff;}
    #aiLogsTable th {font-size:0.75rem; white-space:nowrap; padding:8px 6px;}
    #aiLogsTable td {white-space:nowrap; padding:6px;}
    #aiLogsTable code {font-size:0.7rem;}
</style>
<link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/css/bootstrap.css">
<link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="plugins/datatables-bs5/css/datatables-bs5.min.css">

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <i class="bi bi-robot"></i> AI Management
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php">Home</a></li>
                    <li class="breadcrumb-item active">AI Management</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#ai-logs-panel">
                    <i class="bi bi-journal-text"></i> AI Signup Logs
                </button>
            </li>
        </ul>

        <div class="tab-pane fade show active" id="ai-logs-panel">
                <div class="card p-3">
                    <div class="card-header bg-white">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-0"><i class="bi bi-robot"></i> AI Signup Logs</h5>
                                <small class="text-muted">Customers who signed up for AI Receptionist or AI + Bundles</small>
                            </div>
                            <div class="col-auto">
                                <div class="d-flex gap-2">
                                    <input type="text" id="aiSearchInput" class="form-control form-control-sm" placeholder="Search..." style="width:180px;">
                                    <select id="aiCountryFilter" class="form-select form-select-sm" style="width:120px;">
                                        <option value="">All Countries</option>
                                        <option value="AU">Australia</option>
                                        <option value="NZ">New Zealand</option>
                                        <option value="UK">United Kingdom</option>
                                        <option value="US">United States</option>
                                        <option value="CA">Canada</option>
                                        <option value="TH">Thailand</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="aiLogsTable" class="table table-bordered table-striped table-hover mb-0" style="width:100%">
                                <thead class="thead-dark text-white">
                                    <tr>
                                        <th>Date</th>
                                        <th>Shop Name</th>
                                        <th>Country</th>
                                        <th>Main Product</th>
                                        <th>Main Price ID</th>
                                        <th>Add-ons</th>
                                        <th>Add-on Price ID</th>
                                        <th>Customer ID</th>
                                        <th>Email</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.js"></script>
<script src="plugins/datatables-bs5/js/datatables-bs5.min.js"></script>
<script>
function sendMail(btn) {
    const payload = $(btn).data('payload');
    if(!payload) return alert('No payload data');
    
    $(btn).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Sending...');
    
    $.ajax({
        url: 'https://hook.us1.make.com/6vloshre04tb1xtkjhgawblx2jk7a2ji',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function() {
            alert('Mail sent successfully!');
            $(btn).prop('disabled', false).html('<i class="bi bi-envelope"></i> Send Mail');
        },
        error: function() {
            alert('Error sending mail');
            $(btn).prop('disabled', false).html('<i class="bi bi-envelope"></i> Send Mail');
        }
    });
}

$(function() {
    // AI Logs Table
    const aiTable = $('#aiLogsTable').DataTable({
        pagingType: 'full_numbers',
        pageLength: 25,
        lengthMenu: [[25,50,100,-1],[25,50,100,'All']],
        ajax: {
            url: 'pages/tableRendering/dataAiLogs.php',
            type: 'POST',
            dataSrc: 'data',
            data: function(d) {
                d.country = $('#aiCountryFilter').val();
            }
        },
        columns: [
            {data: 0}, // Date
            {data: 1}, // Shop Name
            {data: 2}, // Country
            {data: 3}, // Main Product
            {data: 4}, // Main Price ID (with discount inside)
            {data: 5}, // Add-ons
            {data: 6}, // Add-on Price ID
            {data: 7}, // Customer ID
            {data: 8}, // Email
            {data: 9, orderable: false} // Action
        ],
        columnDefs: [
            {targets: [3,5], orderable: false},
            {targets: 9, className: 'text-center', width: '100px'}
        ]
    });
    
    // Search functionality
    $('#aiSearchInput').on('keyup', function() {
        aiTable.search(this.value).draw();
    });
    
    $('#aiCountryFilter').on('change', function() {
        aiTable.ajax.reload();
    });
    
});
</script>
