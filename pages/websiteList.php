<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
<script>
window.dataLayer = window.dataLayer || [];

function gtag() {
     dataLayer.push(arguments);
}
gtag('js', new Date());

gtag('config', 'G-LGKDYHL23T');
</script>
<?php
global $db, $date;

// The locale list for the WordPress setup dropdown lives with the setup code
include_once __DIR__ . "/../assets/php/WordPressSetup.php";

$password = "Localeats#".date("Y");
?>
<style>
td>abbr {
     font-size: 1em;
     line-height: 1em;
     /*height:1em;*/
     width: 250px;
     /*border:3px solid #00ACEE;*/
     white-space: nowrap;
     overflow: hidden;
     text-overflow: '...?';
}

.colInfo {
     width: 180px;
}

.modal-body {
     font-size: 0.9rem;
}

h5.text-info {
     font-size: 0.8rem;
}

small {
     font-size: 0.7rem;
}

.filterCol {
     width: 30%;
     max-width: 300px;
}

.filterLabel {
     /*border: 1px solid black;*/
     width: 100px;
}

.filterSelect {
     width: 100% !important;
}

a.linkDetail:link {
     color: black;
     text-decoration: none;
}

a.linkDetail:visited {
     color: black;
}

a.linkDetail:hover {
     color: red;
     text-decoration: underline;
}

a.linkDetail:active {
     color: blue;
}

.table .thead-dark th {
     background-color: #212529 !important;
}

::placeholder {
     color: #DDDDDD !important;
     opacity: 1;
     /* Firefox */
}

::-ms-input-placeholder {
     /* Edge 12 -18 */
     color: #DDDDDD !important;
}

div.dataTables_wrapper div.dataTables_length select {
     width: 100%;
}

/* Row action icons: 34px hit targets instead of bare glyphs */
.rowActions {
     display: inline-flex;
     align-items: center;
     gap: 0;
     white-space: nowrap;
}
.rowActions > a,
.rowActions > span {
     display: inline-flex;
     align-items: center;
     justify-content: center;
     width: 28px;
     height: 30px;
     border-radius: 6px;
     font-size: 1.05rem;
     line-height: 1;
     text-decoration: none;
     transition: background .15s;
}
.rowActions > a:hover,
.rowActions > a:focus-visible {
     background: #eef0f4;
     text-decoration: none;
     outline: 0;
}
.rowActions > span {
     opacity: .45;
     cursor: not-allowed;
}
#websiteListTable td.dt-body-right {
     padding-top: 4px;
     padding-bottom: 4px;
     vertical-align: middle;
}

/* Save progress steps */
.save-progress {
     font-size: 0.8rem;
     text-align: left;
}

.save-step {
     display: flex;
     align-items: center;
     gap: 8px;
     padding: 2px 0;
     color: #adb5bd;
     transition: color .3s ease;
}

.save-step-icon {
     flex: 0 0 14px;
     width: 14px;
     height: 14px;
     border-radius: 50%;
     border: 2px solid currentColor;
     position: relative;
}

.save-step.is-running {
     color: #007bff;
}

.save-step.is-running .save-step-icon {
     border-color: #007bff;
     border-top-color: transparent;
     animation: saveStepSpin .7s linear infinite;
}

.save-step.is-done {
     color: #28a745;
}

.save-step.is-done .save-step-icon {
     background-color: #28a745;
     border-color: #28a745;
}

.save-step.is-done .save-step-icon::after {
     content: '';
     position: absolute;
     left: 3px;
     top: 0;
     width: 3px;
     height: 7px;
     border: solid #fff;
     border-width: 0 2px 2px 0;
     transform: rotate(45deg);
}

.save-step.is-failed {
     color: #dc3545;
}

.save-step.is-failed .save-step-icon {
     background-color: #dc3545;
     border-color: #dc3545;
}

.save-step.is-failed .save-step-icon::after {
     content: '!';
     position: absolute;
     left: 0;
     right: 0;
     top: -3px;
     color: #fff;
     font-size: 10px;
     font-weight: bold;
     line-height: 14px;
     text-align: center;
}

.save-step.is-skipped {
     color: #ffc107;
}

.save-step-detail {
     display: block;
     font-size: 0.7rem;
     color: #6c757d;
}

@keyframes saveStepSpin {
     to {
          transform: rotate(360deg);
     }
}

/* ---- Form Website modal: minimal, sectioned layout ---- */
#formModal {
     --fm-ink: #111827;
     --fm-text: #374151;
     --fm-muted: #8a94a6;
     --fm-line: #e5e8ee;
     --fm-line-soft: #eef0f4;
     --fm-canvas: #f8f9fb;
     --fm-accent: #0d6efd;
     --fm-accent-soft: rgba(13, 110, 253, .12);
     --fm-radius: 8px;
}

#formModal .modal-dialog {
     max-width: 900px;
}

#formModal .modal-content {
     border: 0;
     border-radius: 14px;
     box-shadow: 0 24px 64px rgba(15, 23, 42, .18);
     overflow: hidden;
     color: var(--fm-text);
}

#formModal .modal-header {
     padding: 18px 28px;
     border-bottom: 1px solid var(--fm-line-soft);
     background: #fff;
     align-items: center;
}

#formModal .fm-eyebrow {
     font-size: .66rem;
     font-weight: 600;
     letter-spacing: .14em;
     text-transform: uppercase;
     color: var(--fm-accent);
     margin-bottom: 2px;
}

#formModal .modal-title {
     font-size: 1.1rem;
     font-weight: 600;
     letter-spacing: -.01em;
     color: var(--fm-ink);
     line-height: 1.2;
}

#formModal .close {
     margin: 0;
     padding: 6px 10px;
     font-size: 1.5rem;
     font-weight: 300;
     line-height: 1;
     color: var(--fm-muted);
     opacity: 1;
     text-shadow: none;
     border-radius: 50%;
     transition: background .15s, color .15s;
}

#formModal .close:hover {
     background: var(--fm-line-soft);
     color: var(--fm-ink);
}

#formModal .modal-body {
     padding: 0;
     background: var(--fm-canvas);
     font-size: .86rem;
}

/* Sections: heading column on the left, fields on the right */
#formModal .fm-section {
     display: grid;
     grid-template-columns: 190px minmax(0, 1fr);
     gap: 28px;
     padding: 26px 28px;
     border-bottom: 1px solid var(--fm-line-soft);
}

#formModal .fm-section:last-of-type {
     border-bottom: 0;
}

#formModal .fm-section-num {
     display: block;
     font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
     font-size: .68rem;
     font-weight: 600;
     letter-spacing: .08em;
     color: var(--fm-accent);
     margin-bottom: 6px;
}

#formModal .fm-section-title {
     font-size: .92rem;
     font-weight: 600;
     color: var(--fm-ink);
     margin: 0 0 4px;
}

#formModal .fm-section-desc {
     font-size: .74rem;
     line-height: 1.5;
     color: var(--fm-muted);
     margin: 0;
}

#formModal .fm-grid {
     display: grid;
     grid-template-columns: repeat(2, minmax(0, 1fr));
     gap: 16px 18px;
     align-content: start;
}

#formModal .fm-full {
     grid-column: 1 / -1;
}

#formModal .fm-subhead {
     font-size: .66rem;
     font-weight: 600;
     letter-spacing: .12em;
     text-transform: uppercase;
     color: var(--fm-muted);
     padding-bottom: 6px;
     border-bottom: 1px dashed var(--fm-line);
     margin-bottom: -4px;
}

#formModal .fm-subhead:not(:first-child) {
     margin-top: 8px;
}

/* Fields. Margins instead of flex gap so a jQuery toggle() that sets
       display:block on a field keeps the same spacing. */
#formModal .fm-field {
     min-width: 0;
}

#formModal .fm-field>*+* {
     margin-top: 6px;
}

#formModal .fm-field label {
     display: block;
     font-size: .76rem;
     font-weight: 600;
     color: var(--fm-ink);
     margin: 0;
}

#formModal .fm-field .form-control,
#formModal .fm-field .form-select {
     height: 40px;
     padding: 6px 12px;
     font-size: .9rem;
     font-weight: 500;
     color: var(--fm-ink);
     background-color: #fff;
     border: 1px solid #c9d0da;
     border-radius: var(--fm-radius);
     box-shadow: 0 1px 2px rgba(15, 23, 42, .06);
     transition: border-color .15s, box-shadow .15s;
}

#formModal .fm-field .form-control:hover:not(:focus),
#formModal .fm-field .form-select:hover:not(:focus) {
     border-color: #9aa4b2;
}

#formModal .fm-field textarea.form-control,
#formModal .fm-field span.form-control {
     height: auto;
     line-height: 1.5;
}

#formModal .fm-field .form-control:focus,
#formModal .fm-field .form-select:focus {
     border-color: var(--fm-accent);
     box-shadow: 0 0 0 3px var(--fm-accent-soft), 0 1px 2px rgba(15, 23, 42, .06);
     outline: 0;
}

#formModal .fm-field .form-control::placeholder {
     color: #aab2bf !important;
     font-weight: 400;
}

#formModal .fm-hint {
     display: block;
     font-size: .7rem;
     line-height: 1.45;
     color: var(--fm-muted);
}

/* Input groups: the trailing button is often hidden, so the input keeps
       its full radius unless a visible button follows it */
#formModal .fm-field .input-group>.form-control {
     border-radius: var(--fm-radius);
}

#formModal .fm-field .input-group>.form-control:has(+ .btn:not(.d-none):not([style*="none"])) {
     border-top-right-radius: 0;
     border-bottom-right-radius: 0;
}

#formModal .fm-field .input-group>.btn {
     margin-left: -1px;
     padding: 0 12px;
     font-size: .8rem;
     font-weight: 500;
     color: var(--fm-text);
     background: #fff;
     border: 1px solid #c9d0da;
     border-radius: 0 var(--fm-radius) var(--fm-radius) 0;
     box-shadow: none;
     transition: background .15s, color .15s;
}

#formModal .fm-field .input-group>.btn:hover {
     background: #eef4ff;
     border-color: var(--fm-accent);
     color: var(--fm-accent);
}

/* Systems as toggle chips */
#formModal .fm-chips {
     display: flex;
     flex-wrap: wrap;
     gap: 8px;
}

#formModal .fm-chip {
     position: relative;
}

#formModal .fm-chip input {
     position: absolute;
     opacity: 0;
     width: 0;
     height: 0;
     pointer-events: none;
}

#formModal .fm-chip label {
     display: inline-flex;
     align-items: center;
     gap: 8px;
     margin: 0;
     padding: 7px 14px;
     font-size: .8rem;
     font-weight: 500;
     color: var(--fm-text);
     background: #fff;
     border: 1px solid var(--fm-line);
     border-radius: 999px;
     cursor: pointer;
     user-select: none;
     transition: background .15s, border-color .15s, color .15s;
}

#formModal .fm-chip label::before {
     content: '';
     width: 6px;
     height: 6px;
     border-radius: 50%;
     background: #d1d5db;
     transition: background .15s;
}

#formModal .fm-chip label:hover {
     border-color: var(--fm-accent);
     color: var(--fm-accent);
}

#formModal .fm-chip input:checked+label {
     color: var(--fm-accent);
     background: #eef4ff;
     border-color: var(--fm-accent);
}

#formModal .fm-chip input:checked+label::before {
     background: var(--fm-accent);
}

#formModal .fm-chip input:focus-visible+label {
     box-shadow: 0 0 0 3px var(--fm-accent-soft);
}

/* Footer */
#formModal .modal-footer {
     padding: 16px 28px;
     border-top: 1px solid var(--fm-line-soft);
     background: #fff;
}

#formModal .modal-footer .save-progress {
     padding: 12px 14px;
     background: var(--fm-canvas);
     border: 1px solid var(--fm-line-soft);
     border-radius: 10px;
}

#formModal .fm-actions {
     display: flex;
     align-items: center;
     justify-content: space-between;
     gap: 8px;
     flex-wrap: wrap;
}

#formModal .fm-actions-right {
     display: flex;
     align-items: center;
     gap: 8px;
     flex-wrap: wrap;
     margin-left: auto;
}

#formModal .modal-footer .btn {
     padding: 8px 16px;
     font-size: .84rem;
     font-weight: 500;
     border-radius: var(--fm-radius);
     box-shadow: none;
     transition: background .15s, border-color .15s, color .15s;
}

#formModal .modal-footer .btn-primary {
     color: #fff;
     background: var(--fm-accent);
     border-color: var(--fm-accent);
     box-shadow: 0 1px 2px rgba(13, 110, 253, .25);
}

#formModal .modal-footer .btn-primary:hover:not(:disabled) {
     background: #0b5ed7;
     border-color: #0b5ed7;
}

#formModal .modal-footer .btn-outline-primary {
     color: var(--fm-ink);
     background: #fff;
     border-color: var(--fm-line);
}

#formModal .modal-footer .btn-outline-primary:hover:not(:disabled) {
     background: #eef4ff;
     border-color: var(--fm-accent);
     color: var(--fm-accent);
}

#formModal .modal-footer .btn-secondary {
     color: var(--fm-muted);
     background: transparent;
     border-color: transparent;
}

#formModal .modal-footer .btn-secondary:hover {
     color: var(--fm-ink);
     background: var(--fm-line-soft);
}

@media (max-width: 767.98px) {

     #formModal .modal-header,
     #formModal .modal-footer {
          padding-left: 18px;
          padding-right: 18px;
     }

     #formModal .fm-section {
          grid-template-columns: 1fr;
          gap: 14px;
          padding: 20px 18px;
     }

     #formModal .fm-grid {
          grid-template-columns: 1fr;
     }
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
                         <svg class="nav-icon mr-3" height="1em" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                              viewBox="0 0 512 512">
                              <path d="M152.1 38.2c9.9 8.9 10.7 24 1.8 33.9l-72 80c-4.4 4.9-10.6 7.8-17.2 7.9s-12.9-2.4-17.6-7L7 113C-2.3 103.6-2.3 88.4 7 79s24.6-9.4 33.9 0l22.1 22.1 55.1-61.2c8.9-9.9 24-10.7 33.9-1.8zm0 160c9.9 8.9 10.7 24 1.8 33.9l-72 80c-4.4 4.9-10.6 7.8-17.2 7.9s-12.9-2.4-17.6-7L7 273c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l22.1 22.1 55.1-61.2c8.9-9.9 24-10.7 33.9-1.8zM224 96c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zm0 160c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zM160 416c0-17.7 14.3-32 32-32l288 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-288 0c-17.7 0-32-14.3-32-32zM48 368a48 48 0 1 1 0 96 48 48 0 1 1 0-96z"
                                   fill="#000000" />
                         </svg>
                         Website Lists
                    </h4>
               </div><!-- /.col -->
               <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                         <li class="breadcrumb-item"><a href="main.php">Home</a></li>
                         <li class="breadcrumb-item"><a href="#">Website Lists</a></li>
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
                    <div class="mt-3 mb-5">
                         <div class="row">
                              <div class="col d-flex align-items-center justify-content-between">
                                   <h5>
                                        <i class="nav-icon mr-3 bi bi-funnel"></i>
                                        Filters
                                        <button class="btn btn-sm btn-outline-secondary px-2 pt-0 pb-1"
                                             onclick="filterAll()" title="Clear all filters">
                                             <small><i class="bi bi-x-lg"></i> Clear</small>
                                        </button>
                                   </h5>
                                   <button id="btnModal" type="button" class="btn btn-primary" onclick="openFormModal()"
                                        data-toggle="modal" data-target="#formModal">
                                        <i class="bi bi-plus"></i> New Item
                                   </button>
                              </div>
                         </div>
                         <div class="row mb-3">
                              <div class="col d-flex flex-row gap-5">
                                   <div class="filterCol">
                                        <label for="filterShopType" class="form-label filterLabel">Shop Type</label>
                                        <select class="form-select filterSelect" id="filterShopType"
                                             onchange="filterChange()" aria-label="Default select example">
                                             <option value="" selected>All</option>
                                             <?php
                                    $dbShoptype = $db->query('SELECT id, name FROM tb_shopType ORDER BY id;')->fetchAll();
                                    foreach ($dbShoptype as $row){
                                        ?>
                                             <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?>
                                             </option>
                                             <?php }//foreach ?>
                                        </select>
                                   </div>
                                   <div class="filterCol">
                                        <label for="filterSystem" class="form-label filterLabel">System</label>
                                        <select class="form-select filterSelect" id="filterSystem"
                                             onchange="filterChange()" aria-label="Default select example">
                                             <option value="" selected>All</option>
                                             <option value="GF">Gloria Food</option>
                                             <option value="AM">Amelia</option>
                                             <option value="VC">Voucher</option>
                                        </select>
                                   </div>
                                   <div class="filterCol">
                                        <label for="filterStatus" class="form-label filterLabel">Status</label>
                                        <select class="form-select filterSelect" id="filterStatus"
                                             onchange="filterChange()" aria-label="Default select example">
                                             <?php
                                        $row = $db->query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'websiteList' AND COLUMN_NAME = 'wLiveStatus'")->fetchArray();;
                                        $enum = str_replace(["enum(", ")", "'"], "", $row['COLUMN_TYPE']);
                                        $options = explode(",", $enum);
                                    ?>
                                             <option value="" selected>All</option>
                                             <?php foreach($options as $liveStatus): ?>
                                             <option value="<?php echo $liveStatus ?>"><?php echo $liveStatus ?>
                                             </option>
                                             <?php endforeach; ?>
                                        </select>
                                   </div>
                              </div>
                         </div>
                         <div class="row mb-3">
                              <div class="col d-flex flex-row gap-5">
                                   <div class="filterCol">
                                        <label for="filterTemplate" class="form-label filterLabel">Template</label>
                                        <select class="form-select filterSelect" id="filterTemplate"
                                             onchange="filterChange()" aria-label="Default select example">
                                             <option value="" selected>All</option>
                                             <?php
                                    $dbWebsiteTemplate = $db->query('SELECT id, template FROM WebsiteTemplate ORDER BY id;')->fetchAll();
                                    foreach ($dbWebsiteTemplate as $row){
                                        ?>
                                             <option value="<?php echo $row['id']; ?>"><?php echo $row['template']; ?>
                                             </option>
                                             <?php }//foreach ?>
                                        </select>
                                   </div>
                                   <div class="filterCol">
                                        <label for="filterCountry" class="form-label filterLabel">Country</label>
                                        <select class="form-select filterSelect" id="filterCountry"
                                             onchange="filterChange()" aria-label="Default select example">
                                             <option value="" selected>All</option>
                                             <?php
                                    $dbCountries = $db->query('SELECT id, code, name FROM Countries ORDER BY id;')->fetchAll();
                                    foreach ($dbCountries as $row){
                                        ?>
                                             <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?>
                                             </option>
                                             <?php }//foreach ?>
                                        </select>
                                   </div>
                                   <div class="filterCol">
                                        <label for="filterServer" class="form-label filterLabel">Server</label>
                                        <select class="form-select filterSelect" id="filterServer"
                                             onchange="filterChange()" aria-label="Default select example">
                                             <option value="" selected>All</option>
                                             <?php
                                    $dbl4uServers = $db->query('SELECT svID, svName FROM L4UServers ORDER BY svID;')->fetchAll();
                                    foreach ($dbl4uServers as $row){
                                        ?>
                                             <option value="<?php echo $row['svID']; ?>"><?php echo $row['svName']; ?>
                                             </option>
                                             <?php }//foreach ?>
                                        </select>
                                        </select>
                                   </div>
                                   <div class="filterCol">
                                        <label for="filterProjectLink" class="form-label filterLabel">Project
                                             link</label>
                                        <select class="form-select filterSelect" id="filterProjectLink"
                                             onchange="filterChange()" aria-label="Default select example">
                                             <option value="" selected>All</option>
                                             <option value="linked">Linked to a template submission</option>
                                             <option value="unlinked">Not linked yet</option>
                                        </select>
                                   </div>
                              </div>
                         </div>
                    </div>

                    <div>
                         <div class="card p-3">
                              <div class="card-body">
                                   <table id="websiteListTable" class="table table-borderless table-striped table-hover"
                                        style="width:100%">
                                        <thead class="thead-dark">
                                             <tr>
                                                  <th width="5%">#</th>
                                                  <th>Project name</th>
                                                  <th>URL</th>
                                                  <th width="16%">Server</th>
                                                  <th width="7%">Status</th>
                                                  <th width="12%"></th>
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
               <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                         <div class="modal-header">
                              <div>
                                   <div class="fm-eyebrow">Website</div>
                                   <h5 class="modal-title" id="formModalLabel">Form Website</h5>
                              </div>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                   onclick="newModalFormAction('close')">
                                   <span>&times;</span>
                              </button>
                         </div>
                         <div class="modal-body">
                              <form>
                                   <!-- 01 Project -->
                                   <section class="fm-section">
                                        <div class="fm-section-head">
                                             <span class="fm-section-num">01</span>
                                             <h6 class="fm-section-title">Project</h6>
                                             <p class="fm-section-desc">Who the site is for and where they are.</p>
                                        </div>
                                        <div class="fm-grid">
                                             <div class="fm-field">
                                                  <label for="inputProject">Project</label>
                                                  <input type="text" class="form-control" id="inputProject"
                                                       maxlength="255" placeholder="e.g. Hoon Hay">
                                             </div>
                                             <div class="fm-field">
                                                  <label for="inputCountry">Country</label>
                                                  <select class="form-select inputCountry" id="inputCountry">
                                                       <option value="" selected>-- None --</option>
                                                       <?php
                                            $dbCountries = $db->query('SELECT id, code, name FROM Countries ORDER BY id;')->fetchAll();
                                            foreach ($dbCountries as $row){
                                                ?>
                                                       <option value="<?php echo $row['id']; ?>">
                                                            <?php echo $row['name']; ?></option>
                                                       <?php }//foreach ?>
                                                  </select>
                                             </div>
                                             <div class="fm-field fm-full">
                                                  <label for="inputLocation">Location</label>
                                                  <textarea class="form-control" id="inputLocation" rows="3"
                                                       placeholder="e.g. Hoon Hay"></textarea>
                                             </div>
                                             <div class="fm-field">
                                                  <label for="inputOwner">Owner</label>
                                                  <input type="text" class="form-control" id="inputOwner"
                                                       maxlength="255" placeholder="e.g. John Doe">
                                             </div>
                                             <div class="fm-field">
                                                  <label for="inputOwnerEmail">Owner Email</label>
                                                  <input type="email" class="form-control" id="inputOwnerEmail"
                                                       maxlength="255" placeholder="e.g. johndoe@gmail.com">
                                             </div>
                                             <div class="fm-field fm-full">
                                                  <label for="inputShopEmail">Shop Email</label>
                                                  <input type="email" class="form-control" id="inputShopEmail"
                                                       maxlength="255" placeholder="e.g. info@hoonhaythaimassage.com">
                                                  <small class="fm-hint">Used for the WordPress "Shop Owner" editor
                                                       account</small>
                                             </div>
                                        </div>
                                   </section>

                                   <!-- 02 Domain -->
                                   <section class="fm-section">
                                        <div class="fm-section-head">
                                             <span class="fm-section-num">02</span>
                                             <h6 class="fm-section-title">Domain</h6>
                                             <p class="fm-section-desc">Address, registrar and go-live state.</p>
                                        </div>
                                        <div class="fm-grid">
                                             <div class="fm-field fm-full">
                                                  <label for="inputDomain">Domain</label>
                                                  <div class="input-group">
                                                       <input type="text" class="form-control" id="inputDomain"
                                                            maxlength="255"
                                                            placeholder="e.g. www.hoonhaythaimassage.com">
                                                       <button class="btn btn-outline-primary" type="button"
                                                            id="btnUpdateDomain" onclick="updateDomain()"
                                                            title="Apply this domain to the cPanel account and WordPress"
                                                            style="display:none;">
                                                            <i class="bi bi-arrow-repeat"></i> Change Domain
                                                       </button>
                                                  </div>
                                                  <small class="fm-hint" id="inputDomainHint"
                                                       style="display:none;">Updates the cPanel account domain and the
                                                       WordPress site URL</small>
                                             </div>
                                             <div class="fm-field">
                                                  <label for="inputDomainProvider">Domain Provider</label>
                                                  <select id="inputDomainProvider" class="form-select">
                                                       <option value="" selected>-- None --</option>
                                                       <?php
                                            $dbDomainProviders = $db->query('SELECT id, name FROM DomainProviders WHERE status=1 ORDER BY id;')->fetchAll();
                                            foreach ($dbDomainProviders as $row){
                                                ?>
                                                       <option value="<?php echo $row['id']; ?>">
                                                            <?php echo $row['name']; ?></option>
                                                       <?php }//foreach ?>
                                                  </select>
                                             </div>
                                             <div class="fm-field">
                                                  <label for="inputPublishedDate">Published Date</label>
                                                  <input type="date" class="form-control" id="inputPublishedDate">
                                             </div>
                                             <div class="fm-field">
                                                  <label for="inputLiveStatus">Live Status</label>
                                                  <select id="inputLiveStatus" class="form-select">
                                                       <?php
                                                $row = $db->query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'websiteList' AND COLUMN_NAME = 'wLiveStatus'")->fetchArray();;
                                                $enum = str_replace(["enum(", ")", "'"], "", $row['COLUMN_TYPE']);
                                                $options = explode(",", $enum);
                                            ?>
                                                       <option value="" selected>-- None --</option>
                                                       <?php foreach($options as $liveStatus): ?>
                                                       <option value="<?php echo $liveStatus ?>">
                                                            <?php echo $liveStatus ?></option>
                                                       <?php endforeach; ?>
                                                  </select>
                                             </div>
                                        </div>
                                   </section>

                                   <!-- 03 Site -->
                                   <section class="fm-section">
                                        <div class="fm-section-head">
                                             <span class="fm-section-num">03</span>
                                             <h6 class="fm-section-title">Site</h6>
                                             <p class="fm-section-desc">Industry, template and the server it lives on.
                                             </p>
                                        </div>
                                        <div class="fm-grid">
                                             <div class="fm-field">
                                                  <label for="inputShopType">Shop Type</label>
                                                  <select id="inputShopType" class="form-select">
                                                       <option value="" selected>-- None --</option>
                                                       <?php
                                            $dbShoptype = $db->query('SELECT id, name FROM tb_shopType WHERE status=1 ORDER BY id;')->fetchAll();
                                            foreach ($dbShoptype as $row){
                                                ?>
                                                       <option value="<?php echo $row['id']; ?>">
                                                            <?php echo $row['name']; ?></option>
                                                       <?php }//foreach ?>
                                                  </select>
                                             </div>
                                             <div class="fm-field">
                                                  <label for="inputTemplate">Template</label>
                                                  <select id="inputTemplate" class="form-select">
                                                       <option value="" selected>-- None --</option>
                                                       <?php
                                            $dbWebsiteTemplate = $db->query('SELECT id, template FROM WebsiteTemplate ORDER BY id;')->fetchAll();
                                            foreach ($dbWebsiteTemplate as $row){
                                                ?>
                                                       <option value="<?php echo $row['id']; ?>">
                                                            <?php echo $row['template']; ?></option>
                                                       <?php }//foreach ?>
                                                  </select>
                                             </div>
                                             <div class="fm-field fm-full">
                                                  <label for="inputServer">L4U Server</label>
                                                  <select id="inputServer" class="form-select">
                                                       <option value="" selected>-- None --</option>
                                                       <?php
                                            $dbl4uServers = $db->query('SELECT svID, svName FROM L4UServers ORDER BY svID;')->fetchAll();
                                            foreach ($dbl4uServers as $row){
                                                ?>
                                                       <option value="<?php echo $row['svID']; ?>">
                                                            <?php echo $row['svName']; ?></option>
                                                       <?php }//foreach ?>
                                                  </select>
                                             </div>
                                        </div>
                                   </section>

                                   <!-- 04 cPanel -->
                                   <section class="fm-section">
                                        <div class="fm-section-head">
                                             <span class="fm-section-num">04</span>
                                             <h6 class="fm-section-title">cPanel</h6>
                                             <p class="fm-section-desc">Hosting account created on WHM when the form is
                                                  saved.</p>
                                        </div>
                                        <div class="fm-grid">
                                             <div class="fm-field">
                                                  <label for="inputCPanelUser">User</label>
                                                  <input type="text" class="form-control" id="inputCPanelUser"
                                                       maxlength="16" placeholder="hoonhay">
                                                  <small class="fm-hint"><span id="inputCPanelUserCount">0</span>/16
                                                       &mdash; WHM limit, lowercase letters and digits only</small>
                                             </div>
                                             <div class="fm-field">
                                                  <label for="inputCPanelPass">Password</label>
                                                  <div class="input-group">
                                                       <input type="text" class="form-control" id="inputCPanelPass"
                                                            placeholder="bXWR8r&8Vb">
                                                       <button class="btn btn-outline-secondary genPassBtn"
                                                            type="button" onclick="generatePassword('inputCPanelPass')"
                                                            title="Generate a secure password">
                                                            <i class="bi bi-shuffle"></i>
                                                       </button>
                                                  </div>
                                             </div>
                                        </div>
                                   </section>

                                   <!-- 05 WordPress -->
                                   <section class="fm-section">
                                        <div class="fm-section-head">
                                             <span class="fm-section-num">05</span>
                                             <h6 class="fm-section-title">WordPress</h6>
                                             <p class="fm-section-desc">Admin login and the defaults Auto Setup applies.
                                             </p>
                                        </div>
                                        <div class="fm-grid">
                                             <div class="fm-field">
                                                  <label for="inputWordpressUser">User</label>
                                                  <input type="text" class="form-control" id="inputWordpressUser"
                                                       placeholder="hoonhay">
                                                  <small class="fm-hint">Kept in step with the cPanel user</small>
                                             </div>
                                             <div class="fm-field">
                                                  <label for="inputWordpressPass">Password</label>
                                                  <div class="input-group">
                                                       <input type="text" class="form-control" id="inputWordpressPass"
                                                            placeholder="99U@VFe~Ypm+">
                                                       <button class="btn btn-outline-secondary genPassBtn"
                                                            type="button"
                                                            onclick="generatePassword('inputWordpressPass')"
                                                            title="Generate a secure password">
                                                            <i class="bi bi-shuffle"></i>
                                                       </button>
                                                  </div>
                                             </div>
                                             <div class="fm-field fm-full">
                                                  <label for="inputWordpressURL">Admin URL</label>
                                                  <input type="text" class="form-control" id="inputWordpressURL"
                                                       placeholder="https://www.hoonhay.com/wp-admin/">
                                             </div>
                                             <div class="fm-field">
                                                  <label for="inputWPLocale">Language &amp; Timezone</label>
                                                  <select id="inputWPLocale" class="form-select">
                                                       <?php
                                            foreach (WordPressSetup::locales() as $key => $entry){
                                                ?>
                                                       <option value="<?php echo $key; ?>"
                                                            <?php echo $key === 'en_GB' ? ' selected' : ''; ?>>
                                                            <?php echo $entry['label'] . ' &mdash; ' . $entry['timezone']; ?>
                                                       </option>
                                                       <?php }//foreach ?>
                                                  </select>
                                                  <small class="fm-hint">Applied by Auto Setup, not by saving</small>
                                             </div>
                                             <div class="fm-field">
                                                  <label for="inputWPTagline">Tagline</label>
                                                  <input type="text" class="form-control" id="inputWPTagline"
                                                       maxlength="255" placeholder="Leave empty to remove the tagline">
                                                  <small class="fm-hint">Only fill this in if the client has a tagline
                                                       of their own</small>
                                             </div>
                                        </div>
                                   </section>

                                   <!-- 06 Mailboxes -->
                                   <section class="fm-section">
                                        <div class="fm-section-head">
                                             <span class="fm-section-num">06</span>
                                             <h6 class="fm-section-title">Mailboxes</h6>
                                             <p class="fm-section-desc">Both accounts are created on cPanel when the
                                                  form is saved.</p>
                                        </div>
                                        <div class="fm-grid">
                                             <div class="fm-subhead fm-full">SMTP</div>
                                             <div class="fm-field">
                                                  <label for="inputSMTPUser">Email</label>
                                                  <div class="input-group">
                                                       <input type="text" class="form-control" id="inputSMTPUser"
                                                            placeholder="noreply@hoonhay.com">
                                                       <button class="btn btn-outline-secondary emailSettingsBtn"
                                                            type="button" onclick="emailSettings('inputSMTPUser', this)"
                                                            title="Show the mail client settings for this mailbox"
                                                            style="display:none;">
                                                            <i class="bi bi-phone"></i>
                                                       </button>
                                                  </div>
                                             </div>
                                             <div class="fm-field">
                                                  <label for="inputSMTPPass">Password</label>
                                                  <input type="text" class="form-control" id="inputSMTPPass"
                                                       placeholder="L4U@Notifications">
                                             </div>
                                             <div class="fm-field fm-full">
                                                  <label for="inputSMTPRemark">Remark</label>
                                                  <input type="text" class="form-control" id="inputSMTPRemark"
                                                       placeholder="e.g. SMTP for Hoonhay">
                                             </div>

                                             <div class="fm-subhead fm-full">Contact</div>
                                             <div class="fm-field">
                                                  <label for="inputContactEmailUser">Email</label>
                                                  <div class="input-group">
                                                       <input type="text" class="form-control"
                                                            id="inputContactEmailUser" placeholder="info@hoonhay.com">
                                                       <button class="btn btn-outline-secondary emailSettingsBtn"
                                                            type="button"
                                                            onclick="emailSettings('inputContactEmailUser', this)"
                                                            title="Show the mail client settings for this mailbox"
                                                            style="display:none;">
                                                            <i class="bi bi-phone"></i>
                                                       </button>
                                                  </div>
                                             </div>
                                             <div class="fm-field">
                                                  <label for="inputContactEmailPass">Password</label>
                                                  <input type="text" class="form-control" id="inputContactEmailPass"
                                                       placeholder="hoonhay@123">
                                             </div>
                                             <div class="fm-field fm-full">
                                                  <label for="inputContactEmailRemark">Remark</label>
                                                  <input type="text" class="form-control" id="inputContactEmailRemark"
                                                       placeholder="e.g. Email for Hoonhay">
                                             </div>
                                        </div>
                                   </section>

                                   <!-- 07 Systems -->
                                   <section class="fm-section">
                                        <div class="fm-section-head">
                                             <span class="fm-section-num">07</span>
                                             <h6 class="fm-section-title">Systems</h6>
                                             <p class="fm-section-desc">Third-party services wired into the site.</p>
                                        </div>
                                        <div class="fm-grid">
                                             <div class="fm-chips fm-full">
                                                  <div class="fm-chip">
                                                       <input type="checkbox" id="inputGloriaFood">
                                                       <label for="inputGloriaFood">Gloria Food</label>
                                                  </div>
                                                  <div class="fm-chip">
                                                       <input type="checkbox" id="inputAmelia">
                                                       <label for="inputAmelia">Amelia</label>
                                                  </div>
                                                  <div class="fm-chip">
                                                       <input type="checkbox" id="inputVoucher">
                                                       <label for="inputVoucher">Voucher</label>
                                                  </div>
                                                  <div class="fm-chip">
                                                       <input type="checkbox" id="inputCloudwaitress">
                                                       <label for="inputCloudwaitress">Cloudwaitress</label>
                                                  </div>
                                                  <div class="fm-chip">
                                                       <input type="checkbox" id="inputOtherCheck"
                                                            onchange="toggleOtherInput()">
                                                       <label for="inputOtherCheck">Other</label>
                                                  </div>
                                             </div>
                                             <div class="fm-field fm-full" id="otherInputGroup" style="display:none;">
                                                  <label for="inputOther">Other (details)</label>
                                                  <span id="inputOtherDisplay" class="form-control"
                                                       style="display:none; cursor:pointer; min-height:38px;"
                                                       ondblclick="editOtherInput()"></span>
                                                  <textarea class="form-control" id="inputOther" rows="2"
                                                       maxlength="300"
                                                       placeholder="e.g. OpenTable, Resy ..."></textarea>
                                                  <small class="fm-hint"><span id="inputOtherCount">0</span>/300 &mdash;
                                                       double-click the saved text to edit it</small>
                                             </div>
                                        </div>
                                   </section>

                                   <input type="hidden" name="editID" id="editID" value="">
                                   <input type="hidden" name="formAction" id="formAction" value="add">
                              </form>
                         </div> <!-- modal-body -->

                         <div class="modal-footer flex-column align-items-stretch">
                              <div id="saveProgress" class="save-progress w-100 mb-2" style="display:none;">
                                   <div class="progress mb-2" style="height:4px;">
                                        <div id="saveProgressBar"
                                             class="progress-bar progress-bar-striped progress-bar-animated"
                                             role="progressbar" style="width:0%;"></div>
                                   </div>
                                   <ul class="list-unstyled mb-0" id="saveProgressSteps">
                                        <li class="save-step" data-step="db">
                                             <span class="save-step-icon"></span>
                                             <span class="save-step-label">Saving website record</span>
                                        </li>
                                        <li class="save-step" data-step="cpanel">
                                             <span class="save-step-icon"></span>
                                             <span class="save-step-label">Creating cPanel account on WHM</span>
                                        </li>
                                        <li class="save-step" data-step="mailboxes">
                                             <span class="save-step-icon"></span>
                                             <span class="save-step-label">Creating the mailboxes</span>
                                        </li>
                                        <li class="save-step" data-step="wordpress">
                                             <span class="save-step-icon"></span>
                                             <span class="save-step-label">Installing WordPress</span>
                                        </li>
                                   </ul>
                              </div>
                              <div id="wpSetupSteps" class="save-progress w-100 mb-2" style="display:none;">
                                   <ul class="list-unstyled mb-0" id="wpSetupStepsList"></ul>
                              </div>
                              <div class="fm-actions">
                                   <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                        onclick="newModalFormAction('close')">Close</button>
                                   <div class="fm-actions-right">
                                        <button class="btn btn-outline-primary" type="button" id="btnStaffEmails"
                                             onclick="staffEmails()"
                                             title="Create staff1 to staff7 mailboxes on this domain"
                                             style="display:none;">
                                             <i class="bi bi-envelope-plus"></i> Create 7 Staff Emails
                                        </button>
                                        <button class="btn btn-outline-primary" type="button" id="btnWpSetup"
                                             onclick="wpSetup()"
                                             title="Apply the standard WordPress settings and user accounts to this site"
                                             style="display:none;">
                                             <i class="bi bi-magic"></i> Auto Setup WordPress
                                        </button>
                                        <button onclick="formSave();" type="button" class="btn btn-primary"
                                             name="cmdSubmit" id="cmdSubmit">Save changes</button>
                                   </div>
                              </div>
                         </div>
                    </div>
               </div>
          </div>

          <!-- What a mail client needs for one mailbox, as cPanel reports it -->
          <div class="modal fade" id="mailSettingsModal" tabindex="-1" aria-hidden="true">
               <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                         <div class="modal-header">
                              <h5 class="modal-title">Mail client settings</h5>
                              <!-- Bootstrap 4 and 5 are both on this page and each
                             reads its own dismiss attribute, so both are set
                             and a handler closes the modal either way -->
                              <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"
                                   aria-label="Close" onclick="hideMailSettings()">
                                   <span aria-hidden="true">&times;</span>
                              </button>
                         </div>
                         <div class="modal-body" id="mailSettingsBody"></div>
                         <div class="modal-footer">
                              <button type="button" class="btn btn-outline-secondary" onclick="copyMailSettings()">
                                   <i class="bi bi-clipboard"></i> Copy all
                              </button>
                              <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                   data-bs-dismiss="modal" onclick="hideMailSettings()">Close</button>
                         </div>
                    </div>
               </div>
          </div>

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
                                                            <td><span class="mr-3" id="wIndustry"></span> <b>Template
                                                                      :</b> <span id="wTemplateUsed"></span></td>
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
                                                            <td><span id="wDomainProvidersID"></span></td>
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
                                                            <td><span id="wPublishedDate"></span></td>
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
                                                            <th scope="row" class="colInfo">cPanel Log-in</th>
                                                            <td><span id="cPanelURL"></span></td>
                                                       </tr>
                                                       <tr>
                                                            <th scope="row" class="colInfo">cPanel User</th>
                                                            <td><span id="wCPanelUser"></span></td>
                                                       </tr>
                                                       <tr>
                                                            <th scope="row" class="colInfo">cPanel Pass</th>
                                                            <td><span id="wCPanelPass"></span></td>
                                                       </tr>
                                                  </tbody>
                                             </table>
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

          <!-- /.modal -->
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.js"></script>
<script src="plugins/datatables-bs5/js/datatables-bs5.min.js"></script>

<script>
function showCopy() {
     $("#alert").fadeIn(500);
     setTimeout(function() {
          $("#alert").fadeOut();
     }, 1000);
}

function copyText(text) {
     navigator.clipboard.writeText(text).then(function() {
          showCopy();
     }).catch(function(err) {
          console.error("Error copying text: ", error);
     });
}

// Modal Form Action
const inputProject = $("#inputProject");
const inputCountry = $("#inputCountry");
const inputLocation = $("#inputLocation");
const inputOwner = $("#inputOwner");
const inputOwnerEmail = $("#inputOwnerEmail");
const inputDomain = $("#inputDomain");
const inputDomainProvider = $("#inputDomainProvider");
const inputPublishedDate = $("#inputPublishedDate");
const inputLiveStatus = $("#inputLiveStatus");
const inputShopType = $("#inputShopType");
const inputTemplate = $("#inputTemplate");
const inputServer = $("#inputServer");
const inputCPanelUser = $("#inputCPanelUser");
const inputCPanelPass = $("#inputCPanelPass");
const inputWordpressUser = $("#inputWordpressUser");
const inputWordpressPass = $("#inputWordpressPass");
const inputWordpressURL = $("#inputWordpressURL");
const inputWPLocale = $("#inputWPLocale");
const inputWPTagline = $("#inputWPTagline");
const inputShopEmail = $("#inputShopEmail");
const inputSMTPUser = $("#inputSMTPUser");
const inputSMTPPass = $("#inputSMTPPass");
const inputSMTPRemark = $("#inputSMTPRemark");
const inputContactEmailUser = $("#inputContactEmailUser");
const inputContactEmailPass = $("#inputContactEmailPass");
const inputContactEmailRemark = $("#inputContactEmailRemark");
const inputGloriaFood = $("#inputGloriaFood");
const inputAmelia = $("#inputAmelia");
const inputVoucher = $("#inputVoucher");
const inputCloudwaitress = $("#inputCloudwaitress");
const inputOtherCheck = $("#inputOtherCheck");
const inputOther = $("#inputOther");
const editID = $("#editID");
const formAction = $("#formAction");

function toggleOtherInput() {
     const checked = inputOtherCheck.prop("checked");
     $("#otherInputGroup").toggle(checked);
     if (!checked) {
          inputOther.val('');
          $("#inputOtherDisplay").hide().text('');
          inputOther.show();
          $("#inputOtherCount").text('0');
     }
}

function editOtherInput() {
     const currentText = $("#inputOtherDisplay").text();
     $("#inputOtherDisplay").hide();
     inputOther.val(currentText).show().focus();
     $("#inputOtherCount").text(currentText.length);
}

inputOther.on('input', function() {
     const len = $(this).val().length;
     $("#inputOtherCount").text(len);
});

// cPanel usernames are capped at 16 chars and must be lowercase alphanumeric
inputCPanelUser.on('input', function() {
     const cleaned = $(this).val().toLowerCase().replace(/[^a-z0-9]/g, '').substring(0, 16);
     if ($(this).val() !== cleaned) {
          $(this).val(cleaned);
     }
     $("#inputCPanelUserCount").text(cleaned.length);
     // WordPress logs in with the same account name
     inputWordpressUser.val(cleaned);
});

// Password the provisioning APIs will accept: mixed case, digits and
// punctuation that survives being passed through a URL query string.
const generatePassword = (targetID) => {
     const sets = [
          "abcdefghijkmnopqrstuvwxyz",
          "ABCDEFGHJKLMNPQRSTUVWXYZ",
          "23456789",
          "!@#$%^*_-+="
     ];
     const all = sets.join('');
     const bytes = new Uint32Array(20);
     window.crypto.getRandomValues(bytes);

     // Guarantee one character from each set, then fill the rest
     let chars = sets.map((set, i) => set[bytes[i] % set.length]);
     for (let i = sets.length; i < 20; i++) {
          chars.push(all[bytes[i] % all.length]);
     }

     // Shuffle so the guaranteed characters are not always at the front
     const order = new Uint32Array(chars.length);
     window.crypto.getRandomValues(order);
     for (let i = chars.length - 1; i > 0; i--) {
          const j = order[i] % (i + 1);
          [chars[i], chars[j]] = [chars[j], chars[i]];
     }

     $("#" + targetID).val(chars.join('')).trigger('input');
} //generatePassword

// Modal Form Detail
const ProjectName = $("#wProject");
const Location = $("#wLocation");
const Owner = $("#wOwner");
const OwnerEmail = $("#wOwnerEmail");
const Industry = $("#wIndustry");
const TemplateUsed = $("#wTemplateUsed");
const System = $("#wSystem");
const DomainName = $("#wDomain");
const DomainProvidersID = $("#wDomainProvidersID");
const PublishedDate = $("#wPublishedDate");
const LiveStatus = $("#wLiveStatus");
const cPanelURL = $("#cPanelURL");
const CPanelUser = $("#wCPanelUser");
const CPanelPass = $("#wCPanelPass");
const WordpressURL = $("#wWordpressURL");
const WordpressUser = $("#wWordpressUser");
const WordpressPass = $("#wWordpressPass");
const SMTPEmailUser = $("#wSMTPEmailUser");
const SMTPEmailPass = $("#wSMTPEmailPass");
const SMTPRemark = $("#wSMTPRemark");
const ContactEmailUser = $("#wContactEmailUser");
const ContactEmailPass = $("#wContactEmailPass");
const ContactEmailRemark = $("#wContactEmailRemark");

const filterShopType = $("#filterShopType");
const filterSystem = $("#filterSystem");
const filterStatus = $("#filterStatus");
const filterTemplate = $("#filterTemplate");
const filterCountry = $("#filterCountry");
const filterServer = $("#filterServer");
const filterProjectLink = $("#filterProjectLink");

const newModalForm = new bootstrap.Modal(document.getElementById("formModal"), {});

let shopType = filterShopType.val();
let system = filterSystem.val();
let fstatus = filterStatus.val();
let template = filterTemplate.val();
let country = filterCountry.val();
let server = filterServer.val();

let txt = '';
let txt2 = '';
let urlTxt = '';

let iconCopy =
     '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-copy" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/></svg>';
let iconLink =
     '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/><path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/></svg>';

const cmdSubmit = $("#cmdSubmit");

// Neither the domain update nor the WordPress setup makes sense until the
// site has been provisioned, so both follow the same condition
function toggleDomainUpdate(show) {
     $("#btnUpdateDomain").toggle(show);
     $("#inputDomainHint").toggle(show);
     $("#btnWpSetup").toggle(show);
     $("#btnStaffEmails").toggle(show);
     $(".emailSettingsBtn").toggle(show);
     if (!show) {
          $("#wpSetupSteps").hide();
          $("#wpSetupStepsList").empty();
     }
}

// The generators are for picking a password on a site that does not have
// one yet. Once the account exists the stored password is the real one,
// and overwriting it here would only put the record out of step with the
// server, so the buttons are kept to adding a website.
function toggleGeneratePassword(isAdd) {
     // Driven off the hidden formAction field rather than the caller, so
     // the buttons follow the form's actual mode however it was reached
     const adding = isAdd !== undefined ? isAdd : formAction.val() !== "edit";
     $(".genPassBtn").toggleClass("d-none", !adding);
}

function openFormModal() {
     toggleDomainUpdate(false);
     // Adding, so the form starts in that mode before the modal opens
     formAction.val('add');
     newModalFormAction("open");
}

const viewDetail = (id) => {
     $.ajax({
          url: "assets/php/actionWebsiteList.php",
          method: "POST",
          dataType: "json",
          data: {
               act: "viewDetail",
               id: id
          },
          success: function(res) {
               txt =
               `<a onclick="copyText('${res.wProject}')" href="#">${iconCopy}</a> ${res.wProject}`;
               ProjectName.html(txt);
               Location.text(res.wLocation);
               Owner.text(res.wOwner);
               OwnerEmail.text(res.wOwnerEmail);
               Industry.text(res.wIndustry);
               TemplateUsed.text(res.wTemplateUsed);

               let systems = [];
               if (res.wSystemGloriaFood == 1) systems.push("Gloria Food");
               if (res.wSystemAmelia == 1) systems.push("Amelia");
               if (res.wSystemVoucher == 1) systems.push("Voucher");
               if (res.wSystemCloudwaitress == 1) systems.push("Cloudwaitress");
               if (res.wSystemOther) systems.push(res.wSystemOther);
               System.text(systems.length > 0 ? systems.join(", ") : "-");

               DomainName.text(res.wDomain);
               DomainProvidersID.text(res.domainProvidersName);
               PublishedDate.text(res.wPublishedDate);
               LiveStatus.text(res.wLiveStatus);

               cPanelLink =
                    `<a href="${res.svCpanelURL}" target="_blank">${iconLink}</a> ${res.svCpanelURL}`;
               cPanelURL.html(cPanelLink);
               CPanelUser.text(res.wCPanelUser);
               CPanelPass.text(res.wCPanelPass);

               urlTxt =
                    `<a href="${res.wWordpressURL}" target="_blank">${iconLink}</a> ${res.wWordpressURL}`;
               WordpressURL.html(urlTxt);

               txt =
               `<a onclick="copyText('admin@localforyou.com')" href="#">${iconCopy}</a> admin@localforyou.com`;
               txt2 = `<a onclick="copyText('L4U=New@min')" href="#">${iconCopy}</a> L4U=New@min`;

               WordpressUser.html(res.wWordpressUser);
               WordpressPass.html(res.wWordpressPass);

               SMTPEmailUser.text(res.wSMTPEmailUser);
               SMTPEmailPass.text(res.wSMTPEmailPass);
               SMTPRemark.text(res.wSMTPRemark);
               ContactEmailUser.text(res.wContactEmailUser);
               ContactEmailPass.text(res.wContactEmailPass);
               ContactEmailRemark.text(res.wContactEmailRemark);

               $("#detailModal").modal("show");
          },
          error: function(xhr, status, error) {
               console.error("AJAX Error:", status, error);
          }
     });
};

const setEdit = (id) => {

     const reqAjax = $.ajax({
          url: "assets/php/actionWebsiteList.php",
          method: "POST",
          async: false,
          cache: false,
          dataType: "json",
          data: {
               act: "loadUpdate",
               id: id,
          },
     });


     reqAjax.done(function(res) {
          console.log(res);
          inputProject.val(res.wProject);
          inputCountry.val(res.countryID).prop("selected", true);
          inputDomain.val(res.wDomain);
          inputLocation.val(res.wLocation);
          inputOwner.val(res.wOwner);
          inputOwnerEmail.val(res.wOwnerEmail);
          inputDomainProvider.val(res.wDomainProvidersID).prop("selected", true);
          inputPublishedDate.val(res.wPublishedDate);
          inputLiveStatus.val(res.wLiveStatus);
          inputShopType.val(res.wIndustry).prop("selected", true);
          inputTemplate.val(res.wTemplateUsed).prop("selected", true);
          inputServer.val(res.wServerID);
          inputCPanelUser.val(res.wCPanelUser);
          $("#inputCPanelUserCount").text((res.wCPanelUser || '').length);
          inputCPanelPass.val(res.wCPanelPass);
          inputWordpressUser.val(res.wWordpressUser);
          inputWordpressPass.val(res.wWordpressPass);
          inputWordpressURL.val(res.wWordpressURL);
          inputWPLocale.val(res.wWPLocale || "en_GB");
          inputShopEmail.val(res.wShopEmail);
          inputSMTPUser.val(res.wSMTPEmailUser);
          inputSMTPPass.val(res.wSMTPEmailPass);
          inputSMTPRemark.val(res.wSMTPRemark);
          inputContactEmailUser.val(res.wContactEmailUser);
          inputContactEmailPass.val(res.wContactEmailPass);
          inputContactEmailRemark.val(res.wContactEmailRemark);
          inputGloriaFood.prop("checked", res.wSystemGloriaFood == 1);
          inputAmelia.prop("checked", res.wSystemAmelia == 1);
          inputVoucher.prop("checked", res.wSystemVoucher == 1);
          inputCloudwaitress.prop("checked", res.wSystemCloudwaitress == 1);
          const hasOther = res.wSystemOther && res.wSystemOther.trim() !== '';
          inputOtherCheck.prop("checked", hasOther);
          if (hasOther) {
               $("#otherInputGroup").show();
               inputOther.hide();
               $("#inputOtherDisplay").show().text(res.wSystemOther);
               $("#inputOtherCount").text(res.wSystemOther.length);
          } else {
               $("#otherInputGroup").hide();
               inputOther.val('').show();
               $("#inputOtherDisplay").hide().text('');
               $("#inputOtherCount").text('0');
          }
          editID.val(res.id);
          formAction.val("edit");
          // Only a provisioned site has a cPanel account to move
          toggleDomainUpdate(!!res.wCPanelUser);
          toggleGeneratePassword(false);
          newModalFormAction("open");
     });

     reqAjax.fail(function(xhr, status, error) {
          console.log("ajax loadUpdate fail!!");
          console.log(status + ": " + error);
     })
} // setEdit

const formSave = () => {
     let payload = {
          act: "save",
          inputProject: inputProject.val(),
          inputCountry: inputCountry.val(),
          inputLocation: inputLocation.val(),
          inputOwner: inputOwner.val(),
          inputOwnerEmail: inputOwnerEmail.val(),
          inputDomain: inputDomain.val(),
          inputDomainProvider: inputDomainProvider.val(),
          inputPublishedDate: inputPublishedDate.val(),
          inputLiveStatus: inputLiveStatus.val(),
          inputShopType: inputShopType.val(),
          inputTemplate: inputTemplate.val(),
          inputServer: inputServer.val(),
          inputCPanelUser: inputCPanelUser.val(),
          inputCPanelPass: inputCPanelPass.val(),
          inputWordpressUser: inputWordpressUser.val(),
          inputWordpressPass: inputWordpressPass.val(),
          inputWordpressURL: inputWordpressURL.val(),
          inputWPLocale: inputWPLocale.val(),
          inputShopEmail: inputShopEmail.val(),
          inputSMTPUser: inputSMTPUser.val(),
          inputSMTPPass: inputSMTPPass.val(),
          inputSMTPRemark: inputSMTPRemark.val(),
          inputContactEmailUser: inputContactEmailUser.val(),
          inputContactEmailPass: inputContactEmailPass.val(),
          inputContactEmailRemark: inputContactEmailRemark.val(),
          inputGloriaFood: inputGloriaFood.prop("checked") ? 1 : 0,
          inputAmelia: inputAmelia.prop("checked") ? 1 : 0,
          inputVoucher: inputVoucher.prop("checked") ? 1 : 0,
          inputCloudwaitress: inputCloudwaitress.prop("checked") ? 1 : 0,
          inputOther: inputOtherCheck.prop("checked") ? ($("#inputOtherDisplay").is(":visible") ? $(
               "#inputOtherDisplay").text() : inputOther.val().substring(0, 300)) : '',
          editID: editID.val(),
          formAction: formAction.val(),
     };

     console.log("payload=", payload);

     const isAdd = formAction.val() === 'add';

     progressStart(isAdd);
     cmdSubmit.prop("disabled", true);

     const reqAjax = $.ajax({
          url: "assets/php/actionWebsiteList.php",
          method: "POST",
          cache: false,
          dataType: "json",
          data: payload
     });

     reqAjax.done(function(res) {
          console.log(res);
          progressFinish(res, isAdd);
     });

     reqAjax.fail(function(xhr, status, error) {
          console.log("ajax save fail!!");
          console.log(status + ": " + error);
          progressStepState("db", "failed", status + ": " + error);
          cmdSubmit.prop("disabled", false);
     });

} //formSave

// ---- Save progress panel -------------------------------------------------

const progressStepState = (step, state, detail) => {
     const $step = $('.save-step[data-step="' + step + '"]');
     $step.removeClass("is-running is-done is-failed is-skipped").addClass("is-" + state);
     $step.find(".save-step-detail").remove();
     if (detail) {
          $step.append('<span class="save-step-detail"></span>');
          $step.find(".save-step-detail").text(detail);
     }
} //progressStepState

const progressBar = (percent) => {
     $("#saveProgressBar").css("width", percent + "%");
} //progressBar

// Reset the panel and show the first step as running
const progressStart = (isAdd) => {
     $(".save-step").removeClass("is-running is-done is-failed is-skipped");
     $(".save-step-detail").remove();
     $('.save-step[data-step="cpanel"]').toggle(isAdd);
     $('.save-step[data-step="mailboxes"]').toggle(isAdd);
     $('.save-step[data-step="wordpress"]').toggle(isAdd);
     $("#saveProgressBar").removeClass("bg-danger bg-warning bg-success");
     progressBar(10);
     $("#saveProgress").show();
     progressStepState("db", "running");
} //progressStart

// Fill in the mailbox step from what the server reported.
// Returns true when something went wrong, so the caller can keep the
// modal open.
const renderMailboxStep = (boxes) => {
     if (!boxes || !Array.isArray(boxes.results) || boxes.results.length === 0) {
          progressStepState("mailboxes", "skipped", "No mailbox addresses were filled in");
          return false;
     }

     const made = boxes.results.filter(function(b) {
          return b.success;
     });

     if (made.length === boxes.results.length) {
          progressStepState("mailboxes", "done", made.map(function(b) {
               return b.email;
          }).join(", "));
          return false;
     }

     const failed = boxes.results.filter(function(b) {
          return !b.success;
     });
     progressStepState("mailboxes", "failed", failed.map(function(b) {
          return b.email + ": " + b.message;
     }).join(" | "));
     $("#saveProgressBar").addClass("bg-danger");

     return true;
} //renderMailboxStep

// Walk the panel through the server's reported outcome, then close the modal
const progressFinish = (res, isAdd) => {
     progressStepState("db", "done", isAdd ? "Website record #" + res.insertedID + " created" :
          "Website record updated");

     if (!isAdd) {
          // Editing only reaches cPanel when a mailbox address was filled in
          const edited = res.mailboxes;

          if (!edited || !Array.isArray(edited.results) || edited.results.length === 0) {
               progressBar(100);
               finishAndClose(600);
               return;
          }

          progressBar(60);
          $('.save-step[data-step="mailboxes"]').show();
          const editFailed = renderMailboxStep(edited);
          progressBar(100);

          if (editFailed) {
               cmdSubmit.prop("disabled", false);
               reloadTable_bs5();
          } else {
               finishAndClose(1500);
          }

          return;
     }

     progressBar(45);
     progressStepState("cpanel", "running");

     // Small delay so the step change is visible rather than instant
     setTimeout(function() {
          const whm = res.whm;

          if (!whm) {
               progressStepState("cpanel", "skipped", "No result returned from the server");
          } else if (whm.success) {
               progressStepState("cpanel", "done", whm.username + " @ " + whm.domain);
          } else if (whm.attempted) {
               progressStepState("cpanel", "failed", whm.message);
               $("#saveProgressBar").addClass("bg-danger");
          } else {
               progressStepState("cpanel", "skipped", whm.message);
               $("#saveProgressBar").addClass("bg-warning");
          }

          progressBar(60);

          const boxes = res.mailboxes;
          const boxFailed = renderMailboxStep(boxes);

          progressBar(75);

          const wp = res.wordpress;

          if (!wp) {
               progressStepState("wordpress", "skipped", "No result returned from the server");
          } else if (wp.success) {
               progressStepState("wordpress", "done", wp.adminUrl);
          } else if (wp.attempted) {
               progressStepState("wordpress", "failed", wp.message);
               $("#saveProgressBar").addClass("bg-danger");
          } else {
               progressStepState("wordpress", "skipped", wp.message);
          }

          progressBar(100);

          // Leave failures on screen until the user closes the modal
          const cpanelFailed = whm && whm.attempted && !whm.success;
          const wpFailed = wp && wp.attempted && !wp.success;
          if (cpanelFailed || wpFailed || boxFailed) {
               cmdSubmit.prop("disabled", false);
               reloadTable_bs5();
          } else {
               finishAndClose(1500);
          }
     }, 500);
} //progressFinish

const finishAndClose = (delay) => {
     setTimeout(function() {
          cmdSubmit.prop("disabled", false);
          $("#saveProgress").hide();
          resetForm();
          newModalFormAction("close");
     }, delay);
} //finishAndClose

const resetForm = () => {
     inputProject.val('');
     inputCountry.val('');
     inputLocation.val('');
     inputOwner.val('');
     inputOwnerEmail.val('');
     inputDomain.val('');
     inputDomainProvider.val('');
     inputPublishedDate.val('');
     inputLiveStatus.val('');
     inputShopType.val('');
     inputTemplate.val('');
     inputServer.val('');
     inputCPanelUser.val('');
     $("#inputCPanelUserCount").text('0');
     inputCPanelPass.val('');
     inputWordpressUser.val('');
     inputWordpressPass.val('');
     inputWordpressURL.val('');
     inputWPLocale.val('en_GB');
     inputWPTagline.val('');
     inputShopEmail.val('');
     inputSMTPUser.val('');
     inputSMTPPass.val('');
     inputSMTPRemark.val('');
     inputContactEmailUser.val('');
     inputContactEmailPass.val('');
     inputContactEmailRemark.val('');
     inputGloriaFood.prop("checked", false);
     inputAmelia.prop("checked", false);
     inputVoucher.prop("checked", false);
     inputCloudwaitress.prop("checked", false);
     inputOtherCheck.prop("checked", false);
     $("#otherInputGroup").hide();
     inputOther.val('').show();
     $("#inputOtherDisplay").hide().text('');
     $("#inputOtherCount").text('0');
     editID.val('');
     formAction.val('add');
     toggleDomainUpdate(false);
     toggleGeneratePassword(true);
     reloadTable_bs5();
} //resetForm

// Apply the domain in the form to the live cPanel account and WordPress install.
const updateDomain = () => {
     const wID = editID.val();
     const newDomain = inputDomain.val().trim();

     if (!wID) {
          alert("Save the website first, then update its domain.");
          return;
     }

     if (newDomain === "") {
          alert("Enter the new domain first.");
          return;
     }

     const answer = confirm(
          "This changes the live site, not just this record.\n\n" +
          "The cPanel account's primary domain and the WordPress site URL will both be set to:\n\n" +
          "    " + newDomain + "\n\n" +
          "Continue?"
     );

     if (!answer) return;

     const $btn = $("#btnUpdateDomain");
     const originalHtml = $btn.html();
     $btn.prop("disabled", true).html('<i class="bi bi-hourglass-split"></i> Updating');

     $.ajax({
          url: "assets/php/actionWebsiteList.php",
          method: "POST",
          cache: false,
          dataType: "json",
          data: {
               act: "updateDomain",
               id: wID,
               newDomain: newDomain
          }
     }).done(function(res) {
          console.log(res);
          if (res.success) {
               alert("Domain updated.\n\n" + res.message);
               reloadTable_bs5();
          } else {
               alert("Domain was not fully updated.\n\n" + (res.message || "Unknown error."));
          }
     }).fail(function(xhr, status, error) {
          console.log("ajax updateDomain fail!!");
          console.log(status + ": " + error);
          alert("Domain update failed.\n\n" + status + ": " + error);
     }).always(function() {
          $btn.prop("disabled", false).html(originalHtml);
     });
} //updateDomain

// Apply the standard WordPress settings and user accounts to a site that
// has already been provisioned. Every step overwrites what is there, so
// pressing the button a second time is safe and simply reapplies them.
const wpSetup = () => {
     const wID = editID.val();

     if (!wID) {
          alert("Save the website first, then run the setup.");
          return;
     }

     const shopEmail = inputShopEmail.val().trim();

     const answer = confirm(
          "This changes the live site, not just this record.\n\n" +
          "Language, timezone, admin email, tagline, ping services and search\n" +
          "engine visibility will be set, the Admin and Shop Owner accounts\n" +
          "will be created or brought back in line, and the standard plugins\n" +
          "will be installed and activated.\n\n" +
          "Installing the plugins can take a few minutes.\n\n" +
          (shopEmail === "" ?
               "No shop email is filled in, so the Shop Owner account will be skipped.\n\n" :
               "Shop Owner account: " + shopEmail + "\n\n") +
          "Continue?"
     );

     if (!answer) return;

     const $btn = $("#btnWpSetup");
     const originalHtml = $btn.html();
     $btn.prop("disabled", true).html('<i class="bi bi-hourglass-split"></i> Setting up');

     const $steps = $("#wpSetupStepsList");
     $steps.empty().append(
          '<li class="save-step is-running"><span class="save-step-icon"></span><span class="save-step-label">Applying the WordPress settings</span></li>'
          );
     $("#wpSetupSteps").show();

     $.ajax({
          url: "assets/php/actionWebsiteList.php",
          method: "POST",
          cache: false,
          dataType: "json",
          data: {
               act: "wpSetup",
               id: wID,
               localeKey: inputWPLocale.val(),
               shopEmail: shopEmail,
               tagline: inputWPTagline.val()
          }
     }).done(function(res) {
          console.log(res);
          renderWpSetupSteps(res);

          if (!res.success) {
               alert("The WordPress setup did not finish cleanly.\n\n" + (res.message ||
                    "Unknown error."));
          }
     }).fail(function(xhr, status, error) {
          console.log("ajax wpSetup fail!!");
          console.log(status + ": " + error);
          $steps.empty().append(
               '<li class="save-step is-failed"><span class="save-step-icon"></span><span class="save-step-label">' +
               escapeHtml(status + ": " + error) + '</span></li>');
          alert("The WordPress setup failed.\n\n" + status + ": " + error);
     }).always(function() {
          $btn.prop("disabled", false).html(originalHtml);
     });
} //wpSetup

// Create the seven staff mailboxes on this site's domain. They all share
// one password, so nothing is asked for here. An address that already
// exists has its password reset rather than being reported as an error.
const staffEmails = () => {
     const wID = editID.val();

     if (!wID) {
          alert("Save the website first, then create the mailboxes.");
          return;
     }

     const domain = inputDomain.val().trim();

     const answer = confirm(
          "This creates seven mailboxes on the live cPanel account:\n\n" +
          "    staff1@" + domain + " to staff7@" + domain + "\n\n" +
          "They all share the same password. An address that already exists\n" +
          "has its password reset instead.\n\n" +
          "Continue?"
     );

     if (!answer) return;

     const $btn = $("#btnStaffEmails");
     const originalHtml = $btn.html();
     $btn.prop("disabled", true).html('<i class="bi bi-hourglass-split"></i> Creating');

     const $steps = $("#wpSetupStepsList");
     $steps.empty().append(
          '<li class="save-step is-running"><span class="save-step-icon"></span><span class="save-step-label">Creating the staff mailboxes</span></li>'
          );
     $("#wpSetupSteps").show();

     $.ajax({
          url: "assets/php/actionWebsiteList.php",
          method: "POST",
          cache: false,
          dataType: "json",
          data: {
               act: "staffEmails",
               id: wID
          }
     }).done(function(res) {
          console.log(res);
          renderMailboxResults(res);

          if (!res.success) {
               alert("Not every mailbox was created.\n\n" + (res.message || "Unknown error."));
          }
     }).fail(function(xhr, status, error) {
          console.log("ajax staffEmails fail!!");
          console.log(status + ": " + error);
          $steps.empty().append(
               '<li class="save-step is-failed"><span class="save-step-icon"></span><span class="save-step-label">' +
               escapeHtml(status + ": " + error) + '</span></li>');
          alert("The mailboxes could not be created.\n\n" + status + ": " + error);
     }).always(function() {
          $btn.prop("disabled", false).html(originalHtml);
     });
} //staffEmails

// One line per mailbox, in the same tray the setup steps use
const renderMailboxResults = (res) => {
     const $steps = $("#wpSetupStepsList");
     $steps.empty();

     const results = Array.isArray(res.results) ? res.results : [];

     if (results.length === 0) {
          $steps.append(
               '<li class="save-step is-failed"><span class="save-step-icon"></span><span class="save-step-label">' +
               escapeHtml(res.message || "No mailboxes were reported.") + '</span></li>');
          return;
     }

     results.forEach(function(box) {
          const cls = box.success ? "is-done" : "is-failed";
          const detail = box.message ? " &mdash; " + escapeHtml(box.message) : "";
          $steps.append('<li class="save-step ' + cls +
               '"><span class="save-step-icon"></span><span class="save-step-label">' + escapeHtml(
                    box.email) + detail + '</span></li>');
     });
} //renderMailboxResults

// Ask cPanel what a mail client needs for one mailbox and show it, so the
// details can be passed on without opening cPanel.
const emailSettings = (fieldId, el) => {
     const wID = editID.val();
     const email = $("#" + fieldId).val().trim();

     if (!wID) {
          alert("Save the website first, then look up its mailboxes.");
          return;
     }

     if (email === "") {
          alert("Fill in the mailbox address first.");
          return;
     }

     // The password sits in the field next to the address, so it can be
     // shown alongside the server settings instead of being described
     const password = $("#" + fieldId.replace(/User$/, "Pass")).val().trim();

     const $icon = $(el).find('i');
     const originalIcon = $icon.attr('class');
     $icon.attr('class', 'bi bi-hourglass-split');

     $.ajax({
          url: "assets/php/actionWebsiteList.php",
          method: "POST",
          cache: false,
          dataType: "json",
          data: {
               act: "emailSettings",
               id: wID,
               email: email
          }
     }).done(function(res) {
          // Logged in full: cPanel's answer varies between versions, and
          // this is what tells us which shape a server actually sends
          console.log("emailSettings", res);

          if (!res.success) {
               alert("Could not read the mail settings.\n\n" + (res.message || "Unknown error."));
               return;
          }

          showMailSettings(email, password, res.settings);
     }).fail(function(xhr, status, error) {
          console.log("ajax emailSettings fail!!");
          console.log(status + ": " + error);
          alert("Could not read the mail settings.\n\n" + status + ": " + error);
     }).always(function() {
          $icon.attr('class', originalIcon);
     });
} //emailSettings

// Pull the secure server and port out of whatever cPanel sent.
//
// The answer is flat, and the encrypted port is the plain one: an
// unencrypted alternative is offered separately under a matching
// *_insecure_port key, which is exactly what is not wanted here.
// Servers may still nest their answer, so it is searched rather than
// read from a fixed path.
const mailSettingsBlocks = (settings) => {
     const found = {};

     const remember = (proto, field, value) => {
          if (value === undefined || value === null || value === "") return;
          found[proto] = found[proto] || {};
          if (!found[proto][field]) {
               found[proto][field] = value;
          }
     };

     const walk = (node) => {
          if (!node || typeof node !== "object") return;

          Object.keys(node).forEach(function(key) {
               const value = node[key];

               if (value && typeof value === "object") {
                    walk(value);
                    return;
               }

               const lower = key.toLowerCase();

               // Anything named insecure is the fallback, not the answer
               if (lower.indexOf("insecure") !== -1) return;

               // Outgoing: smtp_host / smtp_port
               let match = lower.match(/^smtp_(host|server|port)$/);
               if (match) {
                    remember("smtp", match[1] === "port" ? "port" : "host", value);
                    return;
               }

               // Incoming: cPanel calls it inbox_*, and inbox_service says
               // whether the mailbox is served over IMAP or POP3
               match = lower.match(/^(?:inbox|imap|pop3)_(host|server|port)$/);
               if (match) {
                    remember("inbox", match[1] === "port" ? "port" : "host", value);
                    return;
               }

               if (lower === "inbox_service") {
                    remember("inbox", "service", String(value).toUpperCase());
               }

               // mail_domain is deliberately ignored: on these servers it
               // comes back as mail.<the server's hostname> rather than
               // mail.<the shop's domain>, so it reads no better than
               // smtp_host and its certificate is no more likely to match.
          });
     };

     walk(settings);

     const blocks = [];

     if (found.inbox && found.inbox.host) {
          blocks.push({
               label: "Incoming",
               protocol: found.inbox.service || "IMAP",
               host: found.inbox.host,
               port: found.inbox.port || ""
          });
     }

     if (found.smtp && found.smtp.host) {
          blocks.push({
               label: "Outgoing",
               protocol: "SMTP",
               host: found.smtp.host,
               port: found.smtp.port || ""
          });
     }

     return blocks;
} //mailSettingsBlocks

// The text put on the clipboard by "Copy all", kept aside so the button
// does not have to read it back out of the table
let mailSettingsText = "";

// Bootstrap 4 comes in with AdminLTE and Bootstrap 5 is loaded on top of
// it, so neither one can be counted on. Whichever answers is used.
const mailSettingsModal = (action) => {
     const el = document.getElementById("mailSettingsModal");

     if (window.bootstrap && window.bootstrap.Modal) {
          const modal = window.bootstrap.Modal.getOrCreateInstance(el);
          action === "hide" ? modal.hide() : modal.show();
          return;
     }

     // Bootstrap 4's jQuery plugin
     $(el).modal(action === "hide" ? "hide" : "show");
} //mailSettingsModal

const hideMailSettings = () => {
     mailSettingsModal("hide");
} //hideMailSettings

// Show the four values a mail client needs, each on its own copy button,
// laid out the way cPanel's own panel shows them.
const showMailSettings = (email, password, settings) => {
     const blocks = mailSettingsBlocks(settings);
     const $body = $("#mailSettingsBody");

     if (blocks.length === 0) {
          // Nothing recognisable came back, so the answer itself is shown
          // rather than a message that says nothing
          console.log("Unrecognised mail settings:", settings);
          mailSettingsText = JSON.stringify(settings, null, 2);
          $body.html(
               '<p class="mb-2">The mail settings came back in a shape this page does not recognise.</p>' +
               '<pre class="small bg-light border rounded p-2 mb-0" style="max-height:320px;overflow:auto;"></pre>'
          );
          $body.find("pre").text(mailSettingsText);
          mailSettingsModal("show");
          return;
     }

     // Only the outgoing server is passed on: it is the one a shop has to
     // enter by hand when it sends through the site's own mailbox
     const smtp = blocks.filter(function(b) {
          return b.protocol === "SMTP";
     })[0];

     if (!smtp) {
          console.log("No SMTP settings in:", settings);
          mailSettingsText = JSON.stringify(settings, null, 2);
          $body.html(
               '<p class="mb-2">cPanel returned mail settings, but none for the outgoing server.</p>' +
               '<pre class="small bg-light border rounded p-2 mb-0" style="max-height:320px;overflow:auto;"></pre>'
          );
          $body.find("pre").text(mailSettingsText);
          mailSettingsModal("show");
          return;
     }

     // The password comes from the form rather than cPanel, which never
     // gives one back. Without it the row says where to find it instead.
     const passCell = password !== "" ?
          copyCell(password) :
          '<em class="text-muted">Use the email account&rsquo;s password.</em>';

     const row = (label, value) =>
          '<tr>' +
          '<th class="text-muted font-weight-normal align-middle" style="width:9rem;">' + label + '</th>' +
          '<td class="align-middle">' + value + '</td>' +
          '</tr>';

     $body.html(
          '<div class="card mb-0">' +
          '<div class="card-header bg-primary text-white py-2">' +
          '<i class="bi bi-lock-fill"></i> Secure SSL/TLS Settings <span class="small">(Recommended)</span>' +
          '</div>' +
          '<div class="table-responsive">' +
          '<table class="table table-sm mb-0">' +
          '<tbody>' +
          row("Username:", copyCell(email)) +
          row("Password:", passCell) +
          row("Outgoing Server:", copyCell(smtp.host)) +
          row("SMTP Port:", copyCell(smtp.port)) +
          '</tbody>' +
          '</table>' +
          '</div>' +
          '<div class="card-footer small text-muted py-2">SMTP requires authentication.</div>' +
          '</div>'
     );

     // The same four values as plain text, for pasting into a message
     mailSettingsText = "Username: " + email + "\n" +
          "Password: " + (password !== "" ? password : "Use the email account's password.") + "\n" +
          "Outgoing Server: " + smtp.host + "\n" +
          "SMTP Port: " + smtp.port;

     mailSettingsModal("show");
} //showMailSettings

// A value with its own copy link, so one field can be taken at a time.
// The value rides on a data attribute rather than inside an onclick, so
// quotes in it cannot break the markup.
const copyCell = (value) => {
     const safe = escapeHtml(value);
     // escapeHtml leaves quotes alone, which is fine for text but would
     // end the attribute early
     const attr = safe.replace(/"/g, "&quot;");

     // The icon and its value belong together, so the pair is kept from
     // breaking across lines while a long address may still wrap on its own
     return '<span class="d-inline-flex align-items-center">' +
          '<a href="#" class="copyMailValue mr-1 flex-shrink-0" data-value="' + attr + '" title="Copy">' +
          iconCopy +
          '</a>' +
          '<span class="text-break">' + safe + '</span>' +
          '</span>';
} //copyCell

$(document).on("click", ".copyMailValue", function(e) {
     e.preventDefault();
     copyText($(this).data("value"));
});

// Everything at once, for pasting into a message
const copyMailSettings = () => {
     copyText(mailSettingsText);
} //copyMailSettings

// Show what the helper reported, one line per step
const renderWpSetupSteps = (res) => {
     const $steps = $("#wpSetupStepsList");
     $steps.empty();

     const steps = Array.isArray(res.steps) ? res.steps : [];

     if (steps.length === 0) {
          $steps.append(
               '<li class="save-step is-failed"><span class="save-step-icon"></span><span class="save-step-label">' +
               escapeHtml(res.message || "No steps were reported.") + '</span></li>');
          return;
     }

     steps.forEach(function(step) {
          const cls = step.success ? "is-done" : "is-failed";
          const detail = step.detail ? " &mdash; " + escapeHtml(step.detail) : "";
          $steps.append('<li class="save-step ' + cls +
               '"><span class="save-step-icon"></span><span class="save-step-label">' + escapeHtml(
                    step.step) + detail + '</span></li>');
     });
} //renderWpSetupSteps

// The helper reports back whatever WordPress said, so it is escaped here
const escapeHtml = (value) => {
     return $("<div>").text(value === undefined || value === null ? "" : value).html();
} //escapeHtml


// Open a tab straight away (keeps popup blockers quiet) and paint a
// small "please wait" page in it until the real URL is known
const openLoadingTab = (label) => {
    const tab = window.open('', '_blank');
    if (!tab) return null;
    try {
        const safe = $("<div>").text(label).html();
        tab.document.write(
            '<!doctype html><html><head><meta charset="utf-8"><title>Opening ' + safe + '...</title>' +
            '<style>body{margin:0;height:100vh;display:flex;align-items:center;justify-content:center;' +
            'font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:#333;background:#f8f9fa}' +
            '.box{text-align:center}.spin{width:36px;height:36px;border:4px solid #dee2e6;border-top-color:#0d6efd;' +
            'border-radius:50%;margin:0 auto 14px;animation:s .8s linear infinite}@keyframes s{to{transform:rotate(360deg)}}' +
            'p{margin:0;font-size:15px}small{color:#888}</style></head><body><div class="box"><div class="spin"></div>' +
            '<p>Opening ' + safe + '...</p><small>Requesting a login session, please wait</small></div></body></html>'
        );
        tab.document.close();
    } catch (e) {
        console.log("loading page could not be written", e);
    }
    return tab;
}//openLoadingTab

// Ask WHM for a one-time cPanel session URL and open it, either on the
// account home or straight on phpMyAdmin.
// The tab is opened up front so the browser does not treat it as a popup,
// then redirected once WHM answers.
const openCpanel = (wID, el, target) => {
     const label = target === "phpmyadmin" ? "phpMyAdmin" : "cPanel";
     const tab = openLoadingTab(label);
     const $icon = $(el).find('i');
     const originalIcon = $icon.attr('class');
     $icon.attr('class', 'bi bi-hourglass-split text-primary');

     $.ajax({
          url: "assets/php/actionWebsiteList.php",
          method: "POST",
          cache: false,
          dataType: "json",
          data: {
               act: "cpanelLogin",
               id: wID,
               target: target || "cpanel"
          }
     }).done(function(res) {
          console.log(res);
          if (res.success && res.url) {
               if (tab) {
                    tab.location.href = res.url;
               } else {
                    window.open(res.url, '_blank');
               }
          } else {
               if (tab) tab.close();
               alert("Could not open " + label + ".\n\n" + (res.message || "Unknown error."));
          }
     }).fail(function(xhr, status, error) {
          if (tab) tab.close();
          console.log("ajax cpanelLogin fail!!");
          console.log(status + ": " + error);
          alert("Could not open " + label + ".\n\n" + status + ": " + error);
     }).always(function() {
          $icon.attr('class', originalIcon);
     });

     return false;
} //openCpanel

// Ask masterPanel for a one-time wp-admin login link and open it.
// Same tab-first trick as openCpanel so popup blockers stay quiet.
const openWpAdmin = (wID, el) => {
     const tab = openLoadingTab("wp-admin");
     const $icon = $(el).find('i');
     const originalIcon = $icon.attr('class');
     $icon.attr('class', 'bi bi-hourglass-split text-primary');

     $.ajax({
          url: "assets/php/actionWebsiteList.php",
          method: "POST",
          cache: false,
          dataType: "json",
          data: {
               act: "wpLogin",
               id: wID
          }
     }).done(function(res) {
          console.log(res);
          if (res.success && res.url) {
               if (tab) {
                    tab.location.href = res.url;
               } else {
                    window.open(res.url, '_blank');
               }
          } else {
               if (tab) tab.close();
               alert("Could not open wp-admin.\n\n" + (res.message || "Unknown error."));
          }
     }).fail(function(xhr, status, error) {
          if (tab) tab.close();
          console.log("ajax wpLogin fail!!");
          console.log(status + ": " + error);
          alert("Could not open wp-admin.\n\n" + status + ": " + error);
     }).always(function() {
          $icon.attr('class', originalIcon);
     });

     return false;
} //openWpAdmin

const setDel = (delID) => {

     let answer = confirm("Are you sure to delete this Staff?");

     console.log(answer);
     if (answer === true) {
          let payload = {
               act: "setDelete",
               id: delID
          };
          console.log("payload=", payload);

          const reqAjax = $.ajax({
               url: "assets/php/actionWebsiteList.php",
               method: "POST",
               async: false,
               cache: false,
               dataType: "json",
               data: payload
          });

          reqAjax.done(function(res) {
               modalFormAction("close");
               console.log(res);
               resetForm();
               $("#formModal").modal('hide');
          });

          reqAjax.fail(function(xhr, status, error) {
               console.log("ajax setDel fail!!");
               console.log(status + ": " + error);
          });

     } //if
} //setDel

function reloadTable_bs5() {
     $('#websiteListTable').DataTable().ajax.reload();
}

function newModalFormAction(action) {
     console.log("goNew = " + action);
     if (action === "open") {
          $("#saveProgress").hide();
          cmdSubmit.prop("disabled", false);
          // Every route into the form passes through here, so this is the
          // one place the generators have to be settled
          toggleGeneratePassword();
          newModalForm.show();
     } else {
          newModalForm.hide();
          $(".modal-backdrop").hide();
     }
}

function filterChange() {
     reloadTable_bs5();
}

function filterAll() {
     filterShopType.val('');
     filterSystem.val('');
     filterStatus.val('');
     filterTemplate.val('');
     filterCountry.val('');
     filterServer.val('');
     filterProjectLink.val('');
     reloadTable_bs5();
}

$(() => {
     $("#alert").hide();

     $('#websiteListTable').DataTable({
          pagingType: 'full_numbers',
          pageLength: 14,
          lengthMenu: [
               [14, 25, 50, -1],
               ['Fit', 25, 50, 'All']
          ],
          ajax: {
               url: 'pages/tableRendering/dataWebsiteList.php',
               type: 'POST',
               dataSrc: 'data',
               data: function(d) {
                    d.shopType = $("#filterShopType").val();
                    d.system = $("#filterSystem").val();
                    d.liveStatus = $("#filterStatus").val();
                    d.template = $("#filterTemplate").val();
                    d.country = $("#filterCountry").val();
                    d.server = $("#filterServer").val();
                    d.projectLink = $("#filterProjectLink").val();
               }
          },
          columnDefs: [{
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