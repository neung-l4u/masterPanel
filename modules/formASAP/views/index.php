
<?php
date_default_timezone_set('Asia/Bangkok');

$id = !empty($_GET['id']) ? strtolower(trim($_GET['id'])): '';
$testMode = ($id == "test") ? 1 : 0;
$leadSource = "Unsubscribe Form";
$formVersion = "1.0.0";
$emailVersion = "1.0";
$timestamps = date("H:i D ,d M Y") . " (BKK)";

// ===== Booking slot config (ต้องตรงกับ checkAvailability.php) =====
$maxSlots       = 2;
$bookingMin     = "2026-06-01";
$bookingMax     = "2026-06-30";
$bookingHourMin = "06:00";
$bookingHourMax = "18:00";

// ถ้าวันนี้เลย bookingMin แล้ว ให้ใช้ today เป็น min (ไม่ให้เลือกวันในอดีต)
$today = date("Y-m-d");
if ($today > $bookingMin) $bookingMin = $today;
$bookingClosed = ($today > $bookingMax); // ถ้าเลย May ไปแล้ว — ปิดการจอง

?>
<!doctype html>
<html lang="en">
<head>
    <title>L4U - Upgrade to the New System</title>
    <?php include "form_header.php"; ?>
    <!-- Modern UI: Bootstrap Icons + Montserrat + shared form styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../aiAraya/assets/css/customerDetailsForm.css?v=2.0.0">
    <style>
        /* Checkbox card (multi-select variant of .radio-card) */
        .check-card-group { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-top: 4px; }
        .check-card input[type="checkbox"] { display: none; }
        .check-card label {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background: #f8f9fb; border: 1.5px solid #e2e5eb; border-radius: 10px;
            padding: 12px 14px; font-size: 0.85rem; font-weight: 500; color: #4a4f5c;
            cursor: pointer; transition: all .15s ease; user-select: none; width: 100%;
        }
        .check-card label:hover { border-color: #0d6efd; color: #0d6efd; background: rgba(13,110,253,0.04); }
        .check-card input[type="checkbox"]:checked + label { border-color: #0d6efd; color: #0d6efd; font-weight: 600; background: rgba(13,110,253,0.06); }
        .check-card label i { font-size: 0.95rem; }
        @media (max-width: 576px) { .check-card-group { grid-template-columns: 1fr; } }

        /* Slot status pill */
        #slotStatus { font-size: 0.82rem; margin-top: 8px; min-height: 1.1rem; }

        /* Time selects row */
        .time-row { display: flex; align-items: center; gap: 10px; }
        .time-row .form-select { max-width: 130px; }

        /* Status action area */
        .submit-area .status-msg { display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; margin-left: 12px; }
    </style>
    <!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-NRXFMRJB');</script>
<!-- End Google Tag Manager -->
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NRXFMRJB"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<div class="container">
    <main>
        <section style="min-height: 60vh;">
            <?php if ($bookingClosed): ?>
                <div class="form-div" style="padding: 40px;">
                    <div class="alert alert-warning mb-0">
                        <h4 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Booking Closed</h4>
                        <p class="mb-0">การจองสำหรับเดือนมิถุนายน 2026 ได้ปิดลงแล้ว กรุณาติดต่อทีมงานสำหรับรอบถัดไป</p>
                    </div>
                </div>
            <?php else: ?>
            <div class="form-div">

                <!-- ===== Header ===== -->
                <div class="form-header">
                    <img src="../assets/img/newL4U-logo-100x100.png" alt="L4U Logo" class="logo-img">
                    <h3 class="form-title text-uppercase">Upgrade to the New L4U System</h3>
                    <p class="form-subtitle">
                        Fill in your details and pick a convenient time — our team will reach out to walk you through the upgrade.
                    </p>
                </div>

                <!-- ===== Form Body ===== -->
                <div class="form-body">
                    <form id="myForm" action="#" method="POST">

                        <!-- ========== SECTION 1: Your Information ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-person"></i> Your Information</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fullName" class="form-label">Name-Surname <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="fullName" name="fullName" autocomplete="off" placeholder="e.g. John Doe" required>
                                    <small class="text-danger warningText" id="smallFullName">Please provide your name.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="restaurantName" class="form-label">Restaurant Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="restaurantName" name="restaurantName" autocomplete="off" placeholder="e.g. Mali Thai Bistro" required>
                                    <small class="text-danger warningText" id="smallRestaurantName">Please provide the restaurant name.</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="formCountry" class="form-label">Country <span class="text-danger">*</span></label>
                                    <select id="formCountry" class="form-select" name="country" required>
                                        <option selected value="" disabled>Please select country</option>
                                        <option value="AU">Australia</option>
                                        <option value="NZ" disabled>New Zealand (coming soon)</option>
                                        <option value="US" disabled>United States (coming soon)</option>
                                        <option value="UK" disabled>United Kingdom (coming soon)</option>
                                        <option value="CA" disabled>Canada (coming soon)</option>
                                        <option value="TH" disabled>Thailand (coming soon)</option>
                                    </select>
                                    <small class="text-danger warningText" id="smallCountry">Please select your country.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control mainEmail text-lowercase" id="email" name="email" maxlength="80" autocomplete="off" placeholder="e.g. mail@localforyou.com" required>
                                    <div class="form-text">We'll send the booking confirmation to this email.</div>
                                    <small class="text-danger warningText" id="smallEmail">Please provide a valid email address.</small>
                                </div>
                            </div>
                        </div>

                        <!-- ========== SECTION 2: Products Needed ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-box-seam"></i> Products Needed</h5>

                            <div class="mb-3">
                                <label class="form-label">Select one or more products <span class="text-danger">*</span></label>
                                <div class="check-card-group">
                                    <div class="check-card">
                                        <input class="productNeeded" type="checkbox" value="true" id="productOnlineOrdering" name="onlineOrderingSystem">
                                        <label for="productOnlineOrdering"><i class="bi bi-cart-check"></i> Online Ordering System</label>
                                    </div>
                                    <div class="check-card">
                                        <input class="productNeeded" type="checkbox" value="true" id="productPOS" name="pos">
                                        <label for="productPOS"><i class="bi bi-calculator"></i> POS</label>
                                    </div>
                                    <div class="check-card">
                                        <input class="productNeeded" type="checkbox" value="true" id="productDelivery" name="deliveryIntegration">
                                        <label for="productDelivery"><i class="bi bi-truck"></i> Delivery Integration</label>
                                    </div>
                                    <div class="check-card">
                                        <input class="productNeeded" type="checkbox" value="true" id="productPayment" name="onlinePayment">
                                        <label for="productPayment"><i class="bi bi-credit-card"></i> Online Payment</label>
                                    </div>
                                </div>
                                <small class="text-danger warningText" id="smallProduct">Please select at least one product.</small>
                            </div>

                            <div class="mb-3">
                                <label for="note" class="form-label">Note <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="note" name="note" rows="3" placeholder="Additional information you would like us to know." required></textarea>
                                <small class="text-danger warningText" id="smallNote">Please provide a note.</small>
                            </div>
                        </div>

                        <!-- ========== SECTION 3: Book Appointment ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-calendar-check"></i> Book Appointment</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="bookingDate" class="form-label">Booking Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="bookingDate" name="bookingDate"
                                           min="<?php echo $bookingMin; ?>" max="<?php echo $bookingMax; ?>"
                                           onkeydown="return false" onpaste="return false" onclick="this.showPicker && this.showPicker()"
                                           inputmode="none" required>
                                    <div class="form-text">June <?php echo date('Y', strtotime($bookingMin)); ?> only &middot; max <?php echo $maxSlots; ?> slots/day &middot; <?php echo $bookingHourMin; ?>–<?php echo $bookingHourMax; ?> (BKK)</div>
                                    <div id="slotStatus"></div>
                                    <small class="text-danger warningText" id="smallBookingDate">Please select a booking date.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Booking Time <span class="text-danger">*</span></label>
                                    <div class="time-row">
                                        <select id="bookingHour" class="form-select" required>
                                            <option value="" disabled selected>HH</option>
                                            <?php
                                            $__hMin = (int)substr($bookingHourMin, 0, 2);
                                            $__hMax = (int)substr($bookingHourMax, 0, 2);
                                            for ($__h = $__hMin; $__h <= $__hMax; $__h++) {
                                                $__v = str_pad($__h, 2, '0', STR_PAD_LEFT);
                                                echo '<option value="' . $__v . '">' . $__v . '</option>';
                                            }
                                            ?>
                                        </select>
                                        <span class="fw-bold">:</span>
                                        <select id="bookingMinute" class="form-select" required>
                                            <option value="" disabled selected>MM</option>
                                            <?php for ($__m = 0; $__m < 60; $__m++) {
                                                $__v = str_pad($__m, 2, '0', STR_PAD_LEFT);
                                                echo '<option value="' . $__v . '">' . $__v . '</option>';
                                            } ?>
                                        </select>
                                    </div>
                                    <div class="form-text" id="bookingTimeHint"><i class="bi bi-info-circle"></i> Please select a booking date first.</div>
                                    <input type="hidden" id="bookingTime" name="bookingTime">
                                    <small class="text-danger warningText" id="smallBookingTime">Please select a booking time.</small>
                                </div>
                            </div>
                        </div>

                        <!-- ===== Submit ===== -->
                        <div class="submit-area">
                            <button type="button" class="btn btn-submit" id="cancelBtn" onclick="validateForm()"><i class="bi bi-send"></i> Confirm Booking</button>
                            <span id="loadingAjax" class="status-msg text-primary warningText"><img src="../assets/img/loadingSpin.gif" alt="" width="28"> Loading...</span>
                            <span id="doneForm" class="status-msg text-success warningText"><i class="bi bi-check-circle-fill"></i> Success</span>
                        </div>

                        <input type="hidden" id="testMode" name="testMode" value="<?php echo $testMode; ?>">
                        <input type="hidden" id="leadSource" name="leadSource" value="<?php echo $leadSource; ?>">
                        <input type="hidden" id="formVersion" name="formVersion" value="<?php echo $formVersion; ?>">
                        <input type="hidden" id="emailVersion" name="emailVersion" value="<?php echo $emailVersion; ?>">
                        <input type="hidden" id="timeStamps" name="timeStamps" value="<?php echo $timestamps; ?>">
                    </form>
                </div>
            </div>
            <?php endif; // bookingClosed ?>
        </section>
    </main>
</div><!-- container-->
<footer class="credit" style="display: flex; justify-content: center; text-align: center;">
    <div>
     Version <?php echo $formVersion;?> Author: IT Team - Distributed By:
    <a
            title="Local For You Website"
            href="https://www.localforyou.com"
            target="_blank"
    >
        Local For You
    </a></div>

</footer>
<script src="../assets/js/jquery.3.6.0.min.js"></script>
<script src="../assets/js/bootstrap5.0.2.bundle.min.js"></script>
<script src="../assets/js/global_data.js?v=1.5.4"></script>
<script src="../assets/js/date_format.js"></script>
<script src="../assets/js/popper.2.11.5.min.js"></script>
<script src="../assets/js/unsubData.js?v=1.5.3"></script>
<script>
    let payload = {};
    const MAX_SLOTS = <?php echo (int)$maxSlots; ?>;
    const SLOT_DURATION_MIN = 90; // ระยะเวลา booking 1 ครั้ง (นาที)
    const GAP_MIN           = 60; // เวลาต้องห่างจาก booking ที่มีอยู่อย่างน้อย (นาที)
    const BOOKING_MIN = "<?php echo $bookingMin; ?>";
    const BOOKING_MAX = "<?php echo $bookingMax; ?>";
    const TODAY_BKK   = "<?php echo $today; ?>";
    const HOUR_MIN    = "<?php echo $bookingHourMin; ?>";
    const HOUR_MAX    = "<?php echo $bookingHourMax; ?>";
    let slotAvailable = false;    // true เมื่อวันที่เลือกยังมีสล็อตว่าง
    let bookedRanges  = [];       // [{start:"HH:MM", end:"HH:MM"}] — ช่วงที่มีคนจองแล้ว

    // คืน "HH:MM" ปัจจุบัน (เวลาเครื่องผู้ใช้ — อาจไม่ตรง BKK แต่ใช้เป็นกันในเบื้องต้น)
    function nowHHMM(){
        const d = new Date();
        return String(d.getHours()).padStart(2,"0") + ":" + String(d.getMinutes()).padStart(2,"0");
    }

    // helper: แปลง "HH:MM" → นาที
    function hmToMin(s){ const [h,m]=s.split(":").map(Number); return h*60+m; }

    // คืน true ถ้าเวลาอยู่นอกช่วงที่อนุญาต (HOUR_MIN – HOUR_MAX)
    function isTimeOutOfRange(timeStr){
        if (!timeStr || !/^\d{2}:\d{2}$/.test(timeStr)) return true;
        const t = hmToMin(timeStr);
        return t < hmToMin(HOUR_MIN) || t > hmToMin(HOUR_MAX);
    }

    // คืน true ถ้าเวลาเริ่มใหม่อยู่ห่างจากเวลาเริ่มของ booking เดิมน้อยกว่า GAP_MIN
    function isTimeConflict(timeStr){
        if (!timeStr) return false;
        const start = hmToMin(timeStr);
        const end   = start + SLOT_DURATION_MIN;
        return bookedRanges.some(r => {
            const rs = hmToMin(r.start);
            const re = hmToMin(r.end);
            // past guard — ห้ามเลือกเวลาที่ผ่านมาแล้ว
            if (r.past) return start < re && end > rs;
            // ต้องห่างจากเวลาเริ่มของ booking เดิม ≥ GAP_MIN
            return Math.abs(start - rs) < GAP_MIN;
        });
    }

    $( document ).ready(function() {
        $(".warningText").hide();

        // helper: ซิงค่าเวลาเมื่อเปลี่ยนวันหรือวันไม่ว่าง (dropdown เปิดได้ตลอดเวลา)
        function setTimeEnabled(enabled) {
            const $hint = $("#bookingTimeHint");
            if (!enabled) {
                $("#bookingHour, #bookingMinute").val("");
                $("#bookingTime").val("");
                $hint.html('<i class="bi bi-info-circle"></i> Please select a booking date first.');
            } else {
                $hint.html('<i class="bi bi-clock"></i> ' + HOUR_MIN + '–' + HOUR_MAX + ' (BKK) &middot; 1-hour gap from existing bookings');
            }
        }

        // จำกัด option ของ minute ตามชั่วโมงที่เลือก (HH=HOUR_MAX → MM อยู่ใน 0..maxMin, HH=HOUR_MIN → MM อยู่ใน minMin..59)
        function rebuildMinuteOptions() {
            const h = parseInt($("#bookingHour").val(), 10);
            const $mm = $("#bookingMinute");
            const prev = $mm.val();
            const [maxH, maxM] = HOUR_MAX.split(":").map(Number);
            const [minH, minM] = HOUR_MIN.split(":").map(Number);
            let lo = 0, hi = 59;
            if (!isNaN(h)) {
                if (h === maxH) hi = maxM;
                if (h === minH) lo = Math.max(lo, minM);
            }
            let html = '<option value="" disabled selected>MM</option>';
            for (let i = lo; i <= hi; i++) {
                const v = String(i).padStart(2, "0");
                html += '<option value="' + v + '">' + v + '</option>';
            }
            $mm.html(html);
            // ถ้าค่าเดิมยังอยู่ในช่วงใหม่ ให้คงไว้
            const pn = parseInt(prev, 10);
            if (!isNaN(pn) && pn >= lo && pn <= hi) $mm.val(prev);
        }

        $("#bookingHour").on("change", rebuildMinuteOptions);

        // รวม hour+minute -> hidden #bookingTime แล้ว trigger change
        $("#bookingHour, #bookingMinute").on("change", function() {
            const h = $("#bookingHour").val();
            const m = $("#bookingMinute").val();
            if (h && m) {
                $("#bookingTime").val(h + ":" + m).trigger("change");
            } else {
                $("#bookingTime").val("");
            }
        });

        // เช็ค availability เมื่อเลือกวัน
        $("#bookingDate").on("change", function() {
            const date = $(this).val();
            const $status = $("#slotStatus");
            slotAvailable = false;
            setTimeEnabled(false);

            if (!date) { $status.text(""); return; }

            $status.html('<span style="color:#6c757d;">Checking availability…</span>');

            $.getJSON("checkAvailability.php", { date: date })
                .done(function(res) {
                    bookedRanges = [];
                    if (res.error) {
                        $status.html('<span style="color:#dc3545;">⚠ ' + res.error + '</span>');
                        return;
                    }
                    // เวลาต้องห่างจาก booking ที่มีอยู่ ≥ 1 ชม.
                    const existing = res.bookedRanges || [];
                    bookedRanges = existing.map(r => ({ start: r.start, end: r.end, past: false }));
                    if (date === TODAY_BKK) {
                        bookedRanges.push({ start: "00:00", end: nowHHMM(), past: true });
                    }
                    const msg = res.booked + "/" + res.max + " booked";
                    const rangesStr = existing.length
                        ? " (The booking full period is: " + existing.map(r => r.start + "–" + r.end).join(", ") + ")" : "";
                    if (res.available) {
                        slotAvailable = true;
                        setTimeEnabled(true);
                        $status.html('<span style="color:#198754;">✓ Available — ' + msg + rangesStr + '</span>');
                    } else {
                        $status.html('<span style="color:#dc3545;">✗ ไม่ว่าง (Full) — ' + msg + rangesStr + '</span>');
                    }
                })
                .fail(function() {
                    bookedRanges = [];
                    $status.html('<span style="color:#dc3545;">⚠ Failed to check availability</span>');
                });
        });

        // เช็คเวลาที่เลือก — out-of-range + 1 ชม. gap + กันเวลาในอดีต
        $("#bookingTime").on("change", function() {
            const t = $(this).val();
            const $warn = $("#smallBookingTime");
            const $sel  = $("#bookingHour, #bookingMinute");
            if (!t) { $warn.hide(); $sel.removeClass("is-invalid"); return; }
            if (isTimeOutOfRange(t)) {
                $warn.text("Please select a time between " + HOUR_MIN + " and " + HOUR_MAX + " (BKK).").show();
                $sel.addClass("is-invalid");
                $("#bookingMinute").val("");
                $(this).val("");
            } else if (isTimeConflict(t)) {
                $warn.text("Please choose a start time at least 1 hour away from the start of an existing booking (or not in the past).").show();
                $sel.addClass("is-invalid");
            } else {
                $warn.hide();
                $sel.removeClass("is-invalid");
            }
        });
    });//ready

    function validateForm(){
        let fullName = $("#fullName").val().trim();
        let restaurantName = $("#restaurantName").val().trim();
        let country = $("#formCountry").val();
        let email = $("#email").val().trim();
        let note = $("#note").val().trim();
        let bookingDate = $("#bookingDate").val();
        let bookingTime = $("#bookingTime").val();
        let productChecked = $(".productNeeded:checked").length;

        $(".warningText").hide();

        if (fullName.length < 1){
            $("#smallFullName").show();
            $("#fullName").focus();
        }else if (restaurantName.length < 1){
            $("#smallRestaurantName").show();
            $("#restaurantName").focus();
        }else if (country === "" || country === null){
            $("#smallCountry").show();
            $("#formCountry").focus();
        }else if (email === ""){
            $("#smallEmail").text("Please provide a valid email address.").show();
            $("#email").focus();
        }else if (!validateEmail(email)){
            $("#smallEmail").text("Email format is invalid. Please include '@' and domain, e.g. mail@localforyou.com").show();
            $("#email").focus();
        }else if (productChecked < 1){
            $("#smallProduct").show();
        }else if (note.length < 1){
            $("#smallNote").show();
            $("#note").focus();
        }else if (!bookingDate){
            $("#smallBookingDate").show();
            $("#bookingDate").focus();
        }else if (!slotAvailable){
            alert("วันที่เลือกไม่มีสล็อตว่าง กรุณาเลือกวันอื่น");
            $("#bookingDate").focus();
        }else if (!bookingTime){
            $("#smallBookingTime").text("Please select a booking time.").show();
            $("#bookingTime").focus();
        }else if (isTimeOutOfRange(bookingTime)){
            $("#smallBookingTime").text("Please select a time between " + HOUR_MIN + " and " + HOUR_MAX + " (BKK).").show();
            $("#bookingTime").val("").focus();
        }else if (isTimeConflict(bookingTime)){
            $("#smallBookingTime").text("Please choose a start time at least 1 hour away from the start of an existing booking (or not in the past).").show();
            $("#bookingTime").focus();
        }else{
            $("#cancelBtn").hide();
            $("#loadingAjax").fadeIn(100);


            // read all input, select, textarea in form
            $('form').find('input, select, textarea').each(function () {
                let name = $(this).attr('name');
                let value = $(this).val();

                if (name) {
                    if ($(this).is(':radio')) { if ($(this).is(':checked')) { payload[name] = value; } } // เช็คว่าเป็น radio และถูกเลือกหรือไม่
                    else if ($(this).is(':checkbox')) { payload[name] = $(this).is(':checked'); } // เช็คว่าเป็น checkbox หรือไม่
                    else { payload[name] = value; } // ค่าอื่นๆ (text, select, textarea)
                }
            }); //read from

            const countryMap = {
                AU: "Australia",
                NZ: "New Zealand",
                TH: "Thailand",
                US: "United States",
                CA: "Canada",
                UK: "United Kingdom"
            };

            payload.country = countryMap[payload.country] || "Please select country";

            // รวม products ที่เลือกเป็น string เรียงตามลำดับ
            const productOrder = [
                { key: "onlineOrderingSystem", label: "Online Ordering System" },
                { key: "pos",                  label: "POS" },
                { key: "deliveryIntegration",  label: "Delivery Integration" },
                { key: "onlinePayment",        label: "Online Payment" },
            ];
            payload.productsNeeded = productOrder
                .filter(p => payload[p.key] === true || payload[p.key] === "true")
                .map(p => p.label)
                .join(", ");

            // คำนวณ endBookingTime = bookingTime + 1:30 ชม.
            if (bookingTime && /^\d{2}:\d{2}$/.test(bookingTime)) {
                const [h, m] = bookingTime.split(":").map(Number);
                const total = h * 60 + m + 90;
                const eh = Math.floor(total / 60) % 24;
                const em = total % 60;
                payload.endBookingTime = String(eh).padStart(2, "0") + ":" + String(em).padStart(2, "0");
            }
            console.log(payload);

            // Final availability re-check ก่อนส่ง (ป้องกัน race กรณีมีคนจองไปก่อน)
            $.getJSON("checkAvailability.php", { date: bookingDate })
                .done(function(res) {
                    if (res && res.available) {
                        saveDB();
                        sendWebhook();
                    } else {
                        $("#loadingAjax").hide();
                        $("#cancelBtn").show();
                        slotAvailable = false;
                        $("#bookingDate").trigger("change");
                        alert("ขออภัย มีผู้จองวันนี้ครบแล้ว กรุณาเลือกวันอื่น");
                    }
                })
                .fail(function() {
                    // ถ้า check ล้มเหลว ให้ส่งไปก่อน (fail-open) — Make + Calendar จะ reconcile ได้
                    saveDB();
                    sendWebhook();
                });
        }//Validate pass
    }//end validateForm()

    function sendWebhook(){
        console.log("call webhook with payload:", payload);
        const callAjax = $.ajax({
            type: "POST",
            crossDomain: true,
            dataType: 'text',
            url: "https://hook.us1.make.com/oa3s2ft8eb3y93gn417egup19rt69k8q",
            data: payload
        });

        callAjax.done(function(res) {
            $("#loadingAjax").hide();
            $("#doneForm").show();
            console.log("Ajax done", res);
            location.replace("https://localforyou.com/thank-you/");
        });

        callAjax.fail(function(xhr, status, error) {
            $("#loadingAjax").hide();
            $("#cancelBtn").show();
            console.log("ajax webhook fail!!", status, error);
            alert("Send fail!! กรุณาลองใหม่");
        });
    }

    function saveDB(){

        //TODO : Save Database table formASAP
        const ajaxSaveDB = $.ajax({
                url: "activeajax.php",
                method: 'POST',
                async: false,
                cache: false,
                dataType: 'json',
                data: payload,
            }
        );

        ajaxSaveDB.done(function(res) {
            console.log("ajax Send to Database Done", res);
            // เก็บ bookingId จาก DB ลง payload เพื่อส่งให้ Make (ใช้สำหรับ reschedule)
            if (res && res.bookingId) {
                payload.bookingId = res.bookingId;
            }
            return true;
        });

        ajaxSaveDB.fail(function(xhr, status, error) {
            console.log("ajax Send to Database fail!!");
            console.log(status + ': ' + error);
            return false;
        });
    }//saveDB

    function validateEmail(email) {
        // ใช้ regex ตรวจรูปแบบอีเมล
        const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-z]{2,}$/i;
        return emailPattern.test(email);
    }

</script>

</body>
</html>