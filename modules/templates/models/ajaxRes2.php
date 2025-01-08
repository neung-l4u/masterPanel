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
$data['token'] = !empty($_POST['token']) ? $_POST['token'] : "no token";

$data['homeSlider1'] = !empty($_POST['homeSlider1']) ? trim($_POST['homeSlider1']) : null;
$data['homeSlider2'] = !empty($_POST['homeSlider2']) ? trim($_POST['homeSlider2']) : null;
$data['homeSlider3'] = !empty($_POST['homeSlider3']) ? trim($_POST['homeSlider3']) : null;
$data['promotionHeadline1'] = !empty($_POST['promotionHeadline1']) ? trim($_POST['promotionHeadline1']) : null;
$data['promotionMessage1'] = !empty($_POST['promotionMessage1']) ? trim($_POST['promotionMessage1']) : null;
$data['introduceImg1'] = !empty($_POST['introduceImg1']) ? trim($_POST['introduceImg1']) : null;
$data['introduceImg2'] = !empty($_POST['introduceImg2']) ? trim($_POST['introduceImg2']) : null;
$data['introduceImg3'] = !empty($_POST['introduceImg3']) ? trim($_POST['introduceImg3']) : null;
$data['introduceImg4'] = !empty($_POST['introduceImg4']) ? trim($_POST['introduceImg4']) : null;
$data['featuredImg1'] = !empty($_POST['featuredImg1']) ? trim($_POST['featuredImg1']) : null;
$data['featuredImg2'] = !empty($_POST['featuredImg2']) ? trim($_POST['featuredImg2']) : null;
$data['featuredImg3'] = !empty($_POST['featuredImg3']) ? trim($_POST['featuredImg3']) : null;
$data['featuredImg4'] = !empty($_POST['featuredImg4']) ? trim($_POST['featuredImg4']) : null;
$data['featuredImg5'] = !empty($_POST['featuredImg5']) ? trim($_POST['featuredImg5']) : null;
$data['featuredImg6'] = !empty($_POST['featuredImg6']) ? trim($_POST['featuredImg6']) : null;
$data['featuredImg7'] = !empty($_POST['featuredImg7']) ? trim($_POST['featuredImg7']) : null;
$data['featuredImg8'] = !empty($_POST['featuredImg8']) ? trim($_POST['featuredImg8']) : null;
$data['promotionBg1'] = !empty($_POST['promotionBg1']) ? trim($_POST['promotionBg1']) : null;
$data['promotionBg2'] = !empty($_POST['promotionBg2']) ? trim($_POST['promotionBg2']) : null;
$data['promotionBg3'] = !empty($_POST['promotionBg3']) ? trim($_POST['promotionBg3']) : null;
$data['promotionBg4'] = !empty($_POST['promotionBg4']) ? trim($_POST['promotionBg4']) : null;
$data['promotionHeadline2'] = !empty($_POST['promotionHeadline2']) ? trim($_POST['promotionHeadline2']) : null;
$data['reviewsBg'] = !empty($_POST['reviewsBg']) ? trim($_POST['reviewsBg']) : null;
$data['testimonialText1'] = !empty($_POST['testimonialText1']) ? trim($_POST['testimonialText1']) : null;
$data['testimonialName1'] = !empty($_POST['testimonialName1']) ? trim($_POST['testimonialName1']) : null;
$data['testimonialImg1'] = !empty($_POST['testimonialImg1']) ? trim($_POST['testimonialImg1']) : null;
$data['testimonialText2'] = !empty($_POST['testimonialText2']) ? trim($_POST['testimonialText2']) : null;
$data['testimonialName2'] = !empty($_POST['testimonialName2']) ? trim($_POST['testimonialName2']) : null;
$data['testimonialImg2'] = !empty($_POST['testimonialImg2']) ? trim($_POST['testimonialImg2']) : null;
$data['promotionImg2'] = !empty($_POST['promotionImg2']) ? trim($_POST['promotionImg2']) : null;
$data['promotionHeadline3'] = !empty($_POST['promotionHeadline3']) ? trim($_POST['promotionHeadline3']) : null;
$data['promotionMessage3'] = !empty($_POST['promotionMessage3']) ? trim($_POST['promotionMessage3']) : null;
$data['footerImg1'] = !empty($_POST['footerImg1']) ? trim($_POST['footerImg1']) : null;
$data['footerImg2'] = !empty($_POST['footerImg2']) ? trim($_POST['footerImg2']) : null;
$data['footerImg3'] = !empty($_POST['footerImg3']) ? trim($_POST['footerImg3']) : null;
$data['footerImg4'] = !empty($_POST['footerImg4']) ? trim($_POST['footerImg4']) : null;
$data['footerBg'] = !empty($_POST['footerBg']) ? trim($_POST['footerBg']) : null;
$data['promotionHeadline4'] = !empty($_POST['promotionHeadline4']) ? trim($_POST['promotionHeadline4']) : null;
$data['promotionMessage4'] = !empty($_POST['promotionMessage4']) ? trim($_POST['promotionMessage4']) : null;

if ($data['mode'] ==  "save") {
    // $insert = $db->query('INSERT INTO `` VALUES (?)',
    //     $data['projectID'],
    //     $data['homeSlider1'],
    //     $data['homeSlider2'],
    //     $data['homeSlider3'],
    //     $data['promotionHeadline1'],
    //     $data['promotionMessage1'],
    //     $data['introduceImg1'],
    //     $data['introduceImg2'],
    //     $data['introduceImg3'],
    //     $data['introduceImg4'],
    //     $data['featuredImg1'],
    //     $data['featuredImg2'],
    //     $data['featuredImg3'],
    //     $data['featuredImg4'],
    //     $data['featuredImg5'],
    //     $data['featuredImg6'],
    //     $data['featuredImg7'],
    //     $data['featuredImg8'],
    //     $data['promotionBg1'],
    //     $data['promotionBg2'],
    //     $data['promotionBg3'],
    //     $data['promotionBg4'],
    //     $data['promotionHeadline2'],
    //     $data['reviewsBg'],
    //     $data['testimonialText1'],
    //     $data['testimonialName1'],
    //     $data['testimonialImg1'],
    //     $data['testimonialText2'],
    //     $data['testimonialName2'],
    //     $data['testimonialImg2'],
    //     $data['promotionImg2'],
    //     $data['promotionHeadline3'],
    //     $data['promotionMessage3'],
    //     $data['footerImg1'],
    //     $data['footerImg2'],
    //     $data['footerImg3'],
    //     $data['footerImg4'],
    //     $data['footerBg'],
    //     $data['promotionHeadline4'],
    //     $data['promotionMessage4'],
    //     $data['token']
    // );

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