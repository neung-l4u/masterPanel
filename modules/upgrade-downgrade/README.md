# Upgrade / Downgrade module

Customer- and staff-facing forms for product upgrades and for downgrades /
unsubscriptions, plus a mirror of Monday.com shop and subscription data that
lets the forms look a customer up by their email address.

- `views/upgradeForm.php`, `views/downgradeForm.php` — the two forms, each with
  a `?mode=customer` and a `?mode=staff` variant
- `assets/js/emailLookup.js` — the shared email lookup, used by both forms
- `assets/productClassify.php` — `udmClassifyProduct()`, which tags a product as
  `araya` / `plan_bundle` / `plan_solo` / `addon`
- `sync/README.md` — the Monday mirror sync and its schedule

## Tests

```
php tests/test_product_classify.php      # 41 assertions
php tests/test_shop_lookup.php
php tests/test_mirror_match.php
php tests/test_mirror_repo.php
php tests/test_mirror_schema.php
php tests/test_monday_client.php
php tests/test_monday_mirror.php
php tests/test_mirror_orchestration.php
php tests/test_lookup_endpoint.php
```

The downgrade form's conditional sections have no automated coverage: there is
no JavaScript test runner here, and only two rows in the mirror classify as
`araya`, so the Araya flows cannot be exercised against production data anyway.
`tests/test_downgrade_conditionals.md` is the manual script, including how to
force each state from the browser console.

## First-time setup

`assets/config.php` is gitignored. Copy `assets/config.example.php` to it and
fill in:

- `monday_token` — needs read access to the per-country project boards, the
  Contacts & Accounts board, and the Active Subscriptions board
- `sync_token` — shared secret for triggering the sync over HTTP; leave empty to
  allow CLI invocation only, which is the safe default
- `product_classification` — the exact-match product map. **Without it the Araya
  flows never fire**: of the 66 distinct `product_group` values in the mirror,
  the keyword fallback matches Araya in exactly one, and `AI + Local Starter
  Bundle` is not it. The form still works; it just stops asking the Araya
  questions.
- `araya_feedback_url` — leave empty until CS supplies it. The redirect is inert
  while empty; the admin notification email sends regardless.

## Open questions

### The Make.com payload contract

**Reconciled against the exported blueprint on 2026-10-15.** The scenario is
`[Mark] Upgrade/Downgrade Form : Logs to monday & send email`, and it reads 47
webhook fields across three Monday modules and eight email modules.

Resolved:

- **`downgradeTarget`** is not read anywhere in the scenario, so dropping it
  broke nothing.
- **`changeType`** is not read either. Its change of meaning is invisible to
  this scenario.
- **`effectiveDate`** is printed verbatim into the emails and never compared
  against a string, so renaming the `Immediately` option is safe.
- **`originalProduct`** is printed into the Monday update. It now carries
  product names rather than slugs, which reads better there.
- **`featuresKeep`** IS read, by all four downgrade email modules (42, 43, 45,
  46). The keep-list renamed this answer to `keepProducts`, which would have
  left an empty box in every one of those emails. The payload now sends
  `featuresKeep` alongside the new names so the scenario needs no edit.

Known mismatches that are not ours to fix:

- **`experienceRating`** is read by modules 42 and 45 but has never existed on
  either form; the scenario asks for a field that was never sent. The rating
  it presumably wants is `overallRating`, which the scenario also reads and
  the form does send. Left alone deliberately: adding an alias would put two
  keys for one value in the payload.
- **`phoneNumber`** and **`addons`** are read by the shared Monday update
  (module 50) but only exist on the upgrade form, so those two lines have
  always been blank for a downgrade. Pre-existing, not a regression.
- Module 50's update body begins with the literal text `Test update`.

### Deferred to their own specs

- **Prices** (CS item 2c, and the upgrade form's package list). The data exists
  and is the right shape — see the design spec's "Prices: deferred, not absent"
  — but the resolver needs three answers from CS: which billing period to
  display, why Solo variants do not match by name, and how to present combined
  products. CS sanctioned shipping without them.
- **The AI Araya feedback form** is built. `modules/feedback` has an Araya mode,
  and completing it grants the shop 30 goodwill minutes once per shop for the
  lifetime of the account. See
  `docs/superpowers/specs/2026-10-14-araya-feedback-credit-design.md`.

  Three settings have to line up before the flow does anything:

  1. `araya_feedback_url` in this module's `config.php`, pointing at
     `modules/feedback/views/feedbackForm.php`. The redirect appends `mode`,
     `shop_id`, `shopName` and `email` itself.
  2. `gcal_base_url` and `feedback_credit_token` in
     `modules/feedback/assets/config.php`, copied from `config.example.php`.
  3. `FEEDBACK_CREDIT_TOKEN` in the gcal API's environment, matching the token
     above. Unset there rejects every credit request, which is the safe
     default.

  With any of them unset the customer still reaches a thank-you page and their
  feedback is still recorded; only the credit is skipped. A redirect that
  arrives without a `shop_id` renders the ordinary lead enquiry form rather
  than promising minutes that cannot be granted.

### Flagged for CS, not a build item

Item 7 promises the customer "30 นาทีฟรี" for completing the feedback form.
Nothing in this system grants minutes. That is a business commitment rendered as
form copy, and CS must be able to honour it operationally before the text ships.
No such text is in the form today.
