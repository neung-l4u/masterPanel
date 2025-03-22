<?php
global $db, $date;
$userLevel = $_SESSION['level'];
$teamID = $_SESSION['teamID'];
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <svg id="Layer_1" class="nav-icon mr-3" height="1em" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 83.35 83.36"><path d="M41.68,0h0s0,0,0,0C18.66,0,0,18.66,0,41.68s18.66,41.68,41.68,41.68,41.68-18.66,41.68-41.68S64.69,0,41.68,0h0ZM21.96,9.44c-.79,1.49-1.51,3.06-2.17,4.71-1-.31-1.96-.64-2.9-1,1.58-1.38,3.27-2.62,5.07-3.71h0ZM61.39,9.44c1.79,1.09,3.49,2.34,5.07,3.71h0c-.94.35-1.9.69-2.9,1-.67-1.65-1.39-3.23-2.18-4.71h0ZM43.62,3.94h0c3.89.2,7.63.98,11.13,2.27h0c1.9,2.49,3.62,5.52,5.07,9h0c-4.89,1.21-10.34,1.96-16.19,2.09V3.94ZM39.72,3.94v13.36s.01,0,.01,0c-5.85-.14-11.29-.88-16.19-2.1h0c1.45-3.48,3.16-6.5,5.06-8.99h0c3.5-1.3,7.22-2.08,11.11-2.28h0ZM13.86,16.11c1.48.61,3.02,1.16,4.61,1.67h0c-2.18,6.59-3.46,14.12-3.64,21.96H3.94c.46-9.11,4.14-17.34,9.91-23.62h0ZM22.18,18.84h0s0,0,0,0c5.46,1.39,11.41,2.21,17.56,2.36h0v18.54h-21.02c.18-7.8,1.45-14.88,3.46-20.9h0ZM61.17,18.84h0s0,0,0,0c2.02,6.02,3.28,13.1,3.46,20.9h-21.02v-18.53h0c6.14-.15,12.09-.97,17.56-2.36h0ZM69.49,16.11h0c5.78,6.28,9.46,14.53,9.92,23.62h0s-10.89,0-10.89,0c-.18-7.84-1.47-15.36-3.64-21.95h0c1.59-.51,3.13-1.07,4.61-1.67h0ZM39.73,43.63v18.54h0c-6.14.14-12.09.96-17.55,2.36h0c-2.02-6.02-3.28-13.09-3.46-20.89h0s21.02,0,21.02,0ZM64.63,43.62h0s0,0,0,0h0c-.18,7.79-1.45,14.87-3.46,20.89h0c-5.46-1.39-11.41-2.21-17.55-2.36h0v-18.53h21.01ZM14.83,43.63h0c.18,7.84,1.46,15.36,3.64,21.95h0c-1.59.51-3.13,1.07-4.61,1.67-5.78-6.29-9.45-14.53-9.91-23.62h10.89ZM79.41,43.62h0s0,0,0,0h0c-.46,9.09-4.14,17.34-9.92,23.62h0c-1.48-.61-3.02-1.16-4.61-1.67h0c2.17-6.59,3.46-14.11,3.64-21.95h0,10.89ZM19.79,69.2c.67,1.65,1.39,3.23,2.18,4.72h0c-1.8-1.1-3.5-2.35-5.08-3.72h0c.94-.36,1.9-.69,2.89-1h0ZM63.55,69.2h0c1,.31,1.96.65,2.9,1h0c-1.59,1.38-3.29,2.62-5.08,3.72h0c.79-1.5,1.51-3.07,2.18-4.73h0ZM43.62,66.05h0c5.85.13,11.3.88,16.2,2.09h0c-1.45,3.48-3.17,6.51-5.07,9h0c-3.49,1.29-7.23,2.07-11.12,2.27v-13.37ZM39.72,66.05h0s0,0,0,0v13.36c-3.89-.2-7.62-.98-11.12-2.27h0c-1.9-2.49-3.62-5.52-5.07-8.99h0c4.9-1.22,10.34-1.97,16.19-2.11h0Z" fill="#000000"/></svg>
                    Website Example
                </h4>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item active"><a href="main.php">Website Example</a></li>
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
                        <?php if($userLevel<=2){ ?>
                        <button id="btnModal" type="button" class="btn btn-primary" data-toggle="modal" data-target="#formModal">
                            <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z" fill="#FFFFFF" /></svg> Add new
                        </button>
                        <?php } ?>

                        <!-- Modal -->


                    </div>
                    <div class="card-body">
                        <div class="card">
                            <div class="card-body table-responsive p-4" style="height: 620px;">
                                <table id="datatable" class="table table-borderless table-striped table-hover" style="width:100%">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th style="width:5%">#</th>
                                        <th style="width:10%">Template</th>
                                        <th style="width:20%">Link Example Website</th>
                                        <th style="width:10%"></th>
                                    </tr>
                                    </thead>
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
                        <h5 class="modal-title" id="formModalLabel">Add Template</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div class="d-flex flex-column">

                            <div class="row mb-3">
                                <div class="col">
                                    <div class="form-group row">
                                        <label for="templateWebsite" class="col-2 col-form-label">Template</label>
                                        <div class="col">
                                            <select id="templateWebsite" class="custom-select">
                                                <?php
                                                $temp = $db->query('SELECT `id`, `template` FROM `WebsiteTemplate` ORDER BY `id`;')->fetchAll();
                                                foreach ($temp as $row){
                                                    ?>
                                                    <option value="<?php echo $row['id']; ?>"><?php echo $row['template']; ?></option>
                                                <?php }//foreach ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="form-group row">
                                        <label for="linkwebsite" class="col-2 col-form-label">Link Website</label>
                                        <div class="col">
                                            <input type="text" class="form-control" id="linkwebsite" placeholder="Eg. https://www.example.com">
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
</div>
<!-- /.content -->

<script>

    const setEdit = (id) => {
        const templateWebsite = $("#templateWebsite");
        const linkwebsite = $("#linkwebsite");
        const editID = $("#editID");
        const formAction = $("#formAction");

        const reqAjax = $.ajax({
            url: "assets/php/actionWebsiteTemplate.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: {
                act: "loadUpdate",
                id: id,
                editID : editID.val(),

            },
        });

        reqAjax.done(function (res) {
            console.log(res);
            templateWebsite.val(res.templateWebsite);
            linkwebsite.val(res.linkwebsite);
            editID.val(res.id);
            formAction.val("edit");
            modalFormAction("open");
        });

        reqAjax.fail(function (xhr, status, error) {
            console.log("ajax request fail!!");
            console.log(status + ": " + error);
        });
    }// const

    const formSave = () => {
        const templateWebsite = $("#templateWebsite");
        const linkwebsite = $("#linkwebsite");
        const editID = $("#editID");
        const formAction = $("#formAction");

        const reqAjax = $.ajax({
            url: "assets/php/actionWebsiteTemplate.php",
            method: "POST",
            async: false,
            cache: false,
            dataType: "json",
            data: {
                act: "save",
                templateWebsite : templateWebsite.val(),
                linkwebsite : linkwebsite.val(),
                editID : editID.val(),
                formAction : formAction.val()
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
    }// const


    const setDel = (id) => {
        const confirmDelete = confirm("Are you sure you want to delete this entry? This action cannot be undone.");
        if (!confirmDelete) {
            return;
        }

        const reqAjax = $.ajax({
            url: "assets/php/actionWebsiteTemplate.php",
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
    };// const

    const resetForm = () => {
        const  templateWebsite = $("#templateWebsite");
        const linkwebsite = $("#linkwebsite");
        const editID = $("#editID");
        const formAction = $("#formAction");


        templateWebsite.val('1');
        linkwebsite.val('');
        editID.val('');
        formAction.val('add');
    }// const

</script>