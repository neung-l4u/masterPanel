/* ============================================================
   Customer Email Lookup - shared by the upgrade and downgrade forms
   ============================================================ */

// Customer mode has no Monday project id to search on, so the email is the
// only key the customer reliably knows. This fills the shop fields and the
// real product list from the mirrored Monday data.
//
// Every failure path leaves the form exactly as the customer left it: the
// lookup is an accelerator, never a gate. A wrong or unrecognised email is not
// an error condition -- CS briefs the customer directly.
//
// Shared rather than copied: the four fixes below (a stale project id cleared
// when the email stops matching, only lookup-written fields cleared, a retry
// allowed after a 503, focus moved when the revert link removes itself) were
// expensive to find, and a copy guarantees the next one lands in only one file.
function upDownInitEmailLookup(options) {
    const opts = options || {};

    const $email        = $(opts.email);
    const $country      = $(opts.country);
    const $status       = $(opts.status);
    const $branchPicker = $(opts.branchPicker);
    const $branchList   = $(opts.branchList);

    // Optional: only the upgrade form has a manual product select to fall back
    // to. The downgrade form renders checkboxes from onProducts instead.
    const $list    = opts.productList       ? $(opts.productList)       : $();
    const $selWrap = opts.productSelectWrap ? $(opts.productSelectWrap) : $();
    const $select  = opts.productSelect     ? $(opts.productSelect)     : $();
    const $hidden  = opts.productHidden     ? $(opts.productHidden)     : $();

    const shopFields     = opts.shopFields     || {};
    const identityFields = opts.identityFields || {};
    const onProducts     = opts.onProducts     || function () {};
    const onChange       = opts.onChange       || function () {};

    let lookupTimer    = null;
    let lastQueried    = '';
    let inFlight       = null;
    // True only while the shop fields hold values this lookup wrote, so a
    // later clear can leave anything the customer typed by hand alone.
    let filledByLookup = false;
    // The shops a multi-branch match returned, awaiting the customer's choice.
    let matchedShops   = [];

    if (opts.disabled || !$email.length) return;

    function setStatus(html) {
        $status.html(html || '');
    }

    // Every selector this lookup is allowed to clear, as one jQuery set.
    function $shopFieldSet() {
        const sel = Object.keys(shopFields).map(k => shopFields[k]).filter(Boolean).join(', ');
        return sel ? $(sel) : $();
    }

    function $identityFieldSet() {
        const sel = Object.keys(identityFields).map(k => identityFields[k]).filter(Boolean).join(', ');
        return sel ? $(sel) : $();
    }

    // With no lookup result, the hidden field that actually submits must track
    // the manual select -- otherwise a customer who picks a plan by hand
    // submits an empty originalProduct.
    //
    // onChange runs after every change to the hidden field: on the upgrade form
    // it drives the Solo-to-Bundle gate and the comparison card, so without
    // this a section or card from a previous selection stays on screen while
    // validateForm has already stopped requiring its fields.
    // `shop` is the shop that is attached despite having no products to list --
    // a real match whose id is already filled in and whose name the status line
    // is naming. Passing it on matters: a consumer that reports "no shop" there
    // would lose the shop name for exactly the customer whose shop was found
    // but has nothing active on it. It is null only when no shop is attached.
    function useManualSelect(shop) {
        $list.addClass('d-none').empty();
        $selWrap.removeClass('d-none');
        $hidden.val($select.val() || '');
        onProducts([], shop || null);
        onChange();
    }

    function useLookupProducts(subscriptions, shop) {
        if (!subscriptions.length) {
            // Still a found shop: its id is filled and the status names it.
            useManualSelect(shop);
            return;
        }

        $selWrap.addClass('d-none');
        $hidden.val(subscriptions.map(s => s.name).join(', '));

        // Names only -- no prices in the customer-facing list. The endpoint does
        // not return them, so there is nothing to render even if this changed.
        if ($list.length) {
            const html = subscriptions.map(s => `
            <div class="subscription-item">
                <i class="bi bi-check-circle-fill"></i>
                <div>
                    <div class="sub-name">${escapeHtml(s.name)}</div>
                </div>
            </div>`).join('');

            $list.html(html).removeClass('d-none');
        }

        // Products before onChange, matching useManualSelect. onChange is where
        // a form recomputes which sections and required fields apply, and on a
        // form that renders its own product controls from onProducts that
        // recompute must not run while the previous shop's controls are still
        // on screen -- it would read the old shop's selection.
        onProducts(subscriptions, shop);
        onChange();
    }

    // The shop name, owner name and product names come from Monday and are
    // rendered as HTML, so they are escaped here rather than trusted.
    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function fillShop(shop) {
        Object.keys(shopFields).forEach(function (key) {
            $(shopFields[key]).val(shop[key] || '');
        });
        // These are hidden inputs in customer mode and have always submitted
        // empty; populating them means the webhook payload finally carries a
        // real project id for a customer submission.
        Object.keys(identityFields).forEach(function (key) {
            $(identityFields[key]).val(shop[key] || '');
        });
        filledByLookup = true;
    }

    // Clears only what a lookup actually wrote. Clearing unconditionally would
    // discard a shop name, owner and phone the customer typed by hand -- the
    // lookup is an accelerator, so it may not destroy their own input.
    function clearShopFields() {
        if (!filledByLookup) return;
        $shopFieldSet().val('');
        $identityFieldSet().val('');
        filledByLookup = false;
    }

    // The Monday project id is the dangerous one to leave behind: a stale id
    // from a previous email would attach this request to the wrong shop in
    // Monday while the form shows the customer's new address.
    //
    // The flag is lowered too: the shop name, owner and phone are left on screen
    // for the customer to correct, which makes them theirs to keep. Leaving the
    // flag raised would let a later country change wipe fields they had since
    // edited by hand.
    function clearShopIdentity() {
        if (!filledByLookup) return;
        $identityFieldSet().val('');
        filledByLookup = false;
    }

    function revertToManual() {
        lastQueried = '';
        clearShopFields();
        hideBranchPicker();
        useManualSelect();
        setStatus('');
    }

    function hideBranchPicker() {
        $branchPicker.addClass('d-none');
        $branchList.empty();
        matchedShops = [];
    }

    // One owner often runs several branches under one email: 69 emails match
    // more than one shop, and in 15 of those the first by name has no products
    // while a sibling does. Picking one silently showed "Found <shop>" with an
    // empty product list and, worse, submitted that shop's Monday project id.
    function showBranchPicker(shops) {
        matchedShops = shops;

        const html = shops.map(function (s, i) {
            const n     = s.subscriptions ? s.subscriptions.length : 0;
            const count = n === 1 ? '1 current product' : n + ' current products';
            return `
                <label class="branch-option">
                    <input type="radio" name="branchChoice" value="${i}">
                    <span class="branch-option-body">
                        <span class="branch-option-name">${escapeHtml(s.shopName)}</span>
                        <span class="branch-option-meta">${count}</span>
                    </span>
                </label>`;
        }).join('');

        $branchList.html(html);
        $branchPicker.removeClass('d-none');
    }

    function applyShop(shop, stale) {
        fillShop(shop);
        useLookupProducts(shop.subscriptions || [], shop);

        const name = escapeHtml(shop.shopName);
        let msg = stale
            ? `<span class="els-stale"><i class="bi bi-exclamation-circle"></i> Using <strong>${name}</strong>. These details may be out of date; our team will confirm them.`
            : `<span class="els-found"><i class="bi bi-check-circle"></i> Using <strong>${name}</strong>.`;

        // A found shop with no products must say so. Silently showing the
        // hardcoded select is the "did the lookup work?" ambiguity this feature
        // exists to remove.
        if (!shop.subscriptions || shop.subscriptions.length === 0) {
            msg += ' We found no active products on it, so please choose below.';
        }

        msg += ' You can edit anything below.</span>'
             + '<a href="#" class="els-revert" id="emailLookupRevert">Not my shop?</a>';
        setStatus(msg);
    }

    function onFound(data) {
        const shops = (data.shops && data.shops.length) ? data.shops : [data.shop];

        if (shops.length > 1) {
            // Fill nothing yet: which branch is theirs decides the project id.
            showBranchPicker(shops);
            useManualSelect();
            setStatus('<span class="els-neutral"><i class="bi bi-diagram-3"></i> This email is used by '
                + shops.length + ' shops. Please choose which one is yours.</span>'
                + '<a href="#" class="els-revert" id="emailLookupRevert">None of these?</a>');
            return;
        }

        hideBranchPicker();
        applyShop(shops[0], data.stale);
    }

    function onNotFound() {
        // Deliberately not alarming: an unrecognised email is expected, and CS
        // reconciles it. Nothing the customer typed by hand is touched.
        //
        // The project id must go, though: this email matched no shop, so a stale
        // id from a previous match would attach the request to the wrong shop.
        clearShopIdentity();
        useManualSelect();
        setStatus('<span class="els-neutral">We couldn\'t prefill your details from this email. Please fill in the form and our team will confirm everything.</span>');
    }

    function runLookup() {
        const country = ($country.val() || '').trim();
        const email   = ($email.val() || '').trim();

        if (!country || !email || email.indexOf('@') === -1) {
            // Mid-edit, so no lookup can be in flight for what is on screen now.
            // Abort it and drop the project id: leaving either would let an older
            // response land, or submit a previous shop's id under a new address.
            if (inFlight) inFlight.abort();
            clearShopIdentity();
            // The shop id has just gone, so a consumer rendering its own product
            // controls must not keep showing the previous shop's products as
            // selectable. The upgrade form's own list is deliberately left alone
            // here, exactly as before.
            onProducts([], null);
            return;
        }

        const key = country + '|' + email.toLowerCase();
        if (key === lastQueried) return;
        lastQueried = key;

        if (inFlight) inFlight.abort();

        setStatus('<span class="els-neutral"><span class="spinner-border spinner-border-sm"></span> Looking up your shop...</span>');

        inFlight = $.getJSON('../assets/API/lookupByEmail.php', { country, email })
            .done(function (data) {
                if (data && data.found) onFound(data);
                else onNotFound();
            })
            .fail(function (jqXHR, textStatus) {
                if (textStatus === 'abort') return;
                // Forget the key so the same address can be retried: a 503 or a
                // timeout should not kill the lookup until the email changes.
                lastQueried = '';
                // A failed lookup must be indistinguishable from no lookup, but
                // a previous match's project id cannot be left to submit.
                clearShopIdentity();
                useManualSelect();
                setStatus('');
            })
            .always(function () { inFlight = null; });
    }

    $email.on('blur', runLookup);

    $email.on('input', function () {
        clearTimeout(lookupTimer);
        lookupTimer = setTimeout(runLookup, 400);
    });

    // A country change invalidates the result, mirroring how staff mode clears
    // its fields when the country changes.
    $country.on('change', function () {
        revertToManual();
        runLookup();
    });

    // The select -> hidden mirroring itself lives in the form's DOM-ready block,
    // because the form depends on it with or without a lookup. This only has to
    // stop a stale manual choice overwriting a successful lookup result.
    $select.on('change', function () {
        if ($selWrap.hasClass('d-none')) {
            // A lookup is showing; its product list owns the hidden field.
            $(this).val('');
        }
    });

    // Choosing a branch is what fills the form, so the project id can only ever
    // be the shop the customer confirmed.
    $branchList.on('change', 'input[name="branchChoice"]', function () {
        const shop = matchedShops[parseInt($(this).val(), 10)];
        if (!shop) return;
        applyShop(shop, false);
    });

    $status.on('click', '#emailLookupRevert', function (e) {
        e.preventDefault();
        revertToManual();
        // This link lives inside the status area, which the revert empties, so
        // activating it destroys the focused element and focus falls to <body>.
        // Send it to the email field, which is what the customer edits next.
        $email.trigger('focus');
    });

    // Handed to the caller so another control that attaches a shop -- the
    // staff project search, which sets the project id directly -- can take
    // ownership back. Without it the lookup still believes it owns the shop
    // fields: the form would show one shop's name and status while submitting
    // another shop's project id, and a later failed lookup would clear the id
    // the staff member deliberately chose.
    return {
        release: function () {
            lastQueried = '';
            filledByLookup = false;
            hideBranchPicker();
            setStatus('');
        }
    };
}
