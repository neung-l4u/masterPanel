<?php
require_once '../../assets/db/db.php';
require_once '../../assets/db/initDB.php';
//https://report.localforyou.com/api/checkDate/dateInMonthly.php?day=2025-06-15
$day = $_GET["day"];

$obj_day = new DateTime($day);

$startDate = $obj_day->format('Y-m-01');

$obj_endDate = new DateTime($day);
$endDate = $obj_endDate->format('Y-m-t');

$obj_prevMonth = new DateTime($startDate);
$obj_prevMonth->modify('-1 month');
$firstDayOfPrevMonth = $obj_prevMonth->format('Y-m-01');
?>

{
    "startDate": "<?php echo $startDate; ?>",
    "endDate": "<?php echo $endDate; ?>",
    "firstDayOfPrevMonth": "<?php echo $firstDayOfPrevMonth; ?>"
}