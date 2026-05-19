<?php
global $db;
session_start();
header('Content-Type: application/json');
include '../assets/db/db.php';
include '../assets/db/initDB.php';

$rows = $db->query(
    'SELECT id, shopName, emailAddress, country, requestType, testMode, status, submittedAt, payload
     FROM upgrade_downgrade_logs
     WHERE formType = ?
     ORDER BY id DESC',
    'downgrade'
)->fetchAll();

$data = ['data' => []];

foreach ($rows as $i => $row) {
    $testBadge = $row['testMode']
        ? '<span class="badge bg-warning text-dark">Test</span>'
        : '';

    $statusMap = [
        'pending'    => 'bg-secondary',
        'processing' => 'bg-primary',
        'completed'  => 'bg-success',
        'cancelled'  => 'bg-danger',
    ];
    $statusClass = $statusMap[$row['status']] ?? 'bg-secondary';
    $statusBadge = '<span class="badge ' . $statusClass . '">' . htmlspecialchars($row['status']) . '</span>';

    $date = $row['submittedAt'] ? date('d M y H:i', strtotime($row['submittedAt'])) : '-';

    $data['data'][] = [
        $i + 1,
        htmlspecialchars($row['shopName'] ?? '-'),
        htmlspecialchars($row['emailAddress'] ?? '-'),
        htmlspecialchars($row['country'] ?? '-'),
        htmlspecialchars($row['requestType'] ?? '-'),
        $statusBadge,
        $date,
        htmlspecialchars($row['payload'] ?? '{}', ENT_QUOTES, 'UTF-8'),
    ];
}

echo json_encode($data);
