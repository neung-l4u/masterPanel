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
    <link href="../assets/css/preSignupForm.css?v=1.0.0" rel="stylesheet">
    <link rel="stylesheet" href="../assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="icon" href="../assets/img/L4U-Site-Icon.png">

    <title>Pre Sign-up</title>
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
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="preSignupForm.php">Pre Sign-up Form</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="entries.php">Entries</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <main>
        <section class="d-flex justify-content-center align-items-center">
            <div class="row align-items-center" style="min-height: 85vh;">
                <!-- Left Content -->
                <div class="col-md-6 text-center text-md-start mb-4 mb-md-0">
                    <h1 class="display-4 fw-bold">Local For You<br>Pre Sign-up</h1>
                    <p class="text-muted mt-3">
                        Open a lead for a customer while you have them on chat or on a call.
                        Fill in what they tell you, and the form hands you a unique link to
                        send them &mdash; it opens the full signup form with their details
                        already in place, so they never retype what they just told you.
                    </p>
                    <div class="mt-4">
                        <a href="preSignupForm.php" class="btn btn-primary me-2">Pre Sign-up Form</a>
                        <a href="entries.php" class="btn btn-outline-dark">View Entries</a>
                    </div>
                </div>

                <!-- Right Content -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body p-4">
                            <h2 class="h5 fw-semibold mb-3">How it works</h2>
                            <ol class="mb-0 ps-3">
                                <li class="mb-2">
                                    A lead arrives by <strong>chat</strong> (Respond.io) or
                                    <strong>cold call</strong>.
                                </li>
                                <li class="mb-2">
                                    You fill in the pre sign-up form on their behalf. Dropdown
                                    choices come straight from the Lead Management board, so the
                                    values always match.
                                </li>
                                <li class="mb-2">
                                    You get a link like
                                    <code>prelead-0000001</code> to send the customer.
                                </li>
                                <li class="mb-0">
                                    They open it and finish signing up, with their details
                                    already filled in.
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div><!-- container-->

<?php include '../layout/footer.php'; ?>

<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
</body>
</html>
