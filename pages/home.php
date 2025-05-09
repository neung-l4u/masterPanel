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

?>
<!-- ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
</style>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <svg class="nav-icon mr-2" height="1.1em" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 576 512"><path d="M575.8 255.5c0 18-15 32.1-32 32.1l-32 0 .7 160.2c0 2.7-.2 5.4-.5 8.1l0 16.2c0 22.1-17.9 40-40 40l-16 0c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1L416 512l-24 0c-22.1 0-40-17.9-40-40l0-24 0-64c0-17.7-14.3-32-32-32l-64 0c-17.7 0-32 14.3-32 32l0 64 0 24c0 22.1-17.9 40-40 40l-24 0-31.9 0c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2l-16 0c-22.1 0-40-17.9-40-40l0-112c0-.9 0-1.9 .1-2.8l0-69.7-32 0c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
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
            <div class="col-3 col-sm-3 col-md-3 col-lg-3 col-xl-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-warning elevation-1">L4U</span>
                    <!--<i class="fas fa-thumbs-up"></i>-->
                    <div class="info-box-content">
                        <span class="info-box-text">Coin(s)</span>
                        <span class="info-box-number"><?php echo number_format($coins["l4u"],2); ?></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix hidden-md-up"></div>

            <div class="col-3 col-sm-3 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-primary elevation-1">CEO</span>
                    <div class="info-box-content">
                        <span class="info-box-text">Coin(s)</span>
                        <span class="info-box-number"><?php echo number_format($coins["ceo"],2); ?></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->

            <div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6">
                <div class="info-box align-items-center">
                    <div class="card-body p-0 ">
                            <div class="d-flex flex-row justify-content-around">
                                <a class="btn btn-outline-primary linkBTN w-auto" data-toggle="modal" data-target="#formModalExchangeCash">
                                    <i class="fas fa-money-bill"></i><span class="d-none d-sm-none d-md-none d-lg-block"> Cash</span>
                                </a>
                                <a class="btn btn-outline-primary linkBTN w-auto" data-toggle="modal" data-target="#formModalConvertCoin">
                                    <i class="fas fa-coins"></i><span class="d-none d-sm-none d-md-none d-lg-block">  Convert</span>
                                </a>
                                <a class="btn btn-outline-primary linkBTN w-auto" data-toggle="modal" data-target="#formModalTransferCoin">
                                    <i class="fas fa-arrow-right"></i><span class="d-none d-sm-none d-md-none d-lg-block">  Transfer</span>
                                </a>
                                <a class="btn btn-outline-primary linkBTN w-auto" data-toggle="modal" data-target="#formModalRedeemGiftCard">
                                    <i class="fas fa-gift"></i><span class="d-none d-sm-none d-md-none d-lg-block">  Gift Card</span>
                                </a>
                            </div>
                    </div>
                </div>
            </div>

        <div class="row">
            <div class="col-md-6">
                <div>
                    <div class="card direct-chat direct-chat-warning">
                    <div class="card-header" >
                        <h3 class="card-title">History of receiving coins (Last 30 days)</h3>
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
                    <div class="card-body" style="height: 28vh; overflow-y: auto; overflow-x: hidden; padding: 0 10px;">
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
                <div>
                    <!-- Change log -->
                    <div class="card">
                        <div class="card-header" >
                            <h3 class="card-title">Change Logs</h3>
                            <div class="card-tools">
                                <!-- <span title="0 items" class="badge badge-warning">0 items</span>-->
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div><!-- /.card-header -->
                        <div class="card-body" style="height: 25vh; overflow-y: auto; overflow-x: hidden; padding: 0 10px;">
                            <div class="direct-chat-messages">
                                <div class="direct-chat-msg">
                                    <div class="direct-chat-infos clearfix">
                                        <span class="float-left">
                                            <h6>02 May 2025</h6>
                                            <ul>
                                                <li>Add page Website Lists.</li>
                                            </ul>
                                            <h6>22 March 2025</h6>
                                            <ul>
                                                <li>Create Tools page.</li>
                                                <li>Create Unsubscribe view log page</li>
                                            </ul>
                                            <h6>18 March 2025</h6>
                                            <ul>
                                                <li>Change UX of Tools > SignUp form.</li>
                                                <li>Add calendar to Monday Report page.</li>
                                                <li>Change to new logo.</li>
                                                <li>add show password on the login page.</li>
                                            </ul>
                                            <h6>16 March 2025</h6>
                                            <ul>
                                                <li>Template submission always cc to admin@localforyou.com </li>
                                            </ul>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div><!-- /.card-body -->
                    </div><!--card-->
                </div> <!-- Change log -->
            </div>
            <!-- /.col -->

            <div class="col-md-6">
                <!-- USERS LIST -->
                <div class="card gift-list gift-list-warning">
                    <div class="card-header">
                        <h3 class="card-title">Gift list</h3>
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
                    <div class="card-body d-flex flex-row flex-wrap justify-content-center align-items-center" style="height: 65vh; overflow-y: auto; overflow-x: hidden; padding: 0 10px;">
                        <?php
                        foreach ($spendLogs as $row){ ?>
                            <div class="col-xl-4 col-lg-4 col-sm-4 col-md-4">
                                <div class="d-flex flex-r info-box">
                                    <div class="col-xl-12 col-lg-12 col-sm-12 col-md-12 d-flex flex-column flex-wrap justify-content-center align-items-center">
                                        <img class="info-box-icon bg-info elevation-1" width="150px" height="80px" src="assets/img/reward/<?php echo $row['rcPic']; ?>" alt="Reward image">

                                        <div class="info-box-content">
                                            <span class="img-rounded" style="font-size: 0.7em; text-align: center;">
                                                <abbr title="<?php echo $row['rcTitle']; ?>">
                                                    <?php echo $row['rcTitle']; ?>
                                                </abbr>
                                            </span>
                                            <span class="info-box-number" style="font-size: 0.8em; text-align: center;">
                                                    <?php echo $row['rcSpend']; ?> L4U
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
                    <h5 class="modal-title" id="formModalLabel">Exchange for cash</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body d-flex flex-column">
                            <h4>How does it work?</h4>
                                <ol>
                                    <li>Only L4U coins are available for redemption. If you have CEO coins, please use the <span class="text-success font-weight-bold">coin convert menu</span>.</li>
                                    <li>The redeem button becomes available once you have enough coins to redeem.</li>
                                    <li>Please make an exchange transaction before the 20th of every month.</li>
                                    <li>If you make an exchange transaction after the 21st of the month, it will be carried over to the next month.</li>
                                    <li>The redeemed money will be credited to your account along with your salary. </li>
                                </ol>
                            <div>
                                <h5>Your coin : </h5>
                                <div class="row mb-3">
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <div class="info">
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
                                            <span class="badge badge-pill badge-primary">CEO</span>
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
                                        <th scope="col">#</th>
                                        <th scope="col">Title</th>
                                        <th scope="col">Require</th>
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
                                                <td><?php echo $i; ?></td>
                                                <td><?php echo $row['title']; ?></td>
                                                <td><?php echo number_format($row['spend']); ?></td>
                                                <td><?php echo $row['reward']; ?></td>
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
                    <h5 class="modal-title" id="formModalLabel">Convert Coin</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body d-flex flex-column">
                            <h4>How does it work?</h4>
                            <ol>
                                <li>Coins used to pay for items or special privileges in the system  <span class="text-success font-weight-bold">must be L4U coins only</span>.</li>
                                <li>You can freely exchange coins between L4U and CEO.</li>
                            </ol>
                            <div>
                                <h5>Your coin : </h5>
                                <div class="row mb-3">
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <div class="info">
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
                                            <span class="badge badge-pill badge-primary">CEO</span>
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
                                    <h5 class="text-info"><i class="fas fa-coins"> </i> CEO >> L4U</h5>
                                    <div class="form-group form-inline">
                                        <label for="CEOSource" class="mr-sm-2">CEO</label>
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
                                    <h5 class="text-info"><i class="fas fa-coins"> </i> L4U >> CEO</h5>
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
                    <h5 class="modal-title" id="formModalLabel">Transfer Coin</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                            <div class="card-body d-flex flex-column">
                                <h4>How does it work?</h4>
                                <ol>
                                    <li>Transferred coin must be <span class="text-success font-weight-bold">L4U coins only</span>.</li>
                                    <li>You cannot undo this action.</li>
                                </ol>
                                <div>
                                    <h5>Your coin : </h5>
                                    <div class="row mb-3">
                                        <div class="col-12 col-sm-6 col-md-3">
                                            <div class="info">
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
                                                <span class="badge badge-pill badge-primary">CEO</span>
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
                    <h5 class="modal-title" id="formModalLabel">Redeem Gift Card</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body d-flex flex-column">
                            <div>
                            <h4>How does it work?</h4>
                                <ol>
                                    <li>Only L4U coins are available for redemption. If you have CEO coins, please use the <span class="text-success font-weight-bold">coin convert menu</span>.</li>
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
                                            <span class="badge badge-pill badge-primary">CEO</span>
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
                                        <th scope="col">#</th>
                                        <th scope="col">Title</th>
                                        <th scope="col">Require</th>
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
                                                <td><?php echo $i; ?></td>
                                                <td><?php echo $row['title']; ?></td>
                                                <td><?php echo number_format($row['spend']); ?></td>
                                                <td><?php echo $row['reward']; ?></td>
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

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div> <!-- modal -->
</div>
<!-- /.content -->
</div>

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
                console.log(res);
                location.reload();
            });

            reqAjax.fail(function (xhr, status, error) {
                console.log("ajax request fail!!");
                console.log(status + ": " + error);
            });
        }

    }//makeRedeem
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
