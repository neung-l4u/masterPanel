<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <i class="bi bi-chat-left-text mr-2"></i> Feedback Monday
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item"><a href="main.php?p=tools">Tools</a></li>
                    <li class="breadcrumb-item active">Feedback Monday</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-table mr-1"></i> Advanced Reports</h5>
                <div>
                    <select id="filterStatus" class="form-control form-control-sm d-inline-block" style="width:auto;">
                        <option value="">All Status</option>
                        <option value="Active" selected>Active</option>
                        <option value="Resolved">Resolved</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <table id="datatable" class="table table-bordered table-striped table-hover" style="width:100%">
                    <thead class="thead-dark">
                    <tr>
                        <th style="width:4%;">#</th>
                        <th style="width:12%;">Reporter</th>
                        <th style="width:14%;">Board</th>
                        <th>Subject</th>
                        <th style="width:13%;">Date</th>
                        <th style="width:8%;">Status</th>
                        <th style="width:8%;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="detailModalLabel"><i class="bi bi-file-earmark-text mr-1"></i> Report Detail</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <div class="text-center p-5"><i class="bi bi-arrow-repeat spin"></i> Loading...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
    let feedbackTable = $('#datatable').DataTable({
        pagingType: 'full_numbers',
        ajax: {
            url: 'pages/tableRendering/dataFeedbackMonday.php',
            dataSrc: 'data'
        },
        "pageLength": 8,
        order: [[0, 'desc']],
        lengthMenu: [
            [8, 25, 50, -1],
            ['Fit', 25, 50, 'All']
        ],
        columnDefs: [
            { targets: [5, 6], className: 'dt-center' },
            { targets: [6], orderable: false }
        ]
    });

    // Status filter
    $('#filterStatus').on('change', function() {
        feedbackTable.column(5).search(this.value).draw();
    });
    feedbackTable.on('init', function() {
        $('#filterStatus').trigger('change');
    });

    // View detail — fetch from DB then show modal
    $(document).on('click', '.btn-view-detail', function() {
        var reportId = $(this).attr('data-id');
        $('#detailModalBody').html('<div class="text-center p-5">Loading...</div>');
        $('#detailModal').modal('show');

        $.ajax({
            url: 'assets/php/getAdvancedReport.php?id=' + reportId,
            dataType: 'json'
        }).done(function(res) {
            if (res.status === 'success') {
                var d = res.data;
                var statusHtml = d.reportStatus == 1
                    ? '<span class="badge badge-warning">Active</span>'
                    : '<span class="badge badge-success">Resolved</span>';

                function imgBlock(path, label) {
                    if (!path) return '<span class="text-muted">No file</span>';
                    return '<a href="' + path + '" target="_blank"><img src="' + path + '" class="img-fluid rounded" style="max-height:220px;" alt="' + label + '"></a>';
                }

                var html = '';
                html += '<div class="row mb-3">';
                html += '  <div class="col-md-6">';
                html += '    <div class="d-flex align-items-center mb-2">';
                html += '      <img src="dist/img/crews/' + (d.pic || 'no_pic.png') + '" class="rounded-circle mr-2" style="width:40px;height:40px;object-fit:cover;" onerror="this.src=\'dist/img/crews/no_pic.png\'" alt="">';
                html += '      <div><b>' + d.reporter + '</b><br><small class="text-muted">' + d.team + '</small></div>';
                html += '    </div>';
                html += '  </div>';
                html += '  <div class="col-md-6 text-right">';
                html += '    ' + statusHtml + '<br><small class="text-muted">' + d.createdAt + '</small>';
                html += '  </div>';
                html += '</div>';
                html += '<table class="table table-sm table-bordered">';
                html += '  <tr><th style="width:120px;">Board</th><td>' + d.board + '</td></tr>';
                html += '  <tr><th>Subject</th><td>' + d.subject + '</td></tr>';
                html += '  <tr><th>Detail</th><td style="white-space:pre-wrap;">' + (d.detail || '-') + '</td></tr>';
                html += '</table>';
                html += '<div class="row mt-3">';
                html += '  <div class="col-md-4"><h6 class="text-center"><i class="bi bi-paperclip"></i> Attachment</h6><div class="text-center">' + imgBlock(d.attachment, 'Attachment') + '</div></div>';
                html += '  <div class="col-md-4"><h6 class="text-center"><i class="bi bi-wifi"></i> Internet Speed</h6><div class="text-center">' + imgBlock(d.screenshot_internet, 'Internet') + '</div></div>';
                html += '  <div class="col-md-4"><h6 class="text-center"><i class="bi bi-laptop"></i> Computer Info</h6><div class="text-center">' + imgBlock(d.screenshot_computer, 'Computer') + '</div></div>';
                html += '</div>';

                $('#detailModalBody').html(html);
            } else {
                $('#detailModalBody').html('<div class="alert alert-danger">Error: ' + res.message + '</div>');
            }
        }).fail(function() {
            $('#detailModalBody').html('<div class="alert alert-danger">Failed to load report data.</div>');
        });
    });

    // Resolve button
    $(document).on('click', '.btn-resolve', function() {
        var id = $(this).attr('data-id');
        if (!confirm('Mark this report as Resolved?')) return;
        $.post('assets/php/resolveAdvancedReport.php', { id: id }, function(res) {
            if (res.status === 'success') {
                feedbackTable.ajax.reload(null, false);
            } else {
                alert('Error: ' + res.message);
            }
        }, 'json');
    });
</script>
