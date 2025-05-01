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
                                $shopType = $db->query('SELECT * FROM `tb_shopType`')->fetchAll();
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
                    <input type="text" name="shop_name" class="form-control mb-2" placeholder="ชื่อร้าน" >
                    <input type="text" name="customer_name" class="form-control mb-2" placeholder="ชื่อเจ้าของร้าน"
                           >
                    <input type="email" name="contact_email" class="form-control mb-2" placeholder="อีเมล">
                    <input type="text" name="contact_phone" class="form-control mb-2" placeholder="เบอร์โทร">
                    <input type="text" name="line_id" class="form-control mb-2" placeholder="LINE ID">
                    <input type="text" name="whatsapp" class="form-control mb-2" placeholder="WhatsApp">
                </div>
                <div class="d-flex flex-row gap-3 mb-3">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(5)">Previous</button>
                    <button type="submit" class="btn btn-success">Book now!!</button>
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
    const country = $('#country');

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
                    $("#country").val($("#country option:first").val());
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
                    alert('✅ จองนัดเรียบร้อย');
                    location.href = 'booking_success.php';
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
</script>
</body>
</html>
