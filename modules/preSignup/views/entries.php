<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/entries.css?v=1.0.0" rel="stylesheet">
    <link rel="stylesheet" href="../assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/libs/DataTables/datatables-bs5.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="icon" href="../assets/img/L4U-Site-Icon.png">
    <title>Pre Sign-up Entries</title>
</head>
<body style="min-height: 100vh;">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><img src="../assets/img/L4U-Site-Icon.png" alt="logo" style="width: 50px;"/></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="preSignupForm.php">Pre Sign-up Form</a></li>
                <li class="nav-item"><a class="nav-link active" aria-current="page" href="entries.php">Entries</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h1 class="h3 fw-bold mb-1">Pre Sign-up Entries</h1>
            <p class="text-muted mb-0">
                Every lead opened on a customer's behalf. &ldquo;Not opened&rdquo; means the
                customer has not yet followed the link you sent them.
            </p>
        </div>
        <a href="preSignupForm.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New pre sign-up
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="entriesTable" class="table table-striped table-hover align-middle">
                    <thead class="table-dark thead-dark">
                        <tr>
                            <th class="col_link">Link</th>
                            <th class="col_shopName">Shop Name</th>
                            <th class="col_country">Country</th>
                            <th class="col_shopType">Shop Type</th>
                            <th class="col_customerType">Customer Type</th>
                            <th class="col_contact">Contact</th>
                            <th class="col_email">Email</th>
                            <th class="col_phone">Phone</th>
                            <th class="col_leadSource">Lead Source</th>
                            <th class="col_channel">Channel</th>
                            <th class="col_salesRep">Opened By</th>
                            <th class="col_status">Status</th>
                            <th class="col_date">Created</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div><!-- container -->

<?php include '../layout/footer.php'; ?>

<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../assets/libs/DataTables/datatables.min.js"></script>
<script src="../controllers/entries.js?v=1.0.0"></script>
<script>
$(function () {
    $('#entriesTable').DataTable({
        pagingType: 'full_numbers',
        ajax: {
            url: '../models/entriesTable.php',
            dataSrc: 'data'
        },
        order: [],
        pageLength: 25,
        lengthMenu: [
            [10, 25, 50, -1],
            ['Fit', 25, 50, 'All']
        ],
        columnDefs: [
            { targets: [0, 9, 11], orderable: false },
            { targets: [0, 2, 11], className: 'dt-center' }
        ],
        layout: {
            topStart: {
                buttons: [
                    {
                        text: 'Excel',
                        extend: 'excelHtml5',
                        // The link column holds markup, so it is left out of
                        // the export; the reference is in the shop row anyway.
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7, 8, 10, 12]
                        }
                    }
                ]
            }
        },
        drawCallback: function () {
            initTooltips();
        }
    });

    initTooltips();
    initCopyLinks();
});
</script>
</body>
</html>
