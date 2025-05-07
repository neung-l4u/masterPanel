<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-LGKDYHL23T');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/libs/datatables/datatables.min.css" rel="stylesheet">
    <link href="../assets/css/websiteList.css" rel="stylesheet">
    <title>L4U - Website List</title>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Logo..</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Features</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Pricing</a>
                </li>
            </ul>
            <span class="navbar-text">
                logout
            </span>
        </div>
    </div>
</nav>

<div class="container py-4">

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 class="m-0">
                        <svg class="nav-icon mr-3" height="1em" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M152.1 38.2c9.9 8.9 10.7 24 1.8 33.9l-72 80c-4.4 4.9-10.6 7.8-17.2 7.9s-12.9-2.4-17.6-7L7 113C-2.3 103.6-2.3 88.4 7 79s24.6-9.4 33.9 0l22.1 22.1 55.1-61.2c8.9-9.9 24-10.7 33.9-1.8zm0 160c9.9 8.9 10.7 24 1.8 33.9l-72 80c-4.4 4.9-10.6 7.8-17.2 7.9s-12.9-2.4-17.6-7L7 273c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l22.1 22.1 55.1-61.2c8.9-9.9 24-10.7 33.9-1.8zM224 96c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zm0 160c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zM160 416c0-17.7 14.3-32 32-32l288 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-288 0c-17.7 0-32-14.3-32-32zM48 368a48 48 0 1 1 0 96 48 48 0 1 1 0-96z"  fill="#000000" /></svg>
                        Website Lists
                    </h4>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="d-flex justify-content-end breadcrumb">
                        <li class="breadcrumb-item"><a href="main.php">Home</a></li>
                        <li class="breadcrumb-item active"><a href="#">Website Lists</a></li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
            <div class="d-flex justify-content-end mb-2 pr-2">
                <span class="me-2">User: admin@localforyou.com</span>
                <a href="#" onclick="copyText('admin@localforyou.com')"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-copy" viewBox="0 0 16 16">  <path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/></svg></a>
                <span class="ms-4 me-2">Pass: L4U=New@min</span>
                <a href="#" onclick="copyText('L4U=New@min')"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-copy" viewBox="0 0 16 16">  <path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/></svg></a>
            </div>
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <main>
        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-end">
                                <!-- Button trigger modal -->
                                <!-- <button id="btnModal" type="button" class="btn btn-primary" data-toggle="modal" data-target="#formModal">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z" fill="#FFFFFF" /></svg> Add new
                                </button> -->

                                <!-- Modal -->
                            </div>
                            <div class="card-body">
                                <div class="card">
                                    <div class="card-body table-responsive p-4" style="height: 620px;">
                                        <table id="datatable" class="table table-borderless table-striped table-hover" style="width:100%">
                                            <thead class="thead-dark">
                                            <tr>
                                                <th style="width:50px;">No.</th>
                                                <th>Project name</th>
                                                <th style="width:250px;">Location</th>
                                                <th style="width:150px">Owner</th>
                                                <th style="width:300px">Owner Email</th>
                                                <th style="width:100px">WP-Admin</th>
                                                <th style="width:20px">Detail</th>
                                            </tr>
                                            </thead>
                                            <tfoot class="thead-dark">
                                            <tr>
                                                <th>No.</th>
                                                <th>Project name</th>
                                                <th>Location</th>
                                                <th>Owner</th>
                                                <th>Owner Email</th>
                                                <th>WP-Admin</th>
                                                <th>Detail</th>
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
            </div><!-- /.container-fluid -->
            
            <div id="alert" style="
                display: block;
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
        </div>
        <!-- /.content -->
    </main>

    <footer>
        copyright
    </footer>


    <!-- Modal -->
    <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formModalLabel">Form Website</h5>
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

    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Website Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-column">

                        <h5 class="text-bold">Basic Information</h5>
                        <div class="row mb-3">
                            <div class="col-6">
                                <p><b>Project Name :</b> <span id="wProject"></span></p>
                                <p><b>Location :</b> <span id="wLocation"></span></p>
                            </div>
                            <div class="col-6">
                                <p><b>Owner :</b> <span id="wOwner"></span></p>
                                <p><b>Owner Email :</b> <span id="wOwnerEmail"></span></p>
                                <p><b>Industry :</b> <span class="mr-3" id="wIndustry"></span> <b>Template :</b> <span id="wTemplateUsed"></span></p>
                                <p><b>System :</b> <span id="wSystem"></span></p>
                            </div>
                        </div>                            

                        <h5 class="text-bold">Domain Information</h5>
                        <div class="row mb-3">
                                <div class="col-6">
                                <p><b>Domain Name :</b> <span id="wDomain"></span></p>
                                <p><b>Domain Provider :</b> <span id="wDomainProviderID"></span></p>
                            </div>
                            <div class="col-6">
                                <p><b>Publish Date :</b> <span id="wPublishDate"></span></p>
                                <p><b>Live Status :</b> <span id="wLiveStatus"></span></p>
                            </div>
                        </div>

                        <h5 class="text-bold">Log-in Information</h5>
                        <div class="row mb-3">
                            <div class="col-6">
                                <p><b>L4U Server :</b> <span id=""></span></p>
                                <p><b>cPanel User :</b> <span id="wCPanelUser"></span></p>
                                <p><b>cPanel Pass :</b> <span id="wCPanelPass"></span></p>
                            </div>
                            <div class="col-6">
                                <p><b>Wordpress Log-in :</b> <span id="wWordpressURL"></span></p>
                                <p><b>Wordpress User :</b> <span id="wWordpressUser"></span></p>
                                <p><b>Wordpress Pass :</b> <span id="wWordpressPass"></span></p>
                            </div>
                        </div>

                        <h5 class="text-bold">SMTP Information</h5>
                        <div class="row mb-3">
                            <div class="col-6">
                                <p><b>SMTP Email User :</b> <span id="wSMTPEmailUser"></span></p>
                                <p><b>SMTP Email Pass :</b> <span id="wSMTPEmailPass"></span></p>
                                <p><b>SMTP Remark :</b> <span id="wSMTPRemark"></span></p>
                            </div>
                            <div class="col-6">
                                <p><b>Contact Email User :</b> <span id="wContactEmailUser"></span></p>
                                <p><b>Contact Email Pass :</b> <span id="wContactEmailPass"></span></p>
                                <p><b>Contact Email Remark :</b> <span id="wContactEmailRemark"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.modal -->
</div><!-- container-->


<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../controllers/websiteList.js"></script>
<script src="../assets/libs/DataTables/datatables.min.js"></script>
</body>
</html>