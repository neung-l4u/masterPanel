<?php
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';
include '../../assets/security/Sanitizer.php';
include '../../assets/security/QueryBuilder.php';

$category = !empty($_POST['category']) ? $_POST['category'] : '';
$status   = !empty($_POST['status'])   ? $_POST['status']   : '';

$qb = new QueryBuilder();
$qb->eq('m.category',    $category)
   ->eq('m.last_status', $status);

$baseSql = "SELECT m.* FROM monitors m WHERE m.delete_at IS NULL AND m.is_active = 1";
$result  = $qb->execute($db, $baseSql, 'ORDER BY m.id DESC')->fetchAll();

$data = ['data' => []];
$i    = 1;

foreach ($result as $row) {
    // Status badge
    $badgeClass = match($row['last_status']) {
        'up'      => 'success',
        'down'    => 'danger',
        default   => 'secondary',
    };
    $statusBadge = '<span class="badge bg-' . $badgeClass . '">' . esc($row['last_status']) . '</span>';

    // SSL column
    $sslDays = $row['ssl_days_left'];
    if ($sslDays === null) {
        $sslDisplay = '<span class="text-muted">-</span>';
    } elseif ($sslDays < 0) {
        $sslDisplay = '<span class="badge bg-danger">Expired</span>';
    } elseif ($sslDays <= 30) {
        $sslDisplay = '<span class="badge bg-warning text-dark">⚠ ' . $sslDays . 'd</span>';
    } else {
        $sslDisplay = '<span class="text-success">✓ ' . $sslDays . 'd</span>';
    }

    // Last check
    $lastCheck = $row['last_checked_at'] ?? '-';

    // URL display
    $urlDisplay = '<a href="' . escUrl($row['url']) . '" target="_blank" class="text-truncate d-inline-block" style="max-width:200px" title="' . escAttr($row['url']) . '">' . esc($row['url']) . '</a>';

    // Category badge
    $catColors = [
        'client'          => 'primary',
        'competitor'      => 'warning',
        'third_party'     => 'info',
        'payment_gateway' => 'success',
        'api_endpoint'    => 'dark',
        'supplier'        => 'secondary',
    ];
    $catColor   = $catColors[$row['category']] ?? 'secondary';
    $catDisplay = '<span class="badge bg-' . $catColor . '">' . esc($row['category']) . '</span>';

    // Actions
    $monId  = (int)$row['id'];
    $btnCheck   = '<a href="#" onclick="manualCheck(' . $monId . ')" title="Check Now"><i class="bi bi-arrow-clockwise text-primary"></i></a>';
    $btnEdit    = '<a href="#" onclick="setEdit(' . $monId . ')" title="Edit"><i class="bi bi-pencil-square text-dark"></i></a>';
    $btnLogs    = '<a href="#" onclick="viewLogs(' . $monId . ')" title="Logs"><i class="bi bi-journal-text text-secondary"></i></a>';
    $btnDown    = '<a href="#" onclick="viewDowntime(' . $monId . ')" title="Downtime"><i class="bi bi-graph-down text-warning"></i></a>';
    $btnDelete  = '<a href="#" onclick="setDel(' . $monId . ')" title="Delete"><i class="bi bi-x-square text-danger"></i></a>';

    $data['data'][] = [
        $i,
        esc($row['name']),
        $urlDisplay,
        $catDisplay,
        $row['check_interval'] . ' min',
        $statusBadge,
        $sslDisplay,
        esc($lastCheck),
        $btnCheck . ' ' . $btnEdit . ' ' . $btnLogs . ' ' . $btnDown . ' ' . $btnDelete,
    ];
    $i++;
}

echo json_encode($data);
