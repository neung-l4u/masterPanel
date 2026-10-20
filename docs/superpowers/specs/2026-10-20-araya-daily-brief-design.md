# Araya Daily Brief — Design

**Date:** 2026-10-20
**Status:** Approved, ready for implementation planning

## Problem

The team has no single view of the AI Araya fleet. Answering "how many shops are
live, who is about to cancel, who is about to run out of minutes" means opening
the Monday board, then opening the gcal admin, then doing arithmetic by hand.
Nobody does it daily, so shops run out of minutes without an upsell
conversation and shops that never use the service go unnoticed for weeks while
paying full price.

Two sources hold the answer and neither holds all of it:

- **Monday** board `AI Receptionist – Client Tracker` (id `2032016220`) is where
  the team actually works. It owns lifecycle state (`Live Status`), ownership
  (`PO`), setup progress (`Set up Status`), and the cancellation reason.
- **gcal-booking-api** owns the numbers: remaining minutes, allocation, billing
  day, and closed billing periods.

## Goal

1. A dashboard in masterPanel that merges both sources and applies the alert
   rules. This ships and is verified first.
2. A plain-text brief posted to the Google Chat space "IT in house" at 07:00
   Asia/Bangkok daily, built from the same code as the dashboard.

## Scope

Eight sections, all backed by data confirmed present in both sources.

**In scope:** overview counts, live shops with remaining minutes and lifespan,
upsell opportunities, two idle-shop rules, stalled setups, pending
cancellations, and a data-gaps section.

**Explicitly out of scope** for this iteration, because the data does not exist
yet. Each needs its own spec:

| Deferred | Blocker |
|---|---|
| Revenue totals | Data exists (`SubscriptionPayment`, `Payment`) but deferred to keep this iteration shippable |
| Per-shop call quality | `Call.outcome` is free text; its real values must be audited before any rule reads them |
| Respond.io ghosting | No integration exists |
| Stripe subscription status / overdue | No synced subscription-status field |
| Contract expiry | No contract end date stored anywhere |
| Real Google Chat @mentions | Needs a Monday-name → Chat-user-id map |

## Architecture

```
gcal-booking-api (FastAPI, prod host)
  NEW  GET /api/admin/araya-summary
       auth: X-Admin-Key header, value from env ARAYA_ADMIN_KEY
       reads ShopSettings, ShopMinutes, BillingPeriodBalance
       returns one JSON row per shop; does NOT know about Monday
                     |
                     |  HTTPS + X-Admin-Key
                     v
masterPanel (PHP, report.localforyou.com)
  modules/arayaBrief/
    models/fetchGcal.php     -> gcal rows, 5-minute cache
    models/fetchMonday.php   -> Monday rows, 5-minute cache
    models/matchShops.php    -> pair them, report what did not pair
    models/buildBrief.php    -> apply all eight section rules
    models/renderChat.php    -> brief -> Chat text
    views/index.php          -> brief -> dashboard HTML
  api/arayaBrief/sendChat.php  -> cron 07:00 -> Chat webhook
  api/arayaBrief/config.php    -> webhook URL + admin key (gitignored)
```

The decisive constraint: **`buildBrief.php` is the only place that knows the
rules.** The dashboard and the Chat message both call it. If each computed its
own numbers they would drift apart, and a brief that disagrees with the
dashboard is worse than no brief.

### Unit responsibilities

| File | Does | In | Out |
|---|---|---|---|
| `fetchGcal.php` | Call gcal endpoint, cache 5 min | — | gcal shop array |
| `fetchMonday.php` | Call Monday GraphQL, cache 5 min | — | Monday shop array |
| `matchShops.php` | Pair shops across sources | both arrays | `matched[]`, `unmatchedMonday[]`, `unmatchedGcal[]` |
| `buildBrief.php` | Apply the eight rules | match result | brief array |
| `renderChat.php` | Format for Chat | brief | string |
| `views/index.php` | Render dashboard | brief | HTML |

`matchShops.php` and `buildBrief.php` take plain arrays and return plain arrays,
so both are testable from fixtures with no network and no API key. These two
hold all the logic that can be wrong, so they are the two that must be testable
in isolation.

Caching is 5 minutes because the dashboard will be refreshed repeatedly, Monday
enforces API rate limits, and the gcal endpoint scans every shop's periods.

## The gcal endpoint

`GET /api/admin/araya-summary`

Authenticated by an `X-Admin-Key` header compared against `ARAYA_ADMIN_KEY`
from the environment. Existing gcal endpoints authenticate per-shop via session
cookie (`_require_user_and_shop`), which cannot serve a cross-shop admin
report, and the repo has no API-key scheme to reuse — hence a new one. Reject
with 401 and log the attempt on a missing or wrong key. Compare with a
constant-time comparison.

Returns non-deleted shops with `account_type = 'production'` (demo and
internal_test rows must not appear in a report the team acts on):

```json
{
  "generated_at": "2026-10-20T06:21:00+07:00",
  "shops": [
    {
      "shop_id": "...",
      "shop_name": "...",
      "shop_email": "...",
      "calendar_id": "...",
      "lifecycle_status": "live",
      "remaining_minutes": 152,
      "allocation": 250,
      "billing_day": 15,
      "days_until_refresh": 21,
      "currency": "AUD",
      "closed_periods": [
        {"period_end": "2026-09-30", "allocation": 250, "used_minutes": 98}
      ]
    }
  ]
}
```

- `remaining_minutes` is computed the same way `/api/minutes` computes it
  (`total_topped_up` minus minutes derived from `Call.duration_sec`), so the two
  endpoints never disagree. Negative balances are returned as negative, not
  clamped to zero — a shop at `-109` is the single most urgent row in the
  report and clamping would hide it.
- `allocation` is `fixed_minutes` when `fixed_minutes_enabled` is set, otherwise
  the most recent period's allocation, otherwise `0`.
- `days_until_refresh` is computed from `billing_day` against today, wrapping
  across month boundaries. `billing_day = 0` means disabled and yields `null`.
- `closed_periods` carries the four most recent `BillingPeriodBalance` rows,
  newest first — the latest period plus the three needed for the trend rule.

## Monday fields read

Board `2032016220`, via the existing Monday MCP/GraphQL access.

| Field | Column id | Used for |
|---|---|---|
| Name | `name` | Shop name, name-match fallback |
| Live Status | `color_mks6nf9m` | Every head count |
| Set up Status | `status` | Stalled setups |
| PO | `multiple_person_mm1jwyxn` | Owner mention |
| Register Date | `date4` | Days stalled |
| Live Date | `date_mm7zba5t` | Lifespan, days since live |
| Reason For Cancel | `text_mm7z30rd` | Cancellation section |
| Note | `text_mm1b8q0z` | Stalled-setup second line |
| Google Calendar ID | `text_mm2y84v4` | Primary match key |
| Email Address (text type) | `text_mks6asn5` | Secondary match key |
| Email Address (email type) | `email_mm7r176d` | Display only, as `email_alt` |
| Last updated | `pulse_updated_mm3fxzh5` | Reserved for future ghosting fallback |

## Matching

Monday is the head-count authority; gcal supplies the numbers. Each Monday shop
is matched to at most one gcal shop, in this order:

1. **Google Calendar ID**, trimmed and lowercased. Unique and unambiguous.
2. **Email**, trimmed and lowercased, used only when the calendar ID is absent
   on either side.
3. **Normalized name** — lowercase, collapse whitespace, strip punctuation, and
   strip a trailing parenthesised group.

Step 3 exists because Amelia-based shops have no Google Calendar ID, and it is
deliberately last because Monday names are unreliable for matching: the board
contains `Roanoke Thai Massage & Spa (Amelia)` and
`Tableland Traditional Thai massage (Amelia)`, where the parenthesised part
names the booking system, not the shop. It also contains
`KLR Massage Therapy ( Croydon )` and `KLR Massage Therapy (Purley)` — two real
and distinct branches that normalize to the same string.

Therefore: **a name match that is ambiguous (more than one candidate on either
side) is not a match.** Both shops go to the data-gaps section instead. Silently
pairing the wrong branch would attribute one branch's minutes to the other,
which is worse than reporting that the pairing is unknown.

Each gcal shop may be consumed by only one Monday shop; first match wins by the
order above.

Everything that fails to pair appears in the data-gaps section. Nothing is
dropped silently — a prior mirror-sync incident in this codebase came directly
from unmatched rows vanishing without a trace.

## Section rules

### 1. Overview

Head counts from Monday `Live Status`:

| Line | Labels counted |
|---|---|
| LIVE | `LIVE` |
| Pending cancellation | `Waiting to cancel` |
| Setup in progress | `Not Live`, `Testing`, `Ready to LIVE`, `Waiting to onboard`, `Stuck`, `Ghosted`, `Pause` |
| Cancelled | `Canceled` + `Cancel Before LIVE`, broken out in parentheses |
| Total | every item on the board |

The board defines `Pause` twice (label ids 6 and 11); both count as one bucket.
An item whose `Live Status` is empty counts only toward the total and is listed
in data gaps.

### 2. Live shops

One line per shop with `Live Status = LIVE`, formatted as the user specified:

```
Aunya Shop (152 mins · 4 ด. 24 วัน)
```

- Minutes come from gcal, shown signed: `-109 mins` stays negative.
- Lifespan is today minus Monday `Live Date`, rendered as months and days.
- No `Live Date` → `ยังไม่ได้บันทึก live date`, and the shop is listed in data gaps.
- No gcal match → the line still appears, marked `(ไม่พบใน gcal ⚠️)`. A live
  shop is never hidden because its numbers are missing.
- Sorted by remaining minutes ascending, so the shops at risk sit at the top.

### 3. Upsell opportunities

`days_until_refresh <= 15` **and** `remaining_minutes < 70`.

- `billing_day = 0` (so `days_until_refresh` is null) → excluded here, reported
  in data gaps.
- Shops in `Waiting to cancel` or `Canceled` are excluded even when they meet
  the condition, with a parenthesised note saying why. Pitching an upsell to a
  shop that has given notice wastes the team's time.
- None qualifying → `ไม่มีวันนี้ ✅`.

### 4. Idle shops, rule A — new shops not using the service

Live for **>= 10 days** and **used less than 15% of allocation**, where
`used = allocation - remaining_minutes` and `pct = used / allocation`.

The threshold is proportional rather than a fixed minute count because
allocations differ per shop: 230 minutes remaining means "untouched" on a
250-minute allocation and "normal use" on a 1000-minute one, so a fixed number
would alarm on large shops every single day.

`allocation = 0` → excluded (no division possible), reported in data gaps.

### 5. Idle shops, rule C — established shops that went quiet

At least **3 closed billing periods**, and the latest period's `used_minutes` is
**less than 30% of the mean** of the three preceding periods.

- Fewer than 3 closed periods → not evaluated here; rule A covers those shops.
- Mean of 0 → excluded, since a shop that never used anything is already caught
  by rule A.
- A shop matching both A and C appears **only under C**, which is the more
  specific finding.

Rules A and C are reported as two separate sections because they are different
problems with different fixes: A is an onboarding or call-forwarding failure, C
is a shop that stopped using something it once used.

### 6. Stalled setups

Every shop whose `Live Status` falls in the setup bucket:

```
KLR Massage Croydon — Not Started · ค้าง 187 วัน · @Namva
   รอ Finn Spa เซ็ต Fresha ก่อน
```

Days stalled is today minus `Register Date`; the status comes from
`Set up Status`, the owner from `PO`, and the second line from `Note` (omitted
when empty). Sorted longest-stalled first. A missing `Register Date` sorts last
and is reported in data gaps.

### 7. Pending cancellations

Every shop in `Waiting to cancel`:

```
Me Ya Wi — Waiting to cancel · 2 ด. 30 วัน · @Noey
   เหตุผล: ยังไม่ได้บันทึก
```

An empty `Reason For Cancel` renders as `ยังไม่ได้บันทึก`, which is itself the
action item — the reason is what later churn analysis depends on.

### 8. Data gaps

Not in the original template, and required. It collects every case the rules
above had to skip:

- Monday shops with no gcal match
- gcal production shops with no Monday item
- Live shops with `billing_day = 0`
- Live shops with `allocation = 0`
- Live shops with no `Live Date`
- Shops with an empty `Live Status`
- Name matches rejected as ambiguous

Without this section those shops disappear from every count and no one finds
out. On the dashboard it sits **at the top**, in red, with its count always
visible even when collapsed; at the bottom of a long page it would never be
read.

## Dashboard

`modules/arayaBrief/views/index.php`, AdminLTE, following the conventions of
the other masterPanel modules, linked from the sidebar.

Top to bottom: title with generated-at timestamp and a refresh button; five
small-box counters from the overview; the data-gaps banner in red; the live-shop
table; then upsell and idle side by side; then stalled setups and pending
cancellations side by side; then a button that previews the Chat message.

The live-shop table columns are shop, minutes, lifespan, days until refresh.
Minutes are colour-coded: red when negative, amber under 70, green otherwise. A
💰 marker on a row means that shop also appears under upsell, which ties the
table to the section below so it reads as one shop and not two findings.

No charts. With 37 rows a table is easier to read than any graph, and a chart
here would be decoration.

The Chat-preview button renders exactly what the 07:00 cron would send. Without
it the only way to check the message is to wait a day.

`?fixture=sample` loads a committed JSON fixture instead of calling either API,
so the layout and the rules can be exercised without credentials. The same
fixture backs the regression tests for `matchShops.php` and `buildBrief.php`.

## Google Chat delivery

Plain text, not a card: long cards get truncated by Chat, and card text cannot
be copied out for reuse. The format follows the user's template, carrying the
eight in-scope sections and omitting the deferred ones entirely rather than
leaving empty headings.

Owner names from Monday `PO` are written as plain `@Namva` text, which does not
ping. Real pings need a Monday-name → Chat-user-id map; that can be added later
as a config file and does not block this work.

Cron, 07:00 Asia/Bangkok, on Linux/cPanel (confirmed: the production host is
Linux, `schtasks` is unavailable):

```
0 7 * * * /usr/bin/php /home/<user>/public_html/api/arayaBrief/sendChat.php
```

The webhook URL and the gcal admin key live in `api/arayaBrief/config.php`,
gitignored in the same way as `initDB.php`. Neither value is ever committed.

### Failure handling

- Every send writes a row to `l4u_araya_brief_logs` (date, status, payload).
- A send for a date that already has a success row is skipped, so a double cron
  fire cannot spam the space.
- **gcal unreachable → still send**, with Monday-only content and a
  `⚠️ ดึงข้อมูลนาทีไม่ได้` banner. Silence is worse than a partial brief,
  because silence is indistinguishable from "nothing to report".
- **Monday unreachable → do not send**, log the error. Monday is the head-count
  authority; without it there is no report to make.

## Testing

| Unit | Test |
|---|---|
| `matchShops.php` | Fixtures covering: calendar-ID match, email fallback, name fallback, the two KLR branches rejected as ambiguous, `(Amelia)` suffix stripped, unmatched on both sides |
| `buildBrief.php` | Fixtures per rule: upsell boundary at exactly 15 days and exactly 70 minutes, cancelling shop excluded from upsell, rule A at exactly 15%, rule C with 2 vs 3 closed periods, a shop matching A and C appearing only in C, every data-gap case |
| gcal endpoint | Wrong key → 401; demo and deleted shops absent; negative balance returned negative; `days_until_refresh` across a month boundary |
| Dashboard | Loads under `?fixture=sample`; data-gaps banner shows its count; Chat preview matches `renderChat.php` output |
| End to end | Preview against live data, with the counts checked by hand against the Monday board before the cron is enabled |

Boundary conditions are written as explicit tests because the thresholds are
where this will be wrong if it is wrong.

## Delivery order

1. gcal endpoint, with its tests.
2. masterPanel fetch, match, and build, with fixture tests.
3. Dashboard, verified by hand against the Monday board.
4. **Stop. The user confirms the numbers are right.**
5. Chat renderer, send script, logging table, and cron.

Step 4 is a gate, per the user's instruction that the dashboard comes first. A
brief with wrong numbers posted to a team space loses trust that is slow to
rebuild.
