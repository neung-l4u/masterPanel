<?php
global $db, $date;
$loginID = $_SESSION['id'];
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">
                    <svg class="nav-icon mr-2" height="1.5em" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z" /></svg>
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
                                    <tfoot class="thead-light">
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
        <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formModalLabel">Form Staff</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
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

                            <div class="form-group mb-3">
                                <label for="inputName">Full Name</label>
                                <input type="text" class="form-control" id="inputName" maxlength="255" placeholder="Eg. Sorasak Thanomsap">
                            </div>

                            <div class="form-group mb-5">
                                <label for="inputNickName">Nick Name</label>
                                <input type="text" class="form-control" id="inputNickName" maxlength="50" placeholder="Enter Staff Nick Name">
                            </div>

                            <div class="form-group">
                                <label for="inputEmail">Email</label>
                                <input type="email" class="form-control" id="inputEmail" placeholder="Enter Staff Email">
                                <small id="emailHelp" class="form-text text-muted">e.g. mail@localforyou.com.</small>
                            </div>

                            <div class="form-group">
                                <label for="inputPhone">Phone</label>
                                <input type="tel" class="form-control" id="inputPhone" placeholder="Enter Staff Phone">
                                <small id="phoneHelp" class="form-text text-muted">e.g. 0891234567</small>
                            </div>

                            <div class="form-group mb-3">
                                <label for="inputPassword">Password <small id="passwordNotAllow" class="text-danger" style="display: none;">Not allow to edit encrypted data.</small></label>
                                <input type="text" class="form-control" id="inputPassword" placeholder="Enter Staff Password" value="Localeats#2023">
                                <small id="passwordHelp" class="form-text text-muted">Default password is Localeats#2023.</small>
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

</script>