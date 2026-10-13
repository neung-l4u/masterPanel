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
// The product list appears only for "Unsubscribe specific product". Unchecking
// everything on the way out matters: a stale checked box would otherwise keep
// an Araya section on screen after the customer switched to cancelling
// everything.
function updateSectionVisibility() {
    const requestType  = $('input[name="requestType"]:checked').val();
    const isSpecific   = (requestType === 'Unsubscribe specific product');

    if (isSpecific) {
        $('#productSelectGroup').slideDown(200);
    } else {
        $('#productSelectGroup').slideUp(200);
        $('#productCheckboxList input[name="cancelledProducts[]"]').prop('checked', false);
    }

    recomputeConditionalState();
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

// Tags per product name, from assets/API/classifyProducts.php. The map that
// produces them lives in config.php, which the browser cannot read, so this is
// asked for rather than computed here -- two copies of the rules would drift.
let udmProductTags = {};

// Id of the latest tag request, so a slow response for a previous shop cannot
// overwrite the tags of the current one.
let udmTagRequestSeq = 0;

// The lookup calls this after every state change: a successful match, a branch
// choice, a revert, a failure. It renders from udmLookupProducts wholesale
// rather than patching what is on screen, so there is no path on which a
// previous shop's products survive. It is called with an empty list on every
// failure/invalidation path, so it must be safe to wipe ticked boxes.
function udmRenderProductList() {
    const $list  = $('#productCheckboxList');
    const $enter = $('#productListPrompt');
    const $empty = $('#productListEmpty');

    if (!udmLookupProducts.length) {
        $list.empty();
        udmProductTags = {};
        udmTagRequestSeq++;   // drop any in-flight classification

        // An empty list has two meanings. udmLookupShop tells them apart: it is
        // non-null when a shop was found that has no active products.
        if (udmLookupShop) {
            $enter.hide();
            $empty.show();
        } else {
            $empty.hide();
            $enter.show();
        }
        recomputeConditionalState();
        return;
    }

    $enter.hide();
    $empty.hide();

    const html = udmLookupProducts.map(function (product, i) {
        const id   = 'cancelProduct' + i;
        const name = udmEscapeHtml(product.name);
        const nameAttr = udmEscapeAttr(product.name);
        return `
            <div class="product-checkbox-item">
                <input type="checkbox" id="${id}" name="cancelledProducts[]" value="${nameAttr}">
                <label for="${id}">${name}</label>
            </div>`;
    }).join('');

    $list.html(html);

    // Fresh boxes are all unticked, so no tags apply until classification lands.
    udmProductTags = {};
    udmFetchProductTags(udmLookupProducts.map(p => p.name));
    recomputeConditionalState();
}

// Product names come from Monday and are rendered as HTML, so they are escaped
// rather than trusted. Same guard as the shared lookup's.
function udmEscapeHtml(value) {
    return $('<div>').text(value == null ? '' : String(value)).html();
}

// For a value that lands inside a double-quoted HTML attribute. jQuery's
// text()->html() round trip escapes & < > but NOT the double quote, so a
// product name containing one would close value="..." and anything after it
// would be parsed as further attributes. No name in the mirror contains a
// quote today, but the names come from a free-text Monday column, so a
// product called 6" Display is one edit away.
function udmEscapeAttr(value) {
    return udmEscapeHtml(value).replace(/"/g, '&quot;');
}

// Classification drives which conditional sections exist, so a failure here
// must fail safe: no tags means no conditional section fires, which is the same
// state as a shop whose products are all add-ons. The customer can still
// submit.
function udmFetchProductTags(names) {
    const seq = ++udmTagRequestSeq;

    if (!names.length) {
        udmProductTags = {};
        recomputeConditionalState();
        return;
    }

    $.getJSON('../assets/API/classifyProducts.php', { names: names })
        .done(function (data) {
            if (seq !== udmTagRequestSeq) { return; }
            udmProductTags = (data && data.tags) ? data.tags : {};
        })
        .fail(function () {
            if (seq !== udmTagRequestSeq) { return; }
            udmProductTags = {};
        })
        .always(function () {
            if (seq !== udmTagRequestSeq) { return; }
            recomputeConditionalState();
        });
}

// The tags across every currently checked product, as a set.
function udmCheckedTags() {
    const tags = {};

    $('#productCheckboxList input[name="cancelledProducts[]"]:checked').each(function () {
        const name = $(this).val();
        (udmProductTags[name] || []).forEach(function (tag) {
            tags[tag] = true;
        });
    });

    return tags;
}

// Item 2c's options are chosen by business type, per CS. "Other" gets the
// union: we do not know which applies, and offering too much is recoverable
// where offering nothing is not.
const UDM_KEEP_OPTIONS = {
    Restaurant: ['Pro Plan', 'Website Hosting'],
    Massage:    ['Booking System', 'Website Hosting'],
    Other:      ['Pro Plan', 'Booking System', 'Website Hosting'],
};

// Rendered from scratch on every call, like the product list: the business type
// can change after a plan bundle is already checked, and a stale option from
// the previous type must not survive it.
function udmRenderKeepList() {
    const $list   = $('#keepProductsList');
    const type    = $('#businessType').val() || 'Other';
    const options = UDM_KEEP_OPTIONS[type] || UDM_KEEP_OPTIONS.Other;

    // What was ticked before, so a re-render for an unrelated reason does not
    // silently discard the customer's answer.
    const wasChecked = {};
    $list.find('input[name="keepProducts[]"]:checked').each(function () {
        wasChecked[$(this).val()] = true;
    });

    const html = options.map(function (name, i) {
        const id      = 'keepProduct' + i;
        const checked = wasChecked[name] ? ' checked' : '';
        return `
            <div class="check-card">
                <input type="checkbox" id="${id}" name="keepProducts[]" value="${udmEscapeAttr(name)}"${checked}>
                <label for="${id}"><i class="bi bi-bookmark-check"></i> ${udmEscapeHtml(name)}</label>
            </div>`;
    }).join('');

    $list.html(html);
}

// ===== Conditional State =====
// Every conditional section is recomputed from the currently checked boxes,
// wholesale, on every change. Never incremental add/remove: ticking and then
// unticking a product must not leave a section behind, which is exactly the
// failure the upgrade form hit when updateSectionVisibility was not called on
// every change to the field that drives it.
//
// Tasks 7 and 8 extend this. It is the only place that reads udmCheckedTags().
function recomputeConditionalState() {
    const tags = udmCheckedTags();
    const hasAraya      = tags.araya       === true;
    const hasPlanBundle = tags.plan_bundle === true;

    // --- Item 2b: five Araya-specific reasons ---
    // Unchecking them on the way out keeps the payload honest: a reason
    // specific to the AI Receptionist must not be submitted by someone who has
    // since switched to cancelling something else.
    $('#arayaReasons').toggleClass('d-none', !hasAraya);
    if (!hasAraya) {
        $('#arayaReasons input[name="reasons[]"]').prop('checked', false);
    }

    // --- Item 3a: the stop-now warning, only when both conditions hold ---
    const stoppingNow = $('#effImmediate').is(':checked');
    $('#arayaImmediateWarning').toggleClass('d-none', !(hasAraya && stoppingNow));

    // --- Item 4: the Araya conditions block in the info modal ---
    $('#arayaConditions').toggleClass('d-none', !hasAraya);

    // --- Item 5: hide the two feedback questions the Araya form also asks ---
    //
    // Hiding alone is not enough where a field is required: a hidden required
    // field fails validation with nothing visible for the customer to fix,
    // which makes the form permanently unsubmittable. Neither of these two
    // carries required today, but the attribute is dropped explicitly so that
    // adding required to the markup later cannot reintroduce that trap silently.
    // (#overallRatingGroup is absent in staff mode; jQuery no-ops on it.)
    $('#improvementSuggestionGroup').toggleClass('d-none', hasAraya);
    $('#overallRatingGroup').toggleClass('d-none', hasAraya);

    if (hasAraya) {
        $('#improvementSuggestion').removeAttr('required').val('');
        $('#overallRating').removeAttr('required').val('');
        udmResetStars('overallRatingWidget', 'overallRatingLabel');
    }

    // --- Item 2c: what to keep, when a plan bundle is being cancelled ---
    //
    // Gated on plan_bundle specifically, not on the word "bundle": "Social
    // Media Marketing Bundle" is an add-on, and asking someone cancelling it
    // what plan they want to keep makes no sense.
    $('#keepProductsGroup').toggleClass('d-none', !hasPlanBundle);

    if (hasPlanBundle) {
        udmRenderKeepList();
    } else {
        // Clear it: an answer about keeping a plan must not be submitted by
        // someone who has since unchecked the plan.
        $('#keepProductsList').empty();
    }
}

// Clearing #overallRating leaves the stars lit, because the widget's state
// lives in CSS classes rather than in the input. Hiding the group makes that
// invisible, but unchecking the Araya product brings the group back still
// showing a rating the field no longer holds.
function udmResetStars(widgetId, labelId) {
    const widget = document.getElementById(widgetId);
    if (!widget) return;

    widget.querySelectorAll('.star').forEach(function (s) {
        s.classList.remove('active', 'selected');
    });

    const labelEl = labelId ? document.getElementById(labelId) : null;
    if (labelEl) labelEl.textContent = '';
}

$(function () {

    // --- Request type toggle ---
    $('input[name="requestType"]').on('change', updateSectionVisibility);
    // Delegated: the checkboxes are rendered after a lookup, so they do not
    // exist when this binds.
    $('#productCheckboxList').on('change', 'input[name="cancelledProducts[]"]', recomputeConditionalState);

    // The keep-list options are chosen by business type, so changing it has to
    // re-render them.
    $('#businessType').on('change', recomputeConditionalState);

    // --- Custom date toggle ---
    $('input[name="effectiveDate"]').on('change', function () {
        if ($('#effCustomDate').is(':checked')) {
            $('#customDateGroup').slideDown(200);
        } else {
            $('#customDateGroup').slideUp(200).find('input').val('');
        }
        // Item 3a's warning depends on this choice as well as on the checked
        // products, so it has to be recomputed here too.
        recomputeConditionalState();
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
