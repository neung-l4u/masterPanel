<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;
$currentPage = basename($_SERVER['PHP_SELF']);
$tomorrow = date("Y-m-d", strtotime("+1 day"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Appointment Booking</title>
    <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/libs/select2/css/select2.min.css" rel="stylesheet"/>
    <link href="../assets/libs/select2/css/select2-bootstrap-5-theme.min.css" rel="stylesheet"/>
    <link href="../assets/libs/flatpickr/flatpickr.min.css" rel="stylesheet"/>
    <style>
        .step-section {
            display: none;
        }

        .step-section.active {
            display: block;
        }
    </style>
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container py-5">
    <header>
        <nav class="mb-4"
             style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);"
             aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">🛖 Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Booking</li>
            </ol>
        </nav>
    </header>

    <main>
        <div class="mb-4">
            <h4 class="text-primary">Local For You - # 1 Marketing Agency for Thai (Internal)</h4>
            <small>Please select the appointment type to make a booking.</small>
        </div>

        <h5 class="text-info">-- Thank you!! --</h5>
    </main>
</div>

<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/select2/js/select2.min.js"></script>
<script src="../assets/libs/flatpickr/flatpickr.js"></script>

<script>
    $(()=>{
        console.log('success');
    });//ready
</script>
</body>
</html>
