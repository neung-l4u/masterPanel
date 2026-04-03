<?php
global $db;
session_start();
header('Content-Type: application/json');

include '../../assets/db/db.php';
include '../../assets/db/initDB.php';

$data = array("data" => array());

try {
    $result = $db->query(
        'SELECT * FROM printer_orders ORDER BY order_date DESC'
    )->fetchAll();

    $i = 1;
    foreach ($result as $row) {
        $customerName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
        
        $countryBadge = $row['country'] === 'AU' 
            ? '<span class="badge badge-success">🇦🇺 AU</span>'
            : '<span class="badge badge-info">🇳🇿 NZ</span>';

        $printerBadge = $row['printer_model'] === 'TM-T82IIIL'
            ? '<span class="badge badge-primary">TM-T82IIIL</span>'
            : '<span class="badge badge-warning">TM-M30</span>';

        $orderDate = date('d/m/Y H:i', strtotime($row['order_date']));

        // Action buttons
        $viewBtn = '<button class="btn btn-sm btn-info btn-view-detail" data-id="'.$row['id'].'" title="View Detail"><i class="bi bi-eye"></i></button>';

        $data["data"][] = array(
            $i,
            $customerName,
            htmlspecialchars($row['shop_name']),
            '<a href="mailto:' . htmlspecialchars($row['email']) . '">' . htmlspecialchars($row['email']) . '</a>',
            $countryBadge,
            $printerBadge,
            '<strong class="text-success">' . htmlspecialchars($row['price']) . '</strong>',
            $orderDate,
            $viewBtn
        );
        $i++;
    }
} catch (Exception $e) {
    // Return empty data on error
    error_log("Printers log error: " . $e->getMessage());
}

echo json_encode($data);
?>
