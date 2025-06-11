<?php
global $db;
date_default_timezone_set('Asia/Bangkok');
$today = date("Y-m-d");
$tomorrow = date("Y-m-d", strtotime("+1 day"));
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
            <li class="breadcrumb-item active" aria-current="page">Time Off</li>
        </ol>
    </nav>
</header>

<main>
    <section style="min-height: 50vh;">
        <h3 class="mb-5"><i class="bi bi-life-preserver"></i> Time Off Request: </h3>
        <form id="checkForm" enctype="multipart/form-data" method="POST">

            <div class="row mb-3">
                <div class="col-6">
                    <label for="staffID" class="form-label"><i class="bi bi-person-fill"></i> Staff name <span class="red">*</span></label>
                    <?php $staff = $db->query("SELECT * FROM `staffs` WHERE `sStaffType` = 'partTime' ORDER BY `sNickName`;")->fetchAll(); ?>
                    <select id="staffID" name="staffID" class="form-select" onchange="getStaffTeam(this.value);" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($staff as $row): ?>
                            <?php
                                if ($row['sNationality'] === 'Foreign') {
                                    $displayName = $row['sName'];
                                } else if ($row['sNationality'] === 'Thai') {
                                    $displayName = $row['sNickName'] . ' ' . showName($row['sName']);
                                }
                            ?>
                            <option value="<?php echo $row['sID']; ?>"><?php echo $displayName; ?></option>
                        <?php endforeach; ?>
                        <input type="hidden" name="staffName" id="staffName" value="">
                    </select>
                </div>
                
                <div class="col-6">
                    <label for="staffTeam" class="form-label"><i class="bi bi-person-fill"></i> Department <span class="red">*</span></label>
                    <select id="staffTeam" name="staffTeam" class="form-select">
                        <option value="">auto select</option>
                        <option value="Customer Support">Customer Support</option>
                        <option value="Account Manager">Account Manager</option>
                        <option value="Sales">Sales</option>
                        <option value="Human Resource">Human Resource</option>
                        <option value="IT">IT</option>
                        <option value="Marketing">Marketing</option>
                        <option value="House Keeping">House Keeping</option>
                    </select>
                    <input type="hidden" name="manager" id="manager" value="">
                </div>
            </div>


            <div class="row mb-3">
                <div class="col-6" id="timeOffStatusDiv">
                    <label for="timeOffStatus" class="form-label"><i class="bi bi-postage-fill"></i> Leave Types <span class="red">*</span></label>
                    <select id="timeOffStatus" name="timeOffStatus" class="form-select" required>
                        <option value="">-- Select --</option>
                        <option value="Sick leave">Sick leave</option>
                        <option value="Public holiday">Public holiday</option>
                        <option value="Vacation">Vacation</option>
                        <option value="Time Off">Time Off</option>
                    </select>
                </div>

                <div class="col-6" id="timeOffDateDiv">
                    <label for="timeOffDateRange" class="form-label"><i class="bi bi-calendar-date-fill"></i> Date Start-End <span class="red">*</span></label>
                    <input type="text" id="timeOffDateRange" name="timeOffDateRange" class="form-control flatpickr" value="<?php echo $today; ?>" required>
                </div>
            </div>

            <div class="mb-3" id="attachmentDiv">
                <label for="attachment" class="form-label"><i class="bi bi-file-earmark-arrow-up"></i> Attach Documents (if any)</label>
                <input type="file" id="attachment" name="attachment" class="form-control file-input mb-2" onchange="handleFileUpload(this)" accept=".jpg,.jpeg,.png,.pdf">
                <input type="hidden" name="uploadedFile" class="filePath w-100">
            </div>

            <div class="d-flex justify-content-end">
                <button id="cmdSubmit" type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>

        <div id="result" class="mt-3"></div>
    </section>
</main>
<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../assets/libs/flatpickr/flatpickr.js"></script>
<script src="../assets/libs/select2/js/select2.min.js"></script>
<script src="../assets/libs/moment-2.30.1/moment.min.js"></script>
<script src="../controllers/timeOff.js?v=1.0.0"></script>
<script>
    const result = $('#result');
    const cmdSubmit = $('#cmdSubmit');
    let now = new moment();

    $(function () {
        $('.flatpickr').flatpickr({ mode: "range",dateFormat: "Y-m-d" });
        // $('#staffName').select2();

        $('#actionType').on('change', function () {
            const action = $(this).val();
            $('#workDateDiv').show();
            $('#checkinTimeDiv, #checkoutTimeDiv').hide();
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
        });

        $('#checkForm').on('submit', function (e) {
            e.preventDefault();
            result.html(`<div class="alert alert-warning"><img src="../assets/img/loading.gif" alt="Loading" height="24"> Processing...</div>`);
            cmdSubmit.prop('disabled', true); // ✅ ปิดปุ่ม save

            const formData = $(this).serializeArray().reduce((obj, item) => {
                obj[item.name] = item.value;
                return obj;
            }, {});

            dateRange = formData.timeOffDateRange;
            const [startDate, endDate] = dateRange.split(" to ");
            formData.timeOffDateStart = startDate;
            formData.timeOffDateEnd = endDate || startDate;

            const makeWebhookURL = "https://hook.us1.make.com/l4qyd4guxkqe2n38dun42bk9upy43499";

            $.post(makeWebhookURL, formData)
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
                });
        });


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
function showName($full): string
{
    $temp = explode(" ", $full);
    return $temp[0];
}
?>