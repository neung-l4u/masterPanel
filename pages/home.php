<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-LGKDYHL23T');
</script>
<?php
global $db, $date;
$timestamp = time();
$coins["l4u"] = $_SESSION['L4UCoin'];
$coins["ceo"] = $_SESSION['CEOCoin'];
$loginID = $_SESSION['id'];

// ── Signup & Unsub data for current week (Sunday–Saturday) ──
$todayObj = new DateTime();
$dow = (int)$todayObj->format('w'); // 0=Sun
$weekStart = (new DateTime())->modify("-{$dow} day")->format('Y-m-d 00:00:00');
$weekEnd   = (new DateTime())->modify('+' . (6 - $dow) . ' days')->format('Y-m-d 23:59:59');
$weekStartDisplay = (new DateTime($weekStart))->format('d M Y');
$weekEndDisplay   = (new DateTime($weekEnd))->format('d M Y');

// Signup
$signupRows = $db->query(
    'SELECT dataLogs FROM logssignup WHERE createAt BETWEEN ? AND ? AND test = 0',
    $weekStart, $weekEnd
)->fetchAll();

$ovSignupByCountry = [];
$ovSignupByType    = [];
$ovProcessedShops  = [];
$ovTotalSignup     = 0;

foreach ($signupRows as $row) {
    $dl       = json_decode($row['dataLogs'], true);
    $shopName = $dl['ShopName'] ?? '';
    $country  = !empty($dl['Country']) ? $dl['Country'] : 'Unknown';
    $custType = !empty($dl['CustomerType']) ? $dl['CustomerType'] : 'Unknown';

    if (in_array($shopName, $ovProcessedShops)) continue;
    $ovProcessedShops[] = $shopName;

    $ovSignupByCountry[$country] = ($ovSignupByCountry[$country] ?? 0) + 1;
    $ovSignupByType[$custType]   = ($ovSignupByType[$custType] ?? 0) + 1;
    $ovTotalSignup++;
}
arsort($ovSignupByCountry);
arsort($ovSignupByType);

// Unsub
$unsubRows = $db->query(
    'SELECT county, industrial FROM Cancellation WHERE timestamp BETWEEN ? AND ?',
    $weekStart, $weekEnd
)->fetchAll();

$ovUnsubByCountry = [];
$ovUnsubByType    = [];
$ovTotalUnsub     = 0;

foreach ($unsubRows as $row) {
    $country = !empty($row['county']) ? $row['county'] : 'Unknown';
    $indType = !empty($row['industrial']) ? $row['industrial'] : 'Unknown';

    $ovUnsubByCountry[$country] = ($ovUnsubByCountry[$country] ?? 0) + 1;
    $ovUnsubByType[$indType]    = ($ovUnsubByType[$indType] ?? 0) + 1;
    $ovTotalUnsub++;
}
arsort($ovUnsubByCountry);
arsort($ovUnsubByType);

$ovNet = $ovTotalSignup - $ovTotalUnsub;

// Encode for JS
$ovDataJson = json_encode([
    'signup'  => ['total' => $ovTotalSignup,  'byCountry' => $ovSignupByCountry, 'byType' => $ovSignupByType],
    'unsub'   => ['total' => $ovTotalUnsub,   'byCountry' => $ovUnsubByCountry,  'byType' => $ovUnsubByType],
    'net'     => $ovNet,
    'range'   => $weekStartDisplay . '  →  ' . $weekEndDisplay,
], JSON_UNESCAPED_UNICODE);
?>
<!-- ChartJS -->
<script src="plugins/chart.js/Chart.min.js"></script>
<style>
    .iconRewardAction{
        height: 90px !important;
    }
    a.disabled{
        pointer-events: none;
        cursor: default;
    }
    abbr[title] {
        border-bottom: none !important;
        cursor: help !important;
        text-decoration: none !important;
    }

    .linkBTN{
        font-size: 0.8rem !important;
    }
    .red{
        color: red !important;
    }


    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .info-box-icon {
            width: 50px;
            height: 50px;
            font-size: 1rem;
            line-height: 50px;
        }
        .info-box-content {
            padding: 5px 10px;
        }
        .info-box-text {
            font-size: 0.75rem;
        }
        .info-box-number {
            font-size: 0.9rem;
        }
        .linkBTN {
            padding: 0.25rem 0.5rem;
            font-size: 0.7rem !important;
        }
        .direct-chat-text {
            font-size: 0.85rem;
        }
        .direct-chat-img {
            width: 30px;
            height: 30px;
        }
        .card-body.d-flex.flex-row.flex-wrap .col {
            flex: 0 0 50%;
            max-width: 50%;
        }
        .card-body.d-flex.flex-row.flex-wrap .info-box img {
            width: 80px !important;
        }
    }
    
    @media (max-width: 576px) {
        .info-box {
            min-height: auto;
        }
        .info-box-icon {
            width: 40px;
            height: 40px;
            font-size: 0.8rem;
            line-height: 40px;
        }
        .info-box-text small {
            display: none;
        }
        .linkBTN i {
            font-size: 1rem;
        }
        .card-body.d-flex.flex-row.flex-wrap .col {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }
</style>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <i class="nav-icon mr-2 bi bi-house-fill"></i>
                    Home
                </h4>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
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
            <div class="col-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-success elevation-1">L4U</span>

                    <div class="info-box-content">
                        <span class="info-box-text">Coin(s)</span>
                        <span class="info-box-number"><i class="bi bi-coin"></i> <?php echo number_format($coins["l4u"],2); ?></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix hidden-md-up"></div>

            <div class="col-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-primary elevation-1">GM</span>
                    <div class="info-box-content">
                        <span class="info-box-text">Coin(s) <small class="text-muted">(1 GM = 10 L4U)</small></span>
                        <span class="info-box-number"><i class="bi bi-cash-coin"></i> <?php echo number_format($coins["ceo"],2); ?></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->

            <div class="col-12 col-md-6 mt-2 mt-md-0">
                <div class="info-box align-items-center">
                    <div class="card-body p-0 ">
                            <div class="d-flex flex-row justify-content-around">
                                <a class="btn btn-outline-primary linkBTN w-auto" data-toggle="modal" data-target="#formModalExchangeCash">
                                    <i class="bi bi-cash-stack"></i><span class="d-none d-sm-none d-md-none d-lg-block"> Cash</span>
                                </a>
                                <a class="btn btn-outline-primary linkBTN w-auto" data-toggle="modal" data-target="#formModalConvertCoin">
                                    <i class="bi bi-arrow-left-right"></i><span class="d-none d-sm-none d-md-none d-lg-block">  Convert</span>
                                </a>
                                <a class="btn btn-outline-primary linkBTN w-auto" data-toggle="modal" data-target="#formModalTransferCoin">
                                    <i class="bi bi-arrow-right-circle-fill"></i><span class="d-none d-sm-none d-md-none d-lg-block">  Transfer</span>
                                </a>
                                <a class="btn btn-outline-primary linkBTN w-auto" data-toggle="modal" data-target="#formModalRedeemGiftCard">
                                    <i class="bi bi-box2-heart-fill"></i><span class="d-none d-sm-none d-md-none d-lg-block">  Gift Card</span>
                                </a>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->

        <div class="row">
            <div class="col-12 col-lg-6 mb-3">
                <div>
                    <div class="card direct-chat direct-chat-warning">
                    <div class="card-header" >
                        <h3 class="card-title"><i class="bi bi-clock-history"></i> History of receiving coins (Last 30 days)</h3>
                        <?php
                        $logs = $db->query('SELECT CL.`id`, CT.`name` AS "coin", CL.`ownerID`, CL.`amount`, ST.`sNickName` AS "nick",ST.`sName` AS "from", ST.`sPic` AS "pic", CL.`reason`, CL.`giveOn`, CL.`lastUpdate`, CL.`activityID`  
                                FROM `CoinLogs` CL, `staffs` ST, `CoinType` CT
                                WHERE CL.`ownerID`= ? AND CL.`status` = ? AND CL.`giveBy` = ST.`sID` AND CL.`coinType` = `CT`.`id` AND CL.`giveOn` >= DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY) 
                                ORDER BY CL.`giveOn` DESC LIMIT 0,5;'
                            , $loginID, 1)->fetchAll();
                        ?>
                        <div class="card-tools">
                            <span title="<?php echo count($logs); ?> New Coin" class="badge badge-warning"><?php echo count($logs); ?> news</span>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-tool" title="Contacts" data-widget="chat-pane-toggle">
                                <i class="fas fa-comments"></i>
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body" style="max-height: calc(100vh - 340px); overflow-y: auto; overflow-x: hidden; padding: 0 10px;">
                        <!-- Conversations are loaded here -->
                        <div class="direct-chat-messages">
                            <!-- Message. Default to the left -->
                            <?php
                            if (count($logs)>=1){
                                foreach ($logs as $row){
                                    $params['logs'][] = $row['amount'].' '.$row['coin'].' By '.$row['from'].' - '.showDate($row['giveOn']).' # '.$row['reason'];
                                    ?>
                                    <div class="direct-chat-msg">
                                        <div class="direct-chat-infos clearfix">
                                            <span class="direct-chat-name float-left">By : <?php echo showName($row['nick'],$row['from']); ?></span>
                                            <span class="direct-chat-timestamp float-right"><?php echo showDate($row['giveOn']); ?></span>
                                        </div>
                                        <img class="direct-chat-img" src="dist/img/crews/<?php echo $row['pic']; ?>" alt="giving coin user">
                                        <div class="direct-chat-text">
                                            <span class="text-success font-weight-bold"><?php echo $row['amount'].' '.$row['coin']; ?></span>:::  <?php echo $row['reason']; ?>
                                        </div>
                                    </div>
                                    <?php
                                }//foreach
                            }else{ ?>
                                <div class="direct-chat-msg">
                                    <div class="direct-chat-infos clearfix">
                                        <span class="direct-chat-name float-left">-- No data --</span>
                                    </div>
                                </div>
                            <?php }//else ?>

                        </div>
                        <!--/.direct-chat-messages-->

                        <!-- Contacts are loaded here -->
                        <div class="direct-chat-contacts">
                            <ul class="contacts-list">
                                <li><h5 class="text-warning">Spending history</h5></li>

                                <?php
                                $spendLogs = $db->query('SELECT SL.`id`, CT.`name`AS "coin", SL.`amount`, ST.`name` AS "SpendType", SL.`reason`, SL.`spendOn` 
                                                               FROM `SpendLogs` SL, `SpendType` ST, `CoinType` CT  
                                                               WHERE SL.`spendType` = ST.`id` AND SL.`coinType` = CT.`id` AND SL.`ownerID` = ? AND SL.`status` = ? 
                                                               ORDER BY SL.`spendOn` DESC'
                                    ,$loginID, 1)->fetchAll();
                                if (count($spendLogs)>=1){
                                    $i=1;
                                    foreach ($spendLogs as $row){ ?>
                                        <li>
                                            <img class="contacts-list-img" src="dist/img/icons/128/icon-Cash-128.png" alt="User Avatar">

                                            <div class="contacts-list-info">
                                            <span class="contacts-list-name">
                                                <?php echo $row['SpendType']; ?>
                                                <small class="contacts-list-date float-right"><?php echo $row['spendOn']; ?></small>
                                            </span>
                                                <span class="contacts-list-msg"><?php echo $row['amount']; ?> <?php echo $row['coin']; ?> Coins - <?php echo $row['reason']; ?>.</span>
                                            </div>
                                            <!-- /.contacts-list-info -->
                                        </li>
                                    <?php }}else{ echo '<li>Nodata</li>'; } ?>


                                <li>
                                    <a href="#">
                                        <img class="contacts-list-img" src="dist/img/icons/128/icon-Gift-128.png" alt="User Avatar">

                                        <div class="contacts-list-info">
                                            <span class="contacts-list-name">
                                                Redeem Gift
                                                <small class="contacts-list-date float-right">23/11/2023</small>
                                            </span>
                                            <span class="contacts-list-msg">120 L4U to 12 Leave days.</span>
                                        </div>
                                        <!-- /.contacts-list-info -->
                                    </a>
                                </li>
                                <!-- End Contact Item -->
                                <li>
                                    <a href="#">
                                        <img class="contacts-list-img" src="dist/img/icons/128/icon-Transfer-128.png" alt="User Avatar">

                                        <div class="contacts-list-info">
                                            <span class="contacts-list-name">
                                                Transfer Coin
                                                <small class="contacts-list-date float-right">20/10/2023</small>
                                            </span>
                                            <span class="contacts-list-msg">14 L4U Transfer to Nan</span>
                                        </div>
                                        <!-- /.contacts-list-info -->
                                    </a>
                                </li>
                                <!-- End Contact Item -->
                            </ul>
                            <!-- /.contacts-list -->
                        </div>
                        <!-- /.direct-chat-pane -->
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer text-center">
                        <a href="javascript:" data-toggle="modal" data-target="#formModal">More ... </a>
                    </div>
                    <!-- /.card-footer-->
                </div>
                </div>
            </div>
            <!-- /.col -->

            <div class="col-12 col-lg-6 mb-3">
                <!-- USERS LIST -->
                <div class="card gift-list gift-list-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="bi bi-box2-heart"></i> Gift list</h3>
                        <?php
                        $spendLogs = $db->query('SELECT `rcTitle`,`rcReward`,`rcSpend`,`rcPic`
                            FROM `rewardcategories`  
                            WHERE `rcStatus` = ? AND `rtID` <> ?
                            ORDER BY `rcTitle` ASC;'
                            ,1 ,1)->fetchAll();

                        $i=1;
                        ?>

                        <div class="card-tools">
                            <span class="badge badge-warning"><?php echo count($spendLogs); ?> items</span>
                            <!--<button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>X
                            <button type="button" class="btn btn-tool" data-card-widget="remove">
                                <i class="fas fa-times"></i>
                            </button>-->
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body d-flex flex-row flex-wrap " style="max-height: calc(100vh - 340px); overflow-y: auto; overflow-x: hidden; padding: 10px 10px;">
                        <?php
                        foreach ($spendLogs as $row){ ?>
                            <div class="col">
                                <div class="d-flex flex-r info-box">
                                    <div class="col-xl-12 col-lg-12 col-sm-12 col-md-12 d-flex flex-row">
                                        <a href="assets/img/reward/<?php echo $row['rcPic']; ?>" target="_blank" title="<?php echo $row['rcTitle'].' : '.$row['rcReward']; ?>"><img class="bg-info elevation-1 rounded-lg" width="130px" src="assets/img/reward/<?php echo $row['rcPic']; ?>" alt="<?php echo $row['rcTitle'].' : '.$row['rcReward']; ?>"></a>
                                        <div class="info-box-content">
                                            <span class="info-box-number" style="font-size: 0.8em; text-align: center;">
                                                    <?php echo sprintf("%03d", $row['rcSpend']); ?> L4U
                                            </span>
                                        </div>
                                        <!-- /.contacts-list-info -->
                                    </div>
                                </div>
                            </div>
                        <?php }//foreach
                        ?>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!--/.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->

        <!-- ===== OVERVIEW SECTION ===== -->
        <div class="row mt-2">

            <!-- Signup / Unsub -->
            <div class="col-12 mb-3">
                <div class="card ov-home-card">
                    <div class="card-header py-2">
                        <div class="text-left">
                            <h3 class="mb-1" style="font-size:14px;font-weight:600;">
                                <i class="bi bi-person-plus-fill mr-1 text-success"></i> Signup &amp; Unsubscribe <small class="text-muted font-weight-normal">This Week</small>
                            </h3>
                            <div class="badge badge-light text-muted" style="font-size:11px;font-weight:400;">
                                <?php echo $weekStartDisplay . '  -  ' . $weekEndDisplay; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <!-- Summary numbers (centered) -->
                        <div class="d-flex justify-content-center mb-4" style="gap:18px;">
                            <div class="ov-home-num ov-home-num-lg ov-home-green">
                                <span>+<?php echo $ovTotalSignup; ?></span>
                                <small>Signup</small>
                            </div>
                            <div class="ov-home-num ov-home-num-lg ov-home-red">
                                <span>-<?php echo $ovTotalUnsub; ?></span>
                                <small>Unsub</small>
                            </div>
                            <div class="ov-home-num ov-home-num-lg <?php echo $ovNet >= 0 ? 'ov-home-warn' : 'ov-home-gray'; ?>">
                                <span><?php echo ($ovNet >= 0 ? '+' : '') . $ovNet; ?></span>
                                <small>Net</small>
                            </div>
                        </div>

                        <!-- ── Signup Section (top) ── -->
                        <h5 class="mb-3" style="font-size:14px;font-weight:700;color:#10b981;"><i class="bi bi-plus-circle"></i> Signup</h5>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <h6 class="text-center mb-2" style="font-size:11px;font-weight:600;color:#475569;">By Country</h6>
                                <div style="height:220px;"><canvas id="chartSignupCountry"></canvas></div>
                                <table class="table table-sm table-borderless mt-2 ov-num-table">
                                    <tbody>
                                    <?php foreach(array_slice($ovSignupByCountry, 0, 10, true) as $k => $v): ?>
                                        <tr><td><?php echo htmlspecialchars($k); ?></td><td class="text-right font-weight-bold text-success"><?php echo $v; ?></td></tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($ovSignupByCountry)): ?><tr><td class="text-muted text-center" colspan="2">No data</td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-center mb-2" style="font-size:11px;font-weight:600;color:#475569;">By Type</h6>
                                <div style="height:220px;"><canvas id="chartSignupType"></canvas></div>
                                <table class="table table-sm table-borderless mt-2 ov-num-table">
                                    <tbody>
                                    <?php foreach(array_slice($ovSignupByType, 0, 10, true) as $k => $v): ?>
                                        <tr><td><?php echo htmlspecialchars($k); ?></td><td class="text-right font-weight-bold text-success"><?php echo $v; ?></td></tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($ovSignupByType)): ?><tr><td class="text-muted text-center" colspan="2">No data</td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <hr style="border-color:#e2e8f0;margin:0 0 16px 0;">

                        <!-- ── Unsubscribe Section (bottom) ── -->
                        <h5 class="mb-3" style="font-size:14px;font-weight:700;color:#ef4444;"><i class="bi bi-dash-circle"></i> Unsubscribe</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <h6 class="text-center mb-2" style="font-size:11px;font-weight:600;color:#475569;">By Country</h6>
                                <div style="height:220px;"><canvas id="chartUnsubCountry"></canvas></div>
                                <table class="table table-sm table-borderless mt-2 ov-num-table">
                                    <tbody>
                                    <?php foreach(array_slice($ovUnsubByCountry, 0, 10, true) as $k => $v): ?>
                                        <tr><td><?php echo htmlspecialchars($k); ?></td><td class="text-right font-weight-bold text-danger"><?php echo $v; ?></td></tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($ovUnsubByCountry)): ?><tr><td class="text-muted text-center" colspan="2">No data</td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-center mb-2" style="font-size:11px;font-weight:600;color:#475569;">By Type</h6>
                                <div style="height:220px;"><canvas id="chartUnsubType"></canvas></div>
                                <table class="table table-sm table-borderless mt-2 ov-num-table">
                                    <tbody>
                                    <?php foreach(array_slice($ovUnsubByType, 0, 10, true) as $k => $v): ?>
                                        <tr><td><?php echo htmlspecialchars($k); ?></td><td class="text-right font-weight-bold text-danger"><?php echo $v; ?></td></tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($ovUnsubByType)): ?><tr><td class="text-muted text-center" colspan="2">No data</td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /.row overview -->

    </div><!-- /.container-fluid -->
    <!-- Modal -->
    <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formModalLabel">History of receiving coins</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="p-2">
                        <a href="#" id="btnReceive" onclick="switchTable('receive');" class="btn btn-secondary disabled">History of receiving coins</a>
                        <a href="#" id="btnSpend" onclick="switchTable('spend');" class="btn btn-primary">Spending history</a>
                    </p>
                    <div class="card">
                        <div class="card-body d-flex">
                            <div id="tableReceivingHistory" class="w-100">
                                <table class="table table-striped table-hover">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Coin</th>
                                        <th scope="col">From</th>
                                        <th scope="col">Reason</th>
                                        <th scope="col">Date</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if(count($logs)>=1){
                                        $i=1;
                                        foreach ($logs as $row){
                                            $params['logs'][] = $row['amount'].' '.$row['coin'].' By '.$row['from'].' - '.showDate($row['giveOn']).' # '.$row['reason'];
                                            ?>
                                            <tr>
                                                <td><?php echo $i; ?></td>
                                                <td><?php echo $row['amount'].' '.$row['coin']; ?></td>
                                                <td><?php echo showName($row['nick'],$row['from']); ?></td>
                                                <td><?php echo $row['reason']; ?></td>
                                                <td><?php echo showDate($row['giveOn']); ?></td>
                                            </tr>
                                            <?php
                                            $i++;
                                        }//foreach
                                    }else{ ?>
                                    <tr>
                                        <td colspan="5">-- No received history yet --</td>
                                    </tr>
                                    <?php }//else ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="tableSpendingHistory" class="w-100" style="display: none;">
                                <table class="table table-striped table-hover">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Coin</th>
                                        <th scope="col">Spend type</th>
                                        <th scope="col">Detail</th>
                                        <th scope="col">Date</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $spendLogs = $db->query('SELECT SL.`id`, CT.`name`AS "coin", SL.`amount`, ST.`name` AS "SpendType", SL.`reason`, SL.`spendOn` 
                                                               FROM `SpendLogs` SL, `SpendType` ST, `CoinType` CT  
                                                               WHERE SL.`spendType` = ST.`id` AND SL.`coinType` = CT.`id` AND SL.`ownerID` = ? AND SL.`status` = ? 
                                                               ORDER BY SL.`spendOn` DESC'
                                        ,$loginID, 1)->fetchAll();
                                    if (count($spendLogs)>=1){
                                        $i=1;
                                        foreach ($spendLogs as $row){ ?>
                                            <tr>
                                                <td><?php echo $i; ?></td>
                                                <td><?php echo $row['amount'].' '.$row['coin']; ?></td>
                                                <td><?php echo $row['SpendType']; ?></td>
                                                <td><?php echo $row['reason']; ?></td>
                                                <td><?php echo showDate($row['spendOn']); ?></td>
                                            </tr>
                                            <?php
                                            $i++;
                                        }//foreach
                                    }else{
                                    ?>
                                        <tr>
                                            <td colspan="5">-- No spending history yet --</td>
                                        </tr>
                                    <?php }//else ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div> <!-- card -->
                </div> <!-- modal-body -->

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div> <!-- modal -->

    <!-- Modal -->
    <div class="modal fade" id="formModalExchangeCash" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formModalLabel"><i class="bi bi-cash-stack"></i> Exchange for cash</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body d-flex flex-column">
                            <h5><i class="bi bi-question-circle"></i> How does it work?</h5>
                                <ol>
                                    <li><span class="red">Only L4U coins are available for redemption.</span> If you have GM coins, please use the <span class="text-success font-weight-bold">coin convert menu</span>.</li>
                                    <li>The redeem button becomes available once you have enough coins to redeem.</li>
                                    <li>Please make an exchange transaction before the 20th of every month.</li>
                                    <li>If you make an exchange transaction after the 21st of the month, it will be carried over to the next month.</li>
                                    <li>The redeemed money will be credited to your account along with your salary. </li>
                                    <li>Please redeem by the 20th of each month. Requests after the deadline will be processed in the following month.</li>
                                </ol>
                            <div>
                                <h5>Your coin : </h5>
                                <div class="row mb-3">
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <div class="info">
                                            <i class="bi bi-coin"></i>
                                            <span class="badge badge-pill badge-warning">L4U</span>
                                            <span class="info-box-number"><?php echo number_format($coins["l4u"],2); ?></span>
                                            <!-- /.info-box-content -->
                                        </div>
                                        <!-- /.info-box -->
                                    </div>
                                    <!-- /.col -->

                                    <!-- fix for small devices only -->
                                    <div class="clearfix hidden-md-up"></div>

                                    <div class="col-12 col-sm-6 col-md-3">
                                        <div class="info">
                                            <i class="bi bi-cash-coin"></i>
                                            <span class="badge badge-pill badge-primary">GM</span>
                                            <span class="info-box-number"><?php echo number_format($coins["ceo"],2); ?></span>
                                            <!-- /.info-box-content -->
                                        </div>
                                        <!-- /.info-box -->
                                    </div>
                                    <!-- /.col -->
                                </div>
                            </div>
                            <div>
                                <table class="table table-striped table-hover">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th style="width:80px;" scope="col">Need</th>
                                        <th scope="col">Reward</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $rewards = $db->query('SELECT c.`rcID` AS "id",t.rtName AS "type", c.`rcTitle` AS "title", c.`rcSpend` "spend", c.`rcReward` AS "reward" FROM `rewardcategories` c, `rewardstype` t WHERE c.rtID = t.rtID AND c.rcStatus=? AND c.rtID=?;'
                                        , 1,1)->fetchAll();
                                    if (count($rewards)>=1){
                                        $i=1;
                                        foreach ($rewards as $row){ ?>
                                            <tr>
                                                <td class="text-right"><?php echo number_format($row['spend']); ?></td>
                                                <td><small class="text-muted"><?php echo $row['title'].'</small> >> '.$row['reward']; ?></td>
                                                <td class="text-right">
                                                    <?php if($row['spend'] <= $coins["l4u"]){ ?>
                                                        <a href="#" onclick="makeRedeem(<?php echo $row['spend']; ?>, '<?php echo $row['type']; ?>');" class="btn btn-primary" >Redeem</a>
                                                    <?php }else{ ?>
                                                    <a href="#" class="btn btn-secondary disabled" >Not enough</a>
                                                    <?php } ?>

                                                </td>
                                            </tr>
                                            <?php
                                            $i++;
                                        }//foreach
                                    }else{
                                        ?>
                                        <tr>
                                            <td colspan="5">-- No data --</td>
                                        </tr>
                                    <?php }//else ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div> <!-- card -->

                </div> <!-- modal-body -->

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div> <!-- modal -->

    <!-- Modal -->
    <div class="modal fade" id="formModalConvertCoin" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formModalLabel"><i class="bi bi-arrow-left-right"></i> Convert Coin</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body d-flex flex-column">
                            <h5><i class="bi bi-question-circle"></i> How does it work?</h5>
                            <ol>
                                <li>Coins used to pay for items or special privileges in the system  <span class="text-success font-weight-bold">must be L4U coins only</span>.</li>
                                <li>You can freely exchange coins between L4U and GM.</li>
                            </ol>
                            <div>
                                <h5>Your coin : </h5>
                                <div class="row mb-3">
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <div class="info">
                                            <i class="bi bi-coin"></i>
                                            <span class="badge badge-pill badge-warning">L4U</span>
                                            <span class="info-box-number"><?php echo number_format($coins["l4u"],2); ?></span>
                                            <!-- /.info-box-content -->
                                        </div>
                                        <!-- /.info-box -->
                                    </div>
                                    <!-- /.col -->

                                    <!-- fix for small devices only -->
                                    <div class="clearfix hidden-md-up"></div>

                                    <div class="col-12 col-sm-6 col-md-3">
                                        <div class="info">
                                            <i class="bi bi-cash-coin"></i>
                                            <span class="badge badge-pill badge-primary">GM</span>
                                            <span class="info-box-number"><?php echo number_format($coins["ceo"],2); ?></span>
                                            <!-- /.info-box-content -->
                                        </div>
                                        <!-- /.info-box -->
                                    </div>
                                    <!-- /.col -->
                                </div>
                            </div>
                        </div>
                    </div> <!-- card -->

                    <div class="card">
                        <div class="card-body d-flex flex-column">
                            <div class="mt-3">
                                <div>
                                    <h5 class="text-info"><i class="fas fa-coins"> </i> GM >> L4U</h5>
                                    <div class="form-group form-inline">
                                        <label for="CEOSource" class="mr-sm-2">GM</label>
                                        <input id="CEOSource" onchange="calConvert('ceo');" onkeyup="calConvert('ceo');" class="form-control w-25 mr-sm-2" <?php echo ($coins["ceo"]>=0)?'':'disabled' ?> type="number" value="<?php echo $coins["ceo"]; ?>" step="1" min="0" max="<?php echo $coins["ceo"]; ?>">
                                        <span class="mr-sm-2">=</span>
                                        <span class="text-success mr-3" id="calCEO"><?php echo ceoToL4u($coins["ceo"]); ?></span>
                                        <a href="#" onclick="convertCoin('ceo');" id="btnConvertCEO" class="btn <?php echo ($coins["ceo"]>=0)?'btn-primary':'btn-secondary disabled' ?>">Convert</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body d-flex flex-column">
                            <div class="mt-3">
                                <div>
                                    <h5 class="text-info"><i class="fas fa-coins"> </i> L4U >> GM</h5>
                                    <div class="form-group form-inline">
                                        <label for="L4USource" class="mr-sm-2">L4U</label>
                                        <input id="L4USource" onchange="calConvert('l4u');" onkeyup="calConvert('l4u');" class="form-control w-25 mr-sm-2" <?php echo ($coins["l4u"]>10)?'':'disabled' ?> type="number" value="<?php echo $coins["l4u"]; ?>" step="1" min="0" max="<?php echo $coins["l4u"]; ?>">
                                        <span class="mr-sm-2">=</span>
                                        <span class="text-success mr-3" id="calL4U"><?php echo l4uToCEO($coins["l4u"]); ?></span>
                                        <a href="#" onclick="convertCoin('l4u');" id="btnConvertL4U" class="btn <?php echo ($coins["l4u"]>10)?'btn-primary':'btn-secondary disabled' ?>">Convert</a>
                                    </div>
                                    <small class="text-muted">* minimum 10</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- modal-body -->

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div> <!-- modal -->

    <!-- Modal -->
    <div class="modal fade" id="formModalTransferCoin" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formModalLabel"><i class="bi bi-arrow-right-circle-fill"></i> Transfer Coin</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                            <div class="card-body d-flex flex-column">
                                <h5><i class="bi bi-question-circle"></i> How does it work?</h5>
                                <ol>
                                    <li>Transferred coin must be <span class="text-success font-weight-bold">L4U coins only</span>.</li>
                                    <li>You cannot undo this action.</li>
                                </ol>
                                <div>
                                    <h5>Your coin : </h5>
                                    <div class="row mb-3">
                                        <div class="col-12 col-sm-6 col-md-3">
                                            <div class="info">
                                                <i class="bi bi-coin"></i>
                                                <span class="badge badge-pill badge-warning">L4U</span>
                                                <span class="info-box-number"><?php echo number_format($coins["l4u"],2); ?></span>
                                                <!-- /.info-box-content -->
                                            </div>
                                            <!-- /.info-box -->
                                        </div>
                                        <!-- /.col -->

                                        <!-- fix for small devices only -->
                                        <div class="clearfix hidden-md-up"></div>

                                        <div class="col-12 col-sm-6 col-md-3">
                                            <div class="info">
                                                <i class="bi bi-cash-coin"></i>
                                                <span class="badge badge-pill badge-primary">GM</span>
                                                <span class="info-box-number"><?php echo number_format($coins["ceo"],2); ?></span>
                                                <!-- /.info-box-content -->
                                            </div>
                                            <!-- /.info-box -->
                                        </div>
                                        <!-- /.col -->
                                    </div>
                                </div>
                            </div>
                        </div> <!-- card -->

                    <div class="card">
                        <div class="card-body d-flex flex-column">
                                <div class="mt-3">
                                    <h5 class="text-info"><i class="fas fa-coins"> </i> Amount >> Receiver</h5>
                                    <div class="form-group form-inline">
                                        <label for="transferAmount" class="mr-sm-2">AMOUNT</label>
                                        <input id="transferAmount" onchange="" class="form-control w-25 mr-sm-2" type="number" value="<?php echo $coins["l4u"]; ?>" step="1" min="0" max="<?php echo $coins["l4u"]; ?>">
                                        <span class="mr-sm-2">>></span>

                                        <?php
                                        $receiver = $db->query('SELECT `sID`, `sName`, `sNickName` FROM `staffs` WHERE `sStatus`=? ORDER BY `sNickName` ASC;',1
                                            )->fetchAll();
                                        if (!empty($receiver)) {
                                            $no = 1;
                                            ?>
                                            <div class="form-group mr-2">
                                                <select id="receiver" name="receiver" class="form-control">
                                                <option value="receiver" disabled selected>-- Please choose a receiver --</option>
                                                    <?php foreach ($receiver as $row) { ?>
                                                        <option value="<?php echo $row['sID']; ?>">
                                                            <?php echo $no.': '.$row['sNickName'] . ' ( ' . nameOnly($row['sName']) . ' )'; ?>
                                                        </option>
                                                    <?php
                                                    $no++; // $no = $no + 1;
                                                    }//foreach
                                                    ?>
                                                </select>
                                            </div>

                                            <?php if("#transferAmount" <= $coins["l4u"]){ ?>
                                                <a href="#" type="button" class="btn btn-primary" onclick="transferCoin()" class="btn btn-primary" >Transfer</a>
                                            <?php }else{ ?>
                                                <a href="#" class="btn btn-secondary disabled" >Not enough</a>
                                            <?php }//else ?>

                                            <?php
                                        } else { ?>
                                            <div class="form-group">
                                                <p class="text-muted">-- No receivers found --</p>
                                            </div>
                                        <?php }//else ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- card -->

                </div> <!-- modal-body -->

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div> <!-- modal -->

    <div class="modal fade" id="formModalRedeemGiftCard" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formModalLabel"><i class="bi bi-box2-heart-fill"></i> Redeem Gift Card</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">`
                        <div class="card-body d-flex flex-column">
                            <div>
                                <h5><i class="bi bi-question-circle"></i> How does it work?</h5>
                                <ol>
                                    <li><span class="red">Only L4U coins are available for redemption.</span>  If you have GM coins, please use the <span class="text-success font-weight-bold">coin convert menu</span>.</li>
                                    <li>The redeem button becomes available once you have enough coins to redeem.</li>
                                    <li>Please make an exchange transaction before the 20th of every month.</li>
                                    <li>If you make an exchange transaction after the 21st of the month, it will be carried over to the next month.</li>
                                    <li>The value of the gift exchanged will be adjusted based on current economic conditions.</li>
                                    <li>Admin will contact you for gift delivery details.</li>
                                </ol>
                            <div>
                                <h5>Your coin : </h5>
                                <div class="row mb-3">
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <div class="info">
                                            <i class="bi bi-coin"></i>
                                            <span class="badge badge-pill badge-warning">L4U</span>
                                            <span class="info-box-number"><?php echo number_format($coins["l4u"],2); ?></span>
                                            <!-- /.info-box-content -->
                                        </div>
                                        <!-- /.info-box -->
                                    </div>
                                    <!-- /.col -->

                                    <!-- fix for small devices only -->
                                    <div class="clearfix hidden-md-up"></div>

                                    <div class="col-12 col-sm-6 col-md-3">
                                        <div class="info">
                                            <i class="bi bi-cash-coin"></i>
                                            <span class="badge badge-pill badge-primary">GM</span>
                                            <span class="info-box-number"><?php echo number_format($coins["ceo"],2); ?></span>
                                            <!-- /.info-box-content -->
                                        </div>
                                        <!-- /.info-box -->
                                    </div>
                                    <!-- /.col -->
                                </div>
                            </div>
                            <div>
                                <table class="table table-striped table-hover">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th scope="col">Need</th>
                                        <th scope="col">Reward</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $rewards = $db->query('SELECT c.`rcID` AS "id",t.rtName AS "type", c.`rcTitle` AS "title", c.`rcSpend` "spend", c.`rcReward` AS "reward" FROM `rewardcategories` c, `rewardstype` t WHERE c.rtID = t.rtID AND c.rcStatus=? AND c.rtID<>?;'
                                        , 1,1)->fetchAll();
                                    if (count($rewards)>=1){
                                        $i=1;
                                        foreach ($rewards as $row){ ?>
                                            <tr>
                                                <td class="text-right"><?php echo number_format($row['spend']); ?></td>
                                                <td><?php echo '<strong>'.$row['title'].'</strong> >> <small class="text-muted">'.$row['reward'].'</small>'; ?></td>
                                                <td>
                                                    <?php if($row['spend'] <= $coins["l4u"]){ ?>
                                                        <a href="#" onclick="makeRedeem(<?php echo $row['spend']; ?>, '<?php echo $row['type']; ?>');" class="btn btn-primary" >Redeem</a>
                                                    <?php }else{ ?>
                                                    <a href="#" class="btn btn-secondary disabled" >Not enough</a>
                                                    <?php } ?>

                                                </td>
                                            </tr>
                                            <?php
                                            $i++;
                                        }//foreach
                                    }else{
                                        ?>
                                        <tr>
                                            <td colspan="5">-- No data --</td>
                                        </tr>
                                    <?php }//else ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div> <!-- card -->

                </div> <!-- modal-body -->
            </div>
        </div>
    </div> <!-- modal -->
</div>
<!-- /.content -->

<script>
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

    const loadData = () => {
        console.log("loadData()");
    }

    const formSave = () => {
        console.log('form save');
    }// const

    const resetForm = () => {
        console.log('reset form');
        loadData();

    }// const

    const openForm = (formType) => {
        console.log("openForm");
        modalForm.show();
    }

    const switchTable = (table) => {
      const tableReceivingHistory = $("#tableReceivingHistory");
      const tableSpendingHistory = $("#tableSpendingHistory");
      const btnReceive = $("#btnReceive");
      const btnSpend = $("#btnSpend");

      if(table === "receive" ){
          btnReceive.removeClass('btn-primary');
          btnReceive.addClass('btn-secondary disabled');
          btnSpend.removeClass('btn-secondary disabled');
          btnSpend.addClass('btn-primary');

          tableSpendingHistory.hide();
          tableReceivingHistory.show();
      }else if(table === "spend" ){
          btnSpend.removeClass('btn-primary');
          btnSpend.addClass('btn-secondary disabled');
          btnReceive.removeClass('btn-secondary disabled');
          btnReceive.addClass('btn-primary');

          tableReceivingHistory.hide();
          tableSpendingHistory.show();
      }
    }

    const convertCoin = (type) => {
        let confirmText = "Are you sure you want to do convert your coin? \nYou can't undo this action.";
        let ans = confirm(confirmText);

        const CEOSource = $("#CEOSource");
        const L4USource = $("#L4USource");
        let addL4U = L4USource.val();
        let addCEO = CEOSource.val();

        let inputCoin = 0;
        if (type==='ceo'){
            inputCoin = addCEO;
        }else if (type==='l4u'){
            inputCoin = addL4U;
        }

        if (ans){
            let params = {
                act: "convert",
                sourceCoin: type,
                input: inputCoin
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

    }//convertCoin

    //คำนวนตัวเลขที่จะ convert
    const calConvert = (type) => {
        const CEOSource = $("#CEOSource");
        const L4USource = $("#L4USource");

        const calCEO = $("#calCEO");
        const calL4U = $("#calL4U");

        const max = {
            "ceo": <?php echo $coins["ceo"]; ?>,
            "l4u": <?php echo $coins["l4u"]; ?>
        };

        const coinValue = {
            "ceo": 10,
            "l4u": 1
        };

        let addL4U = L4USource.val();
        let addCEO = CEOSource.val();
        let result = 0;

        if (addCEO>max.ceo){
            addCEO = max.ceo;
            CEOSource.val(max.ceo);
        } else if (addL4U>max.l4u){
            addL4U = max.l4u;
            L4USource.val(max.l4u);
        }

        if (type==='ceo'){
            result = addCEO * coinValue.ceo;
            calCEO.html(result.toFixed(2));
        }else if (type==='l4u'){
            result = addL4U / coinValue.ceo;
            calL4U.html(result.toFixed(2));
        }

    }//calConvert

    const transferCoin = () => {
        let confirmText = "Are you sure you want to Transfer your coin? \nYou can't undo this action.";
        let ans = confirm(confirmText);

        const transferAmount = $('#transferAmount').val();
        const receiverId = $('#receiver').find(":selected").val();

        if (ans){
            let params = {
                act: "transferCoin",
                transferAmount: transferAmount,
                receiverId: receiverId
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
                if (res.error === undefined){location.reload();}
                else {alert(res.error);}
                location.reload();
            });

            reqAjax.fail(function (xhr, status, error) {
                console.log("ajax request fail!!");
                console.log(status + ": " + error);
            });
        }

    }//transferCoin

    const makeRedeem = (spend, type) => {
        let confirmText = "Are you sure you want to redeem your "+spend+" coins? \nYou cannot undo this action.";
        let ans = confirm(confirmText);

        if (ans){
            let params = {
                act: "redeem",
                input: spend,
                redeemType: type
            };

            console.log(params);

            const reqAjax = $.ajax({
                url: "https://report.localforyou.com/assets/php/actionCoin.php",
                method: "POST",
                async: false,
                cache: false,
                dataType: "json",
                data: params,
            });

            reqAjax.done(function (res) {
                console.log('Redeem Response:', res);
                
                // Send email via webhook if emailData exists
                if (res.emailData) {
                    sendEmailWebhook(res.emailData);
                }
                
                
            });

            reqAjax.fail(function (xhr, status, error) {
                console.log("ajax request fail!!");
                console.log(status + ": " + error);
                alert('Request Failed: ' + status + ' - ' + error);
            });
        }

    }//makeRedeem

    const sendEmailWebhook = (emailData) => {
        const webhookUrl = 'https://hook.us1.make.com/8rugbc9g9qh3ihcfbkpy8wwx7wgtwyby';
        
        $.ajax({
            url: webhookUrl,
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify(emailData),
            success: function(response) {
                console.log('Webhook sent successfully:', response);
                location.reload();
            },
            error: function(xhr, status, error) {
                console.log('Webhook error:', status, error);
            }
        });
    }//sendEmailWebhook
</script>
<?php
function showDate($data){
    return date( "d/m/Y (H:i)", strtotime($data));
}

function showName($nick, $full){
    $tmp = explode(" ", $full);
    $getName = ($nick == $tmp[0]) ? $tmp[1] : $tmp[0];
    return $nick.' '.$getName;
}
?>
<?php
function ceoToL4u($param){
    $tmp = $param*10;
    $tmp = number_format($tmp,2);
    return($tmp);
}

function l4uToCEO($param){
    $tmp = $param/10;
    $tmp = number_format($tmp,2);
    return($tmp);
}

function nameOnly($fullName){
    $tmp = explode(" ", $fullName);
    return $tmp[0];
}//nameOnly
?>

<!-- ===== Overview Section Styles ===== -->
<style>
.ov-home-card { border-radius:10px; border:1px solid #e2e8f0; }
.ov-home-card .card-header { background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:8px 14px; }

.ov-home-num { display:flex; flex-direction:column; align-items:center; border-radius:8px; padding:6px 14px; min-width:64px; background:#f1f5f9; }
.ov-home-num span { font-size:1.4rem; font-weight:700; line-height:1; }
.ov-home-num small { font-size:0.7rem; color:#64748b; margin-top:2px; }
.ov-home-num-lg { padding:14px 28px; min-width:100px; border-radius:12px; }
.ov-home-num-lg span { font-size:2.2rem; }
.ov-home-num-lg small { font-size:0.85rem; margin-top:4px; }
.ov-home-green span { color:#10b981; }
.ov-home-red   span { color:#ef4444; }
.ov-home-warn  span { color:#f59e0b; }
.ov-home-gray  span { color:#64748b; }
.ov-chart-col { padding-top:10px; padding-bottom:10px; }
.ov-num-table { font-size:11px; margin-bottom:0; }
.ov-num-table td { padding:2px 6px; }
.ov-num-table td:first-child { color:#475569; max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
@media (max-width:767px) { .ov-chart-col { border-left:none!important; border-top:2px solid #e2e8f0; margin-top:10px; } }
</style>

<!-- ===== Overview Section Scripts ===== -->
<script>
(function(){
    try {
        var COLORS = ['#0361D1','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16','#f97316','#6366f1'];
        var ovData = <?php echo $ovDataJson ?: '{}'; ?>;

        function makeBar(canvasId, dataObj, barColor) {
            if (!dataObj) return;
            var labels = Object.keys(dataObj);
            var values = [];
            for (var i = 0; i < labels.length; i++) {
                values.push(dataObj[labels[i]]);
            }
            if (labels.length === 0) return;
            var canvas = document.querySelector(canvasId);
            if (!canvas) return;
            new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: barColor || COLORS.slice(0, labels.length),
                        borderWidth: 0,
                        barPercentage: 0.65,
                        categoryPercentage: 0.8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    scales: {
                        yAxes: [{ ticks: { beginAtZero: true, stepSize: 1, fontSize: 10 }, gridLines: { color: '#f1f5f9' } }],
                        xAxes: [{ ticks: { fontSize: 9, maxRotation: 45, minRotation: 0 }, gridLines: { display: false } }]
                    },
                    tooltips: {
                        callbacks: { label: function(t) { return t.yLabel; } }
                    }
                }
            });
        }

        var greenBars = ovData.signup ? Object.keys(ovData.signup.byCountry||{}).map(function(){ return '#10b981'; }) : [];
        var greenBars2 = ovData.signup ? Object.keys(ovData.signup.byType||{}).map(function(){ return '#34d399'; }) : [];
        var redBars = ovData.unsub ? Object.keys(ovData.unsub.byCountry||{}).map(function(){ return '#ef4444'; }) : [];
        var redBars2 = ovData.unsub ? Object.keys(ovData.unsub.byType||{}).map(function(){ return '#f87171'; }) : [];

        makeBar('#chartSignupCountry', ovData.signup ? ovData.signup.byCountry : {}, greenBars);
        makeBar('#chartSignupType',    ovData.signup ? ovData.signup.byType : {},    greenBars2);
        makeBar('#chartUnsubCountry',  ovData.unsub  ? ovData.unsub.byCountry : {}, redBars);
        makeBar('#chartUnsubType',     ovData.unsub  ? ovData.unsub.byType : {},    redBars2);
    } catch(e) {
        console.error('Overview chart error:', e);
    }
})();
</script>
