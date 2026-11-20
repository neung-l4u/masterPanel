/**
 * Fills this form in from a pre sign-up, when opened as ?prelead=0000001.
 *
 * A sales rep collected these details from the customer on a chat or a call,
 * so the customer should not be asked for them a second time. The reference is
 * all that travels in the link; the values are fetched from the pre sign-up
 * module, which keeps the link short and keeps the customer's own details out
 * of their address bar and browser history.
 *
 * Nothing here is required for the form to work. If the reference is unknown,
 * or the lookup fails, the customer simply fills the form in themselves.
 */
(function () {
    const PRELEAD_LOOKUP_URL = '/modules/preSignup/models/lookup.php';

    const params = new URLSearchParams(window.location.search);
    const ref = (params.get('prelead') || '').trim();

    if (!ref) {
        return;
    }

    /**
     * Set one field and let the form react to it.
     *
     * Several of these inputs drive the rest of the form: country decides
     * which currency and which backup form appear, customer type reveals its
     * own section. Assigning .value alone would leave those handlers unaware,
     * so each change is dispatched the way a person typing would.
     */
    function setField(id, value) {
        const $el = $('#' + id);
        if (!$el.length || value === undefined || value === null || value === '') {
            return false;
        }

        // Never overwrite something the customer has already typed, which can
        // happen if they reload the page part-way through filling it in.
        const current = String($el.val() || '').trim();
        if (current !== '') {
            return false;
        }

        if ($el.is('select')) {
            // Only select a value the dropdown actually offers; a missing
            // option would silently leave the select blank.
            if (!$el.find('option').filter(function () { return this.value === value; }).length) {
                return false;
            }
        }

        $el.val(value).trigger('change');
        return true;
    }

    function applyPrefill(data) {
        const prefill = (data && data.prefill) || {};

        // The signup form's own field ids, which is what lookup.php keys on.
        const applied = Object.keys(prefill).filter(function (id) {
            return setField(id, prefill[id]);
        });

        if (!applied.length) {
            return;
        }

        // Keep the reference on the submission so this signup can be tied
        // back to the pre sign-up row it came from.
        if (!$('#preleadRef').length) {
            $('<input>')
                .attr({type: 'hidden', id: 'preleadRef', name: 'preleadRef'})
                .val(data.ref || ref)
                .appendTo($('form').first());
        }

        // Tell the customer why their details are already there, so a
        // pre-filled form does not look like someone else's data.
        const context = (data && data.context) || {};
        let message = '<strong>Welcome back.</strong> We have filled in the details you gave us. '
            + 'Please check them and complete the rest of the form.';

        // Country, state and city are recorded on the pre sign-up but not
        // filled in here: this form offers fewer countries than the lead board
        // knows, and its state and city dropdowns feed each other, so the
        // customer selects those themselves. Naming what they told us lets
        // them re-pick it without having to remember.
        const toPick = [];
        if (context.country) toPick.push('country');
        if (context.state) toPick.push('state (' + context.state + ')');
        if (context.citySuburb) toPick.push('city (' + context.citySuburb + ')');

        if (toPick.length) {
            message += ' <span class="text-muted">Please also select your '
                + toPick.join(', ') + ' below.</span>';
        }

        $('main').first().prepend(
            $('<div>')
                .addClass('alert alert-info')
                .css({margin: '12px auto', maxWidth: '900px'})
                .html(message)
        );
    }

    $(function () {
        // Runs after the form's own init, so the fields exist and are visible
        // and the handlers listening for change are already bound.
        $.ajax({
            url: PRELEAD_LOOKUP_URL,
            type: 'GET',
            data: {ref: ref},
            dataType: 'json'
        }).done(function (res) {
            if (res && res.success) {
                applyPrefill(res);
            }
            // An unknown or cancelled reference is not the customer's problem:
            // they get the ordinary empty form.
        }).fail(function () {
            // Logged for us, invisible to the customer.
            if (window.console) {
                console.warn('prelead lookup failed for ' + ref);
            }
        });
    });
})();
