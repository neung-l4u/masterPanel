<?php $random = rand(); ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template submission : Project</title>
</head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-LGKDYHL23T');
</script>
<link rel="stylesheet" href="../assets/css/project.css?v=1.1.0&r=<?php echo $random; ?>">
<link rel="stylesheet" href="../assets/css/datatables-bs5.min.css">


<div class="row">
    <div class="col">
        <div class="d-flex justify-content-between">
            <h4><i class="bi bi-cast"></i> Project</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm">
                <i class="bi bi-plus-circle"></i> Add new
            </button>
        </div>
    </div>
</div>

<div class="row pt-3">
    <div class="col border rounded py-3">
        <table id="projectData" class="table table-striped table-hover">
            <thead class="table-dark thead-dark">
                <tr>
                    <th class="col_id"><i class="bi bi-record2" title="ID"></i></th>
                    <th class="col_owner"><i class="bi bi-person-circle" title="Project Owner"></i></th>
                    <th class="col_type"><i class="bi bi-images" title="Template"></i></th>
                    <th class="col_name">Project Name</th>
                    <th class="col_page">Page</th>
                    <th class="col_status">Stage</th>
                    <th class="col_action"><i class="bi bi-gear-wide-connected" title="Action"></i></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="modalFormLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalFormLabel"><i class="bi bi-cast"></i> Project</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex flex-column">
                <div class="form-group">
                    <label for="projectName">Project name <span style="color:red;"> *</span></label>
                    <input type="text" class="form-control" id="projectName" placeholder="HoonHay" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="shopType">Shop type<span style="color:red;"> *</span></label>
                    <select class="form-control" id="shopType" onchange="updateTemplates()">
                        <option value="0" disabled>--- Select ---</option>
                    </select>
                </div>

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
                <div class="form-group">
                    <label for="country">Country</label>
                    <select class="form-control" id="country">
                        <option value="0" disabled>--- Select ---</option>
                    </select>
                </div>
                <div class="form-group text-right">
                    <input type="hidden" id="loginID" value="<?php echo $_SESSION['id']; ?>">
                    <input type="hidden" id="editID" value="">
                    <input type="hidden" id="frmAction" value="add">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveForm();"><i class="bi bi-floppy"></i> Save</button>
                </div>
            </div><!--modal-body-->
        </div>
    </div>
</div>

<script src="../controllers/project.js?v=1.0.0&r=<?php echo $random; ?>"></script>
<script src="../assets/js/datatables-bs5.min.js"></script>