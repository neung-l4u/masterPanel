<?php
global $db;
session_start();
include '../assets/db/db.php';
include "../assets/db/initDB.php";
include '../../../assets/security/Sanitizer.php';
include '../../../assets/security/QueryBuilder.php';

$params["filterShopType"] = !empty($_POST['shopType']) ? $_POST['shopType'] : '';
$params["filterSystem"] = !empty($_POST['system']) ? $_POST['system'] : '';
$params["filterStatus"] = !empty($_POST['fstatus']) ? $_POST['fstatus'] : '';
$params["filterCountry"] = !empty($_POST['country']) ? $_POST['country'] : '';

$qb = new QueryBuilder();
$qb->eq('w.wIndustry', $params["filterShopType"]);

if (!empty($params["filterSystem"])) {
    if ($params["filterSystem"] === "AM")      $qb->raw("AND w.wSystemAmelia = 1");
    else if ($params["filterSystem"] === "GF")  $qb->raw("AND w.wSystemGloriaFood = 1");
    else if ($params["filterSystem"] === "VC")  $qb->raw("AND w.wSystemVoucher = 1");
    else if ($params["filterSystem"] === "CW")  $qb->raw("AND w.wSystemCloudwaitress = 1");
}

$qb->eq('w.wLiveStatus', $params["filterStatus"])
   ->eq('w.countryID', $params["filterCountry"]);

$baseSql = 'SELECT w.*, c.name AS countryName, st.name AS shopTypeName FROM `websiteList` w
            LEFT JOIN Countries c ON w.countryID = c.id
            LEFT JOIN tb_shopType st ON w.wIndustry = st.id
            WHERE w.delete_at IS NULL';
$result = $qb->execute($db, $baseSql, 'ORDER BY w.wID DESC')->fetchAll();

$statusBadge = ['Live' => 'success', 'Pre Live' => 'info', 'Subdomain' => 'primary', 'Redirect' => 'warning', 'Transferred' => 'dark', 'Draft' => 'secondary', 'Unpublished' => 'danger'];


$data = array("data"=> array());

$i=1;
foreach ($result as $row) {
    $No = $row["wID"];
    $wpAdminUrl = ensureAbsoluteUrl($row["wWordpressURL"]);
    $btn["URL"] = '<a href="'.escUrl($wpAdminUrl).'" target="_blank" title="WP-Admin" class="btn btn-light btnIcon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/><path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/></svg></a>';
    $btn["detail"] = '<a href="#" onclick="viewDetail('.intval($row["wID"]).')" title="Detail" class="btn btn-light btnIcon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16"><path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/><path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/></svg></a>';
    $siteUrl = ensureAbsoluteUrl($row["wDomain"]);
    $shown = preg_replace('#^https?://(www\.)?#i', '', rtrim($siteUrl, '/'));   // ไม่ต้องโชว์ https:// ให้อ่านง่าย
    $link = !empty($siteUrl) ? '<a href="'.escUrl($siteUrl).'" target="_blank" class="siteLink" title="'.esc($siteUrl).'">'.esc($shown).'</a>' : '-';

    $data["data"][] = array(
        $i,
        '<a href="#" onclick="viewDetail('.intval($row["wID"]).')" class="linkDetail">'.esc(dash($row["wProject"])).'</a>',
        $link,
        esc(dash($row["countryName"])),
        esc(dash($row["shopTypeName"])),
        '<span class="badge statusBadge bg-'.($statusBadge[$row["wLiveStatus"]] ?? 'secondary').'-subtle text-'.($statusBadge[$row["wLiveStatus"]] ?? 'secondary').'-emphasis">'.esc(dash($row["wLiveStatus"])).'</span>',
        '<span class="ownerEmail" title="'.esc($row["wOwnerEmail"]).'">'.esc(dash($row["wOwnerEmail"])).'</span>',
        $btn["URL"].' '.$btn["detail"],
    );
    $i++;
}

echo json_encode($data);

function showName($nick, $full){
   //$text =  $nick . " (" . $full . ")";
    $tmp = explode(" ",$full);
   return $nick . " " . $tmp[0];
}//

function dash($param){
    if (empty($param)) { return "-"; }
    else {
        return $param;
    }
}

