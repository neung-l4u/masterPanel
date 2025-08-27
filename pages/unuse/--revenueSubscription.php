<?php
global $db, $date;
$timestamp = time();
$thisYear = date("Y", $timestamp);
$thisMonth = date("m", $timestamp);
$thisDay = date("d-m-Y", $timestamp);
$yearScope = range(($thisYear-3),($thisYear+1),1);
$monthScope = range(1,12,1);
?>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">
                    <svg class="nav-icon mr-2" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><path d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zM96 136c0-13.3 10.7-24 24-24c137 0 248 111 248 248c0 13.3-10.7 24-24 24s-24-10.7-24-24c0-110.5-89.5-200-200-200c-13.3 0-24-10.7-24-24zm0 96c0-13.3 10.7-24 24-24c83.9 0 152 68.1 152 152c0 13.3-10.7 24-24 24s-24-10.7-24-24c0-57.4-46.6-104-104-104c-13.3 0-24-10.7-24-24zm0 120a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z" fill="#000000" /></svg>
                    Revenue Tracking / Subscription Fees
                </h3>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php">My Desk</a></li>
                    <li class="breadcrumb-item"><a href="#">Revenue Tracking</a></li>
                    <li class="breadcrumb-item active">Subscription Fees</li>
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
                            <select id="selectedYear" class="form-control" onchange="reloadData();">
                                <?php
                                foreach($yearScope as $no => $year) { ?>
                                <option value="<?php echo $year; ?>" <?php echo ($year == $thisYear) ? 'selected' : ''; ?> ><?php echo $year; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mr-3 d-flex align-items-center">
                            <label for="selectedMonth" class="mr-3">Month</label>
                            <select id="selectedMonth" class="form-control" onchange="reloadData();">
                                <?php
                                foreach($monthScope as  $month) {
                                    $month_name = date("F", mktime(0, 0, 0, $month, 10));
                                ?>
                                    <option value="<?php echo sprintf("%02d", $month); ?>" <?php echo (sprintf("%02d", $month) == $thisMonth) ? 'selected' : ''; ?> ><?php echo sprintf("%02d", $month).' : '.$month_name; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <button id="btnModal" type="button" class="btn btn-primary" data-toggle="modal" data-target="#formModal">
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M352 320c88.4 0 160-71.6 160-160c0-15.3-2.2-30.1-6.2-44.2c-3.1-10.8-16.4-13.2-24.3-5.3l-76.8 76.8c-3 3-7.1 4.7-11.3 4.7H336c-8.8 0-16-7.2-16-16V118.6c0-4.2 1.7-8.3 4.7-11.3l76.8-76.8c7.9-7.9 5.4-21.2-5.3-24.3C382.1 2.2 367.3 0 352 0C263.6 0 192 71.6 192 160c0 19.1 3.4 37.5 9.5 54.5L19.9 396.1C7.2 408.8 0 426.1 0 444.1C0 481.6 30.4 512 67.9 512c18 0 35.3-7.2 48-19.9L297.5 310.5c17 6.2 35.4 9.5 54.5 9.5zM80 408a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" fill="#FFFFFF" /></svg>
                            Manage Data
                        </button>

                    </div>
                </div>
            </div><!-- /.col-md-12 -->
        </div><!-- /.row -->

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Monthly</h5>
                        <div id="dataCost" class="mt-4">
                            <table id="datatable" class="table table-borderless table-striped table-hover" style="width:100%">
                                <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Service</th>
                                    <th>Per month</th>
                                </tr>
                                </thead>
                            </table>
                        </div>

                    </div>
                </div>
            </div><!-- /.col-md-6 -->

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Yearly</h5>
                        <div id="dataCost" class="mt-4">
                            <table id="datatable2" class="table table-borderless table-striped table-hover" style="width:100%">
                                <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Service</th>
                                    <th>Paid</th>
                                    <th>Per Year</th>
                                </tr>
                                </thead>
                            </table>
                        </div>

                    </div>
                </div>
            </div><!-- /.col-md-6 -->
        </div><!-- /.row -->

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="card-text d-flex justify-content-between">
                            <div class="text-primary">Total</div>
                            <div class="text-primary" id="totalMonthly">$0.00</div>
                        </div>
                    </div>
                </div>
            </div><!-- /.col-md-6 -->

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="card-text d-flex justify-content-between">
                            <div class="text-primary">Total</div>
                            <div class="text-primary" id="totalYearly">$0.00</div>
                        </div>
                    </div>
                </div>
            </div><!-- /.col-md-6 -->

        </div><!-- /.row -->

    </div><!-- /.container-fluid -->
</div>
<!-- /.content -->

<!-- Modal -->
<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formModalLabel">Form Manage Subscriptions Fee</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column">
                    <div class="form-group">
                        <label for="typeID">Subscriptions Period</label>
                        <select id="typeID" name="typeID" class="form-control" onchange="showForm();">
                            <option value="0" selected disabled>-- Please select Period type --</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="d-flex">
                        <div class="form-group mr-3 frmMonthly frmYearly" style="display: none;" onchange="loadAddedService();">
                            <label for="year">Year</label>
                            <select id="year" name="year" class="form-control">
                                <?php
                                foreach($yearScope as $no => $year) { ?>
                                    <option value="<?php echo $year; ?>" <?php echo ($year == $thisYear) ? 'selected' : ''; ?> ><?php echo $year; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group mr-3 frmMonthly" style="display: none;" onchange="loadAddedService();">
                            <label for="month">Month</label>
                            <select id="month" name="month" class="form-control">
                                <?php
                                $months = array( '01'=>'January', '02'=>'February', '03'=>'March', '04'=>'April', '05'=>'May', '06'=>'June', '07'=>'July ', '08'=>'August', '09'=>'September', '10'=>'October', '11'=>'November', '12'=>'December' );
                                foreach($months as $no => $month) { ?>
                                    <option value="<?php echo $no; ?>" <?php echo ($no == $thisMonth) ? 'selected' : ''; ?> ><?php echo $no . " : " . $month; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex">
                        <div class="form-group mr-2 frmMonthly" style="display: none;">
                            <label for="serviceMonth">Monthly Service</label>
                            <select id="serviceMonth" name="serviceMonth" class="form-control" onchange="loadAddedService();">
                                <option value="0" selected disabled>-- Please select Monthly Service --</option>
                                <?php
                                $result = $db->query('SELECT `id`, `name` FROM `SucscriptionsType` WHERE `period` = ? AND `status` =  ? ORDER BY `name`;', "Monthly", 1)->fetchAll();
                                $i = 1;
                                foreach ($result as $row) { ?>
                                    <option value="<?php echo $row["id"]; ?>"><?php echo $i . " : " . $row["name"]; ?></option>
                                <?php $i++; } //foreach ?>
                            </select>
                        </div>

                        <div class="form-group mr-2 frmYearly" style="display: none;">
                            <label for="serviceYear">Yearly Service</label>
                            <select id="serviceYear" name="serviceYear" class="form-control" onchange="loadAddedService();">
                                <option value="0" selected disabled>-- Please select Yearly Service --</option>
                                <?php
                                $result = $db->query('SELECT `id`, `name` FROM `SucscriptionsType` WHERE `period` = ? AND `status` =  ? ORDER BY `name`;', "Yearly", 1)->fetchAll();
                                $i = 1;
                                foreach ($result as $row) { ?>
                                    <option value="<?php echo $row["id"]; ?>"><?php echo $i . " : " . $row["name"]; ?></option>
                                    <?php $i++; } //foreach ?>
                            </select>
                        </div>

                        <div class="form-group mr-3 frmYearly" style="display: none;">
                            <label class="mr-2" for="datepicker">Paid on</label>
                            <input type="text" class="form-control" id="datepicker" name="datepicker" value="<?php echo $thisDay; ?>">
                        </div>
                    </div>

                    <div class="d-flex">
                        <div class="form-group mr-3 frmMonthly frmYearly" style="display: none;">
                            <label for="total">Total</label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input id="total" name="total" type="text" class="form-control" placeholder="250.53">
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="frmMonthly frmYearly" style="display: none;">
                                <input type="hidden" name="formAction" nonce="formAction" id="formAction" value="add">
                                <button onclick="formSave();" type="button" class="btn btn-primary" name="cmdSubmit" id="cmdSubmit">Add</button>
                            </div>
                        </div>
                    </div>
                </div> <!-- flex -->

                <div class="card">
                    <div id="serviceList" class="card-body" style="display: none;">
                        <p class="text-muted">Please select Service above.</p>
                    </div>
                </div> <!-- card -->
            </div> <!-- modal-body -->

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div> <!-- modal -->

<script>
const loadCostData = () => {
    //$("#dataCost").html("Yes");
    const dataCost=$("#dataCost");

    const reqAjax = $.ajax({
        url: "pages/tableRendering/dataCost.php",
        method: "POST",
        async: false,
        cache: false,
        dataType: "json",
        data: {
            act: "getCost"
        },
    }); //const

    reqAjax.done(function (res) {
        dataCost.html(res.table);
    }); //done

    reqAjax.fail(function (xhr, status, error) {
        console.log("ajax request fail!!");
        console.log(status + ": " + error);
    }); //fail
} // const

const showForm = () => {
    const typeID = $("#typeID");
    const frmMonthly = $(".frmMonthly");
    const frmYearly = $(".frmYearly");
    const formAction = $("#formAction");
    const paidOn = $("#datepicker");
    const serviceList = $("#serviceList");
    const serviceMonth = $("#serviceMonth");
    const serviceYear = $("#serviceYear");

    if(typeID.val() === "Monthly"){
        frmYearly.hide();
        frmMonthly.show();
        formAction.val("Monthly");
    }else if(typeID.val() === "Yearly"){
        frmMonthly.hide();
        frmYearly.show();
        formAction.val("Yearly");
    }

    serviceList.html('<p class="text-muted">Please select Service above.</p>').hide();
    serviceMonth.val(0);
    serviceYear.val(0);
} // const

const resetForm = () => {
    console.log('reset form');
    const frmMonthly = $(".frmMonthly");
    const frmYearly = $(".frmYearly");
    const typeID = $("#typeID");
    const year = $("#year");
    const month = $("#month");
    const serviceMonth = $("#serviceMonth");
    const serviceYear = $("#serviceYear");
    const paidOn = $("#datepicker");
    const total = $("#total");
    const formAction = $("#formAction");
    const serviceList = $("#serviceList");

    let objectDate = new Date();
    const thisDay = objectDate.getDate();
    const thisYear = objectDate.getFullYear();
    const thisMonth = objectDate.getMonth()+1;
    const toDay = thisDay+"-"+thisMonth+"-"+thisYear;

    typeID.val(0);
    year.val(thisYear);
    month.val(thisMonth);
    serviceMonth.val(0);
    serviceYear.val(0);
    paidOn.val(toDay);
    total.val("");
    formAction.val("");
    frmMonthly.hide();
    frmYearly.hide();
    serviceList.html('<p class="text-muted">Please select Service above.</p>').hide();

}// const

const formSave = () => {
    console.log('form save');

    const typeID = $("#typeID");
    const year = $("#year");
    const month = $("#month");
    const serviceMonth = $("#serviceMonth");
    const serviceYear = $("#serviceYear");
    const paidOn = $("#datepicker");
    const total = $("#total");
    const formAction = $("#formAction");

    let objectDate = new Date();
    const thisDay = objectDate.getDate();
    const thisYear = objectDate.getFullYear();
    const thisMonth = objectDate.getMonth()+1;
    const toDay = thisDay+"-"+thisMonth+"-"+thisYear;

    let serviceID = 0;
    if(typeID.val() === "Monthly"){
        serviceID = serviceMonth.val();
    }else if(typeID.val() === "Yearly"){
        serviceID = serviceYear.val();
    }

    const reqAjax = $.ajax({
        url: "assets/php/actionSubscription.php",
        method: "POST",
        async: false,
        cache: false,
        dataType: "json",
        data: {
            act: "save",
            year: year.val(),
            month: month.val(),
            paidOn: paidOn.val(),
            total: total.val(),
            formAction: formAction.val(),
            serviceID: serviceID
        },
    });

    reqAjax.done(function (res) {
        console.log(res);
        if(typeID.val() === "Monthly"){
            reloadTable();
            loadTotalMonthly();
        }else if(typeID.val() === "Yearly"){
            reloadTable2();
            loadTotalYearly();
        }

        loadAddedService();
    });

    reqAjax.fail(function (xhr, status, error) {
        console.log("ajax request fail!!");
        console.log(status + ": " + error);
    });
}// const

const loadAddedService = () => {
    const typeID = $("#typeID");
    const month = $("#month");
    const year = $("#year");
    const serviceList = $("#serviceList");
    const serviceMonth = $("#serviceMonth");
    const serviceYear = $("#serviceYear");

    let serviceID = 0;
    if(typeID.val() === "Monthly"){
        serviceID = serviceMonth.val();
    }else if(typeID.val() === "Yearly"){
        serviceID = serviceYear.val();
    }

    const reqAjax = $.ajax({
        url: "assets/php/getSubscriptionServiceAdded.php",
        method: "POST",
        async: false,
        cache: false,
        dataType: "json",
        data: {
            act: "getAddedService",
            typeID: typeID.val(),
            serviceID: serviceID,
            month: month.val(),
            year: year.val()
        },
    });

    reqAjax.done(function (res) {
        serviceList.html(res.table).show();
    });

    reqAjax.fail(function (xhr, status, error) {
        serviceList.html("load data fail!!");
        console.log("ajax request fail!!");
        console.log(status + ": " + error);
    });

} //const

const setDel = (id) => {
    let answer = confirm("Are you sure you want to delete this? \n This item will be deleted immediately. \n\n You can't undo this action.");
    const typeID = $("#typeID");

    if (answer){
        const reqAjax = $.ajax({
            url: "assets/php/actionSubscription.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: {
                act: "setDelete",
                typeID: typeID.val(),
                id: id,
            },
        }); //const

        reqAjax.done(function (res) {
            if(typeID.val() === "Monthly"){
                reloadTable();
                loadTotalMonthly();
            }else if(typeID.val() === "Yearly"){
                reloadTable2();
                loadTotalYearly();
            }

            loadAddedService();
        }); //done

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        }); //fail
    } //if

}// const

</script>