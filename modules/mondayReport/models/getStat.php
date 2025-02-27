<?php
session_start();
global $db;

//เรียกใช้งาน object
include '../assets/db/db.php';
include "../assets/db/initDB.php";
require_once '../assets/PHP/shareFunction.php';

$return['result'] = '';
$return['msg'] = '';

$sumAll = $db->query('SELECT COUNT(mo.id) AS "count" FROM mondayslowreportlogs mo')->fetchArray();
$sumDate = $db->query('SELECT DATE(whenTime) AS "day",COUNT(mo.id) AS "count" FROM mondayslowreportlogs mo GROUP BY DATE(whenTime) ORDER BY DATE(whenTime) DESC LIMIT 0,10;')->fetchAll();

?>
<table class="table table-hover table-borderless">
    <tr>
        <th style="width: 120px;">Summary All</th>
        <td style="text-align: right; padding-right: 1rem;">
            <?php echo number_format($sumAll['count']); ?>  times.
        </td>
    </tr>

    <tr>
        <td colspan="2" style="text-align: left; padding-left: 1rem;">
            <b>Date: </b><small class="text-muted">(Last 10 days)</small>
            <ul class="list-group">
                <?php foreach ($sumDate as $row){ ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo formatDate($row['day']); ?>
                        <span class="badge bg-primary rounded-pill"><?php echo number_format($row['count']); ?></span>
                    </li>
                <?php } ?>
            </ul>
        </td>
    </tr>
</table>