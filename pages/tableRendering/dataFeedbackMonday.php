<?php
global $db;
session_start();
header('Content-Type: application/json');

include '../../assets/db/db.php';
include '../../assets/db/initDB.php';

$data = array("data" => array());

try {
    $result = $db->query(
        'SELECT r.*, s.sNickName AS nick, s.sName AS name, s.sPic AS pic, t.name AS team
         FROM monday_advanced_reports r
         LEFT JOIN staffs s ON r.staffID = s.sID
         LEFT JOIN Team t ON s.teamID = t.id
         ORDER BY r.createdAt DESC'
    )->fetchAll();

    $i = 1;
    foreach ($result as $row) {
        $displayName = !empty($row['nick']) ? $row['nick'] : explode(' ', $row['name'])[0];
        $pic = $row['pic'] ?? 'no_pic.png';
        $reporterHtml = '<img src="dist/img/crews/'.$pic.'" class="rounded-circle mr-2" style="width:28px;height:28px;object-fit:cover;" onerror="this.src=\'dist/img/crews/no_pic.png\'" alt="">' . htmlspecialchars($displayName);

        $statusBadge = $row['status'] == 1
            ? '<span class="badge badge-warning">Active</span>'
            : '<span class="badge badge-success">Resolved</span>';

        $createdAt = date('d/m/Y H:i', strtotime($row['createdAt']));

        // Action buttons
        $viewBtn = '<button class="btn btn-sm btn-info btn-view-detail" data-id="'.$row['id'].'" title="View Detail"><i class="bi bi-eye"></i></button>';
        $resolveBtn = '';
        if ($row['status'] == 1) {
            $resolveBtn = ' <button class="btn btn-sm btn-success btn-resolve" data-id="'.$row['id'].'" title="Mark as Resolved"><i class="bi bi-check-lg"></i></button>';
        }

        $data["data"][] = array(
            $i,
            $reporterHtml,
            htmlspecialchars($row['board']),
            htmlspecialchars($row['subject']),
            $createdAt,
            $statusBadge,
            $viewBtn 
        );
        $i++;
    }
} catch (Exception $e) {
    // Return empty data on error
}

echo json_encode($data);
