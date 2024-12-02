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

<!-- ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">
                    <svg class="nav-icon mr-2" height="1.5em" xmlns="http://www.w3.org/2000/svg" data-name="Layer 1" viewBox="0 0 64 80" x="0px" y="0px"><path d="M12.669,52.23c5.03,4.807,11.837,7.77,19.328,7.77,15.105,0,27.447-12.025,27.975-27.004h-27.576L12.669,52.23Z"/><path d="M32.989,4.025V30.996h26.982c-.52-14.646-12.335-26.457-26.982-26.971Z"/><path d="M30.989,4.026C16.016,4.559,3.997,16.898,3.997,32c0,7.231,2.755,13.83,7.271,18.803L30.989,31.574V4.026Z"/></svg>
                    Dashboard
                </h3>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active"><a href="main.php">My Desk</a></li>
                    <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
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
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-thumbs-up"></i></span>
                    <!--<i class="fas fa-money-check-dollar-pen"></i>-->
                    <div class="info-box-content">
                        <span class="info-box-text">Earn</span>
                        <span class="info-box-number">289,478</span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-danger elevation-1">$</span>
                    <!--<i class="fas fa-thumbs-up"></i>-->
                    <div class="info-box-content">
                        <span class="info-box-text">Expenses</span>
                        <span class="info-box-number">41,410</span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix hidden-md-up"></div>

            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-shopping-cart"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">IHD</span>
                        <span class="info-box-number">760</span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">New Customer</span>
                        <span class="info-box-number">2,000</span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Monthly Recap Report</h5>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <div class="btn-group">
                                <button type="button" class="btn btn-tool dropdown-toggle" data-toggle="dropdown">
                                    <i class="fas fa-wrench"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" role="menu">
                                    <a href="#" class="dropdown-item">Action</a>
                                    <a href="#" class="dropdown-item">Another action</a>
                                    <a href="#" class="dropdown-item">Something else here</a>
                                    <a class="dropdown-divider"></a>
                                    <a href="#" class="dropdown-item">Separated link</a>
                                </div>
                            </div>
                            <button type="button" class="btn btn-tool" data-card-widget="remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p class="text-center">
                                    <strong>Sales: 1 Jan, 2014 - 30 Jul, 2014</strong>
                                </p>

                                <div class="chart">
                                    <!-- Sales Chart Canvas -->
                                    <canvas id="salesChart" height="180" style="height: 180px;"></canvas>
                                </div>
                                <!-- /.chart-responsive -->
                            </div>
                            <!-- /.col -->
                            <div class="col-md-4">
                                <p class="text-center">
                                    <strong>Goal Completion</strong>
                                </p>

                                <div class="progress-group">
                                    Pro online Ordering System
                                    <span class="float-right"><b>108</b>/150</span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-primary" style="width: 72%"></div>
                                    </div>
                                </div>
                                <!-- /.progress-group -->

                                <div class="progress-group">
                                    Social + Direct Media Marketing
                                    <span class="float-right"><b>194</b>/160</span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-danger" style="width: 100%"></div>
                                    </div>
                                </div>

                                <!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">Mega Media Marketing</span>
                                    <span class="float-right"><b>8</b>/24</span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-success" style="width: 34%"></div>
                                    </div>
                                </div>

                                <!-- /.progress-group -->
                                <div class="progress-group">
                                    IHD
                                    <span class="float-right"><b>35</b>/500</span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-warning" style="width: 7%"></div>
                                    </div>
                                </div>
                                <!-- /.progress-group -->

                                <div class="progress-group">
                                    Smile + Wawio
                                    <span class="float-right"><b>0</b>/170</span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-secondary" style="width: 0%"></div>
                                    </div>
                                </div>
                                <!-- /.progress-group -->
                            </div>
                            <!-- /.col -->
                        </div>
                        <!-- /.row -->
                    </div>
                    <!-- ./card-body -->
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-sm-3 col-6">
                                <div class="description-block border-right">
                                    <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> 17%</span>
                                    <h5 class="description-header">$35,210.43</h5>
                                    <span class="description-text">TOTAL REVENUE</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-3 col-6">
                                <div class="description-block border-right">
                                    <span class="description-percentage text-warning"><i class="fas fa-caret-left"></i> 0%</span>
                                    <h5 class="description-header">$10,390.90</h5>
                                    <span class="description-text">TOTAL COST</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-3 col-6">
                                <div class="description-block border-right">
                                    <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> 20%</span>
                                    <h5 class="description-header">$24,813.53</h5>
                                    <span class="description-text">TOTAL PROFIT</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-3 col-6">
                                <div class="description-block">
                                    <span class="description-percentage text-danger"><i class="fas fa-caret-down"></i> 35%</span>
                                    <h5 class="description-header">1004</h5>
                                    <span class="description-text">GOAL COMPLETIONS</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                        </div>
                        <!-- /.row -->
                    </div>
                    <!-- /.card-footer -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->

        <div class="row">
            <div class="col-md-6">
                <!-- DIRECT CHAT -->
                <div class="card direct-chat direct-chat-warning">
                    <div class="card-header">
                        <h3 class="card-title">Direct Chat</h3>

                        <div class="card-tools">
                            <span title="3 New Messages" class="badge badge-warning">3</span>
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
                    <div class="card-body">
                        <!-- Conversations are loaded here -->
                        <div class="direct-chat-messages">
                            <!-- Message. Default to the left -->
                            <div class="direct-chat-msg">
                                <div class="direct-chat-infos clearfix">
                                    <span class="direct-chat-name float-left">Aom Boonchutinan</span>
                                    <span class="direct-chat-timestamp float-right">23 Jan 2:00 pm</span>
                                </div>
                                <!-- /.direct-chat-infos -->
                                <img class="direct-chat-img" src="dist/img/user1-128x128.png" alt="message user image">
                                <!-- /.direct-chat-img -->
                                <div class="direct-chat-text">
                                    Gungahlin Thai Remedial Massage - AU
                                </div>
                                <!-- /.direct-chat-text -->
                            </div>
                            <!-- /.direct-chat-msg -->

                            <!-- Message to the right -->
                            <div class="direct-chat-msg right">
                                <div class="direct-chat-infos clearfix">
                                    <span class="direct-chat-name float-right">Peter Pheeraya</span>
                                    <span class="direct-chat-timestamp float-left">23 Jan 2:05 pm</span>
                                </div>
                                <!-- /.direct-chat-infos -->
                                <img class="direct-chat-img" src="dist/img/user2-128x128.png" alt="message user image">
                                <!-- /.direct-chat-img -->
                                <div class="direct-chat-text">
                                    I got your message sis
                                </div>
                                <!-- /.direct-chat-text -->
                            </div>
                            <!-- /.direct-chat-msg -->

                            <!-- Message. Default to the left -->
                            <div class="direct-chat-msg">
                                <div class="direct-chat-infos clearfix">
                                    <span class="direct-chat-name float-left">Aom Boonchutinan</span>
                                    <span class="direct-chat-timestamp float-right">23 Jan 5:37 pm</span>
                                </div>
                                <!-- /.direct-chat-infos -->
                                <img class="direct-chat-img" src="dist/img/user1-128x128.png" alt="message user image">
                                <!-- /.direct-chat-img -->
                                <div class="direct-chat-text">
                                    Call me whenever you can...
                                </div>
                                <!-- /.direct-chat-text -->
                            </div>
                            <!-- /.direct-chat-msg -->

                            <!-- Message to the right -->
                            <div class="direct-chat-msg right">
                                <div class="direct-chat-infos clearfix">
                                    <span class="direct-chat-name float-right">Peter Pheeraya</span>
                                    <span class="direct-chat-timestamp float-left">23 Jan 6:10 pm</span>
                                </div>
                                <!-- /.direct-chat-infos -->
                                <img class="direct-chat-img" src="dist/img/user2-128x128.png" alt="message user image">
                                <!-- /.direct-chat-img -->
                                <div class="direct-chat-text">
                                    I would love to.
                                </div>
                                <!-- /.direct-chat-text -->
                            </div>
                            <!-- /.direct-chat-msg -->

                        </div>
                        <!--/.direct-chat-messages-->

                        <!-- Contacts are loaded here -->
                        <div class="direct-chat-contacts">
                            <ul class="contacts-list">
                                <li>
                                    <a href="#">
                                        <img class="contacts-list-img" src="dist/img/user3-128x128.png" alt="User Avatar">

                                        <div class="contacts-list-info">
                              <span class="contacts-list-name">
                                San La-ongnual
                                <small class="contacts-list-date float-right">28/11/2023</small>
                              </span>
                                            <span class="contacts-list-msg">Please check this...</span>
                                        </div>
                                        <!-- /.contacts-list-info -->
                                    </a>
                                </li>
                                <!-- End Contact Item -->
                                <li>
                                    <a href="#">
                                        <img class="contacts-list-img" src="dist/img/user4-128x128.png" alt="User Avatar">

                                        <div class="contacts-list-info">
                              <span class="contacts-list-name">
                                Bee Kevalee
                                <small class="contacts-list-date float-right">23/11/2023</small>
                              </span>
                                            <span class="contacts-list-msg">I will be waiting for...</span>
                                        </div>
                                        <!-- /.contacts-list-info -->
                                    </a>
                                </li>
                                <!-- End Contact Item -->
                                <li>
                                    <a href="#">
                                        <img class="contacts-list-img" src="dist/img/user5-128x128.png" alt="User Avatar">

                                        <div class="contacts-list-info">
                              <span class="contacts-list-name">
                                Nan Chompoonuch
                                <small class="contacts-list-date float-right">20/10/2023</small>
                              </span>
                                            <span class="contacts-list-msg">I'll call you back at...</span>
                                        </div>
                                        <!-- /.contacts-list-info -->
                                    </a>
                                </li>
                                <!-- End Contact Item -->
                                <li>
                                    <a href="#">
                                        <img class="contacts-list-img" src="dist/img/user6-128x128.png" alt="User Avatar">

                                        <div class="contacts-list-info">
                              <span class="contacts-list-name">
                                Cindy
                                <small class="contacts-list-date float-right">10/10/2023</small>
                              </span>
                                            <span class="contacts-list-msg">Where is your new...</span>
                                        </div>
                                        <!-- /.contacts-list-info -->
                                    </a>
                                </li>
                                <!-- End Contact Item -->
                                <li>
                                    <a href="#">
                                        <img class="contacts-list-img" src="dist/img/user7-128x128.png" alt="User Avatar">

                                        <div class="contacts-list-info">
                              <span class="contacts-list-name">
                                Porsche Panita
                                <small class="contacts-list-date float-right">27/09/2023</small>
                              </span>
                                            <span class="contacts-list-msg">Can I take a look at...</span>
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
                    <div class="card-footer">
                        <form action="#" method="post">
                            <div class="input-group">
                                <input type="text" name="message" placeholder="Type Message ..." class="form-control">
                                <span class="input-group-append">
                          <button type="button" class="btn btn-warning">Send</button>
                        </span>
                            </div>
                        </form>
                    </div>
                    <!-- /.card-footer-->
                </div>
                <!--/.direct-chat -->
            </div>
            <!-- /.col -->

            <div class="col-md-6">
                <!-- USERS LIST -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Latest Members</h3>

                        <div class="card-tools">
                            <span class="badge badge-danger">8 New Members</span>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body p-0">
                        <ul class="users-list clearfix">
                            <li>
                                <img src="dist/img/160x160-user4.png" alt="User Image">
                                <a class="users-list-name" href="#">Bee</a>
                                <span class="users-list-date">Today</span>
                            </li>
                            <li>
                                <img src="dist/img/160x160-user5.png" alt="User Image">
                                <a class="users-list-name" href="#">Nan</a>
                                <span class="users-list-date">Yesterday</span>
                            </li>
                            <li>
                                <img src="dist/img/160x160-user6.png" alt="User Image">
                                <a class="users-list-name" href="#">Cindy</a>
                                <span class="users-list-date">12 Oct</span>
                            </li>
                            <li>
                                <img src="dist/img/160x160-user7.png" alt="User Image">
                                <a class="users-list-name" href="#">Porsche</a>
                                <span class="users-list-date">12 Oct</span>
                            </li>
                            <li>
                                <img src="dist/img/160x160-user8.png" alt="User Image">
                                <a class="users-list-name" href="#">Rhig</a>
                                <span class="users-list-date">13 Sep</span>
                            </li>
                            <li>
                                <img src="dist/img/160x160-user9.png" alt="User Image">
                                <a class="users-list-name" href="#">Honey</a>
                                <span class="users-list-date">14 Aug</span>
                            </li>
                            <li>
                                <img src="dist/img/160x160-user10.png" alt="User Image">
                                <a class="users-list-name" href="#">Pluem</a>
                                <span class="users-list-date">15 Jul</span>
                            </li>
                            <li>
                                <img src="dist/img/160x160-user11.png" alt="User Image">
                                <a class="users-list-name" href="#">Sol</a>
                                <span class="users-list-date">15 Jun</span>
                            </li>
                        </ul>
                        <!-- /.users-list -->
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer text-center">
                        <a href="javascript:">View All Users</a>
                    </div>
                    <!-- /.card-footer -->
                </div>
                <!--/.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->


        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Card title</h5>

                        <p class="card-text">
                            Some quick example text to build on the card title and make up the bulk of the card's
                            content.
                        </p>

                        <a href="#" class="card-link">Card link</a>
                        <a href="#" class="card-link">Another link</a>
                    </div>
                </div>

                <div class="card card-primary card-outline">
                    <div class="card-body">
                        <h5 class="card-title">Card title</h5>

                        <p class="card-text">
                            Some quick example text to build on the card title and make up the bulk of the card's
                            content.
                        </p>
                        <a href="#" class="card-link">Card link</a>
                        <a href="#" class="card-link">Another link</a>
                    </div>
                </div><!-- /.card -->
            </div>
            <!-- /.col-md-6 -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0">Featured</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title">Special title treatment</h6>

                        <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                        <a href="#" class="btn btn-primary">Go somewhere</a>
                    </div>
                </div>

                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h5 class="m-0">Featured</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title">Special title treatment</h6>

                        <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                        <a href="#" class="btn btn-primary">Go somewhere</a>
                    </div>
                </div>
            </div>
            <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->

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