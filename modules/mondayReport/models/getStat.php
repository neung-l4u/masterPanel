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
$sumDate = $db->query('SELECT DATE(whenTime) AS "day",COUNT(mo.id) AS "count" FROM mondayslowreportlogs mo GROUP BY DATE(whenTime) ORDER BY DATE(whenTime) DESC LIMIT 0,11;')->fetchAll();

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
                <?php $i=1;
                foreach ($sumDate as $row){ ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <?php echo $i;?>.
                            <a href="viewDateDetail.php?date=<?php echo $row['day']; ?>" target="_blank"><svg height="1rem" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="#0d6efd" d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/></svg></a>
                            <?php echo formatDate($row['day']); ?>
                        </div>
                        <span class="badge bg-primary rounded-pill"><?php echo number_format($row['count']); ?></span>
                    </li>
                    <?php
                    $i++;
                }//foreach
                ?>
            </ul>
        </td>
    </tr>
</table>