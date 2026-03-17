<?php
session_start();
if (isset($_POST['latestId'])) {
    $_SESSION['activityReadId'] = $_POST['latestId'];
    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error']);
}
