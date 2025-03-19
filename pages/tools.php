<?php
global $db, $date;

?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">
                    <svg class="nav-icon mr-3" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM609.3 512H471.4c5.4-9.4 8.6-20.3 8.6-32v-8c0-60.7-27.1-115.2-69.8-151.8c2.4-.1 4.7-.2 7.1-.2h61.4C567.8 320 640 392.2 640 481.3c0 17-13.8 30.7-30.7 30.7zM432 256c-31 0-59-12.6-79.3-32.9C372.4 196.5 384 163.6 384 128c0-26.8-6.6-52.1-18.3-74.3C384.3 40.1 407.2 32 432 32c61.9 0 112 50.1 112 112s-50.1 112-112 112z" fill="#000000" /></svg>
                    Tools
                </h3>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php">My Desk</a></li>
                    <li class="breadcrumb-item active">Tools</li>
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
                    <div class="card-header d-flex justify-content-end">
                        <!-- Button trigger modal -->
                        <button id="btnModal" type="button" class="btn btn-primary" data-toggle="modal" data-target="#formServicesModal">
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z" fill="#FFFFFF" /></svg> Add new
                        </button>
                        <!-- Modal -->
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="card-body">
                                <h4 class="card-header"><b>Check Logs</b></h4>
                                <table class="table table-bordered table-striped table-hover" id="datatable" style="width:100%">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th scope="col" style="width: 5%;">#</th>
                                        <th scope="col">Services</th>
                                        <th scope="col" style="width: 68%;">Description</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    $result = $db->query('SELECT `id`, `name`, `link`, `detail`, `status`, `type`, `createBy`, `createAt`, `updateBy`, `updateAt`, `deleteBy`, `deleteAt`  FROM `tools` WHERE `status` = 1 AND `type` = "logs"')->fetchAll();

                                    $data = array("data"=> array());
                                    if (count($result)>0){
                                        $num = 1;
                                        foreach ($result as $row) {
                                            ?>
                                            <tr>
                                                <td><?php echo $num++; ?></td>
                                                <td><a href="<?php echo $row["link"];?>"><?php echo $row["name"];?></a></td>
                                                <td><?php echo $row["detail"];?> </td>
                                            </tr>
                                        <?php  }//end foreach
                                    }else{ ?>

                                        <tr>
                                            <td class="text-center" colspan="3">ไม่มี</td>
                                        </tr>

                                    <?php }//end else ?>
                                    </tbody>
                                </table>
                            </div>
                        </div><!--End Table Check Logs-->
                        <div class="col">
                            <div class="card-body">
                                <h4 class="card-header"><b>Action</b></h4>
                                <table id="datatable" class="table table-bordered table-striped table-hover " style="width:100%">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th scope="col" style="width: 5%;">#</th>
                                        <th scope="col">Services</th>
                                        <th scope="col" style="width: 68%;">Description</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    $result = $db->query('SELECT `id`, `name`, `link`, `detail`, `status`, `type`, `createBy`, `createAt`, `updateBy`, `updateAt`, `deleteBy`, `deleteAt`  FROM `tools` WHERE `status` = 1 AND `type` = "action"')->fetchAll();

                                    $data = array("data"=> array());
                                    if (count($result)>0){
                                        $numb = 1;
                                        foreach ($result as $row) {
                                            ?>
                                            <tr>
                                                <td><?php echo $numb++;?></td>
                                                <td><a href="<?php echo $row["link"];?>"><?php echo $row["name"];?></a></td>
                                                <td><?php echo $row["detail"];?> </td>
                                            </tr>
                                        <?php  }//end foreach
                                    }else{ ?>

                                        <tr>
                                            <td class="text-center" colspan="2">-- Null --</td>
                                        </tr>

                                    <?php }//end else ?>
                                    </tbody>
                                </table>
                            </div>
                        </div><!--End Table Action-->
                    </div><!-- /.row 2 table-->

                </div><!-- /.card-->
            </div><!-- /.col-->
        </div><!-- /.row -->


        <div class="modal fade" id="formServicesModal" tabindex="-1" aria-labelledby="formModalLabel">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formModalLabel">Add Services</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div class="d-flex flex-column">
                            <div class="row mb-2">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label for="inputType" class="form-label">Type</label>
                                        <select id="inputType" class="custom-select">
                                            <option value="" selected>-- Please Selete --</option>
                                            <option value="logs">Logs</option>
                                            <option value="action">Action</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="form-group">
                                        <label for="inputServices" class="form-label">Services</label>
                                        <input type="text" class="form-control col" id="inputServices" placeholder="e.g. System Check Logs">
                                    </div>
                                </div>

                            </div>
                            <!-- Services and Type-->



                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputDescription">Description</label>
                                        <textarea class="form-control" id="inputDescription" row="3"></textarea>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputLink">Link</label>
                                        <input type="text" class="form-control" id="inputLink">
                                        <small id="emailHelp" class="form-text text-muted">e.g. main.php?p=xxx or https://report.localforyou.com/modules/formemail </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                            <div class="form-group">
                                <label class="col-form-label">Status</label>
                                <div class="col">
                                    <div class="form-group d-flex">
                                        <div class="custom-control custom-radio mr-5">
                                            <input class="custom-control-input" type="radio" id="statusOn" name="inputStatus" value="1" checked>
                                            <label for="statusOn" class="custom-control-label">On</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            <input class="custom-control-input" type="radio" id="statusOff" name="inputStatus" value="0">
                                            <label for="statusOff" class="custom-control-label">Off</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>

                            <input type="hidden" name="editID" id="editID" value="">
                            <input type="hidden" name="formAction" id="formAction" value="add">
                        </div> <!-- flex -->
                    </div> <!-- modal-body -->

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button onclick="formSave();" type="button" class="btn btn-primary" name="cmdSubmit" id="cmdSubmit">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</div> <!-- /.contact-->
<script src="plugins/jquery/jquery.min.js"></script>
<script>

    const setStatus = (id, status) => {
        const flagStatus = !status ? 1 : 0;
        const reqAjax = $.ajax({
            url: "assets/php/actionTools.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: {
                act: "setStatus",
                id: id,
                status: flagStatus
            },
        });

        reqAjax.done(function (res) {
            reloadTable();
        });

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        });
    }// const


    const setEdit = (id) => {
        const inputType = $("#inputType");
        const inputServices = $("#inputServices");
        const inputDescription = $("#inputDescription");
        const inputLink = $("#inputLink");
        const statusOn = $("#statusOn");
        const statusOff = $("#statusOff");
        const editID = $("#editID");
        const formAction = $("#formAction");
        const reqAjax = $.ajax({
            url: "assets/php/actionTools.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: {
                act: "loadUpdate",
                id: id,
            },
        });


        reqAjax.done(function (res) {
            console.log(res);
            inputType.val(res.type);
            inputServices.val(res.services);
            inputDescription.val(res.description);
            inputLink.val(res.link);
            if(res.status === 1) {
                statusOff.prop('checked', false);
                statusOn.prop('checked', true);
            }else{
                statusOn.prop('checked', false);
                statusOff.prop('checked', true);
            }
            editID.val(res.id);
            formAction.val("edit");

        });

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        })
    }// const

    const formSave = () => {
        const inputType = $("#inputType");
        const inputServices = $("#inputServices");
        const inputDescription = $("#inputDescription");
        const inputLink = $("#inputLink");
        const editID = $("#editID");
        const formAction = $("#formAction");
        let statusValue = $("input[name='inputStatus']:checked").val();

        let payload = {
            act: "save",
            inputType : inputType.val(),
            inputServices : inputServices.val(),
            inputDescription : inputDescription.val(),
            inputLink : inputLink.val(),
            inputStatus : statusValue,
            editID : editID.val(),
            formAction : formAction.val()
        };

        console.log("payload=",payload);

        const reqAjax = $.ajax({
            url: "assets/php/actionTools.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: payload
        });

        reqAjax.done(function (res) {
            console.log(res);
            reloadTable();
            resetForm();
            $("#formServicesModal").modal('hide');
        });

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        });

    }// const

    function reloadTable() {
        location.reload();
    }

    const resetForm = () => {
        const inputType = $("#inputType");
        const inputServices = $("#inputServices");
        const inputDescription = $("#inputDescription");
        const inputLink = $("#inputLink");
        const statusOn = $("#statusOn");
        const statusOff = $("#statusOff");
        const editID = $("#editID");
        const formAction = $("#formAction");


        inputType.val('');
        inputServices.val('');
        inputDescription.val('');
        inputLink.val('');
        statusOn.prop('checked', true);
        statusOff.prop('checked', false);
        editID.val('');
        formAction.val('add');
    }// const

    const setDel = (delID) => {
        //alert ("Delete"+delID);

        let answer = confirm ("Are you sure to delete this Staff?");

        console.log (answer);
        if (answer === true){
            let payload = {
                act: "setDelete",
                id : delID
            };

            console.log("payload=",payload);

            const reqAjax = $.ajax({
                url: "assets/php/actionTools.php",
                method: "POST",
                async: false,
                cache: false,
                dataType: "json",
                data: payload
            });

            reqAjax.done(function (res) {

                console.log(res);
                reloadTable();
                resetForm();
                $("#formServicesModal").modal('hide');
            });

            reqAjax.fail(function (xhr, status, error) {
                console.log("ajax request fail!!");
                console.log(status + ": " + error);
            });

        }//if


    }//setDel


</script>
