<?php
global $db, $date;
$timestamp = time();
$thisYear = date("Y", $timestamp);
$thisMonth = date("m", $timestamp);
$thisDay = date("d-m-Y", $timestamp);
$yearScope = range(($thisYear-3),($thisYear+1),1);
$monthScope = range(1,12,1);

$total["IHD20AU"] = $db->query('SELECT * FROM `IHDDetail` WHERE `year`=? AND `month`=? AND `typeID`=? ORDER BY `id` DESC;', $thisYear, $thisMonth, 1)->fetchArray();
$total["IHDMarketAU"] = $db->query('SELECT * FROM `IHDDetail` WHERE `year`=? AND `month`=? AND `typeID`=? ORDER BY `id` DESC;', $thisYear, $thisMonth, 2)->fetchArray();
$total["IHD25USA"] = $db->query('SELECT * FROM `IHDDetail` WHERE `year`=? AND `month`=? AND `typeID`=? ORDER BY `id` DESC;', $thisYear, $thisMonth, 3)->fetchArray();
$total["IHDMarketUSA"] = $db->query('SELECT * FROM `IHDDetail` WHERE `year`=? AND `month`=? AND `typeID`=? ORDER BY `id` DESC;', $thisYear, $thisMonth, 4)->fetchArray();

$total["all"] = ($total["IHD20AU"]["value"]*0.2) + ($total["IHDMarketAU"]["value"]*1.0) + ($total["IHD25USA"]["value"]*0.25) + ($total["IHDMarketUSA"]["value"]*1.0);
?>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">
                    <svg class="nav-icon mr-2" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><path d="M48 0C21.5 0 0 21.5 0 48V368c0 26.5 21.5 48 48 48H64c0 53 43 96 96 96s96-43 96-96H384c0 53 43 96 96 96s96-43 96-96h32c17.7 0 32-14.3 32-32s-14.3-32-32-32V288 256 237.3c0-17-6.7-33.3-18.7-45.3L512 114.7c-12-12-28.3-18.7-45.3-18.7H416V48c0-26.5-21.5-48-48-48H48zM416 160h50.7L544 237.3V256H416V160zM112 416a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zm368-48a48 48 0 1 1 0 96 48 48 0 1 1 0-96z" fill="#000000" /></svg>
                    Revenue Tracking / IHD
                </h3>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php">My Desk</a></li>
                    <li class="breadcrumb-item"><a href="#">Revenue Tracking</a></li>
                    <li class="breadcrumb-item active">IHD</li>
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
                            <select id="selectedYear" class="form-control" onchange="reloadIHD();">
                                <?php
                                foreach($yearScope as $no => $year) { ?>
                                    <option value="<?php echo $year; ?>" <?php echo ($year == $thisYear) ? 'selected' : ''; ?> ><?php echo $year; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mr-3 d-flex align-items-center">
                            <label for="selectedMonth" class="mr-3">Month</label>
                            <select id="selectedMonth" class="form-control" onchange="reloadIHD();">
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
                        <p class="text-center font-weight-bold">
                            <img src="assets/img/money-dollar.svg" alt="Dollar Bill" width="24">
                            IHD 20c AU
                        </p>

                        <p class="display-4 text-center totalIHD" id="IHD20AU">
                            <?php echo "$".number_format(($total["IHD20AU"]["value"]*0.2),2); ?>
                        </p>

                        <a href="#" onclick="openForm(1);" class="card-link" >
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z" fill="#0d6efd" /></svg>
                            Add
                        </a>
                    </div>
                </div>
            </div><!-- /.col-md-6 -->

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <p class="text-center font-weight-bold">
                            <img src="assets/img/money-dollar.svg" alt="Dollar Bill" width="24">
                            IHD $1 Marketplace AU
                        </p>

                        <p class="display-4 text-center totalIHD" id="IHDMarketAU">
                            <?php echo "$".number_format(($total["IHDMarketAU"]["value"]*1.0),2); ?>
                        </p>

                        <a href="#" onclick="openForm(2);" class="card-link" >
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z" fill="#0d6efd" /></svg>
                            Add
                        </a>
                    </div>
                </div>
            </div><!-- /.col-md-6 -->
        </div><!-- /.row -->

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <p class="text-center font-weight-bold">
                            <img src="assets/img/money-dollar.svg" alt="Dollar Bill" width="24">
                            IHD 25c USA
                        </p>

                        <p class="display-4 text-center totalIHD" id="IHD25USA">
                            <?php echo "$".number_format(($total["IHD25USA"]["value"]*0.25),2); ?>
                        </p>

                        <a href="#" onclick="openForm(3);" class="card-link" >
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z" fill="#0d6efd" /></svg>
                            Add
                        </a>
                    </div>
                </div>
            </div><!-- /.col-md-6 -->

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <p class="text-center font-weight-bold">
                            <img src="assets/img/money-dollar.svg" alt="Dollar Bill" width="24">
                            IHD $1 Marketplace USA
                        </p>

                        <p class="display-4 text-center totalIHD" id="IHDMarketUSA">
                            <?php echo "$".number_format(($total["IHDMarketUSA"]["value"]*1.0),2); ?>
                        </p>

                        <a href="#" onclick="openForm(4);" class="card-link" >
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z" fill="#0d6efd" /></svg>
                            Add
                        </a>
                    </div>
                </div>
            </div><!-- /.col-md-6 -->
        </div><!-- /.row -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <p class="text-center font-weight-bold">
                            <img src="assets/img/money-dollar.svg" alt="Dollar Bill" width="24">
                            Total
                        </p>

                        <p class="display-4 text-center totalIHD text-primary" id="IHDAll">
                            <?php echo "$".number_format(($total["all"]),2); ?>
                        </p>
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
                    <h5 class="modal-title" id="formModalLabel">Form Manage IHD</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-column">
                        <h5 id="IHDTypeText">AAA</h5>
                            <div class="d-flex">
                                <div class="form-group mr-3">
                                    <label for="year">Year</label>
                                    <select id="year" name="year" class="form-control" onchange="loadAddedService();">
                                        <?php
                                        foreach($yearScope as $no => $year) { ?>
                                            <option value="<?php echo $year; ?>" <?php echo ($year == $thisYear) ? 'selected' : ''; ?> ><?php echo $year; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="form-group mr-3">
                                    <label for="month">Month</label>
                                    <select id="month" name="month" class="form-control" onchange="loadAddedService();">
                                        <?php
                                        $months = array( '01'=>'January', '02'=>'February', '03'=>'March', '04'=>'April', '05'=>'May', '06'=>'June', '07'=>'July ', '08'=>'August', '09'=>'September', '10'=>'October', '11'=>'November', '12'=>'December' );
                                        foreach($months as $no => $month) { ?>
                                            <option value="<?php echo $no; ?>" <?php echo ($no == $thisMonth) ? 'selected' : ''; ?> ><?php echo $no . " : " . $month; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div> <!-- flex -->

                        <div class="d-flex">
                            <div class="form-group mr-3">
                                <label for="value">How many ?</label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Times</span>
                                    </div>
                                    <input id="value" name="value" type="number" class="form-control" placeholder="124">
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div>
                                    <input type="hidden" name="formAction" id="formAction" value="add">
                                    <input type="hidden" name="typeID" id="typeID" value="0">
                                    <button onclick="formSave();" type="button" class="btn btn-primary" name="cmdSubmit" id="cmdSubmit">Add</button>
                                </div>
                            </div>
                        </div> <!-- flex -->

                    <div class="card">
                        <div id="addedList" class="card-body">
                            <p class="text-muted">No data found of this month.</p>
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

<script>
    const resetForm = () => {
        console.log('reset form');
        const typeID = $("#typeID");
        const formAction = $("#formAction");
        const year = $("#year");
        const month = $("#month");
        const IHDTypeText = $("#IHDTypeText");
        const value = $("#value");

        let objectDate = new Date();
        const thisYear = objectDate.getFullYear();
        const thisMonth = objectDate.getMonth()+1;

        IHDTypeText.html("no type found");
        year.val(thisYear);
        month.val(thisMonth);
        typeID.val(0);
        value.val("");
        formAction.val("add");

    }// const

    const openForm = (formType) => {
        const IHDTypeText = $("#IHDTypeText");
        if (formType===1){ IHDTypeText.html("IHD 20c AU"); }
        else if (formType===2){ IHDTypeText.html("IHD $1 Marketplace AU"); }
        else if (formType===3){ IHDTypeText.html("IHD 25c USA"); }
        else if (formType===4){ IHDTypeText.html("IHD $1 Marketplace USA"); }
        const typeID = $("#typeID");
        typeID.val(formType);
        loadAddedService();
        modalForm.show();
    }

    const reloadIHD = () => {
        const year = $("#selectedYear");
        const month = $("#selectedMonth");
        const IHD20AU = $("#IHD20AU");
        const IHDMarketAU = $("#IHDMarketAU");
        const IHD25USA = $("#IHD25USA");
        const IHDMarketUSA = $("#IHDMarketUSA");
        const IHDAll = $("#IHDAll");

        const reqAjax = $.ajax({
            url: "assets/php/getStatIHD.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: {
                act: "getIHD",
                month: month.val(),
                year: year.val()
            },
        });

        reqAjax.done(function (res) {
            IHD20AU.html(res.IHD20AU);
            IHDMarketAU.html(res.IHDMarketAU);
            IHD25USA.html(res.IHD25USA);
            IHDMarketUSA.html(res.IHDMarketUSA);
            IHDAll.html(res.total);
        });

        reqAjax.fail(function (xhr, status, error) {
            let failText = '<span class="text-danger">Load failed</span>';
            IHD20AU.html(failText);
            IHDMarketAU.html(failText);
            IHD25USA.html(failText);
            IHDMarketUSA.html(failText);
            IHDAll.html(failText);
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
        const value = $("#value");

        const reqAjax = $.ajax({
            url: "assets/php/actionIHD.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: {
                act: "save",
                year: year.val(),
                month: month.val(),
                value: value.val(),
                typeID: typeID.val(),
                formAction: formAction.val()
            },
        });

        reqAjax.done(function (res) {
            console.log(res);
            reloadIHD();
            loadAddedService();
            value.val("");
        });

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        });
    }// const

    const loadAddedService = () => {
        //alert("loadAddedService = "+id);
        const typeID = $("#typeID");
        const month = $("#month");
        const year = $("#year");
        const addedList = $("#addedList");

        const reqAjax = $.ajax({
            url: "assets/php/getIHDAdded.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: {
                act: "getAddedIHD",
                typeID: typeID.val(),
                month: month.val(),
                year: year.val()
            },
        });

        reqAjax.done(function (res) {
            addedList.html(res.table).show();
        });

        reqAjax.fail(function (xhr, status, error) {
            addedList.html("load data fail!!");
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        });

    } //const

    const setDel = (id) => {
        let answer = confirm("Are you sure you want to delete this? \n This item will be deleted immediately. \n\n You can't undo this action.");
        const typeID = $("#typeID");

        if (answer){
            const reqAjax = $.ajax({
                url: "assets/php/actionIHD.php",
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
                console.log("deleted");
                loadAddedService();
                reloadIHD();
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

        reloadIHD();
    }// const
</script>