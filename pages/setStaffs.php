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
<link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/css/bootstrap.css">
<link rel="stylesheet" href="plugins/datatables-bs5/css/datatables-bs5.min.css">

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <i class="bi bi-people-fill"></i>
                    Settings / Staffs
                </h4>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php"><i class="bi bi-house-door-fill"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Settings</a></li>
                    <li class="breadcrumb-item active">Staffs</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content-fluid px-4">
        <div class="row">
            <div class="col px-3">
                <div class="card px-3">
                    <div class="card-header ">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="includeInactive">
                                <label class="form-check-label" for="includeInactive">
                                    Include inactive staffs.
                                </label>
                            </div>
                            <button id="btnModal" type="button" class="btn btn-primary" data-toggle="modal" data-target="#formModal">
                                <i class="bi bi-plus-circle-fill"></i> Add new
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="card">
                            <div class="card-body table-responsive p-4" style="height: 620px;">
                                <table id="datatable" class="table table-borderless table-striped table-hover" style="width:100%">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th style="width:50px;" class="text-center"><i class="bi bi-hash"></i></th>
                                        <th><i class="bi bi-person-fill"></i> name</th>
                                        <th style="width:120px;" class="text-center"><i class="bi bi-star-fill"></i> role</th>
                                        <th style="width:150px;" class="text-center"><i class="bi bi-envelope-fill"></i> mail</th>
                                        <th style="width:120px;" class="text-center"><i class="bi bi-telephone-fill"></i> mob</th>
                                        <th style="width:50px"><i class="bi bi-tools"></i></th>
                                    </tr>
                                    </thead>
                                    <tfoot class="thead-dark">
                                    <tr>
                                        <th class="text-center"><i class="bi bi-hash"></i></th>
                                        <th><i class="bi bi-person-fill"></i> name</th>
                                        <th class="text-center"><i class="bi bi-star-fill"></i> role</th>
                                        <th class="text-center"><i class="bi bi-envelope-fill"></i> mail</th>
                                        <th class="text-center"><i class="bi bi-telephone-fill"></i> mob</th>
                                        <th><i class="bi bi-tools"></i></th>
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

                        <div class="d-flex flex-column px-5">

                            <div class="row">
                                <div class="col">
                                    <div class="form-group row">
                                        <label>Status</label>
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
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputStaffType">Type</label>
                                        <div class="form-group d-flex">
                                            <select id="inputStaffType" class="custom-select">
                                                <option value="fullTime" selected>Full-time</option>
                                                <option value="partTime">Part-time</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputEmployeeNumber">Emp No.</label>
                                        <input type="text" class="form-control" id="inputEmployeeNumber" maxlength="6" placeholder="e.g. LOC061">
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputStartDate">Start</label>
                                        <input type="date" class="form-control col" id="inputStartDate" placeholder="dd-mm-yyyy">
                                    </div>
                                </div>
                            </div>
                            <!-- StartDate and Emp No.-->

                            <div class="row mb-5">
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
                                        <label for="inputName">English Name</label>
                                        <input type="text" class="form-control" id="inputName" maxlength="255" placeholder="e.g. Peeraphat Malimongkhon">
                                    </div>
                                </div>

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
                                        <label for="inputAddress">Address</label>
                                        <textarea id="inputAddress" class="form-control" placeholder="Enter Address" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-5">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputBirthday">Birthday</label>
                                        <input type="date" class="form-control" id="inputBirthday" placeholder="dd-mm-yyyy">
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputReligion">Religion</label>    
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

                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputNationality">Nationality</label>    
                                        <select id="inputNationality" class="custom-select">
                                            <option value="Thai" selected>Thai</option>
                                            <option value="Foreign">Foreign</option>
                                        </select>        
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-5">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputEmail">Email</label>
                                        <input type="email" class="form-control" id="inputEmail" placeholder="Enter Staff Email">
                                        <small id="emailHelp" class="form-text text-muted">e.g. mail@localforyou.com.</small>
                                    </div>
                                </div>

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

                            <div class="row mt-5">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="inputZoomExt">Zoom Extension</label>
                                        <input type="text" class="form-control" id="inputZoomExt" placeholder="Enter Zoom Extension" maxlength="10">
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="form-group">
                                        <label>License</label><br>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" class="form-check-input zoom-license" id="inputZoomlicenseAU" value="AU">
                                            <label class="form-check-label" for="inputZoomlicenseAU">AU</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" class="form-check-input zoom-license" id="inputZoomlicenseNZ" value="NZ">
                                            <label class="form-check-label" for="inputZoomlicenseNZ">NZ</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" class="form-check-input zoom-license" id="inputZoomlicenseUK" value="UK">
                                            <label class="form-check-label" for="inputZoomlicenseUK">UK</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" class="form-check-input zoom-license" id="inputZoomlicenseUS" value="US">
                                            <label class="form-check-label" for="inputZoomlicenseUS">US</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" class="form-check-input zoom-license" id="inputZoomlicenseCA" value="CA">
                                            <label class="form-check-label" for="inputZoomlicenseCA">CA</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" class="form-check-input zoom-license" id="inputZoomlicenseInter" value="Inter">
                                            <label class="form-check-label" for="inputZoomlicenseInter">Inter</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="editID" id="editID" value="">
                            <input type="hidden" name="formAction" id="formAction" value="add">
                        </div> <!-- flex -->
                    </div> <!-- modal-body -->

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="bi bi-x-circle"></i> Close</button>
                        <button onclick="formSave();" type="button" class="btn btn-primary" name="cmdSubmit" id="cmdSubmit"><i class="bi bi-floppy-fill"></i> Save</button>
                    </div>
                </div>
            </div>
        </div>
</div>
<!-- /.content -->

<script src="assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.js"></script>
<script src="plugins/datatables-bs5/js/datatables-bs5.min.js"></script>

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
        const inputStaffType = $("#inputStaffType");
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
        const inputNationality = $("#inputNationality");
        const inputTeam = $("#inputTeam");
        const inputZoomExt = $("#inputZoomExt");
        const zoomLicense = $(".zoom-license");
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
            inputStaffType.val(res.stafftype);
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
            inputNationality.val(res.nationality)
            inputTeam.val(res.team)
            if(res.status === 1) {
                statusOff.prop('checked', false);
                statusOn.prop('checked', true);
            }else{
                statusOn.prop('checked', false);
                statusOff.prop('checked', true);
            }
            let zoomData = (typeof res.zoomExt === "string") ? JSON.parse(res.zoomExt) : res.zoomExt;
            $("#inputZoomExt").val(zoomData.ext);
            if(Array.isArray(zoomData.license)) {
                zoomData.license.forEach(function(lic) {
                    $(".zoom-license[value='" + lic + "']").prop('checked', true);
                });
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
        const includeInactive = $("#includeInactive");
        const inputStaffType = $("#inputStaffType");
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
        const inputNationality = $("#inputNationality");
        const passwordNotAllow = $("#passwordNotAllow");
        const inputTeam = $("#inputTeam");
        const inputLevel = $("#inputLevel");
        
        const inputZoomExt = $("#inputZoomExt").val();
        const licenses = [];
        $(".zoom-license:checked").each(function() {
            licenses.push($(this).val());
        });

        const editID = $("#editID");
        const formAction = $("#formAction");
        let statusValue = $("input[name='inputStatus']:checked").val();

        let payload = {
                act: "save",
                inputStaffType : inputStaffType.val(),
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
                inputNationality : inputNationality.val(),
                inputTeam : inputTeam.val(),
                inputLevel : inputLevel.val(),
                zoomExt: {
                    ext: inputZoomExt,
                    license: licenses
                },
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
        const inputStaffType = $("#inputStaffType");
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
        const inputNationality = $("#inputNationality");
        const inputTeam = $("#inputTeam");
        const inputLevel = $("#inputLevel");
        const inputZoomExt = $("#inputZoomExt");
        const zoomLicense = $(".zoom-license");
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

        inputStaffType.val('fullTime');
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
        inputNationality.val('Thai');
        inputTeam.val('0');
        inputZoomExt.val('');
        zoomLicense.prop('checked', false);
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