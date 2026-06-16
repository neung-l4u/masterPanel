<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-LGKDYHL23T');
</script>
<?php
global $db, $date;
?>
<style>
    .modal-body {font-size:0.9rem;}
    small{font-size:0.7rem;}
    .table .thead-dark th {background-color:#212529!important;}
    ::placeholder {color:#DDDDDD!important;opacity:1;}
    .nav-tabs .nav-link {color:#495057;font-weight:500;}
    .nav-tabs .nav-link.active {color:#0d6efd;font-weight:600;}
    .nav-tabs .nav-link i {margin-right:5px;}
    .badge-show {background-color:#28a745;}
    .badge-hide {background-color:#6c757d;}
    /* Table responsive like reportLifeSpan */
    .table-responsive {max-height:70vh; overflow-y:auto;}
    #aiLogsTable {font-size:0.8rem;}
    #aiLogsTable thead {position:sticky; top:0; z-index:2; background:#212529; color:#fff;}
    #aiLogsTable th {font-size:0.75rem; white-space:nowrap; padding:8px 6px;}
    #aiLogsTable td {white-space:nowrap; padding:6px;}
    #aiLogsTable code {font-size:0.7rem;}
    /* Country sub-tabs */
    #countryTabs .nav-link {font-weight:500; padding:12px 16px; border-radius:0; color:#495057;}
    #countryTabs .nav-link.active {background-color:#0d6efd; color:#fff; font-weight:600;}
    #countryTabs .nav-link:hover:not(.active) {background-color:#e9ecef;}
</style>
<link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/css/bootstrap.css">
<link rel="stylesheet" href="assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="plugins/datatables-bs5/css/datatables-bs5.min.css">

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <i class="bi bi-robot"></i> AI Management
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="main.php">Home</a></li>
                    <li class="breadcrumb-item active">AI Management</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#ai-logs-panel">
                    <i class="bi bi-journal-text"></i> Logs AI
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ai-products-panel">
                    <i class="bi bi-box-seam"></i> Products AI
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Tab 1: Logs AI -->
            <div class="tab-pane fade show active" id="ai-logs-panel">
                <div class="card p-3">
                    <div class="card-header bg-white">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-0"><i class="bi bi-robot"></i> AI Signup Logs</h5>
                                <small class="text-muted">Customers who signed up for AI Receptionist or AI + Bundles</small>
                            </div>
                            <div class="col-auto">
                                <div class="d-flex gap-2">
                                    <input type="text" id="aiSearchInput" class="form-control form-control-sm" placeholder="Search..." style="width:180px;">
                                    <select id="aiCountryFilter" class="form-select form-select-sm" style="width:120px;">
                                        <option value="">All Countries</option>
                                        <option value="AU">Australia</option>
                                        <option value="NZ">New Zealand</option>
                                        <option value="UK">United Kingdom</option>
                                        <option value="US">United States</option>
                                        <option value="CA">Canada</option>
                                        <option value="TH">Thailand</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="aiLogsTable" class="table table-bordered table-striped table-hover mb-0" style="width:100%">
                                <thead class="thead-dark text-white">
                                    <tr>
                                        <th>Date</th>
                                        <th>Shop Name</th>
                                        <th>Country</th>
                                        <th>Main Product</th>
                                        <th>Main Price ID</th>
                                        <th>Add-ons</th>
                                        <th>Add-on Price ID</th>
                                        <th>Customer ID</th>
                                        <th>Email</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Products AI -->
            <div class="tab-pane fade" id="ai-products-panel">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-box-seam"></i> AI Products</h5>
                    </div>
                    <div class="card-body p-0">
                        <!-- Entries per page selector -->
                        <div class="p-2 border-bottom bg-white">
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                <label class="mb-0 small text-muted">Show:</label>
                                <select id="productsPerPage" class="form-select form-select-sm" style="width:80px;">
                                    <option value="fit">Fit</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="9999">All</option>
                                </select>
                                <label class="mb-0 small text-muted">entries</label>
                            </div>
                        </div>
                        <!-- Country Sub-tabs -->
                        <ul class="nav nav-pills nav-fill bg-light border-bottom" id="countryTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#country-AU">
                                    <i class="bi bi-flag"></i> AU
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#country-NZ">
                                    <i class="bi bi-flag"></i> NZ
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#country-UK">
                                    <i class="bi bi-flag"></i> UK
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#country-US">
                                    <i class="bi bi-flag"></i> US
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#country-CA">
                                    <i class="bi bi-flag"></i> CA
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#country-TH">
                                    <i class="bi bi-flag"></i> TH
                                </button>
                            </li>
                        </ul>
                        <!-- Country Content -->
                        <div class="tab-content p-3" id="countryTabContent">
                            <div class="tab-pane fade show active" id="country-AU">
                                <div class="country-products" data-country="AU"></div>
                            </div>
                            <div class="tab-pane fade" id="country-NZ">
                                <div class="country-products" data-country="NZ"></div>
                            </div>
                            <div class="tab-pane fade" id="country-UK">
                                <div class="country-products" data-country="UK"></div>
                            </div>
                            <div class="tab-pane fade" id="country-US">
                                <div class="country-products" data-country="US"></div>
                            </div>
                            <div class="tab-pane fade" id="country-CA">
                                <div class="country-products" data-country="CA"></div>
                            </div>
                            <div class="tab-pane fade" id="country-TH">
                                <div class="country-products" data-country="TH"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.js"></script>
<script src="plugins/datatables-bs5/js/datatables-bs5.min.js"></script>
<script>
function sendMail(btn) {
    const payload = $(btn).data('payload');
    if(!payload) return alert('No payload data');
    
    $(btn).prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Sending...');
    
    $.ajax({
        url: 'https://hook.us1.make.com/6vloshre04tb1xtkjhgawblx2jk7a2ji',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function() {
            alert('Mail sent successfully!');
            $(btn).prop('disabled', false).html('<i class="bi bi-envelope"></i> Send Mail');
        },
        error: function() {
            alert('Error sending mail');
            $(btn).prop('disabled', false).html('<i class="bi bi-envelope"></i> Send Mail');
        }
    });
}

// Global storage for products data
let aiProductsData = {};

function loadAiProducts() {
    const countries = {AU:'Australia',NZ:'New Zealand',UK:'United Kingdom',US:'United States',CA:'Canada',TH:'Thailand'};
    
    // Show loading in all country containers
    Object.keys(countries).forEach(function(cc) {
        $('.country-products[data-country="' + cc + '"]').html(
            '<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>'
        );
    });
    
    $.ajax({
        url: 'modules/signup/assets/API/B-Price-2604041028-updateNewWebsiteHosting.json',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (!response.data) {
                Object.keys(countries).forEach(function(cc) {
                    $('.country-products[data-country="' + cc + '"]').html('<div class="alert alert-warning">Invalid data</div>');
                });
                return;
            }
            
            // Store data and render
            aiProductsData = {};
            Object.keys(countries).forEach(function(cc) {
                const $countryContainer = $('.country-products[data-country="' + cc + '"]');
                
                if(!response.data[cc]) {
                    aiProductsData[cc] = [];
                    $countryContainer.html('<div class="alert alert-info">No products for ' + countries[cc] + '</div>');
                    return;
                }
                
                let products = [];
                const countryData = response.data[cc];
                
                // Loop through shop types (Restaurant, Massage, etc.)
                Object.keys(countryData).forEach(function(shopType) {
                    const shopTypeData = countryData[shopType];
                    if(shopTypeData && shopTypeData.Products && Array.isArray(shopTypeData.Products)) {
                        shopTypeData.Products.forEach(function(periodGroup) {
                            if(periodGroup.items && Array.isArray(periodGroup.items)) {
                                periodGroup.items.forEach(function(item) {
                                    if(item.type === 'aibundle' || (item.name && item.name.match(/AI\s*\+?|AI Receptionist/i))) {
                                        products.push(item);
                                    }
                                });
                            }
                        });
                    }
                });
                
                aiProductsData[cc] = products;
                renderCountryProducts(cc, products, 1);
            });
        },
        error: function(xhr, status, error) {
            Object.keys(countries).forEach(function(cc) {
                $('.country-products[data-country="' + cc + '"]').html('<div class="alert alert-danger">Error: ' + error + '</div>');
            });
        }
    });
}

function renderCountryProducts(cc, products, page) {
    const countries = {AU:'Australia',NZ:'New Zealand',UK:'United Kingdom',US:'United States',CA:'Canada',TH:'Thailand'};
    const $container = $('.country-products[data-country="' + cc + '"]');
    const perPageVal = $('#productsPerPage').val();
    const perPage = perPageVal === 'fit' ? 10 : parseInt(perPageVal);
    const totalPages = Math.ceil(products.length / perPage);
    const start = (page - 1) * perPage;
    const end = Math.min(start + perPage, products.length);
    const pageProducts = products.slice(start, end);
    
    if(!products.length) {
        $container.html('<div class="alert alert-info">No AI products for ' + countries[cc] + '</div>');
        return;
    }
    
    let html = '<div class="table-responsive" style="max-height:60vh; overflow-y:auto;"><table class="table table-sm table-bordered table-hover mb-0">';
    html += '<thead class="table-light sticky-top"><tr><th>Product Name</th><th>Price ID</th><th>Amount</th><th>Period</th></tr></thead><tbody>';
    
    pageProducts.forEach(function(p) {
        const amount = (p.amount / 100).toFixed(2);
        const badgeClass = p.status === 'show' ? 'badge-show' : 'badge-hide';
        html += '<tr>';
        html += '<td><span class="badge ' + badgeClass + ' me-1">' + p.status + '</span> ' + p.name + '</td>';
        html += '<td><code>' + p.price_id + '</code></td>';
        html += '<td>' + amount + ' ' + p.currency.toUpperCase() + '</td>';
        html += '<td>' + (p.ext || '-') + '</td>';
        html += '</tr>';
    });
    
    html += '</tbody></table></div>';
    
    // Pagination info
    if (products.length > perPage) {
        html += '<div class="d-flex justify-content-between align-items-center mt-2 px-2">';
        html += '<small class="text-muted">Showing ' + (start + 1) + ' to ' + end + ' of ' + products.length + ' entries</small>';
        html += '<div class="btn-group btn-group-sm">';
        html += '<button class="btn btn-outline-secondary" onclick="renderCountryProducts(\'' + cc + '\', aiProductsData[\'' + cc + '\'], ' + (page - 1) + ')" ' + (page <= 1 ? 'disabled' : '') + '><i class="bi bi-chevron-left"></i></button>';
        html += '<span class="btn btn-light disabled">' + page + ' / ' + totalPages + '</span>';
        html += '<button class="btn btn-outline-secondary" onclick="renderCountryProducts(\'' + cc + '\', aiProductsData[\'' + cc + '\'], ' + (page + 1) + ')" ' + (page >= totalPages ? 'disabled' : '') + '><i class="bi bi-chevron-right"></i></button>';
        html += '</div></div>';
    } else {
        html += '<div class="mt-2 px-2"><small class="text-muted">' + products.length + ' entries</small></div>';
    }
    
    $container.html(html);
}

$(function() {
    // AI Logs Table
    const aiTable = $('#aiLogsTable').DataTable({
        pagingType: 'full_numbers',
        pageLength: 25,
        lengthMenu: [[25,50,100,-1],[25,50,100,'All']],
        ajax: {
            url: 'pages/tableRendering/dataAiLogs.php',
            type: 'POST',
            dataSrc: 'data',
            data: function(d) {
                d.country = $('#aiCountryFilter').val();
            }
        },
        columns: [
            {data: 0}, // Date
            {data: 1}, // Shop Name
            {data: 2}, // Country
            {data: 3}, // Main Product
            {data: 4}, // Main Price ID (with discount inside)
            {data: 5}, // Add-ons
            {data: 6}, // Add-on Price ID
            {data: 7}, // Customer ID
            {data: 8}, // Email
            {data: 9, orderable: false} // Action
        ],
        columnDefs: [
            {targets: [3,5], orderable: false},
            {targets: 9, className: 'text-center', width: '100px'}
        ]
    });
    
    // Search functionality
    $('#aiSearchInput').on('keyup', function() {
        aiTable.search(this.value).draw();
    });
    
    $('#aiCountryFilter').on('change', function() {
        aiTable.ajax.reload();
    });
    
    // Load Products when tab shown
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        if($(e.target).attr('data-bs-target') === '#ai-products-panel') {
            loadAiProducts();
        }
    });
    
    // Handle products per page change
    $('#productsPerPage').on('change', function() {
        const countries = {AU:'Australia',NZ:'New Zealand',UK:'United Kingdom',US:'United States',CA:'Canada',TH:'Thailand'};
        Object.keys(countries).forEach(function(cc) {
            if(aiProductsData[cc] && aiProductsData[cc].length) {
                renderCountryProducts(cc, aiProductsData[cc], 1);
            }
        });
    });
});
</script>
