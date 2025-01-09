<?php
global $db;

//เรียกใช้งาน object
include '../assets/db/db.php';
include "../assets/db/initDB.php";

//รับค่าที่ส่งมาเก็บใน Array $param
$param['act'] = (!empty($_REQUEST['act'])) ? trim($_REQUEST['act']) : ''; //ใช้เลือกเคสด้านล่างที่ต้องทำ
$param['status'] = (!empty($_REQUEST['status'])) ? trim($_REQUEST['status']) : '1'; //1=On , 2=Send
$param['editID'] = (!empty($_REQUEST['editID'])) ? trim($_REQUEST['editID']) : ''; //จะส่งมาเฉพาะเคส setEdit , Update
$param['delID'] = (!empty($_REQUEST['delID'])) ? trim($_REQUEST['delID']) : ''; //จะส่งมาเฉพาะเคส del
$param['ownerID'] = (!empty($_REQUEST['ownerID'])) ? trim($_REQUEST['ownerID']) : ''; //มาจาก session ที่ frontend อ่านมาให้
$param['recipient'] = (!empty($_REQUEST['recipient'])) ? trim($_REQUEST['recipient']) : '';
$param['channel'] = (!empty($_REQUEST['channel'])) ? trim($_REQUEST['channel']) : '';

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
    $recipient = $db->query('DELETE FROM `TemplateSubmissionSettings` WHERE `id` = ?',$param['delID']);
        $return['result'] = 'success';
    }
}else if ( $param['act'] == 'setEdit' ){ //อ่าน project ตามที่ส่ง projectID แค่ 1 แถว มาว่ามีรายละเอียดอะไร
    $recipient = array();
    $recipient = $db->query('SELECT * FROM `TemplateSubmissionSettings` WHERE `id` = ?',$param['editID'])->fetchArray();
    $row = array();
    $row[] = $recipient;
    $return['result'] = 'success';
    $return['data'] = $row;
}else if ( $param['act'] == 'update' ) { //อัพเดท project
    $recipient = $db->query('UPDATE TemplateSubmissionSettings SET `email` = ?, `channel` = ?, `status` = ? WHERE `id` = ?'
        , $param['recipient'], $param['channel'], $param['status'], $param['editID']);
    $return['result'] = 'success';
    $return['msg'] = 'project updated';
}else if ( $param['act'] == 'add' ) {  //เพิ่ม project
    $recipient = $db->query('INSERT INTO `TemplateSubmissionSettings`(`email`, `channel`, `status`) VALUES (?,?,?)'
        , $param['recipient'], $param['channel'], $param['status']);
    $return['result'] = 'success';
    $return['msg'] = 'project created';
}
echo json_encode($return);  //ข้อความตอบกลับให้ ajax หลังทำงานตามเคสด้านบนเสร็จ
