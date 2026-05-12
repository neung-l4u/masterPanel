<?php
global $db;
session_start();
error_reporting(E_ALL);

include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
include '../../assets/security/QueryBuilder.php';

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'csv';
$params["department"] = !empty($_GET['department']) ? $_GET['department'] : '';
$params["employee"] = !empty($_GET['employee']) ? $_GET['employee'] : '';
$params["status"] = !empty($_GET['status']) ? $_GET['status'] : '';
$params["dateFrom"] = !empty($_GET['dateFrom']) ? $_GET['dateFrom'] : '';
$params["dateTo"] = !empty($_GET['dateTo']) ? $_GET['dateTo'] : '';

$qb = new QueryBuilder();
$qb->eq('C.`department`', $params["department"])
   ->eq('C.`employee`', $params["employee"])
   ->eq('C.`workShiftTimeLogging`', $params["status"])
   ->gte('DATE(C.`dayCheckIn`)', $params["dateFrom"])
   ->lte('DATE(C.`dayCheckIn`)', $params["dateTo"]);

try {
    $baseSql = "SELECT C.`id`, C.`employee`, C.`department`, C.`workShiftTimeLogging`,
                   C.`checkIn`, C.`dayCheckIn`, C.`noteCheckIn`,
                   C.`checkOut`, C.`dayCheckOut`, C.`noteCheckOut`, C.`total`
            FROM `checkin` C
            WHERE 1=1";

    $result = $qb->execute($db, $baseSql, 'ORDER BY C.`dayCheckIn` DESC, C.`id` DESC')->fetchAll();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Check-in Logs');

    // Header row
    $headers = ['#', 'Employee', 'Department', 'Type', 'Check In Time', 'Check In Date', 'Check Out Time', 'Check Out Date', 'Total', 'Note In', 'Note Out'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $col++;
    }

    // Style header
    $headerRange = 'A1:K1';
    $sheet->getStyle($headerRange)->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '343A40']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    ]);

    // Data rows
    $rowNum = 2;
    $i = 1;
    if ($result) {
        foreach ($result as $row) {
            $employee = $row["employee"] ?: '-';
            $department = $row["department"] ?: '-';
            $type = $row["workShiftTimeLogging"] ?: '-';
            $checkInTime = $row["checkIn"] ?: '-';
            $checkInDate = $row["dayCheckIn"] ? date("d/m/Y", strtotime($row["dayCheckIn"])) : '-';
            $checkOutTime = $row["checkOut"] ?: '-';
            $checkOutDate = $row["dayCheckOut"] ? date("d/m/Y", strtotime($row["dayCheckOut"])) : '-';
            $total = $row["total"] ?: '-';
            $noteIn = $row["noteCheckIn"] ?: '-';
            $noteOut = $row["noteCheckOut"] ?: '-';

            $sheet->setCellValue('A' . $rowNum, $i);
            $sheet->setCellValue('B' . $rowNum, $employee);
            $sheet->setCellValue('C' . $rowNum, $department);
            $sheet->setCellValue('D' . $rowNum, $type);
            $sheet->setCellValue('E' . $rowNum, $checkInTime);
            $sheet->setCellValue('F' . $rowNum, $checkInDate);
            $sheet->setCellValue('G' . $rowNum, $checkOutTime);
            $sheet->setCellValue('H' . $rowNum, $checkOutDate);
            $sheet->setCellValue('I' . $rowNum, $total);
            $sheet->setCellValue('J' . $rowNum, $noteIn);
            $sheet->setCellValue('K' . $rowNum, $noteOut);

            $rowNum++;
            $i++;
        }
    }

    // Data borders
    if ($rowNum > 2) {
        $dataRange = 'A2:K' . ($rowNum - 1);
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
    }

    // Auto-size columns
    foreach (range('A', 'K') as $colLetter) {
        $sheet->getColumnDimension($colLetter)->setAutoSize(true);
    }

    // Generate filename
    $filename = 'CheckinLogs_' . date('Ymd_His');

    if ($format === 'xlsx') {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
    } else {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Cache-Control: max-age=0');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel compatibility
        $writer = new Csv($spreadsheet);
        $writer->setDelimiter(',');
        $writer->setEnclosure('"');
    }

    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Export failed: ' . $e->getMessage()]);
}
