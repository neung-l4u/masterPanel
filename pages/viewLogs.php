<?php require_once "../assets/api/googleAnalytics.php";?>
<?php
global $db, $date;

$password = "Localeats#".date("Y");
?>
<link rel="stylesheet" href="plugins/datatables-bs5/css/datatables-bs4.min.css">

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
                    <svg class="nav-icon mr-3" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512"><path d="M0 64C0 28.7 28.7 0 64 0H224V128c0 17.7 14.3 32 32 32H384V285.7l-86.8 86.8c-10.3 10.3-17.5 23.1-21 37.2l-18.7 74.9c-2.3 9.2-1.8 18.8 1.3 27.5H64c-35.3 0-64-28.7-64-64V64zm384 64H256V0L384 128zM549.8 235.7l14.4 14.4c15.6 15.6 15.6 40.9 0 56.6l-29.4 29.4-71-71 29.4-29.4c15.6-15.6 40.9-15.6 56.6 0zM311.9 417L441.1 287.8l71 71L382.9 487.9c-4.1 4.1-9.2 7-14.9 8.4l-60.1 15c-5.5 1.4-11.2-.2-15.2-4.2s-5.6-9.7-4.2-15.2l15-60.1c1.4-5.6 4.3-10.8 8.4-14.9z" fill="#000000" /></svg>
                    SignUp Form Logs
                </h4>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item"><a href="main.php?p=tools">Tools</a></li>
                    <li class="breadcrumb-item active">Logs</li>
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
                    <div class="card-header d-flex justify-content-end">

                    </div>
                    <div class="card-body">
                        <div class="card">
                            <div class="card-body table-responsive p-4" style="height: 630px;">
                                <table id="signupTable" class="table table-borderless table-striped table-hover"
                                       style="width:100%">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th style="width:11%">Timestamp</th>
                                        <th style="width:10%">Country</th>
                                        <th style="width:49%">Shop name</th>
                                        <th style="width:5%">Signup</th>
                                        <th style="width:5%">Stripe</th>
                                        <th style="width:5%">Contract</th>
                                        <th style="width:5%">Status</th>
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
<script>
    let shopName = $(".shopName");
    let logType = $(".logType");
    let signupTable = $('#signupTable').DataTable( {
        pagingType: 'full_numbers',
        ajax: {
            url: 'pages/tableRendering/dataViewLogs.php',
            dataSrc: 'data'
        },
        "pageLength": 8,
        order: [[0, 'desc']],
        lengthMenu: [
            [8, 25, 50, -1],
            ['Fit', 25, 50, 'All']
        ],columnDefs: [
            { targets: [0], className: 'dt-left' },
            { targets: [3, 4, 5], className: 'dt-center', "orderable": "false" },
            { targets: [6], className: 'dt-right' , "orderable": "false"}
        ]
    } );

    function viewJson(data) {
        let jsonData = data;
        console.log("data", data.shopName);
        if(data !== undefined){ shopName.text(data.ShopName); logType.text("Signup");}
        if(data.restaurant_name !== undefined){ shopName.text(data.restaurant_name); logType.text("Stripe");}
        
        $('#formModal').modal('show');
        $('#jsonText').html(JSON.stringify(jsonData, undefined, 2));
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

