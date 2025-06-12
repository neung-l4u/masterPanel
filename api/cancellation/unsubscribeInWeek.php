<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';
$currentDate = date('Y-m-d');

$act = !empty($_GET['act']) ? $_GET['act'] : '';
$day = !empty($_GET['day']) ? $_GET['day'] : $currentDate;


$startDay = $day;
$endDay = $day;



$dayTimeStamp = strtotime($day);//เปลี่ยนเป็นเวลา

$date=date_create($day);//เอาค่าที่ทำเป็นเวลา (ทำเป็น ฟอแมท)
$whatDay = date_format($date,"w");//เอาค่ามาหา w(index ของสัปดาห์) ได้ 0 1 2 3 4 5 6

$endDay = 6-$whatDay;


// Create a DateTime object
$obj_startDate = new DateTime($day);//ทำ obj start date
$obj_endDate = new DateTime($day);//ทำ obj end date


// Add 7 days to the date
$obj_startDate->modify('-'.$whatDay.' days');//เอาค่าที่ได้ไปลบตัวมันเอง ตัวอย่าง วันที่ 3 วัน - วันปัจจุบัน : 1999-08-11 = 1999-08-08
$obj_endDate->modify('+'.$endDay.' days');//เอาค่าที่ได้ไปบวกค่าที่ได้ ตัวอย่าง วันที่ได้ 3 วัน(ตัวแปรนี้เอาไป -6 แล้ว) + วันปัจจุบัน : 1999-08-11 = 1999-08-14

// Format and output the modified date
$startDayForWeek = $obj_startDate->format('Y-m-d 00:00:00'); //เอาตั้งแต่เวลา 00:00:00 ของวันเรา
$lastDayForWeek = $obj_endDate->format('Y-m-d 23:59:59'); //เอาถึงแค่ 23:59:59


/*if ($act == 'newPerWeek') {
    $row = $db->query('SELECT * FROM `Cancellation` WHERE timestamp BETWEEN ? AND ?;',$startDayForWeek ,$lastDayForWeek)->fetchArray();
    $shopname = !empty($row["shopname"]) ? $row["shopname"] : "-";

}*/




?>
<div><?php echo "Time Stamp: ".$whatDay;?></div><br>
<div><?php echo "input Day : ".$day;?></div>
<div><?php echo "Start Day : ".$startDayForWeek;?></div>
<div><?php echo "End Day : ".$lastDayForWeek;?></div>
