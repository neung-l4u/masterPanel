$('#customerDetailsForm').on('submit', function (e) {
    e.preventDefault();
    const result = $('#result');
    const cmdSubmit = $('#cmdSubmit');
    result.html(`<div class="alert alert-warning"><img src="../assets/img/loading.gif" alt="Loading" height="24"> Processing...</div>`);
    cmdSubmit.hide();

    const formData = $(this).serializeArray().reduce((obj, item) => {
        obj[item.name] = item.value;
        return obj;
    }, {});
    const payload = JSON.stringify(formData);

    // Test mode (?testMode=true): skip the DB write, send to the webhook only.
    if (isTestMode()) {
        console.log('🧪 Test mode: skipping DB save, sending webhook only.');
        result.html(`<div class="alert alert-info mt-2"><i class="bi bi-flask"></i> Test mode - webhook only, nothing saved to DB.</div>`);
        sendData(formData, '');
        return;
    }

    saveToDB(payload, result, cmdSubmit, formData.stripeID, formData);
});

function isTestMode() {
    return new URLSearchParams(window.location.search).get('testMode') === 'true';
}

function sendData(formData, qualify) {
    const now = new Date();
    const formattedDate = `${String(now.getDate()).padStart(2, '0')}-${String(now.getMonth() + 1).padStart(2, '0')}-${now.getFullYear()} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

    //const modulePath = "http://localhost/masterPanel/modules/aiAraya"; // Local path
    const modulePath = "https://report.localforyou.com/modules/aiAraya"; // Server path

    const jsonData = {
        businessName: formData.businessName || "",
        businessHours: formData.businessHours || "",
        currentBookingSystem: formData.currentBookingSystem || "",
        bookingUser: formData.bookingUser || "",
        bookingPassword: formData.bookingPassword || "",
        timezoneId: formData.timezoneId || "",
        shopAddress: formData.shopAddress || "",
        businessWebsite: formData.businessWebsite || "",
        phoneNumberDecision: formData.phoneNumberDecision || "",
        mainPhoneNumber: formData.mainPhoneNumber || "",
        backupPhoneNumber: formData.backupPhoneNumber || "",
        phoneCarrier: formData.phoneCarrier || "",
        shopEmail: formData.shopEmail || "",
        servicesOffered: formData.servicesOffered || "",
        bufferMinutes: formData.bufferMinutes || "",
        coupleMassage: formData.coupleMassage || "",
        happyMassage: formData.happyMassage || "",
        maleTherapists: formData.maleTherapists || "",
        femaleTherapists: formData.femaleTherapists || "",
        numberOfTherapists: formData.numberOfTherapists || "",
        therapistNames: formData.therapistNames || "",
        wheelchair: formData.wheelchair || "",
        parking: formData.parking || "",
        restroom: formData.restroom || "",
        promotions: formData.promotions || "",
        voiceType: formData.voiceType || "",
        googleCalendarId: formData.googleCalendarId || "",
        additionalComments: formData.additionalComments || "",
        setPolicies: formData.setPolicies || "",
        lateArrivalFee: formData.lateArrivalFee || "",
        noshowFee: formData.noshowFee || "",
        freeCancellationWindow: formData.freeCancellationWindow || "",
        lateCancellationFee: formData.lateCancellationFee || "",
        setDeposit: formData.setDeposit || "",
        depositAmount: formData.depositAmount || "",
        depositPaymentLink: formData.depositPaymentLink || "",
        termAndConditionsLink: formData.termAndConditionsLink || "",
        stripeID: formData.stripeID || "",
        formVersion: formData.formVersion || "NULL",
        date: formattedDate,
        qualify: qualify || "",
        testMode: isTestMode() ? "yes" : "no",
    };

    $.ajax({
        url: "https://hook.us1.make.com/3y48icq17day9xjnn4wjd3cuan9yutcw",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify(jsonData),
        success: () => {
            console.log("✅ Webhook sent successfully.");
            window.location.replace("thankyou.php");
        },
        error: () => {
            console.error("❌ Webhook sending failed.");
        }
    });
}

function saveToDB(payload, result, cmdSubmit, stripeID, formData) {
    $.ajax({
        url: "../models/customerDetailsForm.php",
        type: "POST",
        data: {payload, stripeID: stripeID},
        success: (response) => {
            const qualify = response?.qualify ?? "no";
            sendData(formData, qualify);
            result.html(`<div class="alert alert-warning mt-2"><img src="../assets/img/loading.gif" alt="Loading" height="24"> Saving data <span id="countDown">3</span>...</div>`);
            let countdown = 3;
            const countdownInterval = setInterval(() => {
                countdown--;
                $('#countDown').text(countdown);
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                }
            }, 1000);
        },
        error: () => {
            result.html(`<div class="alert alert-danger"><i class="bi bi-x-circle-fill"></i> An error occurred. Please try again.</div>`);
            cmdSubmit.show();
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('customerDetailsForm');

    form.addEventListener('submit', function (e) {
        let isValid = true;

        // Validate required text/textarea/number fields
        const requiredFields = [
            'businessName', 'businessHours', 'currentBookingSystem', 'timezoneId',
            'mainPhoneNumber', 'backupPhoneNumber', 'phoneCarrier', 'servicesOffered',
            'numberOfTherapists', 'therapistNames', 'promotions'
        ];
        requiredFields.forEach(id => {
            const el = document.getElementById(id);
            if (el && !el.value.trim()) {
                el.classList.add('is-invalid');
                isValid = false;
            } else if (el) {
                el.classList.remove('is-invalid');
            }
        });

        // Validate email if filled
        const shopEmail = document.getElementById('shopEmail');
        if (shopEmail && shopEmail.value.trim()) {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(shopEmail.value)) {
                shopEmail.classList.add('is-invalid');
                isValid = false;
            } else {
                shopEmail.classList.remove('is-invalid');
            }
        }

        // Validate required radio groups
        const requiredRadios = [
            'phoneNumberDecision', 'coupleMassage', 'happyMassage',
            'maleTherapists', 'femaleTherapists', 'wheelchair',
            'parking', 'restroom', 'voiceType', 'setPolicies', 'setDeposit'
        ];
        requiredRadios.forEach(name => {
            const radios = document.querySelectorAll(`input[name="${name}"]`);
            const checked = document.querySelector(`input[name="${name}"]:checked`);
            if (!checked) {
                radios.forEach(r => r.classList.add('is-invalid'));
                isValid = false;
            } else {
                radios.forEach(r => r.classList.remove('is-invalid'));
            }
        });

        if (!isValid) {
            e.preventDefault();
            document.getElementById('result').innerHTML = `
                <div class="alert alert-danger">Please fill in all required fields correctly.</div>
            `;
            // Scroll to the first invalid field
            const firstInvalid = document.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // Clear validation state on input
    form.querySelectorAll('.form-control, .form-select').forEach(el => {
        el.addEventListener('input', function () {
            this.classList.remove('is-invalid');
        });
    });

    form.querySelectorAll('input[type="radio"]').forEach(el => {
        el.addEventListener('change', function () {
            const radios = document.querySelectorAll(`input[name="${this.name}"]`);
            radios.forEach(r => r.classList.remove('is-invalid'));
        });
    });
});
