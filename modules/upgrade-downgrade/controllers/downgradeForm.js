/* ============================================================
   Downgrade Form v1.0.0 - JavaScript Controller
   ============================================================ */

// ===== Form Mode =====
// 'customer' hides the staff-only fields (Monday project search, internal
// details, submitted-by), so those must not be validated as required.
const FORM_MODE = document.body.dataset.mode || 'customer';
const IS_STAFF = (FORM_MODE === 'staff');

// ===== Package Comparison Data (Downgrade — reverse of upgrade) =====
const downgradeData = {
    'ultimate_to_growth': {
        title: 'Local Ultimate &rarr; Local Growth',
        items: [
            { feature: 'GMB Posts',             from: '8/Month',      to: '4/Month',       removed: false },
            { feature: 'Social Media Posts',    from: '8/Month',      to: '4/Month',       removed: false },
            { feature: 'Email Campaigns',       from: '4/Month',      to: '2/Month',       removed: false },
            { feature: 'SMS Campaigns',         from: '4/Month',      to: '2/Month',       removed: false },
            { feature: 'Advanced SEO Components', from: 'Included',   to: 'Removed',       removed: true  },
        ]
    },
    'ultimate_to_starter': {
        title: 'Local Ultimate &rarr; Local Starter',
        items: [
            { feature: 'GMB Posts',             from: '8/Month',      to: '2/Month',       removed: false },
            { feature: 'Social Media Posts',    from: '8/Month',      to: 'None',          removed: true  },
            { feature: 'Email Campaigns',       from: '4/Month',      to: '1/Month',       removed: false },
            { feature: 'SMS Campaigns',         from: '4/Month',      to: '1/Month',       removed: false },
            { feature: 'Advanced SEO Components', from: 'Included',   to: 'Removed',       removed: true  },
            { feature: 'Ads',                   from: 'All Channels', to: '1 Channel Only',removed: false },
            { feature: 'Monthly Strategy Call', from: 'Included',     to: 'Removed',       removed: true  },
        ]
    },
    'growth_to_starter': {
        title: 'Local Growth &rarr; Local Starter',
        items: [
            { feature: 'GMB Posts',             from: '4/Month',      to: '2/Month',       removed: false },
            { feature: 'Social Media Posts',    from: '4/Month',      to: 'None',          removed: true  },
            { feature: 'Email Campaigns',       from: '2/Month',      to: '1/Month',       removed: false },
            { feature: 'SMS Campaigns',         from: '2/Month',      to: '1/Month',       removed: false },
            { feature: 'Ads',                   from: 'All Channels', to: '1 Channel Only',removed: false },
            { feature: 'Monthly Strategy Call', from: 'Included',     to: 'Removed',       removed: true  },
        ]
    }
};

// ===== Section Visibility =====
function updateSectionVisibility() {
    const requestType    = $('input[name="requestType"]:checked').val();
    const isDowngradePkg = (requestType === 'Downgrade to other product');

    // Second-level target choice appears only for "Downgrade to other product"
    if (isDowngradePkg) {
        $('#downgradeTargetGroup').slideDown(200);
    } else {
        $('#downgradeTargetGroup').slideUp(200);
        $('input[name="downgradeTarget"]').prop('checked', false);
    }
}

// ===== Star Rating Widget =====
const starLabels = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];

function initStarRating(widgetId, inputId, labelId) {
    const widget  = document.getElementById(widgetId);
    const input   = document.getElementById(inputId);
    const labelEl = labelId ? document.getElementById(labelId) : null;
    if (!widget || !input) return;

    const stars = widget.querySelectorAll('.star');

    stars.forEach((star, index) => {
        star.addEventListener('mouseenter', () => {
            stars.forEach((s, i) => {
                s.classList.toggle('active', i <= index);
                s.classList.remove('selected');
            });
            if (labelEl) labelEl.textContent = starLabels[index + 1];
        });

        star.addEventListener('click', () => {
            const val = index + 1;
            input.value = val;
            stars.forEach((s, i) => {
                s.classList.remove('active');
                s.classList.toggle('selected', i < val);
            });
            if (labelEl) labelEl.textContent = starLabels[val];
        });
    });

    widget.addEventListener('mouseleave', () => {
        const selected = parseInt(input.value) || 0;
        stars.forEach((s, i) => {
            s.classList.remove('active');
            s.classList.toggle('selected', selected > 0 && i < selected);
        });
        if (labelEl) labelEl.textContent = selected ? starLabels[selected] : '';
    });
}

// ===== DOM Ready =====
// ===== Project Search Autocomplete (staff mode) =====
(function () {
    const $input = $('#mondayProjectId');
    const $dropdown = $('#projectSearchDropdown');
    // Guarded on the dropdown, not on #mondayProjectId. Customer mode now has
    // #mondayProjectId too -- as a hidden input the email lookup fills -- so
    // guarding on that alone would run this whole staff search on the customer
    // page, and its #country handler would download the country's entire shop
    // directory into the customer's browser on every country change. The
    // dropdown is rendered in staff mode only.
    if (!$dropdown.length || !$input.length) return;

    let searchTimer = null;
    let allProjects = [];

    function setLoading(on) {
        if (on) {
            $input.prop('disabled', true);
            $input.closest('.position-relative').find('.ps-spinner').remove();
            $input.after('<div class="ps-spinner"><span class="spinner-border spinner-border-sm"></span> Fetching projects...</div>');
        } else {
            $input.prop('disabled', false);
            $input.closest('.position-relative').find('.ps-spinner').remove();
        }
    }

    function fetchProjects(country) {
        if (!country) { allProjects = []; return; }
        setLoading(true);
        $dropdown.removeClass('show').empty();
        $.getJSON('../assets/API/selectProjectCountry/live.php', { country }, function (data) {
            allProjects = Array.isArray(data) ? data : [];
        }).fail(function () {
            allProjects = [];
        }).always(function () {
            setLoading(false);
            renderDropdown($input.val().trim());
        });
    }

    function renderDropdown(query) {
        if (!query) { $dropdown.removeClass('show').empty(); return; }
        const q = query.toLowerCase();
        const matches = allProjects.filter(p =>
            p.shopName.toLowerCase().includes(q) ||
            p.shopId.includes(q)
        ).slice(0, 30);

        if (!matches.length) {
            $dropdown.html('<div class="project-search-empty">No results found</div>').addClass('show');
            return;
        }
        const items = matches.map(p => {
            const meta = [p.shopType, p.ownerName, p.phone].filter(Boolean).join(' · ');
            return `<div class="project-search-item" data-id="${p.shopId}" data-name="${p.shopName}" data-boardid="${p.boardId || ''}">
                <div class="ps-name">${p.shopName} <small class="text-muted">#${p.shopId}</small></div>
                ${meta ? `<div class="ps-meta">${meta}</div>` : ''}
            </div>`;
        }).join('');
        $dropdown.html(items).addClass('show');
    }

    $('#country').on('change', function () {
        const country = $(this).val();
        $input.val('');
        // The board id goes with it. Leaving it behind would submit a board id
        // with no project id under a different country -- a request attached to
        // the wrong shop in Monday.
        $('#boardId').val('');
        $dropdown.removeClass('show').empty();
        allProjects = [];
        if (country) fetchProjects(country);
    });

    $input.on('input', function () {
        clearTimeout(searchTimer);
        const q = $(this).val().trim();
        if (!q) { $dropdown.removeClass('show').empty(); return; }
        searchTimer = setTimeout(() => renderDropdown(q), 200);
    });

    $dropdown.on('click', '.project-search-item', function () {
        // Staff picked a shop explicitly, so the email lookup no longer owns
        // the shop fields. Without releasing it the form would keep showing
        // the looked-up shop's name and status line while submitting THIS
        // shop's project id, and a later failed lookup would clear the id
        // just chosen here.
        if (typeof udmLookup !== 'undefined' && udmLookup) udmLookup.release();
        $input.val($(this).data('id'));
        $('#boardId').val($(this).data('boardid'));
        $dropdown.removeClass('show').empty();
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#mondayProjectId, #projectSearchDropdown').length) {
            $dropdown.removeClass('show').empty();
        }
    });
})();

// ===== Customer Email Lookup =====
// The lookup lives in assets/js/emailLookup.js, shared with the upgrade form.
//
// Both modes get it. CS asked for both sides to work the same way, which means
// "staff should not have to type what the email can fill" -- not "remove the
// project search". Staff keeps both, and with both active they can disagree:
// staff searches shop X, then an email fills shop Y. Last action wins, and the
// status line says which shop is currently attached, so what will be submitted
// is never ambiguous.

// The products from the last successful lookup. The product checkbox list is
// rendered from this, and it is the empty array whenever no lookup has
// succeeded -- which is what makes the "enter your email first" prompt
// distinguishable from "this shop has no products".
let udmLookupProducts = [];

// The shop from the last successful lookup, or null. The post-submit Araya
// redirect reads its name.
let udmLookupShop = null;

// `var`, not `const`: the staff project-search handler above is registered
// before this line runs and calls udmLookup.release(). A const would sit in the
// temporal dead zone until here, where even `typeof` throws rather than
// reporting undefined, so the guard there could not do its job. A click can
// only happen after this script has run, but a hoisted var makes that
// ordering irrelevant instead of merely unreachable.
var udmLookup = udmInitEmailLookup({
    email:        '#emailAddress',
    country:      '#country',
    status:       '#emailLookupStatus',
    branchPicker: '#branchPicker',
    branchList:   '#branchPickerList',

    // The downgrade form's field names differ from the upgrade form's: contact
    // person rather than owner name, mobile number rather than phone number.
    shopFields: {
        shopName:  '#shopName',
        ownerName: '#contactPerson',
        phone:     '#mobileNumber',
    },

    identityFields: {
        shopId:  '#mondayProjectId',
        boardId: '#boardId',
    },

    // No productList/productSelect options: this form has no manual product
    // select to fall back to. It renders checkboxes from onProducts instead.
    onProducts: function (subscriptions, shop) {
        udmLookupProducts = subscriptions || [];
        udmLookupShop     = shop || null;
        udmRenderProductList();
    },

    onChange: updateSectionVisibility,
});

// Replaced in full by Task 6. The lookup calls this after every state change,
// so it has to exist before the product list is built.
function udmRenderProductList() {
    // Task 6 renders the checkboxes here.
}

$(function () {

    // --- Request type toggle ---
    $('input[name="requestType"]').on('change', updateSectionVisibility);

    // --- Custom date toggle ---
    $('input[name="effectiveDate"]').on('change', function () {
        if ($('#effCustomDate').is(':checked')) {
            $('#customDateGroup').slideDown(200);
        } else {
            $('#customDateGroup').slideUp(200).find('input').val('');
        }
    });

    // --- Other reason toggle ---
    $('#reasonOther').on('change', function () {
        if (this.checked) {
            $('#otherReasonGroup').slideDown(200);
        } else {
            $('#otherReasonGroup').slideUp(200).find('input').val('');
        }
    });

    // --- Init star ratings ---
    initStarRating('overallRatingWidget',    'overallRating',    'overallRatingLabel');

    // --- Initial state ---
    updateSectionVisibility();
});

// ===== Form Submission =====
$('#downgradeForm').on('submit', function (e) {
    e.preventDefault();

    if (!validateForm()) return;

    const result    = $('#result');
    const cmdSubmit = $('#cmdSubmit');
    result.html('<div class="alert alert-warning"><img src="../assets/img/loading.gif" alt="Loading" height="24"> Processing...</div>');
    cmdSubmit.hide();

    const formData = collectFormData();
    const payload  = JSON.stringify(formData);

    sendData(formData);
    saveToDB(payload, result, cmdSubmit);
});

// ===== Collect Form Data =====
function collectFormData() {
    return $('#downgradeForm').serializeArray().reduce((obj, item) => {
        if (item.name.endsWith('[]')) {
            const key = item.name.replace('[]', '');
            if (!obj[key]) obj[key] = [];
            obj[key].push(item.value);
        } else {
            obj[item.name] = item.value;
        }
        return obj;
    }, {});
}

// Map country code → Monday project board id (keep in sync with
// assets/API/selectProjectCountry/live.php $PROJECT_IDS)
const COUNTRY_BOARD_IDS = {
    CA: '1943203287',
    AU: '1943203246',
    NZ: '1943203264',
    UK: '1943203305',
    US: '1940392927',
    TH: '1943203205',
};

// ===== Send to Webhook =====
function sendData(formData) {
    const now = new Date();
    const formattedDate = `${String(now.getDate()).padStart(2,'0')}-${String(now.getMonth()+1).padStart(2,'0')}-${now.getFullYear()} ${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}`;
    const reasons = formData.reasons || [];
    const reasonText = Array.isArray(reasons) ? reasons.join(', ') : reasons;

    const country = (formData.country || '').toUpperCase();

    const jsonData = Object.assign({}, formData, {
        date: formattedDate,
        // ---- Normalized fields (shared upgrade/downgrade webhook schema) ----
        formType: 'downgrade',
        // "Downgrade to other product" carries a second-level target; report the
        // specific target as changeType so downstream keeps the old granularity.
        changeType: formData.downgradeTarget || formData.requestType || '',
        requestType: formData.requestType || '',
        downgradeTarget: formData.downgradeTarget || '',
        email: formData.emailAddress || '',
        shopName: formData.shopName || '',
        mondayProjectId: formData.mondayProjectId || '',
        country: formData.country || '',
        countryBoardId: COUNTRY_BOARD_IDS[country] || '',
        originalProduct: formData.originalProduct || '',
        newProduct: formData.newProduct || '',
        contractPeriod: formData.contractPeriod || '',
        billingDate: formData.billingDate || '',
        staffName: formData.accountManager || '',
        reason: reasonText || '',
        note: formData.staffDowngradeNote || '',
    });

    $.ajax({
        url: "https://hook.us1.make.com/15w2pggndrpv3ggsm7hfqqh1vsy83s92",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify(jsonData),
        success: () => {
            console.log("Downgrade webhook sent.");
            window.location.replace("thankyou.php");
        },
        error: () => {
            console.error("Downgrade webhook failed.");
        }
    });
}

// ===== Save to Database =====
function saveToDB(payload, result, cmdSubmit) {
    $.ajax({
        url: "../models/downgradeForm.php",
        type: "POST",
        data: { payload },
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
    const requestType    = $('input[name="requestType"]:checked').val();
    const isDowngradePkg = (requestType === 'Downgrade to other product');

    // Always required text fields
    ['country', 'businessType', 'shopName', 'address', 'contactPerson', 'emailAddress', 'mobileNumber'].forEach(id => {
        const el = document.getElementById(id);
        if (el && !el.value.trim()) {
            el.classList.add('is-invalid');
            isValid = false;
        } else if (el) {
            el.classList.remove('is-invalid');
        }
    });

    // Request type
    if (!requestType) {
        const errEl = document.getElementById('requestTypeError');
        if (errEl) { errEl.style.display = ''; errEl.textContent = 'Please select a type of request.'; }
        isValid = false;
    } else {
        const errEl = document.getElementById('requestTypeError');
        if (errEl) errEl.style.display = 'none';
    }

    // Downgrade target (second level) required when downgrading to another product
    const tgtErrEl = document.getElementById('downgradeTargetError');
    if (isDowngradePkg && !$('input[name="downgradeTarget"]:checked').val()) {
        if (tgtErrEl) { tgtErrEl.style.display = ''; tgtErrEl.textContent = 'Please select a product to downgrade to.'; }
        isValid = false;
    } else if (tgtErrEl) {
        tgtErrEl.style.display = 'none';
    }

    // Effective date
    if (!$('input[name="effectiveDate"]:checked').val()) {
        isValid = false;
    }

    // Custom date required when custom option selected
    if ($('#effCustomDate').is(':checked') && !$('#customDate').val()) {
        document.getElementById('customDate').classList.add('is-invalid');
        isValid = false;
    }

    // At least one reason
    if ($('input[name="reasons[]"]:checked').length === 0) {
        isValid = false;
    }

    // Contact preference
    if (!$('input[name="contactPreference"]:checked').val()) {
        const errEl = document.getElementById('contactPreferenceError');
        if (errEl) { errEl.style.display = ''; errEl.textContent = 'Please select a contact preference.'; }
        isValid = false;
    } else {
        const errEl = document.getElementById('contactPreferenceError');
        if (errEl) errEl.style.display = 'none';
    }

    if (!isValid) {
        $('#result').html('<div class="alert alert-danger">Please fill in all required fields.</div>');
        const firstInvalid = document.querySelector('.is-invalid');
        if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    return isValid;
}

// ===== Clear validation on input =====
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('downgradeForm');
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

    if ($('#testMode').val() === 'true') {
        applyTestAutofill();
    }
});

// ===== Test Mode Autofill =====
function applyTestAutofill() {
    // Business Info
    $('#shopName').val('Test Shop');
    $('#tradingName').val('Test Shop LLC');
    $('#address').val('123 Test St, Los Angeles, CA 90001');
    $('#contactPerson').val('Test Person');
    $('#emailAddress').val('test@example.com');
    $('#mobileNumber').val('+1 (555) 010-0000');

    // Staff fields
    $('#country').val('TH');
    $('#mondayProjectId').val('99999');
    $('#boardId').val('1943203205'); // TH project board

    // Request Type
    $('#reqOtherProduct').prop('checked', true).trigger('change');
    $('#tgtOrderingOnly').prop('checked', true).trigger('change');

    // Staff downgrade fields
    $('#contractPeriod').val('12 months');
    $('#accountManager').val('Test Manager');
    $('#billingDate').val(new Date().toISOString().split('T')[0]);
    $('#staffDowngradeNote').val('Test mode - automated autofill');

    // Effective Date
    $('#effEndBilling').prop('checked', true).trigger('change');

    // Reasons
    $('#reasonBudget').prop('checked', true);
    $('#reasonResults').prop('checked', true);

    // Features to Keep
    $('#keepNone').prop('checked', true);

    // Additional Details
    $('#improvementSuggestion').val('Test mode - improvement suggestion');
    $('#contactNo').prop('checked', true).trigger('change');
    $('#feedbackComments').val('Test mode - feedback');

    // Star Ratings
    $('#overallRating').val('5');
    setStarUI('overallRatingWidget', 5, 'overallRatingLabel');

    // Additional Comments
    $('#additionalComments').val('Test mode - automated autofill');
}

function setStarUI(widgetId, val, labelId) {
    const labels = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
    const widget = document.getElementById(widgetId);
    if (!widget) return;
    widget.querySelectorAll('.star').forEach((s, i) => {
        s.classList.remove('active');
        s.classList.toggle('selected', i < val);
    });
    const labelEl = labelId ? document.getElementById(labelId) : null;
    if (labelEl) labelEl.textContent = labels[val] || '';
}
