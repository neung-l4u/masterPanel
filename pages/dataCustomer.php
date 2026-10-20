<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>L4U - Data Customer</title>
    <style>
        .colNo {
            width: 50px;
        }
        .colAction {
            width: 100px;
            text-align: center;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        /* Responsive Table */
        .table-responsive-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        @media (max-width: 768px) {
            .table {
                font-size: 0.875rem;
            }
            
            .table thead {
                display: none;
            }
            
            .table tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #dee2e6;
                border-radius: 0.25rem;
            }
            
            .table tbody td {
                display: block;
                text-align: right;
                padding-left: 50%;
                position: relative;
                border: none;
                padding-top: 0.75rem;
                padding-bottom: 0.75rem;
            }
            
            .table tbody td::before {
                content: attr(data-label);
                position: absolute;
                left: 0.75rem;
                font-weight: 600;
                text-align: left;
                width: 45%;
            }
            
            .table tbody td:first-child {
                background-color: #f8f9fa;
                font-weight: 600;
            }
            
            .colAction {
                width: auto;
                text-align: center;
                padding-left: 0.75rem !important;
            }
            
            .colAction::before {
                display: none;
            }
        }
        
        /* Modal Responsive */
        @media (max-width: 576px) {
            .modal-dialog {
                margin: 0.5rem;
            }
            
            .modal-body {
                padding: 1rem;
            }
            
            .form-label {
                font-size: 0.875rem;
            }
            
            .form-control, .form-select {
                font-size: 0.875rem;
            }
        }
        
        /* Buttons Responsive */
        @media (max-width: 576px) {
            .btn-sm {
                padding: 0.375rem 0.75rem;
                font-size: 0.75rem;
            }
            
            .btn {
                margin: 0.25rem;
            }
        }
        
        /* Modal Close Button Fix */
        .modal-header .btn-close {
            cursor: pointer;
            pointer-events: auto;
            z-index: 10;
        }
        
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <!-- Content Header -->
    <div class="content-header mb-4">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h4 class="m-0">
                    <svg class="nav-icon mr-3" height="1em" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM609.3 512H471.4c1.6-9.7 2.6-19.7 2.6-30c0-61-49.9-110-111-110h-2.3c-6.4-6.8-15.3-13-25.8-17.9c34.3-33.8 56-81.1 56-133.3C391.4 97.9 293.5 0 176 0S0 97.9 0 218.6c0 52.3 21.7 99.6 56 133.3c-10.6 4.9-19.5 11.1-25.8 17.9H28c-61 0-111 49-111 110c0 10.3 1 20.3 2.6 30H0c-16.4 0-29.7-13.3-29.7-29.7C0 437.9 79.8 358 178.3 358h91.4c61 0 111 49 111 110c0 10-1 20-2.6 30z" fill="#000000"/></svg>
                    Data Customer
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="d-flex justify-content-end breadcrumb">
                    <li class="breadcrumb-item">Home</li>
                    <li class="breadcrumb-item active">Data Customer</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive-wrapper">
                        <table id="customerTable" class="table table-borderless table-striped table-hover" style="width:100%">
                            <thead class="thead-dark">
                            <tr>
                                <th class="colNo">#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Type</th>
                                <th>Address</th>
                                <th class="colAction">Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Customer</h5>
                <button type="button" class="btn btn-sm btn-secondary" id="closeModalBtn">✕</button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="customerId">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="editName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPhone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="editPhone">
                    </div>
                    <div class="mb-3">
                        <label for="editType" class="form-label">Type</label>
                        <input type="text" class="form-control" id="editType">
                    </div>
                    <div class="mb-3">
                        <label for="editAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="editAddress" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editTaxNumber" class="form-label">Tax Number</label>
                        <input type="text" class="form-control" id="editTaxNumber">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closeModalBtnFooter" onclick="$('#editModal').modal('hide')">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveCustomer()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
let customerTable;

window.editCustomer = function(id) {
    $.ajax({
        url: 'pages/tableRendering/getCustomerDetail.php',
        type: 'POST',
        dataType: 'json',
        data: { id: id },
        success: function(res) {
            if (res.success) {
                $('#customerId').val(res.data.id);
                $('#editName').val(res.data.name);
                $('#editEmail').val(res.data.email);
                $('#editPhone').val(res.data.phone);
                $('#editType').val(res.data.type);
                $('#editAddress').val(res.data.address);
                $('#editTaxNumber').val(res.data.taxNumber);
                $('#editModal').modal('show');
            }
        }
    });
};

window.saveCustomer = function() {
    const id = $('#customerId').val();
    const data = {
        id: id,
        name: $('#editName').val(),
        email: $('#editEmail').val(),
        phone: $('#editPhone').val(),
        type: $('#editType').val(),
        address: $('#editAddress').val(),
        taxNumber: $('#editTaxNumber').val()
    };

    $.ajax({
        url: 'pages/tableRendering/updateCustomer.php',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res) {
            if (res.success) {
                $('#editModal').modal('hide');
                customerTable.ajax.reload();
                alert('Customer updated successfully');
            } else {
                alert('Error: ' + res.message);
            }
        }
    });
};

window.deleteCustomer = function(id) {
    if (confirm('Are you sure you want to delete this customer?')) {
        $.ajax({
            url: 'pages/tableRendering/deleteCustomer.php',
            type: 'POST',
            dataType: 'json',
            data: { id: id },
            success: function(res) {
                if (res.success) {
                    customerTable.ajax.reload();
                    alert('Customer deleted successfully');
                } else {
                    alert('Error: ' + res.message);
                }
            }
        });
    }
};

window.addEventListener('load', function() {
    if (typeof $ !== 'undefined') {
        customerTable = $('#customerTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: 'pages/tableRendering/getCustomerData.php',
                type: 'POST'
            },
            columns: [
                { 
                    data: 'id', 
                    className: 'colNo',
                    render: function(data, type, row) {
                        return `<span data-label="#">${data}</span>`;
                    }
                },
                { 
                    data: 'name',
                    render: function(data, type, row) {
                        return `<span data-label="Name:">${data}</span>`;
                    }
                },
                { 
                    data: 'email',
                    render: function(data, type, row) {
                        return `<span data-label="Email:">${data}</span>`;
                    }
                },
                { 
                    data: 'phone',
                    render: function(data, type, row) {
                        return `<span data-label="Phone:">${data}</span>`;
                    }
                },
                { 
                    data: 'type',
                    render: function(data, type, row) {
                        return `<span data-label="Type:">${data}</span>`;
                    }
                },
                { 
                    data: 'address',
                    render: function(data, type, row) {
                        return `<span data-label="Address:">${data}</span>`;
                    }
                },
                { 
                    data: null,
                    className: 'colAction',
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-sm btn-warning" onclick="editCustomer(${row.id})" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                                    <path d="M12.146.292a.5.5 0 0 1 .708 0l3.854 3.854a.5.5 0 0 1 0 .708l-10.851 10.851a.5.5 0 0 1-.224.105l-2.5.667a.5.5 0 0 1-.623-.623l.667-2.5a.5.5 0 0 1 .105-.224l10.851-10.851zM4.5 5.5a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1h-6z"/>
                                </svg>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteCustomer(${row.id})" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 1a.5.5 0 0 0-.5.5v1h11v-1a.5.5 0 0 0-.5-.5h-3V1a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 0-.5.5v.5H2.5z"/>
                                </svg>
                            </button>
                        `;
                    }
                }
            ]
        });
        
        // Close button handler
        $('#closeModalBtn').on('click', function() {
            $('#editModal').modal('hide');
        });
    }
});
</script>

</body>
</html>
