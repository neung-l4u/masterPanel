<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';
$act = $_GET['act'];
$day = $_GET["day"];

$obj_day = new DateTime($day);
$dateIndex = $obj_day->format('w');

$endDate = 6-$dateIndex;

$obj_startDate = new DateTime($day);
$startDate = $obj_startDate->modify("-$dateIndex day")->format('Y-m-d 00:00:00');

$obj_endDate = new DateTime($day);
$endDate = $obj_endDate->modify("+$endDate days")->format('Y-m-d 23:59:59');

if ($act = 'newPerWeek') {
    $updateReport = $db->query('UPDATE logssignup SET gen_report = 1, reported_at = NOW() WHERE createAt BETWEEN ? AND ? ;', $startDate, $endDate);
    $selectReport = $db->query('SELECT dataLogs FROM logssignup WHERE createAt BETWEEN ? AND ? AND test = 0 ORDER BY createAt ASC;', $startDate, $endDate)->fetchAll();
    $totalSignups = count($selectReport);
    
    // Group data by country
    $groupedByCountry = [];
    $processedShops = [];
    foreach ($selectReport as $row) {
        $dataLogs = json_decode($row["dataLogs"], true);
        $shopName = $dataLogs["ShopName"];
        $country = $dataLogs["Country"] ?: "Unknown";
        
        // Skip duplicate shopnames
        if (in_array($shopName, $processedShops)) {
            continue;
        }
        $processedShops[] = $shopName;
        
        if (!isset($groupedByCountry[$country])) {
            $groupedByCountry[$country] = [];
        }
        $groupedByCountry[$country][] = $dataLogs;
    }
}

// echo "Date : ".$day."<br>";
// echo "Index : ".$dateIndex."<br>";
// echo "Start date : ".$startDate."<br>";
// echo "End date : ".$endDate;
?>

<?php
$totalRow = 0;
if (!empty($groupedByCountry)) {
    // Calculate total first
    foreach ($groupedByCountry as $rows) {
        $totalRow += count($rows);
    }
?>
<p style="font: 18px roboto, sans-serif; margin-top: 20px;"><b>**New Signups**</b> <span style="color: green;">(Customer: +<?php echo $totalRow; ?>)</span></p>
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
        <th>Product</th>
    </tr>
    <?php
    $index = 1;
    foreach ($rows as $dataLogs) { 
    ?>
        <tr style="border: 1px solid;">
            <td><?php echo $index++; ?></td>
            <td><?php echo $dataLogs["ShopName"] ?: "-"; ?></td>
            <td><?php echo $dataLogs["CustomerType"] ?: "-"; ?></td>
            <td><?php echo $dataLogs["MainProduct"] ?: "-"; ?></td>
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
