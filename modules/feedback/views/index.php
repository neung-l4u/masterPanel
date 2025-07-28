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
    <link href="../assets/css/feedbackForm.css?v=1.0.0" rel="stylesheet">
    <link rel="stylesheet" href="../assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <title>Feedback</title>
</head>
<body style="min-height: 100vh;">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><img src="../assets/img/L4U-Site-Icon.png" alt="logo" style="width: 50px;"/></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../views/feedbackForm.php">Form</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../views/entries.php">Entries</a>
                </li>
            </ul>
            <span class="navbar-text">
                logout
            </span>
        </div>
    </div>
</nav>

<div class="container">
    <main>
        <section class="d-flex justify-content-center align-items-center">
            <div class="row align-items-center" style="height: 85vh;">
                <!-- Left Content -->
                <div class="col-md-6 text-center text-md-start mb-4 mb-md-0">
                    <h1 class="display-4 fw-bold">Local For You<br>Feedback</h1>
                    <p class="text-muted mt-3">
                    Our feedback system allows you to easily share your thoughts, suggestions, or issues. Every entry helps us improve our service and better meet your needs.
                    </p>
                    <div class="mt-4">
                    <a href="feedbackForm.php" class="btn btn-primary me-2">Feedback Form</a>
                    <a href="Entries.php" class="btn btn-outline-dark">View Entries</a>
                    </div>
                </div>

                <!-- Right Image -->
                <div class="col-md-6 text-center">
                    <img src="../assets/img/l4u-feedback.png" alt="l4u-feedback" class="w-100 hero-img">
                </div>
            </div>
        </section>
    </main>
</div><!-- container-->

<?php include '../layout/footer.php'; ?>

<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../controllers/index.js?v=1.0.0"></script>
</body>
</html>