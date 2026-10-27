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
    <link href="../assets/libs/DataTables/datatables.min.css" rel="stylesheet">
    <link href="../assets/css/websiteList.css" rel="stylesheet">
    <title>L4U - CS : Website List</title>
    <style>
        body{
            background-color: #f4f6fa;
        }
        .pageWrap{
            max-width: 1440px;
        }
        .pageTitle{
            font-weight: 600;
        }
        .supportChip{
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 0.85rem;
        }
        .mainCard{
            border: 0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(16, 24, 40, .08);
        }
        .filterBar{
            background: #f8f9fb;
            border-bottom: 1px solid #edf0f4;
            border-radius: 12px 12px 0 0;
        }
        .filterBar .form-label{
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6c757d;
            margin-bottom: 4px;
        }
        #datatable thead th{
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6c757d;
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            white-space: nowrap;
        }
        #datatable td{
            vertical-align: middle;
            padding-top: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f3f5;
        }
        /* Country, Shop Type, Status ไม่ต้องตัดบรรทัด */
        #datatable td:nth-child(4), #datatable td:nth-child(5), #datatable td:nth-child(6){
            white-space: nowrap;
        }
        #datatable tbody tr:hover > *{
            background-color: #f5f9ff;
        }
        .colNo{
            width: 50px;
        }
        .colCountry{
            width: 130px;
        }
        .colShopType{
            width: 110px;
        }
        .colStatus{
            width: 120px;
        }
        .colUrl{
            width: 230px;
        }
        .colDetail{
            width: 90px;
        }
        /* ข้อความยาวเกินช่องให้ตัดเป็น ... (ดูค่าเต็มได้จาก title ตอนชี้เมาส์) */
        .siteLink, .ownerEmail{
            display: inline-block;
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
            text-decoration: none;
        }
        .siteLink:hover{
            text-decoration: underline;
        }
        .ownerEmail{
            color: #6c757d;
            font-size: 0.875rem;
        }
        .linkDetail{
            text-decoration: none;
            font-weight: 600;
            color: #212529;
        }
        .linkDetail:hover{
            color: #0d6efd;
        }
        .statusBadge{
            font-weight: 600;
            font-size: 0.72rem;
            padding: 4px 10px;
            border-radius: 999px;
        }
        .statusBadge::before{
            content: "";
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            margin-right: 6px;
            vertical-align: middle;
        }
        .btnIcon{
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }
        div.dt-container .dt-search input{
            min-width: 260px;
            border-radius: 8px;
        }
        .colInfo{
            width: 180px;
            color: #6c757d;
            font-weight: 500;
        }
        .modal-body {
            font-size: 0.9rem;
        }
        h5.text-info{
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #0d6efd !important;
        }
        small{
            font-size: 0.7rem;
        }
        /* คลิกที่ค่าเพื่อ copy (ดู .copyable ใน controllers/websiteList.js) */
        .copyable{
            cursor: pointer;
            border-bottom: 1px dashed #adb5bd;
        }
        .copyable:hover{
            background-color: #e7f1ff;
        }
        #alert{
            position: fixed;
            left: 50%;
            bottom: 30px;
            transform: translateX(-50%);
            background-color: #212529;
            color: #fff;
            padding: 10px 18px;
            border-radius: 999px;
            font-size: 0.875rem;
            z-index: 2000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
        }
    </style>
</head>
<body>

<?php include "nav.php";?>

<div class="container-fluid pageWrap py-4 px-3 px-md-4">

    <!-- Content Header (Page header) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="pageTitle m-0 d-flex align-items-center gap-2">
                <svg class="nav-icon mr-3" height="1em" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M152.1 38.2c9.9 8.9 10.7 24 1.8 33.9l-72 80c-4.4 4.9-10.6 7.8-17.2 7.9s-12.9-2.4-17.6-7L7 113C-2.3 103.6-2.3 88.4 7 79s24.6-9.4 33.9 0l22.1 22.1 55.1-61.2c8.9-9.9 24-10.7 33.9-1.8zm0 160c9.9 8.9 10.7 24 1.8 33.9l-72 80c-4.4 4.9-10.6 7.8-17.2 7.9s-12.9-2.4-17.6-7L7 273c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l22.1 22.1 55.1-61.2c8.9-9.9 24-10.7 33.9-1.8zM224 96c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zm0 160c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zM160 416c0-17.7 14.3-32 32-32l288 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-288 0c-17.7 0-32-14.3-32-32zM48 368a48 48 0 1 1 0 96 48 48 0 1 1 0-96z"  fill="#000000" /></svg>
                Website Lists
            </h4>
            <div class="text-muted small mt-1" style="font-size:.85rem">คลิกชื่อร้านเพื่อดูรหัสเข้าเว็บ · คลิกรหัสใน popup เพื่อ copy</div>
        </div>
        <div class="supportChip d-flex align-items-center gap-2">
            <span class="text-muted">Admin Support</span>
            <span class="fw-semibold">admin@localforyou.com</span>
            <a href="#" onclick="copyText('admin@localforyou.com'); return false;" title="Copy"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-copy" viewBox="0 0 16 16">  <path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/></svg></a>
            <span class="text-muted ms-2">Password</span>
            <span class="fw-semibold font-monospace" id="headerAdminPass" data-pass="L4U=New@min">••••••••••</span>
            <a href="#" id="btnShowHeaderPass" title="ดูรหัส"></a>
            <a href="#" onclick="copyText($('#headerAdminPass').data('pass')); return false;" title="Copy"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-copy" viewBox="0 0 16 16">  <path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/></svg></a>
        </div>
    </div>
    <!-- /.content-header -->

    <main>
        <div class="card mainCard">
            <div class="filterBar px-3 px-md-4 pt-3 pb-2">
                <div class="row g-3 align-items-end">
                    <div class="col-md col-6 mb-2">
                        <label for="filterShopType" class="form-label">Shop Type</label>
                        <select class="form-select form-select-sm" id="filterShopType" onchange="filterChange()" aria-label="Default select example">
                                        <option value="" >All</option>
                                        <option value="1">Restaurant</option>
                                        <option value="2" selected>Massage</option>
                                        <option value="3">Grocery</option>
                                        <option value="4">Internal</option>
                                        <option value="5">Template</option>
                                        <option value="6">Other</option>
                                    </select>
                    </div>
                    <div class="col-md col-6 mb-2">
                        <label for="filterSystem" class="form-label">System</label>
                        <select class="form-select form-select-sm" id="filterSystem" onchange="filterChange()"  aria-label="Default select example">
                                        <option value="" selected>All</option>
                                        <option value="GF">Gloria Food</option>
                                        <option value="AM">Amelia</option>
                                        <option value="VC">Voucher</option>
                                        <option value="CW">Cloudwaitress</option>
                                    </select>
                    </div>
                    <div class="col-md col-6 mb-2">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-select form-select-sm" id="filterStatus" onchange="filterChange()" aria-label="Default select example">
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
                    <div class="col-md col-6 mb-2">
                        <label for="filterCountry" class="form-label">Country</label>
                        <select class="form-select form-select-sm" id="filterCountry" onchange="filterChange()">
                                        <option value="" selected>All</option>
                                        <?php foreach ($db->query('SELECT id, name FROM Countries ORDER BY id;')->fetchAll() as $row) { ?>
                                            <option value="<?php echo intval($row['id']); ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                                        <?php } ?>
                                    </select>
                    </div>
                    <div class="col-md-auto col-12 mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-nowrap px-3" onclick="resetFilters()">ล้างตัวกรอง</button>
                    </div>
                </div>
            </div>
            <div class="card-body px-3 px-md-4">
                <table id="datatable" class="table table-borderless align-middle mb-0" style="width:100%">
                    <thead>
                    <tr>
                        <th class="colNo">#</th>
                        <th class="colProName">Project name</th>
                        <th class="colUrl">Domain</th>
                        <th class="colCountry">Country</th>
                        <th class="colShopType">Shop Type</th>
                        <th class="colStatus">Status</th>
                        <th class="colEmail">Owner Email</th>
                        <th class="colDetail"></th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div id="alert">Copied ✓</div>
    </main>

        <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Detail</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-column">
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
                                            <th scope="row" class="colInfo">Admin Support User</th>
                                            <td><span class="copyable">admin@localforyou.com</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="colInfo">Admin Password</th>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="font-monospace" id="wAdminSupportPass"></span>
                                                    <button type="button" class="btn btn-light btnIcon" id="btnShowAdminPass" title="ดูรหัส"></button>
                                                    <button type="button" class="btn btn-light btnIcon" id="btnCopyAdminPass" title="คัดลอก"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/></svg></button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

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

                        <h5 class="text-info">SMTP Information</h5>
                        <div class="row mb-1">
                            <div class="col">
                                <table class="table border">
                                    <tbody>
                                        <tr>
                                            <th scope="row" class="colInfo">User</th>
                                            <td><span class="copyable" id="wSMTPEmailUser"></span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="colInfo">Pass</th>
                                            <td><span class="copyable" id="wSMTPEmailPass"></span></td>
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
                                            <td><span class="copyable" id="wContactEmailUser"></span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="colInfo">Contact Pass</th>
                                            <td><span class="copyable" id="wContactEmailPass"></span></td>
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