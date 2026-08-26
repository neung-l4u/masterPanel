<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
include_once "../../assets/php/WHMAPI.php";
include_once "../../assets/php/SoftaculousAPI.php";
include_once "../../assets/php/CpanelAPI.php";
include_once "../../assets/php/DomainUpdater.php";
$myID = $_SESSION['id'];

// WordPress admin contact for every provisioned site, so resets reach the team
define('WP_ADMIN_EMAIL', 'administrator@localforyou.com');

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
                        sv.svName AS wServerName,
                        dp.name AS domainProvidersName,
                        sv.svCpanelURL AS svCpanelURL

                    FROM 
                        websiteList w
                    LEFT JOIN 
                        tb_shopType st ON w.wIndustry = st.id
                    LEFT JOIN 
                        WebsiteTemplate wt ON w.wTemplateUsed = wt.id
                    LEFT JOIN 
                        L4UServers sv ON w.svID = sv.svID
                    LEFT JOIN 
                        DomainProviders dp ON w.wDomainProvidersID = dp.id
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
    $params["wSystemCloudwaitress"] = $row["wSystemCloudwaitress"];
    $params["wSystemOther"] = $row["wSystemOther"];
    $params["wDomain"] = $row["wDomain"];
    $params["domainProvidersName"] = $row["domainProvidersName"];
    $params["wServerName"] = $row["wServerName"];
    $params["svCpanelURL"] = $row["svCpanelURL"];

    $date = $row["wPublishedDate"];
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
                        L4UServers sv ON w.svID = sv.svID
                    WHERE 
                        w.wID = ?;', $params["id"])->fetchArray();

    $params["wID"] = $row["wID"];
    $params["wProject"] = $row["wProject"];
    $params["countryID"] = $row["countryID"];
    $params["wLocation"] = $row["wLocation"];
    $params["wOwner"] = $row["wOwner"];
    $params["wOwnerEmail"] = $row["wOwnerEmail"];
    $params["wIndustry"] = $row["wIndustry"];
    $params["wTemplateUsed"] = $row["wTemplateUsed"];
    $params["wSystemGloriaFood"] = $row["wSystemGloriaFood"];
    $params["wSystemAmelia"] = $row["wSystemAmelia"];
    $params["wSystemVoucher"] = $row["wSystemVoucher"];
    $params["wSystemCloudwaitress"] = $row["wSystemCloudwaitress"];
    $params["wSystemOther"] = $row["wSystemOther"];
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
    $params["wSystemCloudwaitress"] = $row["wSystemCloudwaitress"];
    $params["wSystemOther"] = $row["wSystemOther"];


}elseif ($params ["action"] == "save"){
    $params["inputProject"] = !empty($_POST['inputProject']) ? $_POST['inputProject'] : "";
    $params["inputCountry"] = !empty($_POST['inputCountry']) ? $_POST['inputCountry'] :'';
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
    $params["inputCloudwaitress"] = !empty($_POST['inputCloudwaitress']) ? $_POST['inputCloudwaitress'] : 0;
    $params["inputOther"] = isset($_POST['inputOther']) ? substr($_POST['inputOther'], 0, 300) : '';

    $params["by"] = $_SESSION['id'];

    if($params ["formAction"]=='add'){
        $insert = $db->query('INSERT INTO `websiteList` 
                                (`wProject`, `countryID`,`wLocation`, `wOwner`,`wOwnerEmail`,`wDomain`,`wDomainProvidersID`, `wPublishedDate`, `wLiveStatus`,`wIndustry`,`wTemplateUsed`, `svID`, `wCPanelUser`, `wCPanelPass`, `wWordpressUser`, `wWordpressPass`, `wWordpressURL`, `wSMTPEmailUser`, `wSMTPEmailPass`, `wSMTPRemark`, `wContactEmailUser`, `wContactEmailPass`, `wContactEmailRemark`,`wSystemGloriaFood`,`wSystemAmelia`,`wSystemVoucher`,`wSystemCloudwaitress`,`wSystemOther`, `create_by`)
                                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            ,$params["inputProject"],$params["inputCountry"],$params["inputLocation"],$params["inputOwner"],$params["inputOwnerEmail"],$params["inputDomain"],$params["inputDomainProvider"],$params["inputPublishedDate"],$params["inputLiveStatus"],$params["inputShopType"],$params["inputTemplate"],$params["inputServer"],$params["inputCPanelUser"],$params["inputCPanelPass"],$params["inputWordpressUser"],$params["inputWordpressPass"],$params["inputWordpressURL"],$params["inputSMTPUser"],$params["inputSMTPPass"],$params["inputSMTPRemark"],$params["inputContactEmailUser"],$params["inputContactEmailPass"],$params["inputContactEmailRemark"],$params["inputGloriaFood"],$params["inputAmelia"],$params["inputVoucher"],$params["inputCloudwaitress"],$params["inputOther"],$myID
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();

        // Create the matching cPanel account on the selected server's WHM
        $params["whm"] = createCpanelAccount($db, $params);

        // WordPress goes in straight after the account exists
        $params["wordpress"] = installWordPress($db, $params, $params["whm"]);
    }elseif($params ["formAction"]=='edit'){

        $update = $db->query('UPDATE `websiteList` SET `wProject` = ?, `countryID` = ?, `wLocation` = ?, `wOwner` = ?, `wOwnerEmail` = ?, `wDomain` = ?, `wDomainProvidersID` = ?, `wPublishedDate` = ?, `wLiveStatus` = ?, `wIndustry` = ?, `wTemplateUsed` = ?, `svID` = ?, `wCPanelUser` = ?, `wCPanelPass` = ?, `wWordpressUser` = ?, `wWordpressPass` = ?, `wWordpressURL` = ?, `wSMTPEmailUser` = ?, `wSMTPEmailPass` = ?, `wSMTPRemark` = ?, `wContactEmailUser` = ?, `wContactEmailPass` = ?, `wContactEmailRemark` = ?, `wSystemGloriaFood` = ?, `wSystemAmelia` = ?, `wSystemVoucher` = ?, `wSystemCloudwaitress` = ?, `wSystemOther` = ?, `update_by` = ? 
                                WHERE wID = ?;'
            ,$params["inputProject"],$params["inputCountry"],$params["inputLocation"],$params["inputOwner"],$params["inputOwnerEmail"],$params["inputDomain"],$params["inputDomainProvider"],$params["inputPublishedDate"],$params["inputLiveStatus"],$params["inputShopType"],$params["inputTemplate"],$params["inputServer"],$params["inputCPanelUser"],$params["inputCPanelPass"],$params["inputWordpressUser"],$params["inputWordpressPass"],$params["inputWordpressURL"],$params["inputSMTPUser"],$params["inputSMTPPass"],$params["inputSMTPRemark"],$params["inputContactEmailUser"],$params["inputContactEmailPass"],$params["inputContactEmailRemark"],$params["inputGloriaFood"],$params["inputAmelia"],$params["inputVoucher"],$params["inputCloudwaitress"],$params["inputOther"],$myID,$params["editID"]
        );

        $params["affected"] = $update->affectedRows();
    }

}elseif ($params ["action"] == "updateDomain"){

    $params["newDomain"] = !empty($_POST['newDomain']) ? $_POST['newDomain'] : "";
    $params["cpanel"]    = ["success" => false, "message" => ""];
    $params["wordpress"] = ["success" => false, "message" => ""];
    $params["success"]   = false;
    $params["message"]   = "";

    $row = $db->query('SELECT wID, wDomain, wCPanelUser, svID FROM websiteList WHERE wID = ? AND delete_at IS NULL;', $params["id"])->fetchArray();

    if (empty($row)){
        $params["message"] = "Website not found.";
    }elseif (empty($params["newDomain"])){
        $params["message"] = "No new domain supplied.";
    }elseif (empty($row["wCPanelUser"])){
        $params["message"] = "This website has no cPanel username stored.";
    }elseif (empty($row["svID"])){
        $params["message"] = "This website has no L4U Server assigned.";
    }else{
        $whm = WHMAPI::fromServer($db, $row["svID"]);

        if ($whm === null){
            $params["message"] = "The assigned server has no WHM credentials stored in L4UServers.";
        }else{
            $oldDomain = WHMAPI::normaliseDomain($row["wDomain"]);
            $newDomain = WHMAPI::normaliseDomain($params["newDomain"]);
            $updater   = new DomainUpdater($db, $whm, $row["wCPanelUser"]);

            $logResult = ["username" => $row["wCPanelUser"], "domain" => $newDomain, "message" => ""];

            // WordPress first: it is still reachable on the old domain at this point
            $wp = $updater->updateWordPressUrls($oldDomain, $newDomain);
            $params["wordpress"] = ["success" => $wp["success"], "message" => $wp["message"]];

            $logResult["message"] = $wp["message"];
            logProvision($db, $row["wID"], $row["svID"], $wp["success"] ? "success" : "failed", $logResult, null, "wordpress_domain");

            if (!$wp["success"]){
                $params["message"] = "WordPress URLs were not updated, so the cPanel domain was left alone.";
            }else{
                $cp = $updater->updateCpanelDomain($newDomain);
                $params["cpanel"] = ["success" => $cp["success"], "message" => $cp["message"]];

                $logResult["message"] = $cp["message"];
                logProvision($db, $row["wID"], $row["svID"], $cp["success"] ? "success" : "failed", $logResult, $cp, "cpanel_domain");

                if ($cp["success"]){
                    $db->query('UPDATE `websiteList` SET `wDomain` = ?, `wWordpressURL` = ?, `update_by` = ? WHERE wID = ?;',
                        $newDomain, 'https://' . $newDomain . '/wp-admin/', $myID, $row["wID"]);

                    $params["success"] = true;
                    $params["message"] = "Domain updated to " . $newDomain . ".";
                }else{
                    $params["message"] = "WordPress now points at " . $newDomain . ", but the cPanel domain change failed: " . $cp["message"];
                }
            }
        }
    }

}elseif ($params ["action"] == "cpanelLogin"){

    $params["url"] = "";
    $params["success"] = false;

    // "cpanel" lands on the account home, "phpmyadmin" opens the database tool
    $params["target"] = (!empty($_POST['target']) && $_POST['target'] === 'phpmyadmin') ? 'phpmyadmin' : 'cpanel';

    $gotoUri = $params["target"] === 'phpmyadmin' ? WHMAPI::PMA_URI : '';
    $logStep = $params["target"] === 'phpmyadmin' ? 'phpmyadmin_login' : 'cpanel_login';

    $row = $db->query('SELECT wID, wCPanelUser, wDomain, svID FROM websiteList WHERE wID = ? AND delete_at IS NULL;', $params["id"])->fetchArray();

    if (empty($row)){
        $params["message"] = "Website not found.";
    }elseif (empty($row["wCPanelUser"])){
        $params["message"] = "This website has no cPanel username stored.";
    }elseif (empty($row["svID"])){
        $params["message"] = "This website has no L4U Server assigned.";
    }else{
        $whm = WHMAPI::fromServer($db, $row["svID"]);

        if ($whm === null){
            $params["message"] = "The assigned server has no WHM credentials stored in L4UServers.";
        }else{
            $call = $whm->createUserSession($row["wCPanelUser"], 'cpaneld', $gotoUri);

            $params["success"] = $call["success"];
            $params["message"] = $call["message"];
            $params["url"] = $call["url"];

            $logResult = [
                "username" => $row["wCPanelUser"],
                "domain"   => $row["wDomain"],
                "message"  => $call["message"],
            ];
            logProvision($db, $row["wID"], $row["svID"], $call["success"] ? "success" : "failed", $logResult, $call, $logStep);
        }
    }

}elseif ($params ["action"] == "setDelete"){

    $params["id"] = !empty($_POST['id']) ? $_POST['id'] : "";

    $delete = $db->query('UPDATE `websiteList` SET `delete_at` = NOW(), `delete_by` = ? WHERE wID = ?;', $myID, $params["id"]);
    $params["affected"] = $delete->affectedRows();
}

echo json_encode($params);



/**
 * Create a cPanel account on the WHM of the selected L4U server.
 *
 * The website record is already saved at this point, so a failure here never
 * rolls anything back - it only reports back to the form.
 *
 * @param object $db
 * @param array  $params Saved form values
 * @return array ['attempted' => bool, 'success' => bool, 'message' => string, 'username' => string, 'domain' => string]
 */
function createCpanelAccount($db, $params){
    $result = [
        "attempted" => false,
        "success"   => false,
        "message"   => "",
        "username"  => "",
        "domain"    => "",
    ];

    $wID  = $params["insertedID"];
    $svID = !empty($params["inputServer"]) ? $params["inputServer"] : null;

    if (empty($params["inputServer"])){
        $result["message"] = "No L4U Server selected - cPanel account was not created.";
        logProvision($db, $wID, $svID, "skipped", $result, null);
        return $result;
    }

    if (empty($params["inputDomain"])){
        $result["message"] = "No domain provided - cPanel account was not created.";
        logProvision($db, $wID, $svID, "skipped", $result, null);
        return $result;
    }

    if (empty($params["inputCPanelPass"])){
        $result["message"] = "No cPanel password provided - cPanel account was not created.";
        logProvision($db, $wID, $svID, "skipped", $result, null);
        return $result;
    }

    $whm = WHMAPI::fromServer($db, $params["inputServer"]);

    if ($whm === null){
        $result["message"] = "The selected server has no WHM credentials stored in L4UServers.";
        logProvision($db, $wID, $svID, "skipped", $result, null);
        return $result;
    }

    $domain   = WHMAPI::normaliseDomain($params["inputDomain"]);
    $username = !empty($params["inputCPanelUser"])
        ? WHMAPI::usernameFromDomain($params["inputCPanelUser"])
        : WHMAPI::usernameFromDomain($domain);

    $result["attempted"] = true;
    $result["username"]  = $username;
    $result["domain"]    = $domain;

    $call = $whm->createAccount([
        "username"     => $username,
        "domain"       => $domain,
        "password"     => $params["inputCPanelPass"],
        "contactemail" => $params["inputOwnerEmail"],
        "quota"        => 1000, // MB
    ]);

    $result["success"] = $call["success"];
    $result["message"] = $call["message"];

    if ($call["success"]){
        // Keep the record in sync with the username WHM actually accepted
        $db->query('UPDATE `websiteList` SET `wCPanelUser` = ? WHERE wID = ?;', $username, $wID);
    }

    logProvision($db, $wID, $svID, $call["success"] ? "success" : "failed", $result, $call);

    return $result;
}

/**
 * Install WordPress into the cPanel account that was just created.
 *
 * WordPress admin credentials reuse the cPanel username, so the account and
 * the site stay in step. The contact address is a fixed L4U mailbox rather
 * than the customer's, so password resets stay with the team.
 *
 * @param object $db
 * @param array  $params Saved form values
 * @param array  $whm    Result of createCpanelAccount()
 * @return array ['attempted' => bool, 'success' => bool, 'message' => string, 'siteUrl' => string, 'adminUrl' => string]
 */
function installWordPress($db, $params, $whm){
    $result = [
        "attempted" => false,
        "success"   => false,
        "message"   => "",
        "siteUrl"   => "",
        "adminUrl"  => "",
    ];

    $wID  = $params["insertedID"];
    $svID = !empty($params["inputServer"]) ? $params["inputServer"] : null;

    $logResult = ["username" => "", "domain" => "", "message" => ""];

    if (empty($whm) || empty($whm["success"])){
        $result["message"] = "cPanel account was not created - WordPress install skipped.";
        $logResult["message"] = $result["message"];
        logProvision($db, $wID, $svID, "skipped", $logResult, null, "wordpress_install");
        return $result;
    }

    if (empty($params["inputWordpressPass"])){
        $result["message"] = "No WordPress password provided - WordPress install skipped.";
        $logResult["username"] = $whm["username"];
        $logResult["domain"]   = $whm["domain"];
        $logResult["message"]  = $result["message"];
        logProvision($db, $wID, $svID, "skipped", $logResult, null, "wordpress_install");
        return $result;
    }

    $whmApi = WHMAPI::fromServer($db, $params["inputServer"]);

    if ($whmApi === null){
        $result["message"] = "The selected server has no WHM credentials stored in L4UServers.";
        $logResult["message"] = $result["message"];
        logProvision($db, $wID, $svID, "skipped", $logResult, null, "wordpress_install");
        return $result;
    }

    $username = $whm["username"];
    $domain   = $whm["domain"];

    $result["attempted"]   = true;
    $logResult["username"] = $username;
    $logResult["domain"]   = $domain;

    // Softaculous runs inside the account, so a cPanel session is needed first
    $session = $whmApi->createUserSession($username);

    if (!$session["success"]){
        $result["message"] = "Could not open a cPanel session: " . $session["message"];
        $logResult["message"] = $result["message"];
        logProvision($db, $wID, $svID, "failed", $logResult, $session, "wordpress_install");
        return $result;
    }

    $soft = new SoftaculousAPI($session["url"]);

    $install = $soft->installWordPress([
        "domain"     => $domain,
        "directory"  => "",
        "siteName"   => !empty($params["inputProject"]) ? $params["inputProject"] : $domain,
        "adminUser"  => $username,
        "adminPass"  => $params["inputWordpressPass"],
        "adminEmail" => WP_ADMIN_EMAIL,
        "dbName"     => SoftaculousAPI::dbNameFor($username),
        "protocol"   => "https://",
    ]);

    $result["success"]  = $install["success"];
    $result["message"]  = $install["message"];
    $result["siteUrl"]  = $install["siteUrl"];
    $result["adminUrl"] = $install["adminUrl"];

    if ($install["success"]){
        // Record the credentials that were actually used
        $db->query('UPDATE `websiteList` SET `wWordpressUser` = ?, `wWordpressURL` = ? WHERE wID = ?;',
            $username, $install["adminUrl"], $wID);
    }

    $logResult["message"] = $install["message"];
    $logCall = [
        "httpCode" => null,
        "data"     => $install["raw"],
    ];
    logProvision($db, $wID, $svID, $install["success"] ? "success" : "failed", $logResult, $logCall, "wordpress_install");

    return $result;
}

/**
 * Write one row to the provisioning audit log.
 *
 * @param object     $db
 * @param int|null   $wID
 * @param int|null   $svID
 * @param string     $status success|failed|skipped
 * @param array      $result Outcome as reported back to the form
 * @param array|null $call   Raw WHMAPI::call() result, when a call was made
 * @param string     $step
 * @return void
 */
function logProvision($db, $wID, $svID, $status, $result, $call, $step = "cpanel_createacct"){
    global $myID;

    $httpCode = ($call !== null && isset($call["httpCode"])) ? $call["httpCode"] : null;
    $response = ($call !== null && !empty($call["data"])) ? json_encode(scrubSecrets($call["data"])) : null;

    $db->query('INSERT INTO `websiteProvisionLogs`
                    (`wID`, `svID`, `step`, `status`, `cpanelUser`, `domain`, `httpCode`, `message`, `response`, `create_by`)
                    VALUES (?,?,?,?,?,?,?,?,?,?)',
        $wID,
        $svID,
        $step,
        $status,
        $result["username"],
        $result["domain"],
        $httpCode,
        $result["message"],
        $response,
        $myID
    );
}

/**
 * Remove password-like values from a WHM response before it is stored.
 * @param mixed $data
 * @return mixed
 */
function scrubSecrets($data){
    if (!is_array($data)){
        return $data;
    }

    foreach ($data as $key => $value){
        if (is_array($value)){
            $data[$key] = scrubSecrets($value);
        }elseif (preg_match('/pass|token|secret/i', (string)$key)){
            $data[$key] = '***';
        }
    }

    return $data;
}

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
