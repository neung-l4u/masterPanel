<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$data["table"] = "";
$params["action"] = !empty($_REQUEST['act']) ? $_REQUEST['act'] : "";
$params["typeID"] = !empty($_REQUEST['typeID']) ? $_REQUEST['typeID'] : "";
$params["month"] = !empty($_REQUEST['month']) ? $_REQUEST['month'] : "0";
$params["year"] = !empty($_REQUEST['year']) ? $_REQUEST['year'] : "0";

$data["typeID"] = $params["typeID"];
$result = "";

if ($params ["action"] == "getAddedData"){

    $table = '<table class="table table-hover table-sm table-borderless">';

    if ($params["typeID"] == "formBkkExpense"){
        $result = $db->query('SELECT ed.`id`, et.`typeSection` AS `type`, et.`typeName` AS `name`, ed.`value`, ed.`month`, ed.`year`FROM `ExpenseDetail` ed, `ExpenseType` et WHERE ed.`typeID` = et.`id` AND ed.`month` = ? AND ed.`year` = ? AND et.typeSection = ? ORDER BY et.`typeSection`, et.`typeName`;',$params["month"], $params["year"], "bkkOffice" )->fetchAll();
    }elseif ($params["typeID"] == "formThOperation") {
        $result = $db->query('SELECT ed.`id`, et.`typeSection` AS `type`, et.`typeName` AS `name`, ed.`value`, ed.`month`, ed.`year`FROM `ExpenseDetail` ed, `ExpenseType` et WHERE ed.`typeID` = et.`id` AND ed.`month` = ? AND ed.`year` = ? AND et.typeSection = ? ORDER BY et.`typeSection`, et.`typeName`;',$params["month"], $params["year"], "THOperation" )->fetchAll();
    }elseif ($params["typeID"] == "formCEOLiving") {
        $result = $db->query('SELECT ed.`id`, et.`typeSection` AS `type`, et.`typeName` AS `name`, ed.`value`, ed.`month`, ed.`year`FROM `ExpenseDetail` ed, `ExpenseType` et WHERE ed.`typeID` = et.`id` AND ed.`month` = ? AND ed.`year` = ? AND et.typeSection = ? ORDER BY et.`typeSection`, et.`typeName`;',$params["month"], $params["year"], "CEOLiving" )->fetchAll();
    }elseif ($params["typeID"] == "formEmployee") {
        $result = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`status`, em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`teamID` = te.`id`;')->fetchAll();
    }

    $data["found"] = count($result);
    $i=1;

    if ($data["found"] <= 0) {
        $table .= '<tr>';
            $table .= '<td></td>';
            $table .= '<td class="text-center text-muted"><small> -- No Record -- </small></td>';
            $table .= '<td></td>';
        $table .= '</tr>';
    }else{
        if (($params["typeID"] == "formBkkExpense") or ($params["typeID"] == "formThOperation") or ($params["typeID"] == "formCEOLiving")) {
            $table = '<h6>Found '.$data["found"].' item(s).</h6>'.$table;
            foreach ($result as $row) {
                $table .= '<tr>';
                $table .= '<th scope="row">' . $i . '</th>';
                $table .= '<td>' . $row["name"] . '</td>';
                $table .= '<td class="text-right">THB ' . number_format($row["value"], 2) . '</td>';
                $table .= '<td class="text-right">';
                    $table .= '<a href="#" onclick="setDel('.$row["id"].');">';
                        $table .= '<svg xmlns="http://www.w3.org/2000/svg" height="1.2em" viewBox="0 0 576 512"><path d="M576 128c0-35.3-28.7-64-64-64H205.3c-17 0-33.3 6.7-45.3 18.7L9.4 233.4c-6 6-9.4 14.1-9.4 22.6s3.4 16.6 9.4 22.6L160 429.3c12 12 28.3 18.7 45.3 18.7H512c35.3 0 64-28.7 64-64V128zM271 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z" fill="#dc3545" /></svg>';
                    $table .= '</a>';
                $table .= '</td>';

                $table .= '</tr>';// else
                $i++;
            }// foreach
        }elseif ($params["typeID"] == "formEmployee") {
            $table = '<h6 class="font-weight-bold">Found : '.$data["found"].'people(s)</h6>'.$table;
            foreach ($result as $row) {
                $table .= '<tr>';
                   $table .= '<td width="10">'.$i.'</td>';
                    $table .= '<td>'.$row["nickName"].' ('.$row["fullName"].')</td>';
                    $table .= '<td class="text-right">';
                        $table .= '<a href="#" onclick="setStatus('.$row["id"].', '.$row["status"].');">';
                            if ($row["status"]){
                                $table .= '<svg xmlns="http://www.w3.org/2000/svg" height="1.3em" viewBox="0 0 576 512"><path d="M192 64C86 64 0 150 0 256S86 448 192 448H384c106 0 192-86 192-192s-86-192-192-192H192zm192 96a96 96 0 1 1 0 192 96 96 0 1 1 0-192z" fill="#579125" /></svg>';
                            }else{
                                $table .= '<svg xmlns="http://www.w3.org/2000/svg" height="1.3em" viewBox="0 0 576 512"><path d="M384 128c70.7 0 128 57.3 128 128s-57.3 128-128 128H192c-70.7 0-128-57.3-128-128s57.3-128 128-128H384zM576 256c0-106-86-192-192-192H192C86 64 0 150 0 256S86 448 192 448H384c106 0 192-86 192-192zM192 352a96 96 0 1 0 0-192 96 96 0 1 0 0 192z" fill="#a9aaae" /></svg>';
                            }
                        $table .= '</a>';
                    $table .= '</td>';
                    $table .= '<td class="text-right">';
                        $table .= '<a href="#" onclick="setDel('.$row["id"].');">';
                            $table .= '<svg xmlns="http://www.w3.org/2000/svg" height="1.2em" viewBox="0 0 576 512"><path d="M576 128c0-35.3-28.7-64-64-64H205.3c-17 0-33.3 6.7-45.3 18.7L9.4 233.4c-6 6-9.4 14.1-9.4 22.6s3.4 16.6 9.4 22.6L160 429.3c12 12 28.3 18.7 45.3 18.7H512c35.3 0 64-28.7 64-64V128zM271 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z" fill="#dc3545" /></svg>';
                        $table .= '</a>';
                    $table .= '</td>';
                $table .= '</tr>';// else
                $i++;
            }// foreach
        }
    }//else
    $table .= '</table>';

    $data["table"] = $table;
}//if getAddedData

echo json_encode($data);

function showOnlyDate($param){
    $tmp = explode(" ", $param);
    $tmp2 = explode("-",$tmp[0]);
    return $tmp2[2]."/".$tmp2[1]."/".$tmp2[0];
}
