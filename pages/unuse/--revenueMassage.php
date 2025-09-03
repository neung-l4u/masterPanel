<?php
global$date;
?>
<!-- ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">
                    <svg class="nav-icon mr-2" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512"><path d="M183.1 235.3c33.7 20.7 62.9 48.1 85.8 80.5c7 9.9 13.4 20.3 19.1 31c5.7-10.8 12.1-21.1 19.1-31c22.9-32.4 52.1-59.8 85.8-80.5C437.6 207.8 490.1 192 546 192h9.9c11.1 0 20.1 9 20.1 20.1C576 360.1 456.1 480 308.1 480H288 267.9C119.9 480 0 360.1 0 212.1C0 201 9 192 20.1 192H30c55.9 0 108.4 15.8 153.1 43.3zM301.5 37.6c15.7 16.9 61.1 71.8 84.4 164.6c-38 21.6-71.4 50.8-97.9 85.6c-26.5-34.8-59.9-63.9-97.9-85.6c23.2-92.8 68.6-147.7 84.4-164.6C278 33.9 282.9 32 288 32s10 1.9 13.5 5.6z" fill="#000000" /></svg>
                    Revenue Tracking / Massage Shop
                </h3>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php">My Desk</a></li>
                    <li class="breadcrumb-item"><a href="#">Revenue Tracking</a></li>
                    <li class="breadcrumb-item active">Massage Shop</li>
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

                    <div class="card-header d-flex justify-content-between align-items-center">

                        <div class="d-inline-flex justify-content-start align-items-center">
                            <div class="input-group mr-3">
                                <label class="mr-2" for="startDate">From</label>
                                <input type="text" class="form-control" name="startDate" id="startDate" value="<?php echo $date["last30days"]; ?>">
                            </div>

                            <div class="input-group">
                                <label class="mr-2" for="endDate">To</label>
                                <input type="text" class="form-control" name="endDate" id="endDate" value="<?php echo $date["today"]; ?>">
                            </div>
                        </div>


                        <div class="d-inline-flex justify-content-end align-items-center">
                            <div class="btn-group btn-group-toggle mr-3" data-toggle="buttons">
                                <label class="btn btn-secondary active">
                                    <input type="radio" class="country_code" name="country_code" id="country_code_AU" value="AU" checked> Australia
                                </label>
                                <label class="btn btn-primary">
                                    <input type="radio" class="country_code" name="country_code" id="country_code_NZ" value="NZ"> New Zealand
                                </label>
                                <label class="btn btn-primary">
                                    <input type="radio" class="country_code" name="country_code" id="country_code_US" value="US"> Unites States
                                </label>
                                <label class="btn btn-primary">
                                    <input type="radio" class="country_code" name="country_code" id="country_code_All" value="All"> All
                                </label>
                            </div>

                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-primary" onclick="window.location.replace('main.php?p=revRestaurant');">
                                    <input type="radio" class="formType" name="00N9s000000QRyY" id="formType_rest" value="Thai Restaurants &amp; Takeaways"> Restaurants
                                </label>
                                <label class="btn btn-secondary active">
                                    <input type="radio" class="formType" name="00N9s000000QRyY" id="formType_mas" value="Thai Massage" checked> Massage
                                </label>
                            </div>

                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">
                                        <p class="card-text">
                                            <canvas id="barChart"></canvas>
                                        </p>
                                    </div>
                                </div>
                            </div><!-- /.col-md-4 -->

                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">
                                        <p class="card-text">
                                            <canvas id="doughnutChart"></canvas>
                                        </p>
                                    </div>
                                </div>
                            </div><!-- /.col-md-4 -->

                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body">
                                        <p class="card-text">
                                            <canvas id="lineChart"></canvas>
                                        </p>
                                    </div>
                                </div>
                            </div><!-- /.col-md-4 -->
                        </div><!-- /.row -->
                    </div><!--card-body-->


                </div><!-- /.card -->
            </div>
        </div><!-- /.row -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <button id="btnModal" type="button" class="btn btn-primary" data-toggle="modal" data-target="#formModal">
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M352 320c88.4 0 160-71.6 160-160c0-15.3-2.2-30.1-6.2-44.2c-3.1-10.8-16.4-13.2-24.3-5.3l-76.8 76.8c-3 3-7.1 4.7-11.3 4.7H336c-8.8 0-16-7.2-16-16V118.6c0-4.2 1.7-8.3 4.7-11.3l76.8-76.8c7.9-7.9 5.4-21.2-5.3-24.3C382.1 2.2 367.3 0 352 0C263.6 0 192 71.6 192 160c0 19.1 3.4 37.5 9.5 54.5L19.9 396.1C7.2 408.8 0 426.1 0 444.1C0 481.6 30.4 512 67.9 512c18 0 35.3-7.2 48-19.9L297.5 310.5c17 6.2 35.4 9.5 54.5 9.5zM80 408a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" fill="#FFFFFF" /></svg>
                            Manage Data
                        </button>
                    </div>
                </div><!-- /.card -->
            </div>
        </div><!-- /.row -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Table Detail</h3>

                                <div class="card-tools">
                                    <div class="input-group input-group-sm" style="width: 150px;">
                                        <input type="text" name="table_search" class="form-control float-right" placeholder="Search">

                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body table-responsive p-4" style="height: 620px;">
                                <table id="datatable" class="table table-borderless table-striped table-hover" style="width:100%">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th>Country</th>
                                        <th>Product</th>
                                        <th>Year</th>
                                        <th>Month</th>
                                        <th>Value</th>
                                    </tr>
                                    </thead>
                                    <tfoot class="thead-light">
                                    <tr>
                                        <th>Country</th>
                                        <th>Product</th>
                                        <th>Year</th>
                                        <th>Month</th>
                                        <th>Value</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                    </div>
                </div>
            </div><!-- /.col-md-12 -->
        </div><!-- /.row -->

        <!-- Modal -->
        <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formModalLabel">Revenue data</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div class="d-flex flex-column">
                            <div class="form-group">
                                <label for="revCountry">Country : </label>
                                <select class="form-control" id="revCountry">
                                    <?php
                                    $result = $db->query('SELECT `id`, `code`, `name` FROM `Countries` WHERE `status` = ?',1)->fetchAll();
                                    foreach ($result as $row) { ?>
                                        <option value="<?php echo $row["id"]; ?>"><?php echo $row["name"]; ?></option>
                                    <?php } //foreach ?>
                                </select>
                            </div>
                        </div> <!-- flex -->

                        <div class="d-flex flex-column">
                            <div class="form-group">
                                <label for="revProduct">Product : </label>
                                <select class="form-control" id="revProduct">
                                    <?php
                                    $result = $db->query('SELECT * FROM `Products` WHERE `pTypeShop` = ? AND `pStatus` = ?','Massage',1)->fetchAll();
                                    foreach ($result as $row) { ?>
                                        <option value="<?php echo $row["pID"]; ?>"><?php echo $row["pName"]; ?></option>
                                    <?php } //foreach ?>
                                </select>
                            </div>
                        </div> <!-- flex -->

                        <div class="d-flex" style="gap: 10px">
                            <div class="form-group w-25">
                                <label for="revMonth">Month : </label>
                                <select class="form-control" id="revMonth">
                                    <?php foreach ($date["monthName"] as $key => $value){ ?>
                                        <option value="<?php echo $key; ?>" <?php echo ($key==$date["thisMonth"] ? "selected" : ""); ?> ><?php echo $key.' : '.$value; ?></option>
                                    <?php } //foreach ?>
                                </select>
                            </div>
                            <div class="form-group w-25">
                                <label for="revYear">Year : </label>
                                <select class="form-control" id="revYear">
                                    <?php foreach ($date["yearNumber"] as $key => $value){ ?>
                                        <option value="<?php echo $value; ?>" <?php echo ($value==$date["thisYear"]) ? 'selected' : ''; ?>><?php echo $value; ?></option>
                                    <?php } //foreach ?>
                                </select>
                            </div>
                        </div> <!-- flex -->

                        <div class="d-flex flex-column">
                            <div class="form-group">
                                <label for="revValue">Value : </label>
                                <input id="revValue" class="form-control" type="number" min="0" step="any" value="0" placeholder="2000">
                            </div>
                        </div> <!-- flex -->

                    </div> <!-- modal-body -->

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button onclick="formSave();" type="button" class="btn btn-primary" name="cmdSubmit" id="cmdSubmit">Save changes</button>
                    </div>
                </div>
            </div>
        </div><!-- modal -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content -->

<script>
    const barCTX = document.getElementById('barChart');
    const dougCTX = document.getElementById('doughnutChart');
    const lineCTX = document.getElementById('lineChart');

    let dataObj = {};
    dataObj.label = ['Booking System', 'Social Bundle', 'Direct Bundle', 'Mega Bundle', 'Social Solo', 'Direct Solo', 'Mega Solo', 'Autopilot'];
    //dataObj.data = [70259.43, 26904.08, 67873.41, 44598.00, 24103.36, 41318.00, 82934.00, 195.38];
    dataObj.data = [0, 0, 0, 0, 0, 0, 0, 0];
    //dataObj.data = Array.from({length: 8}, () => Math.floor(Math.random() * 80));

    let barChart = new Chart(barCTX, {
        type: 'bar',
        data: {
            labels: dataObj.label,
            datasets: [{
                label: '# of Revenue',
                data: dataObj.data,
                borderWidth: 1,
            }]
        },
        options: {
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    }); // CTX

    let dougChart = new Chart(dougCTX, {
        type: 'doughnut',
        data: {
            labels: dataObj.label,
            datasets: [{
                data: dataObj.data
            }]
        },
        options: {
            aspectRatio: '2'
        }
    }); // CTX

    let lineChart = new Chart(lineCTX, {
        type: 'line',
        data: {
            labels: dataObj.label,
            datasets: [{
                label: 'Revenue',
                data: dataObj.data,
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    }); // CTX


    const resetForm = () => {
        console.log('reset form');
        let thisYear =  new Date().getFullYear();
        let month = (new Date().getMonth() + 1).toString().padStart(2, "0");

        const revCountry = $("#revCountry");
        const revProduct = $("#revProduct");
        const revMonth = $("#revMonth");
        const revYear = $("#revYear");
        const revValue = $("#revValue");
        revCountry.val(1);
        revProduct.val(1);
        revValue.val(0);
        revMonth.val(month);
        revYear.val(thisYear);
        reloadTable();
        barChart.update();
        dougChart.update();
        lineChart.update();
    }// const

    const formSave = () => {
        console.log('form save');
        const revCountry = $("#revCountry");
        const revProduct = $("#revProduct");
        const revMonth = $("#revMonth");
        const revYear = $("#revYear");
        const revValue = $("#revValue");

        let Payload = {
            "action": 'add',
            "revCountry": revCountry.val(),
            "revProduct": revProduct.val(),
            "revMonth": revMonth.val(),
            "revYear": revYear.val(),
            "revValue": revValue.val()
        };

        const reqSave = $.ajax({
            url: 'assets/php/actionRevenueRestaurant.php',
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: Payload
        });

        reqSave.done(function(res) {
            console.log(res);
            $("#formModal").modal('hide');
            return res.message;
        });

        reqSave.fail(function(xhr, status, error) {
            console.log("ajax request Save fail!!");
            console.log(status + ': ' + error);
        });

    }// const
</script>