<?php
session_start();
global $db;

//เรียกใช้งาน object
include '../assets/db/db.php';
include "../assets/db/initDB.php";

$param['loginID'] = (!empty($_POST['loginID'])) ? trim($_POST['loginID']) : '';
$param['status'] = (!empty($_POST['status'])) ? trim($_POST['status']) : '1';
$param['act'] = (!empty($_POST['act'])) ? trim($_POST['act']) : '';

$return['result'] = '';
$return['msg'] = '';

$reports = $db->query('SELECT st.sID AS "userID", st.sNickName AS "name", COUNT(log.ID) as "total" 
    FROM staffs st 
    LEFT JOIN mondayslowreportlogs log ON st.sID = log.staffID 
    WHERE st.sStatus = 1 
    GROUP BY st.sID 
    ORDER BY total DESC, name ASC;')->fetchAll();

$row = array();
$i = 1;
$data = array("data"=> array());
foreach ($reports as $row) {

    $data["data"][] = array(
        $i,
        $row["name"],
        number_format($row["total"])
    );

    $i++;
}//foreach


if ($param['act'] == 'add') {
    if (empty($_SESSION['id'])) {
        echo json_encode([
            'status' => 'sessionExp',
            'message' => 'Login ID is required',
        ]);
        exit;
    } else {
        $reports = $db->query('INSERT INTO `mondayslowreportlogs`(`staffID`) VALUES (?)', $_SESSION['id']);
    }
}

$return['result'] = 'add report success';
$return['msg'] = 'report sent';

echo json_encode($data);