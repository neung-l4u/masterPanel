# Araya Feedback Credit

**Date:** 2026-10-14
**Repos:** `masterPanel` (PHP) and `gcal-booking-api` (FastAPI)
**Status:** Design — approved for planning

## Problem

The downgrade form now redirects a customer who cancelled an AI Receptionist
product to a feedback form, gated on `araya_feedback_url`. That hook was built
in the downgrade form revisions; the flow behind it was not, because CS had not
specified it yet — "จะแจ้งรายละเอียดให้ทีหลัง".

CS has now specified it: completing the feedback form grants the shop 30 free
minutes, once per shop for the lifetime of the account, as goodwill. The
previous spec flagged exactly this as a business commitment the system could not
honour — "Nothing in this system grants minutes" — and that is what this design
closes.

Two things block it today.

**The feedback form is the wrong form.** `modules/feedback` asks which package
the shop is interested in, what type of shop it is, and invites an image
upload — it is a lead/inquiry form. Pointing a customer who has just cancelled
at it would ask them what they would like to buy.

**The feedback form cannot identify a shop.** It takes no query parameters, and
its only shop field is a free-text `shopName` the customer types. A
once-per-lifetime credit cannot hang off free text: "Thai Spa" and "thai spa"
would be two shops, and a typo would credit someone else's account.

**Goal:** grant the credit reliably, exactly once per shop, without changing
when the customer's service stops.

## Scope

In scope:

- `modules/feedback` gains an Araya mode, driven by query parameters, alongside
  its existing lead mode.
- A `POST /api/feedback-credit` endpoint in gcal that grants the credit, emails
  the customer the new balance, and records the grant in the top-up log.
- The downgrade form passes `shop_id` to the feedback form, not just the shop
  name and email it passes today.

Out of scope:

- Prices (already deferred to their own spec).
- Any change to the monthly refresh, Stripe, or auto-recharge paths.
- Any change to the `TopUp` or `ShopMinutes` schema. Only an index is added.

## What already exists

Verified in `gcal-booking-api`:

- `TopUp` — the top-up log. Carries `note`, `status` (`paid` / `manual`),
  `minutes_added`, `amount_cents`, and `payment_reference`, which is already
  indexed and whose docstring states it "doubles as the idempotency key".
- `ShopMinutes` — `total_topped_up`, and the per-shop allocation settings.
- `send_email(...)` — dispatches via shop SMTP or SES fallback and writes an
  `EmailLog` row per attempt.
- `MONDAY_WEBHOOK_SECRET` — the pattern for authenticating an external caller:
  read from env, compared with `secrets.compare_digest`, 401 on mismatch, and an
  empty configured secret rejects every request.

## Two conditions need no code

**Deducting a negative balance.** A shop's balance is not stored; it is computed
as `total_topped_up - used_minutes`. So `total_topped_up += 30` on a shop sitting
at -12 lands it at +18 on its own. The requirement that the credit absorbs a
negative balance first is already the behaviour of the data model, and writing
logic for it would risk double-counting.

**Not moving the service-stop date.** The credit touches `ShopMinutes` and
writes a `TopUp` row. It does not read or write `cancel_effective_on` or any
billing date, so nothing moves. This is asserted in a test rather than assumed,
because it is the condition most likely to be broken later by someone adding a
"helpful" side effect.

## Architecture

```text
masterPanel (PHP)                      gcal-booking-api (FastAPI)
-----------------                      --------------------------
downgradeForm.js redirect
  ?mode=araya&shop_id=X
  &shopName=Y&email=Z
       |
       v
feedback/views/feedbackForm.php
  araya mode: hides shopType,
  package and upload; shopName
  and email readonly from URL;
  hidden shop_id
       | submit
       v
feedback/models/feedbackForm.php
  1. INSERT feedback          (unchanged)
  2. araya mode + shop_id:
     curl ---------------------------->  POST /api/feedback-credit
     X-Feedback-Token: <shared>            compare_digest the token
     {shop_id, email, feedback_ref}        shop must exist
                                           INSERT TopUp
                                             payment_reference =
                                             "feedback-credit:<shop_id>"
                                             <- UNIQUE index is the gate
                                           total_topped_up += 30
                                           send_email(new balance)
     <----------------------------------  {granted, reason, new_balance, ...}
  3. record the outcome in the feedback row
  4. forward to the Make webhook  (unchanged)
       |
       v
feedback/views/thankyou.php
  states what happened
```

The token never reaches the browser: PHP calls gcal server-side. The credit
decision lives in one endpoint, so there is one place where "once per shop" is
enforced and one place to audit.

## The endpoint

`POST /api/feedback-credit`, header `X-Feedback-Token`, body
`{"shop_id": "...", "email": "...", "feedback_ref": "..."}`.

The 30 is a constant on the server and is **not** read from the request. A
caller holding the token must not be able to choose the amount.

| Outcome | HTTP | Body |
| --- | --- | --- |
| Granted | 200 | `{"granted":true,"minutes":30,"previous_balance":-12,"new_balance":18}` |
| Already claimed | 200 | `{"granted":false,"reason":"already_claimed","claimed_at":"...","new_balance":45}` |
| Unknown shop | 200 | `{"granted":false,"reason":"shop_not_found"}` |
| Bad or missing token | 401 | `{"ok":false,"error":"unauthorized"}` |

"Already claimed" is 200 rather than 409 because the customer did nothing wrong:
they filled in the form and the system declined to credit twice. A 409 would
push PHP into an error branch for an expected outcome.

`shop_id` is checked against `ShopSettings`; the value in the request is not
trusted on its own.

## Once per shop, enforced by the database

```sql
CREATE UNIQUE INDEX IF NOT EXISTS ux_top_up_feedback_credit
  ON top_up (payment_reference)
  WHERE payment_reference LIKE 'feedback-credit:%';
```

A partial unique index, so it constrains only these rows and leaves every Stripe
and manual `payment_reference` alone. Two concurrent submissions race to the
same key and exactly one wins; the loser catches the integrity error and returns
`already_claimed`. No application lock, and no window where a double-click
grants twice.

The row records the grant as:

| Field | Value | Why |
| --- | --- | --- |
| `payment_reference` | `feedback-credit:<shop_id>` | The idempotency key the index constrains |
| `note` | `Feedback credit` | What CS asked to see in the log |
| `status` | `manual` | `paid` means money arrived. This is a zero-value grant, and `paid` would show it in the revenue report as a sale of nothing |
| `amount_cents` | `0` | No money changed hands |
| `minutes_added` | `30` | |
| `package` | `custom` | Not one of the 100/200/500 packages |

## The email

Sent from gcal via the existing `send_email`, only when `granted` is true, so an
`EmailLog` row exists for every grant and none for a refusal.

It shows the previous balance even when negative:

```text
Previous balance:  -12 minutes
Feedback credit:   +30 minutes
New balance:        18 minutes
```

A customer at -12 who is told "30 minutes added" and then sees 18 will think
they were short-changed. Showing the arithmetic is what makes the deduction
legible rather than suspicious.

The email states that the minutes are goodwill and do not change the billing or
cancellation date, because that is the question it otherwise invites.

## The feedback form's two modes

Driven by `?mode=araya` plus a `shop_id`.

| Field | Lead mode (today) | Araya mode |
| --- | --- | --- |
| `name` | asked | asked |
| `shopName` | asked | prefilled from URL, readonly |
| `email` | asked | prefilled from URL, readonly |
| `shopType` | asked | hidden |
| `package` (Topic) | asked | hidden; submitted as `AI Receptionist cancellation` |
| `description` | "Tell us more about your business or needs..." | asks why they cancelled the AI Receptionist |
| image upload | shown | hidden |
| `shop_id`, `mode` | — | hidden inputs |

`shopName` and `email` are readonly rather than hidden so the customer can see
the system recognised them. `shop_id` is what the credit keys on, so editing a
visible shop name could not redirect the credit even if the field were editable.

**Without a `shop_id` the form is its old self.** Someone opening the feedback
form directly, or following a truncated URL, gets lead mode: no credit attempt,
no error, nothing new to go wrong.

## Error handling

| Failure | Behaviour |
| --- | --- |
| gcal unreachable or times out | The feedback row is already written. PHP records `credit_service_unavailable` on it and the customer is thanked without a credit claim. Nothing is lost; an admin can grant it by hand from the existing Minutes page |
| Token wrong or unset | 401. The customer sees only that their feedback was received — a token problem is not theirs to understand |
| Shop not found | Feedback kept, `shop_not_found` recorded, customer told the team will check |
| Already claimed | Customer is told plainly, with the date, and thanked |
| Email send fails | The credit still stands. `send_email` already logs its own failure, and losing the notification must not unwind a granted credit |
| Two submissions at once | The unique index admits one. The other returns `already_claimed` |

The rule carried over from the downgrade form: the credit is a reward, never a
gate. No failure path blocks the feedback itself from being recorded.

## Testing

gcal, with the existing pytest harness:

- First call grants: `granted` true, one `TopUp` row with `note="Feedback
  credit"`, `status="manual"`, `amount_cents=0`, `minutes_added=30`.
- Second call for the same shop: `already_claimed`, and still exactly one row.
- Shop at -12 before: `new_balance` 18. This is the negative-balance condition.
- Shop at 0 before: `new_balance` 30.
- Unknown `shop_id`: `shop_not_found`, no row written.
- Missing, empty and wrong token: 401, no row written.
- **Billing untouched:** `cancel_effective_on` and the shop's billing fields are
  identical before and after a grant.
- `EmailLog` gains a row on a grant and none on `already_claimed`.
- Two concurrent requests: one row.

masterPanel, by rendering from the CLI and by hand:

- Both modes render; Araya mode hides `shopType`, `package` and the upload, and
  carries a hidden `shop_id`.
- No `shop_id` falls back to lead mode.
- A deliberately wrong gcal URL leaves the feedback row written and the customer
  thanked.

## Files

New:

- gcal `tests/test_feedback_credit.py`

Modified:

- gcal `app/db.py` — the partial unique index
- gcal `app/main.py` — `POST /api/feedback-credit` and its email
- masterPanel `modules/feedback/views/feedbackForm.php` — the two modes
- masterPanel `modules/feedback/controllers/feedbackForm.js` — submit the new
  fields, surface the outcome
- masterPanel `modules/feedback/models/feedbackForm.php` — the server-side call
  and recording the outcome
- masterPanel `modules/feedback/views/thankyou.php` — state what happened
- masterPanel `modules/upgrade-downgrade/controllers/downgradeForm.js` — pass
  `shop_id` in the redirect

## Deployment

- gcal: set `FEEDBACK_CREDIT_TOKEN` in its environment. Unset means every call is
  rejected, which is the safe default.
- masterPanel: add the same value to the feedback module's config, which is
  gitignored, plus the gcal base URL.
- The index is created by the existing idempotent migration list in `db.py`.
- `araya_feedback_url` in the upgrade-downgrade config must point at the feedback
  form with `mode=araya`; until it is set, the downgrade form still redirects
  nowhere and this flow is dormant.

## Flagged for CS

The 30 minutes are granted automatically, but nothing verifies that a feedback
submission is genuine — a customer who submits an empty-but-valid form is
credited. That is deliberate: policing feedback quality would cost more than the
credit is worth. If CS wants a quality bar, it needs to be a human review step,
and the credit would then stop being automatic.
