<?php
global $db, $date;
$loginID = $_SESSION['id'];
?>
<style>
    abbr[title] {
        border-bottom: none !important;
        cursor: help !important;
        text-decoration: none !important;
    }
</style>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">
                    <svg class="nav-icon mr-2" height="1.5em" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z" />
                    </svg>
                    L4U Password
                </h3>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active"><a href="main.php">L4U Password</a></li>
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
                                <table id="datatable" class="table table-borderless table-striped table-hover"
                                    style="width:100%">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th style="width:10%">Team:Type</th>
                                            <th style="width:5%">Level</th>
                                            <th style="width:25%">Name</th>
                                            <th style="width:20%">User</th>
                                            <th style="width:20%">Password</th>
                                            <th style="width:5%">Link</th>
                                            <th style="width:5%">Note</th>
                                            <th style="width:10%"></th>
                                        </tr>
                                    </thead>
                                    <tfoot class="thead-light">
                                        <tr>
                                            <th>Team:Type</th>
                                            <th>Level</th>
                                            <th>Name</th>
                                            <th>User</th>
                                            <th>Password</th>
                                            <th>Link</th>
                                            <th>Note</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div> <!-- /.card-body -->
                        </div>  <!-- /.card -->
                    </div> <!-- /.card-body -->
                </div> <!-- /.card -->
            </div><!-- /.col-md-12 -->
        </div><!-- /.row -->

        <div id="alert" style="
            display: float; 
            right: 20px; 
            bottom: 30px; 
            position: fixed; 
            background-color: #007bff; 
            color: white; 
            padding: 15px; 
            border-radius: 5px;
            z-index: 1;
            box-shadow: 0 4px 4px 0 rgb(191 191 191 / 20%);
            ">
            Text Copied
        </div>

        <!-- Modal -->
        <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formModalLabel">Form Password</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div> <!-- modal-header -->

                    <div class="modal-body">
                        <div class="d-flex flex-column">

                            <div class="row mb-5">
                                <div class="col-4">
                                    <div class="form-group row">
                                        <label for="inputType" class="col-2 col-form-label">Type</label>
                                        <div class="col">
                                            <select id="inputType" class="custom-select" required>
                                                <option value="1">Internal</option>
                                                <option value="2">Client</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group row">
                                        <label for="inputTeam" class="col-2 col-form-label">Team</label>
                                        <div class="col">
                                            <select id="inputTeam" class="custom-select" required>
                                                <option value="0" selected>-- None --</option>
                                                <?php
                                                $teams = $db->query('SELECT `id`, `name`, `fullName` FROM `Team` ORDER BY `idx`;')->fetchAll();
                                                foreach ($teams as $row){
                                                    ?>
                                                <option value="<?php echo $row['id']; ?>">
                                                    <?php echo $row['name'].' : '.$row['fullName']; ?></option>
                                                <?php }//foreach ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group row">
                                        <label for="inputLevel" class="col-2 col-form-label">Level</label>
                                        <div class="col">
                                            <select id="inputLevel" class="custom-select" required>
                                                <option value="1">Super Admin</option>
                                                <option value="2">Admin</option>
                                                <option value="3">Manager</option>
                                                <option value="4" selected>User</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="inputpwName">Password Name</label>
                                <input type="text" class="form-control" id="inputpwName" maxlength="255"
                                    placeholder="e.g. Demoeat Log-in" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="inputAccessLink">Access Link</label>
                                <input type="text" class="form-control" id="inputAccessLink" maxlength="255"
                                    placeholder="e.g. https://localforyou.com" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="inputUserName">Username</label>
                                <input type="text" class="form-control" id="inputUserName" maxlength="50"
                                    placeholder="Enter Username" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="inputPassword">Password</label>
                                <input type="text" class="form-control" id="inputPassword"
                                    placeholder="Enter Password" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="inputNote">Note</label>
                                <textarea class="form-control" id="inputNote" rows="3" placeholder="Optional"></textarea>
                            </div>

                            <input type="hidden" name="editID" id="editID" value="">
                            <input type="hidden" name="formAction" id="formAction" value="add">
                        </div> <!-- flex -->
                    </div> <!-- modal-body -->

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button onclick="formSave();" type="button" class="btn btn-primary" name="cmdSubmit"
                            id="cmdSubmit">Save changes</button>
                    </div> <!-- modal-footer -->
                </div> <!-- modal-content -->
            </div> <!-- modal-dialog -->
        </div> <!-- modal -->

    </div><!-- /.container-fluid -->
</div>
<!-- /.content -->

<script>
     const setEdit = (id) => {
        const inputLevel = $("#inputLevel");
        const inputTeam = $("#inputTeam");
        const inputType = $("#inputType");
        const inputpwName = $("#inputpwName");
        const inputAccessLink = $("#inputAccessLink");
        const inputUserName = $("#inputUserName");
        const inputPassword = $("#inputPassword");
        const inputNote = $("#inputNote");
        const editID = $("#editID");
        const formAction = $("#formAction");

        const reqAjax = $.ajax({
            url: "assets/php/actionPassword.php",
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
            inputType.val(res.inputType);
            inputTeam.val(res.inputTeam);
            inputLevel.val(res.inputLevel);
            inputpwName.val(res.inputpwName);
            inputAccessLink.val(res.inputAccessLink);
            inputUserName.val(res.inputUserName);
            inputPassword.val(res.inputPassword);
            inputNote.val(res.inputNote);
            editID.val(res.id);
            formAction.val("edit");
            modalFormAction("open");
        });

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        });
    }// const

    const setDel = (id) => {
        const confirmDelete = confirm("Are you sure you want to delete this entry? This action cannot be undone.");
        if (!confirmDelete) {
            return;
        }

        const reqAjax = $.ajax({
            url: "assets/php/actionPassword.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: {
                act: "setDelete",
                id: id,
            },
        });

        reqAjax.done(function (res) {
            console.log(res);
            if (res.success) {
                reloadTable();
            } else {
                alert("Failed to delete the entry. Please try again.");
            }
        });

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        });
    };
    
    const formSave = () => {
        const inputType = $("#inputType");
        const inputTeam = $("#inputTeam");
        const inputLevel = $("#inputLevel");
        const inputpwName = $("#inputpwName");
        const inputAccessLink = $("#inputAccessLink");
        const inputUserName = $("#inputUserName");
        const inputPassword = $("#inputPassword");
        const inputNote = $("#inputNote");
        const editID = $("#editID");
        const formAction = $("#formAction");

        const reqAjax = $.ajax({
            url: "assets/php/actionPassword.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: {
                act: "save",
                inputType: inputType.val(),
                inputTeam: inputTeam.val(),
                inputLevel: inputLevel.val(),
                inputpwName: inputpwName.val(),
                inputAccessLink: inputAccessLink.val(),
                inputUserName: inputUserName.val(),
                inputPassword: inputPassword.val(),
                inputNote: inputNote.val(),
                editID: editID.val(),
                formAction: formAction.val()
            },
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
    } // const

    const resetForm = () => {
        const inputType = $("#inputType");
        const inputTeam = $("#inputTeam");
        const inputLevel = $("#inputLevel");
        const inputpwName = $("#inputpwName");
        const inputAccessLink = $("#inputAccessLink");
        const inputUserName = $("#inputUserName");
        const inputPassword = $("#inputPassword");
        const inputNote = $("#inputNote");
        const editID = $("#editID");
        const formAction = $("#formAction");

        inputType.val('');
        inputTeam.val('');
        inputLevel.val('');
        inputpwName.val('');
        inputAccessLink.val('');
        inputUserName.val('');
        inputPassword.val('');
        inputNote.val('');
        editID.val('');
        formAction.val('add');
    }// const


    function showCopy() {
        $("#alert").fadeIn(500);
        setTimeout(function () {
            $("#alert").fadeOut();
        }, 1000);
    }

    function copyText(elementId) {
        var copyText = document.getElementById(elementId);
        navigator.clipboard.writeText(copyText.value).then(function () {
            showCopy();
        }).catch(function (error) {
            console.error("Error copying text: ", error);
        });
    }
</script>