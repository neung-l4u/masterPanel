<?php
global $db;
session_start();
include '../assets/db/db.php';
include "../assets/db/initDB.php";

$iconSendMailDraft = '<i class="bi bi-envelope-slash action_icon text-muted" title="please edit designer"></i>';
$iconSendMailReady = '<i class="bi bi-envelope-arrow-up-fill action_icon"></i>';
$iconSendMailSend = '<i class="bi bi-envelope-check-fill action_icon text-success" title="Template submitted"></i>';
$iconPageGray = '<i class="bi bi-file-earmark-fill action_icon text-muted" title="not submit yet"></i>';
$iconPageGreen = '<i class="bi bi-file-earmark-check-fill action_icon text-success" title="page submitted"></i>';
$iconNext = '<i class="bi bi-arrow-up-right-square-fill action_icon"></i>';
$iconEdit = '<i class="bi bi-pencil action_icon"></i>';
$iconDelete = '<i class="bi bi-trash3 action_icon"></i>';
$iconTemplate = '<i class="bi bi-file-earmark-richtext action_icon"></i>';
$iconTemplateGray = '<i class="bi bi-file-earmark-richtext action_icon text-muted"></i>';

$param['ownerID'] = $_SESSION['id'];

    $ownerID = $param['ownerID'];
    $showAll = in_array($ownerID, [1, 14, 60, 100, 27, 48]);
    $where = '';

    $sql = 'SELECT pj.saveFlag, pj.projectID AS id, pj.projectName, t.name AS "shopType", pj.selectedTemplate, pj.statusID, 
                s.sNickName AS "owner", c.name AS "countryName", c.code AS "countryCode", pj.projectTimestamp, 
                pd.home AS "homePage", pd.about AS "aboutPage", pd.services AS "servicesPage", pd.contact AS "contactPage"
            FROM `tb_project` pj 
            LEFT JOIN `templatepagedetails` pd ON pj.projectID = pd.projectID 
            LEFT JOIN `Countries` c ON pj.countryID = c.id
            LEFT JOIN `staffs` s ON pj.projectOwner = s.sID
            LEFT JOIN `tb_shopType` t ON pj.shopTypeID = t.id
            ';
    
    $where = 'WHERE pj.deleteAt IS NULL';
    $order = 'ORDER BY pj.projectID DESC';

    if (!$showAll) {
        $sql = $sql . ' ' . $where . ' AND pj.projectOwner = ? '.$order;
        $projects = $db->query($sql, $ownerID)->fetchAll();
    } else {
        $projects = $db->query($sql . ' ' . $where.' '.$order)->fetchAll();
    }
    
    $row = array();
    $i = 1;
    $data = array("data"=> array());
    foreach ($projects as $row) {

        //$iconSendMailReady = '<img src="../assets/img/sendMail.svg" alt="Send Mail" title="Send Mail" class="action_icon" id="iconSendMailReady' .$row["id"]. '" onclick="sendProject('. $row["id"] .');">';

        $statusText = ($row["statusID"] == 1) ? 'Draft' : 'Send';
        $url = 'main.php?m=detail&id='.$row["id"];
        $temPage = ($row["shopType"] == "Restaurant") ? 'res' : 'mas';
        $temPage = $temPage . $row["selectedTemplate"];
        $templateUrl = 'main.php?m='. $temPage .'&id='. $row["id"];

        $iconTemplateUse = ($row["saveFlag"] == 1) ? $iconTemplate : $iconTemplateGray;
        $linkTemplate = ($row["saveFlag"] == 1) ? '<a href="'. $templateUrl .'" title="Template Designer">'. $iconTemplateUse. '</a>' : $iconTemplateUse;

        if ($row["shopType"] == 'Restaurant') {
            $iconPageHome = ($row["homePage"] == null) ? '<span title="Home">'. $iconPageGray .'</span>' : '<span title="Home">'. $iconPageGreen .'</span>';
            $iconPageAbout = ($row["aboutPage"] == null) ? '<span title="About">'. $iconPageGray .'</span>' : '<span title="About">'. $iconPageGreen .'</span>';
            $iconPageContact = ($row["contactPage"] == null) ? '<span title="Contact">'. $iconPageGray .'</span>' : '<span title="Contact">'. $iconPageGreen .'</span>';
            $iconPage = $iconPageHome . $iconPageAbout . $iconPageContact;
        } else if ($row["shopType"] == 'Massage') {
            $iconPageHome = ($row["homePage"] == null) ? '<span title="Home">'. $iconPageGray .'</span>' : '<span title="Home">'. $iconPageGreen .'</span>';
            $iconPageAbout = ($row["aboutPage"] == null) ? '<span title="About">'. $iconPageGray .'</span>' : '<span title="About">'. $iconPageGreen .'</span>';
            $iconPageServices = ($row["servicesPage"] == null) ? '<span title="Services">'. $iconPageGray .'</span>' : '<span title="Services">'. $iconPageGreen . '</span>';
            $iconPageContact = ($row["contactPage"] == null) ? '<span title="Contact">'. $iconPageGray .'</span>' : '<span title="Contact">'. $iconPageGreen .'</span>';
            $iconPage = $iconPageHome . $iconPageAbout . $iconPageServices . $iconPageContact;
        }

        if ($row["shopType"] == 'Restaurant') {
            if ($row["homePage"] !== null && $row["aboutPage"] !== null && $row["contactPage"] !== null) {
                $iconSendMail = '<a href="#" onclick="sending('. $row["id"] .');">'. $iconSendMailReady .'</a>';
                //$iconSendMail = '<a href="#">'. $iconSendMailReady .'</a>';
                if ($row["statusID"] == 1) {
                    $statusText = "Ready";
                }
            } else {
                $iconSendMail = '<a>'. $iconSendMailDraft .'</a>';
            }
        } else if ($row["shopType"] == 'Massage') {
            if ($row["homePage"] !== null && $row["aboutPage"] !== null && $row["servicesPage"] !== null && $row["contactPage"] !== null) {
                $iconSendMail = '<a href="#" onclick="sending('. $row["id"] .');" title="Submit template">'. $iconSendMailReady .'</a>';
                //$iconSendMail = '<a href="#">'. $iconSendMailReady .'</a>';
                if ($row["statusID"] == 1) {
                    $statusText = "Ready";
                }
            } else {
                $iconSendMail = '<a>'. $iconSendMailDraft .'</a>';
            }
        }
        if ($row["statusID"] == 2) { $iconSendMail = '<a>'. $iconSendMailSend .'</a>'; }

        $processingTxt = '<span id="processingTxt' .$row["id"]. '">Processing &nbsp;</span>';
        $savetoDBTxt = '<span id="savetoDBTxt' .$row["id"]. '">Save to Database &nbsp;</span>';
        $creatMondayTxt = '<span id="creatMondayTxt' .$row["id"]. '">Create Task Monday &nbsp;</span>';
        $sendMailTxt = '<span id="sendMailTxt' .$row["id"]. '">Sending Email &nbsp;</span>';
        $iconSending = '<img src="../assets/img/unnamed.gif" alt="Sending" title="Sending" class="action_icon" id="iconSending' .$row["id"]. '">';
        
        $sendingDiv = '<div id="sendingDiv'.$row["id"].'" style="display:none;">'. $processingTxt . $savetoDBTxt . $creatMondayTxt . $sendMailTxt . $iconSending .'</div>';
        $actionDiv = '<div id="actionDiv'.$row["id"].'">'. $iconSendMail . $linkTemplate . '<a href="'.$url.'" title="Project simple detail">'.$iconNext.'</a><a href="#" onclick="setEdit('.$row["id"].');" title="Edit project">'.$iconEdit.'</a><a href="#" onclick="setDel('.$row["id"].');" title="Delete Project">'.$iconDelete.'</a>'. '</div>';
        
        $data["data"][] = array(
            $i,
            '<a href="'.$url.'" title="Project simple detail">'.$row["owner"].'</a>',
            '<a href="'.$url.'" title="Project simple detail">'.minType($row["shopType"]) ." ". $row["selectedTemplate"].'</a>',
            '<a href="'.$url.'" title="Project simple detail">'.$row["projectName"] . " (".$row["countryCode"].")".'</a>',
            $iconPage,
            '<small>'.$statusText.'</small>',
            $sendingDiv . $actionDiv,
        );

        $i++;
    }//foreach

echo json_encode($data);

    function minType($param)
    {
        $prefix = substr($param, 0, 3);
        preg_match('/(\d+)$/', $param, $matches);
        $number = $matches[1] ?? '';

        return $prefix . ' ' . $number;
    }