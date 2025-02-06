<link rel="stylesheet" href="../assets/css/project.css">


<div class="row">
    <div class="col">
        <div class="d-flex justify-content-between">
            <h4>Project</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm">
                Add new
            </button>
        </div>
    </div>
</div>

<div class="row pt-3">
    <p>
        <small class="text-secondary">The table below only shows projects you added, not others.</small>
    </p>
    <div class="col border rounded py-3">
        <table id="projectData" class="table table-striped table-hover">
            <thead class="table-dark thead-dark">
                <tr>
                    <th class="col_id">#</th>
                    <th class="col_type">Template</th>
                    <th class="col_name">Project Name</th>
                    <th class="col_page">Page</th>
                    <th class="col_status">Status</th>
<!--                    <th class="col_owner">Owner</th>-->
                    <th class="col_action"></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="modalFormLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalFormLabel">Project</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="projectName">Project name <span style="color:red;"> *</span></label>
                            <input type="text" class="form-control" id="projectName" placeholder="HoonHay" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="shopType">Shop type<span style="color:red;"> *</span></label>
                            <select class="form-control" id="shopType" onchange="updateTemplates()">
                                <option value="0" disabled>--- Select ---</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="selectedTemplate">Template</label>
                            <?php
                            $templates = [
                                1 => "Template no. 1",
                                2 => "Template no. 2",
                                3 => "Template no. 3",
                            ];
                            ?>
                            <select class="form-control" id="selectedTemplate">
                                <?php foreach ($templates as $value => $label){ ?>
                                    <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                                <?php }//foreach ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="country">Country</label>
                            <select class="form-control" id="country">
                                <option value="0" disabled>--- Select ---</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div><!--modal-body-->
            <div class="modal-footer">
                <input type="hidden" id="loginID" value="<?php echo $_SESSION['id']; ?>">
                <input type="hidden" id="editID" value="">
                <input type="hidden" id="frmAction" value="add">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveForm();">Save changes</button>
            </div>
        </div>
    </div>
</div>

<script src="../controllers/project.js"></script>