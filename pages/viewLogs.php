<?php
global $db, $date;

$password = "Localeats#".date("Y");
?>
<link rel="stylesheet" href="plugins/datatables-bs5/css/datatables-bs5.min.css">

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
                <h3 class="m-0">
                    <svg class="nav-icon mr-3" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM609.3 512H471.4c5.4-9.4 8.6-20.3 8.6-32v-8c0-60.7-27.1-115.2-69.8-151.8c2.4-.1 4.7-.2 7.1-.2h61.4C567.8 320 640 392.2 640 481.3c0 17-13.8 30.7-30.7 30.7zM432 256c-31 0-59-12.6-79.3-32.9C372.4 196.5 384 163.6 384 128c0-26.8-6.6-52.1-18.3-74.3C384.3 40.1 407.2 32 432 32c61.9 0 112 50.1 112 112s-50.1 112-112 112z" fill="#000000" /></svg>
                    Logs
                </h3>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
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
                                        <th style="width:15%">Date time</th>
                                        <th style="width:15%">Country</th>
                                        <th style="width:40%">Shopname</th>
                                        <th style="width:10%">SignupJson</th>
                                        <th style="width:10%">StripeJson</th>
                                        <th style="width:10%">Contract</th>
                                        <th style="width:10%">Status</th>
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
                        <h3>View Logs</h3>
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
            { targets: [3, 4, 5], className: 'dt-center' },
            { targets: [6], className: 'dt-right' }
        ]
    } );

    function viewJson(data) {
        let jsonData = data;
        
        $('#formModal').modal('show');
        $('#jsonText').html(JSON.stringify(jsonData, undefined, 2));
    }

    const resetForm = () => {
        console.log('resetForm');
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

