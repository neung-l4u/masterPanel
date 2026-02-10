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
    <link href="../assets/css/upgradeForm.css?v=1.0.0" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
    <title>Upgrade - Form</title>
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
                <h3 class="mb-4 text-center text-uppercase form-title">Upgrade Form</h3>
                <p class="text-muted mt-3">
                    Our feedback system allows you to easily share your thoughts, suggestions, or issues. Every entry helps us improve our service and better meet your needs.
                </p>

                <form id="upgradeForm" method="post" enctype="multipart/form-data">

                    <!-- ================= USER TYPE ================= -->
                    <fieldset class="mb-2 d-flex gap-4">
                        <label>
                        <input type="radio" name="userType" value="customer" checked>
                        Customer
                        </label>
                        <label>
                        <input type="radio" name="userType" value="staff">
                        Staff
                        </label>
                    </fieldset>
                    <input type="email" name="email" placeholder="Email" class="form-control mb-4 ">

                    <!-- ================= SHOP INFO ================= -->
                    <fieldset class="mb-4">
                        <h5>Shop Details</h5>
                        <label class="form-label" for="projectCountry">Country</label>
                        <select id="projectCountry" class="form-select mb-2" name="projectCountry">
                            <option value="">-- Project Country --</option>
                            <option value="ALL">All</option>
                            <option value="AU">Australia</option>
                            <option value="NZ">New Zealand</option>
                            <option value="UK">United Kingdom</option>
                            <option value="US">United States</option>
                            <option value="CA">Canada</option>
                            <option value="TH">Thailand</option>
                        </select>

                        <input class="form-control mb-2" name="shop_id" id="shop_id" placeholder="Monday Project / Shop ID" autocomplete="off">
                        <div id="shopSuggest" class="list-group position-absolute w-100" style="z-index:1000"></div>

                        <input class="form-control mb-2" name="shop_name" placeholder="Shop Name (Auto)" disabled>
                        <input class="form-control mb-2" name="shop_type" placeholder="Shop Type (Auto)" disabled>
                        <input class="form-control mb-2" name="owner_name" placeholder="Owner Name (Auto)" disabled>
                        <input class="form-control mb-2" name="phone" placeholder="Phone Number (Auto)" disabled>
                        <input class="form-control mb-2" name="country" placeholder="Country (Auto)" disabled>

                        <input class="form-control mb-2" name="best_time" placeholder="Best Time to Contact">
                    </fieldset>

                    <!-- ================= PRODUCT ================= -->
                    <fieldset class="mb-4">
                        <h5>Upgrade Information</h5>

                        <label>Original Product</label>
                        <select id="originalProduct" class="form-select mb-2"></select>

                        <label>New Product</label>
                        <select id="newProduct" class="form-select mb-2"></select>

                        <input class="form-control mb-2" name="promotion" placeholder="Promotion">
                        <textarea class="form-control mb-2" name="upgrade_reason" placeholder="Reason / Purpose of Upgrade"></textarea>

                        <select class="form-select mb-2" name="contract_period">
                        <option>Monthly</option>
                        <option>6 Months</option>
                        <option>12 Months</option>
                        </select>

                        <input class="form-control mb-2" name="sales_agent" placeholder="Sales Agent">
                        <input type="date" class="form-control mb-2" name="billing_date">
                    </fieldset>

                    <!-- ================= ADD ON ================= -->
                    <fieldset class="mb-4">
                        <h5>Add-on Products (Paid)</h5>
                        <label><input type="checkbox" name="addon[]" value="website_template"> Website Template</label><br>
                        <label><input type="checkbox" name="addon[]" value="website_makeover"> Website Makeover</label><br>
                        <label><input type="checkbox" name="addon[]" value="araya"> ARAYA (Massage)</label><br>
                        <label><input type="checkbox" name="addon[]" value="ai_marketing"> AI Marketing</label><br>
                        <label><input type="checkbox" name="addon[]" value="unlimited_promo"> Unlimited Promotion</label><br>
                        <label><input type="checkbox" name="addon[]" value="social_post"> Social Media Post</label>
                    </fieldset>

                    <!-- ================= CUSTOMER ACK ================= -->
                    <fieldset id="customerFields">
                        <label><input type="checkbox" required> Cancellation requires 30 days notice</label><br>
                        <label><input type="checkbox" required> Contract fee remains until canceled or changed</label><br>
                        <label><input type="checkbox" required> Stripe is our payment partner (L4U does not hold revenue)</label>
                    </fieldset>

                    <!-- ================= STAFF ONLY ================= -->
                    <fieldset id="staffFields" hidden>
                        <h5>Staff Internal Use</h5>

                        <input class="form-control mb-2" name="staff_name" placeholder="Staff Name">

                        <label>Package Knowledge</label>
                        <input type="file" class="form-control mb-2">
                        <input type="url" class="form-control mb-2" placeholder="Link / Drive / Image">

                        <textarea class="form-control mb-2" placeholder="Stripe Info & Fee"></textarea>
                        <textarea class="form-control mb-2" placeholder="Inhouse Delivery / AI Marketing Info"></textarea>

                        <label>
                        <input type="checkbox">
                        Amelia booking requires WordPress only
                        </label>
                    </fieldset>

                    <!-- ================= SUBMIT ================= -->
                    <div class="text-end">
                        <button class="btn btn-primary">Submit</button>
                    </div>

                </form>

            </div>
        </section>
    </main>
</div><!-- container-->

<?php include '../layout/footer.php'; ?>

<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../controllers/upgradeForm.js?v=1.0.0"></script>
<script>
    $(document).ready(function() {
        
    });
</script>
<script>
    const projectCountry = document.getElementById('projectCountry');

    projectCountry.addEventListener('change', () => {
        const selectedCountry = projectCountry.value;
        return selectedCountry;
        console.log(selectedCountry);
    });

    const userRadios = document.querySelectorAll('[name="userType"]');
    const staffFields = document.getElementById('staffFields');
    const customerFields = document.getElementById('customerFields');

    userRadios.forEach(r => {
    r.addEventListener('change', () => {
        staffFields.hidden = r.value !== 'staff';
        customerFields.hidden = r.value !== 'customer';
    });
    });

    const original = document.getElementById('originalProduct');
    const next = document.getElementById('newProduct');

    const packages = {
    pro: ['Local Starter', 'Local Growth', 'Local Ultimate']
    };
</script>

</body>
</html>