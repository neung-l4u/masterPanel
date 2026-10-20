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

$password = "Localeats#".date("Y");

// Identify whoever is looking at this page, for the watermark below. Falls back
// to the staff id so a missing name never leaves the mark blank.
$wmName = $_SESSION['nickName'] ?? ($_SESSION['name'] ?? '');
$wmId   = $_SESSION['id'] ?? '';
$wmText = trim(($wmName !== '' ? $wmName : 'staff') . ' #' . $wmId . '  ' . date('Y-m-d H:i'));
?>
<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">

<style>
<?php
    // One SVG tile carrying the viewer's name, repeated by CSS wherever the
    // watermark is applied. Lower alpha keeps it readable past the data.
    $wmSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="120">'
           . '<text x="0" y="70" transform="rotate(-22 0 70)" '
           . 'fill="rgba(15,23,42,0.05)" font-family="Montserrat,Arial,sans-serif" '
           . 'font-size="17">' . htmlspecialchars($wmText, ENT_QUOTES) . '</text></svg>';
    echo ':root{--wm-image:url(\'data:image/svg+xml;utf8,' . rawurlencode($wmSvg) . '\');}';
?>
    .clickable {
        cursor: pointer;
    }
    .thead-dark {
        background-color: #212529;
    }

    /* This table carries more columns than the rest of the panel, so it runs a
       smaller type scale than the shared table styles. Scoped to this page so
       other tables keep the standard 14px/14.4px. !important is needed because
       the base rules in master-panel.css use it too. */
    #signupTable thead th {
        font-size: 12px !important;
        padding: 0.6rem 0.7rem !important;
        letter-spacing: 0.03em !important;
        /* Keep every header on one line so the row stays a single height -
           "Payment Methods" was wrapping and making the header twice as tall. */
        white-space: nowrap !important;
    }
    #signupTable tbody td {
        font-size: 10px !important;
        padding: 0.6rem 0.7rem !important;
    }
    /* Badges and chips inherit the cell size, which would leave them unreadable
       at 10px, so hold them slightly above the body text. */
    #signupTable tbody td .badge {
        font-size: 10px !important;
    }
    #signupTable tbody td img.rounded-circle {
        width: 22px !important;
        height: 22px !important;
    }

    /* Identifying watermark. Tiled across the content itself (not the margins)
       so cropping it out also crops the data away. Faint enough to read past,
       and raising a screenshot's contrast brings it back. */
    .wm-wrap {
        position: relative;
    }
    .wm-wrap::after,
    .modal-content::after {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;   /* never intercept clicks */
        z-index: 5;
        background-image: var(--wm-image);
        background-repeat: repeat;
    }
    /* Bootstrap moves the modal to <body>, outside .wm-wrap, so it needs the
       mark applied directly - this is where the most detailed data appears
       (customer email, Stripe ids, amounts). */
    .modal-content {
        position: relative;
        overflow: hidden;   /* keep the tile inside the rounded corners */
    }
    .data-notice {
        font-size: 11px;
        color: #b91c1c;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 6px;
        padding: 6px 10px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
</style>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <i class="bi bi-person-plus mr-2"></i>
                    SignUp Logs (Staff)
                </h4>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item"><a href="main.php?p=tools">Tools</a></li>
                    <li class="breadcrumb-item active">SignUp Logs</li>
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
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label mb-1"><i class="bi bi-calendar-range"></i> Date Range</label>
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
                                    <option value="AU">Australia</option>
                                    <option value="NZ">New Zealand</option>
                                    <option value="UK">United Kingdom</option>
                                    <option value="CA">Canada</option>
                                    <option value="US">United States</option>
                                    <option value="TH">Thailand</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1"><i class="bi bi-shop"></i> Shop Type</label>
                                <select id="filterShopType" class="form-control form-control-sm">
                                    <option value="">All Types</option>
                                    <option value="Restaurant">Restaurant</option>
                                    <option value="Massage">Massage</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1"><i class="bi bi-person"></i> Sale Agent</label>
                                <select id="filterSale" class="form-control form-control-sm">
                                    <option value="">All Agents</option>
                                </select>
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
                        <div class="card">
                            <div class="card-body table-responsive p-4 wm-wrap" style="height: 630px;">
                                <div class="data-notice">
                                    <i class="bi bi-shield-lock"></i>
                                    <span>ข้อมูลลูกค้า &mdash; ห้ามเผยแพร่ออกนอกบริษัท</span>
                                </div>
                                <table id="signupTable" class="table table-borderless table-striped table-hover"
                                       style="width:100%">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th style="width:11%">Country</th>
                                        <th style="width:10%">Shop Type</th>
                                        <th style="width:24%">Shop name</th>
                                        <th style="width:11%">Sale</th>
                                        <th style="width:13%">History Payment</th>
                                        <th style="width:15%">Payment Methods</th>
                                        <th style="width:13%">Timestamp</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                    </div>
                </div>
            </div><!-- /.col-md-12 -->
        </div><!-- /.row -->

        <div id="alert" style="
            display: block;
            right: 20px;
            bottom: 30px;
            position: fixed;
            background-color: #007bff;
            color: white;
            padding: 15px;
            border-radius: 5px;
            z-index: 1;
            box-shadow: 0 4px 4px 0 rgb(191 191 191 / 20%);
            ">
            Text Copied
        </div>

        <!-- Modal -->
        <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4><span class="logType font-weight-light">View</span>: <span class="shopName text-primary"></span></h4>
                        <button onclick="copyText();" style="color: #bbb; border: none; background: none;"><i class="far fa-copy" style="font-size: 25px;"></i></button>
                    </div> <!-- modal-header -->

                    <div class="modal-body">
                        <pre id="jsonText" class="json">jsonData</pre>
                        <hr class="my-3">
                        <h5 class="mt-4">Stripe Result</h5>
                        <pre id="stripeResult" class="json">stripeResult</pre>
                    </div> <!-- modal-body -->

                    <div class="modal-footer">
                        
                    </div> <!-- modal-footer -->
                </div> <!-- modal-content -->
            </div> <!-- modal-dialog -->
        </div> <!-- modal -->

    </div><!-- /.container-fluid -->
</div>
<!-- /.content -->

<script>
// main.php loads jQuery and DataTables further down the page, so wait for them
// rather than loading a second copy here - two copies initialise this table
// twice and break sorting and search.
(function bootPage() {
    if (typeof window.jQuery === 'undefined' ||
        typeof window.jQuery.fn.DataTable === 'undefined') {
        return setTimeout(bootPage, 50);
    }
    jQuery(function ($) {
    let shopName = $(".shopName");
    let logType = $(".logType");
    let signupTable = $('#signupTable').DataTable( {
        pagingType: 'full_numbers',
        ajax: {
            url: 'pages/tableRendering/dataSignupLogs.php',
            type: 'POST',
            data: function(d) {
                d.dateStart = $('#filterDateStart').val();
                d.dateEnd = $('#filterDateEnd').val();
                d.country = $('#filterCountry').val();
                d.shopType = $('#filterShopType').val();
                d.sale = $('#filterSale').val();
            },
            dataSrc: 'data'
        },
        "pageLength": 10,
        order: [[6, 'desc']],   // Timestamp is the last column
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, 'All']
        ],columnDefs: [
            { targets: [2, 3, 6], className: 'dt-left' },
            // History Payment / Payment Methods fetch per row over AJAX, so only
            // the rows on screen ever hold a value - ordering by them would be
            // misleading. Use the Search box to find a shop instead.
            { targets: [4, 5], className: 'dt-center', "orderable": false }
        ],
        drawCallback: function() {
            // The partial defining loadFirstPaid loads later in the page, so the
            // first draw can fire before it exists.
            if (typeof loadPayHistory === 'function') loadPayHistory();
            if (typeof loadPayMethod === 'function') loadPayMethod();
        }
    } );

    // Load sale agents for filter dropdown
    $.ajax({
        url: 'pages/tableRendering/getSaleAgents.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            let options = '<option value="">All Agents</option>';
            data.forEach(function(agent) {
                options += '<option value="' + agent.nick + '">' + agent.nick + ' (' + agent.name + ')</option>';
            });
            $('#filterSale').html(options);
        }
    });

    // Apply filter button
    $('#btnApplyFilter').on('click', function() {
        signupTable.ajax.reload();
    });

    // Reset filter button
    $('#btnResetFilter').on('click', function() {
        $('#filterDateStart').val('');
        $('#filterDateEnd').val('');
        $('#filterCountry').val('');
        $('#filterShopType').val('');
        $('#filterSale').val('');
        signupTable.ajax.reload();
    });

    window.viewJson = function(data, result) {
        let signupData = data;
        let stripeResult = result;
        console.log("data", data.shopName);
        if(data !== undefined){ shopName.text(data.ShopName); logType.text("Signup");}
        if(data.restaurant_name !== undefined){ shopName.text(data.restaurant_name); logType.text("Stripe");}

        if(stripeResult === undefined || stripeResult === null) {
            stripeResult = "---";
        }

        $('#formModal').modal('show');
        $('#jsonText').html(JSON.stringify(signupData, undefined, 2));
        $('#stripeResult').html(JSON.stringify(stripeResult, undefined, 2));
    }

    // main.php's hide.bs.modal handler calls this from global scope, so it has
    // to stay reachable there even though this script runs inside a closure.
    window.resetForm = function () {
        shopName.text('');
    };

    function showCopy() {
        $("#alert").fadeIn(500);
        setTimeout(function () {
            $("#alert").fadeOut();
        }, 1000);
    }

    window.copyText = function() {
        const copyText = document.querySelector("pre#jsonText");
        navigator.clipboard.writeText(copyText.textContent)
        showCopy();
    }

    });
})();
</script>

<?php include __DIR__ . '/partials/firstPaidColumn.php'; ?>
