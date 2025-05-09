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

$password = "Localeats#".date("Y");
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <svg class="nav-icon mr-3" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM609.3 512H471.4c5.4-9.4 8.6-20.3 8.6-32v-8c0-60.7-27.1-115.2-69.8-151.8c2.4-.1 4.7-.2 7.1-.2h61.4C567.8 320 640 392.2 640 481.3c0 17-13.8 30.7-30.7 30.7zM432 256c-31 0-59-12.6-79.3-32.9C372.4 196.5 384 163.6 384 128c0-26.8-6.6-52.1-18.3-74.3C384.3 40.1 407.2 32 432 32c61.9 0 112 50.1 112 112s-50.1 112-112 112z" fill="#000000" /></svg>
                    Settings / Staffs
                </h4>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="#">Settings</a></li>
                    <li class="breadcrumb-item active">Staffs</li>
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
                        <button id="btnModal" type="button" class="btn btn-primary" data-toggle="modal" data-target="#formModal">
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z" fill="#FFFFFF" /></svg> Add new
                        </button>

                        <!-- Modal -->


                    </div>
                    <div class="card-body">
                        <div class="card">
                            <div class="card-body table-responsive p-4" style="height: 620px;">
                                <table id="datatable" class="table table-borderless table-striped table-hover" style="width:100%">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th style="width:20%">Email</th>
                                        <th style="width:15%">Phone</th>
                                        <th style="width:15%">Level</th>
                                        <th style="width:10%">Status</th>
                                        <th style="width:10%"></th>
                                    </tr>
                                    </thead>
                                    <tfoot class="thead-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Level</th>
                                        <th>Status</th>
                                        <th></th>
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
        <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formModalLabel">Form Staff</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div class="d-flex flex-column">

                            <div class="form-group row">
                                <label class="col-2 col-form-label">Status</label>
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

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group row">
                                        <label for="inputLevel" class="col-2 col-form-label">Level</label>
                                        <div class="col">
                                            <select id="inputLevel" class="custom-select">
                                                <option value="1">Super Admin</option>
                                                <option value="2">Admin</option>
                                                <option value="3">Manager</option>
                                                <option value="4" selected>User</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group row">
                                        <label for="inputTeam" class="col-2 col-form-label">Team</label>
                                        <div class="col">
                                            <select id="inputTeam" class="custom-select">
                                                <option value="0" selected>-- None --</option>
                                                <?php
                                                $teams = $db->query('SELECT `id`, `name`, `fullName` FROM `Team` ORDER BY `idx`;')->fetchAll();
                                                foreach ($teams as $row){
                                                    ?>
                                                    <option value="<?php echo $row['id']; ?>"><?php echo $row['name'].' : '.$row['fullName']; ?></option>
                                                <?php }//foreach ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                             <!-- Level and Team-->


                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="form-group mb-3 row">
                                        <label class="col-3 col-form-label" for="inputStartDate">StartDate</label>
                                        <input type="date" class="form-control col" id="inputStartDate" placeholder="dd-mm-yyyy">
                                    </div>
                                </div>
                                <div class="col-6">
                                <div class="form-group mb-3 row">
                                    <label class="col-3 col-form-label" for="inputEmployeeNumber">Emp No.</label>
                                    <input type="text" class="form-control col" id="inputEmployeeNumber" maxlength="6" placeholder="e.g. LOC061">
                                </div>
                                </div>
                            </div>
                            <!-- StartDate and Emp No.-->


                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputName">Full Name</label>
                                        <input type="text" class="form-control" id="inputName" maxlength="255" placeholder="e.g. Peeraphat Malimongkhon">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputTname">Thai Name</label>
                                        <input type="text" class="form-control" id="inputTname" maxlength="255" placeholder="e.g. พีรภัทร มะลิมงคล">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputNickName">Nick Name</label>
                                        <input type="text" class="form-control" id="inputNickName" maxlength="50" placeholder="Enter Staff Nick Name">
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputBirthday">Birthday</label>
                                        <input type="date" class="form-control" id="inputBirthday" placeholder="dd-mm-yyyy">
                                     </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputAddress">Address</label>
                                        <textarea id="inputAddress" class="form-control" placeholder="Enter Address" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputReligion" class="col-2 col-form-label">Religion</label>    
                                        <select id="inputReligion" class="custom-select">
                                            <option value="1" selected>-- ไม่ระบุ --</option>
                                            <?php
                                            $teams = $db->query('SELECT `rID`, `rThane` AS "thai" FROM `Religion` WHERE rID <> 1 ORDER BY `rThane`;')->fetchAll();
                                            foreach ($teams as $row){
                                                ?>
                                                <option value="<?php echo $row['rID']; ?>"><?php echo $row['thai']; ?></option>
                                            <?php }//foreach ?>
                                        </select>        
                                    </div>
                                </div>
                            </div>

                            

                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputEmail">Email</label>
                                        <input type="email" class="form-control" id="inputEmail" placeholder="Enter Staff Email">
                                        <small id="emailHelp" class="form-text text-muted">e.g. mail@localforyou.com.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputPhone">Phone</label>
                                        <input type="tel" class="form-control" id="inputPhone" placeholder="Enter Staff Phone" maxlength="10">
                                        <small id="phoneHelp" class="form-text text-muted">e.g. 0891234567</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputPassword">Password <small id="passwordNotAllow" class="text-danger" style="display: none;">Not allow to edit encrypted data.</small></label>
                                        <input type="text" class="form-control" id="inputPassword" placeholder="Enter Staff Password" value="<?php echo $password;?>">
                                        <small id="passwordHelp" class="form-text text-muted">Default password is <?php echo $password;?>.</small>
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
</div>
<!-- /.content -->

<script>
    const setStatus = (id, status) => {
        const flagStatus = !status ? 1 : 0;
        const reqAjax = $.ajax({
            url: "assets/php/actionStaffs.php",
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
        const inputName = $("#inputName");
        const inputTname = $("#inputTname");
        const inputNickName = $("#inputNickName");
        const inputStartDate = $("#inputStartDate");
        const inputEmployeeNumber = $("#inputEmployeeNumber");
        const inputAddress = $("#inputAddress");
        const inputBirthday = $("#inputBirthday");
        const inputEmail = $("#inputEmail");
        const inputPhone = $("#inputPhone");
        const inputPassword = $("#inputPassword");
        const passwordNotAllow = $("#passwordNotAllow");
        const inputLevel = $("#inputLevel");
        const inputReligion = $("#inputReligion");
        const inputTeam = $("#inputTeam");
        const statusOn = $("#statusOn");
        const statusOff = $("#statusOff");
        const editID = $("#editID");
        const formAction = $("#formAction");
        const reqAjax = $.ajax({
            url: "assets/php/actionStaffs.php",
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
            inputName.val(res.name);
            inputTname.val(res.tname);
            inputNickName.val(res.nickname);
            inputBirthday.val(res.birthday);
            inputStartDate.val(res.startdate);
            inputEmployeeNumber.val(res.employeenumber);
            inputAddress.val(res.address);
            inputEmail.val(res.email);
            inputPhone.val(res.phone);
            inputPassword.val("Encrypted : " + res.password).attr('disabled', 'disabled');
            passwordNotAllow.show();
            inputLevel.val(res.level);
            inputReligion.val(res.religion)
            inputTeam.val(res.team)
            if(res.status === 1) {
                statusOff.prop('checked', false);
                statusOn.prop('checked', true);
            }else{
                statusOn.prop('checked', false);
                statusOff.prop('checked', true);
            }
            editID.val(res.id);
            formAction.val("edit");
            modalFormAction("open");
        });

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        })
    }// const

    const formSave = () => {
        const inputName = $("#inputName");
        const inputTname = $("#inputTname");
        const inputNickName = $("#inputNickName");
        const inputBirthday = $("#inputBirthday");
        const inputStartDate = $("#inputStartDate");
        const inputEmployeeNumber = $("#inputEmployeeNumber");
        const inputAddress = $("#inputAddress");
        const inputEmail = $("#inputEmail");
        const inputPhone = $("#inputPhone");
        const inputPassword = $("#inputPassword");
        const inputReligion = $("#inputReligion");
        const inputTeam = $("#inputTeam");
        const inputLevel = $("#inputLevel");
        const editID = $("#editID");
        const formAction = $("#formAction");

        let statusValue = $("input[name='inputStatus']:checked").val();

        let payload = {
                act: "save",
                inputName : inputName.val(),
                inputTname : inputTname.val(),
                inputNickName : inputNickName.val(),
                inputBirthday : inputBirthday.val(),
                inputStartDate : inputStartDate.val(),
                inputEmployeeNumber : inputEmployeeNumber.val(),
                inputAddress : inputAddress.val(),
                inputEmail : inputEmail.val(),
                inputPhone : inputPhone.val(),
                inputPassword : inputPassword.val(),
                inputReligion : inputReligion.val(),
                inputTeam : inputTeam.val(),
                inputLevel : inputLevel.val(),
                inputStatus : statusValue,
                editID : editID.val(),
                formAction : formAction.val()
            };

            console.log("payload=",payload);
            
        const reqAjax = $.ajax({
            url: "assets/php/actionStaffs.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: payload
        });
            
        reqAjax.done(function (res) {
            modalFormAction("close");
            console.log(res);
            reloadTable();
            resetForm();
            $("#formModal").modal('hide');
        });

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        });
        
    }// const


    const resetForm = () => {
        const inputName = $("#inputName");
        const inputTname = $("#inputTname");
        const inputNickName = $("#inputNickName");
        const inputBirthday = $("#inputBirthday");
        const inputStartDate = $("#inputStartDate");
        const inputEmployeeNumber = $("#inputEmployeeNumber");
        const inputAddress = $("#inputAddress");
        const inputEmail = $("#inputEmail");
        const inputPhone = $("#inputPhone");
        const inputPassword = $("#inputPassword");
        const inputReligion = $("#inputReligion");
        const inputTeam = $("#inputTeam");
        const inputLevel = $("#inputLevel");
        const statusOn = $("#statusOn");
        const statusOff = $("#statusOff");
        const editID = $("#editID");
        const formAction = $("#formAction");
        const passwordNotAllow = $("#passwordNotAllow");

        const date = new Date();

        let day = date.getDate();
        let month = date.getMonth() + 1;
        let year = date.getFullYear();
        let currentDate = `${year}-${month}-${day}`;

        inputName.val('');
        inputTname.val('');
        inputNickName.val('');
        inputBirthday.val('');
        inputStartDate.val(currentDate);
        inputEmployeeNumber.val('');
        inputAddress.val('');
        inputEmail.val('');
        inputPhone.val('');
        inputPassword.val('Localeats#2025').removeAttr('disabled');
        passwordNotAllow.hide();
        inputLevel.val('4');
        inputReligion.val('1');
        inputTeam.val('0');
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
                url: "assets/php/actionStaffs.php",
                method: "POST",
                async: false,
                cache: false,
                dataType: "json",
                data: payload
            });
                
            reqAjax.done(function (res) {
                modalFormAction("close");
                console.log(res);
                reloadTable();
                resetForm();
                $("#formModal").modal('hide');
            });

            reqAjax.fail(function (xhr, status, error) {
                console.log("ajax request fail!!");
                console.log(status + ": " + error);
            });

        }//if


    }//setDel

</script>