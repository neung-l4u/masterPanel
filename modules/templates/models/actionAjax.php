
<?php
global $db;
session_start();
include '../assets/db/db.php';
include "../assets/db/initDB.php";

$projectID = $_POST['projectID'];

$mode = $_POST['mode'];
$businessName = $_POST['businessName'];
$businessEmail = $_POST['businessEmail'];
$businessPhone = $_POST['businessPhone'];
$businessAddress = $_POST['businessAddress'];
$openingHours = $_POST['openingHours'];
$colorInput = $_POST['colorInput'];
$domainHave = $_POST['domainHave'];
$domainType = $_POST['domainType'];
$domainUser = $_POST['domainUser'];
$domainPass = $_POST['domainPass'];
$hostingHave = $_POST['hostingHave'];
$hostingUser = $_POST['hostingUser'];
$hostingPass = $_POST['hostingPass'];
//$resSystem = $_POST['resSystem'];
$gloriaHave = $_POST['gloriaFood'];
$orderURL = $_POST['orderURL'];
$tableURL = $_POST['tableURL'];
$orderOther = $_POST['orderOther'];
$resOtherSystem = $_POST['resOtherSystem'];
//$masSystem = $_POST['masSystem'];
$amelia = $_POST['amelia'];
$voucher = $_POST['voucher'];
$bookOther = $_POST['bookOther'];
$masOtherSystem = $_POST['masOtherSystem'];
$facebookURL = $_POST['facebookURL'];
$instagramURL = $_POST['instagramURL'];
$youtubeURL = $_POST['youtubeURL'];
$tiktokURL = $_POST['tiktokURL'];
$projectOwner = $_POST['projectOwner'];


if ($mode ==  "save") {
    $insert = $db->query('INSERT INTO `tb_businessdetail`
        (`bdName`,
        `bdEmail`,
        `bdPhone`,
        `bdAddress`,
        `bdOpening`,
        `bdColorInput`,
        `bdDomainHave`,
        `bdDomainType`,
        `bdDomainUser`,
        `bdDomainPass`,
        `bdHostingHave`,
        `bdHostingUser`,
        `bdHostingPass`,
        `bdGloriaHave`,
        `bdOrderURL`,
        `bdTableURL`,
        `bdOrderOther`,
        `bdResOtherSystem`,
        `bdAmelia`,
        `bdVoucher`,
        `bdBookOther`,
        `bdMasOtherSystem`,
        `bdFacebookURL`,
        `bdInstagramURL`,
        `bdYoutubeURL`,
        `bdTiktokURL`,
        `bdPO`)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);'
        ,$businessName,
        $businessEmail,
        $businessPhone,
        $businessAddress,
        $openingHours,
        $colorInput,
        $domainHave,
        $domainType,
        $domainUser,
        $domainPass,
        $hostingHave,
        $hostingUser,
        $hostingPass,
        $gloriaHave,
        $orderURL,
        $tableURL,
        $orderOther,
        $resOtherSystem,
        $amelia,
        $voucher,
        $bookOther,
        $masOtherSystem,
        $facebookURL,
        $instagramURL,
        $youtubeURL,
        $tiktokURL,
        $projectOwner	
    );

    $params[ 'result'] = "complete";

}else if ($mode == "loadArray") {
    $select = $db->query('SELECT * FROM `tb_project` WHERE `projectID` = ?;', $projectID)->fetchAll();

    $data = array();
    $i = 0;
    foreach ($select as $row){
        $data[$i] = $row;
        $i++;
    }

    $params['result'] = "found ".count($select)." data";
    $params['data'] = $data;
}

$params['name'] = 'Mark ='
.$businessName." "
.$businessEmail." "
.$businessPhone." "
.$businessAddress." "
.$openingHours." "
.$colorInput." "
.$domainHave." "
.$domainType." "
.$domainUser." "
.$domainPass." "
.$hostingHave." "
.$hostingUser." "
.$hostingPass." "
.$gloriaHave." "
.$orderURL." "
.$tableURL." "
.$orderOther." "
.$resOtherSystem." "
.$amelia." "
.$voucher." "
.$bookOther." "
.$masOtherSystem." "
.$facebookURL." "
.$instagramURL." "
.$youtubeURL." "
.$tiktokURL." "
.$projectOwner." "
;

echo json_encode($params); 