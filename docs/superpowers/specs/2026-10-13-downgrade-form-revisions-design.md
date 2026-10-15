# Downgrade / Unsubscription Form Revisions

**Date:** 2026-10-13
**Module:** `modules/upgrade-downgrade`
**Status:** Design — approved for planning

## Problem

CS delivered a revision list for the downgrade form covering thirteen structural
changes, plus one request that opens the list: the form should find the customer
from their email address on both the customer and the staff side.

The form today cannot do the central thing CS asks for. "Unsubscribe specific
product" needs to show the shop's actual products so the customer can tick the
ones to cancel, and the downgrade form has no lookup at all — it asks the
customer to type their shop name and offers a fixed three-option radio group
("website only", "ordering only", "marketing only") that bears no relation to
what they actually bought.

Several further requests are conditional on *which* product is being cancelled:
cancelling AI Receptionist adds five reasons, changes the effective-date warning,
reveals a conditions panel, and hides two feedback questions. Cancelling a plan
bundle opens a follow-up list of what to keep. None of that is possible without
knowing the customer's products, and without being able to tell one kind of
product from another.

**Goal:** implement the thirteen structural items, and give the downgrade form
the email lookup that the conditional items depend on.

## Scope

In scope:

- The thirteen items from the CS list, customer and staff variants.
- The email lookup on the downgrade form, in both modes.
- A product classification function, because almost every conditional item keys
  off whether a product is AI Receptionist, a plan bundle, or neither.

Out of scope, deferred to their own specs:

- **Prices** (CS item 2c, and the upgrade form's package list). The data already
  exists and is the right shape — see "Prices: deferred, not absent" below — but
  resolving it needs three answers CS has not given. CS wrote "ถ้าทำไม่ได้
  เอาแบบไม่มีราคาก่อนได้เลยค่า", so shipping without them is sanctioned.
- **The AI Araya feedback form** (item 7, the redirect). It does not exist yet;
  CS wrote "จะแจ้งรายละเอียดให้ทีหลัง". The hook for it is built; the
  destination is not.

Flagged for CS, not a build item:

- Item 7 promises the customer "30 นาทีฟรี" for completing the feedback form.
  Nothing in this system grants minutes. That is a business commitment rendered
  as form copy, and CS must be able to honour it operationally before the text
  ships.

## Prices: deferred, not absent

Worth recording so the follow-up does not restart from scratch.

`modules/signup/assets/API/B-Price-2607171132-SocialMediaAdsandVisibilityBoost.json`
is the newest full catalogue — 709 items, of which 432 are "Local" plan entries.
Its shape is exactly what CS described: country → business type → `Products` →
`items`, each carrying `name`, `amount`, `currency`, `period`, `ext`, `status`
and `type`. All six countries are present, with `Restaurant`, `Massage` and
`All`, and prices genuinely differ across both axes — AU/Restaurant Local Starter
Bundle is AUD 3,771.60/Year where TH/Massage is THB 57,951.60.

Note the `B-Price-*` files are versioned patches, not snapshots. The 2510/2509/
2508 files are 2025 add-on deltas carrying zero "Local" plan items; only the 2607
files hold the full set. A resolver must pick the right file deliberately.

Three questions block the resolver, all needing CS:

1. **Which `period`?** Monthly, yearly and pay-upfront tiers all exist for the
   same product. A single displayed price has to choose.
2. **Solo variants do not match by name.** "Local Starter Solo" is absent from
   AU/Restaurant while the Bundle equivalents are present. Either the naming
   differs in the catalogue or Solo is sold under a different business type.
3. **Combined products.** "Website Hosting" resolves to "Localforyou Booking
   System (Incl Website Hosting and Booking System)". Showing that name against
   a "Website Hosting" option would confuse the customer.

## Product classification

Every Araya- and Bundle-conditional item depends on telling one kind of product
from another, and the real data makes clear that neither a name match nor a
keyword rule is sufficient on its own.

What the mirror actually holds:

- AI Receptionist appears as `AI Receptionist` (2 rows) and as
  `AI + Local Starter Bundle` (2 rows). The second is a *combined* product where
  Araya ships alongside a plan, so it must trigger both the Araya flows and the
  bundle flow.
- Matching `%bundle%` is unsafe. `Social Media Marketing Bundle` (12 rows) is an
  add-on, not a plan bundle. CS item 2c — "what would you like to keep?" — must
  not fire when someone cancels a social-media add-on.

**Design:** one pure function.

```php
udmClassifyProduct(string $productGroup): array   // e.g. ['araya', 'plan_bundle']
```

It returns a list of tags, not a single type, because a combined product belongs
to more than one category. Tags are `araya`, `plan_bundle`, `plan_solo`, `addon`.

Resolution order:

1. **An explicit map in `assets/config.php`**, keyed by the exact
   `product_group` string. Authoritative, reviewable, and the place a human
   corrects a misclassification.
2. **Keyword fallback** for names absent from the map: `araya` when the name
   contains "AI Receptionist" or "Araya"; `plan_bundle` when it contains both
   "Local" and "Bundle"; `plan_solo` when it contains both "Local" and "Solo".
3. **`addon`** when nothing else matched — the safe default, because an
   unrecognised product should not trigger a conditional flow.

The map is preferred over pure keyword rules because only 83 distinct product
names exist today, which makes a map tractable, and because a map is *visible*:
someone can read it and see exactly what is classified how. A keyword rule is
invisible until it is wrong, which is precisely how `getPlanType` silently broke
the Solo-to-Bundle gate for every real product name on the upgrade form.

Only four rows in the mirror classify as `araya`, so the Araya flows cannot be
meaningfully exercised against production data. They get fixtures.

## Architecture

```text
                      ┌──────────────────────────────────┐
  customer types  →   │ assets/js/emailLookup.js         │ ← shared, extracted
  email, blurs        │  (lookup, branch picker, fill)   │   from upgradeForm.js
                      └───────────────┬──────────────────┘
                                      │ shop + products
                      ┌───────────────▼──────────────────┐
                      │ controllers/downgradeForm.js     │
                      │  product checkboxes              │
                      │  recomputeConditionalState()     │
                      └───────────────┬──────────────────┘
                                      │ product names
                      ┌───────────────▼──────────────────┐
                      │ udmClassifyProduct()             │
                      │  araya / plan_bundle / ...       │
                      └──────────────────────────────────┘
```

The lookup is **extracted, not copied**. It is roughly 170 lines carrying four
fixes that were expensive to find: clearing a stale Monday project id when the
email stops matching, clearing only fields the lookup itself filled, allowing a
retry after a 503, and moving focus when the revert link removes itself. Copying
it into a second file guarantees the next fix lands in one and not the other.

Extraction is a refactor of working, reviewed code, so it is a task of its own
with the upgrade form's behaviour verified unchanged afterwards.

## Field reordering (item 1)

More than a reorder. The fields CS wants in one ordered list are currently split
across two sections — Business Information (`shopName`, `tradingName`,
`address`) and Contact Information (`contactPerson`, `emailAddress`,
`mobileNumber`) — and `country` exists **only in staff mode**.

Target order, one Business Information section:

    Country *            ← promoted to both modes; the lookup needs it
    Business Type *      ← new, matching the upgrade form
    Business Name *      ← renamed from Shop Name
    Trading Name
    Contact Person *
    Email Address *
    Phone Number *

`address` stays, placed after that list. CS did not ask to remove it and it is
currently required. The Contact Information section disappears, its fields
absorbed above.

Business Type is added here for the same reason it was added to the upgrade
form, and because item 2c's "what would you like to keep?" list is chosen by it:
Restaurant gets Pro Plan + Website Hosting, Massage gets Booking System +
Website Hosting.

## The email lookup, both modes

Reuses the endpoint, branch picker and mirror already built and reviewed:
`GET assets/API/lookupByEmail.php?country=XX&email=...`.

**Customer mode** behaves as the upgrade form does: fill shop fields on a match,
show the branch picker when one email matches several shops, degrade silently to
manual entry on a miss or a failure.

**Staff mode keeps its Monday project search and gains the lookup.** CS asked for
both sides to work the same way, which means "staff should not have to type what
the email can fill" — not "remove the search". A staff member taking a
cancellation call may have the shop ID to hand rather than the email.

With both paths active they can disagree: staff searches shop X, then an email
fills shop Y. **Last action wins**, and the form shows which shop is currently
attached, so what will be submitted is never ambiguous.

## Type of Request and the product list (item 2)

`Downgrade to other product` becomes `Unsubscribe specific product`;
`Unsubscribe all services` is unchanged. The existing second-level radio group
("website service only" / "online ordering only" / "marketing only") is removed,
superseded by the real product list.

**Item 2a.** Choosing "specific product" reveals the shop's products from the
lookup as checkboxes. Before a lookup has run it shows a prompt to enter the
email address first — not an empty box, which would read as "you have no
products".

**Item 2b.** When any *checked* product classifies as `araya`, five reasons
append to the Reason for Downgrade/Unsubscription section:

- Too expensive for the value we get
- Not enough calls to justify the cost
- The service didn't perform as we expected
- Problems with the booking system
- We have staff answering calls now

**Item 2c.** When any checked product classifies as `plan_bundle`, a follow-up
list asks what to keep, chosen by Business Type: Restaurant gets Pro Plan and
Website Hosting; Massage gets Booking System and Website Hosting. Without prices
for now.

All three are driven by a single `recomputeConditionalState()` reading the
currently checked boxes, never by incremental add/remove on each click. Ticking
and unticking must not leave a stale section behind, which is the failure the
upgrade form hit when `updateSectionVisibility()` was not called on every change
to the field that drives it.

### What replaces `downgradeTarget` in the payload

Removing the radio group changes what is submitted, and this must be deliberate
rather than incidental. Today `sendData()` sends:

```javascript
changeType: formData.downgradeTarget || formData.requestType || '',
```

So `changeType` currently carries the chosen downgrade target when there is one,
falling back to the request type. The upgrade form sends the same `changeType`
key, so a downstream consumer reads one field from both forms.

With the radio group gone, `downgradeTarget` is never set and `changeType`
silently becomes `requestType` for every submission. That is a change in meaning,
not just in value.

Design: `changeType` keeps carrying the request type, and the cancelled products
are sent as a new `cancelledProducts` array of product names, with
`keepProducts` for item 2c's answer. `downgradeTarget` is dropped from the
payload rather than left present-but-always-empty, because an always-empty field
invites a consumer to keep reading it.

**This needs confirming with whoever owns the Make.com scenario before it
ships.** The same open question already applies to the upgrade form's
`originalProductManual` key and to `originalProduct` now carrying product names
rather than slugs. Both forms feed scenarios this project cannot inspect.

## Araya conditionals (items 3, 3a, 4, 5)

**Item 3.** The `Immediately` effective-date option becomes `Stop the service
now`, with helper text "billing continues until the end of your notice period".

**Item 3a.** That option plus a checked Araya product adds: "Your AI Receptionist
will stop answering calls right away and any remaining minutes will be forfeited.
Billing continues until the end of your 30-day notice period."

**Item 4.** An Araya conditions block is added to the existing
`#modalImportantInfo` sidebar modal, shown only when an Araya product is checked,
carrying the five bullets CS supplied about the 30-day boundary, minute
allowance, remaining balance, overage on the final invoice, and other services
being unaffected.

**Item 5.** "What could we improve to keep you with us?" and the 1–5 experience
rating hide when an Araya product is checked, so the feedback form does not ask
twice.

Both fields are currently `required`. **Hiding them must also drop the
requirement**, or the form becomes unsubmittable: a hidden required field fails
validation with nothing visible for the customer to fix. This is the single most
likely way to ship this item broken.

## Policy section (item 6)

A new section before submit, customer mode only, carrying CS's three paragraphs
about the 30-day notice, the customer's responsibility for removing stale
marketing material, and the website being discontinued if all services or
hosting are cancelled. A required "Yes, I understand." checkbox gates submission.

## Staff variant

Everything above, minus the questions a staff member should not be answering on
the customer's behalf, plus one field:

- Removed: "Would you like us to contact you to discuss alternative options?"
- Removed: the 1–5 experience rating
- Removed: the whole policy section and its acknowledgement checkbox
- Added: a required "Submitted by" field at the end, for the staff member's name

So staff differs from customer by exactly: no feedback questions, no policy
acknowledgement, plus Submitted by. That matches CS's description of the two
sides being otherwise identical.

## Post-submit Araya flow (item 7)

On submit, when any cancelled product classifies as `araya`:

1. **Email `admin@localforyou.com`.** Buildable now; the repo already sends mail.
2. **Redirect to the feedback form** when `araya_feedback_url` is set in
   `assets/config.php`, passing shop name and email as query parameters so the
   customer does not retype them. The key is empty today, so nothing happens
   until CS supplies the URL — at which point it starts working with no code
   change.

The hard part is work being done anyway: knowing that an Araya product was
cancelled, and having the shop name and email to hand.

## Error handling

| Failure | Behaviour |
| --- | --- |
| Lookup fails or times out | Manual entry, exactly as the upgrade form; nothing the customer typed is cleared |
| Email matches no shop | Neutral message; the product list shows a prompt, not an empty box |
| Email matches several shops | Branch picker, as the upgrade form |
| Lookup succeeds, shop has no products | Says so explicitly; "specific product" cannot be completed, so the customer is directed to "all services" or to contact the team |
| A product name classifies as nothing | Treated as `addon`; no conditional flow fires. An unrecognised product must not silently trigger the Araya or bundle paths |
| `araya_feedback_url` unset | No redirect; the admin email still sends |
| Admin email fails to send | Submission still completes. Losing a notification must not lose a cancellation |

The guiding rule from the upgrade form carries over: **the lookup is an
accelerator, never a gate.** Every failure path leaves the form submittable by
hand.

## Testing

- `udmClassifyProduct` against all 83 distinct `product_group` values in the
  mirror, asserting the counts per tag and that `AI + Local Starter Bundle`
  returns both `araya` and `plan_bundle`. Plus the keyword fallback and the
  `addon` default.
- `recomputeConditionalState()` stub-driven, as the sync orchestration is:
  checking an Araya product reveals the five reasons, the conditions block and
  the Araya warning, and hides the two feedback fields *and their required
  attributes*; unchecking reverses all of it; checking a `plan_bundle` reveals
  the keep-list for the selected Business Type; a `Social Media Marketing Bundle`
  does **not** reveal it.
- The extracted `emailLookup.js` verified against the upgrade form first, so the
  refactor is proven not to have changed existing behaviour before the downgrade
  form depends on it.
- Field order, both modes, over HTTP; staff retains its project search, customer
  does not gain Submitted by, staff does not gain the policy section.
- Submission with a hidden-but-formerly-required field, confirming the form
  actually submits.

## Files

New:

- `modules/upgrade-downgrade/assets/js/emailLookup.js` — the shared lookup,
  extracted from `controllers/upgradeForm.js`
- `modules/upgrade-downgrade/assets/productClassify.php` — `udmClassifyProduct`
- `modules/upgrade-downgrade/tests/test_product_classify.php`
- `modules/upgrade-downgrade/tests/test_downgrade_conditionals.php`

Modified:

- `controllers/downgradeForm.js` payload: `cancelledProducts` and `keepProducts`
  added, `downgradeTarget` dropped — see "What replaces `downgradeTarget`"
- `views/downgradeForm.php` — field order, Type of Request, product list,
  conditionals, policy section, staff variant, Araya conditions in the modal
- `controllers/downgradeForm.js` — lookup wiring, `recomputeConditionalState()`,
  validation changes
- `controllers/upgradeForm.js` — lookup block removed, now loaded from the
  shared file
- `views/upgradeForm.php` — script tag for the shared file
- `assets/config.example.php` — the classification map and `araya_feedback_url`
- `views/thankyou.php` — the Araya admin email and the gated redirect

Untouched: the mirror, the sync, `models/shopLookup.php`,
`assets/API/lookupByEmail.php`, `assets/API/getSubscriptions.php`,
`assets/API/selectProjectCountry/live.php`.

## Deployment

No migration; the mirror and endpoint already exist and are populated. The
classification map ships in `config.example.php` and must be copied into the real
`config.php`, which is gitignored — the same first-time-setup step the sync
runbook documents.
