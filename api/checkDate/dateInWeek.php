<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';
//https://report.localforyou.com/api/checkDate/dateInWeek.php?day=2025-06-09
$day = $_GET["day"];

$obj_day = new DateTime($day);
$dateIndex = $obj_day->format('w');

$endDate = 6-$dateIndex;

$obj_startDate = new DateTime($day);
$startDate = $obj_startDate->modify("-$dateIndex day")->format('Y-m-d');

$obj_endDate = new DateTime($day);
$endDate = $obj_endDate->modify("+$endDate days")->format('Y-m-d');
?>

{
    "startDate": "<?php echo $startDate; ?>",
    "endDate": "<?php echo $endDate; ?>"
}