<?php
global $db;
date_default_timezone_set('Asia/Bangkok');
$today = date("Y-m-d");
$now = date("H:i");
?>
<link href="../assets/libs/flatpickr/flatpickr.min.css" rel="stylesheet">
<link href="../assets/libs/select2/css/select2.min.css" rel="stylesheet" />
<link href="../assets/libs/select2/css/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .form-control:focus { box-shadow: none; }
    .red{ color: red; }
    ::placeholder {
        color: lightgray !important;
        opacity: 1; /* Firefox */
    }

    ::-ms-input-placeholder { /* Edge 12 -18 */
        color: lightgray !important;
    }
</style>
<header>
    <nav class="mb-4" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li><i style="margin-right: 0.5em;" class="bi bi-house-fill"></i></li>
            <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Time stamp</li>
        </ol>
    </nav>
</header>

<main>
    <section style="min-height: 50vh;">
        <h3 class="mb-5"><i class="bi bi-life-preserver"></i> Timestamp: </h3>
        <form id="checkForm">

            <div class="row mb-3">
                <div class="col-6">
                    <label for="staffName" class="form-label"><i class="bi bi-person-fill"></i> Staff name <span class="red">*</span></label>
                    <select id="staffName" name="staffName" class="form-select" required>
                        <option value="">-- Select --</option>
                        <?php
                        $staff = $db->query('SELECT * FROM `staffs` WHERE sStaffType = "partTime" ORDER BY sNickName')->fetchAll();
                        foreach ($staff as $row) {
                            ?>
                            <option value="<?php echo $row['sID']; ?>"><?php echo showName($row['sNickName'],$row['sName'],$row['sNationality']); ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-6">
                    <label for="actionType" class="form-label"><i class="bi bi-postage-fill"></i> Log Type <span class="red">*</span></label>
                    <select id="actionType" name="actionType" class="form-select" required>
                        <option value="">-- Select --</option>
                        <option value="checkin">Check-in</option>
                        <option value="checkout">Check-out</option>
                    </select>
                </div>
            </div>


            <div class="row mb-3">
                <div class="col-6" id="workDateDiv" style="display:none;">
                    <label for="workDate" class="form-label"><i class="bi bi-calendar-date-fill"></i> Date: <span class="text-primary">Auto Generate</span> <!--<small><a href="#" class="text-primary" onclick="setToDay();">Today</a></small>--></label>
                    <input type="text" id="workDate" name="workDate" class="form-control flatpickr" value="<?php echo $today; ?>" required>
                </div>

                <div class="col-6" id="checkinTimeDiv" style="display:none;">
                    <label for="checkinTime" class="form-label"><i class="bi bi-clock-fill"></i> Check-in time: <span class="text-primary">Auto Generate</span> <!--<small><a href="#" onclick="setToNow();">Now</a></small>--></label>
                    <input type="time" id="checkinTime" name="checkinTime" value="<?php echo $now; ?>" class="form-control">
                </div>

                <div class="col-6" id="checkoutTimeDiv" style="display:none;">
                    <label for="checkoutTime" class="form-label"><i class="bi bi-clock-fill"></i> Check-out time: <span class="text-primary">Auto Generate</span> <!--<small><a href="#" onclick="setToNow();">Now</a></small>--></label>
                    <input type="time" id="checkoutTime" name="checkoutTime" value="<?php echo $now; ?>" class="form-control">
                </div>
            </div>

            <div class="mb-3" id="noteCheckinDiv" style="display: none;">
                <label for="noteCheckin" class="form-label"><i class="bi bi-card-list"></i> Note (Check-in)</label>
                <textarea id="noteCheckin" name="noteCheckin" class="form-control" rows="3" placeholder="Short description (check-in)"></textarea>
            </div>

            <div class="mb-3" id="noteCheckoutDiv" style="display: none;">
                <label for="noteCheckout" class="form-label"><i class="bi bi-card-list"></i> Note (Check-out)</label>
                <textarea id="noteCheckout" name="noteCheckout" class="form-control" rows="3" placeholder="Short description (check-out)"></textarea>
            </div>
            <!--<input type="hidden" id="Department" name="Department" value="">
            <input type="hidden" id="manager" name="manager" value="">-->
            <input type="hidden" id="activeSQL" name="activeSQL" value="save">

            <div class="d-flex justify-content-end">
                <button id="cmdSubmit" type="submit" class="btn btn-primary">Save <i class="bi bi-floppy-fill"></i></button>
            </div>
        </form>

        <div id="result" class="mt-3"></div>
    </section>
</main>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../assets/libs/flatpickr/flatpickr.js"></script>
<script src="../assets/libs/select2/js/select2.min.js"></script>
<script src="../assets/libs/moment-2.30.1/moment.min.js"></script>
<script>
    //ประกาศตัวแปร
    const result = $('#result');
    const cmdSubmit = $('#cmdSubmit');
    let now = new moment();
    let staff = {};
    let itemData = {};



    $(function () {
        $('.flatpickr').flatpickr({ dateFormat: "Y-m-d" });//กำหนดวัน

        $('#actionType').on('change', function () {
            const action = $(this).val();
            $('#workDateDiv').show();
            $('#checkinTimeDiv, #checkoutTimeDiv, #noteCheckinDiv, #noteCheckoutDiv').hide();

            if (action === 'checkin') {
                $('#checkinTimeDiv').show();
                $('#noteCheckinDiv').show();
                $('input[name="checkoutTime"]').val(now.format("HH:mm"));
                $('textarea[name="noteCheckout"]').val('');
            } else if (action === 'checkout') {
                $('#checkoutTimeDiv').show();
                $('#noteCheckoutDiv').show();
                $('input[name="checkinTime"]').val(now.format("HH:mm"));
                $('textarea[name="noteCheckin"]').val('');
            }
        });//ซ่อน/แสดง input ตามประเภทการบันทึก

        $('#checkForm').on('submit', function (e) {
            e.preventDefault();
            result.html(`<div class="alert alert-warning"><img src="../assets/img/loading.gif" alt="Loading" height="24"> Processing...</div>`);
            cmdSubmit.prop('disabled', true); // ✅ ปิดปุ่ม save
            /*now = new moment();
            $('input[name="checkoutTime"]').val(now.format("HH:mm"));
            $('input[name="checkinTime"]').val(now.format("HH:mm"));*/

            const formData = $(this).serializeArray().reduce((obj, item) => {
                obj[item.name] = item.value;
                return obj;
            }, {});

            const [checkinHour, checkinMinute] = (formData.checkinTime || '').split(':');
            formData.checkinHour = checkinHour;
            formData.checkinMinute = checkinMinute;

            const [checkoutHour, checkoutMinute] = (formData.checkoutTime || '').split(':');
            formData.checkoutHour = checkoutHour;
            formData.checkoutMinute = checkoutMinute;

            formData.noteCheckin = $('textarea[name="noteCheckin"]').val();
            formData.noteCheckout = $('textarea[name="noteCheckout"]').val();

            if (formData.actionType === 'checkin' && !formData.checkinTime) {
                result.html(`<div class="alert alert-warning"><i class="bi bi-exclamation-circle-fill"></i> Please enter start time.</div>`);
                cmdSubmit.prop('disabled', false); // ✅ เปิดปุ่มอีกครั้ง
                return;
            }

            if (formData.actionType === 'checkout' && !formData.checkoutTime) {
                result.html(`<div class="alert alert-warning"><i class="bi bi-exclamation-circle-fill"></i> Please enter end time.</div>`);
                cmdSubmit.prop('disabled', false); // ✅ เปิดปุ่มอีกครั้ง
                return;
            }

            //ดึงข้อมูลไป select ไว้//
            const dataStaff = "../models/getStaff.php";
            const payload = { "staff": $("#staffName").val() };

            const reqStaff = $.ajax({
                url: dataStaff,
                method: "POST",
                async: false,
                cache: false,
                dataType: "json",
                data: payload,
            });

            reqStaff.done(function (res) {
                formData.staffID = res.staffID;
                formData.staffName = res.staffName;
                formData.Department = res.team;
                formData.manager = res.manager;
                formData.manager2 = res.manager2;
            });

            reqStaff.fail(function (xhr, status, error) {
                console.log("ajax reqStaff fail!!");
                console.log(status + ": " + error);
            })


            console.log(formData);




             const dataBase = "../models/activeTimestamp.php";
             $.post(dataBase, formData)
                 .done(() => {
                     formData.staffName = res.staffName;
                     formData.Department = res.team;
                     formData.manager = res.manager;
                     formData.manager2 = res.manager2;
                     console.log('success');

                 })
                 .fail(() => {
                     console.log('fail');
                 });


             if (formData.actionType === 'checkin'){
                 const dataBase2 = "../models/checkin.php";
                 const dataToSend = { ...formData, ...payload };

                 $.post(dataBase2, dataToSend)
                     .done(() => {
                         result.html(`<div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> Data calculate successfully</div>
                  <div class="alert alert-warning mt-2"><img src="../assets/img/loading.gif" alt="Loading" height="24"> Saving data <span id="countDown">3</span>...</div>`);

                         let countdown = 3;
                         const countdownInterval = setInterval(() => {
                             countdown--;
                             $('#countDown').text(countdown);
                             if (countdown <= 0) {
                                 clearInterval(countdownInterval);
                                 window.location.reload();
                             }
                         }, 1000);
                     })
                     .fail(function(error) {
                         console.log('fail', error);
                     });


             }else if (formData.actionType === 'checkout'){
                const dataBase2 = "../models/checkout.php";
                const dataToSend = { ...formData, ...payload };


                $.post(dataBase2, dataToSend)
                    .done(function(response) {

                        itemData = {};
                        itemData = response;

                        // Split checkinTime
                        if (formData.checkinTime && formData.checkinTime.includes(":")) {
                            const [inHour, inMinute] = formData.checkinTime.split(":");
                            itemData.item['checkinHour'] = inHour;
                            itemData.item['checkinMinute'] = inMinute;
                        } else {
                            itemData.item['checkinHour'] = '00';
                            itemData.item['checkinMinute'] = '00';
                        }

                        // Split checkoutTime
                        if (formData.checkoutTime && formData.checkoutTime.includes(":")) {
                            const [outHour, outMinute] = formData.checkoutTime.split(":");
                            itemData.item['checkoutHour'] = outHour;
                            itemData.item['checkoutMinute'] = outMinute;
                        } else {
                            itemData.item['checkoutHour'] = '00';
                            itemData.item['checkoutMinute'] = '00';
                        }

                        if (formData.createAtTime && formData.createAtTime.includes(" ")) {
                            const [date, time] = formData.createAtTime.split(" ");
                            itemData.item['createAtDate'] = date;
                            itemData.item['createAtTime'] = time;
                        }else{
                            itemData.item['createAtDate'] = now.format("YYYY-MM-DD");
                            itemData.item['createAtTime'] = now.format("HH:mm");
                        }

                        if (formData.updateAtTime && formData.updateAtTime.includes(" ")) {
                            const [date, time] = formData.updateAtTime.split(" ");
                            itemData.item['updateAtDate'] = date;
                            itemData.item['updateAtTime'] = time;
                        }else{
                            itemData.item['updateAtDate'] = now.format("YYYY-MM-DD");
                            itemData.item['updateAtTime'] = now.format("HH:mm");
                        }

                        // Set full time fields
                        itemData.item['checkinTime'] = formData.checkinTime;
                        itemData.item['checkoutTime'] = formData.checkoutTime;
                        itemData.item['createAtTime'] = formData.createAtTime;
                        itemData.item['updateAtTime'] = formData.updateAtTime;


                        /*itemData.item['checkinTime'] = formData.checkinTime;
                        itemData.item['checkoutTime'] = formData.checkoutTime;

                        itemData.item['checkinHour'] = 'Local for You';*/

                        ///////////////////////////////////////////////////////////////////////////////////////////
                        const makeWebhookURL = "https://hook.us1.make.com/75bocum3gs8v35045jfkb7qktlq2awru";

                        $.post(makeWebhookURL, itemData)
                            .done(() => {
                                result.html(`<div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> Data calculate successfully</div>
                  <div class="alert alert-warning mt-2"><img src="../assets/img/loading.gif" alt="Loading" height="24"> Saving data <span id="countDown">3</span>...</div>`);

                                let countdown = 3;
                                const countdownInterval = setInterval(() => {
                                    countdown--;
                                    $('#countDown').text(countdown);
                                    if (countdown <= 0) {
                                        clearInterval(countdownInterval);
                                        window.location.reload();
                                    }
                                }, 1000);
                            })
                            .fail(() => {
                                result.html(`<div class="alert alert-danger"><i class="bi bi-x-circle-fill"></i> An error occurred. Please try again.</div>`);
                                cmdSubmit.prop('disabled', false); // ✅ เปิดปุ่มอีกครั้ง
                            });//webhook
                        ///////////////////////////////////////////////////////////////////////////////////////////


                    })
                    .fail(function(error) {
                        console.log('fail', error);
                    });
            }else{
                 console.log('error');
             }





        });//on submitting

    })//ready

    function setToNow() {
        now = new moment();
        console.log(now.format("HH:mm"));
        $('#checkinTime').val(now.format("HH:mm"));
        $('#checkoutTime').val(now.format("HH:mm"));
    }

    function setToDay() {
        now = new moment();
        console.log(now.format('YYYY-MM-DD'));
        $('#workDate').val(now.format('YYYY-MM-DD'));
    }
</script>
<?php
function showName($nick="", $full="", $nationality=""): string
{
    if($nationality == "Thai") {
        $temp = explode(" ", $full);
        return $nick.' '.$temp[0];
    }else{
        return $full;
    }
}
?>