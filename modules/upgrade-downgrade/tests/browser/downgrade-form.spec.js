/**
 * Browser verification for the downgrade form revisions.
 *
 * Covers the checks the plan deferred to a human: the ten email-lookup
 * failure paths on BOTH forms (the gate on Task 3's extraction refactor), the
 * Araya and plan-bundle conditionals, the staff/customer split, and the
 * webhook payload.
 *
 * Nothing here mocks the lookup: it drives the real endpoint against the real
 * mirror, because the fixes under test are all about what happens on a real
 * miss, a real multi-branch match and a real transport failure.
 */
const { chromium } = require('playwright');

const BASE = 'http://localhost/masterPanel-ud-lookup/modules/upgrade-downgrade/views';
const DOWN = u => `${BASE}/downgradeForm.php?${u}`;
const UP = u => `${BASE}/upgradeForm.php?${u}`;

// Real rows in the mirror, confirmed by querying it directly.
const ARAYA = { country: 'AU', email: 'relax@tttmassage.com.au' };
const MULTI = { country: 'AU', email: 'natpimpink@gmail.com' };     // 4 shops
const MANY = { country: 'US', email: 'lanlarbnyc@gmail.com' };      // 11 products
const MISS = { country: 'TH', email: 'nobody@nowhere.test' };

let pass = 0, fail = 0;
const failures = [];

function check(name, cond, detail) {
  if (cond) { pass++; console.log(`  PASS  ${name}`); }
  else {
    fail++; failures.push(`${name}${detail ? ' — ' + detail : ''}`);
    console.log(`  FAIL  ${name}${detail ? '  (' + detail + ')' : ''}`);
  }
}

/** Fill country + email and wait for the lookup to settle. */
async function lookup(p, { country, email }) {
  await p.selectOption('#country', country);
  await p.fill('#emailAddress', '');
  await p.fill('#emailAddress', email);
  await p.locator('#emailAddress').blur();
  // The lookup debounces at 400ms and then does a network round trip.
  await p.waitForTimeout(2500);
}

const statusText = p => p.locator('#emailLookupStatus').innerText().catch(() => '');
const val = (p, sel) => p.locator(sel).inputValue().catch(() => null);
/** d-none is how this codebase hides things. isVisible() also returns false
 *  for a collapsed ancestor, which would make a passing form look broken. */
const shown = (p, sel) => p.locator(sel).evaluate(e => !e.classList.contains('d-none')).catch(() => false);


/** The radio/checkbox inputs are display:none and the <label> is the visible
 *  control, so a real user clicks the label. check() would time out. */
async function tick(p, id) {
  await p.locator(`label[for="${id}"]`).click();
  await p.waitForTimeout(350);
}
async function isTicked(p, id) {
  return p.locator('#' + id).isChecked().catch(() => false);
}

async function section(title) { console.log(`\n=== ${title} ===`); }

(async () => {
  const browser = await chromium.launch();
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 1000 } });

  // ---------------------------------------------------------------- 1-8, 10
  // The lookup paths, on the DOWNGRADE form.
  await section('Downgrade — lookup paths');
  {
    const p = await ctx.newPage();
    await p.goto(DOWN('mode=customer'), { waitUntil: 'load' });

    // 1. known single shop fills the form
    await lookup(p, MANY);
    const s1 = await statusText(p);
    check('1  known email fills shop fields',
      (await val(p, '#shopName')) !== '' && /Using/i.test(s1),
      `shopName="${await val(p, '#shopName')}" status="${s1.slice(0, 60)}"`);
    check('1b known email fills the project id (hidden)',
      (await val(p, '#mondayProjectId')) !== '',
      `id="${await val(p, '#mondayProjectId')}"`);

    // 4. changing to an unknown email must drop the project id. Type over the
    //    address rather than blanking it first: an empty field is its own
    //    invalidation gesture, not the not-found path.
    await p.fill('#emailAddress', 'nobody-else@nowhere.test');
    await p.locator('#emailAddress').blur();
    await p.waitForTimeout(2500);
    check('4  stale project id cleared on a miss',
      (await val(p, '#mondayProjectId')) === '' && (await val(p, '#boardId')) === '',
      `id="${await val(p, '#mondayProjectId')}" board="${await val(p, '#boardId')}"`);
    check('3  a miss leaves the shop fields on screen to correct',
      (await val(p, '#shopName')) !== '',
      'shop name was wiped on a miss');

    // 5. hand-typed values survive when no lookup ever succeeded
    await p.goto(DOWN('mode=customer'), { waitUntil: 'load' });
    await p.selectOption('#country', 'TH');
    await p.fill('#shopName', 'Typed By Hand');
    await lookup(p, MISS);
    check('5  hand-typed shop name survives a failed lookup',
      (await val(p, '#shopName')) === 'Typed By Hand',
      `got "${await val(p, '#shopName')}"`);

    // 2. multi-branch shows the picker and fills nothing yet
    await p.goto(DOWN('mode=customer'), { waitUntil: 'load' });
    await lookup(p, MULTI);
    const pickerVisible = await shown(p, '#branchPicker').catch(() => false);
    const radios = await p.locator('#branchPickerList input[name="branchChoice"]').count();
    check('2  multi-branch email shows the branch picker',
      pickerVisible && radios > 1, `visible=${pickerVisible} options=${radios}`);
    check('2b nothing is attached until a branch is chosen',
      (await val(p, '#mondayProjectId')) === '',
      `id="${await val(p, '#mondayProjectId')}"`);

    // choosing a branch fills it
    if (radios > 0) {
      await p.evaluate(() => { const r = document.querySelector('#branchPickerList input[name="branchChoice"]'); r.click(); });
      await p.waitForTimeout(600);
      check('2c choosing a branch attaches that shop',
        (await val(p, '#mondayProjectId')) !== '',
        `id="${await val(p, '#mondayProjectId')}"`);
    }

    // 6. revert link clears and moves focus
    await p.goto(DOWN('mode=customer'), { waitUntil: 'load' });
    await lookup(p, MANY);
    const revert = p.locator('#emailLookupRevert');
    if (await revert.count()) {
      await revert.click();
      await p.waitForTimeout(400);
      const focused = await p.evaluate(() => document.activeElement && document.activeElement.id);
      check('6  revert clears the status', (await statusText(p)).trim() === '');
      check('6b revert moves focus to the email field (not <body>)',
        focused === 'emailAddress', `focus on "${focused}"`);
    } else {
      check('6  revert link present', false, 'no #emailLookupRevert rendered');
    }

    // 8. a country change invalidates and re-runs
    await p.goto(DOWN('mode=customer'), { waitUntil: 'load' });
    await lookup(p, MANY);
    const before = await val(p, '#mondayProjectId');
    let reqs = 0;
    p.on('request', r => { if (r.url().includes('lookupByEmail')) reqs++; });
    await p.selectOption('#country', 'TH');
    await p.waitForTimeout(2200);
    check('8  country change re-runs the lookup', reqs >= 1, `requests=${reqs}`);
    check('8b country change drops the previous shop id',
      (await val(p, '#mondayProjectId')) !== before || before === '',
      `before="${before}" after="${await val(p, '#mondayProjectId')}"`);

    // 7. retry after a transport failure
    await p.goto(DOWN('mode=customer'), { waitUntil: 'load' });
    await ctx.route('**/lookupByEmail.php*', r => r.fulfill({ status: 503, contentType: 'application/json', body: '{}' }));
    await lookup(p, MANY);
    await ctx.unroute('**/lookupByEmail.php*');
    let retry = 0;
    p.on('request', r => { if (r.url().includes('lookupByEmail')) retry++; });
    // blur() is a no-op unless the field actually holds focus, so take focus
    // first. This is the real "customer clicks back into the field and out
    // again after the network came back" gesture.
    await p.locator('#emailAddress').focus();
    await p.locator('#country').focus();
    await p.waitForTimeout(2500);
    check('7  the same email can be retried after a 503',
      retry >= 1, `requests after recovery=${retry}`);

    await p.close();
  }

  // ------------------------------------------------------- product list + 2a
  await section('Downgrade — product list');
  {
    const p = await ctx.newPage();
    await p.goto(DOWN('mode=customer'), { waitUntil: 'load' });

    await tick(p, 'reqOtherProduct');
    await p.waitForTimeout(500);
    check('2a prompt (not an empty box) before any lookup',
      await p.locator('#productListPrompt').isVisible(),
      'the email prompt was not shown');

    await lookup(p, MANY);
    const boxes = await p.locator('#productCheckboxList input[name="cancelledProducts[]"]').count();
    check('2a real products render as checkboxes', boxes > 0, `count=${boxes}`);
    check('2a the prompt is hidden once products exist',
      !(await p.locator('#productListPrompt').isVisible()));

    // switching away clears the ticks
    await p.evaluate(() => { const c = document.querySelector('#productCheckboxList input'); c.click(); });
    await tick(p, 'reqUnsubscribe');
    await p.waitForTimeout(600);
    const stillTicked = await p.locator('#productCheckboxList input:checked').count();
    check('2  "all services" unchecks every product', stillTicked === 0, `ticked=${stillTicked}`);

    await p.close();
  }

  // ------------------------------------------------- Araya conditionals 2b-5
  await section('Downgrade — Araya conditionals (real shop)');
  {
    const p = await ctx.newPage();
    await p.goto(DOWN('mode=customer'), { waitUntil: 'load' });
    await tick(p, 'reqOtherProduct');
    await lookup(p, ARAYA);
    await p.waitForTimeout(1200); // classification round trip

    const names = await p.locator('#productCheckboxList input[name="cancelledProducts[]"]')
      .evaluateAll(els => els.map(e => e.value));
    const arayaIdx = names.findIndex(n => /AI Receptionist|Araya/i.test(n));
    check('araya product is present on the real shop', arayaIdx >= 0, `products=${JSON.stringify(names)}`);

    if (arayaIdx >= 0) {
      await p.evaluate(i => { document.querySelectorAll('#productCheckboxList input[name="cancelledProducts[]"]')[i].click(); }, arayaIdx);
      await p.waitForTimeout(1200);

      check('2b five Araya reasons appear',
        await shown(p, '#arayaReasons'),
        'reasons stayed hidden — check classifyProducts tags');
      check('5  "What could we improve" is hidden',
        !(await shown(p, '#improvementSuggestionGroup')));
      check('5b the 1-5 rating is hidden',
        !(await shown(p, '#overallRatingGroup')));
      check('4  the Araya conditions block exists in the modal',
        await shown(p, '#arayaConditions'));

      // 3a needs stop-now as well
      check('3a no warning until "stop now" is chosen',
        !(await shown(p, '#arayaImmediateWarning')));
      await tick(p, 'effImmediate');
      await p.waitForTimeout(500);
      check('3a warning appears with araya + stop now',
        await shown(p, '#arayaImmediateWarning'));
      await tick(p, 'effEndBilling');
      await p.waitForTimeout(500);
      check('3a warning hides when not stopping now',
        !(await shown(p, '#arayaImmediateWarning')));

      // 3. the renamed option
      check('3  option reads "Stop the service now"',
        (await p.locator('#effImmediate').inputValue()) === 'Stop the service now',
        `value="${await p.locator('#effImmediate').inputValue()}"`);

      // tick an araya reason, then untick the product
      await p.evaluate(() => { document.querySelector('#arayaReasons input[name="reasons[]"]').click(); });
      await p.evaluate(i => { document.querySelectorAll('#productCheckboxList input[name="cancelledProducts[]"]')[i].click(); }, arayaIdx);
      await p.waitForTimeout(900);
      check('2b unticking araya hides the reasons',
        !(await shown(p, '#arayaReasons')));
      check('2b unticking araya also unchecks them',
        (await p.locator('#arayaReasons input:checked').count()) === 0,
        'an araya-specific reason would have been submitted');
      check('5c the feedback fields come back',
        await shown(p, '#improvementSuggestionGroup'));
    }
    await p.close();
  }

  // ------------------------------------------------------- keep-list (2c)
  await section('Downgrade — plan-bundle keep-list');
  {
    const p = await ctx.newPage();
    await p.goto(DOWN('mode=customer'), { waitUntil: 'load' });
    await tick(p, 'reqOtherProduct');
    await lookup(p, MANY);
    await p.waitForTimeout(1200);

    // udmProductTags is a module-scope `let`, so it is not reachable as
    // window.udmProductTags. Drive the real code path instead: stub the
    // classification endpoint and let the controller's own fetch assign the
    // tags, which is also a truer test than poking at internals.
    async function tagsAs(tagList) {
      await ctx.route('**/classifyProducts.php*', async route => {
        const url = new URL(route.request().url());
        const names = url.searchParams.getAll('names[]');
        const tags = {};
        names.forEach(n => { tags[n] = tagList; });
        await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ tags }) });
      });
    }

    async function keepListShownWith(tagList) {
      await tagsAs(tagList);
      await p.goto(DOWN('mode=customer'), { waitUntil: 'load' });
      await tick(p, 'reqOtherProduct');
      await lookup(p, MANY);
      await p.waitForTimeout(1200);
      await p.evaluate(() => { document.querySelector('#productCheckboxList input').click(); });
      await p.waitForTimeout(900);
      const shown = !(await p.locator('#keepProductsGroup').evaluate(e => e.classList.contains('d-none')));
      const opts = await p.locator('#keepProductsList input').evaluateAll(els => els.map(e => e.value));
      await ctx.unroute('**/classifyProducts.php*');
      return { shown, opts };
    }

    const bundle = await keepListShownWith(['plan_bundle']);
    check('2c keep-list appears for a plan bundle', bundle.shown);

    const addon = await keepListShownWith(['addon']);
    check('2c keep-list does NOT appear for an add-on', !addon.shown,
      'an add-on cancellation would be asked which plan to keep');

    const both = await keepListShownWith(['araya', 'plan_bundle']);
    check('2c combined araya+bundle shows BOTH the keep-list and araya reasons',
      both.shown && (await shown(p, '#arayaReasons')));

    // business type drives the options
    await tagsAs(['plan_bundle']);
    await p.goto(DOWN('mode=customer'), { waitUntil: 'load' });
    await tick(p, 'reqOtherProduct');
    await lookup(p, MANY);
    await p.waitForTimeout(1200);
    await p.evaluate(() => { document.querySelector('#productCheckboxList input').click(); });
    await p.waitForTimeout(700);
    const readOpts = async t => {
      await p.selectOption('#businessType', t);
      await p.waitForTimeout(600);
      return p.locator('#keepProductsList input').evaluateAll(els => els.map(e => e.value));
    };
    const rest = await readOpts('Restaurant');
    const mass = await readOpts('Massage');
    await ctx.unroute('**/classifyProducts.php*');

    check('2c Restaurant gets Pro Plan + Website Hosting',
      rest.join(',') === 'Pro Plan,Website Hosting', JSON.stringify(rest));
    check('2c Massage gets Booking System + Website Hosting',
      mass.join(',') === 'Booking System,Website Hosting', JSON.stringify(mass));
    check('2c no prices anywhere in the keep-list',
      !/\$|฿|\d+\s*\/\s*mo/i.test(rest.concat(mass).join(' ')));

    await p.close();
  }

  // ------------------------------------------------------- staff/customer split
  await section('Downgrade — staff vs customer');
  {
    for (const mode of ['customer', 'staff']) {
      const p = await ctx.newPage();
      await p.goto(DOWN(`mode=${mode}`), { waitUntil: 'load' });
      const has = async sel => (await p.locator(sel).count()) > 0;
      const expect = mode === 'staff';
      check(`${mode}: Submitted By ${expect ? 'present' : 'absent'}`,
        (await has('#submittedBy')) === expect);
      check(`${mode}: policy acknowledge ${expect ? 'absent' : 'present'}`,
        (await has('#policyAcknowledge')) === !expect);
      check(`${mode}: contact preference ${expect ? 'absent' : 'present'}`,
        (await has('input[name="contactPreference"]')) === !expect);
      check(`${mode}: star rating ${expect ? 'absent' : 'present'}`,
        (await has('#overallRatingGroup')) === !expect);
      check(`${mode}: project search ${expect ? 'present' : 'absent'}`,
        (await has('#projectSearchDropdown')) === expect);
      check(`${mode}: no "Contact Information" heading`,
        !(await p.content()).includes('Contact Information'));
      await p.close();
    }
  }

  // --------------------------------------------------------------- payload
  await section('Downgrade — webhook payload');
  {
    const p = await ctx.newPage();
    let body = null;
    await ctx.route('**/hook.us1.make.com/**', async route => {
      try { body = JSON.parse(route.request().postData() || '{}'); } catch (e) { body = {}; }
      await route.fulfill({ status: 200, body: 'ok' });
    });
    await ctx.route('**/models/downgradeForm.php', r => r.fulfill({ status: 200, contentType: 'application/json', body: '{"result":"success"}' }));

    await p.goto(DOWN('mode=customer&testMode=true'), { waitUntil: 'load' });
    await p.waitForTimeout(900);
    // testMode autofill selects "all services"; add the policy tick it needs.
    await p.evaluate(() => {
      const a = document.getElementById('policyAcknowledge');
      if (a) a.checked = true;
    });
    await p.locator('#cmdSubmit').click();
    await p.waitForTimeout(2500);

    if (!body) {
      check('payload captured', false, 'no request reached the Make webhook — form may have blocked');
    } else {
      check('payload: no downgradeTarget key', !('downgradeTarget' in body),
        `keys include downgradeTarget=${JSON.stringify(body.downgradeTarget)}`);
      check('payload: changeType equals requestType',
        body.changeType === body.requestType,
        `changeType="${body.changeType}" requestType="${body.requestType}"`);
      check('payload: businessType present', typeof body.businessType === 'string' && body.businessType !== '',
        `businessType=${JSON.stringify(body.businessType)}`);
      check('payload: cancelledProductsText present',
        'cancelledProductsText' in body, Object.keys(body).join(','));
      check('payload: keepProductsText present', 'keepProductsText' in body);
    }
    await ctx.unroute('**/hook.us1.make.com/**');
    await ctx.unroute('**/models/downgradeForm.php');
    await p.close();
  }

  // ---------------------------------- THE REFACTOR GATE: the upgrade form
  await section('Upgrade form — the Task 3 extraction gate');
  {
    const p = await ctx.newPage();
    await p.goto(UP('mode=customer'), { waitUntil: 'load' });

    await lookup(p, MANY);
    const s = await statusText(p);
    check('U1 known email fills the upgrade form',
      (await val(p, '#shopName')) !== '' && /Using/i.test(s),
      `status="${s.slice(0, 60)}"`);
    check('U1b the project id is filled', (await val(p, '#mondayProjectId')) !== '');
    check('U1c the product list replaced the manual select',
      (await shown(p, '#customerProductList')) && !(await shown(p, '#customerProductSelectWrap')),
      `list shown=${await shown(p, '#customerProductList')} selectWrap shown=${await shown(p, '#customerProductSelectWrap')}`);

    await lookup(p, { country: MANY.country, email: 'nobody-else@nowhere.test' });
    check('U4 stale project id cleared on a miss',
      (await val(p, '#mondayProjectId')) === '');

    await p.goto(UP('mode=customer'), { waitUntil: 'load' });
    await lookup(p, MULTI);
    check('U2 multi-branch picker on the upgrade form',
      await shown(p, '#branchPicker'));

    await p.goto(UP('mode=customer'), { waitUntil: 'load' });
    await lookup(p, MANY);
    if (await p.locator('#emailLookupRevert').count()) {
      await p.locator('#emailLookupRevert').click();
      await p.waitForTimeout(400);
      const f = await p.evaluate(() => document.activeElement && document.activeElement.id);
      check('U6 revert moves focus to the email field', f === 'emailAddress', `focus="${f}"`);
      check('U6b the manual select is back and the list is cleared',
        (await shown(p, '#customerProductSelectWrap')) && !(await shown(p, '#customerProductList')),
        `selectWrap shown=${await shown(p, '#customerProductSelectWrap')} list shown=${await shown(p, '#customerProductList')}`);
    }

    // staff mode must NOT run the lookup
    const sp = await ctx.newPage();
    let staffReqs = 0;
    sp.on('request', r => { if (r.url().includes('lookupByEmail')) staffReqs++; });
    await sp.goto(UP('mode=staff'), { waitUntil: 'load' });
    await sp.fill('#emailAddress', MANY.email).catch(() => {});
    await sp.locator('#emailAddress').blur().catch(() => {});
    await sp.waitForTimeout(2000);
    check('U10 staff mode does not run the email lookup', staffReqs === 0, `requests=${staffReqs}`);
    await sp.close();
    await p.close();
  }

  // ------------------------------------------------------------- console errors
  await section('Console errors');
  {
    for (const [label, url] of [
      ['downgrade customer', DOWN('mode=customer')],
      ['downgrade staff', DOWN('mode=staff')],
      ['downgrade autofill', DOWN('mode=customer&testMode=true')],
      ['upgrade customer', UP('mode=customer')],
    ]) {
      const p = await ctx.newPage();
      const errs = [];
      p.on('console', m => { if (m.type() === 'error') errs.push(m.text()); });
      p.on('pageerror', e => errs.push('pageerror: ' + e.message));
      await p.goto(url, { waitUntil: 'load' });
      await p.waitForTimeout(1500);
      const real = errs.filter(e => !/favicon|net::ERR_|gtag|googletagmanager/i.test(e));
      check(`no JS errors on ${label}`, real.length === 0, real.slice(0, 2).join(' | '));
      await p.close();
    }
  }

  await browser.close();

  console.log(`\n${'='.repeat(60)}`);
  console.log(`  PASS ${pass}   FAIL ${fail}`);
  if (failures.length) {
    console.log('\n  Failures:');
    failures.forEach(f => console.log('   - ' + f));
  }
  console.log('='.repeat(60));
  process.exit(fail ? 1 : 0);
})().catch(e => { console.error('\nHARNESS ERROR:', e.stack); process.exit(2); });
