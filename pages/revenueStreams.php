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
                    <svg class="nav-icon mr-2" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512"><path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z" fill="#000000" /></svg>
                    Revenue Tracking / Generic Rev Streams
                </h3>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php">My Desk</a></li>
                    <li class="breadcrumb-item"><a href="#">Revenue Tracking</a></li>
                    <li class="breadcrumb-item active">Generic Rev Streams</li>
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
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <p class="text-center font-weight-bold">
                            <img src="assets/img/money-dollar.svg" alt="Dollar Bill" width="24">
                            Stripe Connect Rev
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
            </div><!-- /.col-md-4 -->

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <p class="text-center font-weight-bold">
                            <img src="assets/img/money-dollar.svg" alt="Dollar Bill" width="24">
                            Stripe Connect 4% +30c Booking
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
            </div><!-- /.col-md-4 -->

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <p class="text-center font-weight-bold">
                            <img src="assets/img/money-dollar.svg" alt="Dollar Bill" width="24">
                            SMS Transmist
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
            </div><!-- /.col-md-4 -->

        </div><!-- /.row -->

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <p class="text-center font-weight-bold">
                            <img src="assets/img/money-dollar.svg" alt="Dollar Bill" width="24">
                            Domain Name Reseller
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
                            Yelp Partnership
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
        </div><!-- /.row -->

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <p class="text-center font-weight-bold">
                            <img src="assets/img/icon-target.svg" alt="Target" width="24">
                            Revenue targets
                        </p>

                        <h1 class="display-6 text-center text-primary" id="revenueTargets">
                            <?php echo "$".number_format(($total["all"]),2); ?>
                        </h1>
                    </div>
                </div>
            </div><!-- /.col-md-4 -->

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <p class="text-center font-weight-bold">
                            <img src="assets/img/icon-target.svg" alt="Target" width="24">
                            Actual Revenue
                        </p>

                        <h1 class="display-6 text-center text-primary" id="revenueActual">
                            <?php echo "$".number_format(($total["all"]),2); ?>
                        </h1>
                    </div>
                </div>
            </div><!-- /.col-md-4 -->

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <p class="text-center font-weight-bold">
                            <img src="assets/img/icon-target.svg" alt="Target" width="24">
                            Total Revenue
                        </p>

                        <h1 class="display-6 text-center text-primary" id="revenueTotal">
                            <?php echo "$".number_format(($total["all"]),2); ?>
                        </h1>
                    </div>
                </div>
            </div><!-- /.col-md-4 -->

        </div><!-- /.row -->

    </div><!-- /.container-fluid -->

    <!-- Modal -->
    <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formModalLabel">Form Manage Generic Streams</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body d-flex">
                            <h3 class="text-warning">Not ready yet</h3>
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

        /*const formAction = $("#formAction");
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
        });*/
    }// const

    const resetForm = () => {
        console.log('reset form');
        loadData();

    }// const

    const openForm = (formType) => {
        console.log("openForm");
        modalForm.show();
    }
</script>