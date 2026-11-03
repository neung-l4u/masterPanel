// The smallest country set worth falling back on, used only when Monday and
// its cache are both unreachable. Country is not passed to the signup form,
// so this list only has to let a rep finish recording the lead.
const PRESIGNUP_FALLBACK_COUNTRIES = ['AU', 'CA', 'NZ', 'UK', 'US', 'TH'];

// Used only if Monday and its cache are both unreachable. Deliberately the
// smallest set that still lets a rep finish a lead; the labels match Monday.
const PRESIGNUP_FALLBACK_OPTIONS = {
    contactChannel: [{label: 'Call'}, {label: 'Chat'}],
    customerType: [{label: 'New Customer'}, {label: 'Existing Customer'}],
    shopType: [{label: 'Massage'}, {label: 'Restaurant'}, {label: 'Other'}],
    country: PRESIGNUP_FALLBACK_COUNTRIES.map(code => ({label: code})),
    leadSource: [{label: 'Cold Call'}, {label: 'Other'}]
};

function preSignupFillSelect($select, options) {
    const current = $select.val();
    $select.find('option:not([disabled])').remove();
    options.forEach(option => {
        $select.append($('<option>').attr('value', option.label).text(option.label));
    });
    // Keep whatever the rep had already chosen if it still exists.
    if (current && $select.find(`option[value="${current}"]`).length) {
        $select.val(current);
    }
}

function preSignupLoadOptions() {
    const $source = $('#optionsSource');

    $.ajax({
        url: '../models/mondayOptions.php',
        type: 'GET',
        dataType: 'json'
    }).done(res => {
        if (!res || !res.success || !res.data) {
            preSignupUseFallback($source);
            return;
        }

        $('select[data-options]').each(function () {
            const key = $(this).data('options');
            if (res.data[key]) {
                preSignupFillSelect($(this), res.data[key]);
            } else if (PRESIGNUP_FALLBACK_OPTIONS[key]) {
                preSignupFillSelect($(this), PRESIGNUP_FALLBACK_OPTIONS[key]);
            }
        });

        const fromMonday = res.source === 'monday';
        $source
            .removeClass('text-bg-light text-bg-warning text-bg-success')
            .addClass(fromMonday ? 'text-bg-success' : 'text-bg-light')
            .text(fromMonday ? 'Options live from Monday' : 'Options from cache');
    }).fail(() => {
        preSignupUseFallback($source);
    });
}

function preSignupUseFallback($source) {
    $('select[data-options]').each(function () {
        const key = $(this).data('options');
        if (PRESIGNUP_FALLBACK_OPTIONS[key]) {
            preSignupFillSelect($(this), PRESIGNUP_FALLBACK_OPTIONS[key]);
        }
    });
    $source
        .removeClass('text-bg-light text-bg-success')
        .addClass('text-bg-warning')
        .text('Monday unreachable - limited options');
}

// Validation mirrors models/preSignupForm.php. The server decides; this is
// here so the rep sees the problem without a round trip.
function preSignupValidate() {
    let valid = true;

    const markInvalid = ($el, bad) => {
        $el.toggleClass('is-invalid', bad);
        if (bad) valid = false;
    };

    ['shopName', 'country', 'shopType', 'customerType', 'leadSource', 'contactChannel'].forEach(id => {
        const $el = $('#' + id);
        markInvalid($el, !$el.val() || !String($el.val()).trim());
    });

    // Whoever picks the lead up has to be able to reach the shop somehow, but
    // which way is not prescribed: a chat lead may come through Respond.io,
    // Facebook, TikTok or Line, and a cold call has a number and little else.
    const contactPoints = ['#mobNumber', '#shopNumber', '#email', '#conversationLink']
        .filter(selector => $(selector).val().trim());

    ['#mobNumber', '#shopNumber', '#conversationLink'].forEach(selector => {
        markInvalid($(selector), contactPoints.length === 0);
    });
    $('#contactNote').toggleClass('text-danger', contactPoints.length === 0);

    const email = $('#email').val().trim();
    markInvalid($('#email'), email !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email));

    return valid;
}

function preSignupShowLink(res) {
    const link = res.link || '';
    const ref = res.ref || '';

    $('#result').html(`
        <div class="card border-success mb-3">
            <div class="card-body">
                <h2 class="h5 text-success mb-3"><i class="bi bi-check-circle-fill"></i> Pre sign-up saved</h2>
                <p class="mb-2">Send this link to the customer. It opens the signup form with
                   everything you entered already filled in, and can be opened more than once.</p>
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="preleadLink" value="${link}" readonly>
                    <button class="btn btn-outline-primary" type="button" id="cmdCopyLink">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
                <p class="text-muted small mb-0">Reference <code>${ref}</code></p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="preSignupForm.php" class="btn btn-primary">Open another lead</a>
            <a href="entries.php" class="btn btn-outline-dark">View entries</a>
        </div>
    `);

    $('#cmdCopyLink').on('click', function () {
        const $input = $('#preleadLink');
        $input.trigger('select');

        const done = () => {
            $(this).html('<i class="bi bi-check2"></i> Copied');
            window.setTimeout(() => {
                $(this).html('<i class="bi bi-clipboard"></i> Copy');
            }, 2000);
        };

        // navigator.clipboard needs a secure context; the form may be served
        // over plain HTTP internally, so fall back to the old command.
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText($input.val()).then(done, () => {
                document.execCommand('copy');
                done();
            });
        } else {
            document.execCommand('copy');
            done();
        }
    });
}

$(function () {
    preSignupLoadOptions();

    // Clear the "needs a contact point" warning as soon as one is given.
    $('#mobNumber, #shopNumber, #email, #conversationLink').on('input', function () {
        if ($(this).val().trim()) {
            $('#mobNumber, #shopNumber, #conversationLink').removeClass('is-invalid');
            $('#contactNote').removeClass('text-danger');
        }
    });

    $('#preSignupForm').on('submit', function (e) {
        e.preventDefault();

        const $result = $('#result');
        const $submit = $('#cmdSubmit');

        if (!preSignupValidate()) {
            $result.html('<div class="alert alert-danger">Please complete the highlighted fields.</div>');
            return;
        }

        const formData = $(this).serializeArray().reduce((obj, item) => {
            obj[item.name] = item.value;
            return obj;
        }, {});

        $result.html('<div class="alert alert-warning"><img src="../assets/img/loading.gif" alt="" height="24"> Saving&hellip;</div>');
        $submit.prop('disabled', true);

        $.ajax({
            url: '../models/preSignupForm.php',
            type: 'POST',
            data: {payload: JSON.stringify(formData)},
            dataType: 'json'
        }).done(res => {
            if (res && res.result === 'success') {
                preSignupShowLink(res);
                $submit.hide();
            } else {
                $result.html(`<div class="alert alert-danger"><i class="bi bi-x-circle-fill"></i> ${(res && res.msg) || 'Could not save. Please try again.'}</div>`);
                $submit.prop('disabled', false);
            }
        }).fail(xhr => {
            // The row may still have been written, so the rep is told to check
            // entries rather than blindly submitting the same lead again.
            let msg = 'Could not save. Please try again.';
            try {
                const parsed = JSON.parse(xhr.responseText);
                if (parsed && parsed.msg) msg = parsed.msg;
            } catch (err) { /* keep the generic message */ }
            $result.html(`<div class="alert alert-danger"><i class="bi bi-x-circle-fill"></i> ${msg}
                          <a href="entries.php" class="alert-link">Check entries</a> before retrying.</div>`);
            $submit.prop('disabled', false);
        });
    });
});
