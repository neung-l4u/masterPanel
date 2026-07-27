<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;

$stripeID = $_GET['stripeID'] ?? '';
$mode = $_GET['mode'] ?? 'customer';
$testMode = $_GET['testMode'] ?? 'false';
?>
<!doctype html>
<html lang="en">
<head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-LGKDYHL23T');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/downgradeForm.css?v=1.0.0" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Downgrade / Unsubscription Form - Local For You</title>
</head>
<body data-mode="<?php echo htmlspecialchars($mode); ?>">

<div class="container">
    <main>
        <section style="min-height: 50vh;">
            <div class="form-layout">
                <nav class="info-sidebar">
                    <div class="sidebar-section">
                        <h6 class="sidebar-title">Form Type</h6>
                        <a href="upgradeForm.php?mode=<?php echo htmlspecialchars($mode); ?>&stripeID=<?php echo urlencode($stripeID); ?>" class="sidebar-item sidebar-form sidebar-form-upgrade">
                            <i class="bi bi-arrow-up-circle"></i>
                            <span>Upgrade</span>
                        </a>
                        <a href="downgradeForm.php?mode=<?php echo htmlspecialchars($mode); ?>&stripeID=<?php echo urlencode($stripeID); ?>" class="sidebar-item sidebar-form sidebar-form-downgrade sidebar-mode-active">
                            <i class="bi bi-arrow-down-circle"></i>
                            <span>Downgrade</span>
                        </a>
                    </div>
                    <div class="sidebar-divider"></div>
                    <div class="sidebar-section">
                        <h6 class="sidebar-title">Submitted By</h6>
                        <a href="?mode=customer&stripeID=<?php echo urlencode($stripeID); ?>" class="sidebar-item sidebar-mode <?php echo $mode === 'customer' ? 'sidebar-mode-active' : ''; ?>">
                            <i class="bi bi-person"></i>
                            <span>Customer</span>
                        </a>
                        <a href="?mode=staff&stripeID=<?php echo urlencode($stripeID); ?>" class="sidebar-item sidebar-mode <?php echo $mode === 'staff' ? 'sidebar-mode-active' : ''; ?>">
                            <i class="bi bi-headset"></i>
                            <span>Staff</span>
                        </a>
                    </div>
                    <div class="sidebar-divider"></div>
                    <div class="sidebar-section">
                        <h6 class="sidebar-title">Information</h6>
                        <button class="sidebar-item sidebar-action" data-bs-toggle="modal" data-bs-target="#modalPackageRef">
                            <i class="bi bi-box-seam"></i>
                            <span>Products</span>
                        </button>
                        <button class="sidebar-item sidebar-action" data-bs-toggle="modal" data-bs-target="#modalImportantInfo">
                            <i class="bi bi-info-circle"></i>
                            <span>Important Info</span>
                        </button>
                        <button class="sidebar-item sidebar-action" data-bs-toggle="modal" data-bs-target="#modalStaffKB">
                            <i class="bi bi-book"></i>
                            <span>Knowledge Base</span>
                        </button>
                    </div>
                </nav>
            <div class="form-div">

                <!-- ===== Header ===== -->
                <div class="form-header">
                    <img src="../assets/img/L4U-Site-Icon.png" alt="Logo Image" class="logo-img">
                    <h3 class="form-title text-uppercase">Downgrade / Unsubscription Form</h3>
                    <p class="form-subtitle">
                        <?php if ($mode === 'customer'): ?>
                            Please complete this form to request a downgrade or unsubscription from your current service plan.
                        <?php else: ?>
                            Internal downgrade &amp; unsubscription management form. Fill in all relevant details for the customer's request.
                        <?php endif; ?>
                    </p>
                </div>

                <!-- ===== Form Body ===== -->
                <div class="form-body">


                    <!-- ============================================================ -->
                    <!-- MAIN FORM                                                    -->
                    <!-- ============================================================ -->
                    <form id="downgradeForm" name="downgradeForm" method="post" enctype="multipart/form-data">

                        <!-- ========== SECTION: Business Information ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-building"></i> Business Information</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="shopName" class="form-label">Shop Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="shopName" name="shopName" placeholder="e.g. The Green Kitchen" required>
                                    <div class="invalid-feedback">Please enter the shop name.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tradingName" class="form-label">Trading Name</label>
                                    <input type="text" class="form-control" id="tradingName" name="tradingName" placeholder="e.g. Green Kitchen LLC">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="address" name="address" placeholder="e.g. 123 Main St, Los Angeles, CA 90001" required>
                                <div class="invalid-feedback">Please enter the business address.</div>
                            </div>

                            <?php if ($mode === 'staff'): ?>
                            <div class="mb-3">
                                <label for="country" class="form-label">Country</label>
                                <select class="form-select" id="country" name="country">
                                    <option value="">-- Select Country --</option>
                                    <option value="US">United States</option>
                                    <option value="CA">Canada</option>
                                    <option value="AU">Australia</option>
                                    <option value="NZ">New Zealand</option>
                                    <option value="UK">United Kingdom</option>
                                    <option value="TH">Thailand</option>
                                </select>
                            </div>
                            <div class="mb-3 position-relative">
                                <label for="mondayProjectId" class="form-label">Monday Project / Shop ID <small class="text-muted">(Staff Reference)</small></label>
                                <input type="text" class="form-control" id="mondayProjectId" name="mondayProjectId" placeholder="Type shop name or ID to search..." autocomplete="off">
                                <input type="hidden" id="boardId" name="boardId" value="">
                                <div id="projectSearchDropdown" class="project-search-dropdown"></div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- ========== SECTION: Contact Information ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-person-lines-fill"></i> Contact Information</h5>

                            <div class="mb-3">
                                <label for="contactPerson" class="form-label">Contact Person Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="contactPerson" name="contactPerson" placeholder="e.g. Jane Smith" required>
                                <div class="invalid-feedback">Please enter the contact person name.</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="emailAddress" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="emailAddress" name="emailAddress" placeholder="e.g. jane@example.com" required>
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="mobileNumber" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="mobileNumber" name="mobileNumber" placeholder="e.g. +1 (555) 123-4567" required>
                                    <div class="invalid-feedback">Please enter the mobile number.</div>
                                </div>
                            </div>
                        </div>

                        <!-- ========== SECTION: Type of Request ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-arrow-down-circle"></i> Type of Request <span class="text-danger">*</span></h5>
                            <div class="mb-3">
                                <div class="radio-card-group-vertical">
                                    <div class="radio-card-v">
                                        <input type="radio" name="requestType" id="reqWebsiteOnly" value="Downgrade to website service only" required>
                                        <label for="reqWebsiteOnly"><i class="bi bi-globe"></i> Downgrade to website service only</label>
                                    </div>
                                    <div class="radio-card-v">
                                        <input type="radio" name="requestType" id="reqOrderingOnly" value="Downgrade to online ordering system/booking system only">
                                        <label for="reqOrderingOnly"><i class="bi bi-bag-check"></i> Downgrade to online ordering system / booking system only</label>
                                    </div>
                                    <div class="radio-card-v">
                                        <input type="radio" name="requestType" id="reqMarketingOnly" value="Downgrade to marketing only (Solo)">
                                        <label for="reqMarketingOnly"><i class="bi bi-megaphone"></i> Downgrade to marketing only (Solo)</label>
                                    </div>
                                    <div class="radio-card-v">
                                        <input type="radio" name="requestType" id="reqOtherPackage" value="Downgrade to other package">
                                        <label for="reqOtherPackage"><i class="bi bi-box-arrow-down"></i> Downgrade to other package</label>
                                    </div>
                                    <div class="radio-card-v radio-card-v-danger">
                                        <input type="radio" name="requestType" id="reqUnsubscribe" value="Unsubscribe all services">
                                        <label for="reqUnsubscribe"><i class="bi bi-x-octagon"></i> Unsubscribe all services</label>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block" id="requestTypeError" style="display:none!important;"></div>
                            </div>
                        </div>

                        <!-- ========== SECTION: Downgrade Information (shown for "Downgrade to other package") ========== -->
                        <div class="form-section" id="downgradeInfoSection" style="display:none;">
                            <h5 class="section-title"><i class="bi bi-arrow-repeat"></i> Downgrade Information</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Current Product (Active Subscriptions) <span class="text-danger">*</span></label>
                                    <?php if ($mode === 'staff'): ?>
                                    <div id="originalProductList" class="subscription-list">
                                        <span class="subscription-placeholder">Select a project first</span>
                                    </div>
                                    <input type="hidden" id="originalProduct" name="originalProduct">
                                    <?php else: ?>
                                    <select class="form-select" id="originalProduct" name="originalProduct">
                                        <option value="">-- Select Current Product --</option>
                                        <optgroup label="Pro Plans">
                                            <option value="pro_starter">Pro - Local Starter</option>
                                            <option value="pro_growth">Pro - Local Growth</option>
                                            <option value="pro_ultimate">Pro - Local Ultimate</option>
                                        </optgroup>
                                        <optgroup label="Solo Plans">
                                            <option value="solo_starter">Solo - Local Starter</option>
                                            <option value="solo_growth">Solo - Local Growth</option>
                                            <option value="solo_ultimate">Solo - Local Ultimate</option>
                                        </optgroup>
                                        <optgroup label="Bundle Plans">
                                            <option value="bundle_starter">Bundle - Local Starter</option>
                                            <option value="bundle_growth">Bundle - Local Growth</option>
                                            <option value="bundle_ultimate">Bundle - Local Ultimate</option>
                                        </optgroup>
                                    </select>
                                    <?php endif; ?>
                                    <div class="invalid-feedback">Please select the current product.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="newProduct" class="form-label">New Product <span class="text-danger">*</span></label>
                                    <select class="form-select" id="newProduct" name="newProduct">
                                        <option value="">-- Select New Product --</option>
                                        <optgroup label="Pro Plan (Website Only)">
                                            <option value="pro_plan">Pro Plan (No Marketing)</option>
                                        </optgroup>
                                        <optgroup label="Pro Marketing Plans">
                                            <option value="pro_starter">Pro - Local Starter</option>
                                            <option value="pro_growth">Pro - Local Growth</option>
                                            <option value="pro_ultimate">Pro - Local Ultimate</option>
                                        </optgroup>
                                        <optgroup label="Solo Plans">
                                            <option value="solo_starter">Solo - Local Starter</option>
                                            <option value="solo_growth">Solo - Local Growth</option>
                                            <option value="solo_ultimate">Solo - Local Ultimate</option>
                                        </optgroup>
                                        <optgroup label="Bundle Plans">
                                            <option value="bundle_starter">Bundle - Local Starter</option>
                                            <option value="bundle_growth">Bundle - Local Growth</option>
                                            <option value="bundle_ultimate">Bundle - Local Ultimate</option>
                                        </optgroup>
                                    </select>
                                    <div class="invalid-feedback">Please select the new product.</div>
                                </div>
                            </div>

                            <!-- Dynamic Downgrade Comparison Card -->
                            <div id="downgradeComparisonCard" class="mb-3" style="display:none;"></div>

                            <?php if ($mode === 'staff'): ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="contractPeriod" class="form-label">Contract Period</label>
                                    <input type="text" class="form-control" id="contractPeriod" name="contractPeriod" placeholder="e.g. 12 months">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="accountManager" class="form-label">Account Manager</label>
                                    <input type="text" class="form-control" id="accountManager" name="accountManager" placeholder="e.g. John D.">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="billingDate" class="form-label">Billing Date</label>
                                    <input type="date" class="form-control" id="billingDate" name="billingDate">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="staffDowngradeNote" class="form-label">Internal Note</label>
                                <textarea class="form-control" id="staffDowngradeNote" name="staffDowngradeNote" rows="2" placeholder="Any internal notes about this downgrade request..."></textarea>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- ========== SECTION: Effective Date ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-calendar-event"></i> When would you like this downgrade/unsub to take effect? <span class="text-danger">*</span></h5>
                            <div class="mb-3">
                                <div class="radio-card-group-vertical">
                                    <div class="radio-card-v">
                                        <input type="radio" name="effectiveDate" id="effImmediate" value="Immediately" required>
                                        <label for="effImmediate"><i class="bi bi-lightning-charge"></i> Immediately</label>
                                    </div>
                                    <div class="radio-card-v">
                                        <input type="radio" name="effectiveDate" id="effEndBilling" value="End of current billing cycle">
                                        <label for="effEndBilling"><i class="bi bi-calendar-check"></i> End of current billing cycle &nbsp;<small class="text-muted">(cancel after 30 days)</small></label>
                                    </div>
                                    <div class="radio-card-v">
                                        <input type="radio" name="effectiveDate" id="effEndContract" value="At the end of contract">
                                        <label for="effEndContract"><i class="bi bi-file-earmark-text"></i> At the end of contract</label>
                                    </div>
                                    <div class="radio-card-v">
                                        <input type="radio" name="effectiveDate" id="effNotSure" value="Not sure - please contact me">
                                        <label for="effNotSure"><i class="bi bi-question-circle"></i> Not sure &rarr; Please contact me</label>
                                    </div>
                                    <div class="radio-card-v">
                                        <input type="radio" name="effectiveDate" id="effCustomDate" value="Custom Date">
                                        <label for="effCustomDate"><i class="bi bi-calendar-plus"></i> Custom Date</label>
                                    </div>
                                </div>
                                <div class="mt-3" id="customDateGroup" style="display:none;">
                                    <label for="customDate" class="form-label">Select Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="customDate" name="customDate" style="max-width: 260px;">
                                </div>
                            </div>
                        </div>

                        <!-- ========== SECTION: Reason for Downgrade ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-chat-square-text"></i> Reason for Downgrade/Unsubscription <span class="text-danger">*</span></h5>
                            <p class="text-muted mb-3" style="font-size:.84rem;">Select all that apply.</p>
                            <div class="checkbox-grid mb-3">
                                <div class="check-card">
                                    <input type="checkbox" id="reasonSelling" name="reasons[]" value="Selling or transferring my business">
                                    <label for="reasonSelling"><i class="bi bi-building-check"></i> Selling or transferring my business</label>
                                </div>
                                <div class="check-card">
                                    <input type="checkbox" id="reasonClosing" name="reasons[]" value="Closing down my business">
                                    <label for="reasonClosing"><i class="bi bi-shop"></i> Closing down my business</label>
                                </div>
                                <div class="check-card">
                                    <input type="checkbox" id="reasonBudget" name="reasons[]" value="Budget constraints">
                                    <label for="reasonBudget"><i class="bi bi-piggy-bank"></i> Budget constraints</label>
                                </div>
                                <div class="check-card">
                                    <input type="checkbox" id="reasonResults" name="reasons[]" value="Results did not meet my expectations">
                                    <label for="reasonResults"><i class="bi bi-graph-down"></i> Results did not meet my expectations</label>
                                </div>
                                <div class="check-card">
                                    <input type="checkbox" id="reasonPlatform" name="reasons[]" value="Switching to another platform or agency">
                                    <label for="reasonPlatform"><i class="bi bi-box-arrow-right"></i> Switching to another platform or agency</label>
                                </div>
                                <div class="check-card">
                                    <input type="checkbox" id="reasonComplexity" name="reasons[]" value="Platform or Product Complexity">
                                    <label for="reasonComplexity"><i class="bi bi-emoji-frown"></i> Platform or Product Complexity</label>
                                </div>
                                <div class="check-card">
                                    <input type="checkbox" id="reasonSupport" name="reasons[]" value="Dissatisfied with the service or support team">
                                    <label for="reasonSupport"><i class="bi bi-headset"></i> Dissatisfied with the service or support team</label>
                                </div>
                                <div class="check-card">
                                    <input type="checkbox" id="reasonOwnership" name="reasons[]" value="Change in ownership or management direction">
                                    <label for="reasonOwnership"><i class="bi bi-people"></i> Change in ownership or management direction</label>
                                </div>
                                <div class="check-card">
                                    <input type="checkbox" id="reasonObjectives" name="reasons[]" value="Achieved Objectives">
                                    <label for="reasonObjectives"><i class="bi bi-trophy"></i> Achieved Objectives</label>
                                </div>
                                <div class="check-card">
                                    <input type="checkbox" id="reasonOther" name="reasons[]" value="Other">
                                    <label for="reasonOther"><i class="bi bi-three-dots"></i> Other</label>
                                </div>
                            </div>
                            <div id="otherReasonGroup" style="display:none;">
                                <label for="otherReason" class="form-label">Please specify</label>
                                <input type="text" class="form-control" id="otherReason" name="otherReason" placeholder="Please describe your reason...">
                            </div>
                        </div>

                        <!-- ========== SECTION: Features to Keep ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-bookmark-heart"></i> Useful Features to Keep Activated</h5>
                            <p class="text-muted mb-3" style="font-size:.84rem;">Select any features you would like to remain active after the change.</p>
                            <div class="checkbox-grid">
                                <div class="check-card">
                                    <input type="checkbox" id="keepAraya" name="featuresKeep[]" value="Araya (Massage) $199/month">
                                    <label for="keepAraya"><i class="bi bi-robot"></i> Araya (Massage) <small>$199/mo</small></label>
                                </div>
                                <div class="check-card">
                                    <input type="checkbox" id="keepAiMarketing" name="featuresKeep[]" value="AI Marketing (Restaurant) 10% per order">
                                    <label for="keepAiMarketing"><i class="bi bi-stars"></i> AI Marketing (Restaurant) <small>10%/order</small></label>
                                </div>
                                <div class="check-card">
                                    <input type="checkbox" id="keepNone" name="featuresKeep[]" value="None">
                                    <label for="keepNone"><i class="bi bi-x-circle"></i> None</label>
                                </div>
                            </div>
                        </div>

                        <!-- ========== SECTION: Additional Details (For Improvement) ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-clipboard2-pulse"></i> Additional Details (For Improvement)</h5>

                            <div class="mb-4">
                                <label for="improvementSuggestion" class="form-label">What could we improve to keep you with us?</label>
                                <textarea class="form-control" id="improvementSuggestion" name="improvementSuggestion" rows="3" placeholder="Your feedback helps us improve our services..."></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Would you like us to contact you to discuss alternative options? <span class="text-danger">*</span></label>
                                <div class="radio-card-group-vertical">
                                    <div class="radio-card-v">
                                        <input type="radio" name="contactPreference" id="contactYes" value="Yes, please contact me" required>
                                        <label for="contactYes"><i class="bi bi-telephone-outbound"></i> Yes, please contact me.</label>
                                    </div>
                                    <div class="radio-card-v">
                                        <input type="radio" name="contactPreference" id="contactNo" value="No, please proceed with downgrade/unsubscription">
                                        <label for="contactNo"><i class="bi bi-check-circle"></i> No, please proceed with downgrade / unsubscription.</label>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block" id="contactPreferenceError" style="display:none!important;"></div>
                            </div>

                            <div class="sub-section">
                                <h6 class="sub-section-title"><i class="bi bi-headset"></i> Feedback on Customer Support / Account Manager</h6>

                                <div class="mb-3">
                                    <label for="feedbackComments" class="form-label">Comments</label>
                                    <textarea class="form-control" id="feedbackComments" name="feedbackComments" rows="2" placeholder="Any specific feedback about your account manager or support experience..."></textarea>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">How was your experience? (1–5 stars)</label>
                                    <div class="star-rating-widget" id="experienceRatingWidget">
                                        <span class="star" data-value="1"><i class="bi bi-star-fill"></i></span>
                                        <span class="star" data-value="2"><i class="bi bi-star-fill"></i></span>
                                        <span class="star" data-value="3"><i class="bi bi-star-fill"></i></span>
                                        <span class="star" data-value="4"><i class="bi bi-star-fill"></i></span>
                                        <span class="star" data-value="5"><i class="bi bi-star-fill"></i></span>
                                        <span class="star-label" id="expRatingLabel"></span>
                                    </div>
                                    <input type="hidden" name="experienceRating" id="experienceRating" value="">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Rate your overall experience with Local For You: (1–5 stars)</label>
                                <div class="star-rating-widget" id="overallRatingWidget">
                                    <span class="star" data-value="1"><i class="bi bi-star-fill"></i></span>
                                    <span class="star" data-value="2"><i class="bi bi-star-fill"></i></span>
                                    <span class="star" data-value="3"><i class="bi bi-star-fill"></i></span>
                                    <span class="star" data-value="4"><i class="bi bi-star-fill"></i></span>
                                    <span class="star" data-value="5"><i class="bi bi-star-fill"></i></span>
                                    <span class="star-label" id="overallRatingLabel"></span>
                                </div>
                                <input type="hidden" name="overallRating" id="overallRating" value="">
                            </div>
                        </div>

                        <!-- ========== SECTION: Additional Comments ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-chat-dots"></i> Additional Comments</h5>
                            <div class="mb-3">
                                <textarea class="form-control" id="additionalComments" name="additionalComments" rows="3" placeholder="Any additional information..."></textarea>
                            </div>
                        </div>

                        <!-- ===== Submit ===== -->
                        <div class="submit-area">
                            <button type="submit" id="cmdSubmit" class="btn btn-submit"><i class="bi bi-send"></i> Submit Form</button>
                            <div id="result" class="mt-3"></div>
                        </div>

                        <input type="hidden" name="formVersion" value="1.0.0">
                        <input type="hidden" name="formType" value="downgrade">
                        <input type="hidden" name="formMode" value="<?php echo htmlspecialchars($mode); ?>">
                        <input type="hidden" name="stripeID" id="stripeID" value="<?php echo htmlspecialchars($stripeID ?? ''); ?>">
                        <input type="hidden" id="statusUser" name="statusUser" value="completed">
                        <input type="hidden" name="testMode" id="testMode" value="<?php echo htmlspecialchars($testMode); ?>">
                    </form>
                </div>
            </div>
            </div><!-- /form-layout -->
        </section>
    </main>
</div>

<?php include '../layout/footer.php'; ?>


<!-- ===== Modal: Package Reference ===== -->
<div class="modal fade" id="modalPackageRef" tabindex="-1" aria-labelledby="modalPackageRefLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPackageRefLabel"><i class="bi bi-box-seam me-2 text-danger"></i>Current Package Reference</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="pkg-compare-grid">
                    <div class="pkg-column">
                        <div class="pkg-column-header pkg-starter">
                            <i class="bi bi-rocket-takeoff"></i>
                            <h6>Local Starter</h6>
                        </div>
                        <ul class="pkg-feature-list">
                            <li><i class="bi bi-check2"></i> 1 Ad Channel (Google or FB/IG)</li>
                            <li><i class="bi bi-check2"></i> Yelp Ads Management</li>
                            <li><i class="bi bi-check2"></i> Proactive Review Replies</li>
                            <li><i class="bi bi-check2"></i> 2 GMB Posts/Month</li>
                            <li class="pkg-feature-disabled"><i class="bi bi-x"></i> Social Media Posts</li>
                            <li><i class="bi bi-check2"></i> 1 Email Campaign/Month</li>
                            <li><i class="bi bi-check2"></i> 2 SMS Campaigns/Month</li>
                            <li class="pkg-feature-disabled"><i class="bi bi-x"></i> Advanced SEO</li>
                            <li><i class="bi bi-check2"></i> Strategy Call</li>
                            <li><i class="bi bi-check2"></i> Monthly Report</li>
                        </ul>
                    </div>
                    <div class="pkg-column pkg-column-highlight">
                        <div class="pkg-column-header pkg-growth">
                            <span class="pkg-popular-badge">Popular</span>
                            <i class="bi bi-graph-up-arrow"></i>
                            <h6>Local Growth</h6>
                        </div>
                        <ul class="pkg-feature-list">
                            <li><i class="bi bi-check2"></i> Google + FB/IG Ads</li>
                            <li><i class="bi bi-check2"></i> Yelp Ads Management</li>
                            <li><i class="bi bi-check2"></i> Proactive Review Replies</li>
                            <li><i class="bi bi-check2"></i> 4 GMB Posts/Month</li>
                            <li><i class="bi bi-check2"></i> 4 Social Media Posts/Month</li>
                            <li><i class="bi bi-check2"></i> 2 Email Campaigns/Month</li>
                            <li><i class="bi bi-check2"></i> 2 SMS Campaigns/Month</li>
                            <li class="pkg-feature-disabled"><i class="bi bi-x"></i> Advanced SEO</li>
                            <li><i class="bi bi-check2"></i> Monthly Strategy Call</li>
                            <li><i class="bi bi-check2"></i> Monthly Report</li>
                        </ul>
                    </div>
                    <div class="pkg-column">
                        <div class="pkg-column-header pkg-ultimate">
                            <i class="bi bi-trophy"></i>
                            <h6>Local Ultimate</h6>
                        </div>
                        <ul class="pkg-feature-list">
                            <li><i class="bi bi-check2"></i> Google + FB/IG + Advanced Ads</li>
                            <li><i class="bi bi-check2"></i> Yelp Ads</li>
                            <li><i class="bi bi-check2"></i> Proactive Review Replies</li>
                            <li><i class="bi bi-check2"></i> 8 GMB Posts/Month</li>
                            <li><i class="bi bi-check2"></i> 8 Social Media Posts/Month</li>
                            <li><i class="bi bi-check2"></i> 4 Email Campaigns/Month</li>
                            <li><i class="bi bi-check2"></i> 4 SMS Campaigns/Month</li>
                            <li><i class="bi bi-check2"></i> Advanced SEO Components</li>
                            <li><i class="bi bi-check2"></i> Monthly Strategy Call</li>
                            <li><i class="bi bi-check2"></i> Monthly Report</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== Modal: Important Information ===== -->
<div class="modal fade" id="modalImportantInfo" tabindex="-1" aria-labelledby="modalImportantInfoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalImportantInfoLabel"><i class="bi bi-info-circle me-2 text-danger"></i>Important Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="notice-card notice-cancel">
                    <div class="notice-icon"><i class="bi bi-calendar-x"></i></div>
                    <div>
                        <strong>30-Day Cancellation Notice</strong>
                        <p class="mb-0">All cancellations require a <strong>30-day advance notice</strong>. Please notify us at least 30 days before your next billing date if you wish to cancel or downgrade your service.</p>
                    </div>
                </div>
                <div class="notice-card notice-contract">
                    <div class="notice-icon"><i class="bi bi-file-earmark-lock"></i></div>
                    <div>
                        <strong>Contract &amp; Service Fee</strong>
                        <p class="mb-0">If the contract has expired, the service fee will remain as per the contract selected until cancelled or changed to another contract.</p>
                    </div>
                </div>
                <div class="notice-card notice-stripe">
                    <div class="notice-icon"><i class="bi bi-shield-check"></i></div>
                    <div>
                        <strong>Service Changes</strong>
                        <p class="mb-0">Upon downgrade or unsubscription, certain features will be deactivated based on your new plan. Please review your selection carefully before submitting.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== Modal: Staff Knowledge Base ===== -->
<div class="modal fade" id="modalStaffKB" tabindex="-1" aria-labelledby="modalStaffKBLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#fffdf5; border-bottom:2px dashed #ffc107;">
                <h5 class="modal-title" id="modalStaffKBLabel">
                    <i class="bi bi-book me-2" style="color:#d48f00;"></i>Staff Knowledge Base
                    <span class="badge bg-warning text-dark ms-2" style="font-size:.65rem;">Staff Only</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="background:#fffdf5;">
                <div class="mb-3">
                    <label class="form-label">Package Reference Materials <small class="text-muted">(file, link, image)</small></label>
                    <input type="text" class="form-control" name="staffPackageRef" placeholder="e.g. Google Drive link, file path, or image URL">
                </div>
                <div class="info-card info-cancel mb-3">
                    <h6><i class="bi bi-calendar-x"></i> Cancellation &amp; Downgrade Policy</h6>
                    <ul class="mb-0">
                        <li>All cancellations require a <strong>30-day advance notice</strong>.</li>
                        <li>If the contract has not expired, check with management before processing.</li>
                        <li>Billing continues until the 30-day notice period has passed.</li>
                        <li>"End of billing cycle" option = cancel after 30 days.</li>
                    </ul>
                </div>
                <div class="info-card info-downgrade mb-3">
                    <h6><i class="bi bi-arrow-down-circle"></i> Downgrade Feature Changes</h6>
                    <ul class="mb-0">
                        <li><strong>Ultimate &rarr; Growth:</strong> GMB 8&rarr;4, Social 8&rarr;4, Email 4&rarr;2, SMS 4&rarr;2, Remove Advanced SEO.</li>
                        <li><strong>Ultimate &rarr; Starter:</strong> GMB 8&rarr;2, Social 8&rarr;0, Email 4&rarr;1, SMS 4&rarr;1, Remove SEO, Ads all&rarr;1 channel, Remove Strategy Call.</li>
                        <li><strong>Growth &rarr; Starter:</strong> GMB 4&rarr;2, Social 4&rarr;0, Email 2&rarr;1, SMS 2&rarr;1, Ads all&rarr;1 channel, Remove Strategy Call.</li>
                        <li><strong>Any &rarr; Pro Plan:</strong> Remove <em>all</em> marketing-related features.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../controllers/downgradeForm.js?v=1.0.0"></script>
</body>
</html>
