<?php
global $db;

//เรียกใช้งาน object
include '../assets/db/db.php';
include "../assets/db/initDB.php";

//รับค่าที่ส่งมาเก็บใน Array $param
$param['act'] = (!empty($_REQUEST['act'])) ? trim($_REQUEST['act']) : ''; //ใช้เลือกเคสด้านล่างที่ต้องทำ
$param['name'] = (!empty($_REQUEST['name'])) ? trim($_REQUEST['name']) : ''; //ชื่อโปรเจคที่ส่งมา
$param['shopTypeID'] = (!empty($_REQUEST['shopTypeID'])) ? trim($_REQUEST['shopTypeID']) : '';
$param['selectedTemplate'] = (!empty($_REQUEST['selectedTemplate'])) ? trim($_REQUEST['selectedTemplate']) : null;
$param['status'] = (!empty($_REQUEST['status'])) ? trim($_REQUEST['status']) : '1'; //1=Draft , 2=Send
$param['country'] = (!empty($_REQUEST['country'])) ? trim($_REQUEST['country']) : ''; //ตามตาราง Countries
$param['editID'] = (!empty($_REQUEST['editID'])) ? trim($_REQUEST['editID']) : ''; //จะส่งมาเฉพาะเคส setEdit , Update
$param['delID'] = (!empty($_REQUEST['delID'])) ? trim($_REQUEST['delID']) : ''; //จะส่งมาเฉพาะเคส del
$param['ownerID'] = (!empty($_REQUEST['ownerID'])) ? trim($_REQUEST['ownerID']) : ''; //มาจาก session ที่ frontend อ่านมาให้

//สร้างตัวแปร Array ไว้ตอนส่งค่ากลับ
$return['result'] = '';
$return['msg'] = '';
$return['data'] = '';
$return['act'] = $param['act'];

if(empty($param['ownerID'])){ //ถ้าไม่มี session login จะหยุดแค่ตรงนี้ ไม่ทำทุกกรณี
    $return['result'] = 'Exit';
    $return['msg'] = 'Your session is expire please login again.';
    $return['data'] = '';
    $return['act'] = $param['act'];
}else if ( $param['act'] == 'read' ) { //อ่าน project ทั้งหมดของ user นี้
    $settings = $db->query('SELECT id, email, channel, status FROM TemplateSubmissionSettings ORDER BY email')->fetchAll();
    $row = array();
    $i = 0;
    foreach ($settings as $setting) {
        $row[$i] = $setting;
        $i++;
    }

    $return['result'] = 'success';
    $return['msg'] = count($row).' emails found';
    $return['data'] = $row;
}else if ( $param['act'] == 'del' ){ //ลบ project ตามที่ส่ง projectID มา
    if (!empty($param['delID'])) {
        /*$project = $db->query('DELETE FROM `tb_project` WHERE `projectID` = ?'
            ,$param['delID']);*/
        $return['result'] = 'success';
    }
}else if ( $param['act'] == 'setEdit' ){ //อ่าน project ตามที่ส่ง projectID แค่ 1 แถว มาว่ามีรายละเอียดอะไร
    $project = array();
    /*$project = $db->query('SELECT * FROM `tb_project` WHERE `projectID` = ?',$param['editID'])->fetchArray();*/
    $row = array();
    $row[] = $project;
    $return['result'] = 'success';
    $return['data'] = $row;
}else if ( $param['act'] == 'update' ) { //อัพเดท project
    /*$project = $db->query('UPDATE tb_project SET `projectName` = ?, `shopTypeID` = ?, `selectedTemplate` = ?, `countryID` = ?  WHERE `projectID` = ?'
        , $param['name'], $param['shopTypeID'], $param['selectedTemplate'], $param['country'], $param['editID']);*/

    $return['result'] = 'success';
    $return['msg'] = 'project updated';
}else if ( $param['act'] == 'add' ) {  //เพิ่ม project
    /*$project = $db->query('INSERT INTO `tb_project`(`projectName`, `shopTypeID`, `selectedTemplate`, `statusID`, `projectOwner`, `countryID`) VALUES (?,?,?,?,?,?)'
        , $param['name'], $param['shopTypeID'], $param['selectedTemplate'], 1, $param['ownerID'], $param['country']);*/

    $return['result'] = 'success';
    $return['msg'] = 'project created';
}
echo json_encode($return);  //ข้อความตอบกลับให้ ajax หลังทำงานตามเคสด้านบนเสร็จ
