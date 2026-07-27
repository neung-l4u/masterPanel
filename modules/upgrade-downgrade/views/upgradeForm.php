<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;

$stripeID = $_GET['stripeID'] ?? '$_get=stripeID';
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
    <link href="../assets/css/upgradeForm.css?v=2.0.0" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Upgrade Form - Local For You</title>
</head>
<body data-mode="<?php echo htmlspecialchars($mode); ?>">

<div class="container">
    <main>
        <section style="min-height: 50vh;">
            <div class="form-layout">
                <nav class="info-sidebar">
                    <div class="sidebar-section">
                        <h6 class="sidebar-title">Form Type</h6>
                        <a href="upgradeForm.php?mode=<?php echo htmlspecialchars($mode); ?>&stripeID=<?php echo urlencode($stripeID); ?>" class="sidebar-item sidebar-form sidebar-form-upgrade sidebar-mode-active">
                            <i class="bi bi-arrow-up-circle"></i>
                            <span>Upgrade</span>
                        </a>
                        <a href="downgradeForm.php?mode=<?php echo htmlspecialchars($mode); ?>&stripeID=<?php echo urlencode($stripeID); ?>" class="sidebar-item sidebar-form sidebar-form-downgrade">
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
                        <button class="sidebar-item sidebar-action" data-bs-toggle="modal" data-bs-target="#modalPackageDetails">
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
                    <h3 class="form-title text-uppercase">Upgrade / Add-on Form</h3>
                    <p class="form-subtitle">
                        <?php if ($mode === 'customer'): ?>
                            Please review the upgrade details below and complete this form to proceed with your package upgrade or add-on purchase.
                        <?php else: ?>
                            Internal upgrade &amp; add-on management form. Fill in all relevant details for the customer's upgrade.
                        <?php endif; ?>
                    </p>
                </div>

                <!-- ===== Form Body ===== -->
                <div class="form-body">


                    <!-- ============================================================ -->
                    <!-- MAIN FORM                                                    -->
                    <!-- ============================================================ -->
                    <form id="upgradeForm" name="upgradeForm" method="post" enctype="multipart/form-data">

                        <!-- ========== SECTION: Shop Details ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-shop-window"></i> Shop Details</h5>

                            <div class="mb-3">
                                <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                                <select class="form-select" id="country" name="country" required>
                                    <option value="">-- Select Country --</option>
                                    <option value="US">United States</option>
                                    <option value="CA">Canada</option>
                                    <option value="AU">Australia</option>
                                    <option value="NZ">New Zealand</option>
                                    <option value="UK">United Kingdom</option>
                                    <option value="TH">Thailand</option>
                                </select>
                                <div class="invalid-feedback">Please select a country.</div>
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="mondayProjectId" class="form-label">Monday Project / Shop ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="mondayProjectId" name="mondayProjectId" placeholder="Type shop name or ID to search..." autocomplete="off" required>
                                <input type="hidden" id="boardId" name="boardId" value="">
                                <div id="projectSearchDropdown" class="project-search-dropdown"></div>
                                <div class="form-text">Search by shop name or ID. Other fields will auto-fill.</div>
                                <div class="invalid-feedback">Please enter the Monday Project / Shop ID.</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="shopName" class="form-label">Shop Name</label>
                                    <input type="text" class="form-control" id="shopName" name="shopName" placeholder="Auto-filled from ID">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="shopType" class="form-label">Shop Type</label>
                                    <input type="text" class="form-control" id="shopType" name="shopType" placeholder="Auto-filled from ID">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ownerName" class="form-label">Owner's Name</label>
                                    <input type="text" class="form-control" id="ownerName" name="ownerName" placeholder="Auto-filled from ID">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phoneNumber" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" placeholder="Auto-filled from ID">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="emailAddress" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="emailAddress" name="emailAddress" placeholder="e.g. owner@restaurant.com" required>
                                <div class="invalid-feedback">Please enter an email address.</div>
                            </div>

                            <div class="row">
                                <div class="col-md mb-3">
                                    <label for="bestTimeContact" class="form-label">Best Time to Contact <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="bestTimeContact" name="bestTimeContact" placeholder="e.g. Weekdays 10am-2pm PST" required>
                                    <div class="invalid-feedback">Please enter the best time to contact.</div>
                                </div>
                            </div>
                        </div>

                        <!-- ========== SECTION: Upgrade Type ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-arrow-up-circle"></i> What would you like to do?</h5>
                            <div class="mb-3">
                                <div class="radio-card-group">
                                    <div class="radio-card">
                                        <input type="radio" name="upgradeType" id="typeUpgradePackage" value="upgrade_package" required>
                                        <label for="typeUpgradePackage"><i class="bi bi-box-arrow-up"></i> Upgrade Package</label>
                                    </div>
                                    <div class="radio-card">
                                        <input type="radio" name="upgradeType" id="typeAddonOnly" value="addon_only">
                                        <label for="typeAddonOnly"><i class="bi bi-plus-circle"></i> Add-on Only</label>
                                    </div>
                                </div>
                                <div class="invalid-feedback">Please select an upgrade type.</div>
                            </div>
                        </div>

                        <!-- ========== SECTION: Upgrade Information (Package Upgrade) ========== -->
                        <div class="form-section" id="upgradeInfoSection" style="display:none;">
                            <h5 class="section-title"><i class="bi bi-arrow-repeat"></i> Upgrade Information</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Original Product (Active Subscriptions)</label>
                                    <div id="originalProductList" class="subscription-list">
                                        <span class="subscription-placeholder">Select a project first</span>
                                    </div>
                                    <input type="hidden" id="originalProduct" name="originalProduct">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="newProduct" class="form-label">New Product <span class="text-danger">*</span></label>
                                    <select class="form-select" id="newProduct" name="newProduct">
                                        <option value="">-- Select New Product --</option>
                                        <optgroup label="Pro Plans">
                                            <option value="pro_starter">Pro - Local Starter</option>
                                            <option value="pro_growth">Pro - Local Growth</option>
                                            <option value="pro_ultimate">Pro - Local Ultimate</option>
                                        </optgroup>
                                        <optgroup label="Bundle Plans">
                                            <option value="bundle_starter">Bundle - Local Starter</option>
                                            <option value="bundle_growth">Bundle - Local Growth</option>
                                            <option value="bundle_ultimate">Bundle - Local Ultimate</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>

                            <!-- Dynamic Package Comparison -->
                            <div id="packageComparisonCard" class="mb-3" style="display:none;"></div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="promotion" class="form-label">Promotion</label>
                                    <input type="text" class="form-control" id="promotion" name="promotion" placeholder="e.g. 20% off first 3 months">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="contractPeriod" class="form-label">Contract Period <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="contractPeriod" name="contractPeriod" placeholder="e.g. 12 months">
                                    <div class="invalid-feedback">Please enter the contract period.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="upgradeReason" class="form-label">Reason of Upgrade / Upgrade Purpose <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="upgradeReason" name="upgradeReason" rows="2" placeholder="e.g. Want more repeat customers / Want to advertise for walk-in customers"></textarea>
                                <div class="invalid-feedback">Please enter the reason for upgrade.</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="salesAgent" class="form-label">Sales Agent / Upgrade Person <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="salesAgent" name="salesAgent" placeholder="e.g. John D.">
                                    <div class="invalid-feedback">Please enter the sales agent name.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="billingDate" class="form-label">Billing Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="billingDate" name="billingDate">
                                    <div class="invalid-feedback">Please enter the billing date.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="upgradeNote" class="form-label">Note</label>
                                <textarea class="form-control" id="upgradeNote" name="upgradeNote" rows="2" placeholder="Any additional notes about this upgrade..."></textarea>
                            </div>
                        </div>

                        <!-- ========== SECTION: Add-on Products ========== -->
                        <div class="form-section" id="addonSection" style="display:none;">
                            <h5 class="section-title"><i class="bi bi-bag-plus"></i> Add-on Products <small class="text-muted">(Purchase / Not free offer)</small></h5>
                            <div class="checkbox-grid">
                                <div class="check-card"><input type="checkbox" id="addonWebTemplate" name="addons[]" value="Website Template"><label for="addonWebTemplate"><i class="bi bi-layout-text-window-reverse"></i> Website Template</label></div>
                                <div class="check-card"><input type="checkbox" id="addonWebMakeover" name="addons[]" value="Website Makeover"><label for="addonWebMakeover"><i class="bi bi-brush"></i> Website Makeover</label></div>
                                <div class="check-card"><input type="checkbox" id="addonAraya" name="addons[]" value="ARAYA (Massage)"><label for="addonAraya"><i class="bi bi-robot"></i> ARAYA (Massage)</label></div>
                                <div class="check-card"><input type="checkbox" id="addonAiMarketing" name="addons[]" value="AI Marketing (Restaurant)"><label for="addonAiMarketing"><i class="bi bi-stars"></i> AI Marketing (Restaurant)</label></div>
                                <div class="check-card"><input type="checkbox" id="addonUnlimitedPromo" name="addons[]" value="Unlimited Promotion (Restaurant)"><label for="addonUnlimitedPromo"><i class="bi bi-megaphone"></i> Unlimited Promo (Restaurant)</label></div>
                                <div class="check-card"><input type="checkbox" id="addonSocialMedia" name="addons[]" value="Social Media Post"><label for="addonSocialMedia"><i class="bi bi-share"></i> Social Media Post</label></div>
                                <div class="check-card"><input type="checkbox" id="addonSMS" name="addons[]" value="SMS Marketing"><label for="addonSMS"><i class="bi bi-chat-left-text"></i> SMS Marketing</label></div>
                                <div class="check-card"><input type="checkbox" id="addonDineIn" name="addons[]" value="Dine-in Dual"><label for="addonDineIn"><i class="bi bi-cup-straw"></i> Dine-in Dual</label></div>
                                <div class="check-card"><input type="checkbox" id="addonDelivery" name="addons[]" value="Inhouse Delivery"><label for="addonDelivery"><i class="bi bi-truck"></i> Inhouse Delivery</label></div>
                            </div>
                        </div>

                        <!-- ========== SECTION: Social Media Access ========== -->
                        <div class="form-section" id="socialMediaSection" style="display:none;">
                            <h5 class="section-title"><i class="bi bi-globe2"></i> Social Media Access</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="facebookPage" class="form-label">Facebook Page</label>
                                    <input type="url" class="form-control" id="facebookPage" name="facebookPage" placeholder="https://facebook.com/yourpage">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="googleBP" class="form-label">Google Business Profile</label>
                                    <input type="url" class="form-control" id="googleBP" name="googleBP" placeholder="https://business.google.com/...">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="yelpPage" class="form-label">Yelp</label>
                                    <input type="url" class="form-control" id="yelpPage" name="yelpPage" placeholder="https://yelp.com/biz/...">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lineOA" class="form-label">Line Official Account</label>
                                    <input type="text" class="form-control" id="lineOA" name="lineOA" placeholder="e.g. @yourshop">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="tiktokPage" class="form-label">TikTok</label>
                                <input type="url" class="form-control" id="tiktokPage" name="tiktokPage" placeholder="https://tiktok.com/@yourshop">
                            </div>
                        </div>

                        <!-- ========== SECTION: Solo->Bundle System Build ========== -->
                        <div class="form-section" id="soloBundleSection" style="display:none;">
                            <h5 class="section-title"><i class="bi bi-gear-wide-connected"></i> System Build Details <span class="badge bg-info text-dark ms-2" style="font-size:.65rem;">Solo &rarr; Bundle</span></h5>

                            <div class="mb-3">
                                <label for="systemEmail" class="form-label">Email for System Login <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="systemEmail" name="systemEmail" placeholder="e.g. owner@restaurant.com">
                                <div class="invalid-feedback">Please enter an email for system login.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Menu / Logo / Picture</label>
                                <select class="form-select" id="menuLogoStatus" name="menuLogoStatus">
                                    <option value="">-- Select Status --</option>
                                    <option value="google_drive">Google Drive Link</option>
                                    <option value="received">Received</option>
                                    <option value="requested">Requested</option>
                                </select>
                                <input type="url" class="form-control mt-2" id="menuLogoLink" name="menuLogoLink" placeholder="Google Drive link" style="display:none;">
                            </div>

                            <div class="restaurant-fields">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="ownDeliveryArea" class="form-label">(Restaurant) Own Delivery Area &amp; Fee</label>
                                        <textarea class="form-control" id="ownDeliveryArea" name="ownDeliveryArea" rows="2" placeholder="e.g. 5 mile radius - $3.99"></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="inhouseDeliveryArea" class="form-label">(Restaurant) In-House Delivery Area &amp; Fee</label>
                                        <textarea class="form-control" id="inhouseDeliveryArea" name="inhouseDeliveryArea" rows="2" placeholder="e.g. Zone A: $2.99, Zone B: $4.99"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Online Payment (Stripe Account) <span class="text-danger">*</span></label>
                                <div class="radio-card-group radio-card-group-3">
                                    <div class="radio-card"><input type="radio" name="stripeStatus" id="stripeNoWant" value="Don't Want Online Payment"><label for="stripeNoWant"><i class="bi bi-x-circle"></i> Don't Want</label></div>
                                    <div class="radio-card"><input type="radio" name="stripeStatus" id="stripeWaiting" value="Waiting"><label for="stripeWaiting"><i class="bi bi-hourglass-split"></i> Waiting</label></div>
                                    <div class="radio-card"><input type="radio" name="stripeStatus" id="stripeConnected" value="Connected"><label for="stripeConnected"><i class="bi bi-check-circle"></i> Connected</label></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="openingDayTime" class="form-label">Opening Day / Time <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="openingDayTime" name="openingDayTime" rows="2" placeholder="e.g. Mon-Sat 11am-9pm, Sun 12pm-8pm"></textarea>
                                <div class="invalid-feedback">Please enter opening day/time.</div>
                            </div>

                            <div class="restaurant-fields">
                                <div class="mb-3">
                                    <label class="form-label">(Restaurant) Services Provided</label>
                                    <div class="checkbox-grid checkbox-grid-sm">
                                        <div class="check-card"><input type="checkbox" id="svcPickUp" name="restaurantServices[]" value="Pick Up"><label for="svcPickUp"><i class="bi bi-bag"></i> Pick Up</label></div>
                                        <div class="check-card"><input type="checkbox" id="svcHomeDelivery" name="restaurantServices[]" value="Home Delivery"><label for="svcHomeDelivery"><i class="bi bi-house-door"></i> Home Delivery</label></div>
                                        <div class="check-card"><input type="checkbox" id="svcInhouseDelivery" name="restaurantServices[]" value="Inhouse Delivery"><label for="svcInhouseDelivery"><i class="bi bi-truck"></i> Inhouse Delivery</label></div>
                                        <div class="check-card"><input type="checkbox" id="svcTableReserve" name="restaurantServices[]" value="Table Reservation"><label for="svcTableReserve"><i class="bi bi-calendar-check"></i> Table Reservation</label></div>
                                        <div class="check-card"><input type="checkbox" id="svcDineIn" name="restaurantServices[]" value="On Premise (Dine-in)"><label for="svcDineIn"><i class="bi bi-cup-straw"></i> On Premise (Dine-in)</label></div>
                                        <div class="check-card"><input type="checkbox" id="svcScheduled" name="restaurantServices[]" value="Scheduled Orders"><label for="svcScheduled"><i class="bi bi-clock-history"></i> Scheduled Orders</label></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">(Restaurant) AI Marketing Activation</label>
                                    <div class="radio-card-group">
                                        <div class="radio-card"><input type="radio" name="aiMarketingActivation" id="aiMarketingYes" value="Yes"><label for="aiMarketingYes"><i class="bi bi-check-lg"></i> Yes</label></div>
                                        <div class="radio-card"><input type="radio" name="aiMarketingActivation" id="aiMarketingNo" value="No"><label for="aiMarketingNo"><i class="bi bi-x-lg"></i> No</label></div>
                                    </div>
                                    <div class="form-text">Note: AI Marketing activation should be done by the customer themselves.</div>
                                </div>
                            </div>

                            <!-- Website -->
                            <div class="sub-section">
                                <h6 class="sub-section-title"><i class="bi bi-globe"></i> Website</h6>
                                <div class="mb-3">
                                    <div class="radio-card-group">
                                        <div class="radio-card"><input type="radio" name="websiteOption" id="webOptAddButton" value="Add button/amelia to their own website"><label for="webOptAddButton"><i class="bi bi-plus-square"></i> Add to own site</label></div>
                                        <div class="radio-card"><input type="radio" name="websiteOption" id="webOptGF" value="Need GF website"><label for="webOptGF"><i class="bi bi-layout-text-window"></i> Need GF Website</label></div>
                                    </div>
                                </div>
                                <div class="mb-3" id="websiteTemplateGroup" style="display:none;">
                                    <label for="websiteTemplate" class="form-label">Website Template</label>
                                    <select class="form-select" id="websiteTemplate" name="websiteTemplate">
                                        <option value="">-- Select Template --</option>
                                        <option value="restaurant_1">Restaurant Template 1</option>
                                        <option value="restaurant_2">Restaurant Template 2</option>
                                        <option value="restaurant_3">Restaurant Template 3</option>
                                        <option value="massage_1">Massage Template 1</option>
                                    </select>
                                    <div class="form-text">Colors &amp; Tone: Will follow template colour preview</div>
                                </div>
                                <div class="mb-3">
                                    <label for="websiteOther" class="form-label">Other Website Notes</label>
                                    <input type="text" class="form-control" id="websiteOther" name="websiteOther" placeholder="Any other website requirements...">
                                </div>
                            </div>

                            <!-- Domain Hosting -->
                            <div class="sub-section">
                                <h6 class="sub-section-title"><i class="bi bi-hdd-network"></i> Domain Hosting</h6>
                                <div class="mb-3">
                                    <div class="radio-card-group-vertical">
                                        <div class="radio-card-v"><input type="radio" name="domainOption" id="domainBuyNew" value="Buy New with Localforyou Domain"><label for="domainBuyNew">Buy New with Localforyou Domain</label></div>
                                        <div class="radio-card-v"><input type="radio" name="domainOption" id="domainTransfer" value="Transfer In to L4U Domain"><label for="domainTransfer">Transfer In to L4U Domain</label></div>
                                        <div class="radio-card-v"><input type="radio" name="domainOption" id="domainDNS" value="Add DNS (to Another Domain Hosting)"><label for="domainDNS">Add DNS (to Another Domain Hosting)</label></div>
                                        <div class="radio-card-v"><input type="radio" name="domainOption" id="domainOtherRadio" value="Other"><label for="domainOtherRadio">Other</label></div>
                                    </div>
                                </div>
                                <div class="mb-3" id="domainOtherGroup" style="display:none;">
                                    <input type="text" class="form-control" id="domainOtherText" name="domainOtherText" placeholder="Please specify domain hosting details...">
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="sub-section">
                                <h6 class="sub-section-title"><i class="bi bi-credit-card"></i> Payment Method</h6>
                                <div class="checkbox-grid checkbox-grid-sm">
                                    <div class="check-card"><input type="checkbox" id="payCash" name="paymentMethods[]" value="Cash"><label for="payCash"><i class="bi bi-cash"></i> Cash</label></div>
                                    <div class="check-card"><input type="checkbox" id="payCard" name="paymentMethods[]" value="Card at Counter"><label for="payCard"><i class="bi bi-credit-card-2-front"></i> Card at Counter</label></div>
                                    <div class="check-card"><input type="checkbox" id="payOnline" name="paymentMethods[]" value="Online Payment"><label for="payOnline"><i class="bi bi-phone"></i> Online Payment</label></div>
                                </div>
                            </div>

                            <!-- Restaurant QR Code -->
                            <div class="sub-section restaurant-fields">
                                <h6 class="sub-section-title"><i class="bi bi-qr-code"></i> (Restaurant) Dine-in QR Code</h6>
                                <div class="radio-card-group">
                                    <div class="radio-card"><input type="radio" name="qrCodeOption" id="qrDefault" value="Default (A6 with 20 copies)"><label for="qrDefault">Default (A6 x20)</label></div>
                                    <div class="radio-card"><input type="radio" name="qrCodeOption" id="qrOther" value="Other"><label for="qrOther">Other</label></div>
                                </div>
                                <div class="mb-3 mt-2" id="qrOtherGroup" style="display:none;">
                                    <input type="text" class="form-control" id="qrOtherText" name="qrOtherText" placeholder="Specify QR code requirements...">
                                </div>
                            </div>

                            <!-- Restaurant Printer -->
                            <div class="sub-section restaurant-fields">
                                <h6 class="sub-section-title"><i class="bi bi-printer"></i> (Restaurant) Supported Printer</h6>
                                <div class="form-text mb-2">Attach image of printer model that our system supports.</div>
                                <input type="text" class="form-control" id="printerModel" name="printerModel" placeholder="e.g. Epson TM-T82III or image link">
                            </div>

                            <!-- Massage Gift Voucher -->
                            <div class="sub-section massage-fields">
                                <h6 class="sub-section-title"><i class="bi bi-gift"></i> (Massage) Gift Voucher</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="radio-card-group">
                                            <div class="radio-card"><input type="radio" name="giftVoucher" id="giftVoucherYes" value="Yes"><label for="giftVoucherYes"><i class="bi bi-check-lg"></i> Yes</label></div>
                                            <div class="radio-card"><input type="radio" name="giftVoucher" id="giftVoucherNo" value="No"><label for="giftVoucherNo"><i class="bi bi-x-lg"></i> No</label></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3" id="voucherValueGroup" style="display:none;">
                                        <label for="voucherValue" class="form-label">Voucher Value</label>
                                        <input type="text" class="form-control" id="voucherValue" name="voucherValue" placeholder="e.g. $50, $100, $150">
                                    </div>
                                </div>
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

                        <input type="hidden" name="formVersion" value="2.0.0">
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


<!-- ===== Modal: Package Details ===== -->
<div class="modal fade" id="modalPackageDetails" tabindex="-1" aria-labelledby="modalPackageDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPackageDetailsLabel"><i class="bi bi-box-seam me-2 text-primary"></i>Package Details</h5>
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
                <h5 class="modal-title" id="modalImportantInfoLabel"><i class="bi bi-info-circle me-2 text-success"></i>Important Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="notice-card notice-cancel">
                    <div class="notice-icon"><i class="bi bi-calendar-x"></i></div>
                    <div>
                        <strong>30-Day Cancellation Notice</strong>
                        <p class="mb-0">All cancellations require a <strong>30-day advance notice</strong>. Please notify us at least 30 days before your next billing date if you wish to cancel your service.</p>
                    </div>
                </div>
                <div class="notice-card notice-contract">
                    <div class="notice-icon"><i class="bi bi-file-earmark-lock"></i></div>
                    <div>
                        <strong>Contract &amp; Service Fee</strong>
                        <p class="mb-0">If the contract has expired, the service fee will remain as per the contract selected until canceled or changed to another contract.</p>
                    </div>
                </div>
                <div class="notice-card notice-stripe">
                    <div class="notice-icon"><i class="bi bi-shield-check"></i></div>
                    <div>
                        <strong>Payment Processing (Stripe)</strong>
                        <p class="mb-0">Stripe is our trusted payment partner. <strong>Local For You does not hold your revenue at any point.</strong> All payments go directly through Stripe to your connected account.</p>
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
                <div class="info-card info-stripe mb-3">
                    <h6><i class="bi bi-credit-card-2-front"></i> Stripe Information</h6>
                    <ul class="mb-0">
                        <li>Stripe is our payment processing partner.</li>
                        <li>Local For You does <strong>not</strong> hold customer revenue at any point.</li>
                        <li>Stripe fee: <strong>2.9% + 30&cent;</strong> per transaction (standard US rate).</li>
                        <li>Customers connect their own Stripe account for online payments.</li>
                    </ul>
                </div>
                <div class="info-card info-delivery mb-3">
                    <h6><i class="bi bi-truck"></i> In-House Delivery &amp; AI Marketing</h6>
                    <ul class="mb-0">
                        <li><strong>In-House Delivery:</strong> Available for Bundle restaurant customers. Delivery fee is customizable per area/zone.</li>
                        <li><strong>AI Marketing:</strong> Available for restaurant packages. Activation should be done by the customer themselves.</li>
                    </ul>
                </div>
                <div class="info-card info-amelia mb-3">
                    <h6><i class="bi bi-calendar-check"></i> Amelia Booking System</h6>
                    <p class="mb-0">For Amelia booking system, the customer <strong>must use WordPress</strong>. If they want to keep their own website, make sure it is WordPress-based.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/img/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../controllers/upgradeForm.js?v=2.0.0"></script>
</body>
</html>
