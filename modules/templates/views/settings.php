<link rel="stylesheet" href="../assets/css/settings.css">

<div class="row">
    <div class="col">
        <div class="d-flex justify-content-between">
            <h4>Settings</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm">
                Add new
            </button>
        </div>
    </div>
</div>

<div class="row pt-3">
    <p>
        <small class="text-secondary">The system will send notification emails to those who submit the form as usual and will send notification emails to people in the list below,<br></small>
        <small class="text-danger">so if you are a submitter, you do not need to enter your name here. table below only shows projects you added, not others.</small>
    </p>
    <div class="col border rounded py-3">
        <table id="settingsData" class="table table-striped table-hover">
            <thead class="table-dark thead-dark">
            <tr>
                <th class="col_id">#</th>
                <th class="col_email">Email</th>
                <th class="col_channel">Channel</th>
                <th class="col_status">Status</th>
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
                <h1 class="modal-title fs-5" id="modalFormLabel">Add User</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="recipient">Email</label>
                            <input type="text" class="form-control" id="recipient" placeholder="admin@localforyou.com" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="channel">Channel</label>
                            <select class="form-control" id="channel">
                                <option value="0">--- Select ---</option>
                                <option value="1">To</option>
                                <option value="2">CC</option>
                                <option value="3">BCC</option>
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

<script src="../controllers/settings.js">


</script>
