<?php
/**
 * reschedule.php
 * หน้าเปลี่ยนเวลานัด — เปิดผ่าน URL: /reschedule.php?id=<bookingId>
 */
global $db;
date_default_timezone_set('Asia/Bangkok');
include '../assets/db/db.php';
include '../assets/db/initDB2.php';

// ===== Booking slot config (ต้องตรงกับ index.php + checkAvailability.php) =====
$maxSlots       = 2;
$bookingMin     = "2026-07-13";
$bookingMax     = "2026-07-31";
$bookingHourMin = "06:00";
$bookingHourMax = "18:00";

$today = date("Y-m-d");
if ($today > $bookingMin) $bookingMin = $today;
$bookingClosed = ($today > $bookingMax);

// ===== Load booking =====
$bookingId = !empty($_GET['id']) ? trim($_GET['id']) : '';
$booking   = null;
$error     = null;

if (!preg_match('/^[a-f0-9]{32}$/i', $bookingId)) {
    $error = 'Invalid booking link.';
} else {
    $db->query(
        'SELECT id, bookingId, fullName, restaurantName, Country, Email, productNees, noteForm,
                bookingDate, bookingTime, endBookingTime, gcalEventId, status
           FROM `formASAP` WHERE `bookingId` = ? LIMIT 1',
        $bookingId
    );
    $row = $db->fetchArray();
    if (!$row) {
        $error = 'Booking not found.';
    } elseif ($row['status'] === 'cancelled') {
        $error = 'This booking has already been cancelled.';
    } elseif ($row['bookingDate'] < $today) {
        $error = 'Cannot reschedule a booking from the past.';
    } else {
        $booking = $row;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <title>L4U - Reschedule Booking</title>
    <?php include "form_header.php"; ?>
    <!-- Modern UI: Bootstrap Icons + Montserrat + shared form styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../aiAraya/assets/css/customerDetailsForm.css?v=2.0.0">
    <style>
        #slotStatus { font-size: 0.82rem; margin-top: 8px; min-height: 1.1rem; }
        .time-row { display: flex; align-items: center; gap: 10px; }
        .time-row .form-select { max-width: 130px; }
        .submit-area .status-msg { display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; margin-left: 12px; }
        .current-booking-table { width: 100%; font-size: 0.88rem; }
        .current-booking-table th { width: 150px; color: #7a7f8c; font-weight: 500; padding: 6px 0; }
        .current-booking-table td { padding: 6px 0; color: #1a1a2e; font-weight: 500; }
    </style>
</head>
<body>

<div class="container">
    <main>
        <section style="min-height: 60vh;">

        <?php if ($error): ?>
            <div class="form-div" style="padding: 40px;">
                <div class="alert alert-danger mb-0">
                    <h4 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Unable to Reschedule</h4>
                    <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                </div>
            </div>
        <?php elseif ($bookingClosed): ?>
            <div class="form-div" style="padding: 40px;">
                <div class="alert alert-warning mb-0">
                    <h4 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Booking Closed</h4>
                    <p class="mb-0">Bookings for June 2026 are now closed.</p>
                </div>
            </div>
        <?php else: ?>

            <div class="form-div">

                <!-- ===== Header ===== -->
                <div class="form-header">
                    <img src="../assets/img/newL4U-logo-100x100.png" alt="L4U Logo" class="logo-img">
                    <h3 class="form-title text-uppercase">Reschedule Booking</h3>
                    <p class="form-subtitle">Change your appointment to a more convenient time.</p>
                </div>

                <!-- ===== Form Body ===== -->
                <div class="form-body">
                    <form id="rescheduleForm" action="#" method="POST">
                        <input type="hidden" id="bookingId" name="bookingId" value="<?php echo htmlspecialchars($booking['bookingId']); ?>">

                        <!-- ========== SECTION 1: Current Booking Details ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-info-circle"></i> Current Booking Details</h5>
                            <table class="current-booking-table">
                                <tr><th>Name</th><td><?php echo htmlspecialchars($booking['fullName']); ?></td></tr>
                                <tr><th>Restaurant</th><td><?php echo htmlspecialchars($booking['restaurantName'] ?? ''); ?></td></tr>
                                <tr><th>Email</th><td><?php echo htmlspecialchars($booking['Email']); ?></td></tr>
                                <tr><th>Country</th><td><?php echo htmlspecialchars($booking['Country']); ?></td></tr>
                                <tr><th>Products</th><td><?php echo htmlspecialchars($booking['productNees']); ?></td></tr>
                                <tr>
                                    <th>Current Time</th>
                                    <td><strong><?php echo $booking['bookingDate'] . ' ' . substr($booking['bookingTime'], 0, 5); ?></strong> – <?php echo substr($booking['endBookingTime'], 0, 5); ?></td>
                                </tr>
                            </table>
                        </div>

                        <!-- ========== SECTION 2: Select New Time ========== -->
                        <div class="form-section">
                            <h5 class="section-title"><i class="bi bi-calendar-check"></i> Select New Time</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="bookingDate" class="form-label">Booking Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="bookingDate" name="bookingDate"
                                           min="<?php echo $bookingMin; ?>" max="<?php echo $bookingMax; ?>"
                                           onkeydown="return false" onpaste="return false" onclick="this.showPicker && this.showPicker()"
                                           inputmode="none" required>
                                    <div class="form-text">June 2026 only &middot; max <?php echo $maxSlots; ?> slots/day &middot; <?php echo $bookingHourMin; ?>–<?php echo $bookingHourMax; ?> (BKK)</div>
                                    <div id="slotStatus"></div>
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
                                    <small class="text-danger warningText" id="smallBookingTime"></small>
                                </div>
                            </div>
                        </div>

                        <!-- ===== Submit ===== -->
                        <div class="submit-area">
                            <button type="button" class="btn btn-submit" id="submitBtn" onclick="submitReschedule()"><i class="bi bi-calendar-event"></i> Confirm New Time</button>
                            <span id="loadingAjax" class="status-msg text-primary warningText"><img src="../assets/img/loadingSpin.gif" alt="" width="28"> Loading...</span>
                            <span id="doneForm" class="status-msg text-success warningText"><i class="bi bi-check-circle-fill"></i> Success</span>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
        </section>
    </main>
</div><!-- container-->

<footer class="credit" style="display: flex; justify-content: center; text-align: center;">
    <div>Local For You · IT Team</div>
</footer>

<script src="../assets/js/jquery.3.6.0.min.js"></script>
<script src="../assets/js/bootstrap5.0.2.bundle.min.js"></script>
<script src="../assets/js/popper.2.11.5.min.js"></script>
<script>
<?php if (!$error && !$bookingClosed): ?>
    const MAX_SLOTS         = <?php echo (int)$maxSlots; ?>;
    const SLOT_DURATION_MIN = 90;
    const GAP_MIN           = 60;
    const TODAY_BKK         = "<?php echo $today; ?>";
    const HOUR_MIN          = "<?php echo $bookingHourMin; ?>";
    const HOUR_MAX          = "<?php echo $bookingHourMax; ?>";
    const CURRENT_BOOKING_DATE = "<?php echo $booking['bookingDate']; ?>";
    const CURRENT_BOOKING_TIME = "<?php echo substr($booking['bookingTime'],0,5); ?>";
    let slotAvailable = false;
    let bookedRanges  = [];

    function nowHHMM(){ const d=new Date(); return String(d.getHours()).padStart(2,"0")+":"+String(d.getMinutes()).padStart(2,"0"); }
    function hmToMin(s){ const [h,m]=s.split(":").map(Number); return h*60+m; }
    function isTimeOutOfRange(t){
        if (!t || !/^\d{2}:\d{2}$/.test(t)) return true;
        const x = hmToMin(t);
        return x < hmToMin(HOUR_MIN) || x > hmToMin(HOUR_MAX);
    }
    function isTimeConflict(t){
        if(!t) return false;
        const s=hmToMin(t), e=s+SLOT_DURATION_MIN;
        return bookedRanges.some(r => {
            const rs=hmToMin(r.start), re=hmToMin(r.end);
            if(r.past) return s < re && e > rs;
            // ต้องห่างจากเวลาเริ่มของ booking เดิม ≥ GAP_MIN
            return Math.abs(s - rs) < GAP_MIN;
        });
    }

    function setTimeEnabled(enabled){
        const $hint = $("#bookingTimeHint");
        if (!enabled) {
            $("#bookingHour, #bookingMinute").val("");
            $("#bookingTime").val("");
            $hint.html('<i class="bi bi-info-circle"></i> Please select a booking date first.');
        } else {
            $hint.html('<i class="bi bi-clock"></i> ' + HOUR_MIN + '–' + HOUR_MAX + ' (BKK) &middot; 1-hour gap from existing bookings');
        }
    }

    $(document).ready(function(){
        $(".warningText").hide();

        function rebuildMinuteOptions(){
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
            const pn = parseInt(prev, 10);
            if (!isNaN(pn) && pn >= lo && pn <= hi) $mm.val(prev);
        }

        $("#bookingHour").on("change", rebuildMinuteOptions);

        $("#bookingHour, #bookingMinute").on("change", function(){
            const h = $("#bookingHour").val();
            const m = $("#bookingMinute").val();
            if (h && m) {
                $("#bookingTime").val(h + ":" + m).trigger("change");
            } else {
                $("#bookingTime").val("");
            }
        });

        $("#bookingDate").on("change", function(){
            const date = $(this).val();
            const $status = $("#slotStatus");
            slotAvailable = false;
            setTimeEnabled(false);
            if(!date){ $status.text(""); return; }

            $status.html('<span style="color:#6c757d;">Checking availability…</span>');

            $.getJSON("checkAvailability.php", { date: date })
                .done(function(res){
                    bookedRanges = [];
                    if(res.error){
                        $status.html('<span style="color:#dc3545;">⚠ '+res.error+'</span>');
                        return;
                    }
                    // ต้องห่างจาก booking ที่มีอยู่ ≥ 1 ชม. — ยกเว้น slot ปัจจุบันของตัวเอง
                    let existing = res.bookedRanges || [];
                    if (date === CURRENT_BOOKING_DATE) {
                        existing = existing.filter(r => r.start !== CURRENT_BOOKING_TIME);
                    }
                    bookedRanges = existing.map(r => ({ start:r.start, end:r.end, past:false }));
                    if(date === TODAY_BKK){
                        bookedRanges.push({ start:"00:00", end:nowHHMM(), past:true });
                    }
                    const msg = res.booked+"/"+res.max+" booked";
                    const rangesStr = existing.length
                        ? " (The booking full period is: "+existing.map(r=>r.start+"–"+r.end).join(", ")+")" : "";
                    if(res.available){
                        slotAvailable = true;
                        setTimeEnabled(true);
                        $status.html('<span style="color:#198754;">✓ Available — '+msg+rangesStr+'</span>');
                    } else {
                        $status.html('<span style="color:#dc3545;">✗ Unavailable (Full) — '+msg+rangesStr+'</span>');
                    }
                })
                .fail(function(){
                    $status.html('<span style="color:#dc3545;">⚠ Failed to check availability</span>');
                });
        });

        $("#bookingTime").on("change", function(){
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
    });

    function submitReschedule(){
        const bookingId   = $("#bookingId").val();
        const bookingDate = $("#bookingDate").val();
        const bookingTime = $("#bookingTime").val();

        if(!bookingDate){ alert("Please select a date."); return; }
        if(!slotAvailable){ alert("No slots available on the selected date. Please choose another."); return; }
        if(!bookingTime){ alert("Please select a time."); return; }
        if(isTimeOutOfRange(bookingTime)){
            $("#smallBookingTime").text("Please select a time between " + HOUR_MIN + " and " + HOUR_MAX + " (BKK).").show();
            return;
        }
        if(isTimeConflict(bookingTime)){
            $("#smallBookingTime").text("Please choose a start time at least 1 hour away from the start of an existing booking (or not in the past).").show();
            return;
        }

        $("#submitBtn").hide();
        $("#loadingAjax").fadeIn(100);

        $.ajax({
            url: "rescheduleAjax.php",
            method: "POST",
            dataType: "json",
            data: { bookingId, bookingDate, bookingTime }
        })
        .done(function(res){
            $("#loadingAjax").hide();
            if(res && res.ok){
                $("#doneForm").show();
                location.replace("https://localforyou.com/thank-you/");
            } else {
                $("#submitBtn").show();
                alert("Unable to reschedule: " + (res && res.error ? res.error : "Unknown error"));
            }
        })
        .fail(function(xhr, st, err){
            $("#loadingAjax").hide();
            $("#submitBtn").show();
            alert("Network error. Please try again.");
        });
    }
<?php endif; ?>
</script>

</body>
</html>
