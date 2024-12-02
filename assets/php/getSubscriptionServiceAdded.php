<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$data["table"] = "";
$params["action"] = !empty($_REQUEST['act']) ? $_REQUEST['act'] : "";
$params["typeID"] = !empty($_REQUEST['typeID']) ? $_REQUEST['typeID'] : "";
$params["serviceID"] = !empty($_REQUEST['serviceID']) ? $_REQUEST['serviceID'] : "";
$params["month"] = !empty($_REQUEST['month']) ? $_REQUEST['month'] : "";
$params["year"] = !empty($_REQUEST['year']) ? $_REQUEST['year'] : "";

$data["typeID"] = $params["typeID"];

if ($params ["action"] == "getAddedService"){

    $table = '<table class="table  table-hover table-sm">';
    if($params["typeID"]=="Monthly"){
        $result = $db->query('SELECT ST.name, SM.id, SM.month, SM.year, SM.value FROM `SucscriptionsMonthly` SM , `SucscriptionsType` ST WHERE SM.`typeID` = ? AND SM.`year` = ? AND SM.`month` = ? AND SM.`typeID` = ST.`id`;', $params["serviceID"], $params["year"], $params["month"] )->fetchAll();
        $data["found"] = count($result);

        if ($data["found"]<=0){
            $table .= '<tbody><tr><td><p class="text-muted text-center">-- No data --</p></td></tr></tbody>';
        }else{
            $table .= '<thead><tr>';
            $table .= '<th class="text-left" style="width: 40%">Service</th>';
            $table .= '<th class="text-center" style="width: 15%">Year</th>';
            $table .= '<th class="text-center" style="width: 15%">Month</th>';
            $table .= '<th class="text-right" style="width: 25%">Total</th>';
            $table .= '<th class="text-center" style="width: 5%"></th>';
            $table .= '</tr></thead>';

            $table .= '<tbody>';
            foreach ($result as $row) {
                $btn["del"] = '<a href="#" onclick="setDel('.$row["id"].')"><svg xmlns="http://www.w3.org/2000/svg" height="1.2em" viewBox="0 0 576 512"><path d="M576 128c0-35.3-28.7-64-64-64H205.3c-17 0-33.3 6.7-45.3 18.7L9.4 233.4c-6 6-9.4 14.1-9.4 22.6s3.4 16.6 9.4 22.6L160 429.3c12 12 28.3 18.7 45.3 18.7H512c35.3 0 64-28.7 64-64V128zM271 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z" fill="#dc3545" /></svg></a>';
                $table .= '<tr>';
                $table .= '<td class="text-left">'.$row['name'].'</td>';
                $table .= '<td class="text-center">'.$row['year'].'</td>';
                $table .= '<td class="text-center">'.$row['month'].'</td>';
                $table .= '<td class="text-right">$'.number_format((float)$row['value'], 2, '.', ',').'</td>';
                $table .= '<td class="text-center">'.$btn["del"].'</td>';
                $table .= '</tr>';
            } //foreach
            $table .= '</tbody>';
        }

    } elseif($params["typeID"]=="Yearly"){
        //$result = $db->query('SELECT * FROM `SucscriptionsYearly` WHERE `typeID`= ? AND `year` = ?;', $params["serviceID"], $params ["year"] )->fetchAll();
        $result = $db->query('SELECT ST.name, SY.id, SY.typeID, SY.year, SY.value, SY.paidOn FROM `SucscriptionsYearly` SY, `SucscriptionsType` ST  WHERE SY.`typeID`= ? AND SY.`year` = ? AND SY.typeID = ST.id;;', $params["serviceID"], $params ["year"] )->fetchAll();
        $data["found"] = count($result);
        if ($data["found"]<=0){
            $table .= '<tbody><tr><td><p class="text-muted text-center">-- No data --</p></td></tr></tbody>';
        }else{
            $table .= '<thead><tr>';
            $table .= '<th class="text-left" style="width: 40%">Name</th>';
            $table .= '<th class="text-center" style="width: 15%">Year</th>';
            $table .= '<th class="text-right" style="width: 25%">Total</th>';
            $table .= '<th class="text-center" style="width: 15%">Paid On</th>';
            $table .= '<th class="text-center" style="width: 5%"></th>';
            $table .= '</tr></thead>';

            $table .= '<tbody>';
            foreach ($result as $row) {
                $btn["del"] = '<a href="#" onclick="setDel('.$row["id"].')"><svg xmlns="http://www.w3.org/2000/svg" height="1.2em" viewBox="0 0 576 512"><path d="M576 128c0-35.3-28.7-64-64-64H205.3c-17 0-33.3 6.7-45.3 18.7L9.4 233.4c-6 6-9.4 14.1-9.4 22.6s3.4 16.6 9.4 22.6L160 429.3c12 12 28.3 18.7 45.3 18.7H512c35.3 0 64-28.7 64-64V128zM271 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z" fill="#dc3545" /></svg></a>';
                $table .= '<tr>';
                $table .= '<td class="text-left">'.$row['name'].'</td>';
                $table .= '<td class="text-center">'.$row['year'].'</td>';
                $table .= '<td class="text-right">$'.number_format((float)$row['value'], 2, '.', ',').'</td>';
                $table .= '<td class="text-center">'.showOnlyDate($row['paidOn']).'</td>';
                $table .= '<td class="text-center">'.$btn["del"].'</td>';
                $table .= '</tr>';
            } //foreach
            $table .= '</tbody>';
        }

    }
    $table .= '</table>';
    $data["table"] = $table;
}

echo json_encode($data);

function showOnlyDate($param){
    $tmp = explode(" ", $param);
    $tmp2 = explode("-",$tmp[0]);
    return $tmp2[2]."/".$tmp2[1]."/".$tmp2[0];
}
