<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <i class="bi bi-printer mr-2"></i> Printers Log
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                    <li class="breadcrumb-item"><a href="main.php?p=tools">Tools</a></li>
                    <li class="breadcrumb-item active">Printers Log</li>
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
                <h5 class="mb-0"><i class="bi bi-table mr-1"></i> Printer Orders</h5>
                <div>
                    <select id="filterCountry" class="form-control form-control-sm d-inline-block mr-2" style="width:auto;">
                        <option value="">All Countries</option>
                        <option value="AU">Australia</option>
                        <option value="NZ">New Zealand</option>
                    </select>
                    <select id="filterPrinter" class="form-control form-control-sm d-inline-block" style="width:auto;">
                        <option value="">All Printers</option>
                        <option value="TM-T82IIIL">TM-T82IIIL</option>
                        <option value="TM-M30">TM-M30</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <table id="datatable" class="table table-bordered table-striped table-hover" style="width:100%">
                    <thead class="thead-dark">
                    <tr>
                        <th style="width:4%;">#</th>
                        <th style="width:15%;">Customer</th>
                        <th style="width:20%;">Shop Name</th>
                        <th style="width:15%;">Email</th>
                        <th style="width:12%;">Country</th>
                        <th style="width:12%;">Printer Model</th>
                        <th style="width:10%;">Price</th>
                        <th style="width:12%;">Order Date</th>
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
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="detailModalLabel"><i class="bi bi-printer mr-1"></i> Order Detail</h5>
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

<script>
window.addEventListener('load', function() {
    // Check if DataTable is already initialized
    if (jQuery.fn.DataTable.isDataTable('#datatable')) {
        var printersTable = jQuery('#datatable').DataTable();
    } else {
        var printersTable = jQuery('#datatable').DataTable({
            pagingType: 'full_numbers',
            ajax: {
                url: 'pages/tableRendering/dataPrintersLog.php',
                dataSrc: 'data'
            },
            "pageLength": 10,
            order: [[0, 'desc']],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'All']
            ],
            columnDefs: [
                { targets: [4, 5, 6, 7, 8], className: 'dt-center' },
                { targets: [8], orderable: false }
            ],
            dom: 'lfrtip'
        });
    }

    // Country filter
    jQuery('#filterCountry').on('change', function() {
        printersTable.column(4).search(this.value).draw();
    });

    // Printer model filter
    jQuery('#filterPrinter').on('change', function() {
        printersTable.column(5).search(this.value).draw();
    });

    // View detail — fetch from DB then show modal
    jQuery(document).on('click', '.btn-view-detail', function() {
        var orderId = jQuery(this).attr('data-id');
        jQuery('#detailModalBody').html('<div class="text-center p-5">Loading...</div>');
        
        const modalEl = document.getElementById('detailModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        jQuery.ajax({
            url: 'pages/tableRendering/getPrinterOrderDetail.php?id=' + orderId,
            dataType: 'json'
        }).done(function(res) {
            if (res.status === 'success') {
                var d = res.data;
                
                var countryFlag = d.country === 'AU' ? '🇦🇺' : '🇳🇿';
                var countryName = d.country === 'AU' ? 'Australia' : 'New Zealand';

                var html = '';
                html += '<div class="row mb-3">';
                html += '  <div class="col-md-6">';
                html += '    <h6><i class="bi bi-person-fill"></i> Customer Information</h6>';
                html += '    <table class="table table-sm table-bordered">';
                html += '      <tr><th style="width:120px;">Name</th><td>' + d.first_name + ' ' + d.last_name + '</td></tr>';
                html += '      <tr><th>Email</th><td><a href="mailto:' + d.email + '">' + d.email + '</a></td></tr>';
                html += '      <tr><th>Mobile</th><td><a href="tel:' + d.mobile + '">' + d.mobile + '</a></td></tr>';
                html += '      <tr><th>Shop Name</th><td>' + d.shop_name + '</td></tr>';
                html += '      <tr><th>Country</th><td>' + countryFlag + ' ' + countryName + '</td></tr>';
                html += '    </table>';
                html += '  </div>';
                html += '  <div class="col-md-6">';
                html += '    <h6><i class="bi bi-printer-fill"></i> Printer Information</h6>';
                html += '    <table class="table table-sm table-bordered">';
                html += '      <tr><th style="width:120px;">Model</th><td><span class="badge badge-info">' + d.printer_model + '</span></td></tr>';
                html += '      <tr><th>Full Name</th><td>' + d.printer_full_name + '</td></tr>';
                html += '      <tr><th>Price</th><td><strong class="text-success">' + d.price + '</strong></td></tr>';
                html += '      <tr><th>Supplier</th><td><a href="mailto:' + d.supplier_email + '">' + d.supplier_email + '</a></td></tr>';
                html += '      <tr><th>Order Date</th><td>' + new Date(d.order_date).toLocaleString() + '</td></tr>';
                html += '    </table>';
                html += '  </div>';
                html += '</div>';
                html += '<div class="row">';
                html += '  <div class="col-12">';
                html += '    <h6><i class="bi bi-geo-alt-fill"></i> Shipping Address</h6>';
                html += '    <div class="alert alert-light">' + d.address.replace(/\n/g, '<br>') + '</div>';
                html += '  </div>';
                html += '</div>';

                jQuery('#detailModalBody').html(html);
            } else {
                jQuery('#detailModalBody').html('<div class="alert alert-danger">Error: ' + res.message + '</div>');
            }
        }).fail(function() {
            jQuery('#detailModalBody').html('<div class="alert alert-danger">Failed to load order data.</div>');
        });
    });
});
</script>
