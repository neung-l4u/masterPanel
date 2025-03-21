<?php
session_start();
global $db;

include '../assets/db/db.php';
include "../assets/db/initDB.php";
require_once '../assets/PHP/shareFunction.php';

$param['act'] = 'read';
$startCountDate = '2025-02-19';

$return['result'] = '';
$return['msg'] = '';
$return['data'] = '';
$return['act'] = $param['act'];

$person = $db->query('SELECT st.sNickName AS "nick", st.sName AS "name", te.name AS "team", st.sPic AS "pic"
    FROM staffs st 
    LEFT JOIN Team te on st.teamID = te.id 
    WHERE st.sID = ? 
    ',$_SESSION['id'])->fetchArray();

$stat = $db->query('SELECT COUNT(mo.id) AS "count" FROM mondayslowreportlogs mo WHERE mo.staffID = ? GROUP BY mo.staffID;',$_SESSION['id'])->fetchArray();
$stat['count'] = !empty($stat['count']) ? number_format($stat['count']) : 0;

//$sumAll = $db->query('SELECT COUNT(mo.id) AS "count" FROM mondayslowreportlogs mo')->fetchArray();
$sumAll = $db->query('SELECT COUNT(DISTINCT mo.id) AS count FROM mondayslowreportlogs mo;')->fetchArray();

//$sumDate = $db->query('SELECT DATE(whenTime) AS "day",COUNT(mo.id) AS "count" FROM mondayslowreportlogs mo GROUP BY DATE(whenTime) ORDER BY DATE(whenTime) DESC LIMIT 0,10;')->fetchAll();
$sumDate = $db->query('SELECT DATE(mo.whenTime) AS day, COUNT(mo.id) AS count FROM mondayslowreportlogs mo GROUP BY DATE(mo.whenTime) ORDER BY day DESC LIMIT 10;')->fetchAll();
$topDate = $db->query('SELECT DATE(mo.whenTime) AS day, COUNT(mo.id) AS count FROM mondayslowreportlogs mo GROUP BY DATE(mo.whenTime) ORDER BY count DESC LIMIT 1;')->fetchArray();
$lowDate = $db->query('SELECT DATE(mo.whenTime) AS day, COUNT(mo.id) AS count FROM mondayslowreportlogs mo GROUP BY DATE(mo.whenTime) ORDER BY count ASC LIMIT 1;')->fetchArray();
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
            height: 8rem;
            border-radius: 10px;
        }
        .clickAble{
            cursor: pointer;
            height: 1rem;
        }
        .underPicBlock{
            display: inline-block;
            width: 110px;
        }
        .dayBlock{
            display: inline-block;
            width: 110px;
            border: 1px solid black;
        }
    </style>
</head>
<body>
<div class="container pt-5">
    <div class="row mb-3">
        <div class="col text-center">
            <img src="../assets/images/mondayLogo.png" alt="monday" style="width: 8rem;">
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
                                <span class="text-danger">Whenever you notice that Monday.com is slower than 1 minute,</span><br>
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
                    <div class="col-6 p-3">
                        <h6 class="card-subtitle mb-4 text-primary">My Report Statistics.</h6>
                        <div class="d-flex gap-2">
                            <div style="width: 10rem">
                                <img src="https://report.localforyou.com/dist/img/crews/<?php echo $person['pic'];?>" class="avatar" alt="me">
                            </div>
                            <div style="width: 100%" class="d-flex flex-column pl-3">
                                <span><b>Reporter: </b> <?php echo showName($person['nick'],$person['name']); ?></span>
                                <span><b>Team: </b> <?php echo firstOnly($person['team']); ?></span>
                                <span><b>The total I reported: </b> <span id="counterNum"><?php echo number_format($stat['count']); ?></span> times.</span>
                                <span><a href="calendar.php" target="_blank">
                                        <svg  height="1.3rem"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="#0d6efd" d="M128 0c17.7 0 32 14.3 32 32l0 32 128 0 0-32c0-17.7 14.3-32 32-32s32 14.3 32 32l0 32 48 0c26.5 0 48 21.5 48 48l0 48L0 160l0-48C0 85.5 21.5 64 48 64l48 0 0-32c0-17.7 14.3-32 32-32zM0 192l448 0 0 272c0 26.5-21.5 48-48 48L48 512c-26.5 0-48-21.5-48-48L0 192zm64 80l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16zm128 0l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0zM64 400l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0zm112 16l0 32c0 8.8 7.2 16 16 16l32 0c8.8 0 16-7.2 16-16l0-32c0-8.8-7.2-16-16-16l-32 0c-8.8 0-16 7.2-16 16z"/></svg></a></span>
                            </div>
                        </div>


                        <div class="mt-3">
                            <div class="d-flex justify-content-between">
                                <small>
                                    <b class="underPicBlock">Start counting: </b> <?php echo formatDate($startCountDate).' ('.timeAgoInDays($startCountDate).')'; ?>
                                </small>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small>
                                    <b class="underPicBlock">Most reported: </b> <?php echo formatDate($topDate['day']).' = '.number_format($topDate['count']); ?> times.
                                </small>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small>
                                    <b class="underPicBlock">Least reported: </b> <?php echo formatDate($lowDate['day']).' = '.number_format($lowDate['count']); ?> times.
                                </small>
                                <div class="d-flex justify-content-end align-items-baseline gap-1 mb-3 pr-3">
                                    <small class="clickAble" onclick="reloadPage();"><b>reload</b></small>
                                    <img class="clickAble" onclick="reloadPage();" src="../assets/images/reload.png" alt="reload">
                                </div>
                            </div>
                            <div class="border rounded" id="divStat">
                                <table class="table table-hover table-borderless">
                                    <tr>
                                        <th style="width: 120px;">Summary All</th>
                                        <td style="text-align: right; padding-right: 1rem;">
                                            <?php echo number_format($sumAll['count']); ?> times.
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="2" style="text-align: left; padding-left: 1rem;">
                                            <b>Date: </b><small class="text-muted">(Last 10 days)</small>
                                            <ul class="list-group">
                                                <?php $i=1;
                                                foreach ($sumDate as $row){ ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <?php echo $i;?>.
                                                            <a href="viewDateDetail.php?date=<?php echo $row['day']; ?>" target="_blank"><svg height="1rem" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="#0d6efd" d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/></svg></a>
                                                            <?php echo formatDate($row['day']); ?>
                                                        </div>
                                                        <span class="badge bg-primary rounded-pill"><?php echo number_format($row['count']); ?></span>
                                                    </li>
                                                    <?php
                                                    $i++;
                                                }//foreach
                                                ?>
                                            </ul>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 border rounded p-3">
                        <h6 class="card-subtitle mb-4 text-primary">Other user report statistics.</h6>
                        <table id="reportData" class="table table-striped table-hover">
                            <thead class="table-dark thead-dark">
                            <tr>
                                <th style="width:5%;">Top</th>
                                <th>Name</th>
                                <th class="dt-right">Total</th>
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

<div id="loginModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="loginModalLabel">Session Expired</h5>
            </div>
            <div class="modal-body text-center" style="display: flex; align-items: center; justify-content: center; height: 200px;">
                <p class="lead">ไม่สามารถดำเนินการได้เนื่องจาก Session หมดอายุ <br> กรุณา Log in ใหม่</p>
            </div>
            <div class="modal-footer">
                <a href="https://report.localforyou.com/" target="_blank">
                    <button id="redirectButton" class="btn btn-primary btn-lg w-100">Go to Login</button>
                </a>
            </div>
        </div>
    </div>
</div>


<script src="../assets/js/datatables-bs5.min.js"></script>
<script>
    const text_alert = $('.text-alert');
    const counterNum = $('#counterNum');
    const divStat = $('#divStat');

    setInterval(reloadTable, 1000);
    setInterval(reloadStat, 5000);

    let reportTable = $('#reportData').DataTable({
        pagingType: 'full_numbers',
        ajax: {
            url: '../models/actionReport.php',
            dataSrc: 'data'
        },
        "pageLength": 8,
        order: [[2, "desc"], [1, "asc"]],
        lengthMenu: [
            [8, 25, 50, -1],
            ['Fit', 25, 50, 'All']
        ], columnDefs: [
            {targets: [2], className: 'dt-right'}
        ]
    });

    function reloadTable() {
        reportTable.ajax.reload( null, false );
    }

    function reloadPage(){
        location.reload();
    }

    function sendReport() {
        const reportAjax = $.ajax({
            url: '../models/actionReport.php',
            type: 'POST',
            dataType: 'json',
            data: {
                act: 'add'
            }
        });

        reportAjax.done(function (res) {
            if (res.status === 'sessionExp') {
                $('#loginModal').modal('show');
            } else {
                reloadTable();
                reloadCount();
                text_alert.show().fadeOut(1000);
            }
        });

        reportAjax.fail(function (xhr, status, error) {
            console.error("AJAX request failed:", status, error);
            console.error("Response:", xhr.responseText);
        });
    }

    function reloadCount() {
        const readAjax = $.ajax({
            url: '../models/getCount.php',
            dataType: "html",
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
    }//reloadCount

    function reloadStat() {
        const readAjax = $.ajax({
            url: '../models/getStat.php',
            dataType: "html",
            type: 'POST',
            data: {}
        });

        readAjax.done(function (response) {
            //counterNum.text(response);
            divStat.html(response);
        });

        readAjax.fail(function (xhr, status, error) {
            console.log("ajax reloadStat fail!!");
            console.log(status + ": " + error);
            return false;
        });
    }//reloadStat


    $(() => {
        $('.text-alert').hide();
    });//ready
</script>

</body>
</html>