<?php
global $db;
session_start();
include '../assets/db/db.php';
include "../assets/db/initDB.php";
include "../assets/php/share_function.php";

$params = array();
$data = array();

$data['projectID'] = !empty($_POST['projectID']) ? $_POST['projectID'] : null;
$data['shopType'] = !empty($_POST['shopType']) ? $_POST['shopType'] : null;
$data['mode'] = !empty($_POST['mode']) ? $_POST['mode'] : "";
$data['businessName'] = !empty($_POST['businessName']) ? trim($_POST['businessName']) : "No name";
$data['businessEmail'] = !empty($_POST['businessEmail']) ? trim($_POST['businessEmail']) : null;
$data['businessPhone'] = !empty($_POST['businessPhone']) ? trim($_POST['businessPhone']) : null;
$data['businessAddress'] = !empty($_POST['businessAddress']) ? trim($_POST['businessAddress']) : null;
$data['openingHours'] = !empty($_POST['openingHours']) ? trim($_POST['openingHours']) : null;
$data['logo'] = !empty($_POST['logo']) ? $_POST['logo'] : getRandomPic($data['shopType'],'logo');
$data['colorTheme1'] = !empty($_POST['colorTheme1']) ? $_POST['colorTheme1'] : null;
$data['colorTheme2'] = !empty($_POST['colorTheme2']) ? $_POST['colorTheme2'] : null;
$data['colorTheme3'] = !empty($_POST['colorTheme3']) ? $_POST['colorTheme3'] : null;
$data['domainHave'] = ($_POST['domainHave']=='true') ?  1 : 0;
$data['domainProvider'] = !empty($_POST['domainProvider']) ? $_POST['domainProvider'] : null;
$data['domainUser'] = !empty($_POST['domainUser']) ? trim($_POST['domainUser']) : null;
$data['domainPass'] = !empty($_POST['domainPass']) ? trim($_POST['domainPass']) : null;
$data['hostingHave'] = ($_POST['hostingHave']=='true') ?  1 : 0;
$data['hostingUser'] = !empty($_POST['hostingUser']) ? trim($_POST['hostingUser']) : null;
$data['hostingPass'] = !empty($_POST['hostingPass']) ? trim($_POST['hostingPass']) : null;
$data['gloriaHave'] = ($_POST['gloriaHave']=='true') ? 1 : 0;
$data['orderURL'] = !empty($_POST['orderURL']) ? trim($_POST['orderURL']) : null;
$data['tableURL'] = !empty($_POST['tableURL']) ? trim($_POST['tableURL']) : null;
$data['orderOther'] = ($_POST['orderOther']=='true') ?  1 : 0;
$data['resOtherSystem'] = !empty($_POST['resOtherSystem']) ? trim($_POST['resOtherSystem']) : null;
$data['amelia'] = ($_POST['amelia']=='true') ?  1 : 0;
$data['voucher'] = ($_POST['voucher']=='true') ?  1 : 0;
$data['bookOther'] = ($_POST['bookOther']=='true') ?  1 : 0;
$data['masOtherSystem'] = !empty($_POST['masOtherSystem']) ? trim($_POST['masOtherSystem']) : null;
$data['needEmail'] = ($_POST['needEmail']=='true') ?  1 : 0;
$data['facebookURL'] = !empty($_POST['facebookURL']) ? trim($_POST['facebookURL']) : null;
$data['instagramURL'] = !empty($_POST['instagramURL']) ? trim($_POST['instagramURL']) : null;
$data['youtubeURL'] = !empty($_POST['youtubeURL']) ? trim($_POST['youtubeURL']) : null;
$data['tiktokURL'] = !empty($_POST['tiktokURL']) ? trim($_POST['tiktokURL']) : null;
$data['projectOwner'] = !empty($_POST['projectOwner']) ? $_POST['projectOwner'] : "no PO";
$data['TemplateSelect'] = !empty($_POST['TemplateSelect']) ? $_POST['TemplateSelect'] : "0";
$data['token'] = !empty($_POST['token']) ? $_POST['token'] : "no token";



if ($data['mode'] ==  "save") {
    $insert = $db->query('INSERT INTO `tb_businessdetail` (projectID, bdName, bdEmail, bdPhone, bdAddress, bdOpening, bdLogo, colorTheme1, colorTheme2, colorTheme3, bdDomainHave, bdDomainType, bdDomainUser, bdDomainPass, bdHostingHave, bdHostingUser, bdHostingPass, bdGloriaHave, bdOrderURL, bdTableURL, bdOrderOther, bdResOtherSystem, bdAmelia, bdVoucher, bdBookOther, bdMasOtherSystem, needEmail, bdFacebookURL, bdInstagramURL, bdYoutubeURL, bdTiktokURL, bdPO) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
        $data['projectID'],
        $data['businessName'],
        $data['businessEmail'],
        $data['businessPhone'],
        $data['businessAddress'],
        $data['openingHours'],
        $data['logo'],
        $data['colorTheme1'],
        $data['colorTheme2'],
        $data['colorTheme3'],
        $data['domainHave'],
        $data['domainProvider'],
        $data['domainUser'],
        $data['domainPass'],
        $data['hostingHave'],
        $data['hostingUser'],
        $data['hostingPass'],
        $data['gloriaHave'],
        $data['orderURL'],
        $data['tableURL'],
        $data['orderOther'],
        $data['resOtherSystem'],
        $data['amelia'],
        $data['voucher'],
        $data['bookOther'],
        $data['masOtherSystem'],
        $data['needEmail'],
        $data['facebookURL'],
        $data['instagramURL'],
        $data['youtubeURL'],
        $data['tiktokURL'],
        $data['projectOwner']
    );
    $params[ 'result'] = "complete";
    $params['data'] = $data;

}else if ($data['mode'] == "loadArray") {
    $select = $db->query('SELECT * FROM `tb_project` WHERE `projectID` = ?;', $data['projectID'])->fetchAll();

    $data = array();
    $i = 0;
    foreach ($select as $row){
        $data[$i] = $row;
        $i++;
    }

    $params['result'] = "found ".count($select)." data";
    $params['data'] = $data;
}
echo json_encode($params);