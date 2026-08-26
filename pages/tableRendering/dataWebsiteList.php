<?php
global $db;
session_start();
include '../../assets/db/db.php';
include "../../assets/db/initDB.php";
include '../../assets/security/Sanitizer.php';
include '../../assets/security/QueryBuilder.php';

$params = [
    "shopType" => !empty($_POST['shopType']) ? $_POST['shopType'] : '',
    "system"   => !empty($_POST['system'])   ? $_POST['system']   : '',
    "liveStatus"   => !empty($_POST['liveStatus'])  ? $_POST['liveStatus']  : '',
    "template" => !empty($_POST['template']) ? $_POST['template'] : '',
    "country"  => !empty($_POST['country'])  ? $_POST['country']  : '',
    "server"   => !empty($_POST['server'])   ? $_POST['server']   : '',
    "projectLink" => !empty($_POST['projectLink']) ? $_POST['projectLink'] : '',
];

$qb = new QueryBuilder();
$qb->eq('w.wIndustry', $params["shopType"]);

if ($params["system"] === "AM")      $qb->raw("AND w.wSystemAmelia = 1");
elseif ($params["system"] === "GF")  $qb->raw("AND w.wSystemGloriaFood = 1");
elseif ($params["system"] === "VC")  $qb->raw("AND w.wSystemVoucher = 1");

$qb->eq('w.wLiveStatus', $params["liveStatus"])
   ->eq('w.wTemplateUsed', $params["template"])
   ->eq('w.countryID', $params["country"])
   ->eq('sv.svID', $params["server"]);

$baseSql = "SELECT * FROM websiteList w LEFT JOIN L4UServers sv ON w.svID = sv.svID WHERE delete_at IS NULL";
$result = $qb->execute($db, $baseSql, 'ORDER BY w.wID DESC')->fetchAll();

// Template submissions are matched by name when websiteList.projectID is not set.
// Names that resolve to more than one project are left unmatched on purpose.
$projectByName = array();
foreach ($db->query('SELECT projectID, projectName FROM tb_project WHERE deleteAt IS NULL;')->fetchAll() as $projectRow) {
    $key = normaliseProjectName($projectRow["projectName"]);
    if ($key === '') continue;
    if (!isset($projectByName[$key])) {
        $projectByName[$key] = $projectRow["projectID"];
    } else {
        $projectByName[$key] = false; // ambiguous
    }
}

$data = array("data"=> array());

$i=1;
foreach ($result as $row) {
    // Template submission link, from the stored projectID or a unique name match
    $projectID = !empty($row["projectID"]) ? (int)$row["projectID"] : 0;
    if ($projectID === 0) {
        $key = normaliseProjectName($row["wProject"]);
        if ($key !== '' && !empty($projectByName[$key])) {
            $projectID = (int)$projectByName[$key];
        }
    }

    if ($params["projectLink"] === "linked" && $projectID === 0) continue;
    if ($params["projectLink"] === "unlinked" && $projectID > 0) continue;

    $No = $row["wID"];
    $statusWebsite = !empty($row["wLiveStatus"]) ? $row["wLiveStatus"] : '-';
    $siteUrl = ensureAbsoluteUrl(stripWpAdmin($row["wWordpressURL"]));
    $url = '<a href="'.escUrl($siteUrl).'" target="_blank" title="WP-Link">'.esc($siteUrl).'</a>';
    $link = !empty($siteUrl) ? $url : '-';
    $server = $row['svName'] ?? '-';
    $wpAdminUrl = ensureAbsoluteUrl($row["wWordpressURL"]);
    $btn["URL"] = '<a href="'.escUrl($wpAdminUrl).'" target="_blank" title="WP-Admin"><i class="bi bi-box-arrow-up-right text-primary"></i></a>';
    $btn["detail"] = '<a href="#" onclick="viewDetail('.$row["wID"].')" title="Detail"><i class="bi bi-file-earmark-text"></i></a>';
    $btn["edit"] = '<a href="#" onclick="setEdit('.$row["wID"].')" title="Edit"><i class="bi bi-pencil-square text-dark"></i></a>';
    $btn["delete"] = '<a href="#" onclick="setDel('.$row["wID"].')" title="Delete"><i class="bi bi-x-square text-danger"></i></a>';

    $btn["template"] = $projectID > 0
        ? '<a href="pages/tpSubmittedDetails.php?act=readProject&projectID='.$projectID.'" target="_blank" title="Template submission detail"><i class="bi bi-clipboard-data text-primary"></i></a>'
        : '<span class="text-muted" title="No template submission linked to this website"><i class="bi bi-clipboard-data"></i></span>';

    // One-click cPanel login, only when WHM can actually issue a session for this row
    $canCpanel = !empty($row["wCPanelUser"]) && !empty($row["svWHMURL"]) && !empty($row["svWHMUser"]) && !empty($row["svWHMApiToken"]);
    $btn["cpanel"] = $canCpanel
        ? '<a href="#" onclick="openCpanel('.intval($row["wID"]).', this)" title="Open cPanel as '.esc($row["wCPanelUser"]).'"><i class="bi bi-hdd-stack text-primary"></i></a>'
        : '<span class="text-muted" title="cPanel auto-login unavailable for this website"><i class="bi bi-hdd-stack"></i></span>';
    
    $data["data"][] = array(
        $i,
        '<a href="#" onclick="viewDetail('.intval($row["wID"]).')" title="Detail" class="linkDetail">'.esc(dash($row["wProject"])).'</a>',
        $link,
        $server,
        $statusWebsite,
        $btn["URL"]." ".$btn["template"]." ".$btn["cpanel"]." ".$btn["edit"]." ".$btn["delete"]
    );
    $i++;
}

echo json_encode($data);

/**
 * Normalise a project name for matching between websiteList and tb_project.
 * Drops the trailing country suffix ("- AU") and any non-alphanumeric characters.
 */
function normaliseProjectName($name): string
{
    $name = preg_replace('/\s*[-\x{2013}]\s*(AU|UK|USA|US|NZ|TH|CA|IE)\s*$/iu', '', trim($name ?? ''));
    return strtolower(preg_replace('/[^a-z0-9]/i', '', $name));
}

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

function dashAndShort($param): string
{
    if (empty($param)) { return "-"; }
    else {
        $location = mb_substr($param, 0, 15).'...';
        return '<abbr title="'.esc($param).'">'.esc($location).'</abbr>';
    }
}

function stripWpAdmin($url): string
{
    $url = trim($url ?? '');
    if (empty($url)) return $url;
    return preg_replace('~/wp-admin/?$~i', '', $url);
}

function ensureAbsoluteUrl($url): string
{
    $url = trim($url ?? '');
    if (empty($url)) return $url;
    if (preg_match('~^(https?://|mailto:|tel:|//)~i', $url)) return $url;
    return 'https://' . $url;
}