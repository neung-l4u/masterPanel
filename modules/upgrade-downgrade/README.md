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

Both forms post to Make.com scenarios this project cannot inspect, and these
keys have changed or are in doubt:

- **`downgradeTarget` is gone** from the downgrade payload. It used to carry a
  second-level radio choice; the products being cancelled are real product names
  now and travel in `cancelledProducts` / `cancelledProductsText`.
- **`changeType`** used to carry `downgradeTarget || requestType` on the
  downgrade form. It now always carries the request type.
- **`featuresKeep` is gone**, replaced by `keepProducts` /
  `keepProductsText`. `assets/email/customerDowngrade.html` and
  `internalDowngrade.html` were updated to read `{{1.keepProductsText}}`.
- **`effectiveDate`** no longer emits the value `Immediately`; that option is
  now `Stop the service now`.
- **`originalProductManual`** on the upgrade form, and `originalProduct` now
  carrying product names rather than slugs.

Confirm all of these with whoever owns the scenarios before this reaches
production.

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
