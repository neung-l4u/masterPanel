<?php
date_default_timezone_set('Asia/Bangkok');
$id          = !empty($_GET['id']) ? strtolower(trim($_GET['id'])) : '';
$testMode    = ($id == "test") ? 1 : 0;
$isValidCustomerMode = in_array($id, ['oldcustomer', 'newcustomer'], true);
$customerMode = $isValidCustomerMode ? $id : '';
$isOldCustomer = ($customerMode === 'oldcustomer');
$leadSource  = "POS & Online Order Onboarding";
$formVersion = "1.0.0";
$emailVersion= "1.0";
$timestamps  = date("H:i D ,d M Y") . " (BKK)";
?>
<!doctype html>
<html lang="en">
<head>
    <title>L4U - POS & Online Order Onboarding</title>
    <?php include "form_header.php"; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../aiAraya/assets/css/customerDetailsForm.css?v=2.0.0">
    <style>
        .check-card-group { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-top: 4px; }
        .check-card input, .radio-card input { display: none; }
        .check-card label, .radio-card label {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background: #f8f9fb; border: 1.5px solid #e2e5eb; border-radius: 10px;
            padding: 10px 12px; font-size: 0.85rem; font-weight: 500; color: #4a4f5c;
            cursor: pointer; transition: all .15s ease; user-select: none; width: 100%; margin: 0;
        }
        .check-card label:hover, .radio-card label:hover { border-color: #0d6efd; color: #0d6efd; background: rgba(13,110,253,0.04); }
        .check-card input:checked + label, .radio-card input:checked + label { border-color: #0d6efd; color: #0d6efd; font-weight: 600; background: rgba(13,110,253,0.06); }
        .radio-card-group { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
        .day-row { display: grid; grid-template-columns: 110px 1fr; gap: 12px; align-items: center; margin-bottom: 8px; }
        .day-row .day-label { font-weight: 600; color: #444; }
        .day-row .radio-card-group { grid-template-columns: repeat(2, 1fr); }
        .opening-time-range { display: none; grid-template-columns: 1fr auto 1fr; gap: 8px; align-items: center; margin-top: 8px; max-width: 420px; }
        .day-row.show-open .opening-time-range { display: grid; }
        .form-section { padding: 20px 0; border-bottom: 1px solid #eef0f3; }
        .form-section:last-child { border-bottom: 0; }
        .section-title { color: #0d6efd; margin-bottom: 16px; }
        .submit-area .status-msg { display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; margin-left: 12px; }
        /* File upload card */
        .file-card { border: 1.5px solid #e2e5eb; border-radius: 10px; padding: 14px 16px; background: #fff; }
        .file-card .file-label { font-weight: 500; color: #4a4f5c; margin-bottom: 8px; display: block; }
        .file-card input[type="file"] { display: none; }
        .file-card .file-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border: 1px solid #d4d7dd; border-radius: 8px; background: #f8f9fb; cursor: pointer; font-size: 0.85rem; color: #4a4f5c; transition: all .15s ease; }
        .file-card .file-btn:hover { border-color: #0d6efd; color: #0d6efd; background: rgba(13,110,253,0.04); }
        .file-card .file-name { margin-left: 10px; font-size: 0.82rem; color: #6c757d; word-break: break-all; }
        .file-card.has-file { border-color: #0d6efd; background: rgba(13,110,253,0.03); }
        .file-card.has-file .file-name { color: #0d6efd; font-weight: 500; }
        .adyen-terms-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            background: transparent;
            color: #8a8f98;
            font-size: 1.05rem;
            letter-spacing: 1px;
        }
        .adyen-terms-row .form-check-input {
            width: 28px;
            height: 28px;
            border: 2px solid #dee2e6;
            border-radius: 7px;
            margin: 0;
            cursor: pointer;
        }
        .adyen-terms-divider { color: #8a8f98; }
        .adyen-terms-label { margin: 0; font-weight: 400; }
        .adyen-unlock-hint { color: #6c757d; font-size: 0.9rem; font-weight: 700; cursor: pointer; }
        .adyen-terms-link {
            color: #2b78a0;
            text-decoration: underline;
            text-underline-offset: 4px;
            cursor: pointer;
        }
        .adyen-terms-eye {
            border: 0;
            padding: 0;
            background: transparent;
            color: #0d6efd;
            font-size: 1.7rem;
            line-height: 1;
        }
        .adyen-accept-warning { color: #dc3545; font-weight: 700; margin-right: auto; }
        .adyen-accept-btn { display: none; margin-right: auto; }
        .contact-admin-message {
            max-width: 620px;
            margin: 32px auto;
            padding: 28px;
            border: 1px solid #f1c2c7;
            border-radius: 12px;
            background: #fff5f5;
            color: #842029;
            text-align: center;
        }
        .contact-admin-message a {
            color: #0d6efd;
            font-weight: 600;
        }
        .eftpos-option-label {
            display: flex;
            flex-direction: column;
            min-height: 76px;
        }
        .eftpos-option-label img {
            width: 100%;
            height: 150px;
            object-fit: contain;
            display: block;
            margin-bottom: 10px;
        }
        .eftpos-option-label span {
            display: block;
        }
        .eftpos-online-option {
            min-height: 56px;
            flex-direction: row;
        }
        .pos-setup-grid {
            row-gap: 20px;
        }
        .pos-setup-panel {
            height: 100%;
            padding: 18px;
            border: 1px solid #eef0f3;
            border-radius: 16px;
            background: #fff;
        }
        .pos-setup-options {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .pos-setup-options .check-card label {
            min-height: 52px;
        }
        .pos-right-group {
            margin-bottom: 22px;
        }
        .website-options {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            width: 100%;
        }
        @media (max-width: 576px) {
            .website-options { grid-template-columns: 1fr; }
        }
        @media (max-width: 576px) { .check-card-group, .radio-card-group { grid-template-columns: 1fr; } .day-row { grid-template-columns: 1fr; } }
        body.oldcustomer .newcustomer-only { display: none !important; }
        .ajax-loading-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(3px);
        }
        .ajax-loading-card {
            min-width: 260px;
            padding: 28px 32px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 16px 45px rgba(15, 23, 42, 0.16);
            text-align: center;
            color: #1f2937;
        }
        .ajax-loading-card .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        .ajax-loading-title {
            margin-top: 16px;
            margin-bottom: 6px;
            font-weight: 700;
            color: #0d6efd;
        }
        .ajax-loading-text {
            margin: 0;
            font-size: 0.92rem;
            color: #6c757d;
        }
    </style>
</head>
<body class="<?php echo $customerMode; ?>">
<div class="container">
    <main>
        <section style="min-height: 60vh;">
            <div class="form-div">
                <div class="form-header">
                    <img src="../assets/img/newL4U-logo-100x100.png" alt="L4U Logo" class="logo-img" onerror="this.style.display='none'">
                    <h3 class="form-title text-uppercase">POS &amp; New Online Order Onboarding</h3>
                    <p class="form-subtitle"><?php echo !$isValidCustomerMode ? 'This form requires a valid access link.' : ($isOldCustomer ? 'Please complete shop information and upload the required documents below.' : 'Please complete all sections so our team can get your shop set up.'); ?></p>
                </div>

                <?php if (!$isValidCustomerMode): ?>
                    <div class="contact-admin-message">
                        <h5 class="mb-2"><i class="bi bi-exclamation-circle-fill"></i> Please contact admin</h5>
                        <p class="mb-0">Please contact <a href="mailto:admin@localforyou.com">admin@localforyou.com</a> to access this form.</p>
                    </div>
                <?php else: ?>
                <div class="form-body">
                    <form id="myForm" action="#" method="POST" enctype="multipart/form-data" autocomplete="off">

                        <!-- ========== Page 1 : Onboarding new Client ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-shop"></i> Shop Information</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Country <span class="text-danger">*</span></label>
                                    <select class="form-select" id="country" name="country" required>
                                        <option value="" disabled selected>Please select country</option>
                                        <option value="AU">Australia</option>
                                        <option value="USA">United States</option>
                                        <option value="UK">United Kingdom</option>
                                        <option value="NZ">New Zealand</option>
                                    </select>
                                    <small class="form-text text-muted">Currency &amp; phone code are auto-filled from country.</small>
                                </div>
                                <input type="hidden" id="currency" name="currency" value="">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Shop Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="shop_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Shop's Phone Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="phonePrefix">+--</span>
                                        <input type="tel" class="form-control" id="shopPhone" name="shopPhone" placeholder="Select country first" inputmode="numeric" pattern="[0-9]+" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Shop's Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="shopEmail" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Manager's Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="managerName" required>
                                </div>
                                <div class="col-md-6 mb-3 newcustomer-only">
                                    <label class="form-label">Trading Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="tradingName" required>
                                </div>
                                <div class="col-12 mb-3 newcustomer-only">
                                    <label class="form-label">Trading Address <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="tradingAddress" required>
                                </div>

                                <!-- ===== Document Uploads ===== -->
                                <!-- <div class="col-12 mb-3">
                                    <div class="file-card" data-file="logoMenuPictures">
                                        <span class="file-label">Logo / Menu / Food Pictures</span>
                                        <label class="file-btn" for="file_logo">
                                            <i class="bi bi-upload"></i> Add File
                                        </label>
                                        <span class="file-name">No file selected</span>
                                        <input type="file" id="file_logo" name="logoMenuPictures[]" accept="image/*,application/pdf" multiple>
                                    </div>
                                </div> -->
                                <div class="col-12 mb-3">
                                    <div class="file-card" data-file="businessRegistrationDoc">
                                        <span class="file-label">Copy of Business Registration Document <span class="text-danger">*</span></span>
                                        <label class="file-btn" for="file_bizReg">
                                            <i class="bi bi-upload"></i> Add File
                                        </label>
                                        <span class="file-name">No file selected</span>
                                        <input type="file" id="file_bizReg" name="businessRegistrationDoc" accept="image/*,application/pdf" required>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="file-card" data-file="bankStatementDoc">
                                        <span class="file-label">Copy of Bank Statement <span class="text-danger">*</span></span>
                                        <small class="d-block text-muted mb-2">Only the first page is required. Please ensure the company name, BSB, and account number are visible. All other details can be blacked out.</small>
                                        <label class="file-btn" for="file_bank">
                                            <i class="bi bi-upload"></i> Add File
                                        </label>
                                        <span class="file-name">No file selected</span>
                                        <input type="file" id="file_bank" name="bankStatementDoc" accept="image/*,application/pdf" required>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="file-card" data-file="directorIdDoc">
                                        <span class="file-label">Director's ID <span class="text-danger">*</span></span>
                                        <small class="d-block text-muted mb-2">1 form required — either a driver's licence or passport. Please attach a clear copy.</small>
                                        <label class="file-btn" for="file_dirId">
                                            <i class="bi bi-upload"></i> Add File
                                        </label>
                                        <span class="file-name">No file selected</span>
                                        <input type="file" id="file_dirId" name="directorIdDoc" accept="image/*,application/pdf" required>
                                    </div>
                                </div>

                                <!-- Adyen Terms & Conditions Agreement -->
                                <div class="col-12 mb-3">
                                    <div class="adyen-terms-row">
                                        <input class="form-check-input" type="checkbox" id="adyenAgreement" name="adyenAgree" value="agreed" required disabled>
                                        <span class="adyen-terms-divider">|</span>
                                        <label class="adyen-terms-label" for="adyenAgreement">
                                            I agree to <span class="adyen-terms-link" data-bs-toggle="modal" data-bs-target="#adyenTermsModal">terms &amp; conditions.</span>
                                        </label>
                                        <span class="adyen-unlock-hint" data-bs-toggle="modal" data-bs-target="#adyenTermsModal">Click to read &amp; unlock</span>
                                        <button type="button" class="adyen-terms-eye" data-bs-toggle="modal" data-bs-target="#adyenTermsModal" title="View Adyen Terms & Conditions">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Adyen Terms Modal -->
                                <div class="modal fade" id="adyenTermsModal" tabindex="-1" aria-labelledby="adyenTermsModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="adyenTermsModalLabel">View Adyen Terms &amp; Conditions</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="adyen-pages-container" style="max-height:60vh; overflow-y:auto; border:1px solid #dee2e6; border-radius:6px; padding:10px; background:#f8f9fa;">
                                                    <div class="text-center mb-3">
                                                        <span class="badge bg-secondary">21 Pages</span>
                                                    </div>
                                                    <div class="adyen-pages">
                                                        <img src="../assets/img/adyen/adyen_page-0001.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 1">
                                                        <img src="../assets/img/adyen/adyen_page-0002.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 2">
                                                        <img src="../assets/img/adyen/adyen_page-0003.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 3">
                                                        <img src="../assets/img/adyen/adyen_page-0004.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 4">
                                                        <img src="../assets/img/adyen/adyen_page-0005.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 5">
                                                        <img src="../assets/img/adyen/adyen_page-0006.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 6">
                                                        <img src="../assets/img/adyen/adyen_page-0007.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 7">
                                                        <img src="../assets/img/adyen/adyen_page-0008.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 8">
                                                        <img src="../assets/img/adyen/adyen_page-0009.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 9">
                                                        <img src="../assets/img/adyen/adyen_page-0010.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 10">
                                                        <img src="../assets/img/adyen/adyen_page-0011.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 11">
                                                        <img src="../assets/img/adyen/adyen_page-0012.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 12">
                                                        <img src="../assets/img/adyen/adyen_page-0013.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 13">
                                                        <img src="../assets/img/adyen/adyen_page-0014.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 14">
                                                        <img src="../assets/img/adyen/adyen_page-0015.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 15">
                                                        <img src="../assets/img/adyen/adyen_page-0016.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 16">
                                                        <img src="../assets/img/adyen/adyen_page-0017.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 17">
                                                        <img src="../assets/img/adyen/adyen_page-0018.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 18">
                                                        <img src="../assets/img/adyen/adyen_page-0019.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 19">
                                                        <img src="../assets/img/adyen/adyen_page-0020.jpg" class="img-fluid mb-3" alt="Adyen Terms Page 20">
                                                        <img src="../assets/img/adyen/adyen_page-0021.jpg" class="img-fluid mb-0" alt="Adyen Terms Page 21">
                                                    </div>
                                                </div>
                                                <div id="adyenAgreementSection" style="display:none; opacity:0; transition:opacity 0.3s ease-in;">
                                                    <p class="mt-3 mb-2">
                                                        By clicking "I Accept the Terms", you confirm that you have read, understood, and agree to the Adyen Terms and Conditions. You further authorise Local For You to establish and manage your Adyen merchant account on your behalf.
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <span id="adyenAcceptWarning" class="adyen-accept-warning">! scroll to the end unlock acceptance</span>
                                                <button type="button" id="adyenAcceptBtn" class="btn btn-primary adyen-accept-btn">I Accept the Terms</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mb-3 newcustomer-only">
                                    <label class="form-label">Preferred Address for Terminal Delivery</label>
                                    <div class="form-text mb-1">Portable Eftpos $225+GST (No receipt) &middot; Standard Eftpos $525+GST (receipt)</div>
                                    <input type="text" class="form-control" name="terminalDeliveryAddress" required>
                                </div>
                                <div class="col-12 mb-3 newcustomer-only">
                                    <label class="form-label d-block">Service Provided <span class="text-danger">*</span></label>
                                    <div class="check-card-group">
                                        <?php foreach (["Pickup","Delivery","Table Reservation","Dine-in"] as $i=>$opt): $sid="svc_$i"; ?>
                                            <div class="check-card">
                                                <input type="checkbox" id="<?php echo $sid; ?>" class="svcChk" name="serviceProvided[]" value="<?php echo $opt; ?>">
                                                <label for="<?php echo $sid; ?>"><?php echo $opt; ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ========== Page 2 : Opening Hours ========== -->
                        <div class="form-section newcustomer-only">
                            <h5 class="section-title"><i class="bi bi-clock"></i> Opening Hours</h5>
                            <!-- <div class="form-text mb-3">Select <em>Other</em> to specify custom hours (e.g. 09:00-21:00).</div> -->
                            <?php
                            $days = ['Mon'=>'Monday','Tue'=>'Tuesday','Wed'=>'Wednesday','Thu'=>'Thursday','Fri'=>'Friday','Sat'=>'Saturday','Sun'=>'Sunday'];
                            foreach ($days as $k=>$label):
                                $base = "open$k";
                            ?>
                            <div class="day-row" data-day="<?php echo $base; ?>">
                                <div class="day-label"><?php echo $label; ?></div>
                                <div>
                                    <div class="radio-card-group">
                                        <?php foreach (["Open","Closed"] as $i=>$o): $rid="{$base}_$i"; ?>
                                            <div class="radio-card">
                                                <input type="radio" id="<?php echo $rid; ?>" name="<?php echo $base; ?>" value="<?php echo $o; ?>" class="dayRadio">
                                                <label for="<?php echo $rid; ?>"><?php echo $o; ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="opening-time-range">
                                        <input type="time" class="form-control open-time-input" name="<?php echo $base; ?>OpenTime" aria-label="<?php echo $label; ?> time open">
                                        <span>-</span>
                                        <input type="time" class="form-control open-time-input" name="<?php echo $base; ?>CloseTime" aria-label="<?php echo $label; ?> time close">
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- ========== Page 3 : POS set up ========== -->
                        <div class="form-section newcustomer-only">
                            <h5 class="section-title"><i class="bi bi-calculator"></i> POS Setup</h5>
                            <div class="row pos-setup-grid">
                                <div class="col-lg-6">
                                    <div class="pos-setup-panel">
                                        <label class="form-label">Eftpos Model <span class="text-danger">*</span></label>
                                        <div class="form-text mb-2">$225+GST (No receipt) &middot; $525+GST (receipt)</div>
                                        <div class="pos-setup-options">
                                            <div class="check-card">
                                                <input type="radio" id="eftpos_portable" name="eftposModel" value="Portable Eftpos $225+GST (No receipt)" required>
                                                <label for="eftpos_portable" class="eftpos-option-label">
                                                    <img src="../assets/img/pos/AMS1-Portable ($225+GST).webp" alt="Portable Eftpos $225+GST">
                                                    <span>Portable ($225+GST)</span>
                                                </label>
                                            </div>
                                            <div class="check-card">
                                                <input type="radio" id="eftpos_standard" name="eftposModel" value="Standard Eftpos $525+ GST (receipt)" required>
                                                <label for="eftpos_standard" class="eftpos-option-label">
                                                    <img src="../assets/img/pos/S1F2-Standard ($525+GST).webp" alt="Standard Eftpos $525+GST">
                                                    <span>Standard ($525+GST)</span>
                                                </label>
                                            </div>
                                            <div class="check-card">
                                                <input type="radio" id="eftpos_online" name="eftposModel" value="Only online payment (No eftpos)" required>
                                                <label for="eftpos_online" class="eftpos-option-label eftpos-online-option">
                                                    <span>Only online payment (No eftpos)</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="pos-setup-panel">
                                        <div class="pos-right-group">
                                            <label class="form-label">How Many Eftpos terminal need <span class="text-danger">*</span></label>
                                            <select class="form-select mb-3" name="eftposQty" required>
                                                <option value="" disabled selected>Select</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                                <option value="None">None</option>
                                            </select>
                                        </div>

                                        <div class="pos-right-group">
                                            <label class="form-label d-block mt-2">Use third-party platforms <span class="text-danger">*</span></label>
                                            <div class="pos-setup-options">
                                                <?php foreach (["Ubereats","Doordash","Other"] as $i=>$opt): $tid="tp_$i"; ?>
                                                    <div class="check-card">
                                                        <input type="radio" id="<?php echo $tid; ?>" class="thirdPartyRadio" name="thirdPartyPlatforms" value="<?php echo $opt; ?>" required>
                                                        <label for="<?php echo $tid; ?>"><?php echo $opt; ?></label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <input type="text" class="form-control mt-2" id="thirdPartyOther" name="thirdPartyOther" placeholder="Specify other platform..." style="display:none;">
                                        </div>

                                        <div class="pos-right-group">
                                            <label class="form-label">Do they have their own Website <span class="text-danger">*</span></label>
                                            <div class="website-options">
                                                <div class="check-card">
                                                    <input type="radio" id="web_yes" name="hasOwnWebsite" value="YES" required>
                                                    <label for="web_yes">YES</label>
                                                </div>
                                                <div class="check-card">
                                                    <input type="radio" id="web_no" name="hasOwnWebsite" value="No" required>
                                                    <label for="web_no">No</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ========== Page 4 : Restaurant Address / Cuisine ========== -->
                        <div class="form-section newcustomer-only">
                            <h5 class="section-title"><i class="bi bi-geo-alt"></i> Restaurant Address &amp; Cuisine</h5>
                            <div class="row">
                                <input type="hidden" id="countryCode" name="countryCode">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Street Address</label>
                                    <input type="text" class="form-control" name="streetAddress" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="city" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">State / Region <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="stateRegion" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label d-block">Cuisine Selector <span class="text-danger">*</span></label>
                                    <div class="check-card-group" style="grid-template-columns: repeat(5, 1fr);">
                                        <?php foreach (["Thai","Asian","Indian","Cafe","Other"] as $i=>$opt): $cid="cu_$i"; ?>
                                            <div class="check-card">
                                                <input type="checkbox" id="<?php echo $cid; ?>" class="cuisineChk" name="cuisineSelector[]" value="<?php echo $opt; ?>">
                                                <label for="<?php echo $cid; ?>"><?php echo $opt; ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <input type="text" class="form-control mt-2" id="cuisineOther" name="cuisineOther" placeholder="Specify other cuisine..." style="display:none;">
                                </div>
                            </div>
                        </div>

                        <!-- ========== Page 5 : Delivery Service Need ========== -->
                        <div class="form-section newcustomer-only">
                            <h5 class="section-title"><i class="bi bi-truck"></i> Delivery Service</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label d-block">Delivery Service Need <span class="text-danger">*</span></label>
                                    <div class="radio-card-group" style="grid-template-columns: 1fr;">
                                        <?php foreach (["Own Delivery","Inhouse Delivery","No Need"] as $i=>$opt): $did="dsn_$i"; ?>
                                            <div class="check-card">
                                                <input type="radio" id="<?php echo $did; ?>" class="deliveryServiceNeed" name="deliveryServiceNeed" value="<?php echo $opt; ?>" required>
                                                <label for="<?php echo $did; ?>"><?php echo $opt; ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label d-block">Deliver by <span class="text-danger">*</span></label>
                                    <div class="radio-card-group" style="grid-template-columns: repeat(2, 1fr);">
                                        <div class="check-card">
                                            <input type="radio" id="db_radious" name="deliverBy" value="Radious" required>
                                            <label for="db_radious">Radious</label>
                                        </div>
                                        <div class="check-card">
                                            <input type="radio" id="db_suburb" name="deliverBy" value="Suburb" required>
                                            <label for="db_suburb">Suburb</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Serviced Area</label>
                                    <input type="text" class="form-control" name="servicedArea" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Minimum Order</label>
                                    <input type="number" min="0" step="0.01" class="form-control" name="minimumOrder" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Delivery Fee</label>
                                    <input type="number" min="0" step="0.01" class="form-control" name="deliveryFee" required>
                                </div>
                            </div>

                            <!-- Page 6 : Inhouse Delivery (conditional) -->
                            <div id="inhouseBlock" style="display:none;">
                                <h6 class="mt-3 mb-2"><i class="bi bi-house"></i> Inhouse Delivery Pricing</h6>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">0-3 km Price <span class="text-danger">*</span></label>
                                        <input type="number" min="0" step="0.01" class="form-control inhouseField" name="price0to3km">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">4 km Price <span class="text-danger">*</span></label>
                                        <input type="number" min="0" step="0.01" class="form-control inhouseField" name="price4km">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">5 km Price <span class="text-danger">*</span></label>
                                        <input type="number" min="0" step="0.01" class="form-control inhouseField" name="price5km">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">6 km Price <span class="text-danger">*</span></label>
                                        <input type="number" min="0" step="0.01" class="form-control inhouseField" name="price6km">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Minimum Order <span class="text-danger">*</span></label>
                                        <input type="number" min="0" step="0.01" class="form-control inhouseField" name="inhouseMinimumOrder">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ========== Page 7 : Local for you team ========== -->
                        <div class="form-section newcustomer-only">
                            <h5 class="section-title"><i class="bi bi-people"></i> For Local for You Team</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Logo / Menu / Food Pictures <span class="text-danger">*</span></label>
                                    <select class="form-select gmbFbSelect" name="logoStatus" data-other="logoStatusOther" required>
                                        <option value="" disabled selected>Please select</option>
                                        <option value="Request">Request</option>
                                        <option value="Received">Received</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <input type="text" class="form-control mt-2 other-input" id="logoStatusOther" name="logoStatusOther" placeholder="Specify..." style="display:none;">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">GMB Access <span class="text-danger">*</span></label>
                                    <select class="form-select gmbFbSelect" name="gmbAccess" data-other="gmbAccessOther" required>
                                        <option value="" disabled selected>Please select</option>
                                        <option value="Waiting">Waiting</option>
                                        <option value="Granted">Granted</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <input type="text" class="form-control mt-2 other-input" id="gmbAccessOther" name="gmbAccessOther" placeholder="Specify..." style="display:none;">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Facebook Page Access <span class="text-danger">*</span></label>
                                    <select class="form-select gmbFbSelect" name="facebookPageAccess" data-other="facebookPageAccessOther" required>
                                        <option value="" disabled selected>Please select</option>
                                        <option value="Waiting">Waiting</option>
                                        <option value="Granted">Granted</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <input type="text" class="form-control mt-2 other-input" id="facebookPageAccessOther" name="facebookPageAccessOther" placeholder="Specify..." style="display:none;">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Domain Hosting <span class="text-danger">*</span></label>
                                    <select class="form-select" name="domainHosting" required>
                                        <option value="" disabled selected>Please select</option>
                                        <option value="With Us">With Us</option>
                                        <option value="Need to Transfer In">Need to Transfer In</option>
                                        <option value="Add DNS (to Another Domain Hosting)">Add DNS (to Another Domain Hosting)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- ===== Submit ===== -->
                        <div class="submit-area mt-3">
                            <button type="button" class="btn btn-primary btn-submit" id="submitBtn" onclick="validateAndSubmit()">
                                <i class="bi bi-send"></i> Submit Onboarding
                            </button>
                            <span id="loadingAjax" class="status-msg text-primary" style="display:none;">
                                <i class="bi bi-arrow-repeat"></i> Saving...
                            </span>
                            <span id="doneForm" class="status-msg text-success" style="display:none;">
                                <i class="bi bi-check-circle-fill"></i> Success
                            </span>
                            <small class="text-danger d-block mt-2" id="errMsg" style="display:none;"></small>
                        </div>

                        <input type="hidden" name="customerMode" value="<?php echo $customerMode; ?>">
                        <input type="hidden" name="testMode"     value="<?php echo $testMode; ?>">
                        <input type="hidden" name="leadSource"   value="<?php echo $leadSource; ?>">
                        <input type="hidden" name="formVersion"  value="<?php echo $formVersion; ?>">
                        <input type="hidden" name="emailVersion" value="<?php echo $emailVersion; ?>">
                        <input type="hidden" name="timeStamps"   value="<?php echo $timestamps; ?>">
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

<footer class="credit" style="display:flex;justify-content:center;text-align:center;padding:16px;">
    <div>
        Version <?php echo $formVersion; ?> &middot; Author: IT Team &middot;
        <a href="https://www.localforyou.com" target="_blank">Local For You</a>
    </div>
</footer>

<div id="ajaxLoadingOverlay" class="ajax-loading-overlay" aria-hidden="true">
    <div class="ajax-loading-card">
        <div class="spinner-border text-primary" role="status" aria-label="Loading"></div>
        <h5 class="ajax-loading-title">Submitting your form...</h5>
        <p class="ajax-loading-text">Please wait while we save your information.</p>
    </div>
</div>

<script src="../assets/js/jquery.3.6.0.min.js"></script>
<script src="../assets/js/bootstrap5.0.2.bundle.min.js"></script>
<script>
$(function () {
    const CUSTOMER_MODE = <?php echo json_encode($customerMode); ?>;
    if (CUSTOMER_MODE === "oldcustomer") {
        $(".newcustomer-only").find("input, select, textarea").prop("required", false).prop("disabled", true);
    }

    // ===== Country -> Currency / Phone prefix / Country Code auto-fill =====
    const COUNTRY_MAP = {
        "AU":  { currency: "AUD", code: "+61" },
        "USA": { currency: "USD", code: "+1"  },
        "UK":  { currency: "GBP", code: "+44" },
        "NZ":  { currency: "NZD", code: "+64" }
    };
    $("#country").on("change", function () {
        const m = COUNTRY_MAP[$(this).val()];
        if (!m) return;
        $("#currency").val(m.currency);
        $("#countryCode").val(m.code);
        $("#phonePrefix").text(m.code);
        $("#shopPhone").attr("placeholder", "e.g. " + m.code.replace("+", "") + " 412 345 678").focus();
    });

    $("#shopPhone").on("input paste", function () {
        this.value = this.value.replace(/\D/g, "");
    });

    // ===== Show "Other" text input for GMB / Facebook =====
    $(".gmbFbSelect").on("change", function () {
        const $other = $("#" + $(this).data("other"));
        if ($(this).val() === "Other") {
            $other.show().prop("required", true);
        } else {
            $other.hide().prop("required", false).val("");
        }
    });

    // ===== File picker UI =====
    $('.file-card input[type="file"]').on("change", function () {
        const $card = $(this).closest(".file-card");
        const files = this.files;
        if (!files || !files.length) {
            $card.removeClass("has-file").find(".file-name").text("No file selected");
            return;
        }
        const names = Array.from(files).map(f => f.name).join(", ");
        $card.addClass("has-file").find(".file-name").text(names);
    });

    // Toggle opening time inputs
    $(".dayRadio").on("change", function () {
        const $row = $(this).closest(".day-row");
        const $timeInputs = $row.find(".open-time-input");
        if ($(this).val() === "Open") {
            $row.addClass("show-open");
            $timeInputs.prop("required", true);
        } else {
            $row.removeClass("show-open");
            $timeInputs.prop("required", false).val("");
        }
    });

    // Show inhouse pricing block when needed + toggle required
    function toggleInhouse() {
        const v = $('input[name="deliveryServiceNeed"]:checked').val();
        const isInhouse = (v === "Inhouse Delivery");
        $("#inhouseBlock").toggle(isInhouse);
        $(".inhouseField").prop("required", isInhouse);
    }
    $('input[name="deliveryServiceNeed"]').on("change", toggleInhouse);
    toggleInhouse();

    // Third-party platform "Other" toggle
    $(".thirdPartyRadio").on("change", function () {
        const isOther = $(this).val() === "Other";
        $("#thirdPartyOther").toggle(isOther).prop("required", isOther);
        if (!isOther) $("#thirdPartyOther").val("");
    });

    // Cuisine "Other" toggle
    $(".cuisineChk").on("change", function () {
        const checkedOther = $('.cuisineChk[value="Other"]').is(":checked");
        $("#cuisineOther").toggle(checkedOther).prop("required", checkedOther);
        if (!checkedOther) $("#cuisineOther").val("");
    });

    $("#adyenTermsModal").on("shown.bs.modal", function () {
        const container = $(".adyen-pages-container");
        const agreementSection = $("#adyenAgreementSection");
        const acceptWarning = $("#adyenAcceptWarning");
        const acceptBtn = $("#adyenAcceptBtn");

        agreementSection.hide().css("opacity", "0");
        acceptWarning.show();
        acceptBtn.hide();

        function revealAgreementIfBottom() {
            if (this.scrollTop + this.clientHeight >= this.scrollHeight - 10) {
                agreementSection.show();
                acceptWarning.hide();
                acceptBtn.show();
                setTimeout(() => agreementSection.css("opacity", "1"), 10);
            }
        }

        container.off("scroll").on("scroll", revealAgreementIfBottom);
        container.find("img").off("load").on("load", function () {
            container.trigger("scroll");
        });
        container.trigger("scroll");
    });

    $(document).on("click", "#adyenAcceptBtn", function () {
        $("#adyenAgreement").prop("disabled", false).prop("checked", true);
        $("#adyenTermsModal").modal("hide");
    });
});

function validateAndSubmit() {
    const $err = $("#errMsg").hide().text("");
    const required = $("#myForm [required]");
    const isOldCustomer = $('input[name="customerMode"]').val() === "oldcustomer";
    let firstInvalid = null;
    required.each(function () {
        if (this.disabled) return;
        if ((this.type === "checkbox" || this.type === "radio") && !this.checked) {
            if (!firstInvalid) firstInvalid = this;
            $(this).addClass("is-invalid");
        } else if (!$(this).val() || $(this).val() === "") {
            if (!firstInvalid) firstInvalid = this;
            $(this).addClass("is-invalid");
        } else {
            $(this).removeClass("is-invalid");
        }
    });
    if (!$("#adyenAgreement").is(":checked")) {
        $("#adyenAgreement").addClass("is-invalid");
        $err.text("Please read and accept the Adyen Terms and Conditions.").show();
        return;
    }
    if (!isOldCustomer && $(".svcChk:checked").length < 1) {
        $err.text("Please select at least one Service Provided.").show();
        return;
    }
    if (!isOldCustomer && $(".cuisineChk:checked").length < 1) {
        $err.text("Please select at least one Cuisine.").show();
        return;
    }
    if (!isOldCustomer) {
        const missingOpeningDay = $(".day-row").filter(function () {
            return $(this).find(".dayRadio:checked").length < 1;
        }).first();
        if (missingOpeningDay.length) {
            $err.text("Please select Open or Closed for every opening day.").show();
            missingOpeningDay[0].scrollIntoView({ behavior: "smooth", block: "center" });
            return;
        }
    }
    if (firstInvalid) {
        firstInvalid.focus();
        $err.text("Please complete the required fields.").show();
        return;
    }

    const phoneVal = ($("#shopPhone").val() || "").trim();
    if (!/^[0-9]+$/.test(phoneVal)) {
        $("#shopPhone").addClass("is-invalid").focus();
        $err.text("Shop phone number must contain numbers only.").show();
        return;
    }

    const emailEl = document.querySelector('input[name="shopEmail"]');
    if (emailEl && !emailEl.checkValidity()) {
        $(emailEl).addClass("is-invalid").focus();
        $err.text("Please enter a valid shop email address.").show();
        return;
    }

    // Files are required — extra check
    const missingFiles = [];
    $('.file-card input[type="file"][required]').each(function () {
        if (!this.files || !this.files.length) {
            missingFiles.push($(this).closest(".file-card").find(".file-label").text().trim());
            $(this).closest(".file-card").css("border-color", "#dc3545");
        }
    });
    if (missingFiles.length) {
        $err.text("Please attach: " + missingFiles.join(", ")).show();
        return;
    }

    // Build payload (FormData supports arrays for checkboxes + files)
    const fd = new FormData(document.getElementById("myForm"));

    $("#submitBtn").hide();
    $("#loadingAjax").show();
    $("#ajaxLoadingOverlay").css("display", "flex").attr("aria-hidden", "false");

    $.ajax({
        url: "activeajax.php",
        method: "POST",
        data: fd,
        processData: false,
        contentType: false,
        dataType: "json"
    }).done(function (res) {
        $("#loadingAjax").hide();
        if (res && res.success) {
            $("#doneForm").show();
            console.log("Saved:", res);
            location.replace("thank-you.php");
        } else {
            $("#ajaxLoadingOverlay").hide().attr("aria-hidden", "true");
            $("#submitBtn").show();
            $err.text(res && res.result ? res.result : "Submit failed.").show();
        }
    }).fail(function (xhr) {
        $("#loadingAjax").hide();
        $("#ajaxLoadingOverlay").hide().attr("aria-hidden", "true");
        $("#submitBtn").show();
        $err.text("Server error: " + xhr.status + " — " + (xhr.responseText || "").slice(0, 200)).show();
    });
}
</script>
</body>
</html>
