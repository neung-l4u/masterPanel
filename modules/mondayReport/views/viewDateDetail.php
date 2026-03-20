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
$isToday = ($getDate == $today);
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-LGKDYHL23T');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Monday Report — <?php echo formatDate($getDate); ?></title>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">

<div class="max-w-2xl mx-auto px-4 py-10">

    <!-- Close button -->
    <div class="flex justify-end mb-4">
        <a href="javascript:window.open('','_self').close();" class="inline-flex items-center gap-1 text-sm text-red-500 hover:text-red-700 transition font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Close
        </a>
    </div>

    <!-- Header Card -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Report Detail</p>
                <h1 class="text-2xl font-bold text-gray-800"><?php echo formatDate($getDate); ?></h1>
                <?php if($isToday){ ?>
                    <span class="inline-block mt-1 bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-0.5 rounded-full">Today</span>
                <?php } ?>
            </div>
            <div class="flex items-center gap-2">
                <a href="?date=<?php echo $previousDate; ?>" class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Prev
                </a>
                <a href="?date=<?php echo $today; ?>" class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-green-600 bg-green-50 rounded-lg hover:bg-green-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Today
                </a>
                <?php if(!$isToday){ ?>
                <a href="?date=<?php echo $nextDate; ?>" class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                    Next
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <?php } else { ?>
                <span class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                    Next
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

        <!-- Table Header Summary -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-4 flex items-center justify-between">
            <h2 class="text-white font-semibold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Reports
            </h2>
            <span class="bg-white/20 text-white text-sm font-bold px-3 py-1 rounded-full">
                <?php echo number_format(count($dateDetail)); ?> report<?php echo count($dateDetail) != 1 ? 's' : ''; ?>
            </span>
        </div>

        <?php if(count($dateDetail) == 0){ ?>
        <!-- Empty State -->
        <div class="px-6 py-16 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-gray-400 text-lg">No reports on this date</p>
        </div>
        <?php } else { ?>
        <!-- Report Rows -->
        <div class="divide-y divide-gray-100">
            <?php foreach ($dateDetail as $row){ ?>
            <div class="flex items-center justify-between px-6 py-3 hover:bg-blue-50/50 transition group">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 text-xs font-bold group-hover:bg-blue-100 group-hover:text-blue-600 transition">
                        <?php echo $i; ?>
                    </span>
                    <span class="text-gray-700 font-medium"><?php echo $row['nick']; ?></span>
                </div>
                <span class="bg-blue-500 text-white text-xs font-medium px-2.5 py-1 rounded-full">
                    <?php echo $row['time']; ?>
                </span>
            </div>
            <?php $i++; } ?>
        </div>

        <!-- Footer Total -->
        <div class="bg-gray-800 px-6 py-3 flex items-center justify-between">
            <span class="text-white font-semibold text-sm">Total</span>
            <span class="text-blue-400 font-bold"><?php echo number_format(count($dateDetail)); ?> times</span>
        </div>
        <?php } ?>

    </div>

</div>

</body>
</html>
