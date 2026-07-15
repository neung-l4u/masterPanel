<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-LGKDYHL23T');
</script>
<?php global $db; ?>
<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

<style>
    .clickable { cursor: pointer; }
    .thead-dark { background-color: #212529; }
    .note-cell { max-width: 240px; white-space: normal; word-break: break-word; }
    #posNewCustomerTable td { vertical-align: top; }
    #posNewCustomerTable td div { white-space: nowrap; margin-bottom: 2px; }
    .status-badge {
        display: inline-block; padding: 2px 8px; font-size: 11px;
        border-radius: 10px; font-weight: 600;
    }
    .status-active       { background:#d1e7dd; color:#0f5132; }
    .status-rescheduled  { background:#fff3cd; color:#664d03; }
    .status-cancelled    { background:#f8d7da; color:#842029; }
</style>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <i class="bi bi-calendar-check mr-2"></i>
                    Sign Up Form Logs
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item active">Sign Up Form Logs</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label mb-1"><i class="bi bi-calendar-range"></i> Submitted Range</label>
                                <div class="d-flex align-items-center" style="gap:6px;">
                                    <input type="date" id="filterDateStart" class="form-control form-control-sm">
                                    <span style="font-size:12px;">to</span>
                                    <input type="date" id="filterDateEnd" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1"><i class="bi bi-globe"></i> Country</label>
                                <select id="filterCountry" class="form-control form-control-sm">
                                    <option value="">All Countries</option>
                                    <option value="Australia">Australia</option>
                                    <option value="New Zealand">New Zealand</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="Canada">Canada</option>
                                    <option value="United States">United States</option>
                                    <option value="Thailand">Thailand</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1"><i class="bi bi-flag"></i> Status</label>
                                <select id="filterStatus" class="form-control form-control-sm">
                                    <option value="">All</option>
                                    <option value="active">Active</option>
                                    <option value="rescheduled">Rescheduled</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1"><i class="bi bi-search"></i> Search</label>
                                <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Name / email / shop">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">&nbsp;</label>
                                <div class="d-flex" style="gap:6px;">
                                    <button class="btn btn-sm btn-primary w-100" id="btnApplyFilter"><i class="bi bi-funnel"></i> Filter</button>
                                    <button class="btn btn-sm btn-secondary" id="btnResetFilter" title="Reset"><i class="bi bi-arrow-clockwise"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs mb-3" id="logTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="booking-tab" data-toggle="tab" href="#bookingTabPane" role="tab" aria-controls="bookingTabPane" aria-selected="true">Booking Changes System</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="pos-new-tab" data-toggle="tab" href="#posNewTabPane" role="tab" aria-controls="posNewTabPane" aria-selected="false">Logs POS and New Online Order ( New Customer )</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="pos-old-tab" data-toggle="tab" href="#posOldTabPane" role="tab" aria-controls="posOldTabPane" aria-selected="false">Logs POS and New Online Order (Old Customer)</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="logTabsContent">
                            <div class="tab-pane fade show active" id="bookingTabPane" role="tabpanel" aria-labelledby="booking-tab">
                                <div class="table-responsive p-4" style="min-height: 630px;">
                                <h5 class="mb-3">Booking Changes System</h5>
                                <table id="formASAPTable" class="table table-borderless table-striped table-hover" style="width:100%">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th style="width:13%">Date</th>
                                        <th style="width:13%">Full Name</th>
                                        <th style="width:13%">Shop Name</th>
                                        <th style="width:8%">Country</th>
                                        <th style="width:14%">Email</th>
                                        <th style="width:14%">Product Needed</th>
                                        <th style="width:12%">Note</th>
                                        <th style="width:10%">Appointment</th>
                                        <th style="width:8%">Status</th>
                                    </tr>
                                    </thead>
                                </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="posNewTabPane" role="tabpanel" aria-labelledby="pos-new-tab">
                                <div class="table-responsive p-4">
                                <h5 class="mb-3">Logs POS and New Online Order ( New Customer )</h5>
                                <table id="posNewCustomerTable" class="table table-borderless table-striped table-hover" style="width:100%">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th>Date</th>
                                        <!-- <th>Submission ID</th> -->
                                        <th>Shop Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Manager Name</th>
                                        <th>Country</th>
                                        <th>Currency</th>
                                        <th>Trading Name</th>
                                        <th>Trading Address</th>
                                        <th>Terminal Delivery Address</th>
                                        <th>Service</th>
                                        <th>Opening Hours</th>
                                        <th>EftPOS Model</th>
                                        <th>Qty</th>
                                        <th>Website</th>
                                        <th>Third Party Platforms</th>
                                        <th>Restaurant Address</th>
                                        <th>Delivery Service Need</th>
                                        <th>Deliver By</th>
                                        <th>Serviced Area</th>
                                        <th>Minimum Order</th>
                                        <th>Delivery Fee</th>
                                        <th>Inhouse Delivery</th>
                                        <th>Logo Status</th>
                                        <th>GMB Access</th>
                                        <th>Facebook Page Access</th>
                                        <th>Domain Hosting</th>
                                        <!-- <th>Marketing Tricks Opt In</th> -->
                                        <th>Logo/Menu</th>
                                        <th>BRD</th>
                                        <th>BS</th>
                                        <th>DirectorID</th>
                                        <th>adyenAgree</th>
                                        <th>Status</th>
                                    </tr>
                                    </thead>
                                </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="posOldTabPane" role="tabpanel" aria-labelledby="pos-old-tab">
                                <div class="table-responsive p-4">
                                <h5 class="mb-3">Logs POS and New Online Order (Old Customer)</h5>
                                <table id="posOldCustomerTable" class="table table-borderless table-striped table-hover" style="width:100%">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Shop Name</th>
                                        <th>Country</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Manager Name</th>
                                        <th>BRD</th>
                                        <th>BS</th>
                                        <th>DirectorID</th>
                                        <th>adyenAgree</th>
                                    </tr>
                                    </thead>
                                </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4><span class="font-weight-light">Booking Detail</span>: <span class="shopName text-primary"></span></h4>
                    </div>
                    <div class="modal-body">
                        <pre id="jsonText" class="json"></pre>
                    </div>
                    <div class="modal-footer"></div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/datatables-bs5/js/datatables-bs5.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/jszip/jszip.min.js"></script>
<script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script>
    let shopName = $(".shopName");

    const exportStrip = {
        format: {
            body: function(data) {
                if (data === null || data === undefined) return '';
                let s = String(data);
                s = s.replace(/<br\s*\/?>(?=)/gi, ' ');
                s = s.replace(/<[^>]+>/g, '');
                s = s.replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
                return s.trim();
            }
        }
    };

    const buildExportButtons = function(filename) {
        return [
            { extend: 'excelHtml5', text: '<i class="bi bi-file-earmark-excel"></i> Excel', className: 'btn btn-sm btn-success', title: filename, exportOptions: exportStrip },
            // { extend: 'csvHtml5',   text: '<i class="bi bi-filetype-csv"></i> CSV',       className: 'btn btn-sm btn-secondary', title: filename, exportOptions: exportStrip },
            // { extend: 'print',      text: '<i class="bi bi-printer"></i> Print',          className: 'btn btn-sm btn-info', title: filename, exportOptions: exportStrip }
        ];
    };

    let formASAPTable = $('#formASAPTable').DataTable({
        pagingType: 'full_numbers',
        dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>rt<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: buildExportButtons('BookingChangesSystem'),
        ajax: {
            url: 'pages/tableRendering/dataFormASAPLogs.php',
            type: 'POST',
            data: function(d) {
                d.dateStart = $('#filterDateStart').val();
                d.dateEnd   = $('#filterDateEnd').val();
                d.country   = $('#filterCountry').val();
                d.status    = $('#filterStatus').val();
                d.search    = $('#filterSearch').val();
            },
            dataSrc: 'data'
        },
        pageLength: 10,
        order: [[0, 'desc']], // เรียงตามคอลัมน์ Date (ใช้ data-sort = timestamp)
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, 'All']
        ],
        columnDefs: [
            {
                targets: 0,
                type: 'string',
                render: function(data, type) {
                    if (type === 'sort' || type === 'type') {
                        const m = (data || '').match(/data-sort="([^"]+)"/);
                        return m ? m[1] : data;
                    }
                    return data;
                }
            },
            { targets: [0,1,2,3,4,5,7,8], className: 'dt-left' },
            { targets: [6], className: 'dt-left note-cell' },
            { targets: [8], className: 'dt-center' }
        ]
    });

    let posNewCustomerTable = $('#posNewCustomerTable').DataTable({
        pagingType: 'full_numbers',
        dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>rt<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: buildExportButtons('POS_NewCustomerLogs'),
        ajax: {
            url: 'pages/tableRendering/dataPOSNewCustomerLogs.php',
            type: 'POST',
            dataSrc: 'data'
        },
        pageLength: 10,
        order: [[0, 'desc']],
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, 'All']
        ],
        columnDefs: [
            { targets: '_all', className: 'dt-left' }
        ]
    });

    let posOldCustomerTable = $('#posOldCustomerTable').DataTable({
        pagingType: 'full_numbers',
        dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>rt<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: buildExportButtons('POS_OldCustomerLogs'),
        ajax: {
            url: 'pages/tableRendering/dataPOSOldCustomerLogs.php',
            type: 'POST',
            dataSrc: 'data'
        },
        pageLength: 10,
        order: [[0, 'desc']],
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, 'All']
        ],
        columnDefs: [
            { targets: '_all', className: 'dt-left' }
        ]
    });

    $('#btnApplyFilter').on('click', function() { formASAPTable.ajax.reload(); });
    $('#filterSearch').on('keypress', function(e) { if(e.which === 13) formASAPTable.ajax.reload(); });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    $('#btnResetFilter').on('click', function() {
        $('#filterDateStart,#filterDateEnd,#filterCountry,#filterStatus,#filterSearch').val('');
        formASAPTable.ajax.reload();
    });

    function viewJson(data) {
        if(data && data.fullName){ shopName.text(data.restaurantName || data.fullName); }
        $('#formModal').modal('show');
        $('#jsonText').html(JSON.stringify(data, undefined, 2));
    }
</script>
