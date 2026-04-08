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

$sumAll = $db->query('SELECT COUNT(DISTINCT mo.id) AS count FROM mondayslowreportlogs mo;')->fetchArray();

$sumDate = $db->query('SELECT DATE(mo.whenTime) AS day, COUNT(mo.id) AS count FROM mondayslowreportlogs mo GROUP BY DATE(mo.whenTime) ORDER BY day DESC LIMIT 10;')->fetchAll();
$topDate = $db->query('SELECT DATE(mo.whenTime) AS day, COUNT(mo.id) AS count FROM mondayslowreportlogs mo GROUP BY DATE(mo.whenTime) ORDER BY count DESC LIMIT 1;')->fetchArray();
$lowDate = $db->query('SELECT DATE(mo.whenTime) AS day, COUNT(mo.id) AS count FROM mondayslowreportlogs mo GROUP BY DATE(mo.whenTime) ORDER BY count ASC LIMIT 1;')->fetchArray();
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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="../assets/css/bootstrap5.3.3.min.css">
    <script src="../assets/js/bootstrap.bundle.5.3.3.min.js"></script>
    <title>Monday Report</title>
    <style>
        /* 3D Button base */
        button { position: relative; display: inline-block; cursor: pointer; outline: none; border: 0; text-decoration: none; font-family: inherit; }
        /* Send Report btn */
        button.sendReport { font-weight: 600; color: #382b22; text-transform: uppercase; padding: 1em 2em; background: #fff0f0; border: 2px solid #b18597; border-radius: 0.75em; transform-style: preserve-3d; transition: transform 150ms cubic-bezier(0,0,.58,1), background 150ms cubic-bezier(0,0,.58,1); }
        button.sendReport::before { position: absolute; content: ''; width: 100%; height: 100%; top: 0; left: 0; background: #f9c4d2; border-radius: inherit; box-shadow: 0 0 0 2px #b18597, 0 0.625em 0 0 #ffe3e2; transform: translate3d(0, 0.75em, -1em); transition: transform 150ms cubic-bezier(0,0,.58,1), box-shadow 150ms cubic-bezier(0,0,.58,1); }
        button.sendReport:hover { background: #ffe9e9; transform: translate(0, 0.25em); }
        button.sendReport:hover::before { box-shadow: 0 0 0 2px #b18597, 0 0.5em 0 0 #ffe3e2; transform: translate3d(0, 0.5em, -1em); }
        button.sendReport:active { background: #ffe9e9; transform: translate(0, 0.75em); }
        button.sendReport:active::before { box-shadow: 0 0 0 2px #b18597, 0 0 #ffe3e2; transform: translate3d(0, 0, -1em); }
        /* Advanced Report btn */
        button.advancedReport { font-weight: 600; color: #1b2e4b; text-transform: uppercase; padding: 1em 2em; background: #e8f0fe; border: 2px solid #5b86c5; border-radius: 0.75em; transform-style: preserve-3d; transition: transform 150ms cubic-bezier(0,0,.58,1), background 150ms cubic-bezier(0,0,.58,1); }
        button.advancedReport::before { position: absolute; content: ''; width: 100%; height: 100%; top: 0; left: 0; background: #b6d4fe; border-radius: inherit; box-shadow: 0 0 0 2px #5b86c5, 0 0.625em 0 0 #d6e4f7; transform: translate3d(0, 0.75em, -1em); transition: transform 150ms cubic-bezier(0,0,.58,1), box-shadow 150ms cubic-bezier(0,0,.58,1); }
        button.advancedReport:hover { background: #dce8fa; transform: translate(0, 0.25em); }
        button.advancedReport:hover::before { box-shadow: 0 0 0 2px #5b86c5, 0 0.5em 0 0 #d6e4f7; transform: translate3d(0, 0.5em, -1em); }
        button.advancedReport:active { background: #dce8fa; transform: translate(0, 0.75em); }
        button.advancedReport:active::before { box-shadow: 0 0 0 2px #5b86c5, 0 0 #d6e4f7; transform: translate3d(0, 0, -1em); }
        
        /* Custom Scrollbar for Modal */
        .modal-body::-webkit-scrollbar { width: 8px; }
        .modal-body::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
        .modal-body::-webkit-scrollbar-thumb { background: linear-gradient(180deg, #3b82f6, #2563eb); border-radius: 10px; }
        .modal-body::-webkit-scrollbar-thumb:hover { background: linear-gradient(180deg, #2563eb, #1d4ed8); }
        .modal-body { scrollbar-width: thin; scrollbar-color: #3b82f6 #f1f5f9; max-height: 70vh; overflow-y: auto; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">

<div class="max-w-7xl mx-auto px-4 py-10">

    <!-- Header -->
    <div class="text-center mb-8">
        <img src="../assets/images/mondayLogo.png" alt="monday" class="w-24 mx-auto mb-3">
        <h1 class="text-2xl font-bold text-gray-800">Report Slow Loading Issues</h1>
        <p class="text-gray-500 text-sm mt-1">Help us track and fix Monday.com performance</p>
    </div>

    <?php if(empty($_SESSION['id'])){ ?>
    <!-- Not logged in -->
    <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        <p class="text-gray-600 mb-4">Please log in before using this service.</p>
        <a href="https://report.localforyou.com/" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium">Login</a>
    </div>
    <?php exit(); } else { ?>

    <!-- Action Bar -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="text-sm text-gray-600 leading-relaxed">
                The IT team is collecting statistics to fix Monday.com slow loading problem for everyone.<br>
                <span class="text-red-500 font-medium">Whenever you notice that Monday.com is slower than 1 minute,</span><br>
                please click the report button once.
            </div>
            <div class="flex items-center gap-4 flex-shrink-0">
                <button type="button" class="sendReport" onclick="sendReport()" data-ga="click_send_report" data-ga-label="Send Report">Send Report</button>
                <span class="text-alert hidden">
                    <span class="text-green-600 font-bold text-sm">DONE !!</span>
                </span>
                <button type="button" class="advancedReport" data-bs-toggle="modal" data-bs-target="#advancedReportModal" data-ga="click_open_advanced_report" data-ga-label="Advanced Report">Advanced Report</button>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Left: My Report Statistics -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-lg font-semibold text-blue-600 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                My Report Statistics
            </h2>

            <!-- Profile Card -->
            <div class="flex items-start gap-4 mb-6">
                <img src="https://report.localforyou.com/dist/img/crews/<?php echo $person['pic'];?>" 
                     class="w-20 h-20 rounded-xl object-cover shadow-sm" alt="me">
                <div class="space-y-1">
                    <p class="text-gray-800"><span class="font-semibold">Reporter:</span> <?php echo showName($person['nick'],$person['name']); ?></p>
                    <p class="text-gray-800"><span class="font-semibold">Team:</span> <?php echo firstOnly($person['team']); ?></p>
                    <p class="text-gray-800"><span class="font-semibold">Total reported:</span> <span id="counterNum" class="text-blue-600 font-bold"><?php echo number_format($stat['count']); ?></span> times</p>
                    <a href="calendar.php" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-sm mt-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        View Calendar
                    </a>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-3 gap-3 mb-5">
                <div class="bg-blue-50 rounded-xl p-3 text-center">
                    <p class="text-xs text-gray-500">Start counting</p>
                    <p class="text-sm font-semibold text-gray-700"><?php echo formatDate($startCountDate); ?></p>
                    <p class="text-xs text-gray-400"><?php echo timeAgoInDays($startCountDate); ?></p>
                </div>
                <div class="bg-green-50 rounded-xl p-3 text-center">
                    <p class="text-xs text-gray-500">Most reported</p>
                    <p class="text-sm font-semibold text-gray-700"><?php echo formatDate($topDate['day']); ?></p>
                    <p class="text-xs text-green-600 font-medium"><?php echo number_format($topDate['count']); ?> times</p>
                </div>
                <div class="bg-orange-50 rounded-xl p-3 text-center">
                    <p class="text-xs text-gray-500">Least reported</p>
                    <p class="text-sm font-semibold text-gray-700"><?php echo formatDate($lowDate['day']); ?></p>
                    <p class="text-xs text-orange-600 font-medium"><?php echo number_format($lowDate['count']); ?> times</p>
                </div>
            </div>

            <!-- Summary + Last 10 days -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-gray-700">Summary All:</span>
                    <span class="bg-blue-600 text-white text-xs font-bold px-2.5 py-1 rounded-full"><?php echo number_format($sumAll['count']); ?></span>
                </div>
                <button onclick="reloadPage();" class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1 font-medium" data-ga="click_reload_report" data-ga-label="Reload">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Reload
                </button>
            </div>

            <div id="divStat">
                <p class="text-xs text-gray-400 mb-2">Last 10 days</p>
                <div class="space-y-1.5">
                    <?php $i=1; foreach ($sumDate as $row){ ?>
                    <div class="flex items-center justify-between bg-gray-50 hover:bg-blue-50 rounded-lg px-3 py-2 transition group">
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-400 w-5 text-right"><?php echo $i; ?>.</span>
                            <a href="viewDateDetail.php?date=<?php echo $row['day']; ?>" target="_blank" class="text-blue-500 hover:text-blue-700 opacity-0 group-hover:opacity-100 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <span class="text-gray-700"><?php echo formatDate($row['day']); ?></span>
                        </div>
                        <span class="bg-blue-500 text-white text-xs font-medium px-2.5 py-0.5 rounded-full"><?php echo number_format($row['count']); ?></span>
                    </div>
                    <?php $i++; } ?>
                </div>
            </div>
        </div>

        <!-- Right: Other Users -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-lg font-semibold text-blue-600 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Other User Report Statistics
            </h2>
            <div class="overflow-x-auto">
                <table id="reportData" class="table table-striped table-hover w-full">
                    <thead class="table-dark thead-dark">
                    <tr>
                        <th style="width:5%;">Top</th>
                        <th>Name</th>
                        <th style="text-align:right; padding-right:1rem;">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <?php }//else ?>
</div>

<!-- Advanced Report Modal -->
<div class="modal fade" id="advancedReportModal" tabindex="-1" aria-labelledby="advancedReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-xl" style="border-radius: 1rem;">
            <div class="modal-header bg-blue-600 text-white" style="border-radius: 1rem 1rem 0 0;">
                <h5 class="modal-title flex items-center gap-2" id="advancedReportModalLabel">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Advanced Report
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="advancedReportForm" enctype="multipart/form-data">
                <div class="modal-body p-6 space-y-5">

                    <div>
                        <label for="advBoard" class="block text-sm font-semibold text-gray-700 mb-1">Board <span class="text-red-500">*</span></label>
                        <input type="text" class="form-control" id="advBoard" name="board" placeholder="e.g. Projects | TH, CRM" required>
                    </div>

                    <div>
                        <label for="advSubject" class="block text-sm font-semibold text-gray-700 mb-1">Subject <span class="text-red-500">*</span></label>
                        <input type="text" class="form-control" id="advSubject" name="subject" placeholder="e.g. Board loading very slow when opening items" required>
                    </div>

                    <div>
                        <label for="advDetail" class="block text-sm font-semibold text-gray-700 mb-1">Detail</label>
                        <textarea class="form-control" id="advDetail" name="detail" rows="4" placeholder="Describe the issue in detail..."></textarea>
                    </div>

                    <div>
                        <label for="advAttachment" class="block text-sm font-semibold text-gray-700 mb-1">Attach file <span class="text-gray-400 font-normal">(picture only, optional)</span></label>
                        <input type="file" class="form-control" id="advAttachment" name="attachment" accept="image/*">
                        <p class="text-xs text-gray-400 mt-1">Supported: JPG, PNG, GIF, WEBP (max 5MB)</p>
                    </div>

                    <hr class="border-gray-200">

                    <div>
                        <label for="advScreenInternet" class="block text-sm font-semibold text-gray-700 mb-1">
                            Screenshot Internet Speed Test <span class="text-red-500">*</span>
                        </label>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-2.5 mb-2 text-xs text-blue-700 flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Please run a speed test at <a href="https://fiber.google.com/speedtest/" target="_blank" class="font-bold underline">fiber.google.com/speedtest</a> and take a screenshot of the result.</span>
                        </div>
                        <input type="file" class="form-control" id="advScreenInternet" name="screenshot_internet" accept="image/*" required>
                    </div>

                    <div>
                        <label for="advScreenComputer" class="block text-sm font-semibold text-gray-700 mb-1">
                            Screenshot My Computer Info <span class="text-red-500">*</span>
                        </label>
                        <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-2.5 mb-2 text-xs text-amber-700">
                            <b>macOS:</b> Click <b></b> → <b>About This Mac</b> → <b>More Info</b> → Screenshot<br>
                            <b>Windows:</b> Right-click <b>This PC</b> → <b>Properties</b> → Screenshot
                        </div>
                        <input type="file" class="form-control" id="advScreenComputer" name="screenshot_computer" accept="image/*" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitAdvanced" data-ga="click_submit_advanced_report" data-ga-label="Submit Advanced Report">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="advSpinner" role="status"></span>
                        Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Session Expired Modal -->
<div id="loginModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-xl" style="border-radius: 1rem;">
            <div class="modal-header bg-red-500 text-white" style="border-radius: 1rem 1rem 0 0;">
                <h5 class="modal-title" id="loginModalLabel">Session Expired</h5>
            </div>
            <div class="modal-body flex items-center justify-center" style="height: 200px;">
                <p class="text-lg text-center text-gray-600">ไม่สามารถดำเนินการได้เนื่องจาก Session หมดอายุ <br> กรุณา Log in ใหม่</p>
            </div>
            <div class="modal-footer">
                <a href="https://report.localforyou.com/" target="_blank" class="w-full">
                    <button class="btn btn-primary btn-lg w-100">Go to Login</button>
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

    setInterval(reloadTable, 5000);
    setInterval(reloadStat, 6000);

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
            divStat.html(response);
        });

        readAjax.fail(function (xhr, status, error) {
            console.log("ajax reloadStat fail!!");
            console.log(status + ": " + error);
            return false;
        });
    }//reloadStat

    // Advanced Report form submit
    $('#advancedReportForm').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        const btn = $('#btnSubmitAdvanced');
        const spinner = $('#advSpinner');

        const maxSize = 5 * 1024 * 1024;
        const fileInputs = ['advAttachment', 'advScreenInternet', 'advScreenComputer'];
        for (let i = 0; i < fileInputs.length; i++) {
            const input = document.getElementById(fileInputs[i]);
            if (input.files.length > 0 && input.files[0].size > maxSize) {
                alert('File "' + input.files[0].name + '" exceeds 5MB limit.');
                return;
            }
        }

        btn.prop('disabled', true);
        spinner.removeClass('d-none');

        const formData = new FormData(form);

        $.ajax({
            url: '../models/submitAdvancedReport.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false
        }).done(function(res) {
            if (res.status === 'success') {
                alert('Advanced report submitted successfully!');
                $('#advancedReportModal').modal('hide');
                form.reset();
            } else if (res.status === 'sessionExp') {
                $('#loginModal').modal('show');
            } else {
                alert('Error: ' + (res.message || 'Unknown error'));
            }
        }).fail(function(xhr, status, error) {
            console.error('Advanced report submit failed:', status, error);
            alert('Failed to submit report. Please try again.');
        }).always(function() {
            btn.prop('disabled', false);
            spinner.addClass('d-none');
        });
    });

    $(() => {
        $('.text-alert').hide();

        setInterval(function() {
            let reqHeartbeat = $.ajax({
                url: "../assets/api/heartbeat.php",
                method: "POST",
                async: false,
                cache: false,
                dataType: "json",
            });

            reqHeartbeat.done(function (data) {
                if (data.status === 'expired') {
                    alert('Your session has expired. Please log in again.');
                    window.location = '../../../chkLogin.php?act=expired';
                }
            });

            reqHeartbeat.fail(function (xhr, status, error) {
                console.log("check heart beat fail!!");
                console.log(status + ": " + error);
            });
        }, 60000);
    });//ready
</script>

</body>
</html>
