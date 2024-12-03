<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
$myID = $_SESSION['id'];

$email["hr"] = "nattiya@localforyou.com";
$email["admin1"] = "neung@localforyou.com";
$email["admin2"] = "bas@localforyou.com";
$email["admin3"] = "mark@localforyou.com";
$users = $db->query('SELECT `sEmail`,`sName`,`sNickName` FROM `staffs` WHERE `sID` = ?;', $myID)->fetchArray();
$email["my"] = $users['sEmail'];
$myName = $users['sName'];
$myNickName = $users['sNickName'];

$date["today"] = date("d/m/Y H:i:s", strtotime("now"));


$params["act"] = !empty($_REQUEST['act']) ? $_REQUEST['act'] : "";
$params["userID"] = !empty($_REQUEST['userID']) ? $_REQUEST['userID'] : "";
$params["formAction"] = !empty($_REQUEST['formAction']) ? $_REQUEST['formAction'] : "";
$params["coinType"] = !empty($_REQUEST['coinType']) ? $_REQUEST['coinType'] : "";
$params["coinAmount"] = !empty($_REQUEST['coinAmount']) ? $_REQUEST['coinAmount'] : "";
$params["activityID"] = !empty($_REQUEST['activityID']) ? $_REQUEST['activityID'] : "";
$params["coinReason"] = !empty($_REQUEST['coinReason']) ? $_REQUEST['coinReason'] : "";
$params["giveBy"] = $_SESSION['id'];
$params["coinTypeNum"] = 0;
$params["selectedTeam"] = !empty($_REQUEST['selectedTeam']) ? $_REQUEST['selectedTeam'] : "0";;

if($params["coinType"]=="l4u"){ $params["coinTypeNum"] = 1; }
elseif($params["coinType"]=="ceo"){ $params["coinTypeNum"] = 2; }

//for convert coin //
$params["sourceCoin"] = !empty($_REQUEST['sourceCoin']) ? $_REQUEST['sourceCoin'] : "";;
$params["input"] = !empty($_REQUEST['input']) ? $_REQUEST['input'] : "0";;
////////

if ($params ["act"] == "load"){
    $params["txt"] = "Got it";

    $users = $db->query('SELECT s.`sID` AS "id", s.`sNickName` AS "nickName", s.`sName` AS "fullName",s.`sPic` AS "pic", te.`name` AS "team", te.`fullName` AS "fteam",s.`sL4U` AS "L4U", s.`sCEO` AS "CEO" FROM `staffs` s, `Team` te WHERE s.`teamID` = te.`id` AND s.`sID` = ?;', $params["userID"])->fetchArray();
    $params['id'] = $users['id'];
    $params['nickName'] = $users['nickName'];
    $params['fullName'] = $users['fullName'];
    $params['showName'] = showName($users['nickName'], $users['fullName']);
    $params['pic'] = 'dist/img/crews/'.$users['pic'];
    $params['team'] = $users['team'];
    $params['fteam'] = $users['fteam'];
    $params['L4U'] = number_format($users['L4U'],2);
    $params['CEO'] = number_format($users['CEO'],2);
    $params['logs'] = array();

    $logs = $db->query('SELECT CL.`id`, CT.`name` AS "coin", CL.`ownerID`, CL.`amount`, ST.`sName` AS "from", ST.`sNickName` AS "nick", CL.`reason`, CL.`giveOn`, CL.`lastUpdate` 
                                FROM `CoinLogs` CL, `staffs` ST, `CoinType` CT
                                WHERE CL.`ownerID`= ? AND CL.`status` = ? AND CL.`giveBy` = ST.`sID` AND CL.`coinType` = `CT`.`id`
                                ORDER BY CL.`giveOn` DESC;'
        , $params["userID"], 1)->fetchAll();
    foreach ($logs as $row){
        $params['logs'][] = $row['amount'].' '.$row['coin'].' By '.showName($row['nick'],$row['from']).' - '.showDate($row['giveOn']).' # '.$row['reason'];
    }//foreach
}elseif ($params ["act"] == "save"){
    $insert = $db->query('INSERT INTO `CoinLogs`(`coinType`, `ownerID`, `amount`, `giveBy`, `reason`, `activityID`) VALUES (?,?,?,?,?,?);'
        ,$params["coinTypeNum"], $params["userID"], $params["coinAmount"], $params["giveBy"], $params["coinReason"], $params["activityID"]
    );

    $users = $db->query('SELECT `sL4U` AS "L4U", `sCEO` AS "CEO" FROM `staffs` WHERE `sID` = ?;', $params["userID"])->fetchArray();
    $coin['L4U'] = $users['L4U']; // เหรียญปัจจุบันที่มี
    $coin['CEO'] = $users['CEO']; // เหรียญปัจจุบันที่มี

    $coin['L4U'] = ($params["coinType"]=="l4u") ? ($users['L4U']+$params["coinAmount"]) : $users['L4U'];
    $coin['CEO'] = ($params["coinType"]=="ceo") ? ($users['CEO']+$params["coinAmount"]) : $users['CEO'];

    $update = $db->query('UPDATE `staffs` SET `sL4U` = ?, `sCEO` = ? WHERE `sID` = ?;', $coin['L4U'], $coin['CEO'], $params["userID"]);
    $params["affected"] = $update->affectedRows();
}elseif ($params ["act"] == "loadStat"){

    $allCoins = $db->query('SELECT SUM(`sL4U`) AS "allL4U", SUM(`sCEO`) AS "allCEO" FROM `staffs`;')->fetchArray();
    $top['L4U'] = $db->query('SELECT `sNickName` AS "nickName", `sName` AS "fullName" , `sL4U` AS "L4U" FROM `staffs` ORDER BY `sL4U` DESC, `sNickName` LIMIT 1;')->fetchArray();
    $top['CEO'] = $db->query('SELECT `sNickName` AS "nickName", `sName` AS "fullName" , `sCEO` AS "CEO" FROM `staffs` ORDER BY `sCEO` DESC, `sNickName` LIMIT 1;')->fetchArray();

    $coins["OverAll"] = [
        ['All Coin','',number_format($allCoins['allL4U']+$allCoins['allCEO'],2)],
        ['L4U','',number_format($allCoins['allL4U'],2)],
        ['CEO','',number_format($allCoins['allCEO'],2)],
        ['Top L4U',showName($top['L4U']['nickName'], $top['L4U']['fullName']),number_format($top['L4U']['L4U'],2)],
        ['Top CEO',showName($top['CEO']['nickName'], $top['CEO']['fullName']),number_format($top['CEO']['CEO'],2)]
    ];

    $stat = '<div class="card mt-lg-3 mb-lg-5"><div class="card-body d-flex">';
    foreach ($coins["OverAll"] as $row){
        $stat .= '<div class="card_stat d-flex flex-column align-items-center">';
        $stat .= '<h6 class="text-primary font-weight-bold">'.$row[0].'</h6>';
        $stat .= '<div>';
        $stat .= '<span class="font-weight-bold">'.$row[2].'</span>';
        $stat .= '<small class="text-muted"> Coin(s)</small>';
        if (!empty($row[1])){
            $stat .= '<div><small class="text-muted">( '.$row[1].' )</small></div>';
         }
        $stat .= '</div>';
        $stat .= '</div>';
    }//foreach
    $stat .= '</div></div>';
    $params["stat"] = $stat;

    if($params["selectedTeam"]==0){
        $users = $db->query('SELECT em.`sID` AS "id", em.`sNickName` AS "nickName", em.`sName` AS "fullName", em.`sPic` AS "pic", te.`name` AS "team", te.`fullName` AS "fteam", em.`sL4U` AS "L4U", em.`sCEO` AS "CEO" FROM `staffs` em, `Team` te WHERE em.`sStatus` = ? AND em.`teamID` = te.`id` AND em.`sDeleteAt` IS NULL ORDER BY em.`teamID`, em.`sNickName`;', 1)->fetchAll();
    }else if($params["selectedTeam"]==2){
        $users = $db->query('SELECT em.`sID` AS "id", em.`sNickName` AS "nickName", em.`sName` AS "fullName", em.`sPic` AS "pic", te.`name` AS "team", te.`fullName` AS "fteam", em.`sL4U` AS "L4U", em.`sCEO` AS "CEO" FROM `staffs` em, `Team` te WHERE em.`sStatus` = ? AND em.`teamID` = te.`id` AND em.`sDeleteAt` IS NULL AND em.`teamID` IN (2,8,10,11) ORDER BY em.`teamID`, em.`sNickName`;', 1)->fetchAll();
    }else{
        $users = $db->query('SELECT em.`sID` AS "id", em.`sNickName` AS "nickName", em.`sName` AS "fullName", em.`sPic` AS "pic", te.`name` AS "team", te.`fullName` AS "fteam", em.`sL4U` AS "L4U", em.`sCEO` AS "CEO" FROM `staffs` em, `Team` te WHERE em.`sStatus` = ? AND em.`teamID` = te.`id` AND em.`sDeleteAt` IS NULL AND em.`teamID`=? ORDER BY em.`teamID`, em.`sNickName`;', 1,$params["selectedTeam"])->fetchAll();
    }
    $uCard = "";
    foreach ($users as $row){
        $uCard .= '<div class="user_card card py-4 px-4 u-cursor-pt">';
        $uCard .= '<div class="d-flex align-items-center" onclick="openForm('.$row['id'].');">';
        $uCard .= '<img class="profile-coin-img mr-4" src="dist/img/crews/'.$row['pic'].'" alt="'.$row['nickName'].'" title="'.$row['nickName'].'">';
        $uCard .= '<div>';
        $uCard .= '<h5 class="user_name" title="'.showName($row['nickName'],$row['fullName']).'">'.showName($row['nickName'],$row['fullName']).'</h5>';
        $uCard .= '<div class="user_team mb-3" title="'.$row['fteam'].'">'.$row['team'].'</div>';
        $uCard .= '<div class="coin_l4u">L4U : '.number_format($row['L4U'],2).'</div>';
        $uCard .= '<div class="coin_ceo">CEO : '.number_format($row['CEO'],2).'</div>';
        $uCard .= '</div>';
        $uCard .= '</div>';
        $uCard .= '</div>';
    }//foreach
    $params["uCard"] = $uCard;
}elseif ($params ["act"] == "convert"){
    $checkRemainCoin = false;

    $users = $db->query('SELECT `sL4U` AS "L4U", `sCEO` AS "CEO" FROM `staffs` WHERE `sID` = ?;', $myID)->fetchArray();
    $coin['L4U'] = $users['L4U'];
    $coin['CEO'] = $users['CEO'];
    $reason = '';
    $coinType = 0;
    $coinAmount = 0;

    if ($params["sourceCoin"]=="ceo"){
        $reason = 'CEO to L4U';
        $params["input"] = ($coin['CEO']>=$params["input"]) ? $params["input"] : $coin['CEO'];
        $checkRemainCoin = $params["input"]<=$users['CEO'];
        $calCEO = $params["input"]*10;
        $coinAmount = $calCEO;
        $new["CEO"] = $coin['CEO']-$params["input"];
        $new["L4U"] = $coin['L4U']+$calCEO;
        $coinType = 2;
    }else if ($params["sourceCoin"]=="l4u"){
        $reason = 'L4U to CEO';
        $params["input"] = ($coin['L4U']>=$params["input"]) ? $params["input"] : $coin['L4U'];
        $checkRemainCoin = $params["input"]<=$users['L4U'];
        $calL4U = $params["input"]/10;
        $coinAmount = $calL4U;
        $new["CEO"] = $coin['CEO']+$calL4U;
        $new["L4U"] = $coin['L4U']-$params["input"];
        $coinType = 1;
    }
//$myID
    if ($checkRemainCoin){
        //ด้านบนคำนวน $new["CEO"] , $new["L4U"] เสร็จแล้ว ให้เอาไปอัพเดทให้ user ได้เลย
        $update = $db->query('UPDATE `staffs` SET `sL4U` = ?, `sCEO` = ? WHERE `sID` = ?;',$new["L4U"], $new["CEO"], $myID);
        $params["affected"] = $update->affectedRows();

        /// insert log ด้วยนะ
        $insert = $db->query('INSERT INTO `CoinLogs`(`coinType`, `ownerID`, `amount`, `giveBy`, `reason`, `activityID`) VALUES (?,?,?,?,?,?);'
            ,$coinType, $myID, $coinAmount, $myID, $reason, 10
        );
        $coinAmount = $coinAmount*-1;
        $insert = $db->query('INSERT INTO `SpendLogs` (`coinType`, `ownerID`, `amount`, `spendType`, `reason`, `spendBy`) VALUES (?,?,?,?,?,?);'
            ,$coinType, $myID, $coinAmount, 3, $reason, $myID
        );
    }
}elseif ($params ["act"] == "transferCoin"){

    $transferAmount = $_POST['transferAmount'];
    $receiverId = $_POST['receiverId'];

    $usersCoin = $db->query('SELECT `sL4U` AS "L4U", `sNickName` AS "nickName" FROM `staffs` WHERE `sID` = ?;', $myID)->fetchArray();
    if ($transferAmount <= $usersCoin['L4U']) {

        $usersCoinNew["L4U"] = $usersCoin['L4U']-$transferAmount;
        $usersName = $usersCoin['nickName'];

        $receiverCoin = $db->query('SELECT `sL4U` AS "L4U", `sNickName` AS "nickName" FROM `staffs` WHERE `sID` = ?;', $receiverId)->fetchArray();
        $receiverCoinNew["L4U"] = $receiverCoin['L4U']+$transferAmount;
        $receiverName = $receiverCoin['nickName'];

        $update = $db->query('UPDATE `staffs` SET `sL4U` = ? WHERE `sID` = ?;',$usersCoinNew["L4U"], $myID);
        $update = $db->query('UPDATE `staffs` SET `sL4U` = ? WHERE `sID` = ?;',$receiverCoinNew["L4U"], $receiverId);
        $params["affected"] = $update->affectedRows();

        $coinAmount = $transferAmount;
        $coinType = 1;

        $reason = $db->query('SELECT `name` FROM `spendtype` WHERE `id` = ?;', '2')->fetchArray();
        $newReason = $usersName ." ". $reason['name'] ." ". $transferAmount . " to ". $receiverName;

        $insert = $db->query('INSERT INTO `CoinLogs`(`coinType`, `ownerID`, `amount`, `giveBy`, `reason`, `activityID`) VALUES (?,?,?,?,?,?);'
            ,$coinType, $myID, $coinAmount, $myID, $newReason, 13
        );
        $insert = $db->query('INSERT INTO `SpendLogs` (`coinType`, `ownerID`, `amount`, `spendType`, `reason`, `spendBy`) VALUES (?,?,?,?,?,?);'
            ,$coinType, $myID, $coinAmount, 4, $newReason, $myID
        );
    } else {
        $params["error"] = "จำนวนเหรียญไม่เพียงพอ";
    }

}elseif ($params ["act"] == "redeem"){

    $redeemType = $_POST['redeemType'];
    
    //echo 'found email = '.$email["my"];
    $users = $db->query('SELECT `sL4U` AS "L4U" FROM `staffs` WHERE `sID` = ?;', $myID)->fetchArray();
    $coin['L4U'] = $users['L4U'];
    $new["L4U"] = $coin['L4U']-$params["input"];

    //ด้านบนคำนวน $new["CEO"] , $new["L4U"] เสร็จแล้ว ให้เอาไปอัพเดทให้ user ได้เลย
    $update = $db->query('UPDATE `staffs` SET `sL4U` = ? WHERE `sID` = ?;',$new["L4U"], $myID);
    $params["affected"] = $update->affectedRows();

    /// insert log ด้วยนะ
      
    switch ($redeemType) {
        case "Holiday":
        $reason = "Redeem For Dayoff";
        break;
        case "Travel":
        $reason = "Redeem For Travel";
        break;
        case "Gadgets":
        $reason = "Redeem For Gadgets";
        break;
        case "Entertainment":
        $reason = "Redeem For Entertainment";
        break;
        default:
        $reason = "Exchange for cash";
    }

    $coinAmount = $params["input"]*-1;
    $coinType = 1;
    $insert = $db->query('INSERT INTO `CoinLogs`(`coinType`, `ownerID`, `amount`, `giveBy`, `reason`, `activityID`) VALUES (?,?,?,?,?,?);'
        ,$coinType, $myID, $coinAmount, $myID, $reason, 12
    );
    $insert = $db->query('INSERT INTO `SpendLogs` (`coinType`, `ownerID`, `amount`, `spendType`, `reason`, `spendBy`) VALUES (?,?,?,?,?,?);'
        ,$coinType, $myID, $coinAmount, 4, $reason, $myID
    );

    //// send mail ////

    $result = [
        'result' => 0,
        'msg' => "",
        'email' => $data['email']
    ];

    $data = [
        'userEmail' => $email["my"],
        'name' => $myNickName.' '.$myName,
        'hr' => $email["hr"],
        'admin1' => $email["admin1"],
        'admin2' => $email["admin2"],
        'admin3' => $email["admin3"],
    ];

    if (!empty($data['userEmail'])) {
        $data['subject'] = "L4U coin redeem request : " . $myNickName;
        $headers = [
            'From' => $myNickName.' <'.$data['userEmail'].'>',
            'Cc' => $data['admin2'] .",". $data['admin3'],
            'Bcc' => $data['admin1'],
            'Reply-To' => $data['admin1'],
            'X-Sender' => $myNickName.' <'.$data['userEmail'].'>',
            'X-Mailer' => 'PHP/' . phpversion(),
            'X-Priority' => '1',
            'Return-Path' => $data['admin1'], //for error
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=utf-8'
        ];

        $moneyValue = $params["input"]*50;
        $message = $myNickName." ".$reason." amount ".$params["input"].' L4U coin. 
        
        <br><br> <strong>request date:</strong> '.$date["today"];

        $result['email'] = $data['email'];
        $result['payload'] = $data;


        if (mail($data['hr'], $data['subject'], $message, $headers)) {
            $result = [
                'result' => 1,
                'msg' => "Send email successful",
                'email' => $data['userEmail']
            ];
        } else {
            $result = [
                'result' => 0,
                'msg' => "Send email fail!!",
                'email' => $data['userEmail']
            ];
        }
    }

}elseif ($params ["act"] == "x2"){
    $users = $db->query('SELECT `sId`, `sNickName`, `sL4U` FROM `staffs` WHERE `sStatus` = ?;', 1)->fetchAll();
    foreach ($users as $row){
        $id = $row['sId'];
        $add = 0;

        if ($row['sL4U']>0){
            $l4u = $row['sL4U']*2;
            $add = $row['sL4U'];
        }else{
            $l4u = 1;
            $add = 1;
        }

        $update = $db->query('UPDATE `staffs` SET `sL4U` = ? WHERE `sID` = ?;',$l4u, $id);

        $coinType = 1;
        $coinAmount = $add;
        $reason = 'x2 event';

        $insert = $db->query('INSERT INTO `CoinLogs`(`coinType`, `ownerID`, `amount`, `giveBy`, `reason`, `activityID`) VALUES (?,?,?,?,?,?);'
            ,$coinType, $id, $coinAmount, $myID, $reason, 11
        );
    }//foreach

}

echo json_encode($params);

function showName($nick, $full){
    $tmp = explode(" ", $full);
    $getName = ($nick == $tmp[0]) ? $tmp[1] : $tmp[0];
    return $nick.' '.$getName;
}

function showDate($data){
    return date( "d/m/Y (H:i)", strtotime($data));
}