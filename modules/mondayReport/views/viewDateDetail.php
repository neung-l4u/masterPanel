<?php
session_start();
global $db;

include '../assets/db/db.php';
include '../assets/db/initDB.php';
require_once '../assets/PHP/shareFunction.php';

$getDate = $_GET['date'];
$today = date('Y-m-d');

// คำนวณวันก่อนหน้าและวันถัดไป
$previousDate = date('Y-m-d', strtotime($getDate . ' -1 day'));
$nextDate = date('Y-m-d', strtotime($getDate . ' +1 day'));

$dateDetail = $db->query('SELECT sta.sNickName AS "nick", TIME(mon.`whenTime`) AS "time" FROM `mondayslowreportlogs` mon LEFT JOIN `staffs` sta ON mon.staffID = sta.sID WHERE DATE(mon.`whenTime`) = ?  ORDER BY TIME(mon.`whenTime`);', $getDate)->fetchAll();
$i = 1;
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
        .dt-right {
            padding-right: 1.5rem !important;
        }
        .avatar {
            height: 8rem;
            border-radius: 10px;
        }
        .clickAble {
            cursor: pointer;
            height: 1rem;
        }
        .underPicBlock {
            display: inline-block;
            width: 110px;
        }
        .dayBlock {
            display: inline-block;
            width: 110px;
            border: 1px solid black;
        }
        /* ลดขนาดตัวอักษรของวันที่และปุ่ม */
        .header-buttons h4, .header-buttons a {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<div class="container pt-5">
    <!-- ปุ่มปิดหน้าต่าง -->
    <div class="row">
        <div class="col" style="text-align: right;">
            <a style="text-decoration: none;" class="text-danger" href="javascript:window.open('','_self').close();">
                <svg height="1.5rem" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                    <path fill="#dc3545" d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
                </svg> close
            </a>
        </div>
    </div>
    <!-- ส่วนหัวที่แสดงวันที่และปุ่มควบคุม (จัดชิดซ้าย) -->
    <div class="row">
        <div class="col mb-2 header-buttons">
            <h4 class="d-inline"><?php echo formatDate($getDate); ?></h4>
<a href="?date=<?php echo $previousDate; ?>" class="btn btn-outline-primary btn-sm mx-1">Previous</a>
<a href="?date=<?php echo $today; ?>" class="btn btn-outline-success btn-sm mx-1">Today</a>
<a href="?date=<?php echo $nextDate; ?>" class="<?php echo ($getDate == $today) ? 'btn btn-outline-dark bg-secondary disabled' : 'btn btn-outline-primary'; ?> btn-sm mx-1">Next</a>
</div>
</div>
<!-- ตารางข้อมูล -->
<div class="row">
    <div class="col">
        <table class="table table-hover table-bordered table-striped">
            <thead class="table-dark thead-dark">
            <tr>
                <th style="width: 15px;">#</th>
                <th>Reporter</th>
                <th style="width: 100px;">When</th>
            </tr>
            </thead>
            <tbody>
            <?php if(count($dateDetail) == 0){ ?>
                <tr>
                    <td colspan="3" class="text-center">No data</td>
                </tr>
            <?php } else { ?>
                <?php foreach ($dateDetail as $row){ ?>
                    <tr>
                        <th><?php echo $i; ?></th>
                        <td><?php echo $row['nick']; ?></td>
                        <td style="text-align: right;">
                            <span class="badge bg-primary rounded-pill"><?php echo $row['time']; ?></span>
                        </td>
                    </tr>
                    <?php $i++; ?>
                <?php } ?>
            <?php } ?>
            </tbody>
            <tfoot class="bg-dark text-white">
            <tr>
                <th colspan="2">Total</th>
                <th style="text-align: right; padding-right: 1rem;" class="text-primary">
                    <?php echo number_format(count($dateDetail)); ?> times.
                </th>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
</div>
</body>
</html>
