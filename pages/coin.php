<?php
global $db, $date;
$teamID = $_SESSION['teamID'];
if (($teamID == 2) or ($teamID == 8) or ($teamID == 210) or ($teamID == 11)) {
    $teamLogged = 2;
} else {
    $teamLogged = $teamID;
}
$teamIDHardcode = array(
    array(
        "id" => 0,
        "name" => "All"
    ),
    array(
        "id" => 1,
        "name" => "CS: Customer Support"
    ),
    array(
        "id" => 2,
        "name" => "AM: Account Manager (All)"
    ),
    array(
        "id" => 3,
        "name" => "SE: Sales"
    ),
    array(
        "id" => 4,
        "name" => "HR: Human Resource"
    ),
    array(
        "id" => 5,
        "name" => "IT: Information Technology"
    ),
    array(
        "id" => 6,
        "name" => "CEO: CEO"
    ),
    array(
        "id" => 7,
        "name" => "OT: Other"
    )
);
?>
    <link rel="stylesheet" href="assets/css/coin.css">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h3 class="m-0">
                        <svg class="nav-icon mr-2" xmlns="http://www.w3.org/2000/svg" height="1em"
                             viewBox="0 0 384 512">
                            <path d="M48 0C21.5 0 0 21.5 0 48V464c0 26.5 21.5 48 48 48h96V432c0-26.5 21.5-48 48-48s48 21.5 48 48v80h96c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48H48zM64 240c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16V240zm112-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H176c-8.8 0-16-7.2-16-16V240c0-8.8 7.2-16 16-16zm80 16c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H272c-8.8 0-16-7.2-16-16V240zM80 96h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16V112c0-8.8 7.2-16 16-16zm80 16c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H176c-8.8 0-16-7.2-16-16V112zM272 96h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H272c-8.8 0-16-7.2-16-16V112c0-8.8 7.2-16 16-16z"
                                  fill="#000000"/>
                        </svg>
                        Coin Rewards
                        <?php if ($_SESSION['level'] <= 2) { ?>
                            <button type="button" class="btn btn-success" onclick="goX2();">coin x2 to everyone</button>
                        <?php } ?>
                    </h3>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="main.php">My Desk</a></li>
                        <li class="breadcrumb-item"><a href="main.php?p=coin">Coin</a></li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="mr-3 d-flex  align-items-center">
                            <label for="selectedTeam" class="mr-3">Team</label>
                            <select id="selectedTeam" class="form-control" onchange="reloadData();">
                                <?php
                                foreach ($teamIDHardcode as $no => $teams) { ?>
                                    <option value="<?php echo $teams["id"]; ?>" <?php echo ($teams["id"] == $teamLogged) ? 'selected' : ''; ?> ><?php echo $teams["name"]; ?></option>
                                <?php } ?>
                            </select>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-10">
                                    <p class="text-center font-weight-bold">
                                        <svg class="nav-icon mr-2" xmlns="http://www.w3.org/2000/svg" height="1em"
                                             viewBox="0 0 640 512">
                                            <path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM609.3 512H471.4c5.4-9.4 8.6-20.3 8.6-32v-8c0-60.7-27.1-115.2-69.8-151.8c2.4-.1 4.7-.2 7.1-.2h61.4C567.8 320 640 392.2 640 481.3c0 17-13.8 30.7-30.7 30.7zM432 256c-31 0-59-12.6-79.3-32.9C372.4 196.5 384 163.6 384 128c0-26.8-6.6-52.1-18.3-74.3C384.3 40.1 407.2 32 432 32c61.9 0 112 50.1 112 112s-50.1 112-112 112z"
                                                  fill="#000000"/>
                                        </svg>
                                        Statistic
                                    </p>
                                </div>
                                <div class="col-2">
                                </div>
                            </div>
                            <div class="px-5">
                                <div id="placeCoinStat">
                                    <?php
                                    unset($new, $leave, $all, $employee, $cs, $am, $se, $hr, $it, $ceo, $ot);

                                    if ($teamLogged == 0) {
                                        $users = $db->query('SELECT s.`sID` AS "id",s.`sNickName` AS "nickName",s.`sName` AS "fullName",s.`sPic` AS "pic", te.`name` AS "team", te.`fullName` AS "fteam",s.`sL4U` AS "L4U",s.`sCEO` AS "CEO" FROM `staffs` s, `Team` te WHERE s.`sStatus` = ? AND s.`teamID` = te.`id` AND s.`sDeleteAt` IS NULL ORDER BY s.`teamID`, s.`sNickName`;', 1)->fetchAll();
                                    } else if ($teamLogged == 2) {
                                        $users = $db->query('SELECT s.`sID` AS "id",s.`sNickName` AS "nickName",s.`sName` AS "fullName",s.`sPic` AS "pic", te.`name` AS "team", te.`fullName` AS "fteam",s.`sL4U` AS "L4U",s.`sCEO` AS "CEO" FROM `staffs` s, `Team` te WHERE s.`sStatus` = ? AND s.`teamID` = te.`id` AND s.`sDeleteAt` IS NULL AND s.`teamID` IN (2,8,10,11) ORDER BY s.`teamID`, s.`sNickName`;', 1)->fetchAll();
                                    } else {
                                        $users = $db->query('SELECT s.`sID` AS "id",s.`sNickName` AS "nickName",s.`sName` AS "fullName",s.`sPic` AS "pic", te.`name` AS "team", te.`fullName` AS "fteam",s.`sL4U` AS "L4U",s.`sCEO` AS "CEO" FROM `staffs` s, `Team` te WHERE s.`sStatus` = ? AND s.`teamID` = te.`id` AND s.`sDeleteAt` IS NULL AND s.`teamID` = ? ORDER BY s.`teamID`, s.`sNickName`;', 1, $teamLogged)->fetchAll();
                                    }

                                    //$users = $db->query('SELECT s.`sID` AS "id",s.`sNickName` AS "nickName",s.`sName` AS "fullName",s.`sPic` AS "pic", te.`name` AS "team", te.`fullName` AS "fteam",s.`sL4U` AS "L4U",s.`sCEO` AS "CEO" FROM `staffs` s, `Team` te WHERE s.`sStatus` = ? AND s.`teamID` = te.`id` AND s.`sDeleteAt` IS NULL ORDER BY s.`teamID`, s.`sNickName`;', 1)->fetchAll();
                                    $allCoins = $db->query('SELECT SUM(`sL4U`) AS "allL4U", SUM(`sCEO`) AS "allCEO" FROM `staffs`;')->fetchArray();
                                    $top['L4U'] = $db->query('SELECT `sNickName` AS "nickName", `sName` AS "fullName" , `sL4U` AS "L4U" FROM `staffs` ORDER BY `sL4U` DESC, `sNickName` LIMIT 1;')->fetchArray();
                                    $top['CEO'] = $db->query('SELECT `sNickName` AS "nickName", `sName` AS "fullName" , `sCEO` AS "CEO" FROM `staffs` ORDER BY `sCEO` DESC, `sNickName` LIMIT 1;')->fetchArray();

                                    $coins["OverAll"] = [
                                        ['All Coin', '', number_format($allCoins['allL4U'] + $allCoins['allCEO'], 2)],
                                        ['L4U', '', number_format($allCoins['allL4U'], 2)],
                                        ['CEO', '', number_format($allCoins['allCEO'], 2)],
                                        ['Top L4U', showName($top['L4U']['nickName'], $top['L4U']['fullName']), number_format($top['L4U']['L4U'], 2)],
                                        ['Top CEO', showName($top['CEO']['nickName'], $top['CEO']['fullName']), number_format($top['CEO']['CEO'], 2)]
                                    ];
                                    unset($row); ?>

                                    <div class="card mt-lg-3 mb-lg-5">
                                        <div class="card-body d-flex">
                                            <?php foreach ($coins["OverAll"] as $row) { ?>

                                                <div class="card_stat d-flex flex-column align-items-center">
                                                    <h6 class="text-primary font-weight-bold"><?php echo $row[0]; ?></h6>
                                                    <div>
                                                        <span class="font-weight-bold"><?php echo $row[2]; ?></span>
                                                        <small class="text-muted">Coin(s)</small>
                                                        <?php if (!empty($row[1])) { ?>
                                                            <?php if ($row[2] != "0.00") { ?>
                                                                <div>
                                                                    <small class="text-muted">( <?php echo $row[1]; ?>
                                                                        )</small>
                                                                </div>
                                                            <?php } else { ?>
                                                                <div>
                                                                    <small class="text-muted">( - )</small>
                                                                </div>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </div>
                                                </div>

                                            <?php }//foreach ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-10">
                                        <p class="text-center font-weight-bold">
                                            <svg class="nav-icon mr-2" xmlns="http://www.w3.org/2000/svg" height="1.5em"
                                                 viewBox="0 0 640 512">
                                                <path d="M72 88a56 56 0 1 1 112 0A56 56 0 1 1 72 88zM64 245.7C54 256.9 48 271.8 48 288s6 31.1 16 42.3V245.7zm144.4-49.3C178.7 222.7 160 261.2 160 304c0 34.3 12 65.8 32 90.5V416c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V389.2C26.2 371.2 0 332.7 0 288c0-61.9 50.1-112 112-112h32c24 0 46.2 7.5 64.4 20.3zM448 416V394.5c20-24.7 32-56.2 32-90.5c0-42.8-18.7-81.3-48.4-107.7C449.8 183.5 472 176 496 176h32c61.9 0 112 50.1 112 112c0 44.7-26.2 83.2-64 101.2V416c0 17.7-14.3 32-32 32H480c-17.7 0-32-14.3-32-32zm8-328a56 56 0 1 1 112 0A56 56 0 1 1 456 88zM576 245.7v84.7c10-11.3 16-26.1 16-42.3s-6-31.1-16-42.3zM320 32a64 64 0 1 1 0 128 64 64 0 1 1 0-128zM240 304c0 16.2 6 31 16 42.3V261.7c-10 11.3-16 26.1-16 42.3zm144-42.3v84.7c10-11.3 16-26.1 16-42.3s-6-31.1-16-42.3zM448 304c0 44.7-26.2 83.2-64 101.2V448c0 17.7-14.3 32-32 32H288c-17.7 0-32-14.3-32-32V405.2c-37.8-18-64-56.5-64-101.2c0-61.9 50.1-112 112-112h32c61.9 0 112 50.1 112 112z"
                                                      fill="#000000"/>
                                            </svg>
                                            Persons
                                        </p>
                                    </div>
                                    <div class="col-2">

                                    </div>
                                </div>
                                <div id="placeTeamsStat"
                                     class="mb-5 d-flex justify-content-between align-items-center flex-wrap">
                                    <?php foreach ($users as $row) { ?>
                                        <div class="user_card card py-4 px-4 u-cursor-pt">
                                            <div class="d-flex align-items-center"
                                                 onclick="openForm('<?php echo $row['id']; ?>');">
                                                <img class="profile-coin-img mr-4"
                                                     src="dist/img/crews/<?php echo $row['pic']; ?>"
                                                     alt="<?php echo $row['nickName']; ?>"
                                                     title="<?php echo $row['nickName']; ?>">
                                                <div>
                                                    <h5 class="user_name"
                                                        title="<?php echo showName($row['nickName'], $row['fullName']); ?>"><?php echo showName($row['nickName'], $row['fullName']); ?></h5>
                                                    <div class="user_team mb-3"
                                                         title="<?php echo $row['fteam']; ?>"><?php echo $row['team']; ?></div>
                                                    <div class="coin_l4u">L4U
                                                        : <?php echo number_format($row['L4U'], 2); ?></div>
                                                    <div class="coin_ceo">CEO
                                                        : <?php echo number_format($row['CEO'], 2); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }//foreach ?>
                                </div>
                            </div><!-- wrap -->
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
                        <h5 class="modal-title" id="formModalLabel">Form Manage Coin</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-body d-flex align-items-start">
                                <img id="profile-coin-img" class="profile-coin-img mr-4" src="assets/img/crews/no_pic.png"
                                     alt="message user image">
                                <div class="mr-4">
                                    <h4 id="user_name" class="user_name_full">Name</h4>
                                    <div id="user_team" class="user_team mb-3">Team</div>
                                    <div class="d-flex flex-row justify-content-start align-items-center">
                                        <div class="coin_l4u_big mr-4">
                                            <strong>L4U :</strong>
                                            <span id="l4uCoin" class="text-primary">0.00</span>
                                        </div>
                                        <div class="coin_ceo_big">
                                            <strong>CEO :</strong>
                                            <span id="ceoCoin" class="text-primary">0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <b>History</b>
                                <div id="coinHistory" class="p-xl-2">
                                    <ul id="historyList">
                                        <li>history</li>
                                        <li>history</li>
                                        <li>history</li>
                                        <li>history</li>
                                    </ul>
                                </div>
                            </div>
                        </div> <!--card-->


                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col"></div>
                                    <div class="col-4">
                                        <div class="form-group row">
                                            <label id="coinTypeLabel" for="coinType"
                                                   class="col-3 col-form-label">Type</label>
                                            <select id="coinType" class="custom-select col">
                                                <option value="l4u">L4U Coin</option>
                                                <option value="ceo">CEO Coin</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group row">
                                            <label id="coinAmountLabel" for="coinAmount"
                                                   class="col-3 col-form-label">Amount</label>
                                            <input id="coinAmount" type="number" step="1" class="form-control col"
                                                   value="1"
                                                   placeholder="20">
                                        </div>
                                    </div>
                                    <div class="col"></div>
                                </div><!-- row -->

                                <div class="row">
                                    <div class="col"></div>
                                    <div class="form-group col-10">
                                        <label id="coinReasonLabel" for="activityID">Activity type</label>
                                        <select id="activityID" class="form-control">
                                            <?php
                                            //sprintf("%02d", $number);
                                            $allActivities = $db->query('SELECT * FROM `CoinActivities` WHERE `aId` NOT IN (10,11,12,13) ORDER BY `aName`;')->fetchAll();
                                            $i = 1;
                                            foreach ($allActivities as $row) {
                                                ?>
                                                <option value="<?php echo $row['aID']; ?>"><?php echo sprintf("%02d",$i) . ' : ' . $row['aName']; ?></option>
                                                <?php
                                                $i++;
                                            }//foreach
                                            ?>
                                            <option value="11"><?php echo sprintf("%02d",$i) . ' : '; ?>Other</option>
                                        </select>
                                        <small id="CoinActivitiesHelp" class="form-text text-muted">If you need
                                            additional types, please tell Nueng.</small>
                                    </div>
                                    <div class="col"></div>
                                </div>

                                <div class="row">
                                    <div class="col"></div>
                                    <div class="form-group col-10">
                                        <label id="coinReasonLabel" for="coinReason">Reason</label>
                                        <textarea class="form-control" id="coinReason" rows="2"></textarea>
                                        <small id="reasonHelp" class="form-text text-muted">e.g., Win Prize on Company
                                            meeting</small>
                                    </div>
                                    <div class="col"></div>
                                </div>
                            </div>

                            <div class="card-footer text-right">
                                <input type="hidden" name="formAction" id="formAction" value="add">
                                <input type="hidden" name="userID" id="userID" value="0">
                                <button onclick="formSave();" type="button" class="btn btn-primary" name="cmdSubmit"
                                        id="cmdSubmit">Add
                                </button>
                            </div>
                        </div> <!-- modal-body -->

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div> <!-- modal -->
        </div>
    </div>
    <!-- /.content -->

    <script>
        const resetForm = () => {
            console.log('reset form');
            const coinType = $("#coinType");
            const coinAmount = $("#coinAmount");
            const activityID = $("#activityID");
            const coinReason = $("#coinReason");
            const userID = $("#userID");
            const coinAmountLabel = $("#coinAmountLabel");
            const coinReasonLabel = $("#coinReasonLabel");
            const historyList = $("#historyList");
            coinType.val('l4u');
            coinAmount.val(1);
            activityID.val(1);
            coinReason.val('');
            userID.val(0);
            coinAmountLabel.removeClass('text-danger');
            coinReasonLabel.removeClass('text-danger');
            historyList.empty().append($("<li>").text("-- No data --"));
            reloadData();
        }// const

        const openForm = (id) => {
            const historyList = $("#historyList");
            const selectedTeam = $("#selectedTeam");

            let params = {
                act: "load",
                userID: id,
                selectedTeam: selectedTeam.val()
            };

            const reqAjax = $.ajax({
                url: "assets/php/actionCoin.php",
                method: "POST",
                async: false,
                cache: false,
                dataType: "json",
                data: params,
            });

            reqAjax.done(function (res) {
                console.log(res);
                $("#profile-coin-img").attr('src', res.pic);
                $("#user_name").html(res.showName);
                $("#user_team").html(res.fteam);
                $("#l4uCoin").html(res.L4U);
                $("#ceoCoin").html(res.CEO);
                $("#userID").val(res.id);

                historyList.empty();
                if (res.logs.length > 0) {
                    res.logs.forEach(item => historyList.append($("<li>").text(item)));
                } else {
                    historyList.append($("<li>").text("-- No data --"));
                }

            });

            reqAjax.fail(function (xhr, status, error) {
                console.log("ajax request fail!!");
                console.log(status + ": " + error);
            });

            modalForm.show();
        }//const


        const formSave = () => {
            console.log('form save');

            const formAction = $("#formAction");
            const coinType = $("#coinType");
            const coinAmount = $("#coinAmount");
            const activityID = $("#activityID");
            const coinReason = $("#coinReason");
            const userID = $("#userID");
            const coinAmountLabel = $("#coinAmountLabel");
            const coinReasonLabel = $("#coinReasonLabel");

            if (coinAmount.val().length < 1) {
                coinAmountLabel.addClass('text-danger');
                coinAmount.focus();
                return false;
            } else if (coinReason.val().length < 1) {
                coinAmountLabel.removeClass('text-danger');
                coinReasonLabel.addClass('text-danger');
                coinReason.focus();
                return false;
            } else {
                coinReasonLabel.removeClass('text-danger');

                let params = {
                    act: "save",
                    userID: userID.val(),
                    formAction: formAction.val(),
                    coinType: coinType.val(),
                    coinAmount: coinAmount.val(),
                    activityID: activityID.val(),
                    coinReason: coinReason.val()
                };

                console.log(params);

                const reqAjax = $.ajax({
                    url: "assets/php/actionCoin.php",
                    method: "POST",
                    async: false,
                    cache: false,
                    dataType: "json",
                    data: params,
                });

                reqAjax.done(function (res) {
                    console.log(res);
                    resetForm();
                    modalForm.hide();
                });

                reqAjax.fail(function (xhr, status, error) {
                    console.log("ajax request fail!!");
                    console.log(status + ": " + error);
                });

            }//else
        }// const

        const reloadData = () => {
            const placeCoinStat = $("#placeCoinStat");
            const placeTeamsStat = $("#placeTeamsStat");
            const selectedTeam = $("#selectedTeam");

            let params = {
                act: "loadStat",
                selectedTeam: selectedTeam.val()
            };

            const reqAjax = $.ajax({
                url: "assets/php/actionCoin.php",
                method: "POST",
                async: false,
                cache: false,
                dataType: "json",
                data: params,
            });

            reqAjax.done(function (res) {
                console.log(res);
                placeCoinStat.empty().html(res.stat);
                placeTeamsStat.empty().html(res.uCard);
            });

            reqAjax.fail(function (xhr, status, error) {
                console.log("ajax request fail!!");
                console.log(status + ": " + error);
            });
        }//const

        const goX2 = () => {
            let confirmText = "Are you sure you want to give x 2 coins to everyone? \nYou cannot undo this action.";
            let ans = confirm(confirmText);

            if (ans) {
                let params = {
                    act: "x2"
                };

                console.log(params);

                const reqAjax = $.ajax({
                    url: "assets/php/actionCoin.php",
                    method: "POST",
                    async: false,
                    cache: false,
                    dataType: "json",
                    data: params,
                });

                reqAjax.done(function (res) {
                    console.log(res);
                    location.reload();
                });

                reqAjax.fail(function (xhr, status, error) {
                    console.log("ajax request fail!!");
                    console.log(status + ": " + error);
                });
            }
        }
    </script>

<?php
function reFormatDate($param): string
{
    $tmp = explode("-", $param);
    return $tmp[2] . "/" . $tmp[1] . "/" . $tmp[0];
}

function showName($nick, $full): string
{
    $tmp = explode(" ", $full);
    $getName = ($nick == $tmp[0]) ? $tmp[1] : $tmp[0];
    return $nick . ' ' . $getName;
}

?>