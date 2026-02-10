<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';
//https://report.localforyou.com/api/checkDate/dateInYearly.php?day=2026-06-15
$day = $_GET["day"];

$obj_day = new DateTime($day);

$startDate = $obj_day->format('Y-01-01');
$endDate = $obj_day->format('Y-12-31');

$obj_prevYear = new DateTime($startDate);
$obj_prevYear->modify('-1 year');
$firstDayOfPrevYear = $obj_prevYear->format('Y-01-01');
?>

{
    "startDate": "<?php echo $startDate; ?>",
    "endDate": "<?php echo $endDate; ?>",
    "firstDayOfPrevYear": "<?php echo $firstDayOfPrevYear; ?>"
}
