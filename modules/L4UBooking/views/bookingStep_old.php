<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;
$currentPage = basename($_SERVER['PHP_SELF']);
$tomorrow = date("Y-m-d H:i:s", strtotime("now"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Appointment Booking</title>
    <link href="../assets/libs/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../../assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/libs/select2/css/select2.min.css" rel="stylesheet"/>
    <link href="../assets/libs/select2/css/select2-bootstrap-5-theme.min.css" rel="stylesheet"/>
    <link href="../assets/libs/flatpickr/flatpickr.min.css" rel="stylesheet"/>
    <style>
        .step-section {
            display: none;
        }

        .step-section.active {
            display: block;
        }
        #country{
            width:400px;
        }
        #sales{
            width:500px;
        }
        #bookBy{
            width:500px;
        }
        #time{
            width:350px;
        }
        ::placeholder {
            color: #cccccc !important;
            opacity: 1; /* Firefox */
        }
        #timeZone{
            color: #0d6efd !important;
        }
        .red{
            color: red;
        }
    </style>
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container py-5">
    <header>
        <nav class="mb-4"
             aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house-fill"></i> Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Booking</li>
            </ol>
        </nav>
    </header>

    <main>
        <div class="mb-4">
            <h4 class="text-primary"><i class="bi bi-journal-bookmark-fill"></i> Local For You - # 1 Marketing Agency for Thai (Internal)</h4>
            <small class="text-muted">Please select the appointment type to make a booking.</small>
        </div>


        <form id="bookingForm">
            <!-- Step 1: Business Type & Country-->
            <div class="step-section active" id="step-1">
                <div class="row">
                     <div class="col-6">
                        <label for="shop_type" class="form-label">1. Select your shop type <span class="red">*</span><span class="red" id="alertShopType">Please Select Shop Type.</span></label>
                        <div class="d-flex flex-row gap-3">
                            <select id="shop_type" name="shop_type" class="form-control form-select mb-3" >
                                <option value="">--Please Select--</option>
                                <?php
                                $shopType = $db->query('SELECT * FROM `tb_shopType` WHERE status = ?',1)->fetchAll();
                                foreach ($shopType as $row) {
                                ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>

                <label for="country">2. Select your country <span class="red">*</span><span class="red" id="alertCountry">Please Select Country.</span></label>
                <div class="d-flex flex-row gap-3">
                    <select id="country" name="country" class="form-select mb-3" >
                        <option value="">--Please Select--</option>
                        <option value="AU">Australia</option>
                        <option value="NZ">New Zealand</option>
                        <option value="US">United States</option>
                        <option value="UK">United Kingdom</option>
                        <option value="CA">Canada</option>
                        <option value="TH">Thailand</option>
                    </select>
<!--                    Timezone: <span id="timeZone" class="text-primary timeZone">-</span>-->
                    <label for="state">Select State <span class="red">*</span></label>
                    <select id="state" name="state" class="form-select mb-3" style="width: 400px;">
                        <option value="">-- Please Select --</option>
                    </select>
                    <span class="red" id="alertState">* Please Select State.</span>
                </div>
                <div class="d-flex flex-column gap-1">
                    <label for="timezone">Select Timezone <span class="red">*</span></label>
                    <select id="timezone" name="timezone" class="form-select mb-3" style="width: 400px;">
                        <option value="">-- Please Select --</option>
                    </select>
                </div>
                <div class="d-flex flex-column justify-content-start align-items-start mb-3">
                    <div class="mt-2">
                        <label for="city">City</label>
                        <input aria-label="none" type="text" id="city" name="city" class="form-control" placeholder="City name (optional)"/>
                    </div>
                </div>
                <div class="d-flex flex-row gap-3">
                    <button type="button" class="btn btn-primary" onclick="validateStep1()">Next <i class="bi bi-arrow-right-short"></i></button>
                </div>
            </div>

            <!-- Step 3: Select Sales -->
            <div class="step-section" id="step-3">
                <div class="row">
                    <div class="col-6">
                        <label for="sales">3. Select salesperson</label><span class="red">* </span><span class="red" id="alertSale">Please Select Sale.</span>
                        <div class="d-flex flex-row gap-3 mb-3">
                            <select id="sales" name="sales" class="form-select mb-3" >
                                <option></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <label for="bookBy">4. Booked By</label><span class="red">* </span><span class="red" id="alertBooking">Please Select Booking.</span>
                        <div class="d-flex flex-row gap-3 mb-3">
                            <select id="bookBy" name="bookBy" class="form-select mb-3" >
                                <option></option>
                            </select>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-5">
                        <label for="presentation">5. Presentation Language</label><span class="red">* </span><span class="red" id="alertLanguage">Please Select Booking.</span>
                        <div class="d-flex flex-row gap-3 mb-3">
                            <select id="presentation" name="presentation" class="form-select" >
                                <option value="">--Please Select--</option>
                                <option value="English">English</option>
                                <option value="Thai">Thai</option>
                            </select>
                        </div>
                    </div>
                </div>



                <div class="col-4">
                    <label for="date">6. Select appointment date</label>
                    <div class="d-flex flex-row gap-3 mb-1">
                        <input type="text" id="date" name="date" value="<?php echo $tomorrow; ?>" class="form-control mb-3" />
                    </div>
                </div>

                <div class="col-8">
                    <label for="time">7. Available time <small class="text-muted">(<span id="timeZone" class="text-primary timeZone">-</span>)</small></label>
                    <div class="d-flex flex-column gap-3 mb-3">
                        <select id="time" name="time" class="form-select mb-3" >
                            <option></option>
                        </select>
                        <small class="text-muted small" id="thTimePreview"></small>
                    </div>
                </div>
                <div class="d-flex flex-row gap-3 mb-3">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(1)"><i class="bi bi-arrow-left-short"></i> Previous</button>
                    <button type="button" class="btn btn-primary" onclick="validateStep3()">Next <i class="bi bi-arrow-right-short"></i></button>
                </div>
            </div>

            <!-- Step 8: Customer Info -->
            <div class="step-section" id="step-8">
                <label>8. Customer information</label>
                    <div class="col-5">
                        <label for="shop_name">Shop name</label><span class="red">* </span><span class="red" id="alertShopName">Please enter the shop name</span>
                            <input type="text" id="shop_name" name="shop_name" class="form-control mb-2" autocomplete="off" placeholder="fill your shop name" >
                    </div>

                    <div class="row">
                        <div class="col-5">
                            <label for="customer_name">Customer name</label><span class="red">* </span><span class="red" id="alertCustomerName">Please enter Customer name</span>
                                <input type="text" id="customer_name" name="customer_name" class="form-control mb-2" autocomplete="off" placeholder="fill customer name" >
                        </div>
                        <div class="col-5">
                            <label for="contact_email">Contact email</label><span class="red">* </span><span class="red" id="alertCustomerEmail">Please enter Customer email</span>
                            <span class="red" id="alertCustomerEmailValid">Please enter a valid email address.</span>
                                <input type="email" id="contact_email" name="contact_email" class="form-control mb-2" autocomplete="off" placeholder="fill contact email" >
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-5">
                            <label for="contact_phone">Contact phone</label><span class="red">* </span><span class="red" id="alertCustomerPhone">Please enter Phone number</span>
                            <span class="red" id="alertCustomerPhoneComplete">Please enter a complete number.</span>
                            <div class="input-group mb-2">
                                <span class="input-group-text" id="contact_phone_prefix">+</span>
                                <input type="text" id="contact_phone" name="contact_phone" class="form-control" autocomplete="off" placeholder="Number only e.g. 0930396203" oninput="this.value = this.value.replace(/[^0-9]/g, ''); formatPhoneNumber(this.value, 'contact_phone');">
                            </div>
                            <small class="form-text text-muted">without country's code</small>
                            <small class="form-text text-primary" id="contact_phone_formatted"></small>
                        </div>
                        <div class="col-5">
                            <label for="contact_mobile">Contact mobile</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text" id="contact_mobile_prefix">+</span>
                                <input type="text" id="contact_mobile" name="contact_mobile" class="form-control" autocomplete="off" placeholder="Number only e.g. 0930396203" oninput="this.value = this.value.replace(/[^0-9]/g, ''); formatPhoneNumber(this.value, 'contact_mobile');">
                            </div>
                            <small class="form-text text-muted">without country's code</small>
                            <small class="form-text text-primary" id="contact_mobile_formatted"></small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-5">
                            <label for="line_id">Line ID</label>
                                <input type="text" id="line_id" name="line_id" class="form-control mb-2" autocomplete="off" placeholder="LINE ID">
                        </div>
                        <div class="col-5">
                            <label for="whatsapp">WhatsApp</label>
                                <input type="text" id="whatsapp" name="whatsapp" class="form-control mb-2" autocomplete="off" placeholder="WhatsApp">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-5 mb-3">
                            <label for="address">Address</label>
                            <textarea class="form-control" name="address" id="address" rows="4" placeholder="Address"></textarea>
                        </div>
                        <div class="col-5 mb-3">
                            <label for="comment">Comment</label>
                            <textarea class="form-control" name="comment" id="comment" rows="4" placeholder="Comment"></textarea>
                        </div>
                        <input type="hidden" value="<?php echo $tomorrow; ?>" name="timeToDayNow" id="timeToDayNow">
                    </div>
                <div class="d-flex flex-row gap-3 mb-3">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(3)"><i class="bi bi-arrow-left-short"></i> Previous</button>
                    <button type="button" class="btn btn-primary" onclick="showReview()">Next <i class="bi bi-arrow-right-short"></i></button>
                </div>
            </div>

            <!-- Step 9: Review Info -->
            <div class="step-section" id="step-9">
                <label>9. Review your booking</label>
                <div id="reviewSection" class="row row-cols-1 row-cols-md-2 g-3 mb-3 p-5">
                    <!-- Filled dynamically by JS -->
                </div>
                <div class="d-flex flex-row gap-3 mb-3">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(8)"><i class="bi bi-arrow-left-short"></i> Previous</button>
                </div>
                <div class="d-flex flex-row justify-content-center mb-3">
                    <button type="button" class="btn btn-success" onclick="submitBooking()">Confirm & Book <i class="bi bi-journal-check"></i></button>
                </div>
            </div>


        </form>
    </main>
</div>

<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/select2/js/select2.min.js"></script>
<script src="../assets/libs/flatpickr/flatpickr.js"></script>
<script src="https://cdn.jsdelivr.net/npm/luxon@3/build/global/luxon.min.js"></script>

<script>
    //ประกาศตัวแปรผูกกับ id กับ class
    const shop_type = $('#shop_type');
    const country = $('#country');
    const city = $('#city');
    const timeZone = $('.timeZone');
    const sales = $('#sales');
    const bookBy = $('#bookBy');
    const presentation = $('#presentation');
    const date = $('#date');
    const time = $('#time');
    const shop_name = $('#shop_name');
    const customer_name = $('#customer_name');
    const contact_email = $('#contact_email');
    const contact_phone = $('#contact_phone');
    const contact_mobile = $('#contact_mobile');
    const line_id = $('#line_id');
    const whatsapp = $('#whatsapp');
    const address = $('#address');
    const comment = $('#comment');
    const state = $('#state');
    const timeToDayNow = $('#timeToDayNow');

    //ตัวแปรเปล่า
    let appointmentDetail = {};
    let sendEmailPayload = {};
    let thaiTimePreview = "";
    let thaiDayPreview = "";

    //ตัวแปร array
    const timeZoneMap = {
        "AU": "Australia/Sydney",
        "NZ": "Pacific/Auckland",
        "US": "America/New_York",
        "UK": "Europe/London",
        "CA": "America/Toronto",
        "TH": "Asia/Bangkok"
    };

    // Country dial codes mapping
    const countryDialCodes = {
        "AU": "61",
        "NZ": "64",
        "US": "1",
        "UK": "44",
        "CA": "1",
        "TH": "66"
    };

    //เมื่อเริ่มให้ทำอะไร
    $(document).ready(function () {
        //$('#shop_type').select2({placeholder: 'Select your store type',theme: 'bootstrap-5'});
        //country.select2({placeholder: 'Select country',theme: 'bootstrap-5'});
        $('#sales').select2({placeholder: 'Select salesperson',theme: 'bootstrap-5'});
        $('#time').select2({placeholder: 'Select appointment time',theme: 'bootstrap-5'});
        $('#bookBy').select2({placeholder: 'Select booked person',theme: 'bootstrap-5'});

        $('#alertShopType').hide();
        $('#alertCountry').hide();
        $('#alertState').hide();
        $('#alertSale').hide();
        $('#alertBooking').hide();
        $('#alertLanguage').hide();
        $('#alertShopName').hide();
        $('#alertCustomerName').hide();
        $('#alertCustomerEmail').hide();
        $('#alertCustomerPhone').hide();
        $('#alertCustomerEmailValid').hide();
        $('#alertCustomerPhoneComplete').hide();

        date.flatpickr({
            minDate: new Date().fp_incr(0),
            maxDate: new Date().fp_incr(7),
            dateFormat: 'Y-m-d',
            disableMobile: true
        })

        time.empty().trigger('change');
        const times = [];
        for (let h = 0; h < 24; h++) {
            for (let m = 0; m < 60; m += 15) {
                const hh = h.toString().padStart(2, '0');
                const mm = m.toString().padStart(2, '0');
                const label = `${hh}:${mm}`;
                times.push(new Option(label, `${hh}:${mm}:00`, false, false));
            }
        }
        time.append(times).trigger('change');

        //ตัวแปร array เกี่ยวกับ timezone
        const timezones = {
            AU: "Australia/Sydney",
            NZ: "Pacific/Auckland",
            US: "America/New_York",
            UK: "Europe/London",
            CA: "America/Toronto",
            TH: "Asia/Bangkok"
        };


        //เมื่อตัวแปร country จะเปลี่ยน class timeZone ทั้งจะกลายเป็นชื่อของแต่ล่ะ country ตัวอย่างเมื่อเลือก AU ตัวแปร timeZone จะเท่ากับ Australia/Sydney
        country.on('change', function () {
            const tz = timezones[country.val()] || '';
            timeZone.text(tz);
            updatePhonePrefixes();
        });

        // Function to update phone input prefixes based on selected country
        function updatePhonePrefixes() {
            const selectedCountry = country.val();
            const dialCode = countryDialCodes[selectedCountry] || '';
            $('#contact_phone_prefix').text('+' + dialCode);
            $('#contact_mobile_prefix').text('+' + dialCode);
            // Reformat existing phone numbers with new country code
            formatPhoneNumber($('#contact_phone').val(), 'contact_phone');
            formatPhoneNumber($('#contact_mobile').val(), 'contact_mobile');
        }

        // Function to format phone number with country code
        function formatPhoneNumber(value, fieldId) {
            const selectedCountry = country.val();
            const dialCode = countryDialCodes[selectedCountry] || '';
            if (value && dialCode) {
                const formattedNumber = '+' + dialCode + value.replace(/^0+/, '');
                $('#' + fieldId + '_formatted').text('Formatted: ' + formattedNumber);
                // Update the actual input value for submission
                $('#' + fieldId).data('formatted', formattedNumber);
            } else {
                $('#' + fieldId + '_formatted').text('');
                $('#' + fieldId).data('formatted', '');
            }
        }


        // ✅ Step 3: โหลดเซลทั้งหมดในทีม
        $.get('../models/load_all_sales.php', function (res) {
            if (res.status === 'ok') {
                let options = res.data.map(user => new Option(user.text, user.id, false, false));
                $('#sales').append(options).trigger('change');
            }
        });

        $.get('../models/load_all_sales.php', function (res) {
            if (res.status === 'ok') {
                let options = res.data.map(user => new Option(user.text, user.id, false, false));
                $('#bookBy').append(options).trigger('change');
            }
        });

        // ✅ Step 5: แสดงเวลาทุกช่วงแบบ 15 นาที (00:00 - 23:45)
        date.on('change', function () {
            time.empty().trigger('change');
            const times = [];
            for (let h = 0; h < 24; h++) {
                for (let m = 0; m < 60; m += 15) {
                    const hh = h.toString().padStart(2, '0');
                    const mm = m.toString().padStart(2, '0');
                    const label = `${hh}:${mm}`;
                    times.push(new Option(label, `${hh}:${mm}:00`, false, false));
                }
            }
            time.append(times).trigger('change');
        });

        $('#bookingForm').on('submit', function (e) {

                e.preventDefault();
                $.post('../models/save_appointment.php', $(this).serialize(), function (res) {
                    if (res.status === 'ok') {
                        sendEmail();
                        bookCalendar();
                        alert('✅ Appointment has been booked.');

                        location.href = 'booking_success.php';
                    } else {
                        alert('❌ ' + res.message);
                    }
                });


        });//form submit

        country.on('change', updateThaiTimePreview);
        date.on('change', updateThaiTimePreview);
        time.on('change', updateThaiTimePreview);

        const countryToTimezones = {
            AU: ["Australia/Sydney", "Australia/Brisbane", "Australia/Perth", "Australia/Melbourne", "Australia/Adelaide", "Australia/Hobart", "Australia/Darwin"],
            US: ["America/New_York", "America/Chicago", "America/Denver", "America/Los_Angeles", "America/Phoenix", "America/Anchorage", "America/Indiana/Indianapolis", "America/Detroit", "America/Indiana/Knox" ,"Pacific/Honolulu"],
            CA: ["America/Toronto", "America/Vancouver", "America/Edmonton", "America/Winnipeg", "America/Halifax", "America/St_Johns", "America/Moncton", "America/Montreal","America/Regina"],
            UK: ["Europe/London"],
            NZ: ["Pacific/Auckland", "Pacific/Chatham"],
            TH: ["Asia/Bangkok"]
        };

        //เมื่อตัวแปร country ถูก'เปลี่ยน'
        country.on('change', function () {
            const zones = countryToTimezones[country.val()] || [];
            const timezoneSelect = $('#timezone');
            timezoneSelect.empty().append(`<option value="">-- Please Select --</option>`);
            zones.forEach(zone => {
                timezoneSelect.append(`<option value="${zone}">${zone}</option>`);
            });
            timeZone.text(zones[0] || '-');


            const selectedCountry = country.val();
            const states = countryToState[selectedCountry] || [];
            const stateSelect = $('#state');
            stateSelect.empty().append(`<option value="">-- Please Select --</option>`);
            states.forEach(state => {
                stateSelect.append(`<option value="${state.code}">${state.code} : ${state.name}</option>`);

            });
        });

        $('#state').on('change', function() {
            const selectedCode = $(this).val();
            const states = countryToState[country.val()] || [];
            const selectedState = states.find(s => s.code === selectedCode);
            if (selectedState) {
                $('#timezone').val(selectedState.timezone).trigger('change');
                $('#timeZone').text(selectedState.timezone);
            } else {
                $('#timezone').val('').trigger('change');
                $('#timeZone').text('');
            }
        });

        const countryToState = {
                AU: [
                    { code: "NSW", name: "New South Wales", timezone: "Australia/Sydney" },
                    { code: "VIC", name: "Victoria", timezone: "Australia/Melbourne" },
                    { code: "QLD", name: "Queensland", timezone: "Australia/Brisbane" },
                    { code: "SA", name: "South Australia", timezone: "Australia/Adelaide" },
                    { code: "WA", name: "Western Australia", timezone: "Australia/Perth" },
                    { code: "TAS", name: "Tasmania", timezone: "Australia/Hobart" },
                    { code: "NT", name: "Northern Territory", timezone: "Australia/Darwin" },
                    { code: "ACT", name: "Australian Capital Territory", timezone: "Australia/Sydney" } // ACT ใช้ timezone Sydney
                ],
                US: [
                    { code: "AL", name: "Alabama", timezone: "America/Chicago" },
                    { code: "AK", name: "Alaska", timezone: "America/Anchorage" },
                    { code: "AZ", name: "Arizona", timezone: "America/Phoenix" },
                    { code: "AR", name: "Arkansas", timezone: "America/Chicago" },
                    { code: "CA", name: "California", timezone: "America/Los_Angeles" },
                    { code: "CO", name: "Colorado", timezone: "America/Denver" },
                    { code: "CT", name: "Connecticut", timezone: "America/New_York" },
                    { code: "DE", name: "Delaware", timezone: "America/New_York" },
                    { code: "FL", name: "Florida", timezone: "America/New_York" },
                    { code: "GA", name: "Georgia", timezone: "America/New_York" },
                    { code: "HI", name: "Hawaii", timezone: "Pacific/Honolulu" },
                    { code: "ID", name: "Idaho", timezone: "America/Denver" },
                    { code: "IL", name: "Illinois", timezone: "America/Chicago" },
                    { code: "IN", name: "Indiana", timezone: "America/Indiana/Indianapolis" },
                    { code: "IA", name: "Iowa", timezone: "America/Chicago" },
                    { code: "KS", name: "Kansas", timezone: "America/Chicago" },
                    { code: "KY", name: "Kentucky", timezone: "America/New_York" },
                    { code: "LA", name: "Louisiana", timezone: "America/Chicago" },
                    { code: "ME", name: "Maine", timezone: "America/New_York" },
                    { code: "MD", name: "Maryland", timezone: "America/New_York" },
                    { code: "MA", name: "Massachusetts", timezone: "America/New_York" },
                    { code: "MI", name: "Michigan", timezone: "America/Detroit" },
                    { code: "MN", name: "Minnesota", timezone: "America/Chicago" },
                    { code: "MS", name: "Mississippi", timezone: "America/Chicago" },
                    { code: "MO", name: "Missouri", timezone: "America/Chicago" },
                    { code: "MT", name: "Montana", timezone: "America/Denver" },
                    { code: "NE", name: "Nebraska", timezone: "America/Chicago" },
                    { code: "NV", name: "Nevada", timezone: "America/Los_Angeles" },
                    { code: "NH", name: "New Hampshire", timezone: "America/New_York" },
                    { code: "NJ", name: "New Jersey", timezone: "America/New_York" },
                    { code: "NM", name: "New Mexico", timezone: "America/Denver" },
                    { code: "NY", name: "New York", timezone: "America/New_York" },
                    { code: "NC", name: "North Carolina", timezone: "America/New_York" },
                    { code: "ND", name: "North Dakota", timezone: "America/Chicago" },
                    { code: "OH", name: "Ohio", timezone: "America/New_York" },
                    { code: "OK", name: "Oklahoma", timezone: "America/Chicago" },
                    { code: "OR", name: "Oregon", timezone: "America/Los_Angeles" },
                    { code: "PA", name: "Pennsylvania", timezone: "America/New_York" },
                    { code: "RI", name: "Rhode Island", timezone: "America/New_York" },
                    { code: "SC", name: "South Carolina", timezone: "America/New_York" },
                    { code: "SD", name: "South Dakota", timezone: "America/Chicago" },
                    { code: "TN", name: "Tennessee", timezone: "America/Chicago" },
                    { code: "TX", name: "Texas", timezone: "America/Chicago" },
                    { code: "UT", name: "Utah", timezone: "America/Denver" },
                    { code: "VT", name: "Vermont", timezone: "America/New_York" },
                    { code: "VA", name: "Virginia", timezone: "America/New_York" },
                    { code: "WA", name: "Washington", timezone: "America/Los_Angeles" },
                    { code: "WV", name: "West Virginia", timezone: "America/New_York" },
                    { code: "WI", name: "Wisconsin", timezone: "America/Chicago" },
                    { code: "WY", name: "Wyoming", timezone: "America/Denver" }
                ],
                CA: [
                    { code: "AB", name: "Alberta", timezone: "America/Edmonton" },
                    { code: "BC", name: "British Columbia", timezone: "America/Vancouver" },
                    { code: "MB", name: "Manitoba", timezone: "America/Winnipeg" },
                    { code: "NB", name: "New Brunswick", timezone: "America/Moncton" },
                    { code: "NL", name: "Newfoundland and Labrador", timezone: "America/St_Johns" },
                    { code: "NS", name: "Nova Scotia", timezone: "America/Halifax" },
                    { code: "ON", name: "Ontario", timezone: "America/Toronto" },
                    { code: "PE", name: "Prince Edward Island", timezone: "America/Halifax" },
                    { code: "QC", name: "Quebec", timezone: "America/Montreal" },
                    { code: "SK", name: "Saskatchewan", timezone: "America/Regina" },
                    { code: "NT", name: "Northwest Territories", timezone: "America/Yellowknife" },
                    { code: "NU", name: "Nunavut", timezone: "America/Iqaluit" },
                    { code: "YT", name: "Yukon", timezone: "America/Whitehorse" }
                ],
                UK: [
                    { code: "ENG", name: "England", timezone: "Europe/London" },
                    { code: "SCT", name: "Scotland", timezone: "Europe/London" },
                    { code: "WLS", name: "Wales", timezone: "Europe/London" },
                    { code: "NIR", name: "Northern Ireland", timezone: "Europe/London" }
                ],
                NZ: [
                    { code: "AUK", name: "Auckland", timezone: "Pacific/Auckland" },
                    { code: "BOP", name: "Bay of Plenty", timezone: "Pacific/Auckland" },
                    { code: "CAN", name: "Canterbury", timezone: "Pacific/Auckland" },
                    { code: "CIT", name: "Chatham Islands Territory", timezone: "Pacific/Chatham" },
                    { code: "GIS", name: "Gisborne", timezone: "Pacific/Auckland" },
                    { code: "HKB", name: "Hawke's Bay", timezone: "Pacific/Auckland" },
                    { code: "MBH", name: "Marlborough", timezone: "Pacific/Auckland" },
                    { code: "MWT", name: "Manawatu-Wanganui", timezone: "Pacific/Auckland" },
                    { code: "NSN", name: "Nelson", timezone: "Pacific/Auckland" },
                    { code: "NTL", name: "Northland", timezone: "Pacific/Auckland" },
                    { code: "OTA", name: "Otago", timezone: "Pacific/Auckland" },
                    { code: "STL", name: "Southland", timezone: "Pacific/Auckland" },
                    { code: "TAS", name: "Tasman", timezone: "Pacific/Auckland" },
                    { code: "TKI", name: "Taranaki", timezone: "Pacific/Auckland" },
                    { code: "WGN", name: "Wellington", timezone: "Pacific/Auckland" },
                    { code: "WKO", name: "Waikato", timezone: "Pacific/Auckland" }
                ],
                TH: [
                    { code: "BKK", name: "Bangkok", timezone: "Asia/Bangkok" }
                ]
            };





    });//ready

    function nextStep(step) {
        $('.step-section').removeClass('active');
        $('#step-' + step).addClass('active');
    }

    function prevStep(step) {
        $('.step-section').removeClass('active');
        $('#step-' + step).addClass('active');
    }

    function validateStep1() {
        if (shop_type.val() === '') {
            $('#alertShopType').show();
            shop_type.focus();
        } else if (country.val() === '') {
            $('#alertShopType').hide();
            $('#alertCountry').show();
            country.focus();
        } else if (state.val() === '') {
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').show();
            state.focus();
        } else if ($('#timezone').val() === '') {
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            alert('Please Select Timezone.');
            $('#timezone').focus();
        } else {
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            nextStep(3);
        }
    }

    function validateStep3() {
        if (sales.val() === '') {
            $('#alertSale').show();
            sales.focus();
        } else if (bookBy.val() === '') {
            $('#alertSale').hide();
            $('#alertBooking').show();
            bookBy.focus();
        } else if (presentation.val() === '') {
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').show();
            presentation.focus();
        } else if (date.val() === '') {
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
            alert('Please Select appointment date.');
            date.focus();
        } else if (time.val() === '') {
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
            alert('Please Select appointment time.');
            time.focus();
        } else {
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
            nextStep(8);
        }
    }

    function sendEmail() {



        const selectedSale = $("#sales option:selected");
        const selectedBookBy = $("#bookBy option:selected");
        sendEmailPayload = {
            "staff_id": sales.val(),
            "staff_email": getStaffEmail(sales.val()),
            "book_by": getStaffEmail(bookBy.val()),
            "staff_name": selectedSale.text(),
            "staff_nickname": getNickname(selectedSale.text()),
            "book_by_name": selectedBookBy.text(),
            "book_by_nickname": getNickname(selectedBookBy.text()),
            "created_by": "1",
            "shop_type_id": shop_type.val(),
            "shop_type": $("#shop_type option:selected").text(),
            "country": country.val(),
            "timezone": $('#timezone').val(),
            "city": city.val(),
            "daythaionly" : thaiDayPreview,
            "timethaionly": thaiTimePreview,
            "end_timethaionly": addMinutes(thaiTimePreview,15),
            "state": state.val(),
            "date": date.val(),
            "time": time.val(),
            "customer_name": customer_name.val(),
            "shop_name": shop_name.val(),
            "contact_email": contact_email.val(),
            "contact_phone": $('#contact_phone').data('formatted') || contact_phone.val(),
            "contact_mobile": $('#contact_mobile').data('formatted') || contact_mobile.val(),
            "presentation": presentation.val(),
            "line_id": line_id.val(),
            "whatsapp": whatsapp.val(),
            "address": address.val(),
            "comment": comment.val(),
            "timetodaynow": timeToDayNow.val(),
            "formVersion": "1.2.0"
        };

        console.log("we call webhook UCanBookMe - email alert");
        console.log(sendEmailPayload);
        console.table(sendEmailPayload);

         const sendEmail = $.ajax({
             type: "POST",
             crossDomain: true,
             dataType: 'json',
             url: "https://hook.us1.make.com/yk8yef5sm9m5y4gr8qfj71ynmsorhz7d",
             data: sendEmailPayload
         });

         sendEmail.done(function (res) {
             console.log("send Email done");
             console.log("return = ",res);
         });

         sendEmail.fail(function(xhr, status, error) {
             console.log("ajax webhook fail!!");
             console.log(status + ': ' + error);
             //alert("Send fail!!");
         });
    }//sendEmail

    //ส่งจองลงปฏิทิน
    function bookCalendar() {

        const dtStamp = toUTCFormat(date.val(), time.val(),0);
        const dtStart = toUTCFormat(date.val(), time.val(),0);
        const dtEnd = toUTCFormat(date.val(), addMinutes(time.val(),15),0);
        const timezone = $('#timezone').val();

        const dtStartNoZ = dtStart.replace(/Z$/,'');
        const dtEndNoZ = dtEnd.replace(/Z$/,'');

        const dtStartReal = timezone + ":" + dtStartNoZ;
        const dtEndReal = timezone + ":" + dtEndNoZ;

        const selectedSale = $("#sales option:selected");
        const selectedBookBy = $("#bookBy option:selected");
        appointmentDetail = {
            "staff_email": getStaffEmail(sales.val()),
            "staff_name": selectedSale.text(),
            "staff_nickname": getNickname(selectedSale.text()),
            "book_by": getStaffEmail(bookBy.val()),
            "book_by_name": selectedBookBy.text(),
            "book_by_nickname": getNickname(selectedBookBy.text()),
            "shop_type": $("#shop_type option:selected").text(),
            "country": country.val(),
            "timezone": $('#timezone').val(),
            "daythaionly" : thaiDayPreview,
            "timethaionly": thaiTimePreview,
            "end_timethaionly": addMinutes(thaiTimePreview,15),
            "city": city.val(),
            "startDate": date.val(),
            "startTime": time.val(),
            "endDate": date.val(),
            "endTime": addMinutes(time.val(),15),
            "dtStamp": toUTCFormat(date.val(), time.val(),0),
            "dtStart": dtStartReal,
            "dtEnd": dtEndReal,
            "customer_name": customer_name.val(),
            "shop_name": shop_name.val(),
            "contact_email": contact_email.val(),
            "contact_phone": $('#contact_phone').data('formatted') || contact_phone.val(),
            "contact_mobile": $('#contact_mobile').data('formatted') || contact_mobile.val(),
            "presentation": presentation.val(),
            "line_id": line_id.val(),
            "whatsapp": whatsapp.val(),
            "address": address.val(),
            "comment": comment.val(),
            "timetodaynow": timeToDayNow.val(),
            "formVersion": "1.2.0"
        };

        console.log("dtStamp = ",dtStamp);
        console.log("dtStart = ",dtStart);
        console.log("dtEnd = ",dtEnd);

        console.log("we call webhook UCanBookMe - Appointment created");
        console.table(appointmentDetail);
        const makeAppointment = $.ajax({
            type: "POST",
            crossDomain: true,
            dataType: 'json',
            url: "https://hook.us1.make.com/hg8rqnmfuxry86lq4ylr967j9uifhlt1",
            data: appointmentDetail
        });

        makeAppointment.done(function (res) {
            console.log("make Appointment done");
            console.log("return = ",res);
        });

        makeAppointment.fail(function(xhr, status, error) {
            console.log("make Appointment webhook fail!!");
            console.log(status + ': ' + error);
            //alert("Send fail!!");
        });
    }//bookCalendar


    function getStaffEmail(id) {
        const map = {
            1: 'neung@localforyou.com',
            17: 'boom@localforyou.com',
            24: 'honey@localforyou.com',
            35: 'pluem@localforyou.com',
            38: 'pruek@localforyou.com',
            62: 'ball@localforyou.com',
            79: 'gun@localforyou.com',
            84: 'aon@localforyou.com',
            85: 'mild.th@localforyou.com',
            86: 'jiw@localforyou.com',
            90: 'foo.si@localforyou.com'
        };

        return map[id] || 'administrator@localforyou.com';
    }

    function getNickname(fullName) {
        if (!fullName) return '';
        return fullName.trim().split(' ')[0];
    }

    function toUTCFormat(dateStr, timeStr, offsetHours = 0) {
        const [year, month, day] = dateStr.split('-').map(Number);
        const [hour, minute, second] = timeStr.split(':').map(Number);

        const utcMillis = Date.UTC(year, month - 1, day, hour - offsetHours, minute, second || 0);
        const utcDate = new Date(utcMillis);

        const y = utcDate.getUTCFullYear();
        const m = String(utcDate.getUTCMonth() + 1).padStart(2, '0');
        const d = String(utcDate.getUTCDate()).padStart(2, '0');
        const h = String(utcDate.getUTCHours()).padStart(2, '0');
        const min = String(utcDate.getUTCMinutes()).padStart(2, '0');
        const s = String(utcDate.getUTCSeconds()).padStart(2, '0');

        return `${y}${m}${d}T${h}${min}${s}Z`;
    }

    function addMinutes(timeStr, minutesToAdd) {
        const [hour, minute, second] = timeStr.split(':').map(Number);
        const date = new Date(0, 0, 0, hour, minute, second || 0);
        date.setMinutes(date.getMinutes() + minutesToAdd);

        const h = String(date.getHours()).padStart(2, '0');
        const m = String(date.getMinutes()).padStart(2, '0');
        const s = String(date.getSeconds()).padStart(2, '0');

        return `${h}:${m}:${s}`;
    }

    const { DateTime } = luxon;

    const countryToTimezone = {
/*        AU: "Australia/Sydney",
        NZ: "Pacific/Auckland",
        US: "America/New_York",
        UK: "Europe/London",
        CA: "America/Toronto",
        TH: "Asia/Bangkok"*/
        AU: [
            { code: "NSW", name: "New South Wales", timezone: "Australia/Sydney" },
            { code: "VIC", name: "Victoria", timezone: "Australia/Melbourne" },
            { code: "QLD", name: "Queensland", timezone: "Australia/Brisbane" },
            { code: "SA", name: "South Australia", timezone: "Australia/Adelaide" },
            { code: "WA", name: "Western Australia", timezone: "Australia/Perth" },
            { code: "TAS", name: "Tasmania", timezone: "Australia/Hobart" },
            { code: "NT", name: "Northern Territory", timezone: "Australia/Darwin" },
            { code: "ACT", name: "Australian Capital Territory", timezone: "Australia/Sydney" } // ACT ใช้ timezone Sydney
        ],
        US: [
            { code: "AL", name: "Alabama", timezone: "America/Chicago" },
            { code: "AK", name: "Alaska", timezone: "America/Anchorage" },
            { code: "AZ", name: "Arizona", timezone: "America/Phoenix" },
            { code: "AR", name: "Arkansas", timezone: "America/Chicago" },
            { code: "CA", name: "California", timezone: "America/Los_Angeles" },
            { code: "CO", name: "Colorado", timezone: "America/Denver" },
            { code: "CT", name: "Connecticut", timezone: "America/New_York" },
            { code: "DE", name: "Delaware", timezone: "America/New_York" },
            { code: "FL", name: "Florida", timezone: "America/New_York" },
            { code: "GA", name: "Georgia", timezone: "America/New_York" },
            { code: "HI", name: "Hawaii", timezone: "Pacific/Honolulu" },
            { code: "ID", name: "Idaho", timezone: "America/Denver" },
            { code: "IL", name: "Illinois", timezone: "America/Chicago" },
            { code: "IN", name: "Indiana", timezone: "America/Indiana/Indianapolis" },
            { code: "IA", name: "Iowa", timezone: "America/Chicago" },
            { code: "KS", name: "Kansas", timezone: "America/Chicago" },
            { code: "KY", name: "Kentucky", timezone: "America/New_York" },
            { code: "LA", name: "Louisiana", timezone: "America/Chicago" },
            { code: "ME", name: "Maine", timezone: "America/New_York" },
            { code: "MD", name: "Maryland", timezone: "America/New_York" },
            { code: "MA", name: "Massachusetts", timezone: "America/New_York" },
            { code: "MI", name: "Michigan", timezone: "America/Detroit" },
            { code: "MN", name: "Minnesota", timezone: "America/Chicago" },
            { code: "MS", name: "Mississippi", timezone: "America/Chicago" },
            { code: "MO", name: "Missouri", timezone: "America/Chicago" },
            { code: "MT", name: "Montana", timezone: "America/Denver" },
            { code: "NE", name: "Nebraska", timezone: "America/Chicago" },
            { code: "NV", name: "Nevada", timezone: "America/Los_Angeles" },
            { code: "NH", name: "New Hampshire", timezone: "America/New_York" },
            { code: "NJ", name: "New Jersey", timezone: "America/New_York" },
            { code: "NM", name: "New Mexico", timezone: "America/Denver" },
            { code: "NY", name: "New York", timezone: "America/New_York" },
            { code: "NC", name: "North Carolina", timezone: "America/New_York" },
            { code: "ND", name: "North Dakota", timezone: "America/Chicago" },
            { code: "OH", name: "Ohio", timezone: "America/New_York" },
            { code: "OK", name: "Oklahoma", timezone: "America/Chicago" },
            { code: "OR", name: "Oregon", timezone: "America/Los_Angeles" },
            { code: "PA", name: "Pennsylvania", timezone: "America/New_York" },
            { code: "RI", name: "Rhode Island", timezone: "America/New_York" },
            { code: "SC", name: "South Carolina", timezone: "America/New_York" },
            { code: "SD", name: "South Dakota", timezone: "America/Chicago" },
            { code: "TN", name: "Tennessee", timezone: "America/Chicago" },
            { code: "TX", name: "Texas", timezone: "America/Chicago" },
            { code: "UT", name: "Utah", timezone: "America/Denver" },
            { code: "VT", name: "Vermont", timezone: "America/New_York" },
            { code: "VA", name: "Virginia", timezone: "America/New_York" },
            { code: "WA", name: "Washington", timezone: "America/Los_Angeles" },
            { code: "WV", name: "West Virginia", timezone: "America/New_York" },
            { code: "WI", name: "Wisconsin", timezone: "America/Chicago" },
            { code: "WY", name: "Wyoming", timezone: "America/Denver" }
        ],
        CA: [
            { code: "AB", name: "Alberta", timezone: "America/Edmonton" },
            { code: "BC", name: "British Columbia", timezone: "America/Vancouver" },
            { code: "MB", name: "Manitoba", timezone: "America/Winnipeg" },
            { code: "NB", name: "New Brunswick", timezone: "America/Moncton" },
            { code: "NL", name: "Newfoundland and Labrador", timezone: "America/St_Johns" },
            { code: "NS", name: "Nova Scotia", timezone: "America/Halifax" },
            { code: "ON", name: "Ontario", timezone: "America/Toronto" },
            { code: "PE", name: "Prince Edward Island", timezone: "America/Halifax" },
            { code: "QC", name: "Quebec", timezone: "America/Montreal" },
            { code: "SK", name: "Saskatchewan", timezone: "America/Regina" },
            { code: "NT", name: "Northwest Territories", timezone: "America/Yellowknife" },
            { code: "NU", name: "Nunavut", timezone: "America/Iqaluit" },
            { code: "YT", name: "Yukon", timezone: "America/Whitehorse" }
        ],
        UK: [
            { code: "ENG", name: "England", timezone: "Europe/London" },
            { code: "SCT", name: "Scotland", timezone: "Europe/London" },
            { code: "WLS", name: "Wales", timezone: "Europe/London" },
            { code: "NIR", name: "Northern Ireland", timezone: "Europe/London" }
        ],
        NZ: [
            { code: "AUK", name: "Auckland", timezone: "Pacific/Auckland" },
            { code: "BOP", name: "Bay of Plenty", timezone: "Pacific/Auckland" },
            { code: "CAN", name: "Canterbury", timezone: "Pacific/Auckland" },
            { code: "CIT", name: "Chatham Islands Territory", timezone: "Pacific/Chatham" },
            { code: "GIS", name: "Gisborne", timezone: "Pacific/Auckland" },
            { code: "HKB", name: "Hawke's Bay", timezone: "Pacific/Auckland" },
            { code: "MBH", name: "Marlborough", timezone: "Pacific/Auckland" },
            { code: "MWT", name: "Manawatu-Wanganui", timezone: "Pacific/Auckland" },
            { code: "NSN", name: "Nelson", timezone: "Pacific/Auckland" },
            { code: "NTL", name: "Northland", timezone: "Pacific/Auckland" },
            { code: "OTA", name: "Otago", timezone: "Pacific/Auckland" },
            { code: "STL", name: "Southland", timezone: "Pacific/Auckland" },
            { code: "TAS", name: "Tasman", timezone: "Pacific/Auckland" },
            { code: "TKI", name: "Taranaki", timezone: "Pacific/Auckland" },
            { code: "WGN", name: "Wellington", timezone: "Pacific/Auckland" },
            { code: "WKO", name: "Waikato", timezone: "Pacific/Auckland" }
        ],
        TH: [
            { code: "BKK", name: "Bangkok", timezone: "Asia/Bangkok" }
        ]
    };

/*    function updateThaiTimePreview() {
        const selectedCountry = country.val();
        const selectedState = state.val();
        const selectedDate = date.val();
        const selectedTime = time.val();
        const thTimePreview = $('#thTimePreview');

        if (!selectedCountry || !selectedDate || !selectedTime) {
            thTimePreview.text('');
            return;
        }

        /!*const timezone = countryToTimezone[selectedCountry] || 'Asia/Bangkok';*!/
        const timezone = countryToTimezone[selectedState] || 'Asia/Bangkok';
        console.log("selectedCountry = ", selectedCountry);
        console.log("selectedState = ", selectedState);
        const dateTimeInCustomerTZ = DateTime.fromISO(`${selectedDate}T${selectedTime}`, { zone: timezone });
        const dateTimeInThaiTZ = dateTimeInCustomerTZ.setZone('Asia/Bangkok');

        const thaiFormatted = dateTimeInThaiTZ.toFormat("HH:mm (ccc dd MMM)");

        thTimePreview.text(`⏰ BKK: ${thaiFormatted}`);
    }*/

    function updateThaiTimePreview() {
        const selectedCountry = country.val();
        const selectedStateCode = state.val();
        const selectedDate = date.val();
        const selectedTime = time.val();
        const thTimePreview = $('#thTimePreview');

        if (!selectedCountry || !selectedStateCode || !selectedDate || !selectedTime) {
            thTimePreview.text('');
            return;
        }

        let timezone = 'Asia/Bangkok';

        if (countryToTimezone[selectedCountry]) {
            const selectedState = countryToTimezone[selectedCountry].find(s => s.code === selectedStateCode);
            if (selectedState && selectedState.timezone) {
                timezone = selectedState.timezone;
            }
        }

        const dateTimeInCustomerTZ = DateTime.fromISO(`${selectedDate}T${selectedTime}`, { zone: timezone });

        // บางครั้ง dateTimeInCustomerTZ อาจ Invalid ถ้า timezone ผิด
        if (!dateTimeInCustomerTZ.isValid) {
            thTimePreview.text('⚠️ Invalid Date/Time');
            return;
        }

        const dateTimeInThaiTZ = dateTimeInCustomerTZ.setZone('Asia/Bangkok');
        const thaiFormatted = dateTimeInThaiTZ.toFormat("HH:mm (ccc dd MMM)");
        const onlyNumberTimeThai = dateTimeInThaiTZ.toFormat("HH:mm:ss");
        const onlyNumberDayThai = dateTimeInThaiTZ.toFormat("yyyy-MM-dd");

        thaiTimePreview = onlyNumberTimeThai;
        thaiDayPreview = onlyNumberDayThai;



        thTimePreview.text(`⏰ BKK: ${thaiFormatted}`);
        console.log('TimePreview' , thaiTimePreview);
    }


    function showReview() {

        let emailRegex = /^([a-zA-Z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?)$/;

        if (shop_type.val() === '') {
            $('#alertShopType').show();
            shop_type.focus();
            nextStep(1);
        }else if (country.val() === ''){
            $('#alertCountry').show();
            country.focus();
            nextStep(1);
            $('#alertShopType').hide();
        }else if (state.val() === ''){
            $('#alertState').show();
            state.focus();
            nextStep(1);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
        }else if (sales.val() === ''){
            $('#alertSale').show();
            sales.focus();
            nextStep(3);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
        }else if (bookBy.val() === ''){
            $('#alertBooking').show();
            bookBy.focus();
            nextStep(3);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            $('#alertSale').hide();
        }else if (presentation.val() === ''){
            $('#alertLanguage').show();
            presentation.focus();
            nextStep(3);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            $('#alertSale').hide();
            $('#alertBooking').hide();
        }else if (shop_name.val() === ''){
            $('#alertShopName').show();
            presentation.focus();
            nextStep(8);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
        }else if (customer_name.val() === ''){
            $('#alertCustomerName').show();
            customer_name.focus();
            nextStep(8);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
            $('#alertShopName').hide();
        }else if (contact_email.val() === ''){
            $('#alertCustomerEmail').show();
            contact_email.focus();
            nextStep(8);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
            $('#alertShopName').hide();
            $('#alertCustomerName').hide();
        }else if (!emailRegex.test(contact_email.val())){
            $('#alertCustomerEmailValid').show();
            contact_email.focus();
            nextStep(8);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
            $('#alertShopName').hide();
            $('#alertCustomerName').hide();
            $('#alertCustomerEmail').hide();
        }else if (contact_phone.val() === ''){
            $('#alertCustomerPhone').show();
            contact_phone.focus();
            nextStep(8);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
            $('#alertShopName').hide();
            $('#alertCustomerName').hide();
            $('#alertCustomerEmail').hide();
            $('#alertCustomerEmailValid').hide();
        }else if (contact_phone.val().length < 10){
            $('#alertCustomerPhoneComplete').show();
            contact_phone.focus();
            nextStep(8);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
            $('#alertShopName').hide();
            $('#alertCustomerName').hide();
            $('#alertCustomerEmail').hide();
            $('#alertCustomerEmailValid').hide();
            $('#alertCustomerPhone').hide();
        }else {

            const data = {
                "Shop Type": $("#shop_type option:selected").text(),
                "Country": $("#country option:selected").text(),
                "City": city.val() || '-',
                "State": state.val() || '-',
                "Salesperson": $("#sales option:selected").text() || '-',
                "Booking By": $("#bookBy option:selected").text() || '-',
                "Presentation": $("#presentation option:selected").text(),
                "Date": date.val(),
                "Time": `(${country.val()}) ${time.val().substring(0, 5)} = ${getThaiTimeText(date.val(), time.val())}`,
                "Shop Name": shop_name.val() || '-',
                "Customer Name": customer_name.val() || '-',
                "Email": contact_email.val() || '-',
                "Phone": contact_phone.val() || '-',
                "Mobile": contact_mobile.val() || '-',
                "Line ID": line_id.val() || '-',
                "WhatsApp": whatsapp.val() || '-',
                "Address": address.val() || '-',
                "Comment": comment.val() || '-'
            };

            const iconMap = {
                "Shop Type": "bi-shop",
                "Country": "bi-flag",
                "City": "bi-geo",
                "State": "bi-geo-alt-fill",
                "Salesperson": "bi-person",
                "Booking By": "bi bi-journal-bookmark-fill",
                "Presentation": "bi bi-translate",
                "Date": "bi-calendar",
                "Time": "bi-clock",
                "Shop Name": "bi-building",
                "Customer Name": "bi-person-circle",
                "Email": "bi-envelope",
                "Phone": "bi-telephone",
                "Mobile": "bi-phone",
                "Line ID": "bi-chat-dots",
                "WhatsApp": "bi-whatsapp",
                "Address": "bi-geo-alt-fill",
                "Comment": "bi bi-chat-right-text"
            };

            let html = '';
            for (const key in data) {
                html += `
            <div class="col">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title text-primary">
                            <i class="bi ${iconMap[key]} me-2"></i>${key}
                        </h6>
                        <p class="card-text mb-0">${data[key]}</p>
                    </div>
                </div>
            </div>`;
            }

            $('#reviewSection').html(html);

            nextStep(9);
        }


    }

    function submitBooking() {

        $('#bookingForm').submit();

    }

    function getThaiTimeText(dateStr, timeStr) {
        try {
            const selectedZone = $('#timezone').val() || 'Asia/Bangkok';
            const local = luxon.DateTime.fromISO(`${dateStr}T${timeStr}`, { zone: selectedZone });
            const thai = local.setZone('Asia/Bangkok');
            return `(TH) : ${thai.toFormat('HH:mm')}`;
        } catch (e) {
            return '-';
        }
    }
</script>
</body>
</html>
