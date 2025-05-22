<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
$myID = $_SESSION['id'];

$params["action"] = !empty($_POST['act']) ? $_POST['act'] : "";
$params["id"] = !empty($_POST['id']) ? $_POST['id'] : "";
$params["editID"] = !empty($_GET['editID']) ? $_GET['editID'] : "";
$params["formAction"] = !empty($_POST['formAction']) ? $_POST['formAction'] : 'add';

if ($params ["action"] == "setStatus"){
    $update = $db->query('UPDATE `staffs` SET `sStatus` = ? WHERE `staffs`.`sID` = ?;', $params ["status"], $params ["id"]);
    $params["affected"] = $update->affectedRows();

}elseif ($params ["action"] == "viewDetail"){

    $row = $db->query('SELECT 
                        w.*,
                        st.name AS wIndustry,
                        wt.template AS wTemplateUsed,
                        sv.svName AS wServerName
                    FROM 
                        websiteList w
                    LEFT JOIN 
                        tb_shopType st ON w.wIndustry = st.id
                    LEFT JOIN 
                        websiteTemplate wt ON w.wTemplateUsed = wt.id
                    LEFT JOIN 
                        l4uservers sv ON w.svID = sv.svID
                    WHERE 
                        w.wID = ?;', $params["id"])->fetchArray();

    $params["wProject"] = $row["wProject"];
    $params["wLocation"] = $row["wLocation"];
    $params["wOwner"] = $row["wOwner"];
    $params["wOwnerEmail"] = $row["wOwnerEmail"];
    $params["wIndustry"] = $row["wIndustry"];
    $params["wTemplateUsed"] = $row["wTemplateUsed"];
    $params["wSystemGloriaFood"] = $row["wSystemGloriaFood"];
    $params["wSystemAmelia"] = $row["wSystemAmelia"];
    $params["wSystemVoucher"] = $row["wSystemVoucher"];
    $params["wDomain"] = $row["wDomain"];
    $params["wDomainProviderID"] = $row["wDomainProviderID"];
    $params["wPublishDate"] = $row["wPublishDate"];
    $params["wLiveStatus"] = $row["wLiveStatus"];
    $params["wCPanelUser"] = $row["wCPanelUser"];
    $params["wCPanelPass"] = $row["wCPanelPass"];
    $params["wWordpressURL"] = $row["wWordpressURL"];
    $params["wWordpressUser"] = $row["wWordpressUser"];
    $params["wWordpressPass"] = $row["wWordpressPass"];
    $params["wSMTPEmailUser"] = $row["wSMTPEmailUser"];
    $params["wSMTPEmailPass"] = $row["wSMTPEmailPass"];
    $params["wSMTPRemark"] = $row["wSMTPRemark"];
    $params["wContactEmailUser"] = $row["wContactEmailUser"];
    $params["wContactEmailPass"] = $row["wContactEmailPass"];
    $params["wContactEmailRemark"] = $row["wContactEmailRemark"];

}elseif ($params ["action"] == "loadUpdate"){

    $row = $db->query('SELECT * FROM `staffs` WHERE sID = ?;',$params ["id"])->fetchArray();
    $params["name"] = $row["sName"];
    $params["tname"] = $row["sTName"];
    $params["email"] = $row["sEmail"];
    $params["phone"] = $row["sMobile"];
    $params["password"] = $row["sPassword"];
    $params["level"] = $row["sLevel"];
    $params["religion"] = $row["rID"];
    $params["team"] = $row["teamID"];
    $params["status"] = $row["sStatus"];
    $params["address"] = $row["sAddress"];
    $params["nickname"] = $row["sNickName"];
    $params["birthday"] = $row["sDOB"];//yyyy-mm-dd
    $params["startdate"] = $row["sActiveDate"];//yyyy-mm-dd
    $params["employeenumber"] = $row["sEmpID"];
    $params["address"] = $row["sAddress"];

}elseif ($params ["action"] == "save"){
    $params["inputProject"] = !empty($_POST['inputProject']) ? $_POST['inputProject'] : "";
    $params["inputLocation"] = !empty($_POST['inputLocation']) ? $_POST['inputLocation'] : "";
    $params["inputOwner"] = !empty($_POST['inputOwner']) ? $_POST['inputOwner'] : "";
    $params["inputOwnerEmail"] = !empty($_POST['inputOwnerEmail']) ? $_POST['inputOwnerEmail'] : "";
    $params["inputDomain"] = !empty($_POST['inputDomain']) ? $_POST['inputDomain'] : "";
    $params["inputDomainProvider"] = !empty($_POST['inputDomainProvider']) ? $_POST['inputDomainProvider'] : "";
    $params["inputPublishedDate"] = !empty($_POST['inputPublishedDate']) ? $_POST['inputPublishedDate'] : "";
    $params["inputLiveStatus"] = !empty($_POST['inputLiveStatus']) ? $_POST['inputLiveStatus'] : "";
    $params["inputShopType"] = !empty($_POST['inputShopType']) ? $_POST['inputShopType'] : "";
    $params["inputTemplate"] = !empty($_POST['inputTemplate']) ? $_POST['inputTemplate'] : "";
    $params["inputServer"] = !empty($_POST['inputServer']) ? $_POST['inputServer'] : "";
    $params["inputCPanelUser"] = !empty($_POST['inputCPanelUser']) ? $_POST['inputCPanelUser'] : "";
    $params["inputCPanelPass"] = !empty($_POST['inputCPanelPass']) ? $_POST['inputCPanelPass'] : "";
    $params["inputWordPressUser"] = !empty($_POST['inputWordPressUser']) ? $_POST['inputWordPressUser'] : "";
    $params["inputWordPressPass"] = !empty($_POST['inputWordPressPass']) ? $_POST['inputWordPressPass'] : "";
    $params["inputWordpressURL"] = !empty($_POST['inputWordpressURL']) ? $_POST['inputWordpressURL'] : "";
    $params["inputSMTPUser"] = !empty($_POST['inputSMTPUser']) ? $_POST['inputSMTPUser'] : "";
    $params["inputSMTPPass"] = !empty($_POST['inputSMTPPass']) ? $_POST['inputSMTPPass'] : "";
    $params["inputSMTPRemark"] = !empty($_POST['inputSMTPRemark']) ? $_POST['inputSMTPRemark'] : "";
    $params["inputContactEmailUser"] = !empty($_POST['inputContactEmailUser']) ? $_POST['inputContactEmailUser'] : "";
    $params["inputContactEmailPass"] = !empty($_POST['inputContactEmailPass']) ? $_POST['inputContactEmailPass'] : "";
    $params["inputContactEmailRemark"] = !empty($_POST['inputContactEmailRemark']) ? $_POST['inputContactEmailRemark'] : "";
    $params["inputGloriaFood"] = isset($_POST['inputGloriaFood']) ? 1 : 0;
    $params["inputAmelia"] = isset($_POST['inputAmelia']) ? 1 : 0;
    $params["inputVoucher"] = isset($_POST['inputVoucher']) ? 1 : 0;

    $params["by"] = $_SESSION['id'];

    if($params ["formAction"]=='add'){
        $insert = $db->query('INSERT INTO `websiteList` 
                                (`wProject`,`wLocation`, `wOwner`,`wOwnerEmail`,`wDomain`,`wDomainProvidersID`, `wPublishedDate`, `wLiveStatus`,`wIndustry`,`wTemplateUsed`, `svID`, `wCPanelUser`, `wCPanelPass`, `wWordpressUser`, `wWordpressPass`, `wWordpressURL`, `wSMTPEmailUser`, `wSMTPEmailPass`, `wSMTPRemark`, `wContactEmailUser`, `wContactEmailPass`, `wContactEmailRemark`,`wSystemGloriaFood`,`wSystemAmelia`,`wSystemVoucher`, `create_by`)
                                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            ,$params["inputProject"],$params["inputLocation"],$params["inputOwner"],$params["inputOwnerEmail"],$params["inputDomain"],$params["inputDomainProvider"],$params["inputPublishedDate"],$params["inputLiveStatus"],$params["inputShopType"],$params["inputTemplate"],$params["inputServer"],$params["inputCPanelUser"],$params["inputCPanelPass"],$params["inputWordPressUser"],$params["inputWordPressPass"],$params["inputWordpressURL"],$params["inputSMTPUser"],$params["inputSMTPPass"],$params["inputSMTPRemark"],$params["inputContactEmailUser"],$params["inputContactEmailPass"],$params["inputContactEmailRemark"],$params["inputGloriaFood"],$params["inputAmelia"],$params["inputVoucher"],$myID
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();
    }elseif($params ["formAction"]=='edit'){

        $update = $db->query('UPDATE `staffs` SET 
                                `sName`= ?,`sTName`= ?, `sNickName`=?, `sDOB`= ?, `rID`= ?, `sActiveDate`= ?, `sEmpID`= ?, `sAddress`= ?,`sEmail` = ?, `sMobile` =?,`rID` = ?,`teamID` = ?, `sStatus` = ?, `sLevel` = ?, `sUpdateBy`= ?, sUpdateAt = NOW() 
                                WHERE sID = ? ;'
            ,$params["inputName"],$params["inputTname"],$params["inputNickName"],$params["inputBirthday"],$params["inputReligion"],$params["inputStartDate"],$params["inputEmployeeNumber"],$params["inputAddress"],$params["inputEmail"],$params["inputPhone"],$params["inputReligion"],$params["inputTeam"],$params["inputStatus"],$params["inputLevel"],$params["by"],$params ["editID"]
        );

        $params["affected"] = $update->affectedRows();
    }

}elseif ($params ["action"] == "changePassword"){
    if (!empty($_POST['password'])){
        $passwordHash = md5($salt . $_POST['password']);
    }else{
        $passwordHash = md5($salt . "Localeats#2023");
    }

    $params["newPassword"] = $passwordHash;

    $update = $db->query('UPDATE `staffs` SET `sPassword` = ? WHERE sID = ?;', $params["newPassword"], $myID);
    $params["affected"] = $update->affectedRows();

    $_SESSION['password'] = $_POST['password'];

}elseif ($params ["action"] == "setDelete"){

    $delete = $db->query('UPDATE `staffs` SET `sDeleteAt` = NOW(),`sStatus` = 0, `sDeleteBy` = ? WHERE sID = ?;', $_SESSION['id'], $params ["id"]);
    $params["affected"] = $delete->affectedRows();
}

echo json_encode($params);

function dateSqltoHuman($databd){//input yyyy-mm-dd
    $arr = explode("-",$databd);
    $Brithday = $arr[2]."-".$arr[1]."-".$arr[0];

    return ($Brithday);//output dd/mm/yyyy
};

function dateHumantoSql($databd){//dd/mm/yyyy
    $arr = explode("-",$databd);
    $Human = $arr[2]."-".$arr[1]."-".$arr[0];

    return ($Human);//output yyyy-mm-dd
};
