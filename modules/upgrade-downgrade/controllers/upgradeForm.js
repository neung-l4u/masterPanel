/* ============================================================
   Upgrade Form v2.0.0 - JavaScript Controller
   ============================================================ */

// ===== Package Comparison Data =====
const comparisonData = {
    'starter_to_growth': {
        title: 'Local Starter &rarr; Local Growth',
        items: [
            { newVal: 'Google + FB/IG Ads', oldVal: 'Was: 1 Ad Channel only' },
            { newVal: '4 Social Media Posts/Month', oldVal: 'Was: None' },
            { newVal: '4 GMB Posts/Month', oldVal: 'Was: 2 GMB Posts/Month' },
            { newVal: '2 Email Campaigns/Month', oldVal: 'Was: 1 Email Campaign/Month' },
            { newVal: 'Monthly Strategy Call', oldVal: 'Was: None' },
        ]
    },
    'starter_to_ultimate': {
        title: 'Local Starter &rarr; Local Ultimate',
        items: [
            { newVal: 'Google + FB/IG + Advanced Ads', oldVal: 'Was: 1 Ad Channel only' },
            { newVal: '8 Social Media Posts/Month', oldVal: 'Was: None' },
            { newVal: '8 GMB Posts/Month', oldVal: 'Was: 2 GMB Posts/Month' },
            { newVal: '4 Email Campaigns/Month', oldVal: 'Was: 1 Email Campaign/Month' },
            { newVal: '4 SMS Campaigns/Month', oldVal: 'Was: 2 SMS Campaigns/Month' },
            { newVal: 'Advanced SEO Components', oldVal: 'Was: None' },
            { newVal: 'Monthly Strategy Call', oldVal: 'Was: None' },
        ]
    },
    'growth_to_ultimate': {
        title: 'Local Growth &rarr; Local Ultimate',
        items: [
            { newVal: '8 Social Media Posts/Month', oldVal: 'Was: 4 Social Media Posts/Month' },
            { newVal: '8 GMB Posts/Month', oldVal: 'Was: 4 GMB Posts/Month' },
            { newVal: '4 Email Campaigns/Month', oldVal: 'Was: 2 Email Campaigns/Month' },
            { newVal: '4 SMS Campaigns/Month', oldVal: 'Was: 2 SMS Campaigns/Month' },
            { newVal: 'Advanced SEO Components', oldVal: 'Was: None' },
        ]
    }
};

// ===== Helper: Get package tier from product value =====
function getTier(val) {
    if (!val) return null;
    if (val.includes('starter')) return 'starter';
    if (val.includes('growth')) return 'growth';
    if (val.includes('ultimate')) return 'ultimate';
    return null;
}

function getPlanType(val) {
    if (!val) return null;
    if (val.startsWith('pro_')) return 'pro';
    if (val.startsWith('solo_')) return 'solo';
    if (val.startsWith('bundle_')) return 'bundle';
    return null;
}

// ===== Build comparison HTML =====
function buildComparisonHTML(origVal, newVal) {
    const origTier = getTier(origVal);
    const newTier = getTier(newVal);
    const origPlan = getPlanType(origVal);
    const newPlan = getPlanType(newVal);

    if (!origTier || !newTier) return '';

    let key = null;
    if (origTier === 'starter' && newTier === 'growth') key = 'starter_to_growth';
    else if (origTier === 'starter' && newTier === 'ultimate') key = 'starter_to_ultimate';
    else if (origTier === 'growth' && newTier === 'ultimate') key = 'growth_to_ultimate';

    if (!key || !comparisonData[key]) return '';

    const data = comparisonData[key];
    let html = '<div class="comparison-card">';
    html += '<h6><i class="bi bi-arrow-up-circle-fill"></i> ' + data.title + '</h6>';

    data.items.forEach(item => {
        html += '<div class="comparison-item">';
        html += '<i class="bi bi-plus-circle-fill"></i>';
        html += '<div><span class="new-val">' + item.newVal + '</span><br><span class="old-val">' + item.oldVal + '</span></div>';
        html += '</div>';
    });

    // Bundle note
    const isUpgradingToBundle = (newPlan === 'bundle');
    const isFromSolo = (origPlan === 'solo');
    if (isUpgradingToBundle) {
        html += '<div class="bundle-note"><i class="bi bi-info-circle"></i> ';
        if (isFromSolo) {
            html += 'Upgrading from Solo to Bundle includes the online ordering/booking system. Additional system build details are required below.';
        } else {
            html += 'Bundle plan includes the online ordering/booking system (restaurant/massage).';
        }
        html += '</div>';
    }

    html += '</div>';
    return html;
}

// ===== Conditional Section Visibility =====
function updateSectionVisibility() {
    const upgradeType = $('input[name="upgradeType"]:checked').val();
    const origVal = $('#originalProduct').val();
    const newVal = $('#newProduct').val();
    const origPlan = getPlanType(origVal);
    const newPlan = getPlanType(newVal);

    const isUpgradePackage = (upgradeType === 'upgrade_package');
    const isAddonOnly = (upgradeType === 'addon_only');
    const isSoloToBundle = (origPlan === 'solo' && newPlan === 'bundle');

    // Upgrade Info: shown only for package upgrade
    if (isUpgradePackage) {
        $('#upgradeInfoSection').slideDown(200);
    } else {
        $('#upgradeInfoSection').slideUp(200);
    }

    // Add-on section: always shown when a type is selected
    if (isUpgradePackage || isAddonOnly) {
        $('#addonSection').slideDown(200);
        $('#socialMediaSection').slideDown(200);
    } else {
        $('#addonSection').slideUp(200);
        $('#socialMediaSection').slideUp(200);
    }

    // Solo->Bundle section: shown when original is Solo and new is Bundle
    if (isUpgradePackage && isSoloToBundle) {
        $('#soloBundleSection').slideDown(200);
    } else {
        $('#soloBundleSection').slideUp(200);
    }

    // Package comparison card
    if (isUpgradePackage && origVal && newVal) {
        const html = buildComparisonHTML(origVal, newVal);
        if (html) {
            $('#packageComparisonCard').html(html).slideDown(200);
        } else {
            $('#packageComparisonCard').slideUp(200).empty();
        }
    } else {
        $('#packageComparisonCard').slideUp(200).empty();
    }
}

// ===== DOM Ready =====
$(function () {

    // --- Upgrade type toggle ---
    $('input[name="upgradeType"]').on('change', updateSectionVisibility);

    // --- Product selection change ---
    $('#originalProduct, #newProduct').on('change', updateSectionVisibility);

    // --- Menu/Logo status: show link field when Google Drive selected ---
    $('#menuLogoStatus').on('change', function () {
        if ($(this).val() === 'google_drive') {
            $('#menuLogoLink').slideDown(200);
        } else {
            $('#menuLogoLink').slideUp(200).val('');
        }
    });

    // --- Website option: show template dropdown for GF website ---
    $('input[name="websiteOption"]').on('change', function () {
        if ($('#webOptGF').is(':checked')) {
            $('#websiteTemplateGroup').slideDown(200);
        } else {
            $('#websiteTemplateGroup').slideUp(200);
        }
    });

    // --- Domain option: show Other text field ---
    $('input[name="domainOption"]').on('change', function () {
        if ($('#domainOtherRadio').is(':checked')) {
            $('#domainOtherGroup').slideDown(200);
        } else {
            $('#domainOtherGroup').slideUp(200).find('input').val('');
        }
    });

    // --- QR code: show Other text field ---
    $('input[name="qrCodeOption"]').on('change', function () {
        if ($('#qrOther').is(':checked')) {
            $('#qrOtherGroup').slideDown(200);
        } else {
            $('#qrOtherGroup').slideUp(200).find('input').val('');
        }
    });

    // --- Gift voucher: show value field ---
    $('input[name="giftVoucher"]').on('change', function () {
        if ($('#giftVoucherYes').is(':checked')) {
            $('#voucherValueGroup').slideDown(200);
        } else {
            $('#voucherValueGroup').slideUp(200).find('input').val('');
        }
    });

    // --- Initial visibility on page load ---
    updateSectionVisibility();
});

// ===== Form Submission =====
$('#upgradeForm').on('submit', function (e) {
    e.preventDefault();

    if (!validateForm()) return;

    const result = $('#result');
    const cmdSubmit = $('#cmdSubmit');
    result.html('<div class="alert alert-warning"><img src="../assets/img/loading.gif" alt="Loading" height="24"> Processing...</div>');
    cmdSubmit.hide();

    const formData = collectFormData();
    const payload = JSON.stringify(formData);

    sendData(formData);
    saveToDB(payload, result, cmdSubmit, formData.stripeID);
});

// ===== Collect all form data =====
function collectFormData() {
    const raw = $('#upgradeForm').serializeArray().reduce((obj, item) => {
        // Handle arrays (checkboxes with [] names)
        if (item.name.endsWith('[]')) {
            const key = item.name.replace('[]', '');
            if (!obj[key]) obj[key] = [];
            obj[key].push(item.value);
        } else {
            obj[item.name] = item.value;
        }
        return obj;
    }, {});

    return raw;
}

// ===== Send to webhook =====
function sendData(formData) {
    const now = new Date();
    const formattedDate = `${String(now.getDate()).padStart(2, '0')}-${String(now.getMonth() + 1).padStart(2, '0')}-${now.getFullYear()} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

    const jsonData = Object.assign({}, formData, { date: formattedDate });

    $.ajax({
        url: "https://hook.us1.make.com/3y48icq17day9xjnn4wjd3cuan9yutcw",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify(jsonData),
        success: () => {
            console.log("Webhook sent successfully.");
            window.location.replace("thankyou.php");
        },
        error: () => {
            console.error("Webhook sending failed.");
        }
    });
}

// ===== Save to database =====
function saveToDB(payload, result, cmdSubmit, stripeID) {
    $.ajax({
        url: "../models/upgradeForm.php",
        type: "POST",
        data: { payload, stripeID: stripeID },
        success: () => {
            result.html('<div class="alert alert-warning mt-2"><img src="../assets/img/loading.gif" alt="Loading" height="24"> Saving data <span id="countDown">3</span>...</div>');
            let countdown = 3;
            const countdownInterval = setInterval(() => {
                countdown--;
                $('#countDown').text(countdown);
                if (countdown <= 0) clearInterval(countdownInterval);
            }, 1000);
        },
        error: () => {
            result.html('<div class="alert alert-danger"><i class="bi bi-x-circle-fill"></i> An error occurred. Please try again.</div>');
            cmdSubmit.show();
        }
    });
}

// ===== Validation =====
function validateForm() {
    let isValid = true;
    const upgradeType = $('input[name="upgradeType"]:checked').val();
    const isUpgradePackage = (upgradeType === 'upgrade_package');

    // Always required fields
    const alwaysRequired = ['mondayProjectId', 'bestTimeContact'];
    alwaysRequired.forEach(id => {
        const el = document.getElementById(id);
        if (el && !el.value.trim()) {
            el.classList.add('is-invalid');
            isValid = false;
        } else if (el) {
            el.classList.remove('is-invalid');
        }
    });

    // Upgrade type radio must be selected
    if (!upgradeType) {
        $('input[name="upgradeType"]').addClass('is-invalid');
        isValid = false;
    } else {
        $('input[name="upgradeType"]').removeClass('is-invalid');
    }

    // If upgrade package, validate upgrade info fields
    if (isUpgradePackage) {
        const upgradeFields = ['contractPeriod', 'upgradeReason', 'salesAgent', 'billingDate'];
        upgradeFields.forEach(id => {
            const el = document.getElementById(id);
            if (el && !el.value.trim()) {
                el.classList.add('is-invalid');
                isValid = false;
            } else if (el) {
                el.classList.remove('is-invalid');
            }
        });

        // Select fields
        ['originalProduct', 'newProduct'].forEach(id => {
            const el = document.getElementById(id);
            if (el && !el.value) {
                el.classList.add('is-invalid');
                isValid = false;
            } else if (el) {
                el.classList.remove('is-invalid');
            }
        });

        // Solo->Bundle extra required fields
        const origVal = $('#originalProduct').val();
        const newVal = $('#newProduct').val();
        if (getPlanType(origVal) === 'solo' && getPlanType(newVal) === 'bundle') {
            const soloBundleFields = ['systemEmail', 'openingDayTime'];
            soloBundleFields.forEach(id => {
                const el = document.getElementById(id);
                if (el && !el.value.trim()) {
                    el.classList.add('is-invalid');
                    isValid = false;
                } else if (el) {
                    el.classList.remove('is-invalid');
                }
            });
        }
    }

    if (!isValid) {
        $('#result').html('<div class="alert alert-danger">Please fill in all required fields correctly.</div>');
        const firstInvalid = document.querySelector('.is-invalid');
        if (firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    return isValid;
}

// ===== Clear validation on input =====
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('upgradeForm');
    if (!form) return;

    form.querySelectorAll('.form-control, .form-select').forEach(el => {
        el.addEventListener('input', function () {
            this.classList.remove('is-invalid');
        });
    });

    form.querySelectorAll('input[type="radio"]').forEach(el => {
        el.addEventListener('change', function () {
            document.querySelectorAll('input[name="' + this.name + '"]').forEach(r => r.classList.remove('is-invalid'));
        });
    });
});
