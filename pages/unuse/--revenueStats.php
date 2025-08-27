<?php
global $db;
$timestamp = time();
$thisYear = date("Y", $timestamp);
$thisMonth = date("m", $timestamp);
$yearScope = range(($thisYear-3),($thisYear+1),1);
$thisDay = date("d-m-Y", $timestamp);
$monthScope = range(1,12,1);

?>
<!-- ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">
                    <svg class="nav-icon mr-2" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><path d="M384 32H512c17.7 0 32 14.3 32 32s-14.3 32-32 32H398.4c-5.2 25.8-22.9 47.1-46.4 57.3V448H512c17.7 0 32 14.3 32 32s-14.3 32-32 32H320 128c-17.7 0-32-14.3-32-32s14.3-32 32-32H288V153.3c-23.5-10.3-41.2-31.6-46.4-57.3H128c-17.7 0-32-14.3-32-32s14.3-32 32-32H256c14.6-19.4 37.8-32 64-32s49.4 12.6 64 32zm55.6 288H584.4L512 195.8 439.6 320zM512 416c-62.9 0-115.2-34-126-78.9c-2.6-11 1-22.3 6.7-32.1l95.2-163.2c5-8.6 14.2-13.8 24.1-13.8s19.1 5.3 24.1 13.8l95.2 163.2c5.7 9.8 9.3 21.1 6.7 32.1C627.2 382 574.9 416 512 416zM126.8 195.8L54.4 320H199.3L126.8 195.8zM.9 337.1c-2.6-11 1-22.3 6.7-32.1l95.2-163.2c5-8.6 14.2-13.8 24.1-13.8s19.1 5.3 24.1 13.8l95.2 163.2c5.7 9.8 9.3 21.1 6.7 32.1C242 382 189.7 416 126.8 416S11.7 382 .9 337.1z" fill="#000000" /></svg>
                    Revenue Tracking / Stats to Measure
                </h3>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php">My Desk</a></li>
                    <li class="breadcrumb-item"><a href="#">Revenue Tracking</a></li>
                    <li class="breadcrumb-item active">Stats to Measure</li>
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
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h5 class="m-0">Statistics</h5>

                            <div class="d-flex">
                                <button id="btnModal" type="button" class="btn btn-primary" data-toggle="modal" data-target="#formModal">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M352 320c88.4 0 160-71.6 160-160c0-15.3-2.2-30.1-6.2-44.2c-3.1-10.8-16.4-13.2-24.3-5.3l-76.8 76.8c-3 3-7.1 4.7-11.3 4.7H336c-8.8 0-16-7.2-16-16V118.6c0-4.2 1.7-8.3 4.7-11.3l76.8-76.8c7.9-7.9 5.4-21.2-5.3-24.3C382.1 2.2 367.3 0 352 0C263.6 0 192 71.6 192 160c0 19.1 3.4 37.5 9.5 54.5L19.9 396.1C7.2 408.8 0 426.1 0 444.1C0 481.6 30.4 512 67.9 512c18 0 35.3-7.2 48-19.9L297.5 310.5c17 6.2 35.4 9.5 54.5 9.5zM80 408a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" fill="#FFFFFF" /></svg>
                                    Manage Data
                                </button>
                            </div>
                        </div>

                    </div>
                    <div class="card-body">
                        <table id="datatableStats" class="table table-borderless table-striped table-hover" style="width:100%">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Subject</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                        </table>
                    </div><!-- /.card body -->
                </div><!-- /.card -->
            </div><!-- /.col-md-6 -->

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <canvas id="barChart"></canvas>
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
                    <h5 class="modal-title" id="formModalLabel">Form Stats to Measure</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-column">
                                <div class="form-group">
                                    <label for="typeID">Measure Type</label>
                                    <select id="typeID" class="form-control" onchange="loadTypeList();">
                                        <option value="0" selected disabled>-- Please select type --</option>
                                        <?php
                                        $result = $db->query('SELECT id,name FROM `StatsMeasureType` WHERE status = 1;')->fetchAll();
                                        foreach ($result as $row) { ?>
                                            <option value="<?php echo $row["id"]; ?>"><?php echo $row["id"] . " : " . $row["name"]; ?></option>
                                        <?php } //foreach ?>
                                    </select>
                                </div>
                                <div class="d-flex">
                                    <div class="form-group mr-3">
                                        <label for="year">Year</label>
                                        <select id="year" class="form-control" onchange="loadTypeList();">
                                            <?php
                                            foreach($yearScope as $no => $year) { ?>
                                                <option value="<?php echo $year; ?>" <?php echo ($year == $thisYear) ? 'selected' : ''; ?> ><?php echo $year; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="form-group mr-3">
                                        <label for="month">Month</label>
                                        <select id="month" class="form-control">
                                            <?php
                                            $months = array( '01'=>'January', '02'=>'February', '03'=>'March', '04'=>'April', '05'=>'May', '06'=>'June', '07'=>'July ', '08'=>'August', '09'=>'September', '10'=>'October', '11'=>'November', '12'=>'December' );
                                            foreach($months as $no => $month) { ?>
                                                <option value="<?php echo $no; ?>" <?php echo ($no == $thisMonth) ? 'selected' : ''; ?> ><?php echo $no . " : " . $month; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="form-group mr-3">
                                        <label for="total">Total</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input id="total" type="text" class="form-control" placeholder="250.53">
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <input type="hidden" name="formAction" id="formAction" value="add">
                                        <button onclick="formSave();" type="button" class="btn btn-primary" name="cmdSubmit" id="cmdSubmit">Add</button>
                                    </div>
                                </div>
                            </div> <!-- flex -->
                        </div>  <!-- card-body -->
                    </div>  <!-- .card -->

                    <div class="card">
                        <div id="measureTypeList" class="card-body">
                          <p class="text-muted">Please select type above.</p>
                        </div>
                    </div>

                </div> <!-- modal-body -->

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.content -->

<script>
    const barCTX = document.getElementById('barChart');

    let myChart = new Chart(barCTX, {
        type: 'bar',
        data: {
            labels: ['Churn', 'Downgrade', 'Cancellation Lost', 'Cost of Acquisition', 'Ads/Marketing Fees', 'Sales Signup Revenue', 'Bad Debt',],
            datasets: [{
                label: '# of Revenue',
                data: ['2173.00', '2871.00', '1191.00', '334.00', '1129.00', '3984.00', '2053.00'],
                borderWidth: 1,
            }]
        },
        options: {
            indexAxis: 'x',
            aspectRatio: '1.5'
        }
    }); // CTX


    const resetForm = () => {
        console.log('reset form');
        let thisYear =  new Date().getFullYear();
        let month = (new Date().getMonth() + 1).toString().padStart(2, "0");
        $("#typeID").val("0");
        $("#year").val(thisYear);
        $("#month").val(month);
        $("#total").val("");
        $("#measureTypeList").html('<p class="text-muted">Please select type above.</p>');
        reloadTable();
        myChart.update();
    }// const

    const reloadData = () => {
        reloadTable();
        myChart.update();
    }

    const loadTypeList = () => {
        const typeID = $("#typeID");
        const measureTypeList = $("#measureTypeList");
        const month = $("#month");
        const year = $("#year");

        if (typeID.val()>0){
            const reqAjax = $.ajax({
                url: "assets/php/getStatMeasureType.php",
                method: "POST",
                async: false,
                cache: false,
                dataType: "json",
                data: {
                    act: "getType",
                    id: typeID.val(),
                    month: month.val(),
                    year: year.val()
                },
            });

            reqAjax.done(function (res) {
                measureTypeList.html(res.table);
            });

            reqAjax.fail(function (xhr, status, error) {
                measureTypeList.html("load data fail!!");
                console.log("ajax request fail!!");
                console.log(status + ": " + error);
            });
        }

    } //const

    const formSave = () => {
        const typeID = $("#typeID");
        const year = $("#year");
        const month = $("#month");
        const total = $("#total");
        const formAction = $("#formAction");

        let statusValue = $("input[name='inputStatus']:checked").val();

        const reqAjax = $.ajax({
            url: "assets/php/actionStats.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: {
                act: "save",
                typeID : typeID.val(),
                year : year.val(),
                month : month.val(),
                total : total.val(),
                formAction : formAction.val()
            },
        });

        reqAjax.done(function (res) {
            console.log(res);
            total.val("");
            loadTypeList();
            //resetForm();
        });

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        });
    }// const

    const setDel = (id) => {
        let answer = confirm("Are you sure you want to delete this? \n This item will be deleted immediately. \n\n You can't undo this action.");

        if (answer){
            const reqAjax = $.ajax({
                url: "assets/php/actionStats.php",
                method: "POST",
                async: false,
                cache: false,
                dataType: "json",
                data: {
                    act: "setDelete",
                    id: id,
                },
            }); //const

            reqAjax.done(function (res) {
                loadTypeList();
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

    const loadData = () => {
        console.log("load data");
        loadDatatableStats();
    }
</script>



