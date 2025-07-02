<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;
$currentPage = basename($_SERVER['PHP_SELF']);
$tomorrow = date("Y-m-d", strtotime("+1 day"));
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
                        <label for="shop_type" class="form-label">1. Select your shop type <span class="red">*</span></label>
                        <div class="d-flex flex-row gap-3">
                            <select id="shop_type" name="shop_type" class="form-control form-select mb-3" >
                                <option>--Please Select--</option>
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

                <label for="country">2. Select your country <span class="red">*</span></label>
                <div class="d-flex flex-row gap-3">
                    <select id="country" name="country" class="form-select mb-3" >
                        <option>--Please Select--</option>
                        <option value="AU">Australia</option>
                        <option value="NZ">New Zealand</option>
                        <option value="US">United States</option>
                        <option value="UK">United Kingdom</option>
                        <option value="CA">Canada</option>
                        <option value="TH">Thailand</option>
                    </select>
                    Timezone: <span id="timeZone" class="text-primary timeZone">-</span>
                </div>
                <div class="d-flex flex-column justify-content-start align-items-start mb-3">
                    <div class="mt-2">
                        <label for="city">City</label>
                        <input aria-label="none" type="text" id="city" name="city" class="form-control" placeholder="City name (optional)"/>
                    </div>
                </div>
                <div class="d-flex flex-row gap-3">
                    <button type="button" class="btn btn-primary" onclick="nextStep(3)">Next <i class="bi bi-arrow-right-short"></i></button>
                </div>
            </div>

            <!-- Step 3: Select Sales -->
            <div class="step-section" id="step-3">
                <div class="row">
                    <div class="col-6">
                        <label for="sales">3. Select salesperson</label>
                        <div class="d-flex flex-row gap-3 mb-3">
                            <select id="sales" name="sales" class="form-select mb-3" >
                                <option></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-4">
                    <label for="date">4. Select appointment date</label>
                    <div class="d-flex flex-row gap-3 mb-3">
                        <input type="text" id="date" name="date" value="<?php echo $tomorrow; ?>" class="form-control mb-3" />
                    </div>
                </div>

                <div class="col-8">
                    <label for="time">5. Available time <small class="text-muted">(<span id="timeZone" class="text-primary timeZone">-</span>)</small></label>
                    <div class="d-flex flex-column gap-3 mb-3">
                        <select id="time" name="time" class="form-select mb-3" >
                            <option></option>
                        </select>
                        <small class="text-muted small" id="thTimePreview"></small>
                    </div>
                </div>
                <div class="d-flex flex-row gap-3 mb-3">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(1)"><i class="bi bi-arrow-left-short"></i> Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextStep(6)">Next <i class="bi bi-arrow-right-short"></i></button>
                </div>
            </div>

            <!-- Step 6: Customer Info -->
            <div class="step-section" id="step-6">
                <label>6. Customer information</label>
                    <div class="col-4">
                        <label for="shop_name">Shop name</label>
                            <input type="text" id="shop_name" name="shop_name" class="form-control mb-2" autocomplete="off" placeholder="fill your shop name" >
                    </div>
                    <div class="col-4">
                        <label for="customer_name">Customer name</label>
                            <input type="text" id="customer_name" name="customer_name" class="form-control mb-2" autocomplete="off" placeholder="fill customer name" >
                    </div>
                    <div class="col-4">
                        <label for="contact_email">Contact email</label>
                            <input type="email" id="contact_email" name="contact_email" class="form-control mb-2" autocomplete="off" placeholder="fill contact email" >
                    </div>
                    <div class="col-4">
                        <label for="contact_phone">Contact phone</label>
                            <input type="text" id="contact_phone" name="contact_phone" class="form-control mb-2" autocomplete="off" placeholder="fill contact phone" >
                    </div>
                    <div class="col-4">
                        <label for="line_id">Line ID</label>
                            <input type="text" id="line_id" name="line_id" class="form-control mb-2" autocomplete="off" placeholder="LINE ID">
                    </div>
                    <div class="col-4">
                        <label for="whatsapp">WhatsApp</label>
                            <input type="text" id="whatsapp" name="whatsapp" class="form-control mb-2" autocomplete="off" placeholder="WhatsApp">
                    </div>
                    <div class="col-4 mb-3">
                        <label for="address">Address</label>
                        <textarea class="form-control" name="address" id="address" rows="4" placeholder="Address"></textarea>
                    </div>
                <div class="d-flex flex-row gap-3 mb-3">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(3)"><i class="bi bi-arrow-left-short"></i> Previous</button>
                    <button type="button" class="btn btn-primary" onclick="showReview()">Next <i class="bi bi-arrow-right-short"></i></button>
                </div>
            </div>

            <!-- Step 7: Review Info -->
            <div class="step-section" id="step-7">
                <label>7. Review your booking</label>
                <div id="reviewSection" class="row row-cols-1 row-cols-md-2 g-3 mb-3 p-5">
                    <!-- Filled dynamically by JS -->
                </div>
                <div class="d-flex flex-row gap-3 mb-3">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(6)"><i class="bi bi-arrow-left-short"></i> Previous</button>
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
    const shop_type = $('#shop_type');
    const country = $('#country');
    const city = $('#city');
    const timeZone = $('.timeZone');
    const sales = $('#sales');
    const date = $('#date');
    const time = $('#time');
    const shop_name = $('#shop_name');
    const customer_name = $('#customer_name');
    const contact_email = $('#contact_email');
    const contact_phone = $('#contact_phone');
    const line_id = $('#line_id');
    const whatsapp = $('#whatsapp');
    const address = $('#address');

    let appointmentDetail = {};
    let sendEmailPayload = {};

    const timeZoneMap = {
        "AU": "Australia/Sydney",
        "NZ": "Pacific/Auckland",
        "US": "America/New_York",
        "UK": "Europe/London",
        "CA": "America/Toronto",
        "TH": "Asia/Bangkok"
    };

    $(document).ready(function () {
        //$('#shop_type').select2({placeholder: 'Select your store type',theme: 'bootstrap-5'});
        //country.select2({placeholder: 'Select country',theme: 'bootstrap-5'});
        $('#sales').select2({placeholder: 'Select salesperson',theme: 'bootstrap-5'});
        $('#time').select2({placeholder: 'Select appointment time',theme: 'bootstrap-5'});

        date.flatpickr({
            minDate: new Date().fp_incr(1),
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

        const timezones = {
            AU: "Australia/Sydney",
            NZ: "Pacific/Auckland",
            US: "America/New_York",
            UK: "Europe/London",
            CA: "America/Toronto",
            TH: "Asia/Bangkok"
        };
        country.on('change', function () {
            const tz = timezones[country.val()] || '';
            timeZone.text(tz);
        });

        // ✅ Step 3: โหลดเซลทั้งหมดในทีม
        $.get('../models/load_all_sales.php', function (res) {
            if (res.status === 'ok') {
                let options = res.data.map(user => new Option(user.text, user.id, false, false));
                $('#sales').append(options).trigger('change');
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

    });//ready

    function nextStep(step) {

        $('.step-section').removeClass('active');
        $('#step-' + step).addClass('active');
    }

    function prevStep(step) {
        $('.step-section').removeClass('active');
        $('#step-' + step).addClass('active');
    }

    function sendEmail() {
        sendEmailPayload = {
            "staff_id": sales.val(),
            "staff_email": getStaffEmail(sales.val()),
            "staff_name": $("#sales option:selected").text(),
            "staff_nickname": getNickname($("#sales option:selected").text()),
            "created_by": "1",
            "shop_type_id": shop_type.val(),
            "shop_type": $("#shop_type option:selected").text(),
            "country": country.val(),
            "city": city.val(),
            "date": date.val(),
            "time": time.val(),
            "customer_name": customer_name.val(),
            "shop_name": shop_name.val(),
            "contact_email": contact_email.val(),
            "contact_phone": contact_phone.val(),
            "line_id": line_id.val(),
            "whatsapp": whatsapp.val(),
            "address": address.val(),
            "formVersion": "1.0.0"
        };

        console.log("we call webhook UCanBookMe - email alert");
        console.log(sendEmailPayload);

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

    function bookCalendar() {
        appointmentDetail = {
            "staff_email": getStaffEmail(sales.val()),
            "staff_name": $("#sales option:selected").text(),
            "staff_nickname": getNickname($("#sales option:selected").text()),
            "shop_type": $("#shop_type option:selected").text(),
            "country": country.val(),
            "city": city.val(),
            "startDate": date.val(),
            "startTime": time.val(),
            "endDate": date.val(),
            "endTime": addMinutes(time.val(),15),
            "dtStamp": toUTCFormat(date.val(), time.val(),0),
            "dtStart": toUTCFormat(date.val(), time.val(),0),
            "dtEnd": toUTCFormat(date.val(), addMinutes(time.val(),15),0),
            "customer_name": customer_name.val(),
            "shop_name": shop_name.val(),
            "contact_email": contact_email.val(),
            "contact_phone": contact_phone.val(),
            "line_id": line_id.val(),
            "whatsapp": whatsapp.val(),
            "address": address.val(),
            "formVersion": "1.0.0"
        };

        console.log("we call webhook UCanBookMe - Appointment created");
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
        AU: "Australia/Sydney",
        NZ: "Pacific/Auckland",
        US: "America/New_York",
        UK: "Europe/London",
        CA: "America/Toronto",
        TH: "Asia/Bangkok"
    };

    function updateThaiTimePreview() {
        const selectedCountry = country.val();
        const selectedDate = date.val();
        const selectedTime = time.val();

        if (!selectedCountry || !selectedDate || !selectedTime) {
            $('#thTimePreview').text('');
            return;
        }

        const timezone = countryToTimezone[selectedCountry] || 'Asia/Bangkok';

        const dateTimeInCustomerTZ = DateTime.fromISO(`${selectedDate}T${selectedTime}`, { zone: timezone });
        const dateTimeInThaiTZ = dateTimeInCustomerTZ.setZone('Asia/Bangkok');

        const thaiFormatted = dateTimeInThaiTZ.toFormat("HH:mm (ccc dd MMM)");

        $('#thTimePreview').text(`⏰ BKK: ${thaiFormatted}`);
    }

    function showReview() {
        const data = {
            "Shop Type": $("#shop_type option:selected").text(),
            "Country": $("#country option:selected").text(),
            "City": city.val() || '-',
            "Salesperson": $("#sales option:selected").text(),
            "Date": date.val(),
            "Time": `(${country.val()}) ${time.val().substring(0, 5)} = ${getThaiTimeText(date.val(), time.val(), country.val())}`,
            "Shop Name": shop_name.val(),
            "Customer Name": customer_name.val(),
            "Email": contact_email.val(),
            "Phone": contact_phone.val(),
            "Line ID": line_id.val() || '-',
            "WhatsApp": whatsapp.val() || '-',
            "Address": address.val() || '-'
        };

        const iconMap = {
            "Shop Type": "bi-shop",
            "Country": "bi-flag",
            "City": "bi-geo",
            "Salesperson": "bi-person",
            "Date": "bi-calendar",
            "Time": "bi-clock",
            "Shop Name": "bi-building",
            "Customer Name": "bi-person-circle",
            "Email": "bi-envelope",
            "Phone": "bi-telephone",
            "Line ID": "bi-chat-dots",
            "WhatsApp": "bi-whatsapp",
            "Address": "bi-geo-alt-fill"
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
        nextStep(7);
    }

    function submitBooking() {
        $('#bookingForm').submit();
    }


    function getThaiTimeText(dateStr, timeStr, countryCode) {
        try {
            const targetZone = timeZoneMap[countryCode] || 'Asia/Bangkok';
            const thaiZone = 'Asia/Bangkok';

            const datetimeStr = `${dateStr}T${timeStr}`;
            const local = luxon.DateTime.fromISO(datetimeStr, { zone: targetZone });
            const thai = local.setZone(thaiZone);

            return `(TH) : ${thai.toFormat('HH:mm')}`;
        } catch (e) {
            return '-';
        }
    }
</script>
</body>
</html>
