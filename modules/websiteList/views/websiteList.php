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
    <title>L4U - CS : Website List</title>
    <style>
        .colNo{
            width: 50px;
        }
        /* .colProName{

        } */
        .colLocation{
            width: 150px;
        }
        .colOwner{
            width: 150px;
        }
        .colEmail{
            width: 200px;
        }
        .colDetail{
            width: 80px;
        }
        .colInfo{
            width: 180px;
        }
        .modal-body {
            font-size: 0.9rem;
        }
        h5.text-info{
            font-size: 0.8rem;
        }
        small{
            font-size: 0.7rem;
        }
    </style>
</head>
<body>

<?php include "nav.php";?>

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
                        <li class="breadcrumb-item">Home</li>
                        <li class="breadcrumb-item active"><a href="websiteList.php">Website Lists</a></li>
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
             <div class="row mt-4 mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4>Filters</h4>
                            <div class="row mb-3">
                                <div class="col-4 mb-2">
                                    <label for="filterShopType" class="form-label">Shop Type</label>
                                    <select class="form-select" id="filterShopType" onchange="filterChange()" aria-label="Default select example">
                                        <option value="" selected>All</option>
                                        <option value="1">Restaurant</option>
                                        <option value="2">Massage</option>
                                        <option value="3">Grocery</option>
                                        <option value="4">Internal</option>
                                        <option value="5">Template</option>
                                        <option value="6">Other</option>
                                    </select>
                                </div>
                                <div class="col-4 mb-2">
                                    <label for="filterSystem" class="form-label">System</label>
                                    <select class="form-select" id="filterSystem" onchange="filterChange()"  aria-label="Default select example">
                                        <option value="" selected>All</option>
                                        <option value="GF">Gloria Food</option>
                                        <option value="AM">Amelia</option>
                                        <option value="VC">Voucher</option>
                                    </select>
                                </div>
                                <div class="col-4 mb-2">
                                    <label for="filterStatus" class="form-label">Status</label>
                                    <select class="form-select" id="filterStatus" onchange="filterChange()" aria-label="Default select example">
                                        <option value="" selected>All</option>
                                        <option value="Live">Live</option>
                                        <option value="Draft">Draft</option>
                                        <option value="Transferred">Transferred</option>
                                        <option value="Pre Live">Pre Live</option>
                                        <option value="Subdomain">Subdomain</option>
                                        <option value="Redirect">Redirect</option>
                                        <option value="Unpublished">Unpublished</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /.col-md-12 -->
            </div><!-- /.row -->

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="datatable" class="table table-borderless table-striped table-hover" style="width:100%">
                                <thead class="thead-dark">
                                <tr>
                                    <th class="colNo">#</th>
                                    <th class="colProName">Project name</th>
                                    <th class="colLocation">Location</th>
                                    <th class="colOwner">Owner</th>
                                    <th class="colEmail">Owner Email</th>
                                    <th class="colDetail"></th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div><!-- /.col-md-12 -->
            </div><!-- /.row -->

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
    </main>

        <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Detail</h1>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-column">
                        <h5 class="text-info">Basic Information</h5>
                        <div class="row mb-1">
                            <div class="col">
                                <table class="table border">
                                    <tbody>
                                        <tr>
                                            <th scope="row" class="colInfo">Project</th>
                                            <td id="wProject"></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="colInfo">Location</th>
                                            <td><span id="wLocation"></span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <table class="table border">
                                    <tbody>
                                        <tr>
                                            <th scope="row" class="colInfo">Owner</th>
                                            <td><span id="wOwner"></span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="colInfo">Email</th>
                                            <td><span id="wOwnerEmail"></span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="colInfo">Industry</th>
                                            <td><span class="mr-3" id="wIndustry"></span> <b>Template :</b> <span id="wTemplateUsed"></span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="colInfo">System</th>
                                            <td><span id="wSystem"></span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <h5 class="text-info">Domain Information</h5>
                        <div class="row mb-1">
                            <div class="col">
                                <table class="table border">
                                    <tbody>
                                        <tr>
                                            <th scope="row" class="colInfo">Domain Name</th>
                                            <td><span id="wDomain"></span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="colInfo">Domain Provider</th>
                                            <td><span id="wDomainProviderID"></span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <table class="table border">
                                    <tbody>
                                        <tr>
                                            <th scope="row" class="colInfo">Publish Date</th>
                                            <td><span id="wPublishDate"></span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="colInfo">Live Status</th>
                                            <td><span id="wLiveStatus"></span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <h5 class="text-info">Log-in Information</h5>
                        <div class="row mb-3">
                            <div class="col">
                                <table class="table border">
                                    <tbody>
                                        <tr>
                                            <th scope="row" class="colInfo">WP Log-in</th>
                                            <td><span id="wWordpressURL"></span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="colInfo">WP User</th>
                                            <td><span id="wWordpressUser"></span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="colInfo">WP Pass</th>
                                            <td><span id="wWordpressPass"></span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <h5 class="text-info">SMTP Information</h5>
                        <div class="row mb-1">
                            <div class="col">
                                <table class="table border">
                                    <tbody>
                                        <tr>
                                            <th scope="row" class="colInfo">User</th>
                                            <td><span id="wSMTPEmailUser"></span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="colInfo">Pass</th>
                                            <td><span id="wSMTPEmailPass"></span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="colInfo">Remark</th>
                                            <td><span id="wSMTPRemark"></span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <table class="table border">
                                    <tbody>
                                        <tr>
                                            <th scope="row" class="colInfo">Contact User</th>
                                            <td><span id="wContactEmailUser"></span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="colInfo">Contact Pass</th>
                                            <td><span id="wContactEmailPass"></span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="colInfo">Contact Remark</th>
                                            <td><span id="wContactEmailRemark"></span></td>
                                        </tr>
                                    </tbody>
                                </table>
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