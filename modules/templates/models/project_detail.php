<?php
session_start();
global $db;
include '../assets/db/db.php';
include "../assets/db/initDB.php";
include "../assets/php/share_function.php";
include "../assets/php/brief.php";

$params = array();
$data = array();
$myID = $_SESSION['id'];

$data['projectID'] = !empty($_POST['projectID']) ? $_POST['projectID'] : null;
$data['projectCode'] = !empty($_POST['projectCode']) ? $_POST['projectCode'] : null;
$data['shopType'] = !empty($_POST['shopType']) ? $_POST['shopType'] : null; //restaurant, massage
$data['mode'] = !empty($_POST['mode']) ? $_POST['mode'] : "";
$data['projectName'] = !empty($_POST['projectName']) ? trim($_POST['projectName']) : "No name";

$data['email'] = !empty($_POST['email']) ? trim($_POST['email']) : null;
$data['phone'] = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
$data['address'] = !empty($_POST['address']) ? trim($_POST['address']) : null;
// Sample logos exist only for Restaurant/Massage; getRandomPic() fatals on any other shop type.
$data['logo'] = !empty($_POST['logo']) ? $_POST['logo']
    : (in_array(strtolower((string)$data['shopType']), ['restaurant', 'massage'], true) ? getRandomPic($data['shopType'],'logo') : null);
$data['colorTheme1'] = !empty($_POST['colorTheme1']) ? $_POST['colorTheme1'] : null;
$data['colorTheme2'] = !empty($_POST['colorTheme2']) ? $_POST['colorTheme2'] : null;
$data['colorTheme3'] = !empty($_POST['colorTheme3']) ? $_POST['colorTheme3'] : null;
$data['domainName'] = !empty($_POST['domainName']) ? trim($_POST['domainName']) : null;
$data['hostingName'] = !empty($_POST['hostingName']) ? trim($_POST['hostingName']) : null;
$data['domainHave'] = ($_POST['domainHave']=='true') ?  1 : 0;
$data['domainProvidersID'] = !empty($_POST['domainProvidersID']) ? $_POST['domainProvidersID'] : "0";
$data['domainUser'] = !empty($_POST['domainUser']) ? trim($_POST['domainUser']) : null;
$data['domainPass'] = !empty($_POST['domainPass']) ? trim($_POST['domainPass']) : null;
$data['hostingHave'] = (!empty($_POST['hostingHave']) && ($_POST['hostingHave'] == 1 || $_POST['hostingHave'] == 'true')) ?  1 : 0;
$data['hostingProvidersID'] = !empty($_POST['hostingProvidersID']) ? $_POST['hostingProvidersID'] : "0";
$data['hostingUser'] = !empty($_POST['hostingUser']) ? trim($_POST['hostingUser']) : null;
$data['hostingPass'] = !empty($_POST['hostingPass']) ? trim($_POST['hostingPass']) : null;
$data['gloriaHave'] = (!empty($_POST['gloriaHave']) && ($_POST['gloriaHave'] == 1 || $_POST['gloriaHave'] == 'true')) ? 1 : 0;
$data['orderURL'] = !empty($_POST['orderURL']) ? trim($_POST['orderURL']) : null;
$data['tableURL'] = !empty($_POST['tableURL']) ? trim($_POST['tableURL']) : null;
$data['orderOther'] = (!empty($_POST['orderOther']) && ($_POST['orderOther'] == 1 || $_POST['orderOther'] == 'true')) ?  1 : 0;
$data['resOtherSystem'] = !empty($_POST['resOtherSystem']) ? trim($_POST['resOtherSystem']) : null;
$data['amelia'] = (!empty($_POST['amelia']) && ($_POST['amelia'] == 1 || $_POST['amelia'] == 'true')) ?  1 : 0;
$data['voucher'] = (!empty($_POST['voucher']) && ($_POST['voucher'] == 1 || $_POST['voucher'] == 'true')) ?  1 : 0;
$data['bookOther'] = (!empty($_POST['bookOther']) && ($_POST['bookOther'] == 1 || $_POST['bookOther'] == 'true')) ?  1 : 0;
$data['masOtherSystem'] = !empty($_POST['masOtherSystem']) ? trim($_POST['masOtherSystem']) : null;
$data['needEmail'] = (!empty($_POST['needEmail']) && ($_POST['needEmail'] == 1 || $_POST['needEmail'] == 'true')) ?  1 : 0;
$data['facebookURL'] = !empty($_POST['facebookURL']) ? trim($_POST['facebookURL']) : null;
$data['instagramURL'] = !empty($_POST['instagramURL']) ? trim($_POST['instagramURL']) : null;
$data['youtubeURL'] = !empty($_POST['youtubeURL']) ? trim($_POST['youtubeURL']) : null;
$data['tiktokURL'] = !empty($_POST['tiktokURL']) ? trim($_POST['tiktokURL']) : null;
$data['projectOwner'] = !empty($_POST['projectOwner']) ? $_POST['projectOwner'] : "no PO";
$data['TemplateSelect'] = !empty($_POST['TemplateSelect']) ? $_POST['TemplateSelect'] : "0";
$data['token'] = !empty($_POST['token']) ? $_POST['token'] : "no token";

if (!empty($_POST['dayOpenChk']) && $_POST['dayOpenChk'] === 'true') {
    $opening = [
        'sunOpen' => !empty($_POST['sunOpen']) ? $_POST['sunOpen'] : null,
        'monOpen' => !empty($_POST['monOpen']) ? $_POST['monOpen'] : null,
        'tueOpen' => !empty($_POST['tueOpen']) ? $_POST['tueOpen'] : null,
        'wedOpen' => !empty($_POST['wedOpen']) ? $_POST['wedOpen'] : null,
        'thuOpen' => !empty($_POST['thuOpen']) ? $_POST['thuOpen'] : null,
        'friOpen' => !empty($_POST['friOpen']) ? $_POST['friOpen'] : null,
        'satOpen' => !empty($_POST['satOpen']) ? $_POST['satOpen'] : null
    ];
    $data['openingHours'] = implode('__',$opening);
    $data['openingCustom'] = "0";
}

if (!empty($_POST['customOpenChk']) && $_POST['customOpenChk'] === 'true') {
    $data['openingHours'] = !empty($_POST['cusOpen']) ? trim($_POST['cusOpen']) : null;
    $data['openingCustom'] = "1";
}

if (!empty($_POST['dayDeliChk']) && $_POST['dayDeliChk'] === 'true') {
    $delivery = [
        'sunDeli' => !empty($_POST['sunDeli']) ? $_POST['sunDeli'] : null,
        'monDeli' => !empty($_POST['monDeli']) ? $_POST['monDeli'] : null,
        'tueDeli' => !empty($_POST['tueDeli']) ? $_POST['tueDeli'] : null,
        'wedDeli' => !empty($_POST['wedDeli']) ? $_POST['wedDeli'] : null,
        'thuDeli' => !empty($_POST['thuDeli']) ? $_POST['thuDeli'] : null,
        'friDeli' => !empty($_POST['friDeli']) ? $_POST['friDeli'] : null,
        'satDeli' => !empty($_POST['satDeli']) ? $_POST['satDeli'] : null
    ];
    $data['delivery'] = implode('__',$delivery);
    $data['deliveryCustom'] = "0";
}

if (!empty($_POST['customDeliChk']) && $_POST['customDeliChk'] === 'true') {
    $data['delivery'] = !empty($_POST['cusDeli']) ? trim($_POST['cusDeli']) : null;
    $data['deliveryCustom'] = "1";
}

$isCustom = !empty($_POST['isCustom']);
if ($data['mode'] == "save" && $isCustom && !briefTableExists($db)) {
    echo json_encode(['result' => 'fail', 'msg' => 'Fully custom is not available yet: run docs/migrations/2026-10-06-templates-fully-custom.sql']);
    exit;
}

if ($data['mode'] ==  "save") {
    $select = $db->query('UPDATE `tb_project` SET 
        `projectCode`=?, `saveFlag`=?, `email`=?, `phone`=?, `address`=?, `openingCustom`=?, `openingHours`=?, 
        `deliveryCustom`=?, `pickupAndDelivery`=?, `logo`=?, `colorTheme1`=?, `colorTheme2`=?, 
        `colorTheme3`=?, `domainName`=?, `domainHave`=?, `domainProvidersID`=?, `domainUser`=?, 
        `domainPass`=?, `hostingName`=?, `hostingHave`=?, `hostingProvidersID`=?, `hostingUser`=?, 
        `hostingPass`=?, `gloriaHave`=?, `orderURL`=?, `tableURL`=?, `orderOther`=?, `resOtherSystem`=?, 
        `amelia`=?, `voucher`=?, `bookOther`=?, `masOtherSystem`=?, `needEmail`=?, `facebookURL`=?, 
        `instagramURL`=?, `youtubeURL`=?, `tiktokURL`=?, `updateBy`=? 
        WHERE `projectID`=?',
        $data['projectCode'],
        1,
        $data['email'],
        $data['phone'],
        $data['address'],
        $data['openingCustom'],
        $data['openingHours'],
        $data['deliveryCustom'],
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
    if ($isCustom) saveBrief($db, $data['projectID'], $myID);
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

// businessNote is owned by the project modal (models/project.php), so it is not touched here.
function saveBrief($db, $projectID, $myID): void
{
    $txt = fn($k, $max = 65535) => (isset($_POST[$k]) && trim($_POST[$k]) !== '') ? mb_substr(trim($_POST[$k]), 0, $max) : null;
    $pick = fn($k, array $allowed) => isset($allowed[$_POST[$k] ?? '']) ? $_POST[$k] : null;
    $flag = fn($k) => (!empty($_POST[$k]) && ($_POST[$k] == 1 || $_POST[$k] === 'true')) ? 1 : 0;

    $links = json_decode($_POST['refLinks'] ?? '[]', true);
    $links = is_array($links) ? array_values(array_filter(array_map(fn($l) => [
        'url' => mb_substr(trim((string)($l['url'] ?? '')), 0, 255),
        'note' => mb_substr(trim((string)($l['note'] ?? '')), 0, 255),
        'forColor' => empty($l['forColor']) ? 0 : 1,
    ], $links), fn($l) => $l['url'] !== '')) : [];
    $deadline = $txt('deadline', 10);
    if ($deadline !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) $deadline = null;

    $db->query('INSERT INTO `tb_projectBrief` (`projectID`, `colorMode`, `systemName`, `systemURL`, `goal`, `pages`, `oldSiteURL`,
            `keepURLs`, `refLinks`, `mood`, `contentSource`, `hasVectorLogo`, `hasHiResPhotos`, `features`, `tracking`, `assetLinks`, `deadline`, `notes`, `updateBy`)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE `colorMode`=VALUES(`colorMode`), `systemName`=VALUES(`systemName`), `systemURL`=VALUES(`systemURL`),
            `goal`=VALUES(`goal`), `pages`=VALUES(`pages`), `oldSiteURL`=VALUES(`oldSiteURL`), `keepURLs`=VALUES(`keepURLs`),
            `refLinks`=VALUES(`refLinks`), `mood`=VALUES(`mood`), `contentSource`=VALUES(`contentSource`),
            `hasVectorLogo`=VALUES(`hasVectorLogo`), `hasHiResPhotos`=VALUES(`hasHiResPhotos`), `features`=VALUES(`features`),
            `tracking`=VALUES(`tracking`), `assetLinks`=VALUES(`assetLinks`), `deadline`=VALUES(`deadline`), `notes`=VALUES(`notes`), `updateBy`=VALUES(`updateBy`)',
        $projectID,
        $pick('colorMode', BRIEF_COLOR_MODES) ?? 'pick',
        $txt('systemName', 255),
        $txt('systemURL', 255),
        $pick('goal', BRIEF_GOALS),
        $txt('pages'),
        $txt('oldSiteURL', 255),
        $flag('keepURLs'),
        json_encode($links, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        $txt('mood', 255),
        $pick('contentSource', BRIEF_CONTENT),
        $flag('hasVectorLogo'),
        $flag('hasHiResPhotos'),
        $txt('features'),
        $txt('tracking'),
        $txt('assetLinks'),
        $deadline,
        $txt('notes'),
        $myID
    );
}