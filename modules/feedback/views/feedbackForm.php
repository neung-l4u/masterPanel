<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;

// Araya mode is entered by the downgrade form's post-submit redirect, which
// carries the shop identity it already resolved from the email lookup.
//
// A shop_id is required for the mode, not just mode=araya: the 30-minute
// credit is granted once per shop for the lifetime of the account, and a
// once-per-lifetime grant cannot key off a shop name the customer types. With
// no shop_id this form is exactly what it was before -- a lead enquiry form --
// so a truncated URL degrades to the old behaviour instead of breaking.
$fbMode   = ($_GET['mode'] ?? '') === 'araya' ? 'araya' : 'lead';
$fbShopId = trim($_GET['shop_id'] ?? '');
$isAraya  = ($fbMode === 'araya' && $fbShopId !== '');
// The mode that is posted is the effective one: mode=araya with no shop_id is lead mode.
$fbMode   = $isAraya ? 'araya' : 'lead';

// Prefilled from the lookup, shown rather than hidden so the customer can see
// the system recognised them. shop_id is what the credit keys on, so what they
// read here cannot redirect it.
$fbShopName = trim($_GET['shopName'] ?? '');
$fbEmail    = trim($_GET['email'] ?? '');
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
    </div>
</nav>

<div class="container">
    <main>
        <section style="min-height: 50vh;">
            <div class="form-div">
                <img src="../assets/img/L4U-Site-Icon.png" alt="Logo Image" class="mb-4 logo-img">
                <h3 class="mb-4 text-center text-uppercase form-title">Feedback Form</h3>
                <?php if ($isAraya): ?>
                <p class="text-muted mt-3">
                    Sorry to see the AI Receptionist go. Telling us why takes a minute,
                    and we will add <strong>30 free minutes</strong> to your account as a thank-you.
                </p>
                <?php else: ?>
                <p class="text-muted mt-3">
                    Our feedback system allows you to easily share your thoughts, suggestions, or issues. Every entry helps us improve our service and better meet your needs.
                </p>
                <?php endif; ?>
                <form id="feedbackForm" name="feedbackForm" method="post" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="name" class="form-label"><i class="bi bi-file-person"></i> Your Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Jane Doe" required>
                    </div>

                    <div class="mb-4">
                        <label for="shopName" class="form-label"><i class="bi bi-shop-window"></i> Shop Name</label>
                        <input type="text" class="form-control" id="shopName" name="shopName" placeholder="e.g. The Calm Spa" value="<?php echo htmlspecialchars($fbShopName); ?>"<?php echo $isAraya ? ' readonly' : ''; ?> required>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label"><i class="bi bi-envelope"></i> Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="e.g. hello@example.com" value="<?php echo htmlspecialchars($fbEmail); ?>"<?php echo $isAraya ? ' readonly' : ''; ?> required>
                    </div>

                    <?php if (!$isAraya): ?>
                    <div class="mb-4">
                        <label for="shopType" class="form-label"><i class="bi bi-shop-window"></i> Shop Type</label>
                        <select class="form-select" id="shopType" name="shopType" required>
                            <option selected disabled>Select</option>
                            <option value="Restaurant">Restaurant</option>
                            <option value="Massage">Massage</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="mb-4 d-none" id="shopTypeOtherWrapper">
                        <label for="shopTypeOtherInput" class="form-label">Other (please specify)</label>
                        <input type="text" class="form-control" id="shopTypeOtherInput" name="shopTypeOtherInput" placeholder="Enter your custom shop type">
                    </div>
                    <?php endif; ?>

                    <?php if ($isAraya): ?>
                    <input type="hidden" name="package" value="AI Receptionist cancellation">
                    <?php else: ?>
                    <div class="mb-4">
                        <label for="package" class="form-label"><i class="bi bi-bookmark-check"></i> Topic</label>
                        <select class="form-select" id="package" name="package" required>
                            <option selected disabled>Select</option>
                            <option value="Website">Website</option>
                            <option value="Voucher">Voucher</option>
                            <option value="Payment">Payment</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Subscription">Subscription</option>
                            <option value="Customer Support">Customer Support</option>
                            <option value="Massage Booking system">Massage Booking system</option>
                            <option value="Restaurant Online ordering">Restaurant Online ordering</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="mb-4 d-none" id="packageOtherWrapper">
                        <label for="packageOtherInput" class="form-label">Other (please specify)</label>
                        <input type="text" class="form-control" id="packageOtherInput" name="packageOtherInput" placeholder="Enter your custom request">
                    </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <?php if ($isAraya): ?>
                        <label for="description" class="form-label"><i class="bi bi-chat-right-text"></i> Why did you cancel the AI Receptionist?</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Anything you tell us here helps us improve it..." required></textarea>
                        <?php else: ?>
                        <label for="description" class="form-label"><i class="bi bi-chat-right-text"></i> Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Tell us more about your business or needs..." required></textarea>
                        <?php endif; ?>
                    </div>

                    <?php if (!$isAraya): ?>
                    <div class="mb-3" id="imageDiv">
                        <label for="image" class="form-label"><i class="bi bi-file-earmark-arrow-up"></i> Upload Image</label>
                        <input type="file" id="image" name="image" class="form-control file-input mb-2" onchange="handleFileUpload(this)" accept="image/*">
                        <input type="hidden" name="filePath" class="filePath w-100">
                        <input type="hidden" name="fileName" class="fileName w-100">
                    </div>
                    <?php endif; ?>

                    <div class="text-end">
                        <button type="submit" id="cmdSubmit" class="btn btn-submit">Submit</button>
                        <div id="result" class="mt-3"></div>
                    </div>
                    <input type="hidden" name="formVersion" value="1.1.0">
                    <input type="hidden" name="emailVersion" value="1.0.0">
                    <input type="hidden" name="mode" value="<?php echo htmlspecialchars($fbMode); ?>">
                    <input type="hidden" name="shop_id" value="<?php echo htmlspecialchars($fbShopId); ?>">
                </form>
            </div>
        </section>
    </main>
</div><!-- container-->

<?php include '../layout/footer.php'; ?>

<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../controllers/feedbackForm.js?v=1.0.0"></script>
<script>
$(function() {
    $('#shopType').on('change', function () {
        if ($(this).val() === 'Other') {
            $('#shopTypeOtherWrapper').removeClass('d-none');
        } else {
            $('#shopTypeOtherWrapper').addClass('d-none');
        }
    });

    $('#package').on('change', function () {
        if ($(this).val() === 'Other') {
            $('#packageOtherWrapper').removeClass('d-none');
        } else {
            $('#packageOtherWrapper').addClass('d-none');
        }
    });
});
</script>
</body>
</html>