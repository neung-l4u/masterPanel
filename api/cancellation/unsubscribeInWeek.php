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


if ($act == 'newPerWeek') {
    $updateQue = $db->query('UPDATE Cancellation SET reported_at = NOW(), gen_report = 1  WHERE timestamp BETWEEN ? AND ? ;',$startDayForWeek ,$lastDayForWeek);
    $selectQue = $db->query('SELECT * FROM Cancellation WHERE timestamp BETWEEN ? AND ? ORDER BY county ASC ;',$startDayForWeek ,$lastDayForWeek)->fetchAll();
    $totalUnsubscribes = count($selectQue);
    
    // Group data by country
    $groupedByCountry = [];
    $processedShops = [];
    foreach ($selectQue as $row) {
        $country = $row["county"] ?: "Unknown";
        $shopname = $row["shopname"];
        
        // Skip duplicate shopnames
        if (in_array($shopname, $processedShops)) {
            continue;
        }
        $processedShops[] = $shopname;
        
        if (!isset($groupedByCountry[$country])) {
            $groupedByCountry[$country] = [];
        }
        $groupedByCountry[$country][] = $row;
    }
}
?>

<?php
$totalRow = 0;
if (!empty($groupedByCountry)) {
    // Calculate total first
    foreach ($groupedByCountry as $rows) {
        $totalRow += count($rows);
    }
?>
<p style="font: 18px roboto, sans-serif; margin-top: 20px;"><b>**Unsubscribe Requests**</b> <span style="color: red;">(Customer: -<?php echo $totalRow; ?>)</span></p>
<?php
    foreach ($groupedByCountry as $country => $rows) {
        $countryCount = count($rows);
?>
<p style="font: 14px roboto, sans-serif; margin-top: 20px;"><b>Country: <?php echo $country; ?></b> (<?php echo $countryCount; ?> shops)</p>
<table cellpadding="10" cellspacing="0" border="1" style="font: 14px roboto, sans-serif; margin-bottom: 20px;">
    <tr style="background-color: #d6e6f4; border: 1px solid;">
        <th>#</th>
        <th>Shop Name</th>
        <th>Type</th>
        <th>Reason</th>
    </tr>
    <?php
    $index = 1;
    foreach ($rows as $row) { 
    ?>
        <tr style="border: 1px solid;">
            <td><?php echo $index++; ?></td>
            <td><?php echo $row["shopname"] ?: "-"; ?></td>
            <td><?php echo $row["industrial"] ?: "-"; ?></td>
            <td><?php if ($row["reason"] == "other"){
                echo $row["other"] ?: "-";
                }elseif (!empty($row["reason"])){
                 echo $row["reason"];
                }else {
                 echo "-";
                }
             ?></td>
        </tr>
    <?php 
    }//foreach rows
    ?>
</table>
<?php
    }//foreach country
} else {
    echo "<p>ไม่พบข้อมูลในสัปดาห์นี้</p>";
}//if
?>