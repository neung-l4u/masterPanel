<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-LGKDYHL23T');
</script>
<?php
global $db;
$userLevel = $_SESSION['level'];

$activities = $db->query('SELECT `aID`, `aName` FROM `CoinActivities` ORDER BY `aName`;')->fetchAll();
$teams = $db->query('SELECT `id`, `name`, `fullName` FROM `Team` ORDER BY `idx`;')->fetchAll();
?>

<link rel="stylesheet" href="assets/css/coin.css">
<link rel="stylesheet" href="plugins/datatables-bs5/css/datatables-bs5.min.css">

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <i class="bi bi-gem mr-2"></i>
                    Reward Coin History
                </h4>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item"><a href="main.php">My Desk</a></li>
                    <li class="breadcrumb-item active">Reward Coin</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <!-- Filter Card -->
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-6 col-md-4 col-lg-2 mb-2">
                        <label for="filterCoinType" class="small mb-1">Coin Type</label>
                        <select id="filterCoinType" class="form-control form-control-sm" onchange="filterChange();">
                            <option value="">All</option>
                            <option value="1">L4U</option>
                            <option value="2">CEO</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 mb-2">
                        <label for="filterActivity" class="small mb-1">Activity</label>
                        <select id="filterActivity" class="form-control form-control-sm" onchange="filterChange();">
                            <option value="">All</option>
                            <?php foreach ($activities as $act) { ?>
                                <option value="<?php echo $act['aID']; ?>"><?php echo $act['aName']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 mb-2">
                        <label for="filterTeam" class="small mb-1">Team</label>
                        <select id="filterTeam" class="form-control form-control-sm" onchange="filterChange();">
                            <option value="">All</option>
                            <?php foreach ($teams as $team) { ?>
                                <option value="<?php echo $team['id']; ?>"><?php echo $team['name'] . ' : ' . $team['fullName']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 mb-2">
                        <label for="filterDateFrom" class="small mb-1">From</label>
                        <input type="date" id="filterDateFrom" class="form-control form-control-sm" onchange="filterChange();">
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 mb-2">
                        <label for="filterDateTo" class="small mb-1">To</label>
                        <input type="date" id="filterDateTo" class="form-control form-control-sm" onchange="filterChange();">
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 mb-2 d-flex align-items-end">
                        <button class="btn btn-sm btn-secondary w-100" onclick="clearFilter();">Clear Filter</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-table mr-2"></i>
                            Reward Coin Logs
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table id="rewardCoinTable" class="table table-bordered table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th style="width: 80px;">User</th>
                                    <th style="width: 60px;">Team</th>
                                    <th class="text-center" style="width: 100px;">Total Coin</th>
                                    <th style="min-width: 250px;">Reward / Reason</th>
                                    <th class="text-center" style="width: 120px;">Date & Time</th>
                                    <th class="text-center" style="width: 80px;">Exchange</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div><!-- /.col-md-12 -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content -->

<!-- Empty Modal for main.php compatibility -->
<div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content"></div></div>
</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/datatables-bs5/js/datatables-bs5.min.js"></script>
<script>
$(function() {
    $('#rewardCoinTable').DataTable({
        pagingType: 'full_numbers',
        pageLength: 10,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, 'All']
        ],
        ajax: {
            url: 'pages/tableRendering/dataRewardCoin.php',
            type: 'POST',
            dataSrc: 'data',
            data: function(d) {
                d.coinType = $('#filterCoinType').val();
                d.activity = $('#filterActivity').val();
                d.team = $('#filterTeam').val();
                d.dateFrom = $('#filterDateFrom').val();
                d.dateTo = $('#filterDateTo').val();
            }
        },
        order: [[4, 'desc']],
        columnDefs: [
            { targets: [0, 3, 5, 6], className: 'text-center' },
            { targets: [0], orderable: false },
            { 
                targets: 0,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            }
        ],
        language: {
            emptyTable: "No reward coin data available",
            zeroRecords: "No matching records found"
        }
    });
});

function filterChange() {
    $('#rewardCoinTable').DataTable().ajax.reload();
}

function clearFilter() {
    $('#filterCoinType').val('');
    $('#filterActivity').val('');
    $('#filterTeam').val('');
    $('#filterDateFrom').val('');
    $('#filterDateTo').val('');
    filterChange();
}
</script>
