<?php
session_start();
date_default_timezone_set("Asia/Bangkok");
header('Content-Type: application/json');

// Check session
if (empty($_SESSION['id']) && empty($_COOKIE['id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../assets/php/DreamscapeAPI.php';

$api = new DreamscapeAPI();

$period = isset($_GET['period']) ? $_GET['period'] : 'month';

if ($period === 'custom') {
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
} elseif ($period === 'week') {
    $startDate = date('Y-m-d', strtotime('monday this week'));
    $endDate = date('Y-m-d');
} elseif ($period === 'year') {
    $startDate = date('Y-01-01');
    $endDate = date('Y-m-d');
} else {
    $startDate = date('Y-m-01');
    $endDate = date('Y-m-d');
}

try {
    $dashboardData = $api->getDashboardSummary($startDate, $endDate);
    
    if (!$dashboardData['success']) {
        throw new Exception('API call failed');
    }
    
    $data = $dashboardData['data'];
    $periodSales = isset($data['sales']['period_total']) ? $data['sales']['period_total'] : 0;
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'periodSales' => $periodSales,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'period' => $period
    ]);
} catch (Exception $e) {
    error_log('Dreamscape API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
