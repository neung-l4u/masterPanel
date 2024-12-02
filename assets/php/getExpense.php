<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$timestamp = time();
$day = date("d", $timestamp);

$params["action"] = !empty($_REQUEST['act']) ? $_REQUEST['act'] : "";
$params["month"] = !empty($_REQUEST['month']) ? $_REQUEST['month'] : "";
$params["year"] = !empty($_REQUEST['year']) ? $_REQUEST['year'] : "";

$thisDay = $params["year"].'-'.$params["month"].'-'.$day;
$expenses = [];
$total = 0;

$table = "";

if ($params ["action"] == "bkkOffice"){
    $table .= '<table class="table table-hover table-borderless table-sm mb-3">';
    $table .= '<tbody>';

    $bkkOffice = $db->query('SELECT ed.`id`, et.`typeSection` AS `type`, et.`typeName` AS `name`, ed.`value`, ed.`month`, ed.`year`FROM `ExpenseDetail` ed, `ExpenseType` et WHERE ed.`typeID` = et.`id` AND ed.`month` = ? AND ed.`year` = ? AND et.typeSection = ? ORDER BY et.`typeSection`, et.`typeName`;',$params["month"], $params["year"], "bkkOffice" )->fetchAll();
    $expense["bkkOffice"] = count($bkkOffice);

    $i = 0;
    $total = 0;
    foreach ($bkkOffice as $info){
        $expenses["bkkOffice"][$i] =  [ $info["name"], $info["value"] ];
        $total += $info["value"];
        $i++;
    }

    if ($expense["bkkOffice"]<=0){
            $table .= '<tr>';
                $table .= '<td></td>';
                $table .= '<td class="text-center text-muted"><small> -- No Record -- </small></td>';
                $table .= '<td></td>';
            $table .= '</tr>';
    }else {
        unset($row);
        $i = 1;
        foreach ($expenses["bkkOffice"] as $row) {
            $table .= '<tr>';
                $table .= '<th>' . $i . '</th>';
                $table .= '<td>' . $row[0] . '</td>';
                $table .= '<td class="text-right">THB ' . number_format($row[1], 2) . '</td>';
            $table .= '</tr>';
            $i++;
        }// foreach

    }
    $table .= '</tbody>';
    $table .= '<tfoot>';
        $table .= '<tr>';
            $table .= '<td></td>';
            $table .= '<th class="text-right">Total</th>';
            $table .= '<th class="text-right">THB ' . number_format($total, 2) . '</th>';
        $table .= '</tr>';
    $table .= '</tfoot>';
    $table .= '</table>';//else

}elseif ($params ["action"] == "THOperation"){

    $table .= '<table class="table table-hover table-borderless table-sm mb-3">';
    $table .= '<tbody>';

    $THOperation = $db->query('SELECT ed.`id`, et.`typeSection` AS `type`, et.`typeName` AS `name`, ed.`value`, ed.`month`, ed.`year`FROM `ExpenseDetail` ed, `ExpenseType` et WHERE ed.`typeID` = et.`id` AND ed.`month` = ? AND ed.`year` = ? AND et.typeSection = ? ORDER BY et.`typeSection`, et.`typeName`;',$params["month"], $params["year"], "THOperation" )->fetchAll();
    $expense["THOperation"] = count($THOperation);


    if($expense["THOperation"]<=0) {
        $table .= '<tr>';
            $table .= '<td></td>';
            $table .= '<td class="text-center text-muted"><small> -- No Record -- </small></td>';
            $table .= '<td></td>';
        $table .= '</tr>';
    }else{
        $i = 0;
        $total = 0;
        foreach ($THOperation as $info) {
            $expenses["THOperation"][$i] = [$info["name"], $info["value"]];
            $total += $info["value"];
            $i++;
        }

        unset($row);
        $i = 1;
        foreach ($expenses["THOperation"] as $row) {
            $table .= '<tr>';
                $table .= '<th>' . $i . '</th>';
                $table .= '<td>' . $row[0] . '</td>';
                $table .= '<td class="text-right">THB ' . number_format($row[1], 2) . '</td>';
            $table .= '</tr>';
            $i++;
        }// foreach
    }
    $table .= '</tbody>';
    $table .= '<tfoot>';
        $table .= '<tr><td></td>';
            $table .= '<th class="text-right">Total</th>';
            $table .= '<th class="text-right">THB '.number_format($total,2).'</th>';
        $table .= '</tr>';
    $table .= '</tfoot>';
    $table .= '</table>';
}elseif ($params ["action"] == "CEOLiving"){

    $table .= '<table class="table table-hover table-borderless table-sm mb-3">';
    $table .= '<tbody>';

    $THOperation = $db->query('SELECT ed.`id`, et.`typeSection` AS `type`, et.`typeName` AS `name`, ed.`value`, ed.`month`, ed.`year`FROM `ExpenseDetail` ed, `ExpenseType` et WHERE ed.`typeID` = et.`id` AND ed.`month` = ? AND ed.`year` = ? AND et.typeSection = ? ORDER BY et.`typeSection`, et.`typeName`;',$params["month"], $params["year"], "CEOLiving" )->fetchAll();
    $expense["CEOLiving"] = count($THOperation);


    if($expense["CEOLiving"]<=0) {
        $table .= '<tr>';
            $table .= '<td></td>';
            $table .= '<td class="text-center text-muted"><small> -- No Record -- </small></td>';
            $table .= '<td></td>';
        $table .= '</tr>';
    }else{
        $i = 0;
        $total = 0;
        foreach ($THOperation as $info) {
            $expenses["CEOLiving"][$i] = [$info["name"], $info["value"]];
            $total += $info["value"];
            $i++;
        }

        unset($row);
        $i = 1;
        foreach ($expenses["CEOLiving"] as $row) {
            $table .= '<tr>';
                $table .= '<th>' . $i . '</th>';
                $table .= '<td>' . $row[0] . '</td>';
                $table .= '<td class="text-right">THB ' . number_format($row[1], 2) . '</td>';
            $table .= '</tr>';
            $i++;
        }// foreach
    }
    $table .= '</tbody>';
    $table .= '<tfoot>';
        $table .= '<tr><td></td>';
            $table .= '<th class="text-right">Total</th>';
            $table .= '<th class="text-right">THB '.number_format($total,2).'</th>';
        $table .= '</tr>';
    $table .= '</tfoot>';
    $table .= '</table>';
}elseif ($params ["action"] == "totalExpense"){
    $totalExpense = $db->query('SELECT ed.`id`, et.`typeSection` AS `type`, et.`typeName` AS `name`, ed.`value`, ed.`month`, ed.`year`FROM `ExpenseDetail` ed, `ExpenseType` et WHERE ed.`typeID` = et.`id` AND ed.`month` = ? AND ed.`year` = ? ORDER BY et.`typeSection`, et.`typeName`;',$params["month"], $params["year"] )->fetchAll();
    $expense["totalExpense"] = count($totalExpense);

    $i=0;
    $total = 0;
    foreach ($totalExpense as $info){
        $expenses["totalExpense"][$i] =  [ $info["name"], $info["value"] ];
        $total += $info["value"];
        $i++;
    }
    $table = '$'.number_format($total,2);
}elseif ($params ["action"] == "getExpensesTypeBkkOffice"){
    $typeExpense = $db->query('SELECT `et`.`id`, `et`.`typeName` FROM `ExpenseType` `et` WHERE `et`.`typeSection` = ? ORDER BY `et`.`typeName`;',"bkkOffice" )->fetchAll();
    $expense["typeExpense"] = count($typeExpense);

    $i=0;
    foreach ($typeExpense as $info){
        $expenses["typeExpense"][$i] =  [ $info["id"], $info["typeName"] ];
        $i++;
    }

    $table = '<label for="typeExpense" class="col-2 col-form-label">Type</label>';
    $table .= '<select id="typeExpense" class="custom-select col">';
    $table .= '<option value="0" disabled selected> -- Please select type -- </option>';
    $i = 1;
        foreach ($typeExpense as $row) {
            $table .= '<option value="'.$row["id"].'">'.$i." : ".$row["typeName"].'</option>';
            $i++;
        }//foreach
    $table .= '</select>';

}elseif ($params ["action"] == "getExpensesTypeTHOperation"){
    $typeExpense = $db->query('SELECT `et`.`id`, `et`.`typeName` FROM `ExpenseType` `et` WHERE `et`.`typeSection` = ? ORDER BY `et`.`typeName`;',"THOperation" )->fetchAll();
    $expense["typeExpense"] = count($typeExpense);

    $i=0;
    foreach ($typeExpense as $info){
        $expenses["typeExpense"][$i] =  [ $info["id"], $info["typeName"] ];
        $i++;
    }

    $table = '<label for="typeExpense" class="col-2 col-form-label">Type</label>';
    $table .= '<select id="typeExpense" class="custom-select col">';
    $table .= '<option value="0" disabled selected> -- Please select type -- </option>';
    $i = 1;
    foreach ($typeExpense as $row) {
        $table .= '<option value="'.$row["id"].'">'.$i." : ".$row["typeName"].'</option>';
        $i++;
    }//foreach
    $table .= '</select>';
}elseif ($params ["action"] == "getExpensesTypeCEOLiving"){
    $typeExpense = $db->query('SELECT `et`.`id`, `et`.`typeName` FROM `ExpenseType` `et` WHERE `et`.`typeSection` = ? ORDER BY `et`.`typeName`;',"CEOLiving" )->fetchAll();
    $expense["typeExpense"] = count($typeExpense);

    $i=0;
    foreach ($typeExpense as $info){
        $expenses["typeExpense"][$i] =  [ $info["id"], $info["typeName"] ];
        $i++;
    }

    $table = '<label for="typeExpense" class="col-2 col-form-label">Type</label>';
    $table .= '<select id="typeExpense" class="custom-select col">';
    $table .= '<option value="0" disabled selected> -- Please select type -- </option>';
    $i = 1;
    foreach ($typeExpense as $row) {
        $table .= '<option value="'.$row["id"].'">'.$i." : ".$row["typeName"].'</option>';
        $i++;
    }//foreach
    $table .= '</select>';
}//elseif

$data["table"] = $table;

echo json_encode($data);