<?php
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';

// Filters
$dateStart = $_POST['dateStart'] ?? '';
$dateEnd   = $_POST['dateEnd']   ?? '';
$country   = trim($_POST['country'] ?? '');
$status    = trim($_POST['status']  ?? '');
$search    = trim($_POST['search']  ?? '');

// Base query — ใช้ timestamp เป็นหลักในการเรียง, dateThai สำหรับโชว์
$where  = ' WHERE 1=1';
$params = [];

if (!empty($dateStart) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStart)) {
    $where   .= ' AND DATE(`timeStamp`) >= ?';
    $params[] = $dateStart;
}
if (!empty($dateEnd) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateEnd)) {
    $where   .= ' AND DATE(`timeStamp`) <= ?';
    $params[] = $dateEnd;
}
if ($country !== '') {
    $where   .= ' AND `Country` = ?';
    $params[] = $country;
}
if ($status !== '' && in_array($status, ['active','rescheduled','cancelled'], true)) {
    $where   .= ' AND `status` = ?';
    $params[] = $status;
}
if ($search !== '') {
    $where   .= ' AND (`fullName` LIKE ? OR `Email` LIKE ? OR `restaurantName` LIKE ?)';
    $like     = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
}

$sql = 'SELECT id, bookingId, fullName, restaurantName, Country, Email, productNees, noteForm,
               bookingDate, bookingTime, endBookingTime, gcalEventId, hangoutLink, htmlLink,
               dateThai, `timeStamp`, status
          FROM `formASAP`'
     . $where
     . ' ORDER BY `timeStamp` DESC';

$args = array_merge([$sql], $params);
call_user_func_array([$db, 'query'], $args);
$rows = $db->fetchAll();

$data = ['data' => []];

foreach ($rows as $r) {
    $dateDisplay = $r['dateThai'] ?: $r['timeStamp'];
    $dateSort    = $r['timeStamp']; // ใช้ sort

    $fullName = htmlspecialchars($r['fullName'] ?? '', ENT_QUOTES);
    $shopName = htmlspecialchars($r['restaurantName'] ?? '', ENT_QUOTES);
    $ctry     = htmlspecialchars($r['Country'] ?? '', ENT_QUOTES);
    $email    = htmlspecialchars($r['Email'] ?? '', ENT_QUOTES);
    $products = htmlspecialchars($r['productNees'] ?? '', ENT_QUOTES);
    $note     = htmlspecialchars($r['noteForm'] ?? '', ENT_QUOTES);

    // Appointment = bookingDate + bookingTime
    $apt = '';
    if (!empty($r['bookingDate'])) {
        $apt  = $r['bookingDate'];
        if (!empty($r['bookingTime']))    $apt .= ' ' . substr($r['bookingTime'], 0, 5);
        if (!empty($r['endBookingTime'])) $apt .= '–' . substr($r['endBookingTime'], 0, 5);
    }

    // Status badge
    $statusClass = 'status-' . ($r['status'] ?? 'active');
    $statusHtml  = '<span class="status-badge ' . $statusClass . '">'
                 . htmlspecialchars($r['status'] ?? '-')
                 . '</span>';

    // Meet link button (ถ้ามี)
    if (!empty($r['hangoutLink'])) {
        $meetBtn = ' <a href="' . htmlspecialchars($r['hangoutLink']) . '" target="_blank" title="Open Meet"><i class="bi bi-camera-video"></i></a>';
        $apt .= $meetBtn;
    }

    // Date cell with hidden sort key
    $dateCell = '<span data-sort="' . htmlspecialchars($dateSort) . '">' . htmlspecialchars($dateDisplay) . '</span>';

    $data['data'][] = [
        $dateCell,
        $fullName,
        $shopName,
        $ctry,
        $email,
        $products,
        $note,
        $apt,
        $statusHtml,
    ];
}

echo json_encode($data);
