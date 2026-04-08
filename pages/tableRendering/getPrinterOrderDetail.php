<?php
global $db;
session_start();
header('Content-Type: application/json');

include '../../assets/db/db.php';
include '../../assets/db/initDB.php';

$response = array('status' => 'error', 'message' => 'Invalid request');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $orderId = intval($_GET['id']);
    
    try {
        $result = $db->query(
            'SELECT * FROM printer_orders WHERE id = ?',
            $orderId
        )->fetchArray();
        
        if ($result) {
            $response = array(
                'status' => 'success',
                'data' => $result
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Order not found'
            );
        }
    } catch (Exception $e) {
        $response = array(
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        );
        error_log("Get printer order detail error: " . $e->getMessage());
    }
} else {
    $response = array(
        'status' => 'error',
        'message' => 'Invalid order ID'
    );
}

echo json_encode($response);
?>
