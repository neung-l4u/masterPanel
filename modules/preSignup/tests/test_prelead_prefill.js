/* ============================================================
   Tests: prefilling the signup form from a pre sign-up link
   node tests/test_prelead_prefill.js

   Exercises modules/signup/assets/js/preleadPrefill.js against a stub of
   jQuery and of the signup form's real fields. The script is an IIFE that
   runs on load, so each case builds a fresh fake page, loads the script, and
   inspects what it did.
   ============================================================ */

const assert = require('assert');
const fs = require('fs');
const path = require('path');

const SCRIPT = path.join(
    __dirname, '..', '..', 'signup', 'assets', 'js', 'preleadPrefill.js'
);
const source = fs.readFileSync(SCRIPT, 'utf8');

let passed = 0;
function check(label, fn) {
    fn();
    passed++;
    console.log('  ok  ' + label);
}

/**
 * A fake page: the signup fields we care about, a jQuery stand-in, and a
 * stubbed ajax that answers with whatever the test supplies.
 */
function makePage(options) {
    const opts = options || {};

    // id -> {value, tag, options, changes}
    const fields = {};
    const add = (id, tag, selectOptions) => {
        fields[id] = {
            value: '',
            tag: tag || 'input',
            options: selectOptions || null,
            changes: 0
        };
    };

    add('shopName');
    add('first_name');
    add('last_name');
    add('email');
    add('mobile');
    add('shopNumber');
    add('webURL');
    add('streetAddress1');
    add('zip');
    // On the real form state/city are a dependent chain and are not
    // prefilled; kept here so a regression that starts writing to them fails.
    add('state', 'select', ['', 'VIC', 'NSW']);
    add('formCustomerType', 'select', ['', 'New Customer', 'Existing Customer']);

    Object.assign(fields, opts.extraFields || {});
    if (opts.presetValues) {
        Object.keys(opts.presetValues).forEach(id => {
            if (fields[id]) fields[id].value = opts.presetValues[id];
        });
    }

    const appended = [];
    const prepended = [];
    let readyFn = null;
    let ajaxCall = null;

    function wrap(id) {
        const field = fields[id];

        return {
            length: field ? 1 : 0,
            is: sel => Boolean(field) && sel === 'select' && field.tag === 'select',
            val(v) {
                if (v === undefined) return field ? field.value : undefined;
                if (field) field.value = v;
                return this;
            },
            trigger(event) {
                if (field && event === 'change') field.changes++;
                return this;
            },
            find(sel) {
                // Only '#id option' style lookups are used, for selects.
                if (!field || sel !== 'option') return {length: 0, filter: () => ({length: 0})};
                const list = (field.options || []).map(value => ({value}));
                return {
                    length: list.length,
                    filter(fn) {
                        const kept = list.filter(function (o) { return fn.call(o); });
                        return {length: kept.length};
                    }
                };
            },
            attr() { return this; },
            addClass() { return this; },
            css() { return this; },
            html(h) { this._html = h; return this; },
            appendTo(target) { appended.push({el: this, target}); return this; },
            prepend(el) { prepended.push(el); return this; },
            first() { return this; }
        };
    }

    const $ = function (arg) {
        if (typeof arg === 'function') { readyFn = arg; return; }
        if (typeof arg === 'string') {
            if (arg.startsWith('#')) return wrap(arg.slice(1));
            if (arg.startsWith('<')) return wrap(null);
            // 'form', 'main', …
            return wrap(null);
        }
        return wrap(null);
    };

    $.ajax = function (config) {
        ajaxCall = config;
        const chain = {
            done(fn) { chain._done = fn; return chain; },
            fail(fn) { chain._fail = fn; return chain; }
        };
        // Resolved by the test, after the chain is built.
        setImmediate(() => {
            if (opts.fail) {
                if (chain._fail) chain._fail({});
            } else if (chain._done) {
                chain._done(opts.response);
            }
        });
        return chain;
    };

    const sandbox = {
        $: $,
        jQuery: $,
        URLSearchParams: URLSearchParams,
        window: {location: {search: opts.search === undefined ? '?prelead=0000001' : opts.search}, console: null},
        console: {warn() {}},
        setImmediate: setImmediate
    };
    sandbox.window.URLSearchParams = URLSearchParams;

    return {
        fields,
        appended,
        prepended,
        run() {
            const vm = require('vm');
            const context = vm.createContext(sandbox);
            vm.runInContext(source, context);
            if (readyFn) readyFn();
            return new Promise(resolve => setImmediate(() => setImmediate(resolve)));
        },
        get ajaxCall() { return ajaxCall; }
    };
}

const fullResponse = {
    success: true,
    ref: 'prelead-0000001',
    prefill: {
        shopName: 'Siam Thai Massage',
        first_name: 'Somchai',
        last_name: 'Jaidee',
        email: 'somchai@example.com',
        mobile: '0412345678',
        shopNumber: '0398765432',
        webURL: 'https://siam.example',
        streetAddress1: '12 Smith St',
        zip: '3121',
        formCustomerType: 'New Customer'
    },
    context: {
        country: 'AU', citySuburb: 'Richmond', state: 'VIC',
        shopType: 'Massage', leadSource: 'Facebook / Meta'
    }
};

console.log('preleadPrefill — reading the reference from the URL');

(async () => {
    // --- no reference: the script must do nothing at all ------------------
    {
        const page = makePage({search: '', response: fullResponse});
        await page.run();
        check('no ?prelead= makes no lookup call', () => {
            assert.strictEqual(page.ajaxCall, null);
        });
        check('no ?prelead= fills nothing', () => {
            assert.strictEqual(page.fields.shopName.value, '');
        });
    }

    // --- the happy path ---------------------------------------------------
    {
        const page = makePage({response: fullResponse});
        await page.run();

        check('looks the reference up at the pre sign-up module', () => {
            assert.ok(page.ajaxCall, 'expected an ajax call');
            assert.strictEqual(page.ajaxCall.url, '/modules/preSignup/models/lookup.php');
            assert.strictEqual(page.ajaxCall.data.ref, '0000001');
        });

        check('fills the text fields', () => {
            assert.strictEqual(page.fields.shopName.value, 'Siam Thai Massage');
            assert.strictEqual(page.fields.first_name.value, 'Somchai');
            assert.strictEqual(page.fields.email.value, 'somchai@example.com');
            assert.strictEqual(page.fields.zip.value, '3121');
            assert.strictEqual(page.fields.shopNumber.value, '0398765432');
            assert.strictEqual(page.fields.webURL.value, 'https://siam.example');
            assert.strictEqual(page.fields.streetAddress1.value, '12 Smith St');
        });

        check('leaves state and city for the customer to choose', () => {
            assert.strictEqual(page.fields.state.value, '');
            assert.strictEqual(page.fields.state.changes, 0);
        });

        check('fills a select whose option exists', () => {
            assert.strictEqual(page.fields.formCustomerType.value, 'New Customer');
        });

        check('fires change so the form reacts to each value', () => {
            assert.strictEqual(page.fields.shopName.changes, 1);
            assert.strictEqual(page.fields.formCustomerType.changes, 1);
        });

        check('keeps the reference on the form for submission', () => {
            assert.strictEqual(page.appended.length, 1);
        });

        check('explains to the customer why the form is filled in', () => {
            assert.strictEqual(page.prepended.length, 1);
            assert.ok(/Welcome back/.test(page.prepended[0]._html));
        });

        check('names what the customer must still pick themselves', () => {
            const html = page.prepended[0]._html;
            assert.ok(/country/i.test(html), 'should mention country');
            assert.ok(/VIC/.test(html), 'should quote the state they gave');
            assert.ok(/Richmond/.test(html), 'should quote the city they gave');
        });
    }

    // --- a select with no matching option must stay untouched -------------
    {
        const page = makePage({
            response: {
                success: true,
                ref: 'prelead-0000001',
                prefill: {formCustomerType: 'Nonexistent Type', shopName: 'S'},
                context: {}
            }
        });
        await page.run();
        check('leaves a select blank rather than setting an absent option', () => {
            assert.strictEqual(page.fields.formCustomerType.value, '');
            assert.strictEqual(page.fields.formCustomerType.changes, 0);
        });
        check('still fills the fields it can', () => {
            assert.strictEqual(page.fields.shopName.value, 'S');
        });
    }

    // --- never overwrite what the customer already typed ------------------
    {
        const page = makePage({
            presetValues: {shopName: 'Typed By Customer'},
            response: fullResponse
        });
        await page.run();
        check('does not overwrite a field the customer already filled', () => {
            assert.strictEqual(page.fields.shopName.value, 'Typed By Customer');
            assert.strictEqual(page.fields.shopName.changes, 0);
        });
        check('fills the other fields regardless', () => {
            assert.strictEqual(page.fields.first_name.value, 'Somchai');
        });
    }

    // --- empty values must not blank the form's own defaults --------------
    {
        const page = makePage({
            response: {
                success: true, ref: 'prelead-0000001',
                prefill: {shopName: 'S', email: '', mobile: null},
                context: {}
            }
        });
        await page.run();
        check('skips empty and null values', () => {
            assert.strictEqual(page.fields.email.value, '');
            assert.strictEqual(page.fields.email.changes, 0);
            assert.strictEqual(page.fields.mobile.changes, 0);
        });
    }

    // --- an unknown reference is not the customer's problem ---------------
    {
        const page = makePage({
            response: {success: false, error: 'not_found'}
        });
        await page.run();
        check('an unsuccessful lookup fills nothing and shows no notice', () => {
            assert.strictEqual(page.fields.shopName.value, '');
            assert.strictEqual(page.prepended.length, 0);
        });
    }

    // --- a failed request must leave the form usable ----------------------
    {
        const page = makePage({fail: true});
        await page.run();
        check('a failed lookup leaves the form empty and usable', () => {
            assert.strictEqual(page.fields.shopName.value, '');
            assert.strictEqual(page.prepended.length, 0);
        });
    }

    // --- the reference may be written either way --------------------------
    {
        const page = makePage({search: '?prelead=prelead-0000001', response: fullResponse});
        await page.run();
        check('accepts a prelead-0000001 style reference too', () => {
            assert.strictEqual(page.ajaxCall.data.ref, 'prelead-0000001');
        });
    }

    // --- other query parameters must not confuse it -----------------------
    {
        const page = makePage({search: '?testMode=1&prelead=0000042&invoice=1', response: fullResponse});
        await page.run();
        check('picks the reference out from among other parameters', () => {
            assert.strictEqual(page.ajaxCall.data.ref, '0000042');
        });
    }

    console.log('\n' + passed + ' checks passed');
})().catch(err => {
    console.error('\nFAILED: ' + err.message);
    process.exit(1);
});
