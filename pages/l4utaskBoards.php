<?php
global $db, $myID;
$myID = $_SESSION['id'] ?? ($_COOKIE['id'] ?? 0);
?>
<!-- Content Header -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="mb-0"><i class="bi bi-kanban mr-2"></i>L4U Task Boards</h1>
            </div>
            <div class="col-sm-6 text-right">
                <button class="btn btn-outline-success btn-sm mr-1" onclick="openImportBoard()">
                    <i class="bi bi-upload mr-1"></i> Import CSV
                </button>
                <button class="btn btn-primary" onclick="openCreateBoard()">
                    <i class="bi bi-plus-lg mr-1"></i> New Board
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row" id="boardContainer"></div>
        <div class="text-center py-5" id="boardLoading">
            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
        </div>
        <div class="text-center py-5 d-none" id="boardEmpty">
            <i class="bi bi-kanban text-muted" style="font-size:48px;"></i>
            <p class="text-muted mt-3">No boards yet. Create your first board!</p>
        </div>
    </div>
</section>

<!-- ============================================================
     Modal: Create / Edit Board
     ============================================================ -->
<div class="modal fade" id="modalBoard" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header py-2" id="modalBoardHeader" style="background:#0079BF;">
                <h5 class="modal-title text-white" id="modalBoardTitle"><i class="bi bi-kanban mr-1"></i> New Board</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editBoardID" value="">
                <div class="row">
                    <!-- LEFT COL -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Board Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="inputBoardName" placeholder="e.g. Sprint 2026-Q1" maxlength="255">
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Description</label>
                            <textarea class="form-control" id="inputBoardDesc" rows="2" placeholder="What is this board about?"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Board Color</label>
                            <div class="d-flex flex-wrap mb-2" id="colorPicker">
                                <span class="board-color-opt" data-color="#0079BF" style="background:#0079BF;" onclick="pickColor(this)"></span>
                                <span class="board-color-opt" data-color="#D29034" style="background:#D29034;" onclick="pickColor(this)"></span>
                                <span class="board-color-opt" data-color="#519839" style="background:#519839;" onclick="pickColor(this)"></span>
                                <span class="board-color-opt" data-color="#B04632" style="background:#B04632;" onclick="pickColor(this)"></span>
                                <span class="board-color-opt" data-color="#89609E" style="background:#89609E;" onclick="pickColor(this)"></span>
                                <span class="board-color-opt" data-color="#CD5A91" style="background:#CD5A91;" onclick="pickColor(this)"></span>
                                <span class="board-color-opt" data-color="#4BBF6B" style="background:#4BBF6B;" onclick="pickColor(this)"></span>
                                <span class="board-color-opt" data-color="#00AECC" style="background:#00AECC;" onclick="pickColor(this)"></span>
                                <span class="board-color-opt" data-color="#344563" style="background:#344563;" onclick="pickColor(this)"></span>
                                <span class="board-color-opt" data-color="#F5DD29" style="background:#F5DD29;" onclick="pickColor(this)"></span>
                                <span class="board-color-opt" data-color="#FF6F61" style="background:#FF6F61;" onclick="pickColor(this)"></span>
                                <span class="board-color-opt" data-color="#1B2838" style="background:#1B2838;" onclick="pickColor(this)"></span>
                            </div>
                            <div class="d-flex align-items-center">
                                <input type="color" id="customColorPicker" value="#0079BF" class="border-0 p-0" style="width:36px;height:32px;cursor:pointer;" oninput="pickCustomColor(this.value)">
                                <input type="text" class="form-control form-control-sm ml-2" id="customColorHex" value="#0079BF" style="width:90px;font-size:12px;" maxlength="7" onchange="pickCustomColor(this.value)">
                                <div class="ml-2 rounded" id="colorPreview" style="width:80px;height:32px;background:#0079BF;border:1px solid #ddd;"></div>
                            </div>
                            <input type="hidden" id="inputBoardColor" value="#0079BF">
                        </div>
                    </div>
                    <!-- RIGHT COL: Team Permission -->
                    <div class="col-md-6">
                        <label class="font-weight-bold"><i class="bi bi-people mr-1"></i> Team Members</label>
                        <div class="mb-2 d-flex align-items-center">
                            <select class="form-control form-control-sm mr-1" id="addMemberRole" style="width:100px;">
                                <option value="2">Member</option>
                                <option value="1">Admin</option>
                            </select>
                            <span class="text-muted" style="font-size:11px;">Role for added members</span>
                        </div>
                        <div id="teamPickerContainer" style="max-height:280px;overflow-y:auto;border:1px solid #dee2e6;border-radius:6px;padding:6px;">
                            <div class="text-center text-muted py-2" style="font-size:12px;"><i class="fas fa-spinner fa-spin mr-1"></i> Loading teams...</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveBoard()">
                    <i class="bi bi-check-lg mr-1"></i> Save Board
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     Modal: Import CSV
     ============================================================ -->
<div class="modal fade" id="modalImport" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header py-2 bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-upload mr-1"></i> Import Board</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Board Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="importBoardName" placeholder="e.g. Website Tasks" maxlength="255">
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Description</label>
                            <textarea class="form-control" id="importBoardDesc" rows="2" placeholder="Imported from CSV"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Board Color</label>
                            <div class="d-flex align-items-center">
                                <input type="color" id="importColorPicker" value="#519839" class="border-0 p-0" style="width:36px;height:32px;cursor:pointer;">
                                <input type="text" class="form-control form-control-sm ml-2" id="importColorHex" value="#519839" style="width:90px;font-size:12px;" maxlength="7">
                                <div class="ml-2 rounded" id="importColorPreview" style="width:60px;height:32px;background:#519839;border:1px solid #ddd;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Upload CSV/Excel File <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="importFileInput" accept=".csv,.xlsx,.xls">
                                <label class="custom-file-label" for="importFileInput" id="importFileLabel">Choose file...</label>
                            </div>
                            <small class="text-muted mt-1 d-block">Supports .csv and .xlsx files exported from Monday.com, Trello, or similar tools</small>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold"><i class="bi bi-people mr-1"></i> Assign Team</label>
                            <div id="importTeamPicker" style="max-height:160px;overflow-y:auto;border:1px solid #dee2e6;border-radius:6px;padding:6px;">
                                <div class="text-center text-muted py-2" style="font-size:12px;"><i class="fas fa-spinner fa-spin mr-1"></i> Loading...</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="importProgress" class="d-none">
                    <div class="text-center py-3">
                        <i class="fas fa-spinner fa-spin fa-2x text-success"></i>
                        <p class="mt-2 mb-0 text-muted">Importing data...</p>
                    </div>
                </div>
                <div id="importResult" class="d-none">
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle mr-1"></i> <span id="importResultText"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" id="btnDoImport" onclick="doImport()">
                    <i class="bi bi-upload mr-1"></i> Import
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .board-card {
        border-radius: 8px;
        color: #fff;
        min-height: 130px;
        cursor: pointer;
        transition: transform 0.15s, box-shadow 0.15s;
        position: relative;
        overflow: hidden;
    }
    .board-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    }
    .board-card .board-title { font-weight: 700; font-size: 16px; word-break: break-word; }
    .board-card .board-meta { font-size: 12px; opacity: 0.85; }
    .board-card .board-actions {
        position: absolute; top: 8px; right: 8px;
        opacity: 0; transition: opacity 0.15s;
    }
    .board-card:hover .board-actions { opacity: 1; }
    .board-card .board-actions .btn { color: #fff; padding: 2px 6px; font-size: 13px; }
    .board-color-opt {
        display: inline-block; width: 28px; height: 28px;
        border-radius: 4px; cursor: pointer;
        border: 3px solid transparent; margin: 2px;
        transition: border-color 0.15s;
    }
    .board-color-opt.active, .board-color-opt:hover { border-color: #333; }
    .team-group { margin-bottom: 4px; }
    .team-group-header {
        font-size: 12px; font-weight: 700; color: #5e6c84;
        cursor: pointer; padding: 4px 6px; border-radius: 4px;
        transition: background 0.12s; user-select: none;
    }
    .team-group-header:hover { background: #e9ecef; }
    .team-member-item {
        font-size: 12px; padding: 3px 6px 3px 20px;
        display: flex; align-items: center;
    }
    .team-member-item img { width: 20px; height: 20px; object-fit: cover; }
</style>

<script>
var MY_ID = <?= intval($myID) ?>;
var teamsData = [];

function escHtml(s){
    if(!s) return '';
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

/* ---- Init after jQuery + Bootstrap are loaded ---- */
window.addEventListener('load', function(){
    loadBoards();
    loadTeams();

    $('#importColorPicker').on('input', function(){ syncImportColor(this.value); });
    $('#importColorHex').on('change', function(){
        if(/^#[0-9A-Fa-f]{6}$/.test(this.value)) syncImportColor(this.value);
    });
    $('#importFileInput').on('change', function(){
        $('#importFileLabel').text(this.files[0] ? this.files[0].name : 'Choose file...');
    });
});

/* ===================== LOAD TEAMS ===================== */
function loadTeams(){
    $.post('api/l4utask/boards.php', { act: 'getStaffsWithTeam' }, function(res){
        if(res.status === 'success'){
            teamsData = res.data;
            renderTeamPicker('teamPickerContainer');
            renderTeamPicker('importTeamPicker');
        } else {
            $('#teamPickerContainer').html('<span class="text-danger" style="font-size:12px;">Failed to load teams</span>');
            $('#importTeamPicker').html('<span class="text-danger" style="font-size:12px;">Failed to load teams</span>');
        }
    },'json').fail(function(){
        $('#teamPickerContainer').html('<span class="text-danger" style="font-size:12px;">Error loading teams</span>');
        $('#importTeamPicker').html('<span class="text-danger" style="font-size:12px;">Error loading teams</span>');
    });
}

function renderTeamPicker(containerId){
    var container = $('#' + containerId).empty();
    if(!teamsData.length){
        container.html('<span class="text-muted" style="font-size:12px;">No teams found</span>');
        return;
    }
    teamsData.forEach(function(team){
        var html = '<div class="team-group">' +
            '<div class="team-group-header d-flex align-items-center" onclick="toggleTeamGroup(this)">' +
                '<input type="checkbox" class="mr-2 team-all-cb" data-team="' + team.key + '" data-container="' + containerId + '"' +
                    ' onclick="event.stopPropagation(); toggleTeamAll(this)">' +
                '<i class="bi bi-chevron-right mr-1 team-chevron" style="font-size:10px;transition:transform .15s;"></i>' +
                '<span>' + escHtml(team.name) + '</span>' +
                '<span class="badge badge-secondary ml-auto" style="font-size:10px;">' + team.members.length + '</span>' +
            '</div>' +
            '<div class="team-members" style="display:none;">';
        team.members.forEach(function(m){
            html += '<div class="team-member-item">' +
                '<input type="checkbox" class="mr-2 member-cb" value="' + m.sID + '" data-team="' + team.key + '"' +
                    ' data-nick="' + escHtml(m.sNickName) + '" data-pic="' + (m.sPic||'no_pic.png') + '" data-container="' + containerId + '">' +
                '<img src="assets/img/crews/' + (m.sPic||'no_pic.png') + '" class="rounded-circle mr-1">' +
                '<span>' + escHtml(m.sNickName) + '</span>' +
            '</div>';
        });
        html += '</div></div>';
        container.append(html);
    });
}

function toggleTeamGroup(header){
    var $h = $(header);
    $h.next('.team-members').slideToggle(150);
    $h.find('.team-chevron').toggleClass('bi-chevron-right bi-chevron-down');
}

function toggleTeamAll(cb){
    var teamKey = $(cb).data('team');
    var cid = $(cb).data('container');
    $('#' + cid + ' .member-cb[data-team="' + teamKey + '"]').prop('checked', cb.checked);
}

function getSelectedMembers(containerId){
    var members = [];
    var role = parseInt($('#addMemberRole').val() || 2);
    $('#' + containerId + ' .member-cb:checked').each(function(){
        members.push({ sID: parseInt($(this).val()), sNickName: $(this).data('nick'), sPic: $(this).data('pic'), role: role });
    });
    return members;
}

function setSelectedMembers(containerId, membersList){
    $('#' + containerId + ' .member-cb').prop('checked', false);
    $('#' + containerId + ' .team-all-cb').prop('checked', false);
    membersList.forEach(function(m){
        $('#' + containerId + ' .member-cb[value="' + m.sID + '"]').prop('checked', true);
    });
    teamsData.forEach(function(team){
        var total = team.members.length;
        var checked = $('#' + containerId + ' .member-cb[data-team="' + team.key + '"]:checked').length;
        $('#' + containerId + ' .team-all-cb[data-team="' + team.key + '"]').prop('checked', checked === total && total > 0);
    });
}

/* ===================== LOAD BOARDS ===================== */
function loadBoards(){
    $('#boardLoading').show();
    $('#boardEmpty').addClass('d-none');
    $.post('api/l4utask/boards.php', { act: 'getBoards' }, function(res){
        $('#boardLoading').hide();
        if(res.status === 'success'){
            var c = $('#boardContainer').empty();
            if(res.data.length === 0){ $('#boardEmpty').removeClass('d-none'); return; }
            res.data.forEach(function(b){
                c.append(
                    '<div class="col-lg-3 col-md-4 col-sm-6 mb-3">' +
                        '<div class="board-card p-3 d-flex flex-column justify-content-between" style="background:' + b.bColor + ';" onclick="goBoard(' + b.bID + ')">' +
                            '<div class="board-actions" onclick="event.stopPropagation();">' +
                                '<button class="btn btn-sm" title="Edit" onclick="openEditBoard(' + b.bID + ')"><i class="bi bi-pencil"></i></button>' +
                                '<button class="btn btn-sm" title="Delete" onclick="deleteBoard(' + b.bID + ',\'' + escHtml(b.bName).replace(/'/g,"\\'") + '\')"><i class="bi bi-trash"></i></button>' +
                            '</div>' +
                            '<div>' +
                                '<div class="board-title">' + escHtml(b.bName) + '</div>' +
                                (b.bDescription ? '<div class="board-meta mt-1">' + escHtml(b.bDescription).substring(0,80) + '</div>' : '') +
                            '</div>' +
                            '<div class="board-meta mt-2">' +
                                '<i class="bi bi-list-task mr-1"></i>' + b.listCount + ' lists &nbsp;' +
                                '<i class="bi bi-card-heading mr-1"></i>' + b.cardCount + ' cards' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                );
            });
        }
    },'json');
}

function goBoard(bID){ window.location = 'main.php?p=l4utaskBoard&bID=' + bID; }

/* ===================== COLOR PICKER ===================== */
function pickColor(el){
    $('#colorPicker .board-color-opt').removeClass('active');
    $(el).addClass('active');
    syncColor($(el).data('color'));
}
function pickCustomColor(hex){
    if(!/^#[0-9A-Fa-f]{6}$/.test(hex)) return;
    $('#colorPicker .board-color-opt').removeClass('active');
    syncColor(hex);
}
function syncColor(c){
    $('#inputBoardColor').val(c);
    $('#customColorPicker').val(c);
    $('#customColorHex').val(c);
    $('#colorPreview').css('background', c);
    $('#modalBoardHeader').css('background', c);
}
function syncImportColor(c){
    $('#importColorPicker').val(c);
    $('#importColorHex').val(c);
    $('#importColorPreview').css('background', c);
}

/* ===================== CREATE / EDIT BOARD ===================== */
function openCreateBoard(){
    $('#editBoardID').val('');
    $('#inputBoardName').val('');
    $('#inputBoardDesc').val('');
    syncColor('#0079BF');
    $('#colorPicker .board-color-opt').removeClass('active').first().addClass('active');
    setSelectedMembers('teamPickerContainer', []);
    $('#modalBoardTitle').html('<i class="bi bi-kanban mr-1"></i> New Board');
    $('#modalBoard').modal('show');
}

function openEditBoard(bID){
    $.post('api/l4utask/boards.php', { act: 'getBoard', bID: bID }, function(res){
        if(res.status !== 'success') return;
        var b = res.data;
        $('#editBoardID').val(bID);
        $('#inputBoardName').val(b.bName);
        $('#inputBoardDesc').val(b.bDescription || '');
        syncColor(b.bColor);
        var preset = $('#colorPicker .board-color-opt[data-color="' + b.bColor + '"]');
        if(preset.length) preset.addClass('active');
        var members = (b.members || []).map(function(m){
            return { sID: parseInt(m.sID), sNickName: m.sNickName, sPic: m.sPic, role: parseInt(m.bmRole) };
        });
        setSelectedMembers('teamPickerContainer', members);
        $('#modalBoardTitle').html('<i class="bi bi-pencil mr-1"></i> Edit Board');
        $('#modalBoard').modal('show');
    },'json');
}

function saveBoard(){
    var bID   = $('#editBoardID').val();
    var name  = $('#inputBoardName').val().trim();
    var desc  = $('#inputBoardDesc').val().trim();
    var color = $('#inputBoardColor').val();
    if(!name){ alert('Please enter board name'); return; }

    var act = bID ? 'updateBoard' : 'createBoard';
    var members = getSelectedMembers('teamPickerContainer').map(function(m){ return { sID: m.sID, role: m.role }; });
    var data = { act: act, bName: name, bDescription: desc, bColor: color, members: JSON.stringify(members) };
    if(bID) data.bID = bID;

    $.post('api/l4utask/boards.php', data, function(res){
        if(res.status === 'success'){
            $('#modalBoard').modal('hide');
            loadBoards();
        } else {
            alert(res.msg || 'Error saving board');
        }
    },'json');
}

function deleteBoard(bID, name){
    if(!confirm('Delete board "' + name + '"? This cannot be undone.')) return;
    $.post('api/l4utask/boards.php', { act: 'deleteBoard', bID: bID }, function(res){
        if(res.status === 'success') loadBoards();
    },'json');
}

/* ===================== IMPORT CSV ===================== */
function openImportBoard(){
    $('#importBoardName').val('');
    $('#importBoardDesc').val('');
    $('#importFileInput').val('');
    $('#importFileLabel').text('Choose file...');
    syncImportColor('#519839');
    $('#importProgress').addClass('d-none');
    $('#importResult').addClass('d-none');
    $('#btnDoImport').prop('disabled', false);
    setSelectedMembers('importTeamPicker', []);
    $('#modalImport').modal('show');
}

function doImport(){
    var name = $('#importBoardName').val().trim();
    var file = $('#importFileInput')[0].files[0];
    if(!name){ alert('Please enter board name'); return; }
    if(!file){ alert('Please select a CSV or Excel file'); return; }

    var fd = new FormData();
    fd.append('act', 'importCSV');
    fd.append('csvFile', file);
    fd.append('bName', name);
    fd.append('bDescription', $('#importBoardDesc').val().trim());
    fd.append('bColor', $('#importColorHex').val() || '#519839');
    var members = getSelectedMembers('importTeamPicker').map(function(m){ return { sID: m.sID, role: m.role }; });
    fd.append('members', JSON.stringify(members));

    $('#importProgress').removeClass('d-none');
    $('#btnDoImport').prop('disabled', true);

    $.ajax({
        url: 'api/l4utask/boards.php',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res){
            $('#importProgress').addClass('d-none');
            if(res.status === 'success'){
                var s = res.stats;
                $('#importResultText').html(
                    'Import completed! Created <b>' + s.lists + '</b> lists, <b>' + s.cards + '</b> cards, <b>' + s.subitems + '</b> sub-items. ' +
                    '<a href="main.php?p=l4utaskBoard&bID=' + res.bID + '" class="alert-link">Open board &rarr;</a>'
                );
                $('#importResult').removeClass('d-none');
                loadBoards();
            } else {
                alert(res.msg || 'Import failed');
                $('#btnDoImport').prop('disabled', false);
            }
        },
        error: function(){
            $('#importProgress').addClass('d-none');
            alert('Upload failed. Please try again.');
            $('#btnDoImport').prop('disabled', false);
        }
    });
}
</script>
