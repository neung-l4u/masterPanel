<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

try {
    require_once '../../assets/db/db.php';
    require_once '../../assets/db/initDB.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => ["code" => 500, "message" => "DB init failed: " . $e->getMessage()],
        "data" => null
    ]);
    exit;
}

$stripeID = !empty($_REQUEST['stripeID']) ? $_REQUEST['stripeID'] : (!empty($_POST['stripeID']) ? $_POST['stripeID'] : null);
$customerEmail = !empty($_REQUEST['customerEmail']) ? $_REQUEST['customerEmail'] : (!empty($_POST['customerEmail']) ? $_POST['customerEmail'] : null);

if (!$stripeID || !$customerEmail) {
    echo json_encode([
        "status" => [
            "code" => 400,
            "message" => "Bad Request - stripeID and customerEmail are required"
        ],
        "data" => null
    ]);
    exit;
}

try {
    $db->query('INSERT IGNORE INTO order_tracking (stripeID, customerEmail, statusUser, reminderStep) VALUES (?, ?, ?, ?)', $stripeID, $customerEmail, 'pending', 0);

    if ($db->affectedRows() > 0) {
        echo json_encode([
            "status" => [
                "code" => 200,
                "message" => "Insert successful"
            ],
            "data" => [
                "stripeID" => $stripeID,
                "customerEmail" => $customerEmail
            ]
        ]);
    } else {
        echo json_encode([
            "status" => [
                "code" => 200,
                "message" => "Already exists"
            ],
            "data" => [
                "stripeID" => $stripeID,
                "customerEmail" => $customerEmail
            ]
        ]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => [
            "code" => 500,
            "message" => "DB error: " . $e->getMessage()
        ],
        "data" => null
    ]);
}
