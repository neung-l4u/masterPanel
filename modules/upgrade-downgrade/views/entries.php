<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;
?>
<!doctype html>
<html lang="en">
<head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-LGKDYHL23T');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/libs/DataTables/datatables-bs5.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Entries - Local For You</title>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: #f0f2f5;
            color: #1a1a2e;
        }

        /* ── Top bar ── */
        .topbar {
            background: rgba(0,0,0,0.18);
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar a {
            color: rgba(255,255,255,0.65);
            text-decoration: none;
            font-size: .8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color .15s;
        }

        .topbar a:hover { color: #fff; }

        .topbar .sep {
            color: rgba(255,255,255,0.25);
            font-size: .7rem;
        }

        /* ── Header ── */
        .page-header {
            background: linear-gradient(135deg, #0d6efd 0%, #4f8cfd 55%, #80b1ff 100%);
            padding: 56px 32px 36px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -10%;
            width: 280px;
            height: 280px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            pointer-events: none;
        }

        .page-header::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: -5%;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
            pointer-events: none;
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 6px;
            position: relative;
            z-index: 1;
        }

        .page-header p {
            font-size: .83rem;
            color: rgba(255,255,255,0.65);
            margin: 0;
            position: relative;
            z-index: 1;
        }

        /* ── Content ── */
        .content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 20px 64px;
        }

        /* ── Tabs ── */
        .entries-tabs {
            display: flex;
            gap: 4px;
            background: #fff;
            border-radius: 14px;
            padding: 6px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            margin-bottom: 24px;
            width: fit-content;
        }

        .entries-tab {
            padding: 9px 22px;
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            background: transparent;
            color: #6c757d;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: background .15s, color .15s;
        }

        .entries-tab.active.upgrade {
            background: #e8f0fe;
            color: #0d6efd;
        }

        .entries-tab.active.downgrade {
            background: #fdecea;
            color: #dc3545;
        }

        /* ── Table card ── */
        .table-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.07);
            overflow: hidden;
        }

        .table-card-header {
            padding: 20px 24px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .table-card-header h5 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-card-header h5.upgrade { color: #0d6efd; }
        .table-card-header h5.downgrade { color: #dc3545; }

        .table-card-body {
            padding: 16px 24px 24px;
        }

        .btn-form-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: .78rem;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 8px;
            text-decoration: none;
            border: 1.5px solid;
            transition: background .15s, color .15s;
        }

        .btn-form-link.upgrade {
            color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-form-link.upgrade:hover {
            background: #0d6efd;
            color: #fff;
        }

        .btn-form-link.downgrade {
            color: #dc3545;
            border-color: #dc3545;
        }

        .btn-form-link.downgrade:hover {
            background: #dc3545;
            color: #fff;
        }

        /* ── Detail modal ── */
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2px;
        }

        .detail-row {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 8px;
            font-size: .83rem;
        }

        .detail-row:nth-child(odd) { background: #f8f9fa; }

        .detail-label {
            font-weight: 600;
            color: #6c757d;
            display: flex;
            align-items: flex-start;
            gap: 6px;
        }

        .detail-value {
            color: #1a1a2e;
            word-break: break-word;
        }

        .modal-header-upgrade { background: linear-gradient(135deg, #0d6efd, #4f8cfd); }
        .modal-header-downgrade { background: linear-gradient(135deg, #c62828, #ef5350); }

        /* DataTables overrides */
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            font-family: 'Montserrat', sans-serif;
            font-size: .82rem;
        }

        table.dataTable thead th {
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        table.dataTable tbody td {
            font-size: .83rem;
            vertical-align: middle;
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="page-header" style="position:relative;">
    <h1><i class="bi bi-table me-2"></i>Submission Entries</h1>
    <p>All upgrade and downgrade form submissions</p>
</div><!-- /page-header -->

<!-- Content -->
<div class="content">

    <!-- Tabs -->
    <div class="entries-tabs">
        <button class="entries-tab upgrade active" data-tab="upgrade">
            <i class="bi bi-arrow-up-circle-fill"></i> Upgrade
        </button>
        <button class="entries-tab downgrade" data-tab="downgrade">
            <i class="bi bi-arrow-down-circle-fill"></i> Downgrade
        </button>
    </div>

    <!-- Upgrade Table -->
    <div id="tabUpgrade">
        <div class="table-card">
            <div class="table-card-header">
                <h5 class="upgrade"><i class="bi bi-arrow-up-circle-fill"></i> Upgrade Entries</h5>
                <a href="upgradeForm.php" target="_blank" class="btn-form-link upgrade">
                    <i class="bi bi-box-arrow-up-right"></i> Open Form
                </a>
            </div>
            <div class="table-card-body">
                <table id="upgradeTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Shop Name</th>
                            <th>Email</th>
                            <th>Country</th>
                            <th>Upgrade Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Downgrade Table -->
    <div id="tabDowngrade" style="display:none;">
        <div class="table-card">
            <div class="table-card-header">
                <h5 class="downgrade"><i class="bi bi-arrow-down-circle-fill"></i> Downgrade Entries</h5>
                <a href="downgradeForm.php" target="_blank" class="btn-form-link downgrade">
                    <i class="bi bi-box-arrow-up-right"></i> Open Form
                </a>
            </div>
            <div class="table-card-body">
                <table id="downgradeTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Shop Name</th>
                            <th>Email</th>
                            <th>Country</th>
                            <th>Request Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="detailModalHeader">
                <h5 class="modal-title text-white" id="detailModalLabel">
                    <i class="bi bi-file-earmark-text me-2"></i><span id="detailModalShopName"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../assets/libs/DataTables/datatables.min.js"></script>
<script>
$(function () {

    // ── Tab switching ──
    $('.entries-tab').on('click', function () {
        const tab = $(this).data('tab');
        $('.entries-tab').removeClass('active');
        $(this).addClass('active');
        $('#tabUpgrade, #tabDowngrade').hide();
        $('#tab' + tab.charAt(0).toUpperCase() + tab.slice(1)).show();

        // Adjust DataTable columns on show
        if (tab === 'upgrade') $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        if (tab === 'downgrade') $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    // ── Column defs shared ──
    const colDefs = [
        { targets: [0, 3, 5, 6], className: 'dt-center' },
        { targets: [0], width: '40px' },
        { targets: [5], width: '70px' },
        { targets: [6], width: '110px' },
        { targets: [7], orderable: false, className: 'dt-center', width: '40px',
          render: function () {
              return '<button class="btn btn-sm btn-outline-primary btn-view" title="View"><i class="bi bi-eye"></i></button>';
          }
        },
    ];

    // ── Upgrade DataTable ──
    const upgradeTable = $('#upgradeTable').DataTable({
        ajax: { url: '../models/upgradeTable.php', dataSrc: 'data' },
        pageLength: 15,
        lengthMenu: [[15, 25, 50, -1], [15, 25, 50, 'All']],
        columnDefs: colDefs,
        language: { emptyTable: 'No upgrade entries found.' },
    });

    // ── Downgrade DataTable ──
    const downgradeTable = $('#downgradeTable').DataTable({
        ajax: { url: '../models/downgradeTable.php', dataSrc: 'data' },
        pageLength: 15,
        lengthMenu: [[15, 25, 50, -1], [15, 25, 50, 'All']],
        columnDefs: colDefs,
        language: { emptyTable: 'No downgrade entries found.' },
    });

    // ── Detail modal opener ──
    function openDetail(table, row, type) {
        const rowData = table.row(row).data();
        if (!rowData) return;

        const rawJson = $('<textarea/>').html(rowData[7]).text();
        const shopName = rowData[1] || 'Detail';
        let json;
        try { json = JSON.parse(rawJson); } catch (e) { json = {}; }

        // Header colour
        const $header = $('#detailModalHeader');
        $header.removeClass('modal-header-upgrade modal-header-downgrade');
        $header.addClass(type === 'upgrade' ? 'modal-header-upgrade' : 'modal-header-downgrade');
        $('#detailModalShopName').text(shopName);

        // Build grid from all non-empty payload keys
        const skip = ['formVersion', 'formMode', 'formType', 'testMode', 'statusUser'];
        let html = '<div class="detail-grid">';
        for (const [key, val] of Object.entries(json)) {
            if (skip.includes(key)) continue;
            let display = '';
            if (Array.isArray(val)) {
                display = val.length ? val.map(v => `<span class="badge bg-light text-dark border me-1">${v}</span>`).join('') : '<span class="text-muted">—</span>';
            } else if (val === '' || val === null || val === undefined) {
                continue;
            } else {
                display = String(val).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
            }
            const label = key.replace(/([A-Z])/g, ' $1').replace(/^./, s => s.toUpperCase());
            html += `<div class="detail-row">
                <div class="detail-label">${label}</div>
                <div class="detail-value">${display}</div>
            </div>`;
        }
        html += '</div>';

        $('#detailModalBody').html(html);
        new bootstrap.Modal('#detailModal').show();
    }

    $('#upgradeTable').on('click', '.btn-view', function () {
        openDetail(upgradeTable, $(this).closest('tr'), 'upgrade');
    });

    $('#downgradeTable').on('click', '.btn-view', function () {
        openDetail(downgradeTable, $(this).closest('tr'), 'downgrade');
    });

});
</script>
</body>
</html>
