<?php
global $db, $date;
$timestamp = time();
$thisYear = date("Y", $timestamp);
$thisMonth = date("m", $timestamp);
$thisDay = date("d-m-Y", $timestamp);
$yearScope = range(($thisYear-3),($thisYear+1),1);
$monthScope = range(1,12,1);

$grandTotal = 0;
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">
                    <svg class="nav-icon mr-2" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 384 512"><path d="M48 0C21.5 0 0 21.5 0 48V464c0 26.5 21.5 48 48 48h96V432c0-26.5 21.5-48 48-48s48 21.5 48 48v80h96c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48H48zM64 240c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16V240zm112-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H176c-8.8 0-16-7.2-16-16V240c0-8.8 7.2-16 16-16zm80 16c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H272c-8.8 0-16-7.2-16-16V240zM80 96h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16V112c0-8.8 7.2-16 16-16zm80 16c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H176c-8.8 0-16-7.2-16-16V112zM272 96h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H272c-8.8 0-16-7.2-16-16V112c0-8.8 7.2-16 16-16z" fill="#000000" /></svg>
                    Revenue Tracking / Company Stats
                </h3>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php">My Desk</a></li>
                    <li class="breadcrumb-item"><a href="#">Revenue Tracking</a></li>
                    <li class="breadcrumb-item active">Company Stats</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body d-flex">
                        <div class="mr-3 d-flex  align-items-center">
                            <label for="selectedYear" class="mr-3">Year</label>
                            <select id="selectedYear" class="form-control" onchange="loadData();">
                                <?php
                                foreach($yearScope as $no => $year) { ?>
                                    <option value="<?php echo $year; ?>" <?php echo ($year == $thisYear) ? 'selected' : ''; ?> ><?php echo $year; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mr-3 d-flex align-items-center">
                            <label for="selectedMonth" class="mr-3">Month</label>
                            <select id="selectedMonth" class="form-control" onchange="loadData();">
                                <?php
                                foreach($monthScope as  $month) {
                                    $month_name = date("F", mktime(0, 0, 0, $month, 10));
                                    ?>
                                    <option value="<?php echo sprintf("%02d", $month); ?>" <?php echo (sprintf("%02d", $month) == $thisMonth) ? 'selected' : ''; ?> ><?php echo sprintf("%02d", $month).' : '.$month_name; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mr-3 d-flex align-items-center">
                            <a href="#" onclick="setToToday();">Today</a>
                        </div>
                    </div>
                </div>
            </div><!-- /.col-md-12 -->
        </div><!-- /.row -->

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-10">
                                <p class="text-center font-weight-bold">
                                    <svg class="nav-icon mr-2" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM609.3 512H471.4c5.4-9.4 8.6-20.3 8.6-32v-8c0-60.7-27.1-115.2-69.8-151.8c2.4-.1 4.7-.2 7.1-.2h61.4C567.8 320 640 392.2 640 481.3c0 17-13.8 30.7-30.7 30.7zM432 256c-31 0-59-12.6-79.3-32.9C372.4 196.5 384 163.6 384 128c0-26.8-6.6-52.1-18.3-74.3C384.3 40.1 407.2 32 432 32c61.9 0 112 50.1 112 112s-50.1 112-112 112z" fill="#000000" /></svg>
                                    Employees
                                </p>
                            </div>
                            <div class="col-2">
                                <button id="btnModal" type="button" class="btn btn-primary btn-sm" onclick="openForm('formEmployee');">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M352 320c88.4 0 160-71.6 160-160c0-15.3-2.2-30.1-6.2-44.2c-3.1-10.8-16.4-13.2-24.3-5.3l-76.8 76.8c-3 3-7.1 4.7-11.3 4.7H336c-8.8 0-16-7.2-16-16V118.6c0-4.2 1.7-8.3 4.7-11.3l76.8-76.8c7.9-7.9 5.4-21.2-5.3-24.3C382.1 2.2 367.3 0 352 0C263.6 0 192 71.6 192 160c0 19.1 3.4 37.5 9.5 54.5L19.9 396.1C7.2 408.8 0 426.1 0 444.1C0 481.6 30.4 512 67.9 512c18 0 35.3-7.2 48-19.9L297.5 310.5c17 6.2 35.4 9.5 54.5 9.5zM80 408a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" fill="#FFFFFF" /></svg>
                                    Manage Data
                                </button>
                            </div>
                        </div>
                        <div class="px-5">
                            <div id="placeEmployeeStat">
                                <table class="table table-hover table-borderless table-sm mb-3">
                                    <tbody>
                                    <?php
                                    unset($new, $leave, $all, $employee, $cs, $am, $se, $hr, $it, $ceo, $ot);
                                    $new = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE (em.`activeDate` > (curdate() - interval 30 day)) AND em.`status` = ? AND em.`teamID` = te.`id`;', 1)->fetchAll();
                                    $employee["new"] = count($new);
                                    $leave = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE (em.`activeDate` > (curdate() - interval 30 day)) AND em.`status` = ? AND em.`teamID` = te.`id`;', 0)->fetchAll();
                                    $employee["leave"] = count($leave);
                                    $all = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`status`, em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id`;', 1)->fetchAll();
                                    $employee["all"] = count($all);
                                    $allAndLeave = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`status`, em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`teamID` = te.`id`;')->fetchAll();
                                    $employee["allAndLeave"] = count($allAndLeave);

                                    $cs = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 1)->fetchAll();
                                    $employee["cs"] = count($cs);
                                    $amAU = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 2)->fetchAll();
                                    $employee["amAU"] = count($amAU);
                                    $amNZ = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 10)->fetchAll();
                                    $employee["amNZ"] = count($amNZ);
                                    $amUK = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 11)->fetchAll();
                                    $employee["amUK"] = count($amUK);
                                    $amUS = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 8)->fetchAll();
                                    $employee["amUS"] = count($amUS);
                                    $se = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 3)->fetchAll();
                                    $employee["se"] = count($se);
                                    $hr = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 4)->fetchAll();
                                    $employee["hr"] = count($hr);
                                    $it = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 5)->fetchAll();
                                    $employee["it"] = count($it);
                                    $ceo = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 6)->fetchAll();
                                    $employee["ceo"] = count($ceo);
                                    $ot = $db->query('SELECT em.`id`, em.`nickName`, em.`fullName`, te.`name` AS "team", te.`fullName` AS "teamFull", em.`activeDate` FROM `Employees` em, `Team` te WHERE em.`status` = ? AND em.`teamID` = te.`id` AND em.`teamID` = ?;', 1, 7)->fetchAll();
                                    $employee["ot"] = count($ot);

                                    $staffs["OverAll"] = [
                                        ['New Staffs',$employee["new"]],
                                        ['Staff Leaving',$employee["leave"]],
                                        ['Total Staffs',$employee["all"]]
                                    ];
                                    unset($row);
                                    foreach ($staffs["OverAll"] as $row){ ?>
                                    <tr>
                                        <td><?php echo $row[0]; ?></td>
                                        <td class="text-right"><span class="font-weight-bold"><?php echo $row[1]; ?></span> <small class="text-muted">Person(s)</small></td>
                                    </tr>
                                    <?php }//foreach ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="row">
                                <div class="col-10">
                                    <p class="text-center font-weight-bold">
                                        <svg class="nav-icon mr-2" xmlns="http://www.w3.org/2000/svg" height="1.5em" viewBox="0 0 640 512"><path d="M72 88a56 56 0 1 1 112 0A56 56 0 1 1 72 88zM64 245.7C54 256.9 48 271.8 48 288s6 31.1 16 42.3V245.7zm144.4-49.3C178.7 222.7 160 261.2 160 304c0 34.3 12 65.8 32 90.5V416c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V389.2C26.2 371.2 0 332.7 0 288c0-61.9 50.1-112 112-112h32c24 0 46.2 7.5 64.4 20.3zM448 416V394.5c20-24.7 32-56.2 32-90.5c0-42.8-18.7-81.3-48.4-107.7C449.8 183.5 472 176 496 176h32c61.9 0 112 50.1 112 112c0 44.7-26.2 83.2-64 101.2V416c0 17.7-14.3 32-32 32H480c-17.7 0-32-14.3-32-32zm8-328a56 56 0 1 1 112 0A56 56 0 1 1 456 88zM576 245.7v84.7c10-11.3 16-26.1 16-42.3s-6-31.1-16-42.3zM320 32a64 64 0 1 1 0 128 64 64 0 1 1 0-128zM240 304c0 16.2 6 31 16 42.3V261.7c-10 11.3-16 26.1-16 42.3zm144-42.3v84.7c10-11.3 16-26.1 16-42.3s-6-31.1-16-42.3zM448 304c0 44.7-26.2 83.2-64 101.2V448c0 17.7-14.3 32-32 32H288c-17.7 0-32-14.3-32-32V405.2c-37.8-18-64-56.5-64-101.2c0-61.9 50.1-112 112-112h32c61.9 0 112 50.1 112 112z" fill="#000000" /></svg>
                                        Teams
                                    </p>
                                </div>
                                <div class="col-2">

                                </div>
                            </div>
                            <div id="placeTeamsStat" class="mb-5">
                                <table class="table table-hover table-borderless table-sm mb-3">
                                    <tbody>
                                    <?php
                                    $staffs["Teams"] = [
                                        ['CS Team',$employee["cs"]],
                                        ['AM AU Team',$employee["amAU"]],
                                        ['AM NZ Team',$employee["amNZ"]],
                                        ['AM USA Team',$employee["amUS"]],
                                        ['AM UK Team',$employee["amUK"]],
                                        ['SE Team',$employee["se"]],
                                        ['HR Team',$employee["hr"]],
                                        ['IT Team',$employee["it"]],
                                        ['CEO Team',$employee["ceo"]],
                                        ['Other Team',$employee["ot"]]
                                    ];
                                    unset($row);
                                    foreach ($staffs["Teams"] as $row){ ?>
                                    <tr>
                                        <td><?php echo $row[0]; ?></td>
                                        <td class="text-right"><span class="font-weight-bold"><?php echo $row[1]; ?></span> <small class="text-muted">Person(s)</small></td>
                                    </tr>
                                    <?php }//foreach ?>
                                    </tbody>
                                </table>
                            </div>
                            <section class="mb-3">
                                <h6 class="font-weight-bold">
                                    <svg class="nav-icon mr-2" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM472 200H616c13.3 0 24 10.7 24 24s-10.7 24-24 24H472c-13.3 0-24-10.7-24-24s10.7-24 24-24z" fill="#000000" /></svg>
                                    Leaving staff:
                                </h6>
                                <div id="placeEmployeeLeaving">
                                    <ul>
                                        <?php
                                        $staffs["Leaving"] = [];
                                        foreach ($leave as $leaveStaff){
                                            $staffs["Leaving"][] = ['<span class="text-danger">'.$leaveStaff['nickName']."</span> : ".$leaveStaff['fullName'], $leaveStaff['teamFull']];
                                        }// foreach

                                        unset($row);
                                        foreach ($staffs["Leaving"] as $row){ ?>
                                            <li><small><?php echo $row[0]; ?> <span class="text-info"><< <?php echo $row[1]; ?></span></small></li>
                                        <?php }//foreach ?>
                                        <?php if(count($staffs["Leaving"])<=0){ echo '<li class="text-muted"><small>There are no leaving employees.</small></li>'; } ?>
                                    </ul>
                                </div>
                            </section>
                            <section>
                                <h6 class="font-weight-bold">
                                    <svg class="nav-icon mr-2" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM504 312V248H440c-13.3 0-24-10.7-24-24s10.7-24 24-24h64V136c0-13.3 10.7-24 24-24s24 10.7 24 24v64h64c13.3 0 24 10.7 24 24s-10.7 24-24 24H552v64c0 13.3-10.7 24-24 24s-24-10.7-24-24z" fill="#000000" /></svg>
                                    Latest staff:
                                </h6>
                                <div id="placeEmployeeNew">
                                    <ul>
                                        <?php
                                        $staffs["Latest"] = [];
                                        foreach ($new as $newStaff){
                                            $staffs["Latest"][] = ['<span class="text-primary">'.$newStaff['nickName']."</span> : ".$newStaff['fullName'], $newStaff['team']." (".reFormatDate($newStaff['activeDate']).")"];
                                        }// foreach

                                        unset($row);
                                        foreach ($staffs["Latest"] as $row){ ?>
                                            <li><small><?php echo $row[0]; ?> <span class="text-info">>> <?php echo $row[1]; ?></span></small></li>
                                        <?php }//foreach ?>
                                        <?php if(count($staffs["Latest"])<=0){ echo '<li class="text-muted"><small>There are no new employees.</small></li>'; } ?>
                                    </ul>
                                </div>
                            </section>
                        </div><!-- wrap -->
                    </div>
                </div>
            </div><!-- /.col-md-6 -->

            <div class="col-lg-6">
                <div class="d-flex flex-column">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-10">
                                    <p class="text-center font-weight-bold">
                                        <img class="nav-icon mr-2" src="assets/img/money-dollar.svg" alt="Dollar Bill" width="24">
                                        Bkk Office Expense
                                    </p>
                                </div>
                                <div class="col-2">
                                    <button id="btnModal" type="button" class="btn btn-primary btn-sm" onclick="openForm('formBkkExpense');">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M352 320c88.4 0 160-71.6 160-160c0-15.3-2.2-30.1-6.2-44.2c-3.1-10.8-16.4-13.2-24.3-5.3l-76.8 76.8c-3 3-7.1 4.7-11.3 4.7H336c-8.8 0-16-7.2-16-16V118.6c0-4.2 1.7-8.3 4.7-11.3l76.8-76.8c7.9-7.9 5.4-21.2-5.3-24.3C382.1 2.2 367.3 0 352 0C263.6 0 192 71.6 192 160c0 19.1 3.4 37.5 9.5 54.5L19.9 396.1C7.2 408.8 0 426.1 0 444.1C0 481.6 30.4 512 67.9 512c18 0 35.3-7.2 48-19.9L297.5 310.5c17 6.2 35.4 9.5 54.5 9.5zM80 408a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" fill="#FFFFFF" /></svg>
                                        Manage Data
                                    </button>
                                </div>
                            </div>
                            <?php
                            $expenses["bkkOffice"] = [];
                            $bkkOffice = $db->query('SELECT ed.`id`, et.`typeSection` AS `type`, et.`typeName` AS `name`, ed.`value`, ed.`month`, ed.`year`FROM `ExpenseDetail` ed, `ExpenseType` et WHERE ed.`typeID` = et.`id` AND ed.`month` = ? AND ed.`year` = ? AND et.typeSection = ? ORDER BY et.`typeSection`, et.`typeName`;',$thisMonth, $thisYear, "bkkOffice" )->fetchAll();
                            $expense["bkkOffice"] = count($bkkOffice);

                            $i = 0;
                            $total = 0;
                            foreach ($bkkOffice as $info){
                                $expenses["bkkOffice"][$i] =  [ $info["name"], $info["value"], $info["id"] ];
                                $total += $info["value"];
                                $grandTotal += $info["value"];
                                $i++;
                            }
                            ?>
                            <div id="placeBkkOffice">
                                <table class="table table-hover table-sm table-borderless">
                                    <tbody>
                                    <?php
                                    $i=1;
                                    if(count($expenses["bkkOffice"])<=0) { ?>
                                        <tr>
                                            <td></td>
                                            <td class="text-center text-muted"><small> -- No Record -- </small></td>
                                            <td></td>
                                        </tr>
                                    <?php }else{
                                        foreach ($expenses["bkkOffice"] as $row){ ?>
                                            <tr>
                                                <th scope="row"><?php echo $i; ?></th>
                                                <td><?php echo $row[0]; ?></td>
                                                <td class="text-right">THB <?php echo number_format($row[1],2); ?></td>
                                            </tr>
                                        <?php $i++; }}//else foreach ?>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td></td>
                                        <th class="text-right">Total</th>
                                        <th class="text-right">THB <?php echo number_format($total,2); ?></th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-10">
                                    <p class="text-center font-weight-bold">
                                        <img class="nav-icon mr-2" src="assets/img/money-dollar.svg" alt="Dollar Bill" width="24">
                                        TH Operation cost
                                    </p>
                                </div>
                                <div class="col-2">
                                    <button id="btnModal" type="button" class="btn btn-primary btn-sm" onclick="openForm('formThOperation');">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M352 320c88.4 0 160-71.6 160-160c0-15.3-2.2-30.1-6.2-44.2c-3.1-10.8-16.4-13.2-24.3-5.3l-76.8 76.8c-3 3-7.1 4.7-11.3 4.7H336c-8.8 0-16-7.2-16-16V118.6c0-4.2 1.7-8.3 4.7-11.3l76.8-76.8c7.9-7.9 5.4-21.2-5.3-24.3C382.1 2.2 367.3 0 352 0C263.6 0 192 71.6 192 160c0 19.1 3.4 37.5 9.5 54.5L19.9 396.1C7.2 408.8 0 426.1 0 444.1C0 481.6 30.4 512 67.9 512c18 0 35.3-7.2 48-19.9L297.5 310.5c17 6.2 35.4 9.5 54.5 9.5zM80 408a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" fill="#FFFFFF" /></svg>
                                        Manage Data
                                    </button>
                                </div>
                            </div>
                            <?php
                            $expenses["THOperation"] = [];
                            $THOperation = $db->query('SELECT ed.`id`, et.`typeSection` AS `type`, et.`typeName` AS `name`, ed.`value`, ed.`month`, ed.`year`FROM `ExpenseDetail` ed, `ExpenseType` et WHERE ed.`typeID` = et.`id` AND ed.`month` = ? AND ed.`year` = ? AND et.typeSection = ? ORDER BY et.`typeSection`, et.`typeName`;',$thisMonth, $thisYear, "THOperation" )->fetchAll();
                            $expense["THOperation"] = count($THOperation);

                            $i = 0;
                            $total = 0;
                            foreach ($THOperation as $info){
                                $expenses["THOperation"][$i] =  [ $info["name"], $info["value"], $info["id"] ];
                                $total += $info["value"];
                                $grandTotal += $info["value"];
                                $i++;
                            }
                            ?>
                            <div id="placeTHOperation">
                                <table class="table table-hover table-sm table-borderless">
                                    <tbody>
                                    <?php
                                    $i=1;
                                    if(count($expenses["THOperation"])<=0) { ?>
                                        <tr>
                                            <td></td>
                                            <td class="text-center text-muted"><small> -- No Record -- </small></td>
                                            <td></td>
                                        </tr>
                                    <?php }else{
                                        foreach ($expenses["THOperation"] as $row){ ?>
                                            <tr>
                                                <th scope="row"><?php echo $i; ?></th>
                                                <td><?php echo $row[0]; ?></td>
                                                <td class="text-right">THB <?php echo number_format($row[1],2); ?></td>
                                            </tr>
                                            <?php $i++; }}//else foreach ?>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td></td>
                                        <th class="text-right">Total</th>
                                        <th class="text-right">THB <?php echo number_format($total,2); ?></th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div><!-- card -->

                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-10">
                                    <p class="text-center font-weight-bold">
                                        <img class="nav-icon mr-2" src="assets/img/money-dollar.svg" alt="Dollar Bill" width="24">
                                        CEO cost
                                    </p>
                                </div>
                                <div class="col-2">
                                    <button id="btnModal" type="button" class="btn btn-primary btn-sm" onclick="openForm('formCEOLiving');">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M352 320c88.4 0 160-71.6 160-160c0-15.3-2.2-30.1-6.2-44.2c-3.1-10.8-16.4-13.2-24.3-5.3l-76.8 76.8c-3 3-7.1 4.7-11.3 4.7H336c-8.8 0-16-7.2-16-16V118.6c0-4.2 1.7-8.3 4.7-11.3l76.8-76.8c7.9-7.9 5.4-21.2-5.3-24.3C382.1 2.2 367.3 0 352 0C263.6 0 192 71.6 192 160c0 19.1 3.4 37.5 9.5 54.5L19.9 396.1C7.2 408.8 0 426.1 0 444.1C0 481.6 30.4 512 67.9 512c18 0 35.3-7.2 48-19.9L297.5 310.5c17 6.2 35.4 9.5 54.5 9.5zM80 408a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" fill="#FFFFFF" /></svg>
                                        Manage Data
                                    </button>
                                </div>
                            </div>
                            <?php
                            $expenses["CEOLiving"] = [];
                            $CEOLiving = $db->query('SELECT ed.`id`, et.`typeSection` AS `type`, et.`typeName` AS `name`, ed.`value`, ed.`month`, ed.`year`FROM `ExpenseDetail` ed, `ExpenseType` et WHERE ed.`typeID` = et.`id` AND ed.`month` = ? AND ed.`year` = ? AND et.typeSection = ? ORDER BY et.`typeSection`, et.`typeName`;',$thisMonth, $thisYear, "CEOLiving" )->fetchAll();
                            $expense["CEOLiving"] = count($CEOLiving);

                            $i = 0;
                            $total = 0;
                            foreach ($CEOLiving as $info){
                                $expenses["CEOLiving"][$i] =  [ $info["name"], $info["value"], $info["id"] ];
                                $total += $info["value"];
                                $grandTotal += $info["value"];
                                $i++;
                            }
                            ?>
                            <div id="placeCEOLiving">
                                <table class="table table-hover table-sm table-borderless">
                                    <tbody>
                                    <?php
                                    $i=1;
                                    if(count($expenses["CEOLiving"])<=0) { ?>
                                        <tr>
                                            <td></td>
                                            <td class="text-center text-muted"><small> -- No Record -- </small></td>
                                            <td></td>
                                        </tr>
                                    <?php }else{
                                        foreach ($expenses["CEOLiving"] as $row){ ?>
                                            <tr>
                                                <th scope="row"><?php echo $i; ?></th>
                                                <td><?php echo $row[0]; ?></td>
                                                <td class="text-right">THB <?php echo number_format($row[1],2); ?></td>
                                            </tr>
                                            <?php $i++; }}//else foreach ?>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td></td>
                                        <th class="text-right">Total</th>
                                        <th class="text-right">THB <?php echo number_format($total,2); ?></th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div><!-- card -->

                </div><!-- flex-->
            </div><!-- /.col-md-6 -->
        </div><!-- /.row -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-center align-content-center align-items-center">
                            <h3 class="display-6 mr-5">
                                <img src="assets/img/money-dollar.svg" alt="Dollar Bill" width="36">
                                Total expenses
                            </h3>
                            <h3 class="display-6 text-primary" id="placeTotalExpense">
                                <?php //$grandTotal = 7450; ?>
                                THB <?php echo number_format($grandTotal,2); ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div><!-- /.col-md-12 -->
        </div><!-- /.row -->

    </div><!-- /.container-fluid -->

    <!-- Modal -->
    <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formModalLabel">Form Manage Company</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div class="card listBox" id="allEmployee" style="display:none;">
                        <div class="card-body" style="height: 200px; overflow: scroll;" id="addedDataEmployee">
                            <h6 class="font-weight-bold">Found : <?php echo $employee["allAndLeave"]; ?> people(s)</h6>
                            <table class="table table-hover table-sm table-borderless">
                            <?php
                            $i=1;
                            foreach ($allAndLeave as $row){ ?>
                                <tr>
                                    <td width="10"><?php echo $i; ?></td>
                                    <td><?php echo $row["nickName"]." (".$row["fullName"]; ?>)</td>
                                    <td class="text-right">
                                        <a href="#" onclick="setStatus(<?php echo $row["id"]; ?>, <?php echo $row["status"]; ?>);">
                                            <!--Status-->
                                            <?php
                                            if ($row["status"]){
                                                echo '<svg xmlns="http://www.w3.org/2000/svg" height="1.3em" viewBox="0 0 576 512"><path d="M192 64C86 64 0 150 0 256S86 448 192 448H384c106 0 192-86 192-192s-86-192-192-192H192zm192 96a96 96 0 1 1 0 192 96 96 0 1 1 0-192z" fill="#579125" /></svg>';
                                            }else{
                                                echo '<svg xmlns="http://www.w3.org/2000/svg" height="1.3em" viewBox="0 0 576 512"><path d="M384 128c70.7 0 128 57.3 128 128s-57.3 128-128 128H192c-70.7 0-128-57.3-128-128s57.3-128 128-128H384zM576 256c0-106-86-192-192-192H192C86 64 0 150 0 256S86 448 192 448H384c106 0 192-86 192-192zM192 352a96 96 0 1 0 0-192 96 96 0 1 0 0 192z" fill="#a9aaae" /></svg>';
                                            }
                                            ?>
                                        </a>
                                    </td>
                                    <td class="text-right">
                                        <a href="#" onclick="setDel(<?php echo $row["id"]; ?>);">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="1.2em" viewBox="0 0 576 512"><path d="M576 128c0-35.3-28.7-64-64-64H205.3c-17 0-33.3 6.7-45.3 18.7L9.4 233.4c-6 6-9.4 14.1-9.4 22.6s3.4 16.6 9.4 22.6L160 429.3c12 12 28.3 18.7 45.3 18.7H512c35.3 0 64-28.7 64-64V128zM271 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z" fill="#dc3545" /></svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php $i++; }//foreach ?>
                            </table>
                        </div>
                    </div>
                    <div class="card listBox" id="allBkk" style="display:none;">
                        <div class="card-body" style="height: 200px; overflow: scroll;" id="addedDataBkk">
                            <h6>Found <?php echo count($expenses["bkkOffice"]); ?> item(s).</h6>
                            <table class="table table-hover table-sm table-borderless">
                                <?php
                                $i=1;
                                if(count($expenses["bkkOffice"])<=0) { ?>
                                    <tr>
                                        <td></td>
                                        <td class="text-center text-muted"><small> -- No Record -- </small></td>
                                        <td></td>
                                    </tr>
                                <?php }else{
                                foreach ($expenses["bkkOffice"] as $row){ ?>
                                <tr>
                                    <th scope="row"><?php echo $i; ?></th>
                                    <td><?php echo $row[0]; ?></td>
                                    <td class="text-right">THB <?php echo number_format($row[1],2); ?></td>
                                    <td class="text-right">
                                        <a href="#" onclick="setDel(<?php echo $row[2]; ?>);">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="1.2em" viewBox="0 0 576 512"><path d="M576 128c0-35.3-28.7-64-64-64H205.3c-17 0-33.3 6.7-45.3 18.7L9.4 233.4c-6 6-9.4 14.1-9.4 22.6s3.4 16.6 9.4 22.6L160 429.3c12 12 28.3 18.7 45.3 18.7H512c35.3 0 64-28.7 64-64V128zM271 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z" fill="#dc3545" /></svg>
                                        </a>
                                    </td>
                                </tr>
                                <?php $i++; }}//else foreach ?>
                            </table>
                        </div>
                    </div>
                    <div class="card listBox" id="allTH" style="display:none;">
                        <div class="card-body" style="height: 200px; overflow: scroll;" id="addedDataTH">
                            <h6>Found <?php echo count($expenses["THOperation"]); ?> item(s).</h6>
                            <table class="table table-hover table-sm table-borderless">
                                <?php
                                $i=1;
                                if(count($expenses["THOperation"])<=0) { ?>
                                    <tr>
                                        <td></td>
                                        <td class="text-center text-muted"><small> -- No Record -- </small></td>
                                        <td></td>
                                    </tr>
                                <?php }else{
                                    foreach ($expenses["THOperation"] as $row){ ?>
                                        <tr>
                                            <th scope="row"><?php echo $i; ?></th>
                                            <td><?php echo $row[0]; ?></td>
                                            <td class="text-right">THB <?php echo number_format($row[1],2); ?></td>
                                            <td class="text-right">
                                                <a href="#" onclick="setDel(<?php echo $row[2]; ?>);">
                                                    <svg xmlns="http://www.w3.org/2000/svg" height="1.2em" viewBox="0 0 576 512"><path d="M576 128c0-35.3-28.7-64-64-64H205.3c-17 0-33.3 6.7-45.3 18.7L9.4 233.4c-6 6-9.4 14.1-9.4 22.6s3.4 16.6 9.4 22.6L160 429.3c12 12 28.3 18.7 45.3 18.7H512c35.3 0 64-28.7 64-64V128zM271 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z" fill="#dc3545" /></svg>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php $i++; }}//else foreach ?>
                            </table>
                        </div>
                    </div>

                    <div class="card listBox" id="allCEO" style="display:none;">
                        <div class="card-body" style="height: 200px; overflow: scroll;" id="addedDataCEO">
                            <h6>Found <?php echo count($expenses["CEOLiving"]); ?> item(s).</h6>
                            <table class="table table-hover table-sm table-borderless">
                                <?php
                                $i=1;
                                if(count($expenses["CEOLiving"])<=0) { ?>
                                    <tr>
                                        <td></td>
                                        <td class="text-center text-muted"><small> -- No Record -- </small></td>
                                        <td></td>
                                    </tr>
                                <?php }else{
                                    foreach ($expenses["CEOLiving"] as $row){ ?>
                                        <tr>
                                            <th scope="row"><?php echo $i; ?></th>
                                            <td><?php echo $row[0]; ?></td>
                                            <td class="text-right">THB <?php echo number_format($row[1],2); ?></td>
                                            <td class="text-right">
                                                <a href="#" onclick="setDel(<?php echo $row[2]; ?>);">
                                                    <svg xmlns="http://www.w3.org/2000/svg" height="1.2em" viewBox="0 0 576 512"><path d="M576 128c0-35.3-28.7-64-64-64H205.3c-17 0-33.3 6.7-45.3 18.7L9.4 233.4c-6 6-9.4 14.1-9.4 22.6s3.4 16.6 9.4 22.6L160 429.3c12 12 28.3 18.7 45.3 18.7H512c35.3 0 64-28.7 64-64V128zM271 175c9.4-9.4 24.6-9.4 33.9 0l47 47 47-47c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-47 47 47 47c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-47-47-47 47c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l47-47-47-47c-9.4-9.4-9.4-24.6 0-33.9z" fill="#dc3545" /></svg>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php $i++; }}//else foreach ?>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body d-flex">
                            <div class="form-group mr-3 addDate">
                                <label class="mr-2" for="datepicker">Join on</label>
                                <input type="text" class="form-control" id="datepicker" name="datepicker" value="<?php echo $thisDay; ?>">
                            </div>

                            <div class="form-group mr-3 monthYear">
                                <label for="year">Year</label>
                                <select id="year" name="year" class="form-control" onchange="getAddedData();">
                                    <?php
                                    foreach($yearScope as $no => $year) { ?>
                                        <option value="<?php echo $year; ?>" <?php echo ($year == $thisYear) ? 'selected' : ''; ?> ><?php echo $year; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group mr-3 monthYear">
                                <label for="month">Month</label>
                                <select id="month" name="month" class="form-control" onchange="getAddedData();">
                                    <?php
                                    $months = array( '01'=>'January', '02'=>'February', '03'=>'March', '04'=>'April', '05'=>'May', '06'=>'June', '07'=>'July ', '08'=>'August', '09'=>'September', '10'=>'October', '11'=>'November', '12'=>'December' );
                                    foreach($months as $no => $month) { ?>
                                        <option value="<?php echo $no; ?>" <?php echo ($no == $thisMonth) ? 'selected' : ''; ?> ><?php echo $no . " : " . $month; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="card-body multiForm" id="formEmployee">
                            <div class="d-flex flex-column">
                                <div class="form-group">
                                    <label for="inputNickName">Nick Name</label>
                                    <input type="text" class="form-control" id="inputNickName" placeholder="Enter Staff Nick Name">
                                    <small id="nickNameHelp" class="form-text text-muted">Boom</small>

                                </div>
                                <div class="form-group mb-5">
                                    <label for="inputName">Full Name</label>
                                    <input type="text" class="form-control" id="inputName" placeholder="Enter Staff Name">
                                    <small id="nameHelp" class="form-text text-muted">Sorasak Thanomsap</small>
                                </div>


                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group row">
                                            <label for="inputTeam" class="col-2 col-form-label">Team</label>
                                            <select id="inputTeam" class="custom-select col">
                                                <option value="0" disabled selected> -- Please select team -- </option>
                                                <?php
                                                $teams = $db->query('SELECT * FROM `team` ORDER BY `idx`;')->fetchAll();
                                                $i=1;
                                                foreach ($teams as $team){
                                                    ?>
                                                    <option value="<?php echo $team["id"]; ?>"><?php echo $i." : ".$team["name"]." - ".$team["fullName"]; ?></option>
                                                <?php $i++; }//foreach ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group row">
                                            <label for="inputStatus" class="col-2 col-form-label">Status</label>
                                            <select id="inputStatus" class="custom-select col">
                                                <option value="1" selected>Working</option>
                                                <option value="0">Resigned</option>
                                            </select>
                                        </div>
                                    </div>
                                </div><!-- row -->

                            </div> <!-- flex -->
                        </div>

                        <div class="card-body multiForm" id="formBkkExpense">
                            <div id="inputExpensesTypeBkkOffice" class="mb-3"></div>
                            <div class="form-group">
                                <label for="totalBkkOffice">Total</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">THB </span>
                                    </div>
                                    <input id="totalBkkOffice" name="totalBkkOffice" type="number" step="any" class="form-control" placeholder="250.53">
                                </div>
                            </div>
                        </div>

                        <div class="card-body multiForm" id="formThOperation">
                            <div id="inputExpensesTypeTHOperation" class="mb-3"></div>
                            <div class="form-group">
                                <label for="totalTHOperation">Total</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">THB </span>
                                    </div>
                                    <input id="totalTHOperation" name="totalTHOperation" type="number" step="any" class="form-control" placeholder="250.53">
                                </div>
                            </div>
                        </div>

                        <div class="card-body multiForm" id="formCEOLiving">
                            <div id="inputExpensesTypeCEOLiving" class="mb-3"></div>
                            <div class="form-group">
                                <label for="totalCEOLiving">Total</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">THB </span>
                                    </div>
                                    <input id="totalCEOLiving" name="totalCEOLiving" type="number" step="any" class="form-control" placeholder="250.53">
                                </div>
                            </div>
                        </div>
                    </div> <!-- card -->

                    <div class="card-footer text-right">
                        <input type="hidden" name="formAction" id="formAction" value="add">
                        <input type="hidden" name="typeID" id="typeID" value="0">
                        <button onclick="formSave();" type="button" class="btn btn-primary" name="cmdSubmit" id="cmdSubmit">Add</button>
                    </div>
                </div> <!-- modal-body -->

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div> <!-- modal -->
</div>
<!-- /.content -->

<script>
    const resetForm = () => {
        console.log('reset form');

        const totalBkkOffice = $("#totalBkkOffice");
        const totalTHOperation = $("#totalTHOperation");
        const totalCEOLiving = $("#totalCEOLiving");
        const inputNickName = $("#inputNickName");
        const inputName = $("#inputName");
        const inputTeam = $("#inputTeam");
        const inputStatus = $("#inputStatus");
        const listBox = $(".listBox");
        inputTeam.val(0);
        inputStatus.val(1);
        inputNickName.val("");
        inputName.val("");
        totalBkkOffice.val("");
        totalTHOperation.val("");
        totalCEOLiving.val("");
        listBox.hide();
        loadData();

    }// const

    const openForm = (formType) => {
        const typeID = $("#typeID");
        const multiForm = $(".multiForm");
        const formEmployee = $("#formEmployee");
        const formBkkExpense = $("#formBkkExpense");
        const formThOperation = $("#formThOperation");
        const formCEOLiving = $("#formCEOLiving");
        const monthYear = $(".monthYear");
        const addDate = $(".addDate");
        const allEmployee = $("#allEmployee");
        const allBkk = $("#allBkk");
        const allTH = $("#allTH");
        const allCEO = $("#allCEO");

        multiForm.hide();
        monthYear.hide();
        addDate.hide();
        switch (formType){
            case "formEmployee":
                typeID.val('formEmployee');
                addDate.show();
                allEmployee.show();
                formEmployee.show();
                break;
            case "formBkkExpense":
                typeID.val('formBkkExpense');
                loadExpense('getExpensesTypeBkkOffice');
                monthYear.show();
                allBkk.show();
                formBkkExpense.show();
                break;
            case "formThOperation":
                typeID.val('formThOperation');
                loadExpense('getExpensesTypeTHOperation');
                monthYear.show();
                allTH.show();
                formThOperation.show();
                break;
            case "formCEOLiving":
                typeID.val('formCEOLiving');
                loadExpense('getExpensesTypeCEOLiving');
                monthYear.show();
                allCEO.show();
                formCEOLiving.show();
                break;
        }

        modalForm.show();
    }

    const loadData = () => {
      loadEmployee('employee');
      loadEmployee('team');
      loadEmployee('leaving');
      loadEmployee('new');
      loadExpense('bkkOffice');
      loadExpense('THOperation');
      loadExpense('CEOLiving');
      loadExpense('totalExpense');
    }

    const loadEmployee = (section) => {
        const year = $("#selectedYear");
        const month = $("#selectedMonth");
        let act = "";
        let place = {};

        switch (section) {
            case "employee":
                place = $("#placeEmployeeStat");
                act = "getEmployee";
                break;
            case "team":
                place = $("#placeTeamsStat");
                act = "getTeam";
                break;
            case "leaving":
                place = $("#placeEmployeeLeaving");
                act = "getLeaving";
                break;
            case "new":
                place = $("#placeEmployeeNew");
                act = "getNew";
                break;
        } //switch

        const params = {
                        act: act,
                        month: month.val(),
                        year: year.val()
                      };

        const reqAjax = $.ajax({
            url: "assets/php/getEmployee.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: params,
        });

        reqAjax.done(function (res) {
            place.html(res.table);
        });

        reqAjax.fail(function (xhr, status, error) {
            let failText = '<span class="text-danger">Load failed</span>';
            place.html(failText);
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        });
    } // const

    const loadExpense = (type) => {
        const year = $("#selectedYear");
        const month = $("#selectedMonth");
        let act = "";
        let place = {};

        switch (type) {
            case "bkkOffice":
                act = "bkkOffice";
                place = $("#placeBkkOffice");
                break;
            case "THOperation":
                act = "THOperation";
                place = $("#placeTHOperation");
                break;
            case "CEOLiving":
                act = "CEOLiving";
                place = $("#placeCEOLiving");
                break;
            case "totalExpense":
                act = "totalExpense";
                place = $("#placeTotalExpense");
                break;
            case "getExpensesTypeBkkOffice":
                act = "getExpensesTypeBkkOffice";
                place = $("#inputExpensesTypeBkkOffice");
                break;
            case "getExpensesTypeTHOperation":
                act = "getExpensesTypeTHOperation";
                place = $("#inputExpensesTypeTHOperation");
                break;
            case "getExpensesTypeCEOLiving":
                act = "getExpensesTypeCEOLiving";
                place = $("#inputExpensesTypeCEOLiving");
                break;
        }//switch

        let params = {
                        act: act,
                        month: month.val(),
                        year: year.val()
                     };

        const reqAjax = $.ajax({
            url: "assets/php/getExpense.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: params,
        });

        reqAjax.done(function (res) {
            place.html(res.table);
        });

        reqAjax.fail(function (xhr, status, error) {
            let failText = '<span class="text-danger">Load failed</span>';
            place.html(failText);
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        });
    } // const

    const formSave = () => {
        console.log('form save');

        const formAction = $("#formAction");
        const typeID = $("#typeID");
        const year = $("#year");
        const month = $("#month");
        const typeExpense = $("#typeExpense");
        const totalBkkOffice = $("#totalBkkOffice");
        const totalTHOperation = $("#totalTHOperation");
        const totalCEOLiving = $("#totalCEOLiving");
        const datepicker = $("#datepicker");
        const inputNickName = $("#inputNickName");
        const inputName = $("#inputName");
        const inputTeam = $("#inputTeam");
        const inputStatus = $("#inputStatus");

        let values = 0;
        switch (typeID.val()){
            case "formEmployee":
                values = 0;
                break;
            case "formBkkExpense":
                values = totalBkkOffice.val();
                break;
            case "formCEOLiving":
                values = totalCEOLiving.val();
                break;
            case "formThOperation":
                values = totalTHOperation.val();
                break;
        }

        let params = {
                        act: "save",
                        formAction: formAction.val(),
                        year: year.val(),
                        month: month.val(),
                        value: values,
                        typeExpense: typeExpense.val(),
                        typeID: typeID.val(),
                        datepicker: datepicker.val(),
                        nickName: inputNickName.val(),
                        name: inputName.val(),
                        team: inputTeam.val(),
                        status: inputStatus.val()
                     };

        const reqAjax = $.ajax({
            url: "assets/php/actionCompany.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: params,
        });

        reqAjax.done(function (res) {
            console.log(res);
            modalForm.hide();
            //resetForm();
        });

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        });
    }// const

    const getAddedData = () => {
        const typeID = $("#typeID");
        const month = $("#month");
        const year = $("#year");
        console.log("get Added Data "+typeID.val());

        let place = {};
        let type = '';

        switch (typeID.val()){
            case "formBkkExpense":
                place = $("#addedDataBkk");
                type = "formBkkExpense";
                break;
            case "formThOperation":
                place = $("#addedDataTH");
                type = "formThOperation";
                break;
            case "formCEOLiving":
                place = $("#addedDataCEO");
                type = "formCEOLiving";
                break;
            default:
                place = $("#addedDataEmployee");
                type = "formEmployee";
                break;
        }//switch

        let params = {
                        act: "getAddedData",
                        typeID: type,
                        month: month.val(),
                        year: year.val()
                    };

        const reqAjax = $.ajax({
            url: "assets/php/getCompanyAdded.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: params,
        });

        reqAjax.done(function (res) {
            place.html(res.table).show();
        });

        reqAjax.fail(function (xhr, status, error) {
            place.html("load data fail!!");
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        });

    } //const

    const setStatus = (id, status) => {
        const typeID = $("#typeID");
        console.log(id,status);
        // alert("change status = "+id);
        let flagStatus = !status ? 1 : 0;

        let params = {
                        act: "setStatus",
                        typeID: typeID.val(),
                        employeeStatus: flagStatus,
                        id: id
                     };

            const reqAjax = $.ajax({
                url: "assets/php/actionCompany.php",
                method: "POST",
                async: false,
                cache: false,
                dataType: "json",
                data: params,
            }); //const

            reqAjax.done(function (res) {
                console.log("status Changed");
                getAddedData();
            }); //done

            reqAjax.fail(function (xhr, status, error) {
                console.log("ajax request fail!!");
                console.log(status + ": " + error);
            }); //fail


    }// const

    const setDel = (id) => {
        let answer = confirm("Are you sure you want to delete this? \n This item will be deleted immediately. \n\n You can't undo this action.");

        const typeID = $("#typeID");
        let params = {
            act: "setDelete",
            typeID: typeID.val(),
            id: id,
        };

        if (answer){
            const reqAjax = $.ajax({
                url: "assets/php/actionCompany.php",
                method: "POST",
                async: false,
                cache: false,
                dataType: "json",
                data: params,
            }); //const

            reqAjax.done(function (res) {
                console.log("deleted");
                getAddedData();
            }); //done

            reqAjax.fail(function (xhr, status, error) {
                console.log("ajax request fail!!");
                console.log(status + ": " + error);
            }); //fail
        } //if

    }// const

    const setToToday = () => {
        const year = $("#selectedYear");
        const month = $("#selectedMonth");

        let objectDate = new Date();
        const thisYear = objectDate.getFullYear();
        const thisMonth = objectDate.getMonth()+1;

        year.val(thisYear);
        month.val(thisMonth);

        loadData();
    }// const
</script>

<?php
function reFormatDate($param){
    $tmp = explode("-", $param);
    return $tmp[2]."/".$tmp[1]."/".$tmp[0];
}
?>