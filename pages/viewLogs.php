<?php
global $db, $date;

$password = "Localeats#".date("Y");
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">
                    <svg class="nav-icon mr-3" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM609.3 512H471.4c5.4-9.4 8.6-20.3 8.6-32v-8c0-60.7-27.1-115.2-69.8-151.8c2.4-.1 4.7-.2 7.1-.2h61.4C567.8 320 640 392.2 640 481.3c0 17-13.8 30.7-30.7 30.7zM432 256c-31 0-59-12.6-79.3-32.9C372.4 196.5 384 163.6 384 128c0-26.8-6.6-52.1-18.3-74.3C384.3 40.1 407.2 32 432 32c61.9 0 112 50.1 112 112s-50.1 112-112 112z" fill="#000000" /></svg>
                    Logs
                </h3>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active">Logs</li>
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

                    </div>
                    <div class="card-body">
                        <div class="card">
                            <div class="card-body table-responsive p-4" style="height: 620px;">
                                <table id="datatable" class="table table-borderless table-striped table-hover" style="width:100%">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th style="width:15%">Date</th>
                                        <th style="width:10%">Country</th>
                                        <th style="width:70%">Data</th>
                                        <th style="width:10%">Status</th>
                                    </tr>
                                    </thead>
                                    <tfoot class="thead-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Country</th>
                                        <th>Data</th>
                                        <th>Status</th>
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
                        
                    </div>
                    <div class="modal-body">

                    </div>
                    <div class="modal-footer">
                    
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
        inputPassword.val('Localeats#2024').removeAttr('disabled');
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