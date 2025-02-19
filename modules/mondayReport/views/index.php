<?php
session_start();
global $db;

include '../assets/db/db.php';
include "../assets/db/initDB.php";
$param['act'] = 'read';

$return['result'] = '';
$return['msg'] = '';
$return['data'] = '';
$return['act'] = $param['act'];

$id = $_GET['id'];


$person = $db->query('SELECT st.sNickName AS "name", te.name AS "team", st.sPic AS "pic"
    FROM staffs st 
    LEFT JOIN Team te on st.teamID = te.id 
    WHERE st.sID = ? 
    ',$_SESSION['id'])->fetchArray();

$stat = $db->query('SELECT COUNT(mo.id) AS "count" FROM mondayslowreportlogs mo WHERE mo.staffID = ? GROUP BY mo.staffID;',$_SESSION['id'])->fetchArray();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../assets/css/bootstrap5.3.3.min.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.5.3.3.min.js"></script>
    <title>Monday Report</title>
    <style>
        .dt-right{
            padding-right: 1.5rem !important;
        }
        .avatar{
            height: 10rem;
            border-radius: 10px;
        }
    </style>
</head>
<body>
<div class="container pt-5">
    <div class="row mb-3">
        <div class="col text-center">
            <img src="../assets/images/mondayLogo.png" alt="monday" style="width: 10rem;">
            <h4 class="text-center">Report slow loading issues</h4>
        </div>
    </div>
<?php if(empty($_SESSION['id'])){ ?>
    <div class="row mb-5">
        <div class="col">
            Please <a href="https://report.localforyou.com/">Login</a> before use this service.
        </div>
    </div>
<?php
    exit();
    }else{
    ?>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-8">
                    <p>
                        <small>
                            The IT team is collecting statistics to fix Monday.com slow loading problem for everyone. <br>
                            <span class="text-danger">Whenever you notice that Monday.com is slower than 30 seconds,</span><br>
                            please click the report button once.
                        </small>
                    </p>
                </div>
                <div class="col">
                    <button type="button" class="btn sendReport" onclick="sendReport()">Send Report</button>
                    <span class="text-alert pl-3">
                        <b class="text-success">DONE !!</b>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <h6 class="card-subtitle mb-4 text-muted">My Report</h6>
                    <div>
                        <img src="../../../assets/img/crews/<?php echo $person['pic'];?>" class="avatar" alt="me">
                    </div>
                    <div><b>Reporter by: </b> <?php echo $person['name']; ?></div>
                    <div><b>Team: </b> <?php echo firstOnly($person['team']); ?></div>
                    <div>
                        <b>Total: </b> <span id="counterNum"><?php echo $stat['count']; ?></span>
                    </div>
                </div>
                <div class="col-6">
                    <h6 class="card-subtitle mb-4 text-muted">All Report</h6>
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
        </div>
    </div>
    <?php }//else ?>
</div>

<input type="hidden" id="loginID" value="<?php echo $id; ?>">

<script src="../assets/js/datatables-bs5.min.js"></script>
<script>
    let loginID = $('#loginID').val();
    const text_alert = $('.text-alert');
    const counterNum = $('#counterNum');

    setInterval(reloadTable, 1000);

    let reportTable = $('#reportData').DataTable({
        pagingType: 'full_numbers',
        ajax: {
            url: '../models/actionReport.php',
            dataSrc: 'data'
        },
        "pageLength": 50,
        order: [[2, "desc"], [1, "asc"]],
        lengthMenu: [
            [8, 25, 50, -1],
            ['Fit', 25, 50, 'All']
        ], columnDefs: [
            {targets: [2], className: 'dt-right'}
        ]
    });

    function reloadTable() {
        reportTable.ajax.reload();
    }

    function sendReport() {
        const sendReportAjax = $.ajax({
            url: '../models/actionReport.php',
            type: 'POST',
            data: {
                loginID: loginID,
                act: 'add'
            }
        });

        sendReportAjax.done(function (response) {
            console.log(response);
            reloadTable();
            reloadCount();
            text_alert.show().fadeOut(1000);
        });

        sendReportAjax.fail(function (xhr, status, error) {
            console.log("ajax call fail!!");
            console.log(status + ": " + error);
            return false;
        });
    }//sendReport


    function reloadCount() {
        const readAjax = $.ajax({
            url: '../models/getCount.php',
            type: 'POST',
            data: {}
        });

        readAjax.done(function (response) {
            counterNum.text(response);
        });

        readAjax.fail(function (xhr, status, error) {
            console.log("ajax reloadCount fail!!");
            console.log(status + ": " + error);
            return false;
        });
    }//sendReport



    $(() => {
        $('.text-alert').hide();
    });//ready

    const reloadPage = () => {
        location.reload();
    }
</script>

</body>
</html>
<?php
function firstOnly($string): string
{
    $temp = explode(" ", $string);
    return $temp[0];
}
?>