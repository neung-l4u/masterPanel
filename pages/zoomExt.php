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
?>
<style>
    td > abbr{
        font-size:1em;
        line-height:1em;
        /*height:1em;*/
        width: 250px;
        /*border:3px solid #00ACEE;*/
        white-space: nowrap;
        overflow: hidden;
        text-overflow: '...?';
    }
    .modal-body {
        font-size: 0.9rem;
    }
    small{
        font-size: 0.7rem;
    }

    .table .thead-dark th {
        background-color: #212529 !important;
    }
    ::placeholder {
        color: #DDDDDD !important;
        opacity: 1; /* Firefox */
    }

    ::-ms-input-placeholder { /* Edge 12 -18 */
        color: #DDDDDD !important;
    }

    .table-box {
        padding: 10px;
    }

    .table-box h2 {
        background: #444;
        color: #fff;
        margin: 0;
        padding: 12px;
        font-size: 16px;
        text-align: center;
    }

    div.dataTables_wrapper div.dataTables_length select {
        width: 100%;
    }
</style>
<link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/css/bootstrap.css">
<link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="plugins/datatables-bs5/css/datatables-bs5.min.css">

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <svg class="nav-icon mr-3" height="1em" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M152.1 38.2c9.9 8.9 10.7 24 1.8 33.9l-72 80c-4.4 4.9-10.6 7.8-17.2 7.9s-12.9-2.4-17.6-7L7 113C-2.3 103.6-2.3 88.4 7 79s24.6-9.4 33.9 0l22.1 22.1 55.1-61.2c8.9-9.9 24-10.7 33.9-1.8zm0 160c9.9 8.9 10.7 24 1.8 33.9l-72 80c-4.4 4.9-10.6 7.8-17.2 7.9s-12.9-2.4-17.6-7L7 273c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l22.1 22.1 55.1-61.2c8.9-9.9 24-10.7 33.9-1.8zM224 96c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zm0 160c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zM160 416c0-17.7 14.3-32 32-32l288 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-288 0c-17.7 0-32-14.3-32-32zM48 368a48 48 0 1 1 0 96 48 48 0 1 1 0-96z"  fill="#000000" /></svg>
                    Zoom Extension 
                </h4>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php">Home</a></li>
                    <li class="breadcrumb-item active"><a href="#">Zoom Extension</a></li>
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
                <div>
                    <div class="card p-3">
                        <div class="card-header bg-white">
                            <div class="row">
                                <div class="col d-flex align-items-center justify-content-between">
                                    <button id="btnModal" type="button" class="btn btn-primary" data-toggle="modal" data-target="#detailModal">
                                        <i class="bi bi-info-circle"></i> Digit Meaning & Extensions
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="zoomExtTable" class="table table-borderless table-striped table-hover" style="width:100%">
                                <thead class="thead-dark">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="15%">Name</th>
                                    <th >Team</th>
                                    <th width="5%">Ext.</th>
                                    <th >Phone License</th>
                                    <th width="5%"></th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div><!-- /.col-md-12 -->
        </div><!-- /.row -->

        <!-- Modal -->
        <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel">
            <div class="modal-dialog modal-lg d-flex align-items-center">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formModalLabel"><i class="bi bi-file-earmark-plus"></i>Edit Extension</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="newModalFormAction('close')">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="container py-4">
                            <form>
                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="inputZoomExt">Zoom Extension</label>
                                            <input type="text" class="form-control" id="inputZoomExt" placeholder="Enter Zoom Extension" maxlength="10">
                                        </div>
                                    </div>

                                    <div class="col-8">
                                        <div class="form-group">
                                            <label>License</label>
                                            <div class="d-flex flex-row">
                                                <div class="form-check form-check-inline d-flex align-items-center">
                                                    <input type="checkbox" class="form-check-input zoom-license" id="inputZoomlicenseAU" value="AU">
                                                    <label class="form-check-label" for="inputZoomlicenseAU">AU</label>
                                                </div>
                                                <div class="form-check form-check-inline d-flex align-items-center">
                                                    <input type="checkbox" class="form-check-input zoom-license" id="inputZoomlicenseNZ" value="NZ">
                                                    <label class="form-check-label" for="inputZoomlicenseNZ">NZ</label>
                                                </div>
                                                <div class="form-check form-check-inline d-flex align-items-center">
                                                    <input type="checkbox" class="form-check-input zoom-license" id="inputZoomlicenseUK" value="UK">
                                                    <label class="form-check-label" for="inputZoomlicenseUK">UK</label>
                                                </div>
                                                <div class="form-check form-check-inline d-flex align-items-center">
                                                    <input type="checkbox" class="form-check-input zoom-license" id="inputZoomlicenseUS" value="US">
                                                    <label class="form-check-label" for="inputZoomlicenseUS">US</label>
                                                </div>
                                                <div class="form-check form-check-inline d-flex align-items-center">
                                                    <input type="checkbox" class="form-check-input zoom-license" id="inputZoomlicenseCA" value="CA">
                                                    <label class="form-check-label" for="inputZoomlicenseCA">CA</label>
                                                </div>
                                                <div class="form-check form-check-inline d-flex align-items-center">
                                                    <input type="checkbox" class="form-check-input zoom-license" id="inputZoomlicenseInter" value="Inter">
                                                    <label class="form-check-label" for="inputZoomlicenseInter">Inter</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="editID" id="editID" value="">
                                <input type="hidden" name="formAction" id="formAction" value="add">
                            </form>
                        </div>

                    </div> <!-- modal-body -->

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="newModalFormAction('close')">Close</button>
                        <button onclick="formSave();" type="button" class="btn btn-primary" name="cmdSubmit" id="cmdSubmit">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal -->

        <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle mr-2"></i>
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Digit Meaning & Extensions</h1>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col">
                                <!-- Table 1 -->
                                <div class="table-box">
                                    <h2>Digit Meaning</h2>
                                    <table class="table table-bordered">
                                        <tr><td>1XX</td><td>CS Members</td></tr>
                                        <tr><td>2XX</td><td>SL Members</td></tr>
                                        <tr><td>3XX</td><td>AM Members</td></tr>
                                        <tr><td>9XX</td><td>Other Members</td></tr>
                                        <tr><td>8XX</td><td>Group Call</td></tr>
                                        <tr><td>7XX</td><td>IVR</td></tr>
                                    </table>
                                </div>

                                <!-- Table 2 -->
                                <div class="table-box">
                                    <h2>IVR Extensions</h2>
                                    <table class="table table-bordered">
                                        <caption><small class="text-danger">7 + 0 + Language (0=Eng, 1=TH)</small></caption>
                                        <tr><td>700</td><td>English IVR</td></tr>
                                        <tr><td>701</td><td>Thai IVR</td></tr>
                                    </table>
                                </div>
                            </div>

                            <div class="col">
                                <!-- Table 3 -->
                                <div class="table-box">
                                    <h2>Call Group Extensions</h2>
                                    <table class="table table-bordered">
                                        <!-- <caption><small class="text-danger">8 + Team + Language (0=Eng, 1=TH)</small></caption> -->
                                        <tr><td>830</td><td>Acc Team (EN)</td></tr>
                                        <tr><td>916</td><td>Acc Team (EN) + Manager</td></tr>
                                        <tr><td>831</td><td>Acc Team (TH)</td></tr>
                                        <tr><td>917</td><td>Acc Team (TH) + Manager</td></tr>
                                        <tr><td>801</td><td>CS Team (All)</td></tr>
                                        <tr><td>810</td><td>CS Team (EN)</td></tr>
                                        <tr><td>914</td><td>CS Team (EN) + Manager</td></tr>
                                        <tr><td>811</td><td>CS Team (TH)</td></tr>
                                        <tr><td>915</td><td>CS Team (TH) + Manager</td></tr>
                                        <tr><td>802</td><td>Direct Sales</td></tr>
                                        <tr><td>820</td><td>Sales (EN)</td></tr>
                                        <tr><td>918</td><td>Sales (EN) + Manager</td></tr>
                                        <tr><td>803</td><td>Sales (Main Line)</td></tr>
                                        <tr><td>821</td><td>Sales (TH)</td></tr>
                                        <tr><td>919</td><td>Sales (TH) + Manager</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.js"></script>
<script src="plugins/datatables-bs5/js/datatables-bs5.min.js"></script>

<script>
    const editID = $("#editID");
    const formAction = $("#formAction");
    const newModalForm = new bootstrap.Modal(document.getElementById("formModal"), {});
    function openFormModal() {newModalForm.show();}

    const setEdit = (id) => {
        const reqAjax = $.ajax({
            url: "assets/php/actionZoomExt.php",
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
            let zoomData = (typeof res.zoomExt === "string") ? JSON.parse(res.zoomExt) : res.zoomExt;
            $("#inputZoomExt").val(zoomData.ext);
            if(Array.isArray(zoomData.license)) {
                zoomData.license.forEach(function(lic) {
                    $(".zoom-license[value='" + lic + "']").prop('checked', true);
                });
            }
            editID.val(res.id);
            formAction.val("edit");
            newModalFormAction("open");
        });

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax loadUpdate fail!!");
            console.log(status + ": " + error);
        })
    }// setEdit

    const formSave = () => {
        const inputZoomExt = $("#inputZoomExt").val();
        const licenses = [];
        $(".zoom-license:checked").each(function() {
            licenses.push($(this).val());
        });
        let payload = {
                act: "save",
                zoomExt: {
                    ext: inputZoomExt,
                    license: licenses
                },
                editID: editID.val(),
                formAction: formAction.val(),
            };

            console.log("payload=",payload);
            
        const reqAjax = $.ajax({
            url: "assets/php/actionZoomExt.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: payload
        });
            
        reqAjax.done(function (res) {
            console.log(res);
            resetForm();
            newModalFormAction("close");
        });

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax save fail!!");
            console.log(status + ": " + error);
        });
    }//formSave

    const resetForm = () => {
        $("#inputZoomExt").val('');
        $(".zoom-license").prop('checked', false);
        editID.val('');
        formAction.val('add');
        reloadTable_bs5();
    }//resetForm

    function reloadTable_bs5() {
        $('#zoomExtTable').DataTable().ajax.reload();
    }

    function newModalFormAction(action) {
        console.log("goNew = " + action);
        if (action === "open") {
            newModalForm.show();
        } else {
            newModalForm.hide();
            $(".modal-backdrop").hide();
        }
    }

    $(() => {
        $('#zoomExtTable').DataTable(
        {
            pagingType: 'full_numbers',
            pageLength: 11,
            lengthMenu: [
                [11, 25, 50, -1],
                ['Fit', 25, 50, 'All']
            ],
            ajax: {
                url: 'pages/tableRendering/dataZoomExt.php',
                type: 'POST',
                dataSrc: 'data',
                data: function (d) {
                }
            },
            columnDefs: [
                {
                    targets: -1,
                    className: 'dt-body-right'
                },
                {
                    targets: [5],
                    orderable: false
                }
            ],
        });
    });

</script>