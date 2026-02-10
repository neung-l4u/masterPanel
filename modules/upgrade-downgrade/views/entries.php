<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-LGKDYHL23T');
    </script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/entries.css?v=1.0.0" rel="stylesheet">
    <link rel="stylesheet" href="../assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/libs/DataTables/datatables-bs5.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
    <title>Upgrade/Downgrade - Entries</title>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><img src="../assets/img/L4U-Site-Icon.png" alt="logo" style="width: 50px;"/></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../views/upgradeForm.php">Upgrade Form</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../views/downgradeForm.php">Downgrade Form</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="../views/entries.php">Entries</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<body>

<div class="container mt-5">
    <main>
        <section style="min-height: 50vh;">
            <div class="form-div">
                <!-- <header>
                    <nav class="text-center mb-4" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                        <ol class="justify-content-center breadcrumb">
                            <i class="bi bi-house-fill"></i>&nbsp;
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Entries</li>
                        </ol>
                    </nav>
                </header> -->
                <h3 class="mb-4 text-center text-uppercase form-title">Feedback Entries</h3>
                <p class="text-center">Here you can view all feedback entries submitted through the form.</p>
            </div>

            <div class="row pt-3">
                <div class="col border rounded py-3">
                    <table id="entriesTable" class="table table-striped table-hover">
                        <thead class="table-dark thead-dark">
                            <tr>
                                <th class="col_id"><i class="bi bi-record2" title="ID"></i></th>
                                <th class="col_owner"><i class="bi bi-person-circle" title="Owner"></i></th>
                                <th class="col_shopName">Shop Name</th>
                                <th class="col_email">Email</th>
                                <th class="col_shopType">Shop Type</th>
                                <th class="col_package">Package</th>
                                <th class="col_shortDesc">description</th>
                                <th class="col_fullDesc"></th>
                                <th class="col_attachFile">Image</th>
                                <th class="col_date">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div><!-- container-->

<?php include '../layout/footer.php'; ?>

<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../assets/libs/DataTables/datatables.min.js"></script>
<script src="../controllers/entries.js?v=1.0.0"></script>
<script>
$(function() {
    $('#entriesTable').DataTable( {
        pagingType: 'full_numbers',
        ajax: {
            url: '../models/entriesTable.php',
            dataSrc: 'data'
        },
        "pageLength": 10,
        lengthMenu: [
            [10, 25, 50, -1],
            ['Fit', 25, 50, 'All']
        ],columnDefs: [
            { targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9], "orderable": "false"},
            { targets: [0, 8], className: 'dt-center'},
            { targets: [7], className: 'd-none' }
        ],
        layout: {
            topStart: {
                buttons: [
                {
                    text: 'Excel',
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 7, 8, 9]
                    },
                },
                // {
                //     text: 'PDF',
                //     extend: 'pdfHtml5',
                //     orientation: 'landscape',
                //     pageSize: 'A4',
                //     exportOptions: {
                //         columns: [0, 1, 2, 3, 4, 5, 6, 8]
                //     },
                // }
                ]
            }
        },
        drawCallback: function () {
            initTooltips();
        }
    } );

initTooltips();

});//ready
</script>
</body>
</html>