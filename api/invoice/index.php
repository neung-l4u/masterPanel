<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';

$invoiceID = $_GET['invoiceID'];
if (!$invoiceID) {
    $respond = [
        "status" => [
            "code"       => "400",
            "message"    => "Bad Request",
        ]
    ];
    echo json_encode($respond);
    exit();
}

$data = $db->query('SELECT * FROM quotation WHERE invoiceID = ?', $invoiceID)->fetchArray();

if ($data) {
    $stamp = date('Y-m-d H:i:s');

    $respond = [
        "data" => [
            "id" => $data['id'],
            "check" => $data['check'],
            "type" => $data['type'],
            "name" => $data['name'],
            "address" => $data['address'],
            "taxNumber" => $data['taxNumber'],
            "invoiceID" => $data['invoiceID'],
            "customerEmail" => $data['customerEmail'],
            "createdAt" => $stamp
        ]
    ];
} else {
    $respond = [
        "status" => [
            "code"       => "404",
            "message"    => "Not found"
        ]
    ];
} 

echo json_encode($respond);
?>