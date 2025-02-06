
<?php
global $db;
session_start();
include '../assets/db/db.php';
include "../assets/db/initDB.php";
include "../assets/php/share_function.php";

$params = array();
$data = array();
$myID = $_SESSION['id'];

$data['projectID'] = !empty($_POST['projectID']) ? $_POST['projectID'] : null;
$data['shopType'] = !empty($_POST['shopType']) ? $_POST['shopType'] : null; //restaurant, massage
$data['mode'] = !empty($_POST['mode']) ? $_POST['mode'] : "";
$data['projectName'] = !empty($_POST['projectName']) ? trim($_POST['projectName']) : "No name";

$data['email'] = !empty($_POST['email']) ? trim($_POST['email']) : null;
$data['phone'] = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
$data['address'] = !empty($_POST['address']) ? trim($_POST['address']) : null;
$data['logo'] = !empty($_POST['logo']) ? $_POST['logo'] : getRandomPic($data['shopType'],'logo');
$data['colorTheme1'] = !empty($_POST['colorTheme1']) ? $_POST['colorTheme1'] : null;
$data['colorTheme2'] = !empty($_POST['colorTheme2']) ? $_POST['colorTheme2'] : null;
$data['colorTheme3'] = !empty($_POST['colorTheme3']) ? $_POST['colorTheme3'] : null;
$data['domainName'] = !empty($_POST['domainName']) ? trim($_POST['domainName']) : null;
$data['hostingName'] = !empty($_POST['hostingName']) ? trim($_POST['hostingName']) : null;
$data['domainHave'] = ($_POST['domainHave']=='true') ?  1 : 0;
$data['domainProvidersID'] = !empty($_POST['domainProvidersID']) ? $_POST['domainProvidersID'] : null;
$data['domainUser'] = !empty($_POST['domainUser']) ? trim($_POST['domainUser']) : null;
$data['domainPass'] = !empty($_POST['domainPass']) ? trim($_POST['domainPass']) : null;
$data['hostingHave'] = ($_POST['hostingHave']=='true') ?  1 : 0;
$data['hostingProvidersID'] = ($_POST['hostingProvidersID']=='true') ?  1 : 0;
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

$opening = array();
$opening['sunOpen'] = !empty($_POST['sunOpen']) ? $_POST['sunOpen'] : null;
$opening['monOpen'] = !empty($_POST['monOpen']) ? $_POST['monOpen'] : null;
$opening['tueOpen'] = !empty($_POST['tueOpen']) ? $_POST['tueOpen'] : null;
$opening['wedOpen'] = !empty($_POST['wedOpen']) ? $_POST['wedOpen'] : null;
$opening['thuOpen'] = !empty($_POST['thuOpen']) ? $_POST['thuOpen'] : null;
$opening['friOpen'] = !empty($_POST['friOpen']) ? $_POST['friOpen'] : null;
$opening['satOpen'] = !empty($_POST['satOpen']) ? $_POST['satOpen'] : null;

$delivery = array();
$delivery['sunDeli'] = !empty($_POST['sunDeli']) ? $_POST['sunDeli'] : null;
$delivery['monDeli'] = !empty($_POST['monDeli']) ? $_POST['monDeli'] : null;
$delivery['tueDeli'] = !empty($_POST['tueDeli']) ? $_POST['tueDeli'] : null;
$delivery['wedDeli'] = !empty($_POST['wedDeli']) ? $_POST['wedDeli'] : null;
$delivery['thuDeli'] = !empty($_POST['thuDeli']) ? $_POST['thuDeli'] : null;
$delivery['friDeli'] = !empty($_POST['friDeli']) ? $_POST['friDeli'] : null;
$delivery['satDeli'] = !empty($_POST['satDeli']) ? $_POST['satDeli'] : null;

$data['openingHours'] = implode('__',$opening);
$data['delivery'] = implode('__',$delivery);

if ($data['mode'] ==  "save") {
    $select = $db->query('UPDATE `tb_project` SET `saveFlag`=?,`email`=?,`phone`=?,`address`=?,`openingHours`=?,`pickupAndDelivery`=?,`logo`=?,`colorTheme1`=?,`colorTheme2`=?,`colorTheme3`=?, `domainName`=?,`domainHave`=?,`domainProvidersID`=?,`domainUser`=?,`domainPass`=?,`hostingName`=?,`hostingHave`=?,`hostingProvidersID`=?,`hostingUser`=?,`hostingPass`=?,`gloriaHave`=?,`orderURL`=?,`tableURL`=?,`orderOther`=?,`resOtherSystem`=?,`amelia`=?,`voucher`=?,`bookOther`=?,`masOtherSystem`=?,`needEmail`=?,`facebookURL`=?,`instagramURL`=?,`youtubeURL`=?,`tiktokURL`=?,`updateBy`=? WHERE `projectID`=?',
        1,
        $data['email'],
        $data['phone'],
        $data['address'],
        $data['openingHours'],
        $data['delivery'],
        $data['logo'],
        $data['colorTheme1'],
        $data['colorTheme2'],
        $data['colorTheme3'],
        $data['domainName'],
        $data['domainHave'],
        $data['domainProvidersID'],
        $data['domainUser'],
        $data['domainPass'],
        $data['hostingName'],
        $data['hostingHave'],
        $data['hostingProvidersID'],
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
        $myID,
        $data['projectID']
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