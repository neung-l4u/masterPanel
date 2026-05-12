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
?>
<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">

<style>
    .clickable {
        cursor: pointer;
    }
    .thead-dark {
        background-color: #212529;
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
                            <div class="card-body table-responsive p-4" style="height: 630px;">
                                <table id="signupTable" class="table table-borderless table-striped table-hover"
                                       style="width:100%">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th style="width:17%">Timestamp</th>
                                        <th style="width:10%">Country</th>
                                        <th style="width:10%">Shop Type</th>
                                        <th style="width:40%">Shop name</th>
                                        <th style="width:5%">Signup</th>
                                        <!-- <th style="width:5%">Stripe</th> -->
                                        <th style="width:5%">Contract</th>
                                        <!-- <th style="width:5%">Status</th> -->
                                        <th style="width:12%">Sale</th>
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

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/datatables-bs5/js/datatables-bs5.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script>
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
        order: [[0, 'desc']],
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, 'All']
        ],columnDefs: [
            { targets: [0,3], className: 'dt-left' },
            { targets: [4], className: 'dt-center', "orderable": "false" },
            { targets: [5], className: 'dt-right', "orderable": "false" },
            { targets: [6], className: 'dt-left' }
        ]
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

    function viewJson(data, result) {
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

    const resetForm = () => {
        console.log('resetForm');
        shopName.text('');
    }// const

    function showCopy() {
        $("#alert").fadeIn(500);
        setTimeout(function () {
            $("#alert").fadeOut();
        }, 1000);
    }

    function copyText() {
        const copyText = document.querySelector("pre#jsonText");
        navigator.clipboard.writeText(copyText.textContent)
        showCopy();
    }
</script>
