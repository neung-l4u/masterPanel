<?php
global $showPage, $datatable, $datatable2, $db, $loadTotal, $title;
session_start();
include 'assets/db/db.php';
include "assets/db/initDB.php";

$myID = $_SESSION['id'];
if (isset($_COOKIE['id'])){ $myID = $_COOKIE['id']; }
$users = $db->query('SELECT `sL4U` AS "L4U", `sCEO` AS "CEO", `sNickName` FROM `staffs` WHERE `sID` = ?;', $myID)->fetchArray();
$_SESSION['L4UCoin'] = $users['L4U'];
$_SESSION['CEOCoin'] = $users['CEO'];
if (!empty($users['sNickName'])) { $_SESSION['nickName'] = $users['sNickName']; }

require ("assets/php/page_navigate.php");
date_default_timezone_set("Asia/Bangkok");
$date["today"] = date("d/m/Y", strtotime("today")); //23/02/2024
$date["thisMonth"] = date("m"); //02
$date["thisYear"] = date("Y"); //2024
$date["next30days"] = date("d/m/Y", strtotime("+ 1 months"));
$date["last30days"] = date("d/m/Y", strtotime("- 30 days"));
$date["monthName"] = array("01"=>'January', "02"=>'February', "03"=>'March', "04"=>'April', "05"=>'May', "06"=>'June', "07"=>'July', "08"=>'August', "09"=>'September', "10"=>'October', "11"=>'November', "12"=>'December');
$date["yearNumber"] = array();
for ($i=(date("Y")-3); $i<=(date("Y")+2); $i++){
    $date["yearNumber"][] = $i;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once "assets/api/googleAnalytics.php";?>
  <link rel="shortcut icon" href="assets/img/logo-login2.png?v=2">
  <link rel="icon" type="image/png" href="assets/img/logo-login2.png?v=2">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $title;?></title>

  <!-- Icon libraries -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet" media="screen" />
  <link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
  <!-- Framework CSS -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" type="text/css" href="assets/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/css/jquery-ui-v1.13.2.css">
  <!-- Daterangepicker for deliveries report -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
  <!-- Custom theme (loads last = highest priority) -->
  <link rel="stylesheet" href="assets/css/master-panel.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <!-- Navbar -->
  <?php include "navBar.php"; ?>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <?php include "sideBar.php"; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
      <?php include("modalRespond.php"); ?>
      <?php include("modalConfirm.php"); ?>
      <?php include "pages/".$showPage; ?>
      <!-- Main Footer (inside content-wrapper so it scrolls together) -->
      
  </div>
  <!-- /.content-wrapper -->
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.js"></script>

<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
    const latestActivityId = '<?php echo isset($navActivities) ? (count($navActivities) > 0 ? $navActivities[0]["id"] : 0) : 0; ?>';
    function markActivityRead(){
        const badge = document.getElementById('activityBadge');
        if(badge) badge.style.display = 'none';
        $.post('assets/api/markActivityRead.php', { latestId: latestActivityId });
    }
    let showPage = "<?php echo $showPage; ?>";
    const showDatatable = <?php echo isset($datatable["show"]) ? $datatable["show"] : false; ?>;
    const srcDatatable = '<?php echo $datatable["src"]; ?>';
    const showDatatable2 = <?php echo isset($datatable2["show"]) ? $datatable["show"] : false; ?>;
    const srcDatatable2 = '<?php echo $datatable2["src"]; ?>';
    const showDatatableStats = <?php echo isset($datatable["showDatatableStats"]) ? $datatable["showDatatableStats"] : false; ?>;
    const totalMonthly = '<?php echo $loadTotal["totalMonthly"]; ?>';
    const totalYearly = '<?php echo $loadTotal["totalYearly"]; ?>';

    const startDate = $('#startDate');
    const endDate = $("#endDate");
    const datepicker = $("#datepicker");
    let myTable = {};
    let myTable2 = {};
    let datatableStats = {};
    
    // Initialize modals only if elements exist
    const modalResponseEl = document.getElementById("modalResponse");
    const modalFormEl = document.getElementById("formModal");
    const modalConfirmEl = document.getElementById("modalConfirm");
    
    const modalResponse = modalResponseEl ? new bootstrap.Modal(modalResponseEl, {}) : null;
    const modalForm = modalFormEl ? new bootstrap.Modal(modalFormEl, {}) : null;
    const modalConfirm = modalConfirmEl ? new bootstrap.Modal(modalConfirmEl, {}) : null;

    const datepickerOption = {
        dateFormat: 'dd-mm-yy',
        showButtonPanel: true,
        changeYear: true,
        minDate: "-3M",
        maxDate: "+0D",
        numberOfMonths: 3,
        showCurrentAtPos: 1
    };

    startDate.datepicker(datepickerOption);

    endDate.datepicker(datepickerOption);

    datepicker.datepicker(datepickerOption);

    const loadDatatableStats = () => {
        myTable = new DataTable('#datatableStats',
            {
                pagingType: 'full_numbers',
                ajax: srcDatatable,
                "pageLength": 8,
                lengthMenu: [
                    [8, 25, 50, -1],
                    ['Fit', 25, 50, 'All']
                ],columnDefs: [
                    {
                        targets: -1,
                        className: 'dt-body-right'
                    }
                ]
            }
        );
    }//loadDatatableStats

    $(()=>{
        if (showDatatable){
            myTable = new DataTable('#datatable',
                {
                    pagingType: 'full_numbers',
                    ajax: srcDatatable,
                    "pageLength": 8,
                    lengthMenu: [
                        [8, 25, 50, -1],
                        ['Fit', 25, 50, 'All']
                    ],columnDefs: [
                        {
                            targets: -1,
                            className: 'dt-body-right'
                        }
                    ]
                }
            );
        } //if

        if (showDatatable2){
            myTable2 = new DataTable('#datatable2',
                {
                    pagingType: 'full_numbers',
                    ajax: srcDatatable2,
                    "pageLength": 8,
                    lengthMenu: [
                        [8, 25, 50, -1],
                        ['Fit', 25, 50, 'All']
                    ],columnDefs: [
                        {
                            targets: -1,
                            className: 'dt-body-right'
                        }
                    ]
                }
            );
        } //if

        if(showDatatableStats){
            //loadDatatableStats();

            myTable = new DataTable('#datatableStats',
                {
                    pagingType: 'full_numbers',
                    ajax: srcDatatable,
                    "pageLength": 8,
                    lengthMenu: [
                        [8, 25, 50, -1],
                        ['Fit', 25, 50, 'All']
                    ],columnDefs: [
                        {
                            targets: -1,
                            className: 'dt-body-right'
                        }
                    ]
                }
            );
        }

        if(totalMonthly){
            loadTotalMonthly();
        } //if

        if(totalYearly){
            loadTotalYearly();
        } //if

        $('#formModal').on('hide.bs.modal', function (event) {
            // do something...
            resetForm();
        });

        // Fix aria-hidden warning: blur focus before hiding any modal
        $('.modal').on('hide.bs.modal', function () {
            if (document.activeElement && this.contains(document.activeElement)) {
                document.activeElement.blur();
            }
        });

        $("#alert").hide();

        setInterval(function() {
            let reqHeartbeat = $.ajax({
                url: "assets/api/heartbeat.php",
                method: "POST",
                async: false,
                cache: false,
                dataType: "json",
            }); //const

            reqHeartbeat.done(function (data) {
                if (data.status === 'expired') {
                    alert('Your session has expired. Please log in again.');
                    window.location = 'chkLogin.php?act=expired';
                }
            }); //done

            reqHeartbeat.fail(function (xhr, status, error) {
                console.log("check heart beat fail!!");
                console.log(status + ": " + error);
            }); //fail
        }, 60000); //check heartbeat every 1 minute

    });//ready

    const reloadTable = () => {
        myTable.ajax.reload();
    }

    const reloadTable2 = () => {
        myTable2.ajax.reload();
    }
    
    const loadTotalMonthly = () => {
        const placeTotalMonthly = $("#totalMonthly");

        const reqAjax = $.ajax({
            url: "pages/tableRendering/dataTotalMonthly.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: {
                act: "getMonthly",
                year: $("#selectedYear").val(),
                month: $("#selectedMonth").val()
            },
        }); //const

        reqAjax.done(function (res) {
            placeTotalMonthly.html(res.total);
        }); //done

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        }); //fail
    } //const

    const loadTotalYearly = () => {
        const placeTotalYearly = $("#totalYearly");

        const reqAjax = $.ajax({
            url: "pages/tableRendering/dataTotalYearly.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: {
                act: "getYearly",
                year: $("#selectedYear").val(),
                month: $("#selectedMonth").val()
            },
        }); //const

        reqAjax.done(function (res) {
            placeTotalYearly.html(res.total);
        }); //done

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        }); //fail
    } //const

    const modalRespondAction = (action,status) => {
        const respondSuccess = $(".respondSuccess");
        const respondFail = $(".respondFail");
        respondSuccess.hide();
        respondFail.hide();
        if (status==="success"){
            respondSuccess.show();
        }else if (status==="fail"){
            respondFail.show();
        }
        if (action==="open"){ modalResponse.show();}
    } // const

    const modalFormAction = (action) => {
        console.log("go = "+action);
        if (action==="open"){ modalForm.show();}
        else {
            $("#formModal").modal('hide');
            modalForm.hide();
        }
    } // const



    if(showPage==="myDesk.php"){
        //-----------------------
        // - MONTHLY SALES CHART -
        //-----------------------

        // Get context with jQuery - using jQuery's .get() method.
        let salesChartCanvas = $('#salesChart').get(0).getContext('2d')

        let salesChartData = {
            labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            datasets: [
                {
                    label: 'Restaurant',
                    backgroundColor: 'rgba(60,141,188,0.9)',
                    borderColor: 'rgba(60,141,188,0.8)',
                    pointRadius: false,
                    pointColor: '#3b8bba',
                    pointStrokeColor: 'rgba(60,141,188,1)',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(60,141,188,1)',
                    data: [28, 48, 40, 19, 86, 27, 90]
                },
                {
                    label: 'Massage',
                    backgroundColor: 'rgba(210, 214, 222, 1)',
                    borderColor: 'rgba(210, 214, 222, 1)',
                    pointRadius: false,
                    pointColor: 'rgba(210, 214, 222, 1)',
                    pointStrokeColor: '#c1c7d1',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(220,220,220,1)',
                    data: [65, 59, 80, 81, 56, 55, 40]
                }
            ]
        }

        let salesChartOptions = {
            maintainAspectRatio: false,
            responsive: true,
            legend: {
                display: false
            },
            scales: {
                x: {
                    display: true
                },
                y: {
                    display: true
                }
            }
        }

        // This will get the first returned node in the jQuery collection.
        // eslint-disable-next-line no-unused-vars
        let salesChart = new Chart(salesChartCanvas, {
                type: 'line',
                data: salesChartData,
                options: salesChartOptions
            }
        )

        //---------------------------
        // - END MONTHLY SALES CHART -
        //---------------------------
    }//myDesk.php

</script>

</body>
</html>
