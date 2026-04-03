<?php
global $db, $myID;
$myID = $_SESSION['id'] ?? ($_COOKIE['id'] ?? 0);
$bID = intval($_GET['bID'] ?? 0);
if (!$bID) { echo '<div class="p-5 text-center text-danger">Board not found.</div>'; return; }

$board = $db->query('SELECT * FROM l4utask_boards WHERE bID = ? AND bDeletedAt IS NULL', $bID)->fetchArray();
if (empty($board)) { echo '<div class="p-5 text-center text-danger">Board not found or has been deleted.</div>'; return; }
?>
<!-- Content Header -->
<section class="content-header py-2">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-4 d-flex align-items-center">
                <a href="main.php?p=l4utaskBoards" class="btn btn-sm btn-outline-secondary mr-3" title="Back to boards">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h1 class="mb-0" style="font-size:20px;">
                    <span class="d-inline-block rounded mr-2" style="width:14px;height:14px;background:<?php echo htmlspecialchars($board['bColor']);?>;vertical-align:middle;"></span>
                    <?php echo htmlspecialchars($board['bName']); ?>
                </h1>
            </div>
            <!-- Action Bar -->
            <div class="col-sm-8 d-flex align-items-center" style="gap: 12px;">
                <!-- New Item Button -->
                <div class="dropdown">
                    <button class="btn btn-info btn-sm dropdown-toggle" type="button" id="btnNewItem" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-plus-lg mr-1"></i> ADD
                    </button>
                    <div class="dropdown-menu" aria-labelledby="btnNewItem">
                        <a class="dropdown-item" href="#" onclick="openAddList(); return false;"><i class="bi bi-list-ul mr-2"></i>New Item</a>
                        <a class="dropdown-item" href="#" onclick="showAddCardForList(null, this); return false;"><i class="bi bi-card-text mr-2"></i>New Group</a>
                    </div>
                </div>
                
                <!-- Search -->
                <div class="input-group input-group-sm" style="max-width: 300px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-dark border-right-0">
                            <i class="bi bi-search "></i>
                        </span>
                    </div>
                    <input type="text" class="form-control border-left-0" id="boardSearch" placeholder="Search" onkeyup="searchBoard(this.value)">
                </div>
                
                <!-- View Switcher (Hidden in dropdown) -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="btnFilter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-eye-fill"></i> View
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="btnFilter">
                        <a class="dropdown-item" href="#" onclick="switchView('maintable'); return false;"><i class="bi bi-table mr-2"></i>Main Table View</a>
                        <a class="dropdown-item" href="#" onclick="switchView('kanban'); return false;"><i class="bi bi-kanban mr-2"></i>Kanban View</a>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ VIEW: MAIN TABLE ============ -->
<section class="content view-panel" id="viewMainTable">
    <div class="container-fluid" id="mainTableContainer"></div>
</section>

<!-- ============ VIEW: KANBAN ============ -->
<section class="content view-panel d-none" id="viewKanban" style="overflow-x:auto;">
    <div class="kanban-wrapper" id="kanbanBoard"></div>
</section>



<!-- Loading -->
<div class="text-center py-5" id="kanbanLoading">
    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
</div>

<!-- Modal: Card Detail (Monday.com dark style) -->
<div class="modal fade" id="modalCardDetail" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content cd-modal">
            <div class="cd-modal-header">
                <div class="cd-header-left">
                    <span class="cd-item-id" id="cdItemID"></span>
                    <h5 class="cd-title" id="cardDetailTitle"></h5>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="cd-tabs">
                <button class="cd-tab active" data-tab="overview" onclick="switchCardTab('overview',this)"><i class="bi bi-grid mr-1"></i> Overview</button>
                <button class="cd-tab" data-tab="updates" onclick="switchCardTab('updates',this)"><i class="bi bi-chat-left-text mr-1"></i> Updates <span class="cd-tab-count" id="cdUpdateCount"></span></button>
                <button class="cd-tab" data-tab="activity" onclick="switchCardTab('activity',this)"><i class="bi bi-clock-history mr-1"></i> Activity Log</button>
            </div>
            <div class="cd-body">
                <div class="cd-panel" id="cdPanelOverview"></div>
                <div class="cd-panel d-none" id="cdPanelUpdates"></div>
                <div class="cd-panel d-none" id="cdPanelActivity"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Subitem Detail -->
<div class="modal fade" id="modalSubitemDetail" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content cd-modal">
            <div class="cd-modal-header">
                <div class="cd-header-left">
                    <span class="cd-item-id" id="sdItemID"></span>
                    <h5 class="cd-title" id="subitemDetailTitle"></h5>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="cd-tabs">
                <button class="cd-tab active" data-tab="si-overview" onclick="switchSiTab('si-overview',this)"><i class="bi bi-grid mr-1"></i> Overview</button>
                <button class="cd-tab" data-tab="si-activity" onclick="switchSiTab('si-activity',this)"><i class="bi bi-clock-history mr-1"></i> Activity Log</button>
            </div>
            <div class="cd-body">
                <div class="cd-panel" id="sdPanelOverview"></div>
                <div class="cd-panel d-none" id="sdPanelActivity"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: CSV Upload -->
<div class="modal fade" id="modalCSVUpload" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-earmark-arrow-up mr-2"></i>Upload Excel/CSV Files</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle mr-2"></i>
                    <strong>Supported Formats:</strong> Excel (.xlsx, .xls) and CSV files exported from Monday.com<br>
                    <strong>Required Columns:</strong> <code>title</code> (required), <code>description</code>, <code>list</code>, <code>priority</code>, <code>due_date</code>, <code>stage</code>, <code>assignee</code>
                </div>
                
                <div class="form-group">
                    <label class="font-weight-bold">Select Excel/CSV Files</label>
                    <div class="csv-upload-area" id="csvUploadArea">
                        <input type="file" id="csvFileInput" multiple accept=".csv,.xlsx,.xls" style="display: none;">
                        <div class="csv-drop-zone" id="csvDropZone">
                            <i class="bi bi-cloud-upload" style="font-size: 48px; color: #6c757d;"></i>
                            <p class="mt-3 mb-1">Drag & drop Excel/CSV files here or click to browse</p>
                            <p class="text-muted small">You can select multiple Excel (.xlsx, .xls) and CSV files at once</p>
                        </div>
                        <div class="csv-file-list mt-3" id="csvFileList"></div>
                    </div>
                </div>

                <div class="csv-progress-section" id="csvProgressSection" style="display: none;">
                    <h6 class="mb-3">Upload Progress</h6>
                    <div class="csv-progress-list" id="csvProgressList"></div>
                </div>

                <div class="csv-recent-uploads mt-4">
                    <h6 class="mb-3">Recent Uploads</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="csvUploadsTable">
                            <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Status</th>
                                    <th>Records</th>
                                    <th>Uploaded</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="btnUploadCSV" onclick="uploadCSVFiles()">
                    <i class="bi bi-upload mr-1"></i> Upload Files
                </button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="assets/css/l4utask.css">

<script>
const BOARD_ID = <?php echo $bID; ?>;
const MY_ID = <?php echo $myID; ?>;
// cardDetailModal managed via BS4 jQuery .modal()
let allStaffs = [];
let cachedData = [];
let currentView = 'maintable';

const priorityLabels = {0:'None', 1:'Low', 2:'Medium', 3:'High', 4:'Urgent'};
const priorityColors = {0:'#ccc', 1:'#61bd4f', 2:'#f2d600', 3:'#ff9f1a', 4:'#eb5a46'};

// Detect touch device
const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0;

$(document).ready(function(){
    loadBoardData();
    loadStaffs();
    
    // Add touch-specific class to body for CSS targeting
    if (isTouchDevice) {
        document.body.classList.add('touch-device');
        
        // Manual touch support for sortable elements
        addTouchSupport();
    }
    
    // Ensure jQuery UI is loaded, if not try to load it
    if (typeof $.ui === 'undefined' || typeof $.fn.sortable === 'undefined') {
        console.warn('jQuery UI not detected, attempting to load...');
        var script = document.createElement('script');
        script.src = 'https://code.jquery.com/ui/1.13.2/jquery-ui.min.js';
        script.onload = function() {
            console.log('jQuery UI loaded successfully');
            // Retry sortable initialization after a delay
            setTimeout(function() {
                if (currentView === 'kanban') {
                    initSortable();
                } else if (currentView === 'maintable') {
                    initMainTableSortable();
                }
            }, 500);
        };
        script.onerror = function() {
            console.error('Failed to load jQuery UI');
        };
        document.head.appendChild(script);
    }
});

// Manual touch support implementation
function addTouchSupport() {
    let dragElement = null;
    let startPos = { x: 0, y: 0 };
    let isDragging = false;
    
    // Add touch events to kanban cards
    $(document).on('touchstart', '.kanban-card', function(e) {
        const touch = e.originalEvent.touches[0];
        startPos = { x: touch.clientX, y: touch.clientY };
        dragElement = $(this);
        isDragging = false;
    });
    
    $(document).on('touchmove', '.kanban-card', function(e) {
        if (!dragElement) return;
        
        const touch = e.originalEvent.touches[0];
        const deltaX = Math.abs(touch.clientX - startPos.x);
        const deltaY = Math.abs(touch.clientY - startPos.y);
        
        if (deltaX > 10 || deltaY > 10) {
            isDragging = true;
            e.preventDefault();
        }
    });
    
    $(document).on('touchend', '.kanban-card', function(e) {
        if (isDragging && dragElement) {
            // Handle drop logic here if needed
        }
        dragElement = null;
        isDragging = false;
    });
    
    // Add touch events to table rows
    $(document).on('touchstart', '.mt-card-row-monday', function(e) {
        const touch = e.originalEvent.touches[0];
        startPos = { x: touch.clientX, y: touch.clientY };
        dragElement = $(this);
        isDragging = false;
    });
    
    $(document).on('touchmove', '.mt-card-row-monday', function(e) {
        if (!dragElement) return;
        
        const touch = e.originalEvent.touches[0];
        const deltaX = Math.abs(touch.clientX - startPos.x);
        const deltaY = Math.abs(touch.clientY - startPos.y);
        
        if (deltaX > 10 || deltaY > 10) {
            isDragging = true;
            e.preventDefault();
        }
    });
    
    $(document).on('touchend', '.mt-card-row-monday', function(e) {
        dragElement = null;
        isDragging = false;
    });
}

/* ===================== VIEW SWITCHER ===================== */
function switchView(view){
    currentView = view;
    $('.view-switcher .btn').removeClass('active');
    $(`.view-switcher .btn[data-view="${view}"]`).addClass('active');
    $('.view-panel').addClass('d-none');

    if(view === 'kanban'){
        $('#viewKanban').removeClass('d-none');
        $('#btnAddList').show();
        renderKanbanView();
        // Ensure sortable is initialized when switching to Kanban view
        setTimeout(function() {
            initSortable();
        }, 200);
    } else if(view === 'maintable'){
        $('#viewMainTable').removeClass('d-none');
        $('#btnAddList').hide();
        renderMainTableView();
    }
}

/* ===================== LOAD DATA (shared by all views) ===================== */
function loadBoardData(callback){
    $('#kanbanLoading').show();
    $.post('api/l4utask/lists.php', { act: 'getLists', bID: BOARD_ID }, function(res){
        $('#kanbanLoading').hide();
        if(res.status !== 'success') return;
        cachedData = res.data;
        if(currentView === 'kanban') renderKanbanView();
        else if(currentView === 'maintable') renderMainTableView();
        if(callback) callback();
    },'json');
}

function reloadCurrentView(){
    loadBoardData();
}

/* ===================== KANBAN VIEW ===================== */
function renderKanbanView(data = null){
    const board = $('#kanbanBoard').empty();
    const dataToRender = data || cachedData;
    dataToRender.forEach(list => {
        const cardsHtml = (list.cards || []).map(c => renderCard(c)).join('');
        board.append(`
            <div class="kanban-list" data-lid="${list.lID}">
                <div class="kanban-list-header">
                    <span class="kanban-list-title" ondblclick="editListTitle(this, ${list.lID})">${esc(list.lName)}</span>
                    <span class="kanban-list-count">${(list.cards||[]).length}</span>
                    <div class="dropdown ml-auto">
                        <button class="btn btn-sm text-muted p-0" data-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="#" onclick="archiveList(${list.lID}); return false;"><i class="bi bi-archive mr-2"></i>Archive List</a>
                        </div>
                    </div>
                </div>
                <div class="kanban-cards" data-lid="${list.lID}">
                    ${cardsHtml}
                </div>
                <div class="kanban-add-card">
                    <button class="btn btn-sm btn-block text-muted text-left" onclick="showAddCardInput(this, ${list.lID})">
                        <i class="bi bi-plus mr-1"></i> Add a card
                    </button>
                </div>
            </div>
        `);
    });
    board.append(`<div class="kanban-list kanban-list-add d-none" id="addListPlaceholder">
        <input type="text" class="form-control form-control-sm mb-2" id="inputNewListName" placeholder="Enter list name..." maxlength="255">
        <div>
            <button class="btn btn-primary btn-sm" onclick="saveNewList()">Add List</button>
            <button class="btn btn-sm text-muted" onclick="$('#addListPlaceholder').addClass('d-none')"><i class="bi bi-x-lg"></i></button>
        </div>
    </div>`);
    // Initialize sortable after DOM elements are rendered with debugging
    setTimeout(function() {
        console.log('Attempting to initialize sortable...');
        console.log('jQuery available:', typeof $ !== 'undefined');
        console.log('jQuery UI available:', typeof $.ui !== 'undefined');
        console.log('Sortable available:', typeof $.fn.sortable !== 'undefined');
        console.log('Kanban board element exists:', $('#kanbanBoard').length > 0);
        console.log('Kanban cards elements:', $('.kanban-cards').length);
        console.log('Cards in board:', $('.kanban-card').length);
        
        if ($('#kanbanBoard').length > 0 && $('.kanban-cards').length > 0) {
            initSortable();
        } else {
            console.error('Required elements not found for sortable initialization');
        }
    }, 200);
}

function renderCard(c){
    const memberAvatars = (c.members||[]).map(m =>
        `<img src="assets/img/crews/${m.sPic || 'no_pic.png'}" class="kanban-avatar" title="${esc(m.sNickName)}">`
    ).join('');
    const priorityBadge = c.cPriority > 0
        ? `<span class="badge mr-1" style="background:${priorityColors[c.cPriority]};color:#fff;font-size:10px;">${priorityLabels[c.cPriority]}</span>` : '';
    const colorBar = c.cColor ? `<div class="kanban-card-color" style="background:${c.cColor};"></div>` : '';
    const dueHtml = c.cDueDate
        ? `<span class="kanban-card-due ${isDueOverdue(c.cDueDate)?'overdue':''}"><i class="bi bi-clock mr-1"></i>${formatDate(c.cDueDate)}</span>` : '';
    const descIcon = c.cDescription ? '<i class="bi bi-text-left text-muted mr-2" title="Has description"></i>' : '';
    return `
        <div class="kanban-card" data-cid="${c.cID}" onclick="openCardDetail(${c.cID})">
            ${colorBar}
            <div class="kanban-card-body">
                <div class="kanban-card-labels">${priorityBadge}</div>
                <div class="kanban-card-title">${esc(c.cTitle)}</div>
                <div class="kanban-card-footer">
                    <div>${descIcon}${dueHtml}</div>
                    <div class="kanban-card-avatars">${memberAvatars}</div>
                </div>
            </div>
        </div>`;
}

/* ===================== MAIN TABLE VIEW (Monday.com Style) ===================== */
var groupColors = [
    {bg:'#579bfc',text:'#fff'},      // Blue
    {bg:'#a25ddc',text:'#fff'},      // Purple
    {bg:'#00c875',text:'#fff'},      // Green
    {bg:'#fdab3d',text:'#fff'},      // Orange
    {bg:'#ff642e',text:'#fff'},      // Red-Orange
    {bg:'#e2445c',text:'#fff'},      // Red
    {bg:'#0086c0',text:'#fff'},      // Dark Blue
    {bg:'#9cd326',text:'#fff'}       // Lime
];

function renderMainTableView(data = null){
    var container = $('#mainTableContainer').empty();
    var dataToRender = data || cachedData;
    if(dataToRender.length === 0){
        container.html('<div class="text-center py-5" style="color:#9ca3af;">No lists yet</div>');
        return;
    }
    
    // Calculate overall stats
    var totalCards = 0, totalSubitems = 0;
    dataToRender.forEach(function(list){
        totalCards += (list.cards || []).length;
        (list.cards || []).forEach(function(c){
            totalSubitems += (c.subitems || []).length;
        });
    });
    
    dataToRender.forEach(function(list, gi){
        var cards = list.cards || [];
        var gColor = groupColors[gi % groupColors.length];
        var gID = 'mtg_' + list.lID;
        
        // Calculate group stats
        var groupSubitems = 0;
        var completedItems = 0;
        cards.forEach(function(c){
            groupSubitems += (c.subitems || []).length;
            if(c.cStage === 'Done' || c.cStage === 'Completed') completedItems++;
        });
        var progressPercent = cards.length > 0 ? Math.round((completedItems / cards.length) * 100) : 0;
        
        // Build group header with Monday.com style
        var headerHtml = 
            '<div class="mt-group-header-monday" style="background:' + gColor.bg + ';" data-lid="' + list.lID + '">' +
                '<div class="mt-group-header-left">' +
                    '<i class="bi bi-chevron-down mt-group-chevron open" id="chev_' + gID + '"></i>' +
                    '<span class="mt-group-name-monday clickable" ondblclick="event.stopPropagation(); editListTitle(this, ' + list.lID + ')">' + esc(list.lName) + '</span>' +
                    '<span class="mt-group-count-monday">' + cards.length + ' Tasks / ' + groupSubitems + ' Subitems</span>' +
                '</div>' +
                '<div class="mt-group-header-right">' +
                    '<div class="mt-progress-container">' +
                        '<div class="mt-progress-bar" style="width:' + progressPercent + '%;background:' + (progressPercent === 100 ? '#00c875' : progressPercent > 50 ? '#fdab3d' : '#e2445c') + ';"></div>' +
                    '</div>' +
                    '<span class="mt-progress-text">' + progressPercent + '%</span>' +
                    '<button class="btn btn-sm btn-light ml-2" onclick="event.stopPropagation(); showAddCardForList(' + list.lID + ', this)" title="Add task"><i class="bi bi-plus-lg"></i></button>' +
                    '<div class="dropdown ml-2">' +
                        '<button class="btn btn-sm btn-light mt-group-menu-btn" data-toggle="dropdown" onclick="event.stopPropagation();" title="More options">' +
                            '<i class="bi bi-three-dots"></i>' +
                        '</button>' +
                        '<div class="dropdown-menu dropdown-menu-right">' +
                            '<a class="dropdown-item" href="#" onclick="event.stopPropagation(); editListTitleByName(' + list.lID + '); return false;"><i class="bi bi-pencil mr-2"></i>Edit List Name</a>' +
                            '<a class="dropdown-item" href="#" onclick="event.stopPropagation(); archiveList(' + list.lID + '); return false;"><i class="bi bi-archive mr-2"></i>Archive List</a>' +
                            '<a class="dropdown-item" href="#" onclick="event.stopPropagation(); duplicateList(' + list.lID + '); return false;"><i class="bi bi-copy mr-2"></i>Duplicate List</a>' +
                            '<div class="dropdown-divider"></div>' +
                            '<a class="dropdown-item text-danger" href="#" onclick="event.stopPropagation(); deleteList(' + list.lID + '); return false;"><i class="bi bi-trash mr-2"></i>Delete List</a>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        
        // Build table rows
        var rowsHtml = '';
        if(cards.length === 0){
            rowsHtml = '<tr class="mt-card-row-monday"><td colspan="6" class="mt-empty-cell">No tasks in this group</td></tr>';
        } else {
            cards.forEach(function(c, ci){
                var subs = c.subitems || [];
                var hasSubs = subs.length > 0;
                
                // Task row with Monday.com style
                var expandBtn = hasSubs
                    ? '<button class="mt-expand-btn-monday" onclick="event.stopPropagation(); toggleMtSubs(this,' + c.cID + ')"><i class="bi bi-chevron-right"></i></button>'
                    : '<span class="mt-expand-placeholder-monday"></span>';
                
                var memberAvatars = (c.members||[]).slice(0,3).map(function(m){
                    return '<img src="assets/img/crews/' + (m.sPic||'no_pic.png') + '" class="mt-avatar-monday" title="' + esc(m.sNickName) + '">';
                }).join('');
                if((c.members||[]).length > 3){
                    memberAvatars += '<span class="mt-avatar-more">+' + ((c.members||[]).length - 3) + '</span>';
                }
                
                var stageBadge = mtStageBadgeMonday(c.cStage || 'Draft', gColor.bg);
                var priBadge = c.cPriority > 0 ? '<span class="mt-pri-dot" style="background:' + priorityColors[c.cPriority] + ';"></span>' : '';
                
                var dueHtml = c.cDueDate
                    ? '<span class="mt-due-date ' + (isDueOverdue(c.cDueDate)?'mt-overdue':'') + '">' + formatDate(c.cDueDate) + '</span>'
                    : '<span class="mt-no-date">-</span>';
                
                var timelineHtml = renderTimeline(c.cDueDate, c.cCompletedAt);
                
                rowsHtml += '<tr class="mt-card-row-monday" data-cid="' + c.cID + '" data-lid="' + list.lID + '" onclick="openCardDetail(' + c.cID + ')" draggable="true">' +
                    '<td class="mt-card-checkbox-cell">' +
                        '<div class="d-flex align-items-center">' +
                            '<div class="dropdown mr-1">' +
                                '<button class="btn btn-sm mt-card-menu-btn" onclick="event.stopPropagation(); toggleCardMenu(' + c.cID + ', this);" title="More options">' +
                                    '<i class="bi bi-three-dots"></i>' +
                                '</button>' +
                                '<div class="dropdown-menu" id="card-menu-' + c.cID + '" style="display:none;">' +
                                    '<a class="dropdown-item" href="#" onclick="event.stopPropagation(); editCard(' + c.cID + '); closeCardMenu(' + c.cID + '); return false;"><i class="bi bi-pencil mr-2"></i>Edit Card</a>' +
                                    '<a class="dropdown-item" href="#" onclick="event.stopPropagation(); duplicateCard(' + c.cID + '); closeCardMenu(' + c.cID + '); return false;"><i class="bi bi-copy mr-2"></i>Duplicate Card</a>' +
                                    '<a class="dropdown-item has-submenu" href="#" onclick="event.stopPropagation(); return false;"><i class="bi bi-arrows-move mr-2"></i>Move Card <i class="bi bi-chevron-right float-right mt-1"></i>' +
                                        '<div class="dropdown-submenu">' +
                                            '<a class="dropdown-item" href="#" onclick="event.stopPropagation(); moveCardToTop(' + c.cID + '); closeCardMenu(' + c.cID + '); return false;"><i class="bi bi-arrow-up mr-2"></i>Move to Top</a>' +
                                            '<a class="dropdown-item" href="#" onclick="event.stopPropagation(); moveCardToGroup(' + c.cID + '); closeCardMenu(' + c.cID + '); return false;"><i class="bi bi-collection mr-2"></i>Move to Group</a>' +
                                            '<a class="dropdown-item" href="#" onclick="event.stopPropagation(); moveCardToBoard(' + c.cID + '); closeCardMenu(' + c.cID + '); return false;"><i class="bi bi-kanban mr-2"></i>Move to Board</a>' +
                                        '</div>' +
                                    '</a>' +
                                    '<div class="dropdown-divider"></div>' +
                                    '<a class="dropdown-item text-danger" href="#" onclick="event.stopPropagation(); deleteCard(' + c.cID + '); closeCardMenu(' + c.cID + '); return false;"><i class="bi bi-trash mr-2"></i>Delete Card</a>' +
                                '</div>' +
                            '</div>' +
                            '<input type="checkbox" class="mt-card-checkbox" data-cid="' + c.cID + '" onclick="event.stopPropagation(); toggleCardSelect(' + c.cID + ');">' +
                        '</div>' +
                    '</td>' +
                    '<td class="mt-task-name">' +
                        '<div class="mt-task-title-wrap">' +
                            expandBtn +
                            priBadge +
                            '<span class="mt-task-title">' + esc(c.cTitle) + '</span>' +
                            (hasSubs ? '<span class="mt-sub-badge">' + subs.length + '</span>' : '') +
                        '</div>' +
                    '</td>' +
                    '<td class="mt-task-owner">' + (memberAvatars || '<span class="mt-unassigned"><i class="bi bi-person"></i></span>') + '</td>' +
                    '<td class="mt-task-status">' + stageBadge + '</td>' +
                    '<td class="mt-task-due">' + dueHtml + '</td>' +
                    '<td class="mt-task-timeline">' + timelineHtml + '</td>' +
                '</tr>';
                
                // Subitem rows
                subs.forEach(function(si, siIdx){
                    var siAssignee = si.assigneeName
                        ? '<img src="assets/img/crews/' + (si.assigneePic||'no_pic.png') + '" class="mt-avatar-monday" title="' + esc(si.assigneeName) + '">'
                        : '<span class="mt-unassigned"><i class="bi bi-person"></i></span>';
                    var siDue = si.siDueDate
                        ? '<span class="mt-due-date ' + (isDueOverdue(si.siDueDate)?'mt-overdue':'') + '">' + formatDate(si.siDueDate) + '</span>'
                        : '<span class="mt-no-date">-</span>';
                    var siStage = mtStageBadgeMonday(si.siStatus || 'Pending', '#6c757d');
                    var siTimeline = renderTimeline(si.siDueDate, si.siCompletedAt);
                    
                    rowsHtml += '<tr class="mt-sub-row-monday mt-subs-' + c.cID + '" style="display:none;">' +
                        '<td class="mt-card-checkbox-cell"><input type="checkbox" class="mt-card-checkbox" onclick="event.stopPropagation();"></td>' +
                        '<td class="mt-task-name">' +
                            '<div class="mt-task-title-wrap">' +
                                '<span class="mt-sub-line"></span>' +
                                '<span class="mt-sub-title">' + esc(si.siTitle) + '</span>' +
                            '</div>' +
                        '</td>' +
                        '<td class="mt-task-owner">' + siAssignee + '</td>' +
                        '<td class="mt-task-status">' + siStage + '</td>' +
                        '<td class="mt-task-due">' + siDue + '</td>' +
                        '<td class="mt-task-timeline">' + siTimeline + '</td>' +
                    '</tr>';
                });
            });
        }
        
        container.append(
            '<div class="mt-group-monday">' +
                headerHtml +
                '<div class="mt-group-body-monday" id="' + gID + '">' +
                    '<table class="mt-table-monday">' +
                        '<thead>' +
                            '<tr>' +
                                '<th class="mt-col-task"></th>' +
                                '<th class="mt-col-task">Task Name</th>' +
                                '<th class="mt-col-owner">Owner</th>' +
                                '<th class="mt-col-status">Status</th>' +
                                '<th class="mt-col-due">Due Date</th>' +
                                '<th class="mt-col-timeline">Timeline</th>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody class="mt-sortable-tbody" data-lid="' + list.lID + '">' + rowsHtml + '</tbody>' +
                    '</table>' +
                '</div>' +
            '</div>'
        );
    });
    
    // Add click handlers after rendering
    $('.mt-group-header-monday').each(function(){
        var $header = $(this);
        var lID = $header.data('lid');
        var clicks = 0;
        var timer = null;
        
        $header.on('click', function(e){
            clicks++;
            if(clicks === 1) {
                timer = setTimeout(function(){
                    clicks = 0;
                    toggleMtGroup('mtg_' + lID, $header[0]);
                }, 250);
            } else {
                clearTimeout(timer);
                clicks = 0;
                // Find the name element and trigger edit
                var $nameEl = $header.find('.mt-group-name-monday');
                if($nameEl.length) {
                    editListTitle($nameEl[0], lID);
                }
            }
        });
    });
    
    // Initialize sortable for main table after DOM is rendered with debugging
    setTimeout(function() {
        console.log('Attempting to initialize main table sortable...');
        console.log('Main table tbody elements:', $('.mt-sortable-tbody').length);
        console.log('Table rows:', $('.mt-card-row-monday').length);
        
        if ($('.mt-sortable-tbody').length > 0) {
            initMainTableSortable();
        } else {
            console.error('Main table tbody elements not found');
        }
    }, 200);
}

/* Render timeline bar */
function renderTimeline(dueDate, completedDate){
    if(!dueDate && !completedDate) return '<span class="mt-no-timeline">-</span>';
    
    var now = new Date();
    var due = dueDate ? new Date(dueDate) : null;
    var completed = completedDate ? new Date(completedDate) : null;
    
    if(completed){
        return '<div class="mt-timeline-bar mt-timeline-done"><span>Completed</span></div>';
    }
    
    if(due){
        var diff = Math.ceil((due - now) / (1000 * 60 * 60 * 24));
        var color = diff < 0 ? '#e2445c' : diff < 3 ? '#fdab3d' : '#00c875';
        var text = diff < 0 ? 'Overdue ' + Math.abs(diff) + 'd' : diff + ' days left';
        return '<div class="mt-timeline-bar" style="background:' + color + ';"><span>' + text + '</span></div>';
    }
    
    return '<span class="mt-no-timeline">-</span>';
}

/* Stage badge for Monday style */
function mtStageBadgeMonday(stage, groupColor){
    if(!stage) return '<span class="mt-badge-monday mt-badge-gray">-</span>';
    var s = stage.toLowerCase().replace(/\s+/g,'');
    var cls = 'mt-badge-monday ';
    var style = '';
    
    if(s === 'done' || s === 'completed'){
        cls += 'mt-badge-green';
    } else if(s === 'draft'){
        cls += 'mt-badge-gray';
    } else if(s === 'newrequest' || s === 'new'){
        style = 'background:#579bfc;color:#fff;';
    } else if(s === 'review'){
        style = 'background:#a25ddc;color:#fff;';
    } else if(s === 'inprogress' || s === 'progress' || s === 'working'){
        style = 'background:#fdab3d;color:#fff;';
    } else if(s === 'pending'){
        cls += 'mt-badge-orange';
    } else {
        style = 'background:' + groupColor + ';color:#fff;';
    }
    
    return '<span class="' + cls + '" style="' + style + '">' + esc(stage) + '</span>';
}

/* Toggle group collapse */
function toggleMtGroup(gID, header){
    var body = $('#' + gID);
    body.slideToggle(200);
    $('#chev_' + gID).toggleClass('open');
}

/* Toggle subitems expand */
function toggleMtSubs(btn, cID){
    var $btn = $(btn);
    $btn.toggleClass('open');
    $('.mt-subs-' + cID).each(function(){
        if($btn.hasClass('open')){
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

/* Stage badge helper */
function mtStageBadge(stage){
    if(!stage) return '<span class="mt-badge mt-badge-empty">-</span>';
    var s = stage.toLowerCase().replace(/\s+/g,'');
    var cls = 'mt-badge ';
    if(s === 'done') cls += 'mt-badge-done';
    else if(s === 'draft') cls += 'mt-badge-draft';
    else if(s === 'newrequest' || s === 'new') cls += 'mt-badge-new';
    else if(s === 'review') cls += 'mt-badge-review';
    else if(s === 'inprogress' || s === 'progress' || s === 'working') cls += 'mt-badge-progress';
    else if(s === 'needfix' || s === 'stuck') cls += 'mt-badge-needfix';
    else if(s === 'queuetogolive' || s === 'queue') cls += 'mt-badge-queue';
    else if(s === 'waitingondomainname' || s === 'waiting') cls += 'mt-badge-waiting';
    else if(s === 'online') cls += 'mt-badge-online';
    else if(s === 'pending') cls += 'mt-badge-draft';
    else if(s === 'critical') cls += 'mt-badge-needfix';
    else if(s === 'high') cls += 'mt-badge-high';
    else cls += 'mt-badge-empty';
    return '<span class="' + cls + '">' + esc(stage) + '</span>';
}

/* Priority badge helper */
function mtPriBadge(pri){
    var p = parseInt(pri) || 0;
    if(p === 0) return '<span class="mt-badge mt-pri-none">-</span>';
    var cls = 'mt-badge ';
    if(p === 1) cls += 'mt-pri-low';
    else if(p === 2) cls += 'mt-pri-medium';
    else if(p === 3) cls += 'mt-pri-high';
    else if(p === 4) cls += 'mt-pri-urgent';
    return '<span class="' + cls + '">' + priorityLabels[p] + '</span>';
}

function showAddCardForList(lID, btn){
    var group = $(btn).closest('.mt-group');
    var existing = group.find('.mt-add-input');
    if(existing.length){ existing.focus(); return; }
    var body = group.find('.mt-group-body');
    body.append(
        '<div class="mt-add-row" style="padding:8px 16px;background:#1a1e3a;border-bottom:1px solid #2a2e4a;">' +
            '<div style="display:flex;gap:8px;">' +
                '<input type="text" class="form-control form-control-sm mt-add-input" placeholder="Enter card title..." maxlength="500" style="background:#22264a;color:#e0e0e0;border:1px solid #3a3f6a;">' +
                '<button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); saveCardFromMainTable(' + lID + ', this)" style="white-space:nowrap;">Add</button>' +
                '<button class="btn btn-sm" style="color:#9ca3af;" onclick="event.stopPropagation(); $(this).closest(\'.mt-add-row\').remove()"><i class="bi bi-x-lg"></i></button>' +
            '</div>' +
        '</div>'
    );
    body.find('.mt-add-input').focus().on('keydown', function(e){
        if(e.key === 'Enter'){ saveCardFromMainTable(lID, this); }
    });
}

function saveCardFromMainTable(lID, el){
    var row = $(el).closest('.mt-add-row');
    var title = row.find('.mt-add-input').val().trim();
    if(!title) return;
    $.post('api/l4utask/cards.php', { act: 'createCard', lID: lID, bID: BOARD_ID, cTitle: title }, function(res){
        if(res.status === 'success') reloadCurrentView();
    },'json');
}

/* ===================== SORTABLE (Drag & Drop) ===================== */
function initSortable(){
    console.log('Initializing sortable...');
    
    // Check if jQuery UI sortable is available
    if (!$.fn.sortable) {
        console.error('jQuery UI sortable is not loaded');
        return;
    }

    try {
        // Destroy existing sortable instances to prevent conflicts
        if ($('#kanbanBoard').hasClass('ui-sortable')) {
            $('#kanbanBoard').sortable('destroy');
        }
        $('.kanban-cards').each(function() {
            if ($(this).hasClass('ui-sortable')) {
                $(this).sortable('destroy');
            }
        });

        // Initialize list sorting (simplified for reliability)
        $('#kanbanBoard').sortable({
            items: '.kanban-list:not(.kanban-list-add)',
            handle: '.kanban-list-header',
            tolerance: 'intersect',
            placeholder: 'kanban-list-placeholder',
            opacity: 0.8,
            update: function(){
                console.log('List order updated');
                const positions = [];
                $('#kanbanBoard .kanban-list:not(.kanban-list-add)').each(function(i){
                    positions.push({ lID: $(this).data('lid'), position: i });
                });
                $.post('api/l4utask/lists.php', { act: 'reorderLists', positions: JSON.stringify(positions) });
            }
        });

        // Initialize card sorting (simplified for reliability)
        $('.kanban-cards').sortable({
            connectWith: '.kanban-cards',
            items: '.kanban-card',
            tolerance: 'intersect',
            placeholder: 'kanban-card-placeholder',
            opacity: 0.8,
            update: function(e, ui){
                console.log('Card order updated');
                if(this === ui.item.parent()[0]) saveCardPositions();
            }
        });

        console.log('Sortable initialized successfully');
        console.log('Kanban board sortable:', $('#kanbanBoard').hasClass('ui-sortable'));
        console.log('Cards sortable:', $('.kanban-cards').hasClass('ui-sortable'));
    } catch (error) {
        console.error('Error initializing sortable:', error);
    }
}

function initMainTableSortable(){
    // Check if jQuery UI sortable is available
    if (!$.fn.sortable) {
        console.error('jQuery UI sortable is not loaded for main table');
        return;
    }

    try {
        // Destroy existing sortable instances
        $('.mt-sortable-tbody').each(function() {
            if ($(this).hasClass('ui-sortable')) {
                $(this).sortable('destroy');
            }
        });

        // Initialize table row sorting (simplified for reliability)
        $('.mt-sortable-tbody').sortable({
            connectWith: '.mt-sortable-tbody',
            items: 'tr.mt-card-row-monday',
            handle: 'td.mt-task-name',
            tolerance: 'intersect',
            placeholder: 'mt-card-row-placeholder',
            opacity: 0.8,
            helper: function(e, tr) {
                var $originals = tr.children();
                var $helper = tr.clone();
                $helper.children().each(function(index) {
                    $(this).width($originals.eq(index).width());
                });
                return $helper;
            },
            update: function(e, ui){
                console.log('Table row order updated');
                if(this === ui.item.parent()[0]) saveMainTablePositions();
            }
        });

        console.log('Main table sortable initialized successfully');
    } catch (error) {
        console.error('Error initializing main table sortable:', error);
    }
}

function saveMainTablePositions(){
    const positions = [];
    $('.mt-sortable-tbody').each(function(){
        const lID = $(this).data('lid');
        $(this).find('tr.mt-card-row-monday').each(function(i){
            const cID = $(this).data('cid');
            if (cID) {
                positions.push({ cID: cID, lID: lID, position: i });
            }
        });
    });
    $.post('api/l4utask/cards.php', { act: 'reorderCards', positions: JSON.stringify(positions) }, function(res){
        if(res.status === 'success'){
            // Update group counts after reordering
            $('.mt-group-monday').each(function(){
                const $group = $(this);
                const lID = $group.find('.mt-sortable-tbody').data('lid');
                const cardCount = $group.find('tr.mt-card-row-monday').length;
                $group.find('.mt-group-count-monday').text(cardCount + ' Tasks / ' + $group.find('tr.mt-sub-row-monday').length + ' Subitems');
            });
        }
    },'json');
}

function saveCardPositions(){
    const positions = [];
    $('.kanban-cards').each(function(){
        const lID = $(this).data('lid');
        $(this).find('.kanban-card').each(function(i){
            positions.push({ cID: $(this).data('cid'), lID: lID, position: i });
        });
    });
    $.post('api/l4utask/cards.php', { act: 'reorderCards', positions: JSON.stringify(positions) });
    $('.kanban-list').each(function(){
        const cnt = $(this).find('.kanban-card').length;
        $(this).find('.kanban-list-count').text(cnt);
    });
}

/* ===================== ADD LIST ===================== */
function openAddList(){
    if(currentView !== 'kanban') switchView('kanban');
    setTimeout(function(){
        $('#addListPlaceholder').removeClass('d-none');
        $('#inputNewListName').val('').focus();
    }, 100);
}

function saveNewList(){
    const name = $('#inputNewListName').val().trim();
    if(!name) return;
    $.post('api/l4utask/lists.php', { act: 'createList', bID: BOARD_ID, lName: name }, function(res){
        if(res.status === 'success') reloadCurrentView();
    },'json');
}

/* ===================== EDIT LIST TITLE ===================== */
function editListTitle(el, lID){
    const current = $(el).text().trim();
    const isMainTable = $(el).hasClass('mt-group-name-monday');
    const inputClass = isMainTable ? 'form-control form-control-sm mt-group-title-input' : 'form-control form-control-sm kanban-list-title-input';
    const input = $(`<input type="text" class="${inputClass}" value="${esc(current)}" maxlength="255">`);
    $(el).replaceWith(input);
    input.focus().select();
    function save(){
        const newName = input.val().trim() || current;
        const spanClass = isMainTable ? 'mt-group-name-monday' : 'kanban-list-title';
        const span = $(`<span class="${spanClass}" ondblclick="event.stopPropagation(); editListTitle(this, ${lID})">${esc(newName)}</span>`);
        input.replaceWith(span);
        if(newName !== current){
            $.post('api/l4utask/lists.php', { act: 'updateList', lID, lName: newName }, function(res){
                if(res.status === 'success'){
                    if(currentView === 'maintable') {
                        reloadCurrentView(); // Refresh main table to update the name
                    }
                }
            });
        }
    }
    input.on('blur', save).on('keydown', function(e){
        if(e.key === 'Enter') save();
        if(e.key === 'Escape'){ input.val(current); save(); }
    });
}

function archiveList(lID){
    if(!confirm('Archive this list and all its cards?')) return;
    $.post('api/l4utask/lists.php', { act: 'archiveList', lID }, function(res){
        if(res.status === 'success') reloadCurrentView();
    },'json');
}

/* ===================== ADD CARD ===================== */
function showAddCardInput(btn, lID){
    const wrapper = $(btn).parent();
    wrapper.html(`
        <textarea class="form-control form-control-sm mb-2" id="newCardTitle_${lID}" placeholder="Enter a title for this card..." rows="2" style="resize:none;"></textarea>
        <div>
            <button class="btn btn-primary btn-sm" onclick="saveNewCard(${lID})">Add Card</button>
            <button class="btn btn-sm text-muted" onclick="cancelAddCard(${lID}, this)"><i class="bi bi-x-lg"></i></button>
        </div>
    `);
    $(`#newCardTitle_${lID}`).focus();
    $(`#newCardTitle_${lID}`).on('keydown', function(e){
        if(e.key === 'Enter' && !e.shiftKey){ e.preventDefault(); saveNewCard(lID); }
    });
}

function cancelAddCard(lID, el){
    $(el).closest('.kanban-add-card').html(`
        <button class="btn btn-sm btn-block text-muted text-left" onclick="showAddCardInput(this, ${lID})">
            <i class="bi bi-plus mr-1"></i> Add a card
        </button>
    `);
}

function saveNewCard(lID){
    const title = $(`#newCardTitle_${lID}`).val().trim();
    if(!title) return;
    $.post('api/l4utask/cards.php', { act: 'createCard', lID, bID: BOARD_ID, cTitle: title }, function(res){
        if(res.status === 'success') reloadCurrentView();
    },'json');
}

/* ===================== CARD DETAIL MODAL ===================== */
var currentCardData = null;

function switchCardTab(tab, btn){
    $('.cd-tabs .cd-tab').removeClass('active');
    $(btn).addClass('active');
    $('#cdPanelOverview, #cdPanelUpdates, #cdPanelActivity').addClass('d-none');
    if(tab === 'overview') $('#cdPanelOverview').removeClass('d-none');
    else if(tab === 'updates') $('#cdPanelUpdates').removeClass('d-none');
    else if(tab === 'activity'){
        $('#cdPanelActivity').removeClass('d-none');
        if(currentCardData) loadCardActivities(currentCardData.cID);
    }
}

function openCardDetail(cID){
    $.post('api/l4utask/cards.php', { act: 'getCard', cID: cID }, function(res){
        if(res.status !== 'success') return;
        var c = res.data;
        currentCardData = c;
        $('#cdItemID').text('#' + c.cID);
        $('#cardDetailTitle').text(c.cTitle);
        $('#cdUpdateCount').text(c.comments && c.comments.length ? '/ ' + c.comments.length : '');
        // Reset to overview tab
        $('.cd-tabs .cd-tab').removeClass('active').first().addClass('active');
        $('#cdPanelOverview').removeClass('d-none');
        $('#cdPanelUpdates, #cdPanelActivity').addClass('d-none');
        // Render panels
        buildOverviewPanel(c);
        buildUpdatesPanel(c);
        $('#modalCardDetail').modal('show');
    },'json');
}

/* ---- OVERVIEW TAB ---- */
function buildOverviewPanel(c){
    var staffOpts = allStaffs.map(function(s){ return '<option value="' + s.sID + '">' + esc(s.sNickName) + '</option>'; }).join('');
    var memberAvatars = (c.members||[]).map(function(m){
        return '<div class="cd-member" id="memberRow_' + m.sID + '">' +
            '<img src="assets/img/crews/' + (m.sPic||'no_pic.png') + '" class="cd-avatar" title="' + esc(m.sNickName) + '">' +
            '<button class="cd-member-remove" onclick="removeMember(' + c.cID + ',' + m.sID + ')"><i class="bi bi-x"></i></button>' +
        '</div>';
    }).join('') || '<span class="cd-empty">-</span>';

    var stageOpts = ['Draft','New Request','In Progress','Review','Need Fix','Queue to go live','Waiting on Domain Name','Online','Done'];
    var stageSelect = stageOpts.map(function(s){
        return '<option value="' + s + '" ' + ((c.cStage||'Draft')===s?'selected':'') + '>' + s + '</option>';
    }).join('');

    var priOpts = [{v:0,l:'None'},{v:1,l:'Low'},{v:2,l:'Medium'},{v:3,l:'High'},{v:4,l:'Urgent'}];
    var priSelect = priOpts.map(function(p){
        return '<option value="' + p.v + '" ' + (parseInt(c.cPriority)==p.v?'selected':'') + '>' + p.l + '</option>';
    }).join('');

    var html = '<div class="cd-overview">' +
        '<div class="cd-overview-grid">' +
            /* Row 1: main fields */
            '<div class="cd-field">' +
                '<div class="cd-field-label"><i class="bi bi-list-ul"></i> List</div>' +
                '<div class="cd-field-value">' + esc(c.lName||'-') + '</div>' +
            '</div>' +
            '<div class="cd-field">' +
                '<div class="cd-field-label"><i class="bi bi-people"></i> Task Owner</div>' +
                '<div class="cd-field-value"><div class="cd-members-row">' + memberAvatars +
                    '<div class="cd-member-add-wrap"><select class="cd-member-add" id="addMemberSelect_' + c.cID + '" onchange="assignMember(' + c.cID + ')">' +
                    '<option value="">+</option>' + staffOpts + '</select></div>' +
                '</div></div>' +
            '</div>' +
            '<div class="cd-field cd-field-wide">' +
                '<div class="cd-field-label"><i class="bi bi-text-left"></i> Description</div>' +
                '<div class="cd-field-value"><textarea class="cd-textarea" rows="4" placeholder="Add description..." onchange="updateCardField(' + c.cID + ',\'cDescription\',this.value)">' + esc(c.cDescription||'') + '</textarea></div>' +
            '</div>' +
            '<div class="cd-field">' +
                '<div class="cd-field-label"><i class="bi bi-flag"></i> Priority</div>' +
                '<div class="cd-field-value"><select class="cd-select" onchange="updateCardField(' + c.cID + ',\'cPriority\',this.value)">' + priSelect + '</select></div>' +
            '</div>' +
            '<div class="cd-field">' +
                '<div class="cd-field-label"><i class="bi bi-person"></i> Created by</div>' +
                '<div class="cd-field-value"><div class="d-flex align-items-center">' +
                    '<img src="assets/img/crews/' + (c.creatorPic||'no_pic.png') + '" class="cd-avatar mr-2">' +
                    '<span>' + esc(c.creatorName||'Unknown') + '</span>' +
                '</div></div>' +
            '</div>' +
        '</div>' +
        /* Bottom section: stage, dates, etc */
        '<div class="cd-overview-bottom">' +
            '<div class="cd-field-inline">' +
                '<div class="cd-field-label"><i class="bi bi-layers"></i> Stage</div>' +
                '<div class="cd-field-value">' + mtStageBadge(c.cStage||'Draft') +
                    '<select class="cd-select-inline" onchange="updateCardField(' + c.cID + ',\'cStage\',this.value)">' + stageSelect + '</select>' +
                '</div>' +
            '</div>' +
            '<div class="cd-field-inline">' +
                '<div class="cd-field-label"><i class="bi bi-calendar-event"></i> Due Date</div>' +
                '<div class="cd-field-value"><input type="date" class="cd-input" value="' + (c.cDueDate||'') + '" onchange="updateCardField(' + c.cID + ',\'cDueDate\',this.value)"></div>' +
            '</div>' +
            '<div class="cd-field-inline">' +
                '<div class="cd-field-label"><i class="bi bi-calendar-check"></i> Completion Date</div>' +
                '<div class="cd-field-value"><input type="date" class="cd-input" value="' + (c.cCompletedAt||'') + '" onchange="updateCardField(' + c.cID + ',\'cCompletedAt\',this.value)"></div>' +
            '</div>' +
            '<div class="cd-field-inline">' +
                '<div class="cd-field-label"><i class="bi bi-clock"></i> Creation at</div>' +
                '<div class="cd-field-value"><div class="d-flex align-items-center">' +
                    '<img src="assets/img/crews/' + (c.creatorPic||'no_pic.png') + '" class="cd-avatar-sm mr-2">' +
                    '<span>' + formatDateTime(c.cCreatedAt) + '</span>' +
                '</div></div>' +
            '</div>' +
            '<div class="cd-field-inline">' +
                '<div class="cd-field-label"><i class="bi bi-arrow-clockwise"></i> Last updated</div>' +
                '<div class="cd-field-value">' + (c.cUpdatedAt ? formatDateTime(c.cUpdatedAt) : '-') + '</div>' +
            '</div>' +
        '</div>' +
        /* Sub-items */
        '<div class="cd-subitems-section">' +
            '<div class="cd-field-label" style="margin-bottom:8px;"><i class="bi bi-diagram-3"></i> Sub-items</div>' +
            buildSubitemsHtml(c) +
        '</div>' +
        /* Delete button */
        '<div style="margin-top:16px;text-align:right;">' +
            '<button class="btn btn-outline-danger btn-sm" onclick="deleteCard(' + c.cID + ')"><i class="bi bi-trash mr-1"></i> Delete Card</button>' +
        '</div>' +
    '</div>';
    $('#cdPanelOverview').html(html);
}

/* ---- UPDATES TAB ---- */
function buildUpdatesPanel(c){
    var commentsHtml = '';
    var comments = c.comments || [];
    if(comments.length === 0){
        commentsHtml = '<div class="cd-empty-state"><i class="bi bi-chat-left-text" style="font-size:32px;color:#555;"></i><p style="color:#9ca3af;margin-top:8px;">No updates yet. Write the first one!</p></div>';
    } else {
        comments.slice().reverse().forEach(function(cm){
            var deleteBtn = cm.sID == MY_ID
                ? '<button class="cd-update-action" onclick="deleteComment(' + cm.ccID + ',' + c.cID + ')" title="Delete"><i class="bi bi-three-dots"></i></button>' : '';
            commentsHtml +=
                '<div class="cd-update-item">' +
                    '<div class="cd-update-header">' +
                        '<img src="assets/img/crews/' + (cm.sPic||'no_pic.png') + '" class="cd-avatar mr-2">' +
                        '<strong class="cd-update-author">' + esc(cm.sNickName) + '</strong>' +
                        '<span class="cd-update-time">' + timeAgo(cm.ccCreatedAt) + '</span>' +
                        deleteBtn +
                    '</div>' +
                    '<div class="cd-update-body">' + escNl(cm.ccText) + '</div>' +
                    '<div class="cd-update-footer">' +
                        '<span class="cd-update-btn"><i class="bi bi-hand-thumbs-up"></i> Like</span>' +
                        '<span class="cd-update-btn"><i class="bi bi-reply"></i> Reply</span>' +
                    '</div>' +
                '</div>';
        });
    }

    var html =
        '<div class="cd-updates">' +
            '<div class="cd-update-compose">' +
                '<div class="cd-compose-box">' +
                    '<textarea class="cd-compose-input" id="newComment_' + c.cID + '" placeholder="Write an update and mention others with @" rows="3"></textarea>' +
                    '<div class="cd-compose-toolbar">' +
                        '<button class="btn btn-primary btn-sm" onclick="addComment(' + c.cID + ')">Update</button>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="cd-updates-list">' + commentsHtml + '</div>' +
        '</div>';
    $('#cdPanelUpdates').html(html);
}

/* ---- ACTIVITY LOG TAB ---- */
function loadCardActivities(cID){
    $('#cdPanelActivity').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin" style="color:#9ca3af;"></i></div>');
    $.post('api/l4utask/cards.php', { act: 'getActivities', cID: cID }, function(res){
        if(res.status !== 'success') return;
        buildActivityPanel(res.data, 'cdPanelActivity');
    },'json');
}

function buildActivityPanel(activities, panelId){
    if(!activities || activities.length === 0){
        $('#' + panelId).html('<div class="cd-empty-state"><i class="bi bi-clock-history" style="font-size:32px;color:#555;"></i><p style="color:#9ca3af;margin-top:8px;">No activity yet</p></div>');
        return;
    }
    var html = '<div class="cd-activity-list">' +
        '<div class="cd-activity-header-bar">' +
            '<span class="cd-activity-filter"><i class="bi bi-funnel"></i> Filter log</span>' +
        '</div>';

    activities.forEach(function(a){
        var icon = 'bi-pencil';
        var fieldLabel = esc(a.caField || '');
        if(a.caType === 'created'){ icon = 'bi-plus-circle'; fieldLabel = 'Created'; }
        else if(a.caType === 'member_added'){ icon = 'bi-person-plus'; fieldLabel = 'Added Owner'; }
        else if(a.caType === 'member_removed'){ icon = 'bi-person-dash'; fieldLabel = 'Removed Owner'; }
        else if(a.caField === 'Status' || a.caField === 'Stage'){ icon = 'bi-bar-chart'; }
        else if(a.caField === 'Priority'){ icon = 'bi-flag'; }

        var valueHtml = '';
        if(a.caType === 'field_change' && (a.caField === 'Stage' || a.caField === 'Status')){
            var oldBadge = a.caOldValue ? mtStageBadge(a.caOldValue) : '<span class="cd-act-empty"></span>';
            var newBadge = a.caNewValue ? mtStageBadge(a.caNewValue) : '<span class="cd-act-empty"></span>';
            valueHtml = oldBadge + '<i class="bi bi-chevron-right cd-act-arrow"></i>' + newBadge;
        } else if(a.caType === 'member_added' || a.caType === 'member_removed'){
            valueHtml = '<span class="cd-act-value">' + esc(a.caNewValue || a.caOldValue || '') + '</span>';
        } else if(a.caType === 'created'){
            valueHtml = '<span class="cd-act-value">Group: ' + esc(a.caNewValue || '') + '</span>';
        } else {
            if(a.caOldValue) valueHtml += '<span class="cd-act-old">' + esc(a.caOldValue) + '</span><i class="bi bi-chevron-right cd-act-arrow"></i>';
            valueHtml += '<span class="cd-act-value">' + esc(a.caNewValue || '-') + '</span>';
        }

        html += '<div class="cd-activity-row">' +
            '<span class="cd-act-time">' + timeAgo(a.caCreatedAt) + '</span>' +
            '<img src="assets/img/crews/' + (a.sPic||'no_pic.png') + '" class="cd-avatar-sm">' +
            '<span class="cd-act-name">' + esc(a.sNickName||'') + '</span>' +
            '<span class="cd-act-icon"><i class="bi ' + icon + '"></i></span>' +
            '<span class="cd-act-field">' + fieldLabel + '</span>' +
            '<div class="cd-act-values">' + valueHtml + '</div>' +
        '</div>';
    });

    html += '</div>';
    $('#' + panelId).html(html);
}

/* ===================== CARD ACTIONS ===================== */
function updateCardField(cID, field, value){
    var data = { act: 'updateCard', cID: cID };
    data[field] = value;
    $.post('api/l4utask/cards.php', data, function(res){
        if(field === 'cTitle') $('#cardDetailTitle').text(value);
    },'json');
}

function setCardColor(cID, el, color){
    $(el).siblings().removeClass('active');
    $(el).addClass('active');
    updateCardField(cID, 'cColor', color);
}

function deleteCard(cID){
    if(!confirm('Delete this card?')) return;
    $.post('api/l4utask/cards.php', { act: 'deleteCard', cID: cID }, function(res){
        if(res.status === 'success'){
            $('#modalCardDetail').modal('hide');
            reloadCurrentView();
        }
    },'json');
}

/* ===================== MEMBERS ===================== */
function loadStaffs(){
    $.post('api/l4utask/cards.php', { act: 'getStaffs' }, function(res){
        if(res.status === 'success') allStaffs = res.data;
    },'json');
}

function assignMember(cID){
    var sel = $('#addMemberSelect_' + cID);
    var sID = sel.val();
    if(!sID) return;
    $.post('api/l4utask/cards.php', { act: 'assignMember', cID: cID, sID: sID }, function(res){
        if(res.status === 'success') openCardDetail(cID);
    },'json');
}

function removeMember(cID, sID){
    $.post('api/l4utask/cards.php', { act: 'removeMember', cID: cID, sID: sID }, function(res){
        if(res.status === 'success') $('#memberRow_' + sID).fadeOut(200, function(){ $(this).remove(); });
    },'json');
}

/* ===================== COMMENTS ===================== */
function addComment(cID){
    var text = $('#newComment_' + cID).val().trim();
    if(!text) return;
    $.post('api/l4utask/cards.php', { act: 'addComment', cID: cID, ccText: text }, function(res){
        if(res.status === 'success') openCardDetail(cID);
    },'json');
}

function deleteComment(ccID, cID){
    if(!confirm('Delete this comment?')) return;
    $.post('api/l4utask/cards.php', { act: 'deleteComment', ccID: ccID }, function(res){
        if(res.status === 'success') openCardDetail(cID);
    },'json');
}

/* ===================== SUBITEMS ===================== */
function buildSubitemsHtml(c){
    var subs = c.subitems || [];
    var total = subs.length;
    var done = subs.filter(function(s){ return s.siStatus === 'Done'; }).length;
    var pct = total > 0 ? Math.round(done/total*100) : 0;

    var progressHtml = total > 0
        ? '<div class="cd-si-progress"><span>' + done + '/' + total + '</span><div class="cd-si-bar"><div class="cd-si-bar-fill" style="width:' + pct + '%"></div></div><span>' + pct + '%</span></div>'
        : '';

    var listHtml = '';
    subs.forEach(function(si){
        var isDone = si.siStatus === 'Done';
        var assigneeHtml = si.assigneeName
            ? '<img src="assets/img/crews/' + (si.assigneePic||'no_pic.png') + '" class="cd-avatar-sm" title="' + esc(si.assigneeName) + '">'
            : '';
        var statusBadge = mtStageBadge(si.siStatus || 'Pending');

        listHtml += '<div class="cd-si-row ' + (isDone?'cd-si-done':'') + '" id="subitem_' + si.siID + '">' +
            '<input type="checkbox" class="cd-si-check" ' + (isDone?'checked':'') + ' onchange="toggleSubitem(' + si.siID + ',' + c.cID + ',this.checked)">' +
            '<span class="cd-si-title ' + (isDone?'cd-si-strikethrough':'') + '" onclick="openSubitemDetail(' + si.siID + ')">' + esc(si.siTitle) + '</span>' +
            '<span class="cd-si-status">' + statusBadge + '</span>' +
            '<span class="cd-si-assignee">' + assigneeHtml + '</span>' +
            '<div class="cd-si-actions">' +
                '<button class="cd-si-action-btn" onclick="deleteSubitem(' + si.siID + ',' + c.cID + ')" title="Delete"><i class="bi bi-x"></i></button>' +
            '</div>' +
        '</div>';
    });

    return progressHtml +
        '<div id="subitemsList_' + c.cID + '">' + listHtml + '</div>' +
        '<div class="cd-si-add">' +
            '<input type="text" class="cd-si-add-input" id="newSubitemTitle_' + c.cID + '" placeholder="+ Add sub-item" maxlength="500" onkeydown="if(event.key===\'Enter\')addSubitem(' + c.cID + ')">' +
        '</div>';
}

function addSubitem(cID){
    var input = $('#newSubitemTitle_' + cID);
    var title = input.val().trim();
    if(!title) return;
    $.post('api/l4utask/cards.php', { act: 'addSubitem', cID: cID, siTitle: title }, function(res){
        if(res.status === 'success') openCardDetail(cID);
    },'json');
}

function toggleSubitem(siID, cID, checked){
    var status = checked ? 'Done' : 'Pending';
    $.post('api/l4utask/cards.php', { act: 'toggleSubitem', siID: siID, siStatus: status }, function(res){
        if(res.status === 'success') openCardDetail(cID);
    },'json');
}

function deleteSubitem(siID, cID){
    if(!confirm('Delete this sub-item?')) return;
    $.post('api/l4utask/cards.php', { act: 'deleteSubitem', siID: siID }, function(res){
        if(res.status === 'success') openCardDetail(cID);
    },'json');
}

/* ===================== SUBITEM DETAIL MODAL ===================== */
function switchSiTab(tab, btn){
    $('#modalSubitemDetail .cd-tabs .cd-tab').removeClass('active');
    $(btn).addClass('active');
    $('#sdPanelOverview, #sdPanelActivity').addClass('d-none');
    if(tab === 'si-overview') $('#sdPanelOverview').removeClass('d-none');
    else if(tab === 'si-activity') {
        $('#sdPanelActivity').removeClass('d-none');
    }
}

function openSubitemDetail(siID){
    $.post('api/l4utask/cards.php', { act: 'getSubitem', siID: siID }, function(res){
        if(res.status !== 'success') return;
        var si = res.data;
        $('#sdItemID').text('#SI-' + si.siID);
        $('#subitemDetailTitle').text(si.siTitle);
        // Reset tabs
        $('#modalSubitemDetail .cd-tabs .cd-tab').removeClass('active').first().addClass('active');
        $('#sdPanelOverview').removeClass('d-none');
        $('#sdPanelActivity').addClass('d-none');

        var staffOpts = allStaffs.map(function(s){ return '<option value="' + s.sID + '" ' + (parseInt(si.siAssignee)==s.sID?'selected':'') + '>' + esc(s.sNickName) + '</option>'; }).join('');
        var statusOpts = ['Pending','In Progress','Review','Need Fix','Done'].map(function(s){
            return '<option value="' + s + '" ' + ((si.siStatus||'Pending')===s?'selected':'') + '>' + s + '</option>';
        }).join('');
        var priOpts = ['','Normal','High','Critical'].map(function(p){
            return '<option value="' + p + '" ' + ((si.siPriority||'')===p?'selected':'') + '>' + (p||'None') + '</option>';
        }).join('');

        var html = '<div class="cd-overview">' +
            '<div class="cd-overview-grid">' +
                '<div class="cd-field">' +
                    '<div class="cd-field-label"><i class="bi bi-card-heading"></i> Parent Card</div>' +
                    '<div class="cd-field-value">' + esc(si.cardTitle) + '</div>' +
                '</div>' +
                '<div class="cd-field">' +
                    '<div class="cd-field-label"><i class="bi bi-person"></i> Assignee</div>' +
                    '<div class="cd-field-value"><select class="cd-select" onchange="updateSubitemField(' + si.siID + ',' + si.cID + ',\'siAssignee\',this.value)"><option value="">Unassigned</option>' + staffOpts + '</select></div>' +
                '</div>' +
                '<div class="cd-field">' +
                    '<div class="cd-field-label"><i class="bi bi-bar-chart"></i> Status</div>' +
                    '<div class="cd-field-value"><select class="cd-select" onchange="updateSubitemField(' + si.siID + ',' + si.cID + ',\'siStatus\',this.value)">' + statusOpts + '</select></div>' +
                '</div>' +
                '<div class="cd-field">' +
                    '<div class="cd-field-label"><i class="bi bi-flag"></i> Priority</div>' +
                    '<div class="cd-field-value"><select class="cd-select" onchange="updateSubitemField(' + si.siID + ',' + si.cID + ',\'siPriority\',this.value)">' + priOpts + '</select></div>' +
                '</div>' +
            '</div>' +
            '<div class="cd-overview-bottom">' +
                '<div class="cd-field-inline">' +
                    '<div class="cd-field-label"><i class="bi bi-calendar-event"></i> Due Date</div>' +
                    '<div class="cd-field-value"><input type="date" class="cd-input" value="' + (si.siDueDate||'') + '" onchange="updateSubitemField(' + si.siID + ',' + si.cID + ',\'siDueDate\',this.value)"></div>' +
                '</div>' +
                '<div class="cd-field-inline">' +
                    '<div class="cd-field-label"><i class="bi bi-calendar-check"></i> Completed</div>' +
                    '<div class="cd-field-value"><input type="date" class="cd-input" value="' + (si.siCompletedAt||'') + '" onchange="updateSubitemField(' + si.siID + ',' + si.cID + ',\'siCompletedAt\',this.value)"></div>' +
                '</div>' +
                '<div class="cd-field-inline">' +
                    '<div class="cd-field-label"><i class="bi bi-clock"></i> Created</div>' +
                    '<div class="cd-field-value">' + formatDateTime(si.siCreatedAt) + '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
        $('#sdPanelOverview').html(html);
        // Build activity
        buildActivityPanel(si.activities || [], 'sdPanelActivity');
        $('#modalSubitemDetail').modal('show');
    },'json');
}

function updateSubitemField(siID, cID, field, value){
    var data = { act: 'updateSubitem', siID: siID };
    data[field] = value;
    $.post('api/l4utask/cards.php', data, function(res){},'json');
}

/* ===================== UTILS ===================== */
function esc(s){
    if(!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
function escNl(s){
    if(!s) return '';
    return esc(s).replace(/\n/g, '<br>');
}
function formatDate(d){
    if(!d) return '';
    var dt = new Date(d);
    return dt.toLocaleDateString('en-GB', { day:'2-digit', month:'short' });
}
function formatDateTime(d){
    if(!d) return '';
    var dt = new Date(d);
    return dt.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
}
function isDueOverdue(d){
    if(!d) return false;
    return new Date(d) < new Date(new Date().toDateString());
}
function timeAgo(d){
    if(!d) return '';
    var now = new Date();
    var then = new Date(d);
    var diff = Math.floor((now - then) / 1000);
    if(diff < 60) return 'Just now';
    if(diff < 3600) return Math.floor(diff/60) + 'm';
    if(diff < 86400) return Math.floor(diff/3600) + 'h';
    var days = Math.floor(diff/86400);
    if(days === 1) return '1d';
    if(days < 30) return days + 'd';
    return formatDate(d);
}

/* ===================== CSV UPLOAD ===================== */
var selectedCSVFiles = [];

function showCSVUploadModal(){
    $('#modalCSVUpload').modal('show');
    loadCSVUploads();
    setupCSVUploadEvents();
}

function setupCSVUploadEvents(){
    var $dropZone = $('#csvDropZone');
    var $fileInput = $('#csvFileInput');

    // Click to browse
    $dropZone.off('click').on('click', function(){
        $fileInput.click();
    });

    // File selection
    $fileInput.off('change').on('change', function(e){
        handleFileSelection(e.target.files);
    });

    // Drag and drop
    $dropZone.off('dragover dragenter dragleave drop').on('dragover dragenter', function(e){
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('csv-dragover');
    }).on('dragleave', function(e){
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('csv-dragover');
    }).on('drop', function(e){
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('csv-dragover');
        handleFileSelection(e.originalEvent.dataTransfer.files);
    });
}

function handleFileSelection(files){
    selectedCSVFiles = [];
    var $fileList = $('#csvFileList');
    $fileList.empty();

    for(var i = 0; i < files.length; i++){
        var file = files[i];
        var fileName = file.name.toLowerCase();
        var isValidFile = file.type === 'text/csv' || fileName.endsWith('.csv') || 
                         fileName.endsWith('.xlsx') || fileName.endsWith('.xls');
        
        if(isValidFile){
            selectedCSVFiles.push(file);
            var fileIcon = fileName.endsWith('.xlsx') || fileName.endsWith('.xls') 
                ? 'bi-file-earmark-excel text-success' 
                : 'bi-file-earmark-text text-success';
            
            $fileList.append(
                '<div class="csv-file-item">' +
                    '<i class="bi ' + fileIcon + ' mr-2"></i>' +
                    '<span>' + esc(file.name) + '</span>' +
                    '<span class="text-muted ml-2">(' + formatFileSize(file.size) + ')</span>' +
                '</div>'
            );
        }
    }

    $('#btnUploadCSV').prop('disabled', selectedCSVFiles.length === 0);
}

function formatFileSize(bytes){
    if(bytes === 0) return '0 Bytes';
    var k = 1024;
    var sizes = ['Bytes', 'KB', 'MB', 'GB'];
    var i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function uploadCSVFiles(){
    if(selectedCSVFiles.length === 0) return;

    var formData = new FormData();
    formData.append('act', 'uploadCSV');
    formData.append('bID', BOARD_ID);
    
    for(var i = 0; i < selectedCSVFiles.length; i++){
        formData.append('csvFiles[]', selectedCSVFiles[i]);
    }

    // Show progress section
    $('#csvProgressSection').show();
    $('#csvProgressList').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Uploading files...</div>');

    $.ajax({
        url: 'api/l4utask/csv.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res){
            if(res.status === 'success'){
                showUploadProgress(res.uploadedFiles);
                loadCSVUploads();
                // Reset file selection
                selectedCSVFiles = [];
                $('#csvFileList').empty();
                $('#btnUploadCSV').prop('disabled', true);
                $('#csvFileInput').val('');
                
                // Reload board data after a delay
                setTimeout(function(){
                    reloadCurrentView();
                }, 2000);
            } else {
                alert('Upload failed: ' + (res.msg || 'Unknown error'));
            }
        },
        error: function(){
            alert('Upload failed due to server error');
        }
    });
}

function showUploadProgress(files){
    var $progressList = $('#csvProgressList');
    $progressList.empty();

    files.forEach(function(file){
        var statusClass = file.status === 'uploading' ? 'text-warning' : 'text-success';
        var statusIcon = file.status === 'uploading' ? 'spinner fa-spin' : 'check-circle';
        var fileName = file.originalName.toLowerCase();
        var fileIcon = fileName.endsWith('.xlsx') || fileName.endsWith('.xls') 
            ? 'bi-file-earmark-excel' 
            : 'bi-file-earmark-text';
        
        $progressList.append(
            '<div class="csv-progress-item">' +
                '<i class="bi ' + fileIcon + ' mr-2"></i>' +
                '<span>' + esc(file.originalName) + '</span>' +
                '<span class="ml-auto ' + statusClass + '">' +
                    '<i class="fas fa-' + statusIcon + ' mr-1"></i>' +
                    file.status.charAt(0).toUpperCase() + file.status.slice(1) +
                '</span>' +
            '</div>'
        );
    });
}

function loadCSVUploads(){
    $.post('api/l4utask/csv.php', { act: 'getUploadStatus', bID: BOARD_ID }, function(res){
        if(res.status === 'success'){
            displayUploadHistory(res.data);
        }
    },'json');
}

function displayUploadHistory(uploads){
    var $tbody = $('#csvUploadsTable tbody');
    $tbody.empty();

    if(uploads.length === 0){
        $tbody.append('<tr><td colspan="5" class="text-center text-muted">No uploads yet</td></tr>');
        return;
    }

    uploads.forEach(function(upload){
        var statusBadge = getStatusBadge(upload.csvStatus);
        var recordsText = upload.csvRecordsTotal > 0 
            ? upload.csvRecordsProcessed + '/' + upload.csvRecordsTotal 
            : '-';
        
        $tbody.append(
            '<tr>' +
                '<td>' + esc(upload.csvOriginalName) + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td>' + recordsText + '</td>' +
                '<td>' + formatDateTime(upload.csvCreatedAt) + '</td>' +
                '<td class="mt-col-actions">' +
                    '<button class="btn btn-sm btn-outline-light mt-expand-btn" onclick="toggleSubitems(\'sub_' + upload.csvID + '\', this)" title="Toggle subitems">' +
                        '<i class="bi bi-chevron-right"></i>' +
                    '</button>' +
                    '<div class="dropdown ml-1">' +
                        '<button class="btn btn-sm btn-light mt-card-menu-btn" data-toggle="dropdown" onclick="event.stopPropagation();" title="More options">' +
                            '<i class="bi bi-three-dots"></i>' +
                        '</button>' +
                        '<div class="dropdown-menu dropdown-menu-right">' +
                            '<a class="dropdown-item" href="#" onclick="event.stopPropagation(); editCSVUpload(' + upload.csvID + '); return false;"><i class="bi bi-pencil mr-2"></i>Edit Upload</a>' +
                            '<a class="dropdown-item" href="#" onclick="event.stopPropagation(); duplicateCSVUpload(' + upload.csvID + '); return false;"><i class="bi bi-copy mr-2"></i>Duplicate Upload</a>' +
                            '<a class="dropdown-item" href="#" onclick="event.stopPropagation(); moveCSVUpload(' + upload.csvID + '); return false;"><i class="bi bi-arrows-move mr-2"></i>Move Upload</a>' +
                            '<div class="dropdown-divider"></div>' +
                            '<a class="dropdown-item text-danger" href="#" onclick="event.stopPropagation(); deleteCSVUpload(' + upload.csvID + '); return false;"><i class="bi bi-trash mr-2"></i>Delete Upload</a>' +
                        '</div>' +
                    '</div>' +
                '</td>' +
            '</tr>'
        );
    });
}

function getStatusBadge(status){
    var badges = {
        'uploading': '<span class="badge badge-warning">Uploading</span>',
        'processing': '<span class="badge badge-info">Processing</span>',
        'completed': '<span class="badge badge-success">Completed</span>',
        'failed': '<span class="badge badge-danger">Failed</span>'
    };
    return badges[status] || '<span class="badge badge-secondary">Unknown</span>';
}

function deleteCSVUpload(csvID){
    if(!confirm('Delete this upload record?')) return;
    
    $.post('api/l4utask/csv.php', { act: 'deleteUpload', csvID: csvID }, function(res){
        if(res.status === 'success'){
            loadCSVUploads();
        } else {
            alert('Delete failed: ' + (res.msg || 'Unknown error'));
        }
    },'json');
}

/* ===================== SEARCH ===================== */
function searchBoard(query){
    if(!query || query.length < 2){
        renderCurrentView();
        return;
    }
    
    var searchTerm = query.toLowerCase();
    var filteredData = [];
    
    cachedData.forEach(function(list){
        var filteredList = {
            lID: list.lID,
            lName: list.lName,
            cards: []
        };
        
        // Filter cards and subitems
        (list.cards || []).forEach(function(card){
            var cardMatches = card.cTitle && card.cTitle.toLowerCase().includes(searchTerm);
            var descMatches = card.cDescription && card.cDescription.toLowerCase().includes(searchTerm);
            
            // Check subitems
            var filteredSubitems = [];
            (card.subitems || []).forEach(function(subitem){
                if(subitem.siTitle && subitem.siTitle.toLowerCase().includes(searchTerm)){
                    filteredSubitems.push(subitem);
                    cardMatches = true; // Show card if subitem matches
                }
            });
            
            // Include card if it matches or has matching subitems
            if(cardMatches || descMatches || filteredSubitems.length > 0){
                var filteredCard = Object.assign({}, card);
                if(filteredSubitems.length > 0){
                    filteredCard.subitems = filteredSubitems;
                }
                filteredList.cards.push(filteredCard);
            }
        });
        
        // Only add list if it has matching cards
        if(filteredList.cards.length > 0){
            filteredData.push(filteredList);
        }
    });
    
    // Render filtered data
    if(currentView === 'kanban'){
        renderKanbanView(filteredData);
    } else if(currentView === 'maintable'){
        renderMainTableView(filteredData);
    }
}

function renderCurrentView(){
    if(currentView === 'kanban'){
        renderKanbanView();
    } else if(currentView === 'maintable'){
        renderMainTableView();
    }
}

/* ===================== LIST MENU FUNCTIONS ===================== */
function editListTitleByName(lID){
    // Find the list name element and trigger edit
    var $nameEl = $('.mt-group-name-monday').filter(function(){
        return $(this).attr('ondblclick').indexOf(lID) > -1;
    });
    if($nameEl.length){
        editListTitle($nameEl[0], lID);
    }
}

function duplicateList(lID){
    if(!confirm('Duplicate this list and all its cards?')) return;
    
    $.post('api/l4utask/lists.php', { act: 'duplicateList', lID: lID }, function(res){
        if(res.status === 'success'){
            reloadCurrentView();
        } else {
            alert('Duplicate failed: ' + (res.msg || 'Unknown error'));
        }
    },'json');
}

function deleteList(lID){
    if(!confirm('Delete this list and all its cards? This cannot be undone!')) return;
    
    $.post('api/l4utask/lists.php', { act: 'deleteList', lID: lID }, function(res){
        if(res.status === 'success'){
            reloadCurrentView();
        } else {
            alert('Delete failed: ' + (res.msg || 'Unknown error'));
        }
    },'json');
}

/* ===================== CARD MENU FUNCTIONS ===================== */
function editCard(cID){
    openCardDetail(cID);
}

function duplicateCard(cID){
    if(!confirm('Duplicate this card and all its subitems?')) return;
    
    $.post('api/l4utask/cards.php', { act: 'duplicateCard', cID: cID }, function(res){
        if(res.status === 'success'){
            reloadCurrentView();
        } else {
            alert('Duplicate failed: ' + (res.msg || 'Unknown error'));
        }
    },'json');
}

function moveCardToTop(cID){
    $.post('api/l4utask/cards.php', { act: 'moveCardToTop', cID: cID }, function(res){
        if(res.status === 'success'){
            reloadCurrentView();
        } else {
            alert('Move failed: ' + (res.msg || 'Unknown error'));
        }
    },'json');
}

function moveCardToGroup(cID){
    // Build group selection HTML
    var html = '<div style="max-height:300px;overflow-y:auto;">';
    cachedData.forEach(function(list){
        html += '<a href="#" class="dropdown-item" onclick="doMoveCardToGroup(' + cID + ',' + list.lID + '); return false;">' +
            '<i class="bi bi-collection mr-2"></i>' + esc(list.lName) + ' (' + (list.cards||[]).length + ' tasks)</a>';
    });
    html += '</div>';
    
    // Show modal
    showMoveModal('Move to Group', html);
}

function doMoveCardToGroup(cID, lID){
    closeMoveModal();
    $.post('api/l4utask/cards.php', { act: 'moveCard', cID: cID, lID: lID }, function(res){
        if(res.status === 'success'){
            reloadCurrentView();
        } else {
            alert('Move failed: ' + (res.msg || 'Unknown error'));
        }
    },'json');
}

function moveCardToBoard(cID){
    // Step 1: Load all boards
    $.get('api/l4utask/boards.php', { act: 'getBoards' }, function(res){
        if(res.status === 'success'){
            var html = '<div style="max-height:300px;overflow-y:auto;" id="moveBoardList">';
            (res.boards || res.data || []).forEach(function(b){
                html += '<a href="#" class="dropdown-item" onclick="loadBoardGroups(' + cID + ',' + b.bID + '); return false;">' +
                    '<i class="bi bi-kanban mr-2"></i>' + esc(b.bName) + '</a>';
            });
            html += '</div>';
            showMoveModal('Move to Board', html);
        } else {
            alert('Failed to load boards');
        }
    },'json');
}

function loadBoardGroups(cID, bID){
    $.get('api/l4utask/lists.php', { act: 'getLists', bID: bID }, function(res){
        if(res.status === 'success'){
            var html = '<div style="max-height:300px;overflow-y:auto;">';
            html += '<p class="px-3 py-1 mb-1 text-muted small"><i class="bi bi-arrow-left mr-1"></i><a href="#" onclick="moveCardToBoard(' + cID + '); return false;">Back to boards</a></p>';
            (res.lists || res.data || []).forEach(function(list){
                html += '<a href="#" class="dropdown-item" onclick="doMoveCardToBoard(' + cID + ',' + list.lID + '); return false;">' +
                    '<i class="bi bi-collection mr-2"></i>' + esc(list.lName) + '</a>';
            });
            html += '</div>';
            $('#moveModalBody').html(html);
        }
    },'json');
}

function doMoveCardToBoard(cID, lID){
    closeMoveModal();
    $.post('api/l4utask/cards.php', { act: 'moveCard', cID: cID, lID: lID }, function(res){
        if(res.status === 'success'){
            reloadCurrentView();
        } else {
            alert('Move failed: ' + (res.msg || 'Unknown error'));
        }
    },'json');
}

function showMoveModal(title, bodyHtml){
    // Remove existing modal
    $('#moveCardModal').remove();
    
    var modal = $(
        '<div class="modal fade" id="moveCardModal" tabindex="-1">' +
            '<div class="modal-dialog modal-sm">' +
                '<div class="modal-content" style="background:#2a2e4a;color:#e0e0e0;border:1px solid #3a3f6a;">' +
                    '<div class="modal-header" style="border-bottom:1px solid #3a3f6a;padding:10px 15px;">' +
                        '<h6 class="modal-title">' + title + '</h6>' +
                        '<button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>' +
                    '</div>' +
                    '<div class="modal-body p-0" id="moveModalBody">' + bodyHtml + '</div>' +
                '</div>' +
            '</div>' +
        '</div>'
    );
    $('body').append(modal);
    modal.modal('show');
}

function closeMoveModal(){
    $('#moveCardModal').modal('hide');
    setTimeout(function(){ $('#moveCardModal').remove(); }, 300);
}

/* ===================== CHECKBOX FUNCTIONS ===================== */
var selectedCards = [];

function toggleCardSelect(cID){
    var idx = selectedCards.indexOf(cID);
    if(idx > -1){
        selectedCards.splice(idx, 1);
    } else {
        selectedCards.push(cID);
    }
    updateBulkActionBar();
}

function toggleSelectAll(checkbox, lID){
    var checked = checkbox.checked;
    $(checkbox).closest('table').find('.mt-card-checkbox').prop('checked', checked);
    
    if(checked){
        $(checkbox).closest('table').find('.mt-card-checkbox[data-cid]').each(function(){
            var cid = parseInt($(this).data('cid'));
            if(selectedCards.indexOf(cid) === -1) selectedCards.push(cid);
        });
    } else {
        $(checkbox).closest('table').find('.mt-card-checkbox[data-cid]').each(function(){
            var cid = parseInt($(this).data('cid'));
            var idx = selectedCards.indexOf(cid);
            if(idx > -1) selectedCards.splice(idx, 1);
        });
    }
    updateBulkActionBar();
}

function updateBulkActionBar(){
    if(selectedCards.length > 0){
        if($('#bulkActionBar').length === 0){
            $('body').append(
                '<div id="bulkActionBar" class="bulk-action-bar">' +
                    '<span class="bulk-count">' + selectedCards.length + ' selected</span>' +
                    '<button class="btn btn-sm btn-outline-light ml-2" onclick="bulkDelete()"><i class="bi bi-trash mr-1"></i>Delete</button>' +
                    '<button class="btn btn-sm btn-outline-light ml-2" onclick="bulkMove()"><i class="bi bi-arrows-move mr-1"></i>Move</button>' +
                    '<button class="btn btn-sm btn-outline-light ml-2" onclick="clearSelection()"><i class="bi bi-x mr-1"></i>Clear</button>' +
                '</div>'
            );
        } else {
            $('#bulkActionBar .bulk-count').text(selectedCards.length + ' selected');
        }
    } else {
        $('#bulkActionBar').remove();
    }
}

function clearSelection(){
    selectedCards = [];
    $('.mt-card-checkbox').prop('checked', false);
    $('.mt-select-all').prop('checked', false);
    $('#bulkActionBar').remove();
}

function bulkDelete(){
    if(!confirm('Delete ' + selectedCards.length + ' cards? This cannot be undone!')) return;
    var promises = selectedCards.map(function(cID){
        return $.post('api/l4utask/cards.php', { act: 'deleteCard', cID: cID });
    });
    $.when.apply($, promises).then(function(){
        clearSelection();
        reloadCurrentView();
    });
}

function bulkMove(){
    var html = '<div style="max-height:300px;overflow-y:auto;">';
    cachedData.forEach(function(list){
        html += '<a href="#" class="dropdown-item" onclick="doBulkMove(' + list.lID + '); return false;">' +
            '<i class="bi bi-collection mr-2"></i>' + esc(list.lName) + '</a>';
    });
    html += '</div>';
    showMoveModal('Move ' + selectedCards.length + ' cards to...', html);
}

function doBulkMove(lID){
    closeMoveModal();
    var promises = selectedCards.map(function(cID){
        return $.post('api/l4utask/cards.php', { act: 'moveCard', cID: cID, lID: lID });
    });
    $.when.apply($, promises).then(function(){
        clearSelection();
        reloadCurrentView();
    });
}

function deleteCard(cID){
    if(!confirm('Delete this card and all its subitems? This cannot be undone!')) return;
    
    $.post('api/l4utask/cards.php', { act: 'deleteCard', cID: cID }, function(res){
        if(res.status === 'success'){
            reloadCurrentView();
        } else {
            alert('Delete failed: ' + (res.msg || 'Unknown error'));
        }
    },'json');
}

/* ===================== MANUAL DROPDOWN FUNCTIONS ===================== */
function toggleCardMenu(cID, button) {
    var menu = document.getElementById('card-menu-' + cID);
    var allMenus = document.querySelectorAll('.dropdown-menu');
    
    // Close all other menus
    allMenus.forEach(function(m) {
        if (m.id !== 'card-menu-' + cID) {
            m.style.display = 'none';
        }
    });
    
    // Toggle current menu
    if (menu.style.display === 'none' || menu.style.display === '') {
        menu.style.display = 'block';
        // Position the menu
        var rect = button.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.left = rect.left + 'px';
        menu.style.top = (rect.bottom + 2) + 'px';
        menu.style.zIndex = '1000';
    } else {
        menu.style.display = 'none';
    }
}

function closeCardMenu(cID) {
    var menu = document.getElementById('card-menu-' + cID);
    if (menu) {
        menu.style.display = 'none';
    }
}

// Close menus when clicking outside
$(document).on('click', function(e) {
    if (!$(e.target).closest('.dropdown').length) {
        $('.dropdown-menu').hide();
    }
});

// Close menus when pressing Escape
$(document).on('keydown', function(e) {
    if (e.key === 'Escape') {
        $('.dropdown-menu').hide();
    }
});

// Touch support for dropdown submenu
if (isTouchDevice) {
    $(document).on('touchstart', '.has-submenu', function(e) {
        e.stopPropagation();
        var $this = $(this);
        var $submenu = $this.find('.dropdown-submenu');
        
        // Toggle submenu visibility
        if ($submenu.is(':visible')) {
            $submenu.hide();
            $this.removeClass('active');
        } else {
            // Hide other submenus
            $('.has-submenu').removeClass('active');
            $('.dropdown-submenu').hide();
            
            // Show this submenu
            $submenu.show();
            $this.addClass('active');
        }
    });
    
    // Close submenu when touching outside
    $(document).on('touchstart', function(e) {
        if (!$(e.target).closest('.has-submenu').length) {
            $('.has-submenu').removeClass('active');
            $('.dropdown-submenu').hide();
        }
    });
}

// Hide upload button based on conditions
$(document).ready(function() {
    // Hide upload button immediately
    hideUploadButton();
});

function hideUploadButton() {
    // Hide upload button using multiple selectors to ensure it's hidden
    $('button[onclick*="showCSVUploadModal"]').hide();
    $('button:contains("Upload")').hide();
    $('button:contains("Excel")').hide();
    $('.upload-btn').hide();
    
    // Also hide by class if it exists
    $('.btn-outline-success').hide();
    $('.btn-outline-info').hide();
    
    // Force hide using CSS
    $('<style>').text('button[onclick*="showCSVUploadModal"], .upload-btn, .btn-outline-success, .btn-outline-info { display: none !important; }').appendTo('head');
}
</script>
