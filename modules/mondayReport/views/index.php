<?php
global $db;

include '../assets/db/db.php';
include "../assets/db/initDB.php";
$param['act'] = 'read';

$return['result'] = '';
$return['msg'] = '';
$return['data'] = '';
$return['act'] = $param['act'];

$id = $_GET['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monday Report</title>
    <link rel="stylesheet" href="../assets/css/bootstrap5.3.3.min.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.5.3.3.min.js"></script>
</head>

<body>
<div class="container pt-5">
    <div class="row mb-5">
        <div class="col">
            <h1 class="text-center">MONDAY REPORT</h1>
        </div>
    </div>
    <div class="inner-row mb-4">
        <button type="button" class="btn sendReport" onclick="sendReport()">Send Report</button>
    </div>
    <div class="row">
        <div class="col text-center">
            <span>&nbsp;</span>
            <span class="text-alert ">
                <b class="text-success">DONE !!</b>
            </sapn>
        </div>
    </div>
    <div class="row">
        <table id="reportData" class="table table-striped table-hover">
            <thead class="table-dark thead-dark">
                <tr>
                    <th style="width:5%;">Top</th>
                    <th>Name</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<input type="hidden" id="loginID" value="<?php echo $id; ?>">
</body>

<script src="../assets/js/datatables-bs5.min.js"></script>

<script>
let loginID = $('#loginID').val();

setInterval(reloadTable, 1000);

let reportTable = $('#reportData').DataTable( {
    pagingType: 'full_numbers',
    ajax: {
        url: '../models/actionReport.php',
        dataSrc: 'data'
    },
    "pageLength": 50,
    order: [[ 2, "desc" ], [ 1, "asc" ]],
    lengthMenu: [
        [8, 25, 50, -1],
        ['Fit', 25, 50, 'All']
    ],columnDefs: [
        { targets: [2], className: 'dt-right' }
    ]
} );

function reloadTable()  {
    reportTable.ajax.reload();
}

function sendReport () {
    const sendReport = $.ajax({
        url: '../models/actionReport.php',
        type: 'POST',
        data: {
            loginID: loginID,
            act: 'add'
        }
    });

    sendReport.done(function (response) {
        console.log(response);
        reloadTable();
        $('.text-alert').show();
        $('.text-alert').fadeOut();

    });

    sendReport.fail(function (xhr, status, error) {
        console.log("ajax call fail!!");
        console.log(status + ": " + error);
        return false;
    });
}

$(() => {
    $('.text-alert').hide();
});
</script>

</html>