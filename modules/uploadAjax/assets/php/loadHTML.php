<?php
global $db;
include '../db/db.php';
include "../db/initDB.php";

$mode = $_POST['mode'];
$token = $_POST['token'];

if ($mode == "loadHTML"){
    $select = $db->query('SELECT * FROM `testAjaxData`;')->fetchAll();

    $table = '<table border="1">';
    foreach ($select as $row){
        $table .= '<tr>';
            $table .= '<td width="200">'.$row['sName'].'</td>';
            $table .= '<td width="80">'.$row['sType'].'</td>';
            $table .= '<th>'.$row['sPO'].'</th>';
        $table .= '</tr>';
    }
    $table .= '</table>';

    $params['result'] = "found ".count($select)." data";
    $params['table'] = $table;
}

echo json_encode($params);