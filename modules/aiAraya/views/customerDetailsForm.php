<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;

$stripeID = $_GET['stripeID'] ?? '$_get=stripeID';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/customerDetailsForm.css?v=2.0.0" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
    <title>Customer Details - Form</title>
</head>
<body>

<div class="container">
    <main>
        <section style="min-height: 50vh;">
            <div class="form-div">

                <!-- ===== Header ===== -->
                <div class="form-header">
                    <img src="../assets/img/L4U-Site-Icon.png" alt="Logo Image" class="logo-img">
                    <h3 class="form-title text-uppercase">AI Araya – Customer Details Form</h3>
                    <p class="form-subtitle">
                        Please complete this form so we can configure your AI receptionist correctly. You may skip any fields you prefer to handle during the onboarding call.
                    </p>
                    <div class="howto-links">
                        <a href="howToGetCalendarId.php" target="_blank">
                            <i class="bi bi-calendar3"></i> Setting up Google Calendar
                        </a>
                    </div>
                </div>
                

                <!-- ===== Form Body ===== -->
                <div class="form-body">
                    <form id="customerDetailsForm" name="customerDetailsForm" method="post" enctype="multipart/form-data">

                        <!-- ========== SECTION 1: Business Information ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-building"></i> Business Information</h5>

                            <div class="mb-3">
                                <label for="businessName" class="form-label">Business Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="businessName" name="businessName" placeholder="e.g. Serenity Spa & Wellness" required>
                                <div class="invalid-feedback">Please enter your business name.</div>
                            </div>

                            <div class="mb-3">
                                <label for="businessHours" class="form-label">Business Hours <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="businessHours" name="businessHours" rows="3" placeholder="e.g. Mon-Fri 9:00 AM - 7:00 PM, Sat 10:00 AM - 5:00 PM, Sun Closed" required></textarea>
                                <div class="invalid-feedback">Please enter your business hours.</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="shopAddress" class="form-label">Shop Address</label>
                                    <textarea class="form-control" id="shopAddress" name="shopAddress" rows="2" placeholder="e.g. 123 Main Street, Suite 4, Los Angeles, CA 90001"></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="timezoneId" class="form-label">Timezone ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="timezoneId" name="timezoneId" placeholder="e.g. America/New_York" required>
                                    <div class="invalid-feedback">Please enter your timezone ID.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="currentBookingSystem" class="form-label">Current Booking System <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="currentBookingSystem" name="currentBookingSystem" placeholder="e.g. Google Calendar, Fresha" required>
                                <div class="invalid-feedback">Please enter your current booking system.</div>
                            </div>

                            <div class="row" id="bookingCredentials" style="display: none;">
                                <div class="col-12 mb-2">
                                    <small class="text-muted"><i class="bi bi-lock"></i> Booking system login credentials</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="bookingUser" class="form-label">Username / Email</label>
                                    <input type="text" class="form-control" id="bookingUser" name="bookingUser" placeholder="e.g. admin@yourbusiness.com">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="bookingPassword" class="form-label">Password</label>
                                    <input type="text" class="form-control" id="bookingPassword" name="bookingPassword" placeholder="e.g. ••••••••">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="businessWebsite" class="form-label">Business Website</label>
                                <input type="url" class="form-control" id="businessWebsite" name="businessWebsite" placeholder="e.g. https://www.yourbusiness.com">
                            </div>
                        </div>

                        <!-- ========== SECTION 2: Phone & Contact ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-telephone"></i> Phone & Contact</h5>

                            <div class="mb-3">
                                <label class="form-label">Phone Number Decision <span class="text-danger">*</span></label>
                                <div class="radio-card-group">
                                    <div class="radio-card">
                                        <input type="radio" name="phoneNumberDecision" id="phoneDecisionPromote" value="Promote AI Phone Number" required>
                                        <label for="phoneDecisionPromote"><i class="bi bi-megaphone"></i> Promote AI Phone Number</label>
                                    </div>
                                    <div class="radio-card">
                                        <input type="radio" name="phoneNumberDecision" id="phoneDecisionTransfer" value="Transfer call to Shop's number">
                                        <label for="phoneDecisionTransfer"><i class="bi bi-telephone-forward"></i> Transfer to Shop's Number</label>
                                    </div>
                                </div>
                                <div class="invalid-feedback">Please select a phone number decision.</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="mainPhoneNumber" class="form-label">Main Phone Number <span class="text-danger">*</span>
                                        <div class="form-text text-muted">Shop's primary phone number</div>
                                    </label>
                                    <input type="tel" class="form-control" id="mainPhoneNumber" name="mainPhoneNumber" placeholder="e.g. +1 (555) 000-1111" required>
                                    <div class="invalid-feedback">Please enter your main phone number.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="backupPhoneNumber" class="form-label">Backup Phone Number <span class="text-danger">*</span><br>
                                        <div class="form-text text-muted">AI transfers to human at this number</div>
                                    </label>
                                    <input type="tel" class="form-control" id="backupPhoneNumber" name="backupPhoneNumber" placeholder="e.g. +1 (555) 123-4567" required>
                                    <div class="invalid-feedback">Please enter a backup phone number.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="phoneCarrier" class="form-label">Phone Carrier <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phoneCarrier" name="phoneCarrier" placeholder="e.g. AT&T, Verizon, T-Mobile" required>
                                <div class="invalid-feedback">Please enter your phone carrier.</div>
                            </div>

                            <div class="mb-3">
                                <label for="shopEmail" class="form-label">Shop Email Address <small class="text-muted">(for booking confirmations)</small></label>
                                <input type="email" class="form-control" id="shopEmail" name="shopEmail" placeholder="e.g. bookings@yourbusiness.com">
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                        </div>

                        <!-- ========== SECTION 3: Services ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-list-check"></i> Services</h5>

                            <div class="mb-3">
                                <label for="servicesOffered" class="form-label">Services Offered <small class="text-muted">(Include name, duration, and price)</small> <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="servicesOffered" name="servicesOffered" rows="5" placeholder="e.g.&#10;Swedish Massage - 60 min - $80&#10;Deep Tissue Massage - 90 min - $120&#10;Hot Stone Therapy - 60 min - $100&#10;Aromatherapy Massage - 60 min - $90" required></textarea>
                                <div class="invalid-feedback">Please list your services offered.</div>
                            </div>

                            <div class="mb-3">
                                <label for="bufferMinutes" class="form-label">Buffer Minutes <small class="text-muted">(Optional)</small></label>
                                <input type="number" class="form-control" id="bufferMinutes" name="bufferMinutes" min="0" placeholder="e.g. 15" style="max-width: 200px;">
                                <div class="form-text">Minutes between appointments for preparation.</div>
                            </div>

                            <div class="radio-grid">
                                <div class="radio-field">
                                    <label class="form-label">Couple Massage <span class="text-danger">*</span></label>
                                    <div class="radio-card-group">
                                        <div class="radio-card">
                                            <input type="radio" name="coupleMassage" id="coupleMassageYes" value="Couple Massage Available" required>
                                            <label for="coupleMassageYes"><i class="bi bi-check-lg"></i> Available</label>
                                        </div>
                                        <div class="radio-card">
                                            <input type="radio" name="coupleMassage" id="coupleMassageNo" value="Couple Massage Not Available">
                                            <label for="coupleMassageNo"><i class="bi bi-x-lg"></i> Not Available</label>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">Please select an option.</div>
                                </div>

                                <div class="radio-field">
                                    <label class="form-label">Happy Massage <span class="text-danger">*</span></label>
                                    <div class="radio-card-group">
                                        <div class="radio-card">
                                            <input type="radio" name="happyMassage" id="happyMassageYes" value="Happy Massage Available" required>
                                            <label for="happyMassageYes"><i class="bi bi-check-lg"></i> Available</label>
                                        </div>
                                        <div class="radio-card">
                                            <input type="radio" name="happyMassage" id="happyMassageNo" value="Happy Massage Not Available">
                                            <label for="happyMassageNo"><i class="bi bi-x-lg"></i> Not Available</label>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">Please select an option.</div>
                                </div>
                            </div>
                        </div>

                        <!-- ========== SECTION 4: Therapists ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-people"></i> Therapists</h5>

                            <div class="radio-grid mb-3">
                                <div class="radio-field">
                                    <label class="form-label">Male Therapists <span class="text-danger">*</span></label>
                                    <div class="radio-card-group">
                                        <div class="radio-card">
                                            <input type="radio" name="maleTherapists" id="maleTherapistYes" value="Male Therapist Available" required>
                                            <label for="maleTherapistYes"><i class="bi bi-check-lg"></i> Available</label>
                                        </div>
                                        <div class="radio-card">
                                            <input type="radio" name="maleTherapists" id="maleTherapistNo" value="Male Therapist Not Available">
                                            <label for="maleTherapistNo"><i class="bi bi-x-lg"></i> Not Available</label>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">Please select an option.</div>
                                </div>

                                <div class="radio-field">
                                    <label class="form-label">Female Therapists <span class="text-danger">*</span></label>
                                    <div class="radio-card-group">
                                        <div class="radio-card">
                                            <input type="radio" name="femaleTherapists" id="femaleTherapistYes" value="Female Therapists Available" required>
                                            <label for="femaleTherapistYes"><i class="bi bi-check-lg"></i> Available</label>
                                        </div>
                                        <div class="radio-card">
                                            <input type="radio" name="femaleTherapists" id="femaleTherapistNo" value="Female Therapists Not Available">
                                            <label for="femaleTherapistNo"><i class="bi bi-x-lg"></i> Not Available</label>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">Please select an option.</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="numberOfTherapists" class="form-label">Number of Therapists <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="numberOfTherapists" name="numberOfTherapists" min="1" placeholder="e.g. 5" required>
                                    <div class="invalid-feedback">Please enter the number of therapists.</div>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label for="therapistNames" class="form-label">Therapist Names <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="therapistNames" name="therapistNames" rows="2" placeholder="e.g. Anna, Brian, Clara, David" required></textarea>
                                    <div class="invalid-feedback">Please enter therapist names.</div>
                                </div>
                            </div>
                        </div>

                        <!-- ========== SECTION 5: Facilities ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-shop"></i> Facilities</h5>

                            <div class="radio-grid">
                                <div class="radio-field">
                                    <label class="form-label">Wheelchair <span class="text-danger">*</span></label>
                                    <div class="radio-card-group">
                                        <div class="radio-card">
                                            <input type="radio" name="wheelchair" id="wheelchairYes" value="Wheelchair Accessible" required>
                                            <label for="wheelchairYes"><i class="bi bi-check-lg"></i> Accessible</label>
                                        </div>
                                        <div class="radio-card">
                                            <input type="radio" name="wheelchair" id="wheelchairNo" value="Wheelchair Not Accessible">
                                            <label for="wheelchairNo"><i class="bi bi-x-lg"></i> Not Accessible</label>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">Please select an option.</div>
                                </div>

                                <div class="radio-field">
                                    <label class="form-label">Parking <span class="text-danger">*</span></label>
                                    <div class="radio-card-group">
                                        <div class="radio-card">
                                            <input type="radio" name="parking" id="parkingYes" value="Parking Available" required>
                                            <label for="parkingYes"><i class="bi bi-check-lg"></i> Available</label>
                                        </div>
                                        <div class="radio-card">
                                            <input type="radio" name="parking" id="parkingNo" value="Parking Not Available">
                                            <label for="parkingNo"><i class="bi bi-x-lg"></i> Not Available</label>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">Please select an option.</div>
                                </div>

                                <div class="radio-field">
                                    <label class="form-label">Restroom <span class="text-danger">*</span></label>
                                    <div class="radio-card-group">
                                        <div class="radio-card">
                                            <input type="radio" name="restroom" id="restroomYes" value="Restroom Available" required>
                                            <label for="restroomYes"><i class="bi bi-check-lg"></i> Available</label>
                                        </div>
                                        <div class="radio-card">
                                            <input type="radio" name="restroom" id="restroomNo" value="Restroom Not Available">
                                            <label for="restroomNo"><i class="bi bi-x-lg"></i> Not Available</label>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">Please select an option.</div>
                                </div>
                            </div>
                        </div>

                        <!-- ========== SECTION 6: Promotions & AI Preferences ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-stars"></i> Promotions & AI Preferences</h5>

                            <div class="mb-3">
                                <label for="promotions" class="form-label">Ongoing Promotions & Gift Vouchers <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="promotions" name="promotions" rows="3" placeholder="e.g. 20% off first visit, Gift cards available from $50, Buy 5 sessions get 1 free" required></textarea>
                                <div class="invalid-feedback">Please enter promotions or type "None".</div>
                                <div class="form-text">AI will mention these before call end & in confirmation email sent to customers.</div>
                            </div>

                            <div class="radio-grid mb-3">
                                <div class="radio-field">
                                    <label class="form-label">Voice Type <span class="text-danger">*</span></label>
                                    <div class="radio-card-group">
                                        <div class="radio-card">
                                            <input type="radio" name="voiceType" id="voiceFemale" value="Female" required>
                                            <label for="voiceFemale"><i class="bi bi-gender-female"></i> Female</label>
                                        </div>
                                        <div class="radio-card">
                                            <input type="radio" name="voiceType" id="voiceMale" value="Male">
                                            <label for="voiceMale"><i class="bi bi-gender-male"></i> Male</label>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">Please select a voice type.</div>
                                </div>
                            </div>

                            <div class="mb-3" id="googleCalendarIdGroup">
                                <label for="googleCalendarId" class="form-label">Google Calendar ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="googleCalendarId" name="googleCalendarId" placeholder="e.g. abc123@group.calendar.google.com" required>
                                <div class="invalid-feedback">Please enter your Google Calendar ID.</div>
                                <div class="form-text d-flex gap-3">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#calendarHowToModal"><i class="bi bi-play-circle"></i> Quick Guide</a>
                                    <a href="howToGetCalendarId.php" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Full Guide</a>
                                </div>
                            </div>
                        </div>

                        <!-- ========== SECTION 8: Booking Policy ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-stars"></i> Booking Policy</h5>

                            <div class="radio-grid mb-3">
                                <div class="radio-field">
                                    <label class="form-label">Set Booking Policies? <span class="text-danger">*</span></label>
                                    <div class="radio-card-group">
                                        <div class="radio-card">
                                            <input type="radio" name="setPolicies" id="yesPolicy" value="yesPolicy" required>
                                            <label for="yesPolicy"><i class="bi bi-check-lg"></i> Yes</label>
                                        </div>
                                        <div class="radio-card">
                                            <input type="radio" name="setPolicies" id="noPolicy" value="noPolicy">
                                            <label for="noPolicy"><i class="bi bi-x-lg"></i> No, skip</label>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">Please select a policy.</div>
                                </div>
                            </div>

                            <div class="mb-3 radio-field" style="display: none; background: #f8f9fb61; border: 1.5px solid #eef0f4; border-radius: 12px; padding: 16px 18px;" id="bookingPolicies">
                                <div class="row" id="lateAndNo-show">
                                    <h6 class="fw-bold">Late & No-Show</h6>
                                    <div class="col-md-6 mb-3">
                                        <label for="lateArrivalFee" class="form-label">Late Arrival Fee <span class="text-danger">*</span><br></label>
                                        <input type="text" class="form-control" id="lateArrivalFee" name="lateArrivalFee" placeholder="e.g. $20 after 15 min late" required>
                                        <div class="invalid-feedback">Please enter late arrival fee.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="noshowFee" class="form-label">No-Show Fee <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="noshowFee" name="noshowFee" placeholder="e.g. 100% of service" required>
                                        <div class="invalid-feedback">Please enter no-show fee.</div>
                                    </div>
                                </div>

                                <div class="row" id="cancellation">
                                    <h6 class="fw-bold">Cancellation</h6>
                                    <div class="col-md-6 mb-3">
                                        <label for="freeCancellationWindow" class="form-label">Free Cancellation Window <span class="text-danger">*</span><br></label>
                                        <input type="text" class="form-control" id="freeCancellationWindow" name="freeCancellationWindow" placeholder="e.g. 24 Hours before" required>
                                        <div class="invalid-feedback">Please enter free cancellation window.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="lateCancellationFee" class="form-label">Late Cancellation Fee <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="lateCancellationFee" name="lateCancellationFee" placeholder="e.g. 50% of service" required>
                                        <div class="invalid-feedback">Please enter late cancellation fee.</div>
                                    </div>
                                </div>

                                <div class="row" id="requireDeposit">
                                    <div class="radio-grid mb-3">
                                        <div class="radio-field">
                                            <label class="form-label">Require Deposit? <span class="text-danger">*</span></label>
                                            <div class="radio-card-group">
                                                <div class="radio-card">
                                                    <input type="radio" name="setDeposit" id="yesDeposit" value="yesDeposit" required>
                                                    <label for="yesDeposit"><i class="bi bi-check-lg"></i> Yes</label>
                                                </div>
                                                <div class="radio-card">
                                                    <input type="radio" name="setDeposit" id="noDeposit" value="noDeposit">
                                                    <label for="noDeposit"><i class="bi bi-x-lg"></i> No</label>
                                                </div>
                                            </div>
                                            <div class="invalid-feedback">Please select a require doposit.</div>
                                        </div>
                                    </div>

                                    <div class="row" id="deposit" style="display: none;">
                                        <div class="col-md-6 mb-3">
                                            <label for="depositAmount" class="form-label">Deposit Amount <span class="text-danger">*</span><br></label>
                                            <input type="text" class="form-control" id="depositAmount" name="depositAmount" placeholder="e.g. $30 or 30%" required>
                                            <div class="invalid-feedback">Please enter deposit amount.</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="depositPaymentLink" class="form-label">Deposit Payment Link <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="depositPaymentLink" name="depositPaymentLink" placeholder="e.g. https://buy.stripe.com/..." required>
                                            <div class="invalid-feedback">Please enter deposit payment link.</div>
                                        </div>
                                    </div>
                                </div>
                                

                                <div class="mb-3" id="termAndConditionsLink">
                                    <label for="termAndConditionsLink" class="form-label">Term and Conditions Link (if you have one) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="termAndConditionsLink" name="termAndConditionsLink" placeholder="e.g. https://yourbusiness.com/terms">
                                </div>
                            </div>
                        </div>

                        <!-- ========== SECTION 7: Additional ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-chat-dots"></i> Additional Comments</h5>

                            <div class="mb-3">
                                <textarea class="form-control" id="additionalComments" name="additionalComments" rows="4" placeholder="Any additional information you'd like us to know..."></textarea>
                            </div>
                        </div>

                        <!-- ===== Submit ===== -->
                        <div class="submit-area">
                            <button type="submit" id="cmdSubmit" class="btn btn-submit"><i class="bi bi-send"></i> Submit Form</button>
                            <div id="result" class="mt-3"></div>
                        </div>
                        
                        <input type="hidden" name="formVersion" value="1.0.0">
                        <input type="hidden" name="stripeID" id="stripeID" value="<?php echo htmlspecialchars($stripeID ?? ''); ?>">
                        <input type="hidden" id="statusUser" name="statusUser" value="completed">
                    </form>
                </div>
            </div>
        </section>
    </main>
</div><!-- container-->

<?php include '../layout/footer.php'; ?>

<style>
    #calendarHowToModal .modal-body { font-family: 'Montserrat', sans-serif; font-weight: 400; font-size: 0.85rem; color: #444; line-height: 1.75; }
    #calendarHowToModal .step-card { background: #f8f9fb; border-radius: 12px; padding: 18px 20px; margin-bottom: 14px; }
    #calendarHowToModal .step-num { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; background: #0d6efd; color: #fff; border-radius: 50%; font-weight: 700; font-size: 0.75rem; flex-shrink: 0; }
    #calendarHowToModal .step-label { font-weight: 600; font-size: 0.88rem; color: #1a1a2e; }
    #calendarHowToModal .step-desc { margin-top: 8px; padding-left: 38px; }
    #calendarHowToModal .email-list { background: #fff; border: 1px solid #e0e3e8; border-radius: 8px; padding: 10px 14px; font-family: monospace; font-size: 0.72rem; color: #0d6efd; line-height: 2; word-break: break-all; margin-top: 8px; }
    #calendarHowToModal .tip { background: #fff8e6; border-radius: 8px; padding: 10px 14px; font-size: 0.8rem; color: #665500; margin-top: 10px; }
    #calendarHowToModal .done-box { background: #eafaf1; border-radius: 8px; padding: 10px 14px; font-size: 0.8rem; color: #1a5c2e; margin-top: 10px; }
    #calendarHowToModal .badge-perm { background: #eef3ff; color: #0d6efd; font-size: 0.74rem; font-weight: 600; padding: 3px 10px; border-radius: 50px; white-space: nowrap; }
    #calendarHowToModal ul { padding-left: 18px; margin: 6px 0 0; }
    #calendarHowToModal ul li { margin-bottom: 4px; }
    #calendarHowToModal .step-video h6 { font-weight: 700; color: #0d6efd; }
</style>
<div class="modal fade" id="calendarHowToModal" tabindex="-1" aria-labelledby="calendarHowToModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content" style="border: none; border-radius: 16px; overflow: hidden;">
            <div class="modal-header" style="background: #0d6efd; border: none; padding: 20px 24px;">
                <h6 class="modal-title text-white" id="calendarHowToModalLabel" style="font-weight: 700; font-size: 0.95rem;"><i class="bi bi-calendar3"></i> Google Calendar Setup</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 24px;">

                <!-- Video -->
                 <div class="step-card step-video">
                    <div class="d-flex align-items-center gap-2">
                        <span class="step-num"><i class="bi bi-play-circle"></i></span>
                        <span class="step-label text-bold"> Video Guide</span>
                    </div>
                    <div class="step-desc">
                        <video controls style="width: 100%; max-width: 640px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.10);">
                            <source src="../assets/img/google-calendar-step/set-up-Google-Calendar-guide.mp4" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </div>

                <!-- Step 1 -->
                <div class="step-card">
                    <div class="d-flex align-items-center gap-2">
                        <span class="step-num">1</span>
                        <span class="step-label">Open Calendar Settings</span>
                    </div>
                    <div class="step-desc">
                        ไปที่ <a href="https://calendar.google.com" target="_blank">calendar.google.com</a> → คลิกที่ไอคอนเฟือง ⚙️ → <b>Settings</b>
                        <div style="margin-top: 10px;"><img src="../assets/img/google-calendar-step/step1.png" alt="Step 1" style="width: 100%; border-radius: 8px; border: 1px solid #e0e3e8;"></div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="step-card">
                    <div class="d-flex align-items-center gap-2">
                        <span class="step-num">2</span>
                        <span class="step-label">Check Time Zone</span>
                    </div>
                    <div class="step-desc">
                        ตรวจสอบให้แน่ใจว่า Time zone ตรงกับโลเคชันร้านของคุณ
                        <div style="margin-top: 10px;"><img src="../assets/img/google-calendar-step/step2.png" alt="Step 2" style="width: 100%; border-radius: 8px; border: 1px solid #e0e3e8;"></div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="step-card">
                    <div class="d-flex align-items-center gap-2">
                        <span class="step-num">3</span>
                        <span class="step-label">Share Calendar</span>
                    </div>
                    <div class="step-desc">
                        แถบด้านข้างซ้าย เลื่อนมาดูตรง Settings for my calendars → ชื่อปฏิทินของคุณ → <b>Share with</b><br>
                        กด <b>+ Add people and groups</b><br>
                        เพิ่มที่อยู่อีเมลต่อไปนี้ โดยให้สิทธิ์ <b>Make changes to events</b> สำหรับแต่ละรายการ:
                        <div class="email-list">
                            calendar-api@lfy-ai-gcal-463906-b1.iam.gserviceaccount.com<br>
                            calendar-bot@gcalbookingapi.iam.gserviceaccount.com<br>
                            ailogins@localforyou.com
                        </div>
                        <div style="margin-top: 10px;"><img src="../assets/img/google-calendar-step/step3-1.png" alt="Step 3-1" style="width: 100%; border-radius: 8px; border: 1px solid #e0e3e8;"></div>
                        <div style="margin-top: 8px;"><img src="../assets/img/google-calendar-step/step3-2.png" alt="Step 3-2" style="width: 100%; border-radius: 8px; border: 1px solid #e0e3e8;"></div>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="step-card">
                    <div class="d-flex align-items-center gap-2">
                        <span class="step-num">4</span>
                        <span class="step-label">Copy Calendar ID</span>
                    </div>
                    <div class="step-desc">
                        ให้เลื่อนลงไปที่ Integrate calendar จะเจอ <b>Calendar ID</b><br>
                        คัดลอก Calendar ID ที่แสดง<br>
                        <span style="color: #888; font-size: 0.78rem;">Example: abc123xyz@group.calendar.google.com</span>
                        <div style="margin-top: 10px;"><img src="../assets/img/google-calendar-step/step4-1.png" alt="Step 4-1" style="width: 100%; border-radius: 8px; border: 1px solid #e0e3e8;"></div>
                        กลับไปที่ฟอร์ม AI Araya วาง Calendar ID ที่คัดลอกลงในช่อง <b>Google Calendar ID</b>
                        <div style="margin-top: 8px;"><img src="../assets/img/google-calendar-step/step4-2.png" alt="Step 4-2" style="width: 100%; border-radius: 8px; border: 1px solid #e0e3e8;"></div>
                    </div>
                </div>

                <div class="done-box" style="margin-top: 18px;">
                    <i class="bi bi-check-circle-fill" style="color: #28a745;"></i> That's it! Your calendar is ready for AI Araya.
                </div>

            </div>
            <div class="modal-footer" style="border-top: 1px solid #eef0f4; padding: 12px 24px;">
                <a href="howToGetCalendarId.php" target="_blank" class="btn btn-outline-primary btn-sm" style="border-radius: 50px; font-size: 0.8rem;"><i class="bi bi-box-arrow-up-right"></i> Full Guide</a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="border-radius: 50px; font-size: 0.8rem;">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../controllers/customerDetailsForm.js?v=1.0.0"></script>
<script>
$(function() {
    // Toggle booking credentials when currentBookingSystem has value
    const $bookingInput = $('#currentBookingSystem');
    const $credentials = $('#bookingCredentials');
    $bookingInput.on('input', function() {
        if ($(this).val().trim()) {
            $credentials.slideDown(200);
        } else {
            $credentials.slideUp(200);
            $('#bookingUser, #bookingPassword').val('');
        }
    });
    // Check on page load (e.g. browser autofill)
    if ($bookingInput.val().trim()) $credentials.show();

    // Toggle Booking Policies when "Yes" is selected
    $('input[name="setPolicies"]').on('change', function() {
        if ($('#yesPolicy').is(':checked')) {
            $('#bookingPolicies').slideDown(200);
            $('#bookingPolicies').find('input[type="text"], input[type="radio"]').each(function() {
                if ($(this).closest('#deposit').length === 0 || $('#yesDeposit').is(':checked')) {
                    $(this).prop('required', true);
                }
            });
        } else {
            $('#bookingPolicies').slideUp(200);
            $('#bookingPolicies').find('input').prop('required', false);
        }
    });

    // Toggle Deposit fields when "Yes" is selected
    $('input[name="setDeposit"]').on('change', function() {
        if ($('#yesDeposit').is(':checked')) {
            $('#deposit').slideDown(200);
            $('#deposit').find('input').prop('required', true);
        } else {
            $('#deposit').slideUp(200);
            $('#deposit').find('input').val('').prop('required', false);
        }
    });

    const params = new URLSearchParams(window.location.search);
    if (params.get('test') === 'true') {
        // Text / Textarea / Number fields
        $('#businessName').val('Mark Test & Spa');
        $('#businessHours').val('Mon-Fri 9:00 AM - 7:00 PM\nSat 10:00 AM - 5:00 PM\nSun Closed');
        $('#currentBookingSystem').val('Google Calendar');
        $('#timezoneId').val('America/New_York');
        $('#shopAddress').val('123 Main Street, Suite 4, Los Angeles, CA 90001');
        $('#businessWebsite').val('https://www.marktesttest.com');
        $('#mainPhoneNumber').val('+1 (555) 000-1111');
        $('#backupPhoneNumber').val('+1 (555) 123-4567');
        $('#phoneCarrier').val('AT&T');
        $('#shopEmail').val('bookings@marktesttest.com');
        $('#servicesOffered').val('Swedish Massage - 60 min - $80\nDeep Tissue Massage - 90 min - $120\nHot Stone Therapy - 60 min - $100\nAromatherapy Massage - 60 min - $90');
        $('#bufferMinutes').val('15');
        $('#numberOfTherapists').val('5');
        $('#therapistNames').val('Anna, Brian, Clara, David, Emily');
        $('#promotions').val('20% off first visit, Gift cards available from $50, Buy 5 sessions get 1 free');
        $('#additionalComments').val('Test submission - please ignore.');

        // Radio buttons
        $('#phoneDecisionPromote').prop('checked', true);
        $('#coupleMassageYes').prop('checked', true);
        $('#happyMassageNo').prop('checked', true);
        $('#maleTherapistYes').prop('checked', true);
        $('#femaleTherapistYes').prop('checked', true);
        $('#wheelchairYes').prop('checked', true);
        $('#parkingYes').prop('checked', true);
        $('#restroomYes').prop('checked', true);
        $('#voiceFemale').prop('checked', true);
        $('#stripeID').val(params.get('stripeID') || '');

        console.log('🧪 Test mode: form auto-filled with sample data.');
    }
});
</script>
</body>
</html>