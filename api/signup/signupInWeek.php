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
    $selectReport = $db->query('SELECT dataLogs FROM logssignup WHERE createAt BETWEEN ? AND ? ORDER BY createAt ASC;', $startDate, $endDate)->fetchAll();
    $totalSignups = count($selectReport);
}

// echo "Date : ".$day."<br>";
// echo "Index : ".$dateIndex."<br>";
// echo "Start date : ".$startDate."<br>";
// echo "End date : ".$endDate;
?>

<p><b>**New Signups**</b> (Total:<?php echo $totalSignups; ?> )</p>

<table cellpadding="10" cellspacing="0" border="1">
    <tr>
        <th>#</th>
        <th>Shop Name</th>
        <th>Type</th>
        <th>Country</th>
    </tr>
<?php
    $index = 1;
    if ($totalSignups == 0) {
        ?>
        <tr>
            <td colspan="4">No items</td>
        </tr>
        <?php
    } else {
        foreach ($selectReport as $row) {

            $dataLogs = json_decode($row["dataLogs"], true);
            $shopName = $dataLogs["ShopName"];
            $customerType = $dataLogs["CustomerType"];
            $country = $dataLogs["Country"];

            ?>
            <tr>
                <td><?php echo $index++; ?></td>
                <td><?php echo $shopName ?: "-"; ?></td>
                <td><?php echo $customerType ?: "-"; ?></td>
                <td><?php echo $country ?: "-"; ?></td>
            </tr>
        <?php 
        } //foreach
    } //else
?>
</table>

