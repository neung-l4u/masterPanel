<?php
global $db;
session_start();
header('Content-Type: application/json');

include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

try {
    $result = $db->query('SELECT DISTINCT sName, sNickName FROM staffs WHERE sDeleteAt IS NULL AND sStatus = 1 AND teamID = 3 ORDER BY sNickName')->fetchAll();
    
    $agents = array();
    foreach ($result as $row) {
        $agents[] = array(
            'name' => $row['sName'],
            'nick' => $row['sNickName']
        );
    }
    
    echo json_encode($agents);
} catch (Exception $e) {
    echo json_encode([]);
    error_log("Get sale agents error: " . $e->getMessage());
}
?>
