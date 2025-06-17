<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';
$act = $_GET['act'];
$getDateString = $_GET["day"];

$obj_getDate = new DateTime($getDateString);
$dateIndex = $obj_getDate->format('w');

$endDate = 6-$dateIndex;

$obj_startDate = new DateTime($getDateString);
$startDate = $obj_startDate->modify("-$dateIndex day")->format('Y-m-d 00:00:00');

$obj_endDate = new DateTime($getDateString);
$endDate = $obj_endDate->modify("+$endDate days")->format('Y-m-d 23:59:59');

if ($act = 'newPerWeek') {
    $updateReport = $db->query('UPDATE logssignup SET gen_report = 1, reported_at = NOW() WHERE createAt BETWEEN ? AND ? ;', $startDate, $endDate);
    $selectReport = $db->query('SELECT dataLogs FROM logssignup WHERE createAt BETWEEN ? AND ? ORDER BY createAt ASC;', $startDate, $endDate)->fetchAll();
    $totalSignups = count($selectReport);
}

// echo "Date : ".$getDateString."<br>";
// echo "Index : ".$dateIndex."<br>";
// echo "Start date : ".$startDate."<br>";
// echo "End date : ".$endDate;
?>

<h2><b>**New Signups**</b> (Total:<?php echo $totalSignups; ?> )</h2>

<table cellpadding="10" cellspacing="0" border="1">
    <tr style="background-color:#f2f2f2" bgcolor="#f2f2f2" >
        <th style="font-size: 16px;">#</th>
        <th style="font-size: 16px;">Shop</th>
        <th style="font-size: 16px;">Type</th>
        <th style="font-size: 16px;">Country</th>
    </tr>
<?php
    $index = 1;
    foreach ($selectReport as $row) {

        $dataLogs = json_decode($row["dataLogs"], true);
        $shopName = $dataLogs["ShopName"];
        $customerType = $dataLogs["CustomerType"];
        $country = $dataLogs["Country"];
        
        ?>
        <tr>
            <td style="font-size: 14px;"><?php echo $index++; ?></td>
            <td style="font-size: 14px;"><?php echo $shopName ?: "-"; ?></td>
            <td style="font-size: 14px;"><?php echo $customerType ?: "-"; ?></td>
            <td style="font-size: 14px;"><?php echo $country ?: "-"; ?></td>
        </tr>
    <?php } ?>
</table>

