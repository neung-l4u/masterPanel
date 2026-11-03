<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;

$formVersion = '1.0.0';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/preSignupForm.css?v=<?php echo $formVersion; ?>" rel="stylesheet">
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
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link active" aria-current="page" href="preSignupForm.php">Pre Sign-up Form</a></li>
                <li class="nav-item"><a class="nav-link" href="entries.php">Entries</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4" style="max-width: 900px;">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
        <h1 class="h3 fw-bold mb-0">Pre Sign-up</h1>
        <span class="badge text-bg-light border" id="optionsSource">Loading options&hellip;</span>
    </div>
    <p class="text-muted">
        Fill this in for the customer while you have them on chat or on a call.
        Submitting creates a unique pre-signup link to send them, which opens the
        signup form with everything below already filled in.
    </p>

    <form id="preSignupForm" novalidate>
        <input type="hidden" name="formVersion" value="<?php echo $formVersion; ?>">

        <!-- Contact channel ------------------------------------------------ -->
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">How did this lead come in?</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="contactChannel" class="form-label">Contact channel <span class="text-danger">*</span></label>
                        <select class="form-select" id="contactChannel" name="contactChannel" data-options="contactChannel" required>
                            <option value="" selected disabled>Please select</option>
                        </select>
                        <div class="form-text">Chat covers any messaging platform. Call means a cold call.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="leadSource" class="form-label">Lead source <span class="text-danger">*</span></label>
                        <select class="form-select" id="leadSource" name="leadSource" data-options="leadSource" required>
                            <option value="" selected disabled>Please select</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="conversationLink" class="form-label">Conversation link</label>
                        <input type="url" class="form-control" id="conversationLink" name="conversationLink"
                               placeholder="https://">
                        <div class="form-text">
                            Respond.io, Facebook, TikTok, Line &mdash; wherever the conversation is,
                            so whoever picks the lead up can read it. Leave empty if there is none.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="salesRep" class="form-label">Your name</label>
                        <input type="text" class="form-control" id="salesRep" name="salesRep" placeholder="Who opened this lead">
                    </div>
                </div>
            </div>
        </div>

        <!-- Shop ----------------------------------------------------------- -->
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">Shop</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="shopName" class="form-label">Shop name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="shopName" name="shopName" required>
                    </div>
                    <div class="col-md-6">
                        <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                        <select class="form-select" id="country" name="country" data-options="country" required>
                            <option value="" selected disabled>Please select</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="shopType" class="form-label">Shop type <span class="text-danger">*</span></label>
                        <select class="form-select" id="shopType" name="shopType" data-options="shopType" required>
                            <option value="" selected disabled>Please select</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="customerType" class="form-label">Customer type <span class="text-danger">*</span></label>
                        <select class="form-select" id="customerType" name="customerType" data-options="customerType" required>
                            <option value="" selected disabled>Please select</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="mobNumber" class="form-label">Mob number</label>
                        <input type="tel" class="form-control" id="mobNumber" name="mobNumber" inputmode="tel">
                    </div>
                    <div class="col-md-6">
                        <label for="shopNumber" class="form-label">Shop number</label>
                        <input type="tel" class="form-control" id="shopNumber" name="shopNumber" inputmode="tel">
                    </div>
                    <div class="col-12">
                        <div class="form-text" id="contactNote">
                            Give at least one way to reach the shop &mdash; a number, an email,
                            or the conversation link above.
                        </div>
                    </div>
                    <div class="col-12">
                        <label for="websiteLink" class="form-label">Website link</label>
                        <input type="url" class="form-control" id="websiteLink" name="websiteLink" placeholder="https://">
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact person -------------------------------------------------- -->
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">Contact person</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="nickname" class="form-label">Nickname</label>
                        <input type="text" class="form-control" id="nickname" name="nickname">
                    </div>
                    <div class="col-md-4">
                        <label for="firstName" class="form-label">First name</label>
                        <input type="text" class="form-control" id="firstName" name="firstName">
                    </div>
                    <div class="col-md-4">
                        <label for="lastName" class="form-label">Last name</label>
                        <input type="text" class="form-control" id="lastName" name="lastName">
                    </div>
                    <div class="col-12">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                </div>
            </div>
        </div>

        <!-- Address --------------------------------------------------------- -->
        <div class="card mb-3">
            <div class="card-header bg-white fw-semibold">Address</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="streetAddress" class="form-label">Street address</label>
                        <input type="text" class="form-control" id="streetAddress" name="streetAddress">
                    </div>
                    <div class="col-md-5">
                        <label for="citySuburb" class="form-label">City / Suburb</label>
                        <input type="text" class="form-control" id="citySuburb" name="citySuburb">
                    </div>
                    <div class="col-md-4">
                        <label for="state" class="form-label">State</label>
                        <input type="text" class="form-control" id="state" name="state">
                    </div>
                    <div class="col-md-3">
                        <label for="postalCode" class="form-label">Postal code</label>
                        <input type="text" class="form-control" id="postalCode" name="postalCode">
                    </div>
                </div>
            </div>
        </div>

        <div id="result"></div>

        <div class="d-flex gap-2 mt-3 mb-5">
            <button type="submit" class="btn btn-primary px-4" id="cmdSubmit">
                <i class="bi bi-link-45deg"></i> Create pre-signup link
            </button>
            <a href="entries.php" class="btn btn-outline-dark">View entries</a>
        </div>
    </form>
</div><!-- container -->

<?php include '../layout/footer.php'; ?>

<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../controllers/preSignupForm.js?v=<?php echo $formVersion; ?>"></script>
</body>
</html>
