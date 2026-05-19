<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-LGKDYHL23T');
</script>
<?php
global $db;

// Auto-import from websiteList on page load
$toImport = $db->query(
    "SELECT wID, wProject, wDomain FROM websiteList
     WHERE delete_at IS NULL AND wLiveStatus = 'Live'
       AND wID NOT IN (SELECT source_wID FROM monitors WHERE source_wID IS NOT NULL)"
)->fetchAll();

foreach ($toImport as $w) {
    $url = trim($w['wDomain']);
    if (empty($url)) continue;
    if (!preg_match('#^https?://#i', $url)) $url = 'https://' . $url;
    if (!filter_var($url, FILTER_VALIDATE_URL)) continue;
    $db->query(
        "INSERT INTO monitors (name, url, category, source_wID, check_interval) VALUES (?, ?, 'client', ?, 5)",
        $w['wProject'], $url, $w['wID']
    );
}
?>
<style>
    .filterCol { width: 30%; max-width: 280px; }
    .filterLabel { width: 100px; }
    .filterSelect { width: 100% !important; }
    div.dataTables_wrapper div.dataTables_length select { width: 100%; }
</style>
<link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/css/bootstrap.css">
<link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="plugins/datatables-bs5/css/datatables-bs5.min.css">

<!-- Page Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <i class="bi bi-activity me-2"></i>Domain Monitor
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php">Home</a></li>
                    <li class="breadcrumb-item active">Monitor</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">

        <!-- Stats Cards -->
        <div class="row mb-4" id="statsCards">
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><div class="text-muted small">Up</div><div class="fs-4 fw-bold text-success" id="statUp">-</div></div>
                            <i class="bi bi-check-circle-fill text-success fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-danger">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><div class="text-muted small">Down</div><div class="fs-4 fw-bold text-danger" id="statDown">-</div></div>
                            <i class="bi bi-x-circle-fill text-danger fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><div class="text-muted small">SSL ≤ 30d</div><div class="fs-4 fw-bold text-warning" id="statSsl">-</div></div>
                            <i class="bi bi-shield-exclamation text-warning fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-secondary">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><div class="text-muted small">Total</div><div class="fs-4 fw-bold" id="statTotal">-</div></div>
                            <i class="bi bi-globe fs-3 text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters + Toolbar -->
        <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="mb-0"><i class="bi bi-funnel me-1"></i>Filters
                    <button class="btn btn-sm btn-outline-secondary px-2 pt-0 pb-1 ms-1" onclick="filterAll()">
                        <small><i class="bi bi-x-lg"></i> Clear</small>
                    </button>
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-info" onclick="syncWebsiteList()">
                        <i class="bi bi-arrow-repeat"></i> Sync Website List
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="openFormModal()">
                        <i class="bi bi-plus"></i> Add Monitor
                    </button>
                </div>
            </div>
            <div class="d-flex flex-row gap-4">
                <div class="filterCol">
                    <label class="form-label filterLabel">Category</label>
                    <select class="form-select filterSelect" id="filterCategory" onchange="reloadTable()">
                        <option value="">All</option>
                        <option value="client">Client</option>
                        <option value="competitor">Competitor</option>
                        <option value="third_party">Third Party</option>
                        <option value="payment_gateway">Payment Gateway</option>
                        <option value="api_endpoint">API Endpoint</option>
                        <option value="supplier">Supplier</option>
                    </select>
                </div>
                <div class="filterCol">
                    <label class="form-label filterLabel">Status</label>
                    <select class="form-select filterSelect" id="filterStatus" onchange="reloadTable()">
                        <option value="">All</option>
                        <option value="up">Up</option>
                        <option value="down">Down</option>
                        <option value="unknown">Unknown</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <div class="card p-3">
            <div class="card-body">
                <table id="monitorTable" class="table table-borderless table-striped table-hover" style="width:100%">
                    <thead class="thead-dark">
                        <tr>
                            <th width="4%">#</th>
                            <th>Name</th>
                            <th>URL</th>
                            <th width="12%">Category</th>
                            <th width="8%">Interval</th>
                            <th width="8%">Status</th>
                            <th width="9%">SSL</th>
                            <th width="14%">Last Check</th>
                            <th width="10%"></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <!-- ── FORM MODAL (Add/Edit) ── -->
        <div class="modal fade" id="formModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-file-earmark-plus"></i> <span id="formModalTitle">Add Monitor</span></h5>
                        <button type="button" class="close" onclick="closeFormModal()"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="container py-2">
                            <div class="form-row">
                                <div class="form-group col-md-8">
                                    <label>Name</label>
                                    <input type="text" class="form-control" id="inputName" placeholder="e.g. True Thai Cairns">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Category</label>
                                    <select class="form-select" id="inputCategory">
                                        <option value="client">Client</option>
                                        <option value="competitor">Competitor</option>
                                        <option value="third_party">Third Party</option>
                                        <option value="payment_gateway">Payment Gateway</option>
                                        <option value="api_endpoint">API Endpoint</option>
                                        <option value="supplier">Supplier</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>URL</label>
                                <input type="text" class="form-control" id="inputUrl" placeholder="https://www.example.com">
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Check Interval (minutes)</label>
                                    <input type="number" class="form-control" id="inputInterval" value="5" min="1" max="1440">
                                </div>
                                <div class="form-group col-md-4 d-flex align-items-end">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="inputActive" checked>
                                        <label class="form-check-label" for="inputActive">Active</label>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h6>Notifications</h6>
                            <div class="form-group">
                                <label>Email (comma-separated)</label>
                                <input type="text" class="form-control" id="inputEmail" placeholder="alert@company.com, dev@company.com">
                            </div>
                            <div class="form-group">
                                <label>Line Notify Token</label>
                                <input type="text" class="form-control" id="inputLine" placeholder="Line Notify token">
                            </div>
                            <div class="form-group">
                                <label>Webhook URL</label>
                                <input type="text" class="form-control" id="inputWebhook" placeholder="https://hooks.example.com/...">
                            </div>
                            <input type="hidden" id="editID" value="">
                            <input type="hidden" id="formAction" value="add">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" onclick="closeFormModal()">Close</button>
                        <button class="btn btn-primary" onclick="formSave()">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── LOG MODAL ── -->
        <div class="modal fade" id="logModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-journal-text"></i> Check Logs</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-sm table-striped" id="logTable">
                            <thead>
                                <tr>
                                    <th>Checked At</th><th>Status</th><th>HTTP</th>
                                    <th>Response</th><th>SSL Days</th><th>Type</th><th>Error</th>
                                </tr>
                            </thead>
                            <tbody id="logTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── DOWNTIME MODAL ── -->
        <div class="modal fade" id="downtimeModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-graph-down"></i> Downtime Summary (Last 30 Days)</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <strong>Uptime:</strong> <span id="uptimePct" class="fs-5 fw-bold text-success">-</span>
                        </div>
                        <table class="table table-sm table-bordered" id="downtimeTable">
                            <thead>
                                <tr><th>Start</th><th>End</th><th>Duration (min)</th></tr>
                            </thead>
                            <tbody id="downtimeTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.container-fluid -->
</div><!-- /.content -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.js"></script>
<script src="plugins/datatables-bs5/js/datatables-bs5.min.js"></script>

<script>
const formModal     = new bootstrap.Modal(document.getElementById('formModal'), {});
const logModal      = new bootstrap.Modal(document.getElementById('logModal'), {});
const downtimeModal = new bootstrap.Modal(document.getElementById('downtimeModal'), {});

// ── Stats ──────────────────────────────────────────────
function loadStats() {
    $.post('assets/php/actionMonitor.php', { act: 'getStats' }, function(res) {
        $('#statUp').text(res.cnt_up ?? 0);
        $('#statDown').text(res.cnt_down ?? 0);
        $('#statSsl').text(res.cnt_ssl_warn ?? 0);
        $('#statTotal').text(res.cnt_total ?? 0);
    }, 'json');
}

// ── Table ──────────────────────────────────────────────
function reloadTable() {
    $('#monitorTable').DataTable().ajax.reload();
    loadStats();
}

function filterAll() {
    $('#filterCategory').val('');
    $('#filterStatus').val('');
    reloadTable();
}

// ── Form Modal ─────────────────────────────────────────
function openFormModal() {
    resetForm();
    $('#formModalTitle').text('Add Monitor');
    formModal.show();
}

function closeFormModal() {
    formModal.hide();
    $('.modal-backdrop').hide();
}

function resetForm() {
    $('#inputName').val('');
    $('#inputUrl').val('');
    $('#inputCategory').val('client');
    $('#inputInterval').val(5);
    $('#inputActive').prop('checked', true);
    $('#inputEmail').val('');
    $('#inputLine').val('');
    $('#inputWebhook').val('');
    $('#editID').val('');
    $('#formAction').val('add');
}

function formSave() {
    $.post('assets/php/actionMonitor.php', {
        act:           'save',
        formAction:    $('#formAction').val(),
        inputName:     $('#inputName').val(),
        inputUrl:      $('#inputUrl').val(),
        inputCategory: $('#inputCategory').val(),
        inputInterval: $('#inputInterval').val(),
        inputActive:   $('#inputActive').prop('checked') ? 1 : 0,
        inputEmail:    $('#inputEmail').val(),
        inputLine:     $('#inputLine').val(),
        inputWebhook:  $('#inputWebhook').val(),
        editID:        $('#editID').val(),
    }, function(res) {
        closeFormModal();
        reloadTable();
    }, 'json');
}

function setEdit(id) {
    $.post('assets/php/actionMonitor.php', { act: 'loadUpdate', id: id }, function(res) {
        $('#inputName').val(res.name);
        $('#inputUrl').val(res.url);
        $('#inputCategory').val(res.category);
        $('#inputInterval').val(res.check_interval);
        $('#inputActive').prop('checked', res.is_active == 1);
        $('#inputEmail').val(res.notify_email);
        $('#inputLine').val(res.notify_line);
        $('#inputWebhook').val(res.notify_webhook);
        $('#editID').val(res.id);
        $('#formAction').val('edit');
        $('#formModalTitle').text('Edit Monitor');
        formModal.show();
    }, 'json');
}

function setDel(id) {
    if (!confirm('Delete this monitor?')) return;
    $.post('assets/php/actionMonitor.php', { act: 'setDelete', id: id }, function() {
        reloadTable();
    }, 'json');
}

// ── Manual Check ───────────────────────────────────────
function manualCheck(id) {
    $.post('assets/php/actionMonitor.php', { act: 'manualCheck', id: id }, function(res) {
        reloadTable();
        alert('Check done: ' + res.status + ' (HTTP ' + (res.httpCode || '-') + ', ' + (res.responseMs || '-') + 'ms)');
    }, 'json');
}

// ── Logs Modal ─────────────────────────────────────────
function viewLogs(id) {
    $.post('assets/php/actionMonitor.php', { act: 'getLogs', id: id }, function(res) {
        const rows = res.data.map(r => `
            <tr>
                <td>${r.checked_at}</td>
                <td><span class="badge bg-${r.status === 'up' ? 'success' : 'danger'}">${r.status}</span></td>
                <td>${r.http_code ?? '-'}</td>
                <td>${r.response_ms != null ? r.response_ms + 'ms' : '-'}</td>
                <td>${r.ssl_days_left ?? '-'}</td>
                <td>${r.check_type}</td>
                <td><small>${r.error_msg ?? ''}</small></td>
            </tr>`).join('');
        $('#logTableBody').html(rows || '<tr><td colspan="7" class="text-center">No logs</td></tr>');
        logModal.show();
    }, 'json');
}

// ── Downtime Modal ─────────────────────────────────────
function viewDowntime(id) {
    $.post('assets/php/actionMonitor.php', { act: 'getDowntime', id: id }, function(res) {
        $('#uptimePct').text(res.uptime_pct !== null ? res.uptime_pct + '%' : 'No data');
        const rows = (res.incidents || []).map(inc => `
            <tr>
                <td>${inc.start}</td>
                <td>${inc.end ?? '<span class="text-danger">Still down</span>'}</td>
                <td>${inc.duration_min}</td>
            </tr>`).join('');
        $('#downtimeTableBody').html(rows || '<tr><td colspan="3" class="text-center">No downtime incidents</td></tr>');
        downtimeModal.show();
    }, 'json');
}

// ── Sync Website List ──────────────────────────────────
function syncWebsiteList() {
    $.post('assets/php/actionMonitor.php', { act: 'syncWebsiteList' }, function(res) {
        alert('Synced: ' + res.inserted + ' new monitors added.');
        reloadTable();
    }, 'json');
}

// ── Init ───────────────────────────────────────────────
$(() => {
    loadStats();

    $('#monitorTable').DataTable({
        pagingType: 'full_numbers',
        pageLength: 14,
        lengthMenu: [[14, 25, 50, -1], ['Fit', 25, 50, 'All']],
        ajax: {
            url:     'pages/tableRendering/datamonitor.php',
            type:    'POST',
            dataSrc: 'data',
            data: function(d) {
                d.category = $('#filterCategory').val();
                d.status   = $('#filterStatus').val();
            }
        },
        columnDefs: [
            { targets: -1, className: 'dt-body-right', orderable: false }
        ],
    });
});
</script>
