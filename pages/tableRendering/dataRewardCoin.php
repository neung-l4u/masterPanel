<?php
global $db;
session_start();
error_reporting(0);
header('Content-Type: application/json');

include '../../assets/db/db.php';
include "../../assets/db/initDB.php";

$data = array("data" => array());

$params["coinType"] = !empty($_POST['coinType']) ? $_POST['coinType'] : '';
$params["activity"] = !empty($_POST['activity']) ? $_POST['activity'] : '';
$params["team"] = !empty($_POST['team']) ? $_POST['team'] : '';
$params["dateFrom"] = !empty($_POST['dateFrom']) ? $_POST['dateFrom'] : '';
$params["dateTo"] = !empty($_POST['dateTo']) ? $_POST['dateTo'] : '';

$where = "";
if (!empty($params["coinType"])) {
    $where .= " AND CL.`coinType` = '" . $params["coinType"] . "'";
}
if (!empty($params["activity"])) {
    $where .= " AND CL.`activityID` = '" . $params["activity"] . "'";
}
if (!empty($params["team"])) {
    $where .= " AND S.`teamID` = '" . $params["team"] . "'";
}
if (!empty($params["dateFrom"])) {
    $where .= " AND DATE(CL.`giveOn`) >= '" . $params["dateFrom"] . "'";
}
if (!empty($params["dateTo"])) {
    $where .= " AND DATE(CL.`giveOn`) <= '" . $params["dateTo"] . "'";
}

try {
    $sql = "SELECT CL.`id`, CT.`name` AS 'coin', CL.`coinType`, CL.`ownerID`, CL.`amount`, 
                   S.`sNickName` AS 'ownerNick', S.`sName` AS 'ownerName', S.`sPic` AS 'ownerPic',
                   S.`teamID`, T.`name` AS 'teamName',
                   G.`sNickName` AS 'giverNick', G.`sName` AS 'giverName',
                   CL.`reason`, CL.`giveOn`, CL.`activityID`, CA.`aName` AS 'activity'
            FROM `CoinLogs` CL, `staffs` S, `staffs` G, `CoinType` CT, `CoinActivities` CA, `Team` T
            WHERE CL.`status` = 1 
            AND CL.`ownerID` = S.`sID` 
            AND CL.`giveBy` = G.`sID` 
            AND CL.`coinType` = CT.`id`
            AND CL.`activityID` = CA.`aID`
            AND S.`teamID` = T.`id`
            AND S.`sStatus` = 1" . $where . " ORDER BY CL.`giveOn` DESC";
    
    $result = $db->query($sql)->fetchAll();

    if ($result) {
        $i = 1;
        foreach ($result as $row) {
            $ownerPic = (!empty($row["ownerPic"]) && $row["ownerPic"] != 'no_pic.png') ? $row["ownerPic"] : 'no_pic.png';
            $userImg = '<img src="dist/img/crews/'.$ownerPic.'" class="rounded-circle mr-2" style="width:30px;height:30px;object-fit:cover;" onerror="this.src=\'dist/img/crews/no_pic.png\'" alt="">';
            $userName = $userImg . showName($row["ownerNick"], $row["ownerName"]);
            $teamName = $row["teamName"];
            
            $amount = $row["amount"];
            $coinName = $row["coin"];
            $coinBadge = ($row["coinType"] == 1) ? 'badge-warning' : 'badge-info';
            $amountSign = ($amount >= 0) ? '+' : '';
            $amountClass = ($amount >= 0) ? 'text-success' : 'text-danger';
            $totalCoin = '<span class="' . $amountClass . ' font-weight-bold">' . $amountSign . number_format($amount, 2) . '</span>';
            $totalCoin .= ' <span class="badge ' . $coinBadge . '">' . $coinName . '</span>';
            
            $reward = !empty($row["activity"]) ? $row["activity"] : '-';
            $reason = !empty($row["reason"]) ? $row["reason"] : '-';
            $rewardDisplay = '<strong>' . $reward . '</strong>';
            if ($reason != '-' && $reason != $reward) {
                $rewardDisplay .= '<br><small class="text-muted">' . $reason . '</small>';
            }
            
            // ถ้าเป็น Redeem for gift (activityID = 12) แสดง THB ตาม CoinType
            // L4U (coinType = 1) = 50 THB, CEO (coinType = 2) = 500 THB
            if ($row["activityID"] == 12) {
                $coinRate = ($row["coinType"] == 1) ? 50 : 500;
                $thbAmount = abs($amount) * $coinRate;
                $rewardDisplay .= '<br><span class="badge badge-success">' . number_format($thbAmount) . ' THB</span>';
            }
            
            $dateTime = date("d/m/Y H:i", strtotime($row["giveOn"]));
            
            $giverName = showName($row["giverNick"], $row["giverName"]);
            $exchange = '<small>By: ' . $giverName . '</small>';

            $data["data"][] = array(
                $i,
                $userName,
                $teamName,
                $totalCoin,
                $rewardDisplay,
                $dateTime,
                $exchange
            );
            $i++;
        }
    }
} catch (Exception $e) {
    // Return empty data on error
}

echo json_encode($data);

function showName($nick, $full) {
    return $nick ?: '-';
}
