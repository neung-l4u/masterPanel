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
    
    <title>Feedback - Form</title>
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
                    <a class="nav-link active" href="../views/feedbackForm.php">Form</a>
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
        <section style="min-height: 50vh;">
            <div class="form-div">
                <img src="../assets/img/L4U-Site-Icon.png" alt="Logo Image" class="mb-4 logo-img">
                <h3 class="mb-4 text-center text-uppercase form-title">Feedback Form</h3>
                <p class="text-muted mt-3">
                    Our feedback system allows you to easily share your thoughts, suggestions, or issues. Every entry helps us improve our service and better meet your needs.
                </p>
                <form id="feedbackForm" name="feedbackForm" method="post" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="name" class="form-label"><i class="bi bi-file-person"></i> Your Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Jane Doe" required>
                    </div>

                    <div class="mb-4">
                        <label for="shopName" class="form-label"><i class="bi bi-shop-window"></i> Shop Name</label>
                        <input type="text" class="form-control" id="shopName" name="shopName" placeholder="e.g. The Calm Spa" required>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label"><i class="bi bi-envelope"></i> Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="e.g. hello@example.com" required>
                    </div>

                    <div class="mb-4">
                        <label for="shopType" class="form-label"><i class="bi bi-shop-window"></i> Shop Type</label>
                        <select class="form-select" id="shopType" name="shopType" required>
                            <option selected disabled>Select</option>
                            <option value="Restaurant">Restaurant</option>
                            <option value="Massage">Massage</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="package" class="form-label"><i class="bi bi-bookmark-check"></i> Select Package</label>
                        <select class="form-select" id="package" name="package" onchange="toggleOtherInput(this)" required>
                            <option selected disabled>Select</option>
                            <option value="Customer Support">Customer Support</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Massage Booking system">Massage Booking system</option>
                            <option value="Restaurant Online ordering">Restaurant Online ordering</option>
                            <option value="Website">Website</option>
                            <option value="Voucher">Voucher</option>
                            <option value="Payment">Payment</option>
                            <option value="Subscription">Subscription</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="mb-4 d-none" id="otherInputWrapper">
                        <label for="otherInput" class="form-label">Other (please specify)</label>
                        <input type="text" class="form-control" id="otherInput" name="otherInput" placeholder="Enter your custom request">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label"><i class="bi bi-chat-right-text"></i> Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Tell us more about your business or needs..." required></textarea>
                    </div>

                    <div class="mb-3" id="imageDiv">
                        <label for="image" class="form-label"><i class="bi bi-file-earmark-arrow-up"></i> Upload Image</label>
                        <input type="file" id="image" name="image" class="form-control file-input mb-2" onchange="handleFileUpload(this)" accept="image/*">
                        <input type="hidden" name="filePath" class="filePath w-100">
                        <input type="hidden" name="fileName" class="fileName w-100">
                    </div>

                    <div class="text-end">
                        <button type="submit" id="cmdSubmit" class="btn btn-submit">Submit</button>
                        <div id="result" class="mt-3"></div>
                    </div>
                    <input type="hidden" name="formVersion" value="1.0.0">
                    <input type="hidden" name="emailVersion" value="1.0.0">
                </form>
            </div>
        </section>
    </main>
</div><!-- container-->

<?php include '../layout/footer.php'; ?>

<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../controllers/feedbackForm.js?v=1.0.0"></script>
</body>
</html>