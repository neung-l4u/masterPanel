<?php
global $db;
session_start();
error_reporting(E_ALL);
header('Content-Type: application/json');

include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
include '../../assets/security/Sanitizer.php';
include '../../assets/security/QueryBuilder.php';

$data = array("data" => array());

$params["department"] = !empty($_POST['department']) ? $_POST['department'] : '';
$params["employee"] = !empty($_POST['employee']) ? $_POST['employee'] : '';
$params["status"] = !empty($_POST['status']) ? $_POST['status'] : '';
$params["dateFrom"] = !empty($_POST['dateFrom']) ? $_POST['dateFrom'] : '';
$params["dateTo"] = !empty($_POST['dateTo']) ? $_POST['dateTo'] : '';

$qb = new QueryBuilder();
$qb->eq('C.`department`', $params["department"])
   ->eq('C.`employee`', $params["employee"])
   ->eq('C.`workShiftTimeLogging`', $params["status"])
   ->gte('DATE(C.`dayCheckIn`)', $params["dateFrom"])
   ->lte('DATE(C.`dayCheckIn`)', $params["dateTo"]);

try {
    $baseSql = "SELECT C.`id`, C.`employee`, C.`status`, C.`department`, C.`workShiftTimeLogging`,
                   C.`checkinDate`, C.`checkIn`, C.`dayCheckIn`, C.`noteCheckIn`, C.`picCheckin`,
                   C.`checkOut`, C.`dayCheckOut`, C.`noteCheckOut`, C.`total`,
                   C.`createBy`, C.`updateAt`, S.`sPic` AS 'employeePic'
            FROM `checkin` C
            LEFT JOIN `staffs` S ON C.`createBy` = S.`sID`
            WHERE 1=1";
    
    $result = $qb->execute($db, $baseSql, 'ORDER BY C.`dayCheckIn` DESC, C.`id` DESC')->fetchAll();

    if ($result) {
        $i = 1;
        foreach ($result as $row) {
            $employeePic = (!empty($row["employeePic"]) && $row["employeePic"] != 'no_pic.png') ? $row["employeePic"] : 'no_pic.png';
            $employeeImg = '<img src="dist/img/crews/'.$employeePic.'" class="rounded-circle mr-2" style="width:30px;height:30px;object-fit:cover;" onerror="this.src=\'dist/img/crews/no_pic.png\'" alt="">';
            $employeeName = $employeeImg . ($row["employee"] ?: '-');
            $department = $row["department"] ?: '-';
            
            // Status badge
            $statusType = $row["workShiftTimeLogging"];
            if ($statusType == 'Clock In') {
                $statusBadge = '<span class="badge badge-success">Clock In</span>';
            } elseif ($statusType == 'Clock Out') {
                $statusBadge = '<span class="badge badge-danger">Clock Out</span>';
            } else {
                $statusBadge = '<span class="badge badge-secondary">' . esc($statusType) . '</span>';
            }
            
            // Check In time
            $checkInTime = $row["checkIn"] ?: '-';
            $checkInDate = $row["dayCheckIn"] ? date("d/m/Y", strtotime($row["dayCheckIn"])) : '-';
            $checkInDisplay = $checkInTime;
            if ($checkInDate != '-') {
                $checkInDisplay .= '<br><small class="text-muted">' . $checkInDate . '</small>';
            }
            
            // Check Out time
            $checkOutTime = $row["checkOut"] ?: '-';
            $checkOutDate = $row["dayCheckOut"] ? date("d/m/Y", strtotime($row["dayCheckOut"])) : '-';
            $checkOutDisplay = $checkOutTime;
            if ($checkOutDate != '-' && $checkOutTime != '-') {
                $checkOutDisplay .= '<br><small class="text-muted">' . $checkOutDate . '</small>';
            }
            
            // Total hours
            $totalHours = $row["total"] ?: '-';
            
            // Notes
            $noteIn = $row["noteCheckIn"] ?: '';
            $noteOut = $row["noteCheckOut"] ?: '';
            $notes = '';
            if (!empty($noteIn) && $noteIn != '-') {
                $notes .= '<small><strong>In:</strong> ' . esc($noteIn) . '</small>';
            }
            if (!empty($noteOut) && $noteOut != '-') {
                if (!empty($notes)) $notes .= '<br>';
                $notes .= '<small><strong>Out:</strong> ' . esc($noteOut) . '</small>';
            }
            if (empty($notes)) $notes = '-';
            
            // Attachment link
            $attachment = '-';
            $picCheckin = $row["picCheckin"] ?? '';
            if (!empty($picCheckin) && $picCheckin != '-') {
                $fileExt = strtolower(pathinfo($picCheckin, PATHINFO_EXTENSION));
                $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                if ($isImage) {
                    $attachment = '<a href="modules/checkin/upload/'.$picCheckin.'" target="_blank" data-lightbox="checkin-'.$row["id"].'"><i class="bi bi-image text-primary" style="font-size:1.2rem;"></i></a>';
                } else {
                    $attachment = '<a href="modules/checkin/upload/'.$picCheckin.'" target="_blank"><i class="bi bi-file-earmark-arrow-down text-primary" style="font-size:1.2rem;"></i></a>';
                }
            }
            
            // Status icon: loading if only check-in, checkmark if complete
            $statusIcon = '';
            if (!empty($row["checkIn"]) && (empty($row["checkOut"]) || $row["checkOut"] == '-')) {
                $statusIcon = '<div class="spinner-border spinner-border-sm text-warning" role="status" title="In Progress"><span class="sr-only">Loading...</span></div>';
            } elseif (!empty($row["checkIn"]) && !empty($row["checkOut"]) && $row["checkOut"] != '-') {
                $statusIcon = '<i class="bi bi-check-circle-fill text-success" style="font-size:1.5rem;" title="Complete"></i>';
            } else {
                $statusIcon = '<i class="bi bi-dash-circle text-muted" style="font-size:1.2rem;" title="No Data"></i>';
            }

            $data["data"][] = array(
                $i,
                $statusIcon,
                $employeeName,
                $department,
                $statusBadge,
                $checkInDisplay,
                $checkOutDisplay,
                $totalHours,
                $attachment,
                $notes
            );
            $i++;
        }
    }
} catch (Exception $e) {
    // Return empty data on error
}

echo json_encode($data);
