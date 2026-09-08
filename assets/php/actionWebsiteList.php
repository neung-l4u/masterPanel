<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
include_once "../../assets/php/WHMAPI.php";
include_once "../../assets/php/SoftaculousAPI.php";
include_once "../../assets/php/CpanelAPI.php";
include_once "../../assets/php/DomainUpdater.php";
include_once "../../assets/php/WordPressSetup.php";
include_once "../../assets/php/L4UConfig.php";
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
    $params["wShopEmail"] = $row["wShopEmail"];
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
    $params["wWPLocale"] = $row["wWPLocale"];
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
    $params["inputShopEmail"] = !empty($_POST['inputShopEmail']) ? $_POST['inputShopEmail'] : "";
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
    $params["inputWPLocale"] = !empty($_POST['inputWPLocale']) ? $_POST['inputWPLocale'] : "";
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
                                (`wProject`, `countryID`,`wLocation`, `wOwner`,`wOwnerEmail`,`wShopEmail`,`wDomain`,`wDomainProvidersID`, `wPublishedDate`, `wLiveStatus`,`wIndustry`,`wTemplateUsed`, `svID`, `wCPanelUser`, `wCPanelPass`, `wWordpressUser`, `wWordpressPass`, `wWordpressURL`, `wWPLocale`, `wSMTPEmailUser`, `wSMTPEmailPass`, `wSMTPRemark`, `wContactEmailUser`, `wContactEmailPass`, `wContactEmailRemark`,`wSystemGloriaFood`,`wSystemAmelia`,`wSystemVoucher`,`wSystemCloudwaitress`,`wSystemOther`, `create_by`)
                                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            ,$params["inputProject"],$params["inputCountry"],$params["inputLocation"],$params["inputOwner"],$params["inputOwnerEmail"],$params["inputShopEmail"],$params["inputDomain"],$params["inputDomainProvider"],$params["inputPublishedDate"],$params["inputLiveStatus"],$params["inputShopType"],$params["inputTemplate"],$params["inputServer"],$params["inputCPanelUser"],$params["inputCPanelPass"],$params["inputWordpressUser"],$params["inputWordpressPass"],$params["inputWordpressURL"],$params["inputWPLocale"],$params["inputSMTPUser"],$params["inputSMTPPass"],$params["inputSMTPRemark"],$params["inputContactEmailUser"],$params["inputContactEmailPass"],$params["inputContactEmailRemark"],$params["inputGloriaFood"],$params["inputAmelia"],$params["inputVoucher"],$params["inputCloudwaitress"],$params["inputOther"],$myID
        );

        $params["affected"] = $insert->affectedRows();
        $params["insertedID"] = $db->lastInsertID();

        // Create the matching cPanel account on the selected server's WHM
        $params["whm"] = createCpanelAccount($db, $params);

        // The mailboxes the form asked for, once the account can hold them
        $params["mailboxes"] = createFormMailboxes($db, $params, $params["whm"]);

        // WordPress goes in straight after the account exists
        $params["wordpress"] = installWordPress($db, $params, $params["whm"]);
    }elseif($params ["formAction"]=='edit'){

        $update = $db->query('UPDATE `websiteList` SET `wProject` = ?, `countryID` = ?, `wLocation` = ?, `wOwner` = ?, `wOwnerEmail` = ?, `wShopEmail` = ?, `wDomain` = ?, `wDomainProvidersID` = ?, `wPublishedDate` = ?, `wLiveStatus` = ?, `wIndustry` = ?, `wTemplateUsed` = ?, `svID` = ?, `wCPanelUser` = ?, `wCPanelPass` = ?, `wWordpressUser` = ?, `wWordpressPass` = ?, `wWordpressURL` = ?, `wWPLocale` = ?, `wSMTPEmailUser` = ?, `wSMTPEmailPass` = ?, `wSMTPRemark` = ?, `wContactEmailUser` = ?, `wContactEmailPass` = ?, `wContactEmailRemark` = ?, `wSystemGloriaFood` = ?, `wSystemAmelia` = ?, `wSystemVoucher` = ?, `wSystemCloudwaitress` = ?, `wSystemOther` = ?, `update_by` = ? 
                                WHERE wID = ?;'
            ,$params["inputProject"],$params["inputCountry"],$params["inputLocation"],$params["inputOwner"],$params["inputOwnerEmail"],$params["inputShopEmail"],$params["inputDomain"],$params["inputDomainProvider"],$params["inputPublishedDate"],$params["inputLiveStatus"],$params["inputShopType"],$params["inputTemplate"],$params["inputServer"],$params["inputCPanelUser"],$params["inputCPanelPass"],$params["inputWordpressUser"],$params["inputWordpressPass"],$params["inputWordpressURL"],$params["inputWPLocale"],$params["inputSMTPUser"],$params["inputSMTPPass"],$params["inputSMTPRemark"],$params["inputContactEmailUser"],$params["inputContactEmailPass"],$params["inputContactEmailRemark"],$params["inputGloriaFood"],$params["inputAmelia"],$params["inputVoucher"],$params["inputCloudwaitress"],$params["inputOther"],$myID,$params["editID"]
        );

        $params["affected"] = $update->affectedRows();

        // An address added to an existing website is created the same way one
        // typed in at the start is
        $params["mailboxes"] = createFormMailboxes($db, $params, null);
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

}elseif ($params ["action"] == "wpLogin"){

    // One-click wp-admin: a self-deleting helper is dropped into the document
    // root and its one-time URL handed back for the browser to open
    $params["url"] = "";
    $params["success"] = false;

    $row = $db->query('SELECT wID, wCPanelUser, wDomain, svID FROM websiteList WHERE wID = ? AND delete_at IS NULL;', $params["id"])->fetchArray();

    if (empty($row)){
        $params["message"] = "Website not found.";
    }elseif (empty($row["wCPanelUser"])){
        $params["message"] = "This website has no cPanel username stored.";
    }elseif (empty($row["svID"])){
        $params["message"] = "This website has no L4U Server assigned.";
    }elseif (empty($row["wDomain"])){
        $params["message"] = "This website has no domain stored.";
    }else{
        $whm = WHMAPI::fromServer($db, $row["svID"]);

        if ($whm === null){
            $params["message"] = "The assigned server has no WHM credentials stored in L4UServers.";
        }else{
            $domain = WHMAPI::normaliseDomain($row["wDomain"]);
            $setup  = new WordPressSetup($whm, $row["wCPanelUser"]);
            $link   = $setup->loginLink($domain);

            $params["success"] = $link["success"];
            $params["message"] = $link["message"];
            $params["url"]     = $link["url"];

            $logResult = [
                "username" => $row["wCPanelUser"],
                "domain"   => $domain,
                "message"  => $link["message"],
            ];
            logProvision($db, $row["wID"], $row["svID"], $link["success"] ? "success" : "failed", $logResult, null, "wordpress_login");
        }
    }

}elseif ($params ["action"] == "wpSetup"){

    // Everything the checklist asks for after Softaculous has finished:
    // language, timezone, admin email, tagline, ping services, search engine
    // visibility, and the two standing user accounts.
    //
    // The website record is expected to be saved already; whatever is on the
    // form for locale and shop email is written back first so pressing the
    // button twice keeps using the same values.

    $params["localeKey"]  = !empty($_POST['localeKey']) ? $_POST['localeKey'] : "";
    $params["shopEmail"]  = !empty($_POST['shopEmail']) ? $_POST['shopEmail'] : "";
    $params["tagline"]    = isset($_POST['tagline']) ? substr($_POST['tagline'], 0, 255) : "";
    $params["success"]    = false;
    $params["message"]    = "";
    $params["steps"]      = [];

    $row = $db->query('SELECT wID, wProject, wDomain, wCPanelUser, wShopEmail, wWPLocale, wLocation, svID FROM websiteList WHERE wID = ? AND delete_at IS NULL;', $params["id"])->fetchArray();

    // Fall back to what is stored when the form sent nothing
    $localeKey = $params["localeKey"] !== "" ? $params["localeKey"] : (!empty($row["wWPLocale"]) ? $row["wWPLocale"] : "");
    $shopEmail = $params["shopEmail"] !== "" ? $params["shopEmail"] : (!empty($row["wShopEmail"]) ? $row["wShopEmail"] : "");

    if (empty($row)){
        $params["message"] = "Website not found.";
    }elseif (empty($row["wCPanelUser"])){
        $params["message"] = "This website has no cPanel username stored.";
    }elseif (empty($row["svID"])){
        $params["message"] = "This website has no L4U Server assigned.";
    }elseif (empty($row["wDomain"])){
        $params["message"] = "This website has no domain stored.";
    }else{
        $whm = WHMAPI::fromServer($db, $row["svID"]);

        if ($whm === null){
            $params["message"] = "The assigned server has no WHM credentials stored in L4UServers.";
        }else{
            // Keep the record in step with what was just applied
            $db->query('UPDATE `websiteList` SET `wWPLocale` = ?, `wShopEmail` = ?, `update_by` = ? WHERE wID = ?;',
                $localeKey, $shopEmail, $myID, $row["wID"]);

            $domain = WHMAPI::normaliseDomain($row["wDomain"]);
            $setup  = new WordPressSetup($whm, $row["wCPanelUser"]);

            $apply = $setup->apply($domain, [
                "siteTitle"  => !empty($row["wProject"]) ? $row["wProject"] : $domain,
                // Closes off both contact form emails
                "address"    => !empty($row["wLocation"]) ? $row["wLocation"] : "",
                "localeKey"  => $localeKey,
                "tagline"    => $params["tagline"],
                "ownerEmail" => $shopEmail,
                // Licence keys and storage credentials, kept out of the repository
                "config"     => L4UConfig::all($db),
            ]);

            $params["success"] = $apply["success"];
            $params["message"] = $apply["message"];
            $params["steps"]   = $apply["steps"];

            $logResult = [
                "username" => $row["wCPanelUser"],
                "domain"   => $domain,
                "message"  => $apply["message"],
                "steps"    => $apply["steps"],
            ];
            logProvision($db, $row["wID"], $row["svID"], $apply["success"] ? "success" : "failed", $logResult, null, "wordpress_setup");
        }
    }

}elseif ($params ["action"] == "staffEmails"){

    // The seven staff mailboxes every site gets. They share one password and
    // differ only in the number, so nothing is asked for on the form.
    $params["success"] = false;
    $params["message"] = "";
    $params["results"] = [];

    $row = $db->query('SELECT wID, wDomain, wCPanelUser, svID FROM websiteList WHERE wID = ? AND delete_at IS NULL;', $params["id"])->fetchArray();

    if (empty($row)){
        $params["message"] = "Website not found.";
    }elseif (empty($row["wCPanelUser"])){
        $params["message"] = "This website has no cPanel username stored.";
    }elseif (empty($row["svID"])){
        $params["message"] = "This website has no L4U Server assigned.";
    }elseif (empty($row["wDomain"])){
        $params["message"] = "This website has no domain stored.";
    }else{
        $domain = WHMAPI::normaliseDomain($row["wDomain"]);
        $wanted = [];

        for ($i = 1; $i <= STAFF_MAILBOX_COUNT; $i++){
            $wanted[] = [
                "email" => STAFF_MAILBOX_PREFIX . $i . '@' . $domain,
                "pass"  => STAFF_MAILBOX_PASSWORD,
                "label" => "Staff " . $i,
            ];
        }

        $created = createMailboxes($db, $row["wID"], $row["svID"], $row["wCPanelUser"], $wanted);

        $params["success"] = $created["success"];
        $params["results"] = $created["results"];
        $params["message"] = $created["message"] !== ""
            ? $created["message"]
            : ($created["success"] ? "All staff mailboxes are in place." : "Some mailboxes were not created.");
    }

}elseif ($params ["action"] == "emailSettings"){

    // The host, ports and encryption a mail client needs for one mailbox
    $params["email"]    = !empty($_POST['email']) ? $_POST['email'] : "";
    $params["success"]  = false;
    $params["message"]  = "";
    $params["settings"] = [];

    $row = $db->query('SELECT wID, wCPanelUser, svID FROM websiteList WHERE wID = ? AND delete_at IS NULL;', $params["id"])->fetchArray();

    if (empty($row)){
        $params["message"] = "Website not found.";
    }elseif (empty($params["email"])){
        $params["message"] = "No mailbox supplied.";
    }elseif (empty($row["wCPanelUser"])){
        $params["message"] = "This website has no cPanel username stored.";
    }elseif (empty($row["svID"])){
        $params["message"] = "This website has no L4U Server assigned.";
    }else{
        $whmApi = WHMAPI::fromServer($db, $row["svID"]);

        if ($whmApi === null){
            $params["message"] = "The assigned server has no WHM credentials stored in L4UServers.";
        }else{
            $session = $whmApi->createUserSession($row["wCPanelUser"]);

            if (!$session["success"]){
                $params["message"] = "Could not open a cPanel session: " . $session["message"];
            }else{
                $cpanel = new CpanelAPI($session["url"], $row["wCPanelUser"]);
                $call   = $cpanel->emailClientSettings($params["email"]);

                $params["success"]  = $call["success"];
                $params["message"]  = $call["message"];
                $params["settings"] = $call["settings"];
            }
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
 * Standard mailbox size, in MB.
 */
define('MAILBOX_QUOTA_MB', 1024);

/**
 * The staff mailboxes every site gets, created on demand.
 *
 * Only the local part is fixed; the domain comes from the website being
 * worked on, and they all share the same password.
 */
define('STAFF_MAILBOX_COUNT', 7);
define('STAFF_MAILBOX_PREFIX', 'staff');
define('STAFF_MAILBOX_PASSWORD', 'Localbooking');

/**
 * Create the mailboxes named on the save form.
 *
 * Both saving a new website and editing an existing one come through here, so
 * an address added later is created the same way one typed in at the start is.
 * On a new website the cPanel account has only just been made and its name is
 * passed in; on an edit it is read back from the record.
 *
 * The SMTP and contact addresses are typed in by hand, so only the ones that
 * were filled in are created. cPanel refuses a duplicate address, so a mailbox
 * that is already there has its password brought in line instead - which is
 * also what makes saving the same form twice harmless.
 *
 * A failure here is reported but changes nothing else: the website record and
 * the cPanel account both stand on their own.
 *
 * @param object     $db
 * @param array      $params
 * @param array|null $whm Result of createCpanelAccount(), or null on an edit
 * @return array ['attempted' => bool, 'results' => array]
 */
function createFormMailboxes($db, $params, $whm){
    $result = ["attempted" => false, "results" => []];

    $isAdd = $whm !== null;
    $wID   = $isAdd ? $params["insertedID"] : $params["editID"];
    $svID  = !empty($params["inputServer"]) ? $params["inputServer"] : null;

    $wanted = [];

    if (!empty($params["inputSMTPUser"])){
        $wanted[] = ["email" => $params["inputSMTPUser"], "pass" => $params["inputSMTPPass"], "label" => "SMTP"];
    }

    if (!empty($params["inputContactEmailUser"])){
        $wanted[] = ["email" => $params["inputContactEmailUser"], "pass" => $params["inputContactEmailPass"], "label" => "Contact"];
    }

    if (empty($wanted)){
        return $result;
    }

    if ($isAdd){
        if (empty($whm["success"])){
            $result["results"][] = [
                "email"   => "",
                "success" => false,
                "message" => "cPanel account was not created - mailboxes skipped.",
            ];
            logProvision($db, $wID, $svID, "skipped", ["message" => "cPanel account was not created."], null, "email_create");
            return $result;
        }

        $username = $whm["username"];
    }else{
        // The account already exists, so its name comes from the form
        $username = !empty($params["inputCPanelUser"]) ? $params["inputCPanelUser"] : "";

        if ($username === "" || $svID === null){
            $result["results"][] = [
                "email"   => "",
                "success" => false,
                "message" => "No cPanel username or server on this website - mailboxes skipped.",
            ];
            return $result;
        }
    }

    $result["attempted"] = true;

    $created = createMailboxes($db, $wID, $svID, $username, $wanted);
    $result["results"] = $created["results"];

    return $result;
}

/**
 * Create a set of mailboxes on one cPanel account.
 *
 * One cPanel session covers the whole set, so the mailboxes are passed in
 * together rather than one call at a time.
 *
 * @param object   $db
 * @param int|null $wID
 * @param int|null $svID
 * @param string   $username cPanel account name
 * @param array    $wanted   Each ['email' => ..., 'pass' => ..., 'label' => ...]
 * @return array ['success' => bool, 'message' => string, 'results' => array]
 */
function createMailboxes($db, $wID, $svID, $username, $wanted){
    $out = ["success" => false, "message" => "", "results" => []];

    $whmApi = WHMAPI::fromServer($db, $svID);

    if ($whmApi === null){
        $out["message"] = "The selected server has no WHM credentials stored in L4UServers.";
        logProvision($db, $wID, $svID, "skipped", ["message" => $out["message"]], null, "email_create");
        return $out;
    }

    $session = $whmApi->createUserSession($username);

    if (!$session["success"]){
        $out["message"] = "Could not open a cPanel session: " . $session["message"];
        logProvision($db, $wID, $svID, "failed", ["message" => $out["message"]], $session, "email_create");
        return $out;
    }

    $cpanel  = new CpanelAPI($session["url"], $username);
    $allGood = true;

    foreach ($wanted as $box){
        if (empty($box["pass"])){
            $out["results"][] = [
                "email"   => $box["email"],
                "label"   => isset($box["label"]) ? $box["label"] : "",
                "success" => false,
                "message" => "No password provided - skipped.",
            ];
            $allGood = false;
            continue;
        }

        $add = $cpanel->addEmailAccount($box["email"], $box["pass"], MAILBOX_QUOTA_MB);

        $out["results"][] = [
            "email"   => $box["email"],
            "label"   => isset($box["label"]) ? $box["label"] : "",
            "success" => $add["success"],
            "message" => $add["message"],
        ];

        if (!$add["success"]){
            $allGood = false;
        }

        logProvision($db, $wID, $svID,
            $add["success"] ? "success" : "failed",
            ["username" => $username, "email" => $box["email"], "message" => $add["message"]],
            null,
            "email_create"
        );
    }

    $out["success"] = $allGood;

    return $out;
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
