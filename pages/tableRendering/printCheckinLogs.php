<?php
global $db;
session_start();
error_reporting(E_ALL);

include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'csv';
$params["department"] = !empty($_GET['department']) ? $_GET['department'] : '';
$params["employee"] = !empty($_GET['employee']) ? $_GET['employee'] : '';
$params["status"] = !empty($_GET['status']) ? $_GET['status'] : '';
$params["dateFrom"] = !empty($_GET['dateFrom']) ? $_GET['dateFrom'] : '';
$params["dateTo"] = !empty($_GET['dateTo']) ? $_GET['dateTo'] : '';

$where = "";
if (!empty($params["department"])) {
    $where .= " AND C.`department` = '" . $params["department"] . "'";
}
if (!empty($params["employee"])) {
    $where .= " AND C.`employee` = '" . $params["employee"] . "'";
}
if (!empty($params["status"])) {
    $where .= " AND C.`workShiftTimeLogging` = '" . $params["status"] . "'";
}
if (!empty($params["dateFrom"])) {
    $where .= " AND DATE(C.`dayCheckIn`) >= '" . $params["dateFrom"] . "'";
}
if (!empty($params["dateTo"])) {
    $where .= " AND DATE(C.`dayCheckIn`) <= '" . $params["dateTo"] . "'";
}

$result = [];
try {
    $sql = "SELECT C.`id`, C.`employee`, C.`department`, C.`workShiftTimeLogging`,
                   C.`checkIn`, C.`dayCheckIn`, C.`noteCheckIn`,
                   C.`checkOut`, C.`dayCheckOut`, C.`noteCheckOut`, C.`total`
            FROM `checkin` C
            WHERE 1=1" . $where . " ORDER BY C.`dayCheckIn` DESC, C.`id` DESC";
    $result = $db->query($sql)->fetchAll();
} catch (Exception $e) {
    $result = [];
}

$rowsPerPage = 25;
$totalRows = count($result);
$totalPages = max(1, ceil($totalRows / $rowsPerPage));

// Filter info
$filters = [];
if (!empty($params["department"])) $filters[] = "Department: " . $params["department"];
if (!empty($params["employee"])) $filters[] = "Employee: " . $params["employee"];
if (!empty($params["status"])) $filters[] = "Status: " . $params["status"];
if (!empty($params["dateFrom"])) $filters[] = "From: " . $params["dateFrom"];
if (!empty($params["dateTo"])) $filters[] = "To: " . $params["dateTo"];
$filterText = !empty($filters) ? implode(' | ', $filters) : 'All Data';
$formatLabel = strtoupper($format);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Check-in Logs (<?= $formatLabel ?>)</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 11px; color: #333; }

        .page {
            width: 100%;
            min-height: 100vh;
            padding: 15mm 10mm 20mm 10mm;
            page-break-after: always;
            position: relative;
        }
        .page:last-child { page-break-after: auto; }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #343a40;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header-title { font-size: 16px; font-weight: bold; }
        .header-sub { font-size: 10px; color: #666; }
        .header-format {
            display: inline-block;
            background: <?= $format === 'xlsx' ? '#ffc107' : '#28a745' ?>;
            color: <?= $format === 'xlsx' ? '#333' : '#fff' ?>;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .filter-info {
            font-size: 10px;
            color: #666;
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        th {
            background-color: #343a40;
            color: #fff;
            padding: 5px 4px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #454d55;
        }
        td {
            padding: 4px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        tr:nth-child(even) { background-color: #f8f9fa; }
        tr:hover { background-color: #e9ecef; }

        .footer {
            position: absolute;
            bottom: 10mm;
            left: 10mm;
            right: 10mm;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }

        .text-center { text-align: center; }
        .text-muted { color: #999; }
        .no-data { text-align: center; padding: 40px; color: #999; font-size: 14px; }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .page { padding: 10mm 8mm 18mm 8mm; }
            .no-print { display: none !important; }
        }

        .toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #343a40;
            color: #fff;
            padding: 8px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 9999;
            font-size: 13px;
        }
        .toolbar button {
            background: #fff;
            color: #333;
            border: none;
            padding: 6px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 12px;
            margin-left: 8px;
        }
        .toolbar button:hover { background: #e9ecef; }
        .toolbar button.btn-print { background: #007bff; color: #fff; }
        .toolbar button.btn-print:hover { background: #0056b3; }
        .toolbar button.btn-download { background: #28a745; color: #fff; }
        .toolbar button.btn-download:hover { background: #1e7e34; }

        @media print { .toolbar { display: none !important; } }
        body { padding-top: 45px; }
    </style>
</head>
<body>

<!-- Toolbar (hidden when printing) -->
<div class="toolbar no-print">
    <div>
        <strong>Check-in Logs</strong> &mdash;
        <span class="header-format"><?= $formatLabel ?></span>
        &nbsp; <?= $totalRows ?> records, <?= $totalPages ?> page(s)
    </div>
    <div>
        <button class="btn-download" onclick="window.location.href='exportCheckinLogs.php?<?= http_build_query($_GET) ?>'">
            &#x2B73; Download <?= $formatLabel ?>
        </button>
        <button class="btn-print" onclick="window.print()">&#x1F5A8; Print</button>
        <button onclick="window.close()">&#x2715; Close</button>
    </div>
</div>

<?php if ($totalRows === 0): ?>
<div class="page">
    <div class="header">
        <div>
            <div class="header-title">Check-in Logs Report</div>
            <div class="header-sub">Printed: <?= date('d/m/Y H:i') ?> | Format: <span class="header-format"><?= $formatLabel ?></span></div>
        </div>
    </div>
    <div class="no-data">No data found</div>
    <div class="footer">
        <div>Check-in Logs Report</div>
        <div>Page 1 / 1</div>
    </div>
</div>
<?php else: ?>

<?php for ($page = 0; $page < $totalPages; $page++): ?>
<?php
    $startIdx = $page * $rowsPerPage;
    $endIdx = min($startIdx + $rowsPerPage, $totalRows);
    $currentPage = $page + 1;
?>
<div class="page">
    <div class="header">
        <div>
            <div class="header-title">Check-in Logs Report</div>
            <div class="header-sub">
                Printed: <?= date('d/m/Y H:i') ?> |
                Format: <span class="header-format"><?= $formatLabel ?></span> |
                Filter: <?= htmlspecialchars($filterText) ?>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:12px;font-weight:bold;">Page <?= $currentPage ?> / <?= $totalPages ?></div>
            <div class="header-sub">Total: <?= $totalRows ?> records</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th style="width:16%">Employee</th>
                <th style="width:12%">Department</th>
                <th style="width:8%">Type</th>
                <th style="width:14%">Check In</th>
                <th style="width:14%">Check Out</th>
                <th style="width:8%">Total</th>
                <th style="width:24%">Notes</th>
            </tr>
        </thead>
        <tbody>
<?php for ($i = $startIdx; $i < $endIdx; $i++):
    $row = $result[$i];
    $employee = $row["employee"] ?: '-';
    $department = $row["department"] ?: '-';
    $type = $row["workShiftTimeLogging"] ?: '-';
    $checkInTime = $row["checkIn"] ?: '-';
    $checkInDate = $row["dayCheckIn"] ? date("d/m/Y", strtotime($row["dayCheckIn"])) : '';
    $checkIn = $checkInTime . ($checkInDate ? ' ' . $checkInDate : '');
    $checkOutTime = $row["checkOut"] ?: '-';
    $checkOutDate = $row["dayCheckOut"] ? date("d/m/Y", strtotime($row["dayCheckOut"])) : '';
    $checkOut = $checkOutTime . ($checkOutDate && $checkOutTime !== '-' ? ' ' . $checkOutDate : '');
    $total = $row["total"] ?: '-';
    $noteIn = $row["noteCheckIn"] ?: '';
    $noteOut = $row["noteCheckOut"] ?: '';
    $notes = '';
    if (!empty($noteIn) && $noteIn !== '-') $notes .= 'In: ' . $noteIn;
    if (!empty($noteOut) && $noteOut !== '-') {
        if (!empty($notes)) $notes .= ' | ';
        $notes .= 'Out: ' . $noteOut;
    }
    if (empty($notes)) $notes = '-';
?>
            <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($employee) ?></td>
                <td><?= htmlspecialchars($department) ?></td>
                <td><?= htmlspecialchars($type) ?></td>
                <td><?= htmlspecialchars($checkIn) ?></td>
                <td><?= htmlspecialchars($checkOut) ?></td>
                <td><?= htmlspecialchars($total) ?></td>
                <td><?= htmlspecialchars($notes) ?></td>
            </tr>
<?php endfor; ?>
        </tbody>
    </table>

    <div class="footer">
        <div>Check-in Logs Report &mdash; <?= date('d/m/Y H:i') ?></div>
        <div>Page <?= $currentPage ?> / <?= $totalPages ?></div>
    </div>
</div>
<?php endfor; ?>

<?php endif; ?>

</body>
</html>
