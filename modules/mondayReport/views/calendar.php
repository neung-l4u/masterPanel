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

$totalDays = count($data);
$activeDays = 0;
foreach ($data as $c) { if ($c > 0) $activeDays++; }
$avgPerDay = $activeDays > 0 ? round($total_reports / $activeDays, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-LGKDYHL23T');
        document.addEventListener('click', function(e) {
            var el = e.target.closest('[data-ga]');
            if (el) {
                gtag('event', el.getAttribute('data-ga'), {
                    event_category: el.getAttribute('data-ga-category') || 'button',
                    event_label: el.getAttribute('data-ga-label') || el.textContent.trim().substring(0, 50)
                });
            }
        });
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Summary — Calendar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/fullcalendar@5.11.3.min.js"></script>
    <link rel="stylesheet" href="../assets/css/fullcalendar@5.11.3.min.css">
    <style>
        .fc-daygrid-event .fc-event-title {
            text-align: right;
            display: block;
            width: 100%;
        }
        .fc .fc-toolbar-title { font-size: 1.1rem; font-weight: 700; color: #1e293b; }
        .fc .fc-button-primary { background-color: #3b82f6; border-color: #3b82f6; font-size: 0.8rem; }
        .fc .fc-button-primary:hover { background-color: #2563eb; border-color: #2563eb; }
        .fc .fc-daygrid-day-number { font-size: 0.8rem; }
        .scrollbar-thin::-webkit-scrollbar { width: 6px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">

<div class="max-w-7xl mx-auto px-4 py-8">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Report Summary
            </h1>
            <p class="text-sm text-gray-400 mt-0.5">Calendar view of all slow loading reports</p>
        </div>
        <a href="index.php" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-blue-600 transition font-medium bg-white px-4 py-2 rounded-lg shadow-sm hover:shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Total Reports</p>
            <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo number_format($total_reports); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Total Days</p>
            <p class="text-2xl font-bold text-gray-700 mt-1"><?php echo number_format($totalDays); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Active Days</p>
            <p class="text-2xl font-bold text-green-600 mt-1"><?php echo number_format($activeDays); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Avg / Active Day</p>
            <p class="text-2xl font-bold text-orange-500 mt-1"><?php echo $avgPerDay; ?></p>
        </div>
    </div>

    <!-- Main Grid: Table + Calendar -->
    <div class="grid grid-cols-12 gap-6">

        <!-- Left: Table -->
        <div class="col-span-4">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-slate-700 to-slate-600 px-5 py-3 flex items-center justify-between">
                    <h2 class="text-white font-semibold text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Daily Breakdown
                    </h2>
                    <span class="text-white/60 text-xs"><?php echo number_format($totalDays); ?> days</span>
                </div>

                <!-- Scrollable table body -->
                <div class="max-h-[500px] overflow-y-auto scrollbar-thin">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase w-10">#</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase">Reports</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        <?php $i = 1; foreach ($data as $date => $count): ?>
                            <tr class="hover:bg-blue-50/50 transition <?php echo $count > 0 ? '' : 'text-gray-300'; ?>">
                                <td class="px-4 py-2 text-gray-400 text-xs"><?php echo $i++; ?></td>
                                <td class="px-4 py-2 <?php echo $count > 0 ? 'text-gray-700 font-medium' : ''; ?>"><?php echo $date; ?></td>
                                <td class="px-4 py-2 text-right">
                                    <?php if ($count > 0): ?>
                                        <span class="bg-blue-500 text-white text-xs font-medium px-2 py-0.5 rounded-full"><?php echo $count; ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-300">0</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="bg-gray-800 px-5 py-3 flex items-center justify-between">
                    <span class="text-white font-semibold text-sm">Total</span>
                    <span class="text-blue-400 font-bold"><?php echo number_format($total_reports); ?> times</span>
                </div>
            </div>
        </div>

        <!-- Right: Calendar -->
        <div class="col-span-8">
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div id="calendar"></div>
            </div>
        </div>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 'auto',
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
