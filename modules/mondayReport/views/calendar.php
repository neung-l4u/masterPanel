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

// ดึงข้อมูลทั้งหมด และเติมวันที่ที่ไม่มีข้อมูลให้เป็น 0
$startDate = $db->query("SELECT MIN(DATE(whenTime)) as min_date FROM mondayslowreportlogs")->fetchArray()['min_date'];
$endDate = date('Y-m-d');

$period = new DatePeriod(new DateTime($startDate), new DateInterval('P1D'), new DateTime($endDate . ' +1 day'));
$data = [];
$total_reports = 0;

foreach ($period as $date) {
    $formatted_date = $date->format("d/m/Y");
    $data[$formatted_date] = 0;
}

$dateDetail = $db->query('SELECT DATE(whenTime) as report_date, COUNT(*) as report_count FROM mondayslowreportlogs GROUP BY report_date ORDER BY report_date')->fetchAll();

foreach ($dateDetail as $row) {
    $formatted_date = date("d/m/Y", strtotime($row['report_date']));
    $data[$formatted_date] = $row['report_count'];
    $total_reports += $row['report_count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Summary</title>
    <link href="../assets/css/bootstrap5.3.3.min.css" rel="stylesheet">
    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/fullcalendar@5.11.3.min.js"></script>
    <link rel="stylesheet" href="../assets/css/fullcalendar@5.11.3.min.css">
    <script src="../assets/js/bootstrap.bundle.5.3.3.min.js"></script>
    <style>
        .scrollable-table {
            max-height: 500px; /* กำหนดความสูงให้เท่ากับปฏิทิน */
            overflow-y: auto;
        }
        .fc-daygrid-event .fc-event-title {
            text-align: right; /* จัดชิดขวาข้อความจำนวนนับในปฏิทิน */
            display: block;
            width: 100%;
        }
    </style>
</head>
<body class="container pt-5 mt-4">

<div class="row">
    <div class="col-4">
        <h3 class="mb-4">Report Summary</h3>
        <div class="scrollable-table">
            <table class="table table-hover table-bordered table-striped">
                <thead class="table-dark thead-dark">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th class="text-end">Reports</th>
                </tr>
                </thead>
                <tbody>
                <?php $i = 1; foreach ($data as $date => $count): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo $date; ?></td>
                        <td class="text-end"><?php echo $count; ?> Times</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="2">Total</th>
                    <th class="text-end"><?php echo $total_reports; ?> Times</th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="col-8">
        <div id='calendar'></div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: [
                <?php foreach ($data as $date => $count): ?>
                {
                    title: '<?php echo $count; ?> Times',
                    start: '<?php echo date("Y-m-d", strtotime(str_replace("/", "-", $date))); ?>'
                },
                <?php endforeach; ?>
            ]
        });
        calendar.render();
    });
</script>
</body>
</html>