<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$params["action"] = !empty($_REQUEST['act']) ? $_REQUEST['act'] : "";
$params["id"] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : "";
$params["month"] = !empty($_REQUEST['month']) ? $_REQUEST['month'] : "";
$params["year"] = !empty($_REQUEST['year']) ? $_REQUEST['year'] : "";
$total = 0;

if ($params ["action"] == "getType"){
    $result = $db->query('SELECT *  FROM `StatsMeasureDetail` WHERE `typeID` = ? AND `year` = ? ORDER BY year , month ;', $params ["id"], $params ["year"] )->fetchAll();
    $data["found"] = count($result);

    $table = '<table class="table  table-hover table-sm"><tbody>';
    if ($data["found"]>0){
        $table .= '<thead><tr>';
        $table .= '<th class="text-center" style="width: 25%">Year</th>';
        $table .= '<th class="text-center" style="width: 25%">Month</th>';
        $table .= '<th class="text-center" style="width: 45%">Value</th>';
        $table .= '<th class="text-center" style="width: 5%"></th>';
        $table .= '</tr></thead>';
        foreach ($result as $row) {
            $btn["del"] = '<a href="#" onclick="setDel('.$row["id"].')"><svg xmlns="http://www.w3.org/2000/svg" height="1.2em" viewBox="0 0 576 512"><path d="M576 128c0-35.3-28.7-64-64-64H205.3c-17 0-33.3 6.7-45.3 18.7L9.4 233.4c-6 6-9.4 14.1-9.4 22.6s3.4 16.6 9.4 22.6L160 429.3c12 12 28.3 18.7 45.3 18.7H512c35.3 0 64-28.7 64-64V128zM271 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z" fill="#dc3545" /></svg></a>';
            $table .= '<tr>';
            $table .= '<td class="text-center">'.$row['year'].'</td>';
            $table .= '<td class="text-center">'.$row['month'].'</td>';
            $table .= '<td class="text-right">$'.number_format((float)$row['total'], 2, '.', ',').'</td>';
            $table .= '<td class="text-center">'.$btn["del"].'</td>';
            $table .= '</tr>';
            $total += (float)$row['total'];
        } //foreach
        $table .= '<tfoot><tr>';
        $table .= '<th></th>';
        $table .= '<th class="text-right">Total</th>';
        $table .= '<th class="text-right">$'.number_format((float)$total, 2, '.', ',').'</th>';
        $table .= '<th></th>';
        $table .= '</tr></tfoot>';

    }else{
        $table .= '<tr><td><p class="text-muted">-- No data --</p></td></tr>';
    }
    $table .= '</tbody></table>';
    $data["table"] = $table;
}

echo json_encode($data);
