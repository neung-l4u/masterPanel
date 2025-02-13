<?php
global $db;
session_start();
include '../assets/db/db.php';
include "../assets/db/initDB.php";

$iconSendMailDraft = '<img src="../assets/img/sendMailGray.svg" alt="Send Mail" title="Send Mail" class="action_icon">';
$iconSendMailReady = '<img src="../assets/img/sendMail.svg" alt="Send Mail" title="Send Mail" class="action_icon">';
$iconSendMailSend = '<img src="../assets/img/sendMailGreen.svg" alt="Send Mail" title="Send Mail" class="action_icon">';
$iconPageGray = '<img src="../assets/img/page_gray.svg" alt="Unsubmit page" class="action_icon page_icon">';
$iconPageGreen = '<img src="../assets/img/page_green.svg" alt="Submit page" class="action_icon page_icon">';
$iconNext = '<img src="../assets/img/next.svg" alt="detail" title="Detail" class="action_icon">';
$iconEdit = '<img src="../assets/img/edit.svg" alt="edit" title="Edit" class="action_icon">';
$iconDelete = '<img src="../assets/img/del.svg" alt="delete" title="Delete" class="action_icon">';
$iconTemplate = '<img src="../assets/img/template.svg" alt="Edit Template" title="Edit Template" class="action_icon">';
$iconTemplateGray = '<img src="../assets/img/template_gray.svg" alt="Edit Template" title="Edit Template" class="action_icon">';

$param['ownerID'] = $_SESSION['id'];

$projects = $db->query('SELECT pj.saveFlag, pj.projectID AS "id", pj.projectName, t.name AS "shopType", pj.selectedTemplate, pj.statusID, s.sNickName AS "owner", c.name AS "countryName", c.code AS "countryCode", pj.projectTimestamp, 
                                   pd.home AS "homePage", pd.about AS "aboutPage", pd.services AS "servicesPage", pd.contact AS "contactPage"
                            FROM `tb_project` pj 
                            LEFT JOIN `templatepagedetails` pd ON pj.projectID = pd.projectID 
                            LEFT JOIN `Countries` c ON pj.countryID = c.id
                            LEFT JOIN `staffs` s ON pj.projectOwner = s.sID
                            LEFT JOIN `tb_shopType` t ON pj.shopTypeID = t.id
                            WHERE pj.projectOwner = ?
                            AND pj.deleteAt IS NULL;'
        ,$param['ownerID'])->fetchAll();
    
    $row = array();
    $i = 1;
    $data = array("data"=> array());
    foreach ($projects as $row) {
        
        $statusText = ($row["statusID"] == 1) ? 'Draft' : 'Send';
        $url = 'main.php?m=detail&id='.$row["id"];
        $temPage = ($row["shopType"] == "Restaurant") ? 'res' : 'mas';
        $temPage = $temPage . $row["selectedTemplate"];
        $templateUrl = 'main.php?m='. $temPage .'&id='. $row["id"];

        $iconTemplateUse = ($row["saveFlag"] == 1) ? $iconTemplate : $iconTemplateGray;
        $linkTemplate = ($row["saveFlag"] == 1) ? '<a href="'. $templateUrl .'">'. $iconTemplateUse. '</a>' : $iconTemplateUse;

        if ($row["shopType"] == 'Restaurant') {
            $iconPageHome = ($row["homePage"] == null) ? '<span title="Home">'. $iconPageGray .'</span>' : '<span title="Home">'. $iconPageGreen .'</span>';
            $iconPageAbout = ($row["aboutPage"] == null) ? '<span title="About">'. $iconPageGray .'</span>' : '<span title="About">'. $iconPageGreen .'</span>';
            $iconPageContact = ($row["contactPage"] == null) ? '<span title="Contact">'. $iconPageGray .'</span>' : '<span title="Contact">'. $iconPageGreen .'</span>';
            $iconPage = $iconPageHome ." ". $iconPageAbout ." ". $iconPageContact;
        } else if ($row["shopType"] == 'Massage') {
            $iconPageHome = ($row["homePage"] == null) ? '<span title="Home">'. $iconPageGray .'</span>' : '<span title="Home">'. $iconPageGreen .'</span>';
            $iconPageAbout = ($row["aboutPage"] == null) ? '<span title="About">'. $iconPageGray .'</span>' : '<span title="About">'. $iconPageGreen .'</span>';
            $iconPageServices = ($row["servicesPage"] == null) ? '<span title="Services">'. $iconPageGray .'</span>' : '<span title="Services">'. $iconPageGreen . '</span>';
            $iconPageContact = ($row["contactPage"] == null) ? '<span title="Contact">'. $iconPageGray .'</span>' : '<span title="Contact">'. $iconPageGreen .'</span>';
            $iconPage = $iconPageHome ." ". $iconPageAbout ." ". $iconPageServices ." ". $iconPageContact;
        }

        if ($row["shopType"] == 'Restaurant') {
            if ($row["homePage"] !== null && $row["aboutPage"] !== null && $row["contactPage"] !== null) {
                $iconSendMail = '<a href="#" onclick="sendProject('. $row["id"] .');">'. $iconSendMailReady .'</a>';
                if ($row["statusID"] == 1) {
                    $statusText = "Ready";
                }
            } else {
                $iconSendMail = '<a>'. $iconSendMailDraft .'</a>';
            }
        } else if ($row["shopType"] == 'Massage') {
            if ($row["homePage"] !== null && $row["aboutPage"] !== null && $row["servicesPage"] !== null && $row["contactPage"] !== null) {
                $iconSendMail = '<a href="#" onclick="sendProject('. $row["id"] .');">'. $iconSendMailReady .'</a>';
                if ($row["statusID"] == 1) {
                    $statusText = "Ready";
                }
            } else {
                $iconSendMail = '<a>'. $iconSendMailDraft .'</a>';
            }
        }
        if ($row["statusID"] == 2) { $iconSendMail = '<a>'. $iconSendMailSend .'</a>'; }
        
        $data["data"][] = array(
            $i,
            $row["shopType"] ." ". $row["selectedTemplate"],
            $row["countryCode"] .' : '. $row["projectName"],
            $iconPage,
            $statusText,
            $iconSendMail . $linkTemplate . '<a href="'.$url.'">'.$iconNext.'</a><a href="#" onclick="setEdit('.$row["id"].');">'.$iconEdit.'</a><a href="#" onclick="setDel('.$row["id"].');">'.$iconDelete.'</a>'
        );

        $i++;
    }//foreach


echo json_encode($data);