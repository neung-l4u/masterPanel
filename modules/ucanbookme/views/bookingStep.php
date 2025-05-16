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
    </style>
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container py-5">
    <header>
        <nav class="mb-4"
             style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);"
             aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">🛖 Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Booking</li>
            </ol>
        </nav>
    </header>

    <main>
        <div class="mb-4">
            <h4 class="text-primary">Local For You - # 1 Marketing Agency for Thai (Internal)</h4>
            <small>Please select the appointment type to make a booking.</small>
        </div>


        <form id="bookingForm">
            <!-- Step 1: Business Type -->
            <div class="step-section active" id="step-1">
                <div class="row">
                    <div class="col">
                        <label for="shop_type" class="form-label">1. Select your shop type</label>
                        <div class="d-flex flex-row gap-3">
                            <select id="shop_type" name="shop_type" class="form-control form-select mb-3" >
                                <option></option>
                                <?php
                                $shopType = $db->query('SELECT * FROM `tb_shopType` WHERE status = ?',1)->fetchAll();
                                foreach ($shopType as $row) {
                                ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                                <?php } ?>
                            </select>
                            <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Country -->
            <div class="step-section" id="step-2">
                <label for="country">2. Select your country</label>
                <div class="d-flex flex-row gap-3 mb-3">
                    <select id="country" name="country" class="form-select mb-3" >
                        <option></option>
                        <option value="AU">Australia</option>
                        <option value="NZ">New Zealand</option>
                        <option value="US">United States</option>
                        <option value="UK">United Kingdom</option>
                        <option value="CA">Canada</option>
                        <option value="TH">Thailand</option>
                    </select>
                    <input aria-label="none" type="text" id="city" name="city" class="form-control mb-3" placeholder="เมือง (ถ้ามี)"/>
                </div>
                <div class="d-flex flex-row gap-3">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(1)">Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextStep(3)">Next</button>
                </div>
            </div>

            <!-- Step 3: Select Sales -->
            <div class="step-section" id="step-3">
                <label for="sales">3. เลือกเซล</label>
                <div class="d-flex flex-row gap-3 mb-3">
                    <select id="sales" name="sales" class="form-select mb-3" >
                        <option></option>
                    </select>
                </div>
                <div class="d-flex flex-row gap-3 mb-3">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(2)">Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextStep(4)">Next</button>
                </div>
            </div>

            <!-- Step 4: Select Date -->
            <div class="step-section" id="step-4">
                <label for="date">4. เลือกวันนัด</label>
                <div class="d-flex flex-row gap-3 mb-3">
                    <input type="text" id="date" name="date" value="<?php echo $tomorrow; ?>" class="form-control mb-3" />
                </div>
                <div class="d-flex flex-row gap-3 mb-3">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(3)">Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextStep(5)">Next</button>
                </div>
            </div>

            <!-- Step 5: Select Time -->
            <div class="step-section" id="step-5">
                <label for="time">5. Available time</label>
                <div class="d-flex flex-row gap-3 mb-3">
                    <select id="time" name="time" class="form-select mb-3" >
                        <option></option>
                    </select>
                </div>
                <div class="d-flex flex-row gap-3 mb-3">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(4)">Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextStep(6)">Next</button>
                </div>
            </div>

            <!-- Step 6: Customer Info -->
            <div class="step-section" id="step-6">
                <label>6. ข้อมูลลูกค้า</label>
                <div class="d-flex flex-column gap-3 mb-3">
                    <label for="shop_name">Shop name</label>
                        <input type="text" id="shop_name" name="shop_name" class="form-control mb-2" autocomplete="off" placeholder="ชื่อร้าน" >
                    <label for="customer_name">Customer name</label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control mb-2" autocomplete="off" placeholder="ชื่อเจ้าของร้าน">
                    <label for="contact_email">Contact email</label>
                        <input type="email" id="contact_email" name="contact_email" class="form-control mb-2" autocomplete="off" placeholder="อีเมล">
                    <label for="contact_phone">Contact phone</label>
                        <input type="text" id="contact_phone" name="contact_phone" class="form-control mb-2" autocomplete="off" placeholder="เบอร์โทร">
                    <label for="line_id">Line ID</label>
                        <input type="text" id="line_id" name="line_id" class="form-control mb-2" autocomplete="off" placeholder="LINE ID">
                    <label for="whatsapp">WhatsApp</label>
                        <input type="text" id="whatsapp" name="whatsapp" class="form-control mb-2" autocomplete="off" placeholder="WhatsApp">
                </div>
                <div class="d-flex flex-row gap-3 mb-3">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(5)">Previous</button>
                    <button type="submit" class="btn btn-success">Book now!!</button>
                    <a href="#" onclick="sendEmail();">Alert</a>
                </div>
            </div>
        </form>
    </main>
</div>

<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../assets/libs/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/select2/js/select2.min.js"></script>
<script src="../assets/libs/flatpickr/flatpickr.js"></script>

<script>
    const shop_type = $('#shop_type');
    const country = $('#country');
    const city = $('#city');
    const sales = $('#sales');
    const date = $('#date');
    const time = $('#time');
    const shop_name = $('#shop_name');
    const customer_name = $('#customer_name');
    const contact_email = $('#contact_email');
    const contact_phone = $('#contact_phone');
    const line_id = $('#line_id');
    const whatsapp = $('#whatsapp');

    let appointmentDetail = {};
    let sendEmailPayload = {};

    $(document).ready(function () {
        $('#shop_type').select2({placeholder: 'เลือกประเภทร้าน',theme: 'bootstrap-5'});
        country.select2({placeholder: 'เลือกประเทศ',theme: 'bootstrap-5'});
        $('#sales').select2({placeholder: 'เลือกเซล',theme: 'bootstrap-5'});
        $('#time').select2({placeholder: 'เลือกเวลานัด',theme: 'bootstrap-5'});
        $('#date').flatpickr({
            minDate: new Date().fp_incr(1),
            maxDate: new Date().fp_incr(7),
            dateFormat: 'Y-m-d',
            disableMobile: true
        });

        country.on('change', function () {
            let country = $(this).val();
            $('#sales').empty().trigger('change');
            if (!country) return;

            $.get('../models/load_sales_by_country.php', { country_code: country }, function (res) {
                if (res.status === 'ok') {
                    let newOptions = res.data.map(user => new Option(user.text, user.id, false, false));
                    $('#sales').append(newOptions).trigger('change');
                    //$("#country").val($("#country option:first").val());
                }
            });
        });//country change


        $('#date, #sales').on('change', function () {
            let staff_id = $('#sales').val();
            let date = $('#date').val();
            $('#time').empty().trigger('change');

            if (!staff_id || !date) return;

            $.get('../models/load_available_times.php', { staff_id, date }, function (res) {
                if (res.status === 'ok') {
                    let options = res.data.map(t => new Option(t.text, t.id, false, false));
                    $('#time').append(options).trigger('change');
                }
            });
        });//date, sale change

        $('#bookingForm').on('submit', function (e) {
            e.preventDefault();
            $.post('../models/save_appointment.php', $(this).serialize(), function (res) {
                if (res.status === 'ok') {
                    sendEmail();
                    bookCalendar();
                    alert('✅ จองนัดเรียบร้อย');

                    //location.href = 'booking_success.php';
                } else {
                    alert('❌ ' + res.message);
                }
            });

        });//form submit

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
             alert("Send fail!!");
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
            alert("Send fail!!");
        });
    }//bookCalendar


    function getStaffEmail(id) {
        /*const map = {
            17: 'boom@localforyou.com',
            18: 'dear@localforyou.com',
            24: 'honey@localforyou.com',
            35: 'pluem@localforyou.com',
            38: 'pruek@localforyou.com',
            47: 'toffee@localforyou.com',
            62: 'ball@localforyou.com',
            72: 'lani@localforyou.com',
            76: 'naya@localforyou.com',
            79: 'gun@localforyou.com',
            84: 'aon@localforyou.com',
        };*/

        const map = {
            17: 'neung@localforyou.com',
            18: 'neung@localforyou.com',
            24: 'neung@localforyou.com',
            35: 'neung@localforyou.com',
            38: 'neung@localforyou.com',
            47: 'neung@localforyou.com',
            62: 'neung@localforyou.com',
            72: 'neung@localforyou.com',
            76: 'neung@localforyou.com',
            79: 'neung@localforyou.com',
            84: 'neung@localforyou.com',
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
</script>
</body>
</html>
