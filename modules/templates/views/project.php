
<link rel="stylesheet" href="../assets/css/bootstrap5.3.3.min.css">
<link rel="stylesheet" href="../assets/css/project.css">


<div class="row">
    <div class="col">
        <div class="d-flex justify-content-between">
            <h3>Project</h3>
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm">Add new</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <table id="projectData" class="table table-striped table-hover mt-5">
            <thead class="thead-dark">
                <tr>
                    <th class="col_id">#</th>
                    <th class="col_type">Type</th>
                    <th class="col_template">Template</th>
                    <th class="col_name">Name</th>
                    <th class="col_status">Status</th>
                    <th class="col_owner">Owner</th>
                    <th class="col_country">Country</th>
                    <th class="col_action"></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>


<!-- Modal -->
<div class="modal" id="modalForm" tabindex="-1" aria-labelledby="modalFormlLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalFormLabel">Project</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#" id="frmProject">
                <div class="modal-body">

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="projectName">Project name</label>
                                <input type="text" class="form-control" id="projectName" placeholder="HoonHay" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="shopType">Shop type</label>
                                <select class="form-control" id="shopType">
                                    <option value="0">--- Select ---</option>
                                    <option value="1">Restaurant</option>
                                    <option value="2">Massage</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="selectedTemplate">Template</label>
                                <select class="form-control" id="selectedTemplate">
                                    <option value="1">Template no. 1</option>
                                    <option value="2">Template no. 2</option>
                                    <option value="3">Template no. 3</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="country">Country</label>
                                <select class="form-control" id="country">
                                    <option value="0">--- Select ---</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="modal-footer">
                <input type="hidden" id="editID" value="">
                <input type="hidden" id="frmAction" value="add">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveForm();">Save changes</button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="loginID" value="<?php echo $_SESSION['id']; ?>">
<script src="../assets/js/jquery-3.7.1.min.js"></script>
<script src="../controllers/project.js"></script>
<script src="../assets/js/bootstrap.bundle.5.3.3.min.js"></script>
