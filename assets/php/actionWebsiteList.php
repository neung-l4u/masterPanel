<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
$myID = $_SESSION['id'];

$params["action"] = !empty($_POST['act']) ? $_POST['act'] : "";
$params["id"] = !empty($_POST['id']) ? $_POST['id'] : "";
$params["editID"] = !empty($_POST['editID']) ? $_POST['editID'] : "";
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

    $date = $row["wPublishedDate"];
    function changeDateFormat($date) {
        $date = date("d-m-Y", strtotime($date));
        return $date;
    }
    $date = changeDateFormat($date);
    $params["wPublishedDate"] = $date;
    
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

    $row = $db->query('SELECT 
                        w.*,
                        sv.svName AS wServerName
                    FROM 
                        websiteList w
                    LEFT JOIN 
                        l4uservers sv ON w.svID = sv.svID
                    WHERE 
                        w.wID = ?;', $params["id"])->fetchArray();

    $params["wID"] = $row["wID"];
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
    $params["wDomainProvidersID"] = $row["wDomainProvidersID"];
    $params["wPublishedDate"] = $row["wPublishedDate"];
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
    $params["wServerID"] = $row["svID"];
    $params["inputGloriaFood"] = $row["wSystemGloriaFood"] == 1 ? 1 : 0;
    $params["inputAmelia"] = $row["wSystemAmelia"] == 1 ? 1 : 0;
    $params["inputVoucher"] = $row["wSystemVoucher"] == 1 ? 1 : 0;


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
    $params["inputWordpressUser"] = !empty($_POST['inputWordpressUser']) ? $_POST['inputWordpressUser'] : "";
    $params["inputWordpressPass"] = !empty($_POST['inputWordpressPass']) ? $_POST['inputWordpressPass'] : "";
    $params["inputWordpressURL"] = !empty($_POST['inputWordpressURL']) ? $_POST['inputWordpressURL'] : "";
    $params["inputSMTPUser"] = !empty($_POST['inputSMTPUser']) ? $_POST['inputSMTPUser'] : "";
    $params["inputSMTPPass"] = !empty($_POST['inputSMTPPass']) ? $_POST['inputSMTPPass'] : "";
    $params["inputSMTPRemark"] = !empty($_POST['inputSMTPRemark']) ? $_POST['inputSMTPRemark'] : "";
    $params["inputContactEmailUser"] = !empty($_POST['inputContactEmailUser']) ? $_POST['inputContactEmailUser'] : "";
    $params["inputContactEmailPass"] = !empty($_POST['inputContactEmailPass']) ? $_POST['inputContactEmailPass'] : "";
    $params["inputContactEmailRemark"] = !empty($_POST['inputContactEmailRemark']) ? $_POST['inputContactEmailRemark'] : "";
    $params["inputGloriaFood"] = !empty($_POST['inputGloriaFood']) ? $_POST['inputGloriaFood'] : 0; 
    $params["inputAmelia"] = !empty($_POST['inputAmelia']) ? $_POST['inputAmelia'] : 0;
    $params["inputVoucher"] = !empty($_POST['inputVoucher']) ? $_POST['inputVoucher'] : 0;

    $params["by"] = $_SESSION['id'];

    if($params ["formAction"]=='add'){
        $insert = $db->query('INSERT INTO `websiteList` 
                                (`wProject`,`wLocation`, `wOwner`,`wOwnerEmail`,`wDomain`,`wDomainProvidersID`, `wPublishedDate`, `wLiveStatus`,`wIndustry`,`wTemplateUsed`, `svID`, `wCPanelUser`, `wCPanelPass`, `wWordpressUser`, `wWordpressPass`, `wWordpressURL`, `wSMTPEmailUser`, `wSMTPEmailPass`, `wSMTPRemark`, `wContactEmailUser`, `wContactEmailPass`, `wContactEmailRemark`,`wSystemGloriaFood`,`wSystemAmelia`,`wSystemVoucher`, `create_by`)
                                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            ,$params["inputProject"],$params["inputLocation"],$params["inputOwner"],$params["inputOwnerEmail"],$params["inputDomain"],$params["inputDomainProvider"],$params["inputPublishedDate"],$params["inputLiveStatus"],$params["inputShopType"],$params["inputTemplate"],$params["inputServer"],$params["inputCPanelUser"],$params["inputCPanelPass"],$params["inputWordpressUser"],$params["inputWordpressPass"],$params["inputWordpressURL"],$params["inputSMTPUser"],$params["inputSMTPPass"],$params["inputSMTPRemark"],$params["inputContactEmailUser"],$params["inputContactEmailPass"],$params["inputContactEmailRemark"],$params["inputGloriaFood"],$params["inputAmelia"],$params["inputVoucher"],$myID
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();
    }elseif($params ["formAction"]=='edit'){

        $update = $db->query('UPDATE `websiteList` SET `wProject` = ?, `wLocation` = ?, `wOwner` = ?, `wOwnerEmail` = ?, `wDomain` = ?, `wDomainProvidersID` = ?, `wPublishedDate` = ?, `wLiveStatus` = ?, `wIndustry` = ?, `wTemplateUsed` = ?, `svID` = ?, `wCPanelUser` = ?, `wCPanelPass` = ?, `wWordpressUser` = ?, `wWordpressPass` = ?, `wWordpressURL` = ?, `wSMTPEmailUser` = ?, `wSMTPEmailPass` = ?, `wSMTPRemark` = ?, `wContactEmailUser` = ?, `wContactEmailPass` = ?, `wContactEmailRemark` = ?, `wSystemGloriaFood` = ?, `wSystemAmelia` = ?, `wSystemVoucher` = ?,`update_by` = ? 
                                WHERE wID = ?;'
            ,$params["inputProject"],$params["inputLocation"],$params["inputOwner"],$params["inputOwnerEmail"],$params["inputDomain"],$params["inputDomainProvider"],$params["inputPublishedDate"],$params["inputLiveStatus"],$params["inputShopType"],$params["inputTemplate"],$params["inputServer"],$params["inputCPanelUser"],$params["inputCPanelPass"],$params["inputWordpressUser"],$params["inputWordpressPass"],$params["inputWordpressURL"],$params["inputSMTPUser"],$params["inputSMTPPass"],$params["inputSMTPRemark"],$params["inputContactEmailUser"],$params["inputContactEmailPass"],$params["inputContactEmailRemark"],$params["inputGloriaFood"],$params["inputAmelia"],$params["inputVoucher"], $myID,$params ["editID"] , 
        );

        $params["affected"] = $update->affectedRows();
    }

}elseif ($params ["action"] == "setDelete"){

    $params["id"] = !empty($_POST['id']) ? $_POST['id'] : "";

    $delete = $db->query('UPDATE `websiteList` SET `delete_at` = NOW(), `delete_by` = ? WHERE wID = ?;', $myID, $params["id"]);
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
