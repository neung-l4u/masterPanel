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

$table = "";
$new = "";
$leave = "";
$all = "";


//(em.`activeDate` > ("2023-11-01" - interval 30 day)) AND (em.`activeDate` < ("2023-11-01" + interval 30 day))
//

if (($params ["action"] == "getEmployee") or ($params ["action"] == "getLeaving") or ($params ["action"] == "getNew")){
    $new = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE (em.`activeDate` > (? - interval 30 day)) AND (em.`activeDate` < (? + interval 30 day)) AND em.`status` = ? AND em.`teamID` = te.`id`;',$thisDay, $thisDay, 1)->fetchAll();
        $employee["new"] = count($new);
    $leave = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE (em.`activeDate` > (? - interval 30 day)) AND (em.`activeDate` < (? + interval 30 day)) AND em.`status` = ? AND em.`teamID` = te.`id`;',$thisDay, $thisDay, 0)->fetchAll();
        $employee["leave"] = count($leave);
    $all = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id`;', 1)->fetchAll();
        $employee["all"] = count($all);
}


if ($params ["action"] == "getEmployee"){
    $table .= '<table class="table table-hover table-borderless table-sm mb-3">';
    $table .= '<tbody>';

    $staffs["OverAll"] = [
        [ 'New Staffs', $employee["new"] ],
        [ 'Staff Leaving', $employee["leave"] ],
        [ 'Total Staffs', $employee["all"] ]
    ];
    unset($row);

    foreach ($staffs["OverAll"] as $row){
        $table .= '<tr>';
            $table .= '<td>'.$row[0].'</td>';
            $table .= '<td class="text-right"><span class="font-weight-bold">'.$row[1].'</span> <small class="text-muted">Person(s)</small></td>';
        $table .= '</tr>';
    }// foreach

    $table .= '</tbody>';
    $table .= '</table>';

}elseif ($params ["action"] == "getTeam"){
    $cs = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 1)->fetchAll();
    $employee["cs"] = count($cs);
    $amAU = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 2)->fetchAll();
    $employee["amAU"] = count($amAU);
    $amNZ = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 10)->fetchAll();
    $employee["amNZ"] = count($amNZ);
    $amUK = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 11)->fetchAll();
    $employee["amUK"] = count($amUK);
    $amUS = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 8)->fetchAll();
    $employee["amUS"] = count($amUS);
    $se = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 3)->fetchAll();
    $employee["se"] = count($se);
    $hr = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 4)->fetchAll();
    $employee["hr"] = count($hr);
    $it = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 5)->fetchAll();
    $employee["it"] = count($it);
    $ceo = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 6)->fetchAll();
    $employee["ceo"] = count($ceo);
    $ot = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 7)->fetchAll();
    $employee["ot"] = count($ot);

    $staffs["Teams"] = [
        ['CS Team',$employee["cs"]],
        ['AM AU Team',$employee["amAU"]],
        ['AM NZ Team',$employee["amNZ"]],
        ['AM USA Team',$employee["amUS"]],
        ['AM UK Team',$employee["amUK"]],
        ['SE Team',$employee["se"]],
        ['HR Team',$employee["hr"]],
        ['IT Team',$employee["it"]],
        ['CEO Team',$employee["ceo"]],
        ['Other Team',$employee["ot"]]
    ];
    unset($row);

    $table .= '<table class="table table-hover table-borderless table-sm mb-3">';
    $table .= '<tbody>';
    foreach ($staffs["Teams"] as $row){
        $table .= '<tr>';
            $table .= '<td>'.$row[0].'</td>';
            $table .= '<td class="text-right"><span class="font-weight-bold">'.$row[1].'</span> <small class="text-muted">Person(s)</small></td>';
        $table .= '</tr>';
    }//foreach
    $table .= '</tbody>';
    $table .= '</table>';

}elseif ($params ["action"] == "getLeaving"){
    $staffs["Leaving"] = [];
    foreach ($leave as $leaveStaff){
        $staffs["Leaving"][] = ['<span class="text-danger">'.$leaveStaff['nickName']."</span> : ".$leaveStaff['fullName'], $leaveStaff['teamFull']];
    }// foreach

    unset($row);

    $table = '<ul>';
    foreach ($staffs["Leaving"] as $row){
        $table .= '<li><small>'.$row[0].' <span class="text-info"> << '.$row[1].'</span></small></li>';
    }//foreach
    if(count($staffs["Leaving"])<=0){
        $table .= '<li class="text-muted"><small>There are no leaving employees.</small></li>';
    }
    $table .= '</ul>';

}elseif ($params ["action"] == "getNew") {

    $staffs["Latest"] = [];
    foreach ($new as $newStaff){
        $staffs["Latest"][] = ['<span class="text-primary">'.$newStaff['nickName']."</span> : ".$newStaff['fullName'], $newStaff['team']." (".reFormatDate($newStaff['activeDate']).")" ];
    }// foreach

    unset($row);
    $table = '<ul>';
    foreach ($staffs["Latest"] as $row){
        $table .= '<li><small>'.$row[0].'<span class="text-info"> >> '.$row[1].'</span></small></li>';
    }//foreach
    if(count($staffs["Latest"])<=0){
        $table .= '<li class="text-muted"><small>There are no new employees.</small></li>';
    }//if
}

$data["table"] = $table;

echo json_encode($data);

function reFormatDate($param){
    $tmp = explode("-", $param);
    return $tmp[2]."/".$tmp[1]."/".$tmp[0];
}