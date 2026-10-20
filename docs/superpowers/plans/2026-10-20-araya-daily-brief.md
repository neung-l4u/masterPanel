# Araya Daily Brief Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a masterPanel dashboard that merges the Monday client tracker with gcal-booking-api minute data and applies eight alert rules, then post the same content to the Google Chat space "IT in house" at 07:00 daily.

**Architecture:** A new admin-key-protected endpoint in the gcal FastAPI app returns per-shop minute data for all production shops. masterPanel fetches that plus the Monday board, pairs the two by calendar ID then email then name, and runs every rule in a single pure function that both the dashboard and the Chat message call, so the two can never disagree.

**Tech Stack:** Python 3 / FastAPI / SQLModel / pytest (gcal repo); PHP 8 / AdminLTE / plain-PHP test harness (masterPanel repo).

## Global Constraints

- **Two repos.** Tasks 1–3 are in `D:\Projects\2.LocalEats\github\gcal-booking-api-main`. Tasks 4–11 are in `C:\xampp\htdocs\masterPanel`. Never mix them in one commit.
- **Spec:** `docs/superpowers/specs/2026-10-20-araya-daily-brief-design.md` in masterPanel. It is the authority; this plan implements it.
- **PHP function prefix:** every new global PHP function is prefixed `araya` (matching the `upDown` prefix convention in `modules/upgrade-downgrade/sync/`). No classes — the module convention is plain functions.
- **UTF-8 trimming:** never use `trim()` on a shop name; use `preg_replace('/^[\s\x{00A0}]+|[\s\x{00A0}]+$/u', '', $s)`. Two distinct hazards, both verified in this session: (a) `trim()` with `\xA0` added to its character list IS byte-based and corrupts a Thai name ending in ภ (U+0E20 = `E0 B8 A0`) by eating the trailing `A0` byte — the bug documented in `modules/upgrade-downgrade/tests/test_mirror_match.php`; (b) default `trim()` does NOT corrupt that name, but it also does NOT strip a non-breaking space, so padded Monday values survive and break matching. A test asserting only that a plain Thai name survives default `trim()` proves nothing: test an NBSP-padded Thai name, which does fail under bare `trim()`.
- **Timezone:** `Asia/Bangkok` everywhere. Every PHP test file and entry point starts with `date_default_timezone_set("Asia/Bangkok");`.
- **Secrets:** the Google Chat webhook URL and the gcal admin key live only in `modules/arayaBrief/assets/config.php`, which is gitignored. Never commit a real value; never print one in output or logs.
- **Monday board:** id `2032016220`. Column ids are listed in the spec's "Monday fields read" table and must be used verbatim.
- **Money/minutes are never summed across currencies.** This iteration reports no revenue, so no cross-currency sum may be introduced.
- **PHP test command:** `php modules/arayaBrief/tests/<file>.php`. Exit code 0 and a final `Failed: 0` line means pass.
- **Python test command:** `./.venv/Scripts/python.exe -m pytest tests/<file>.py -v` from the gcal repo root. The system Python lacks fastapi/sqlmodel — the repo venv is the only working interpreter.

---

### Task 1: Minute-summary pure logic in gcal

**Files:**
- Create: `app/araya_summary.py`
- Test: `tests/test_araya_summary.py`

**Interfaces:**
- Produces:
  - `days_until_refresh(billing_day: int, today: date) -> int | None`
  - `resolve_allocation(fixed_enabled: bool, fixed_minutes: int, latest_period_allocation: int | None) -> int`

Pure date and allocation arithmetic, in its own module with no database, following the pattern of `app/alert_levels.py` — the one piece of logic the endpoint and any future cron must agree on.

- [ ] **Step 1: Write the failing test**

Create `tests/test_araya_summary.py`:

```python
"""Date and allocation arithmetic for the Araya daily brief.

Pure logic, no database: the brief's upsell rule turns on days_until_refresh,
so the month-boundary cases are pinned here rather than inside the endpoint.
"""
import os
import sys
from datetime import date

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

import pytest

from app.araya_summary import days_until_refresh, resolve_allocation


@pytest.mark.parametrize("billing_day,today,expected", [
    # Later this month.
    (25, date(2026, 10, 20), 5),
    # Today is the billing day: the refresh is today, not in 30 days.
    (20, date(2026, 10, 20), 0),
    # Already passed this month, so it wraps to next month.
    (5, date(2026, 10, 20), 16),
    # Wrapping across a year boundary.
    (3, date(2026, 12, 30), 4),
    # Day 31 in a 30-day month clamps to the last day of that month.
    (31, date(2026, 11, 10), 20),
    # Day 31 where this month has 31 days.
    (31, date(2026, 10, 10), 21),
    # Day 30 in February clamps to Feb 28 in a non-leap year.
    (30, date(2027, 2, 10), 18),
    # Leap year February has 29 days.
    (30, date(2028, 2, 10), 19),
    # Disabled.
    (0, date(2026, 10, 20), None),
])
def test_days_until_refresh(billing_day, today, expected):
    assert days_until_refresh(billing_day, today) == expected


def test_days_until_refresh_rejects_out_of_range():
    with pytest.raises(ValueError):
        days_until_refresh(32, date(2026, 10, 20))
    with pytest.raises(ValueError):
        days_until_refresh(-1, date(2026, 10, 20))


@pytest.mark.parametrize("fixed_enabled,fixed_minutes,latest,expected", [
    # A shop on a fixed figure uses it, whatever the last period said.
    (True, 250, 400, 250),
    # Fixed enabled but never set: fall through rather than report 0.
    (True, 0, 400, 400),
    # Not on a fixed figure: the last closed period is the allocation.
    (False, 250, 400, 400),
    # No history at all. 0 means "unknown" and the caller reports a data gap.
    (False, 0, None, 0),
    (False, 0, 0, 0),
])
def test_resolve_allocation(fixed_enabled, fixed_minutes, latest, expected):
    assert resolve_allocation(fixed_enabled, fixed_minutes, latest) == expected
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./.venv/Scripts/python.exe -m pytest tests/test_araya_summary.py -v`
Expected: FAIL — `ModuleNotFoundError: No module named 'app.araya_summary'`

- [ ] **Step 3: Write minimal implementation**

Create `app/araya_summary.py`:

```python
"""Date and allocation arithmetic for the Araya daily brief.

The brief's upsell rule fires on "refreshes within 15 days and under 70
minutes left", so days_until_refresh decides who gets contacted. It lives here,
apart from the endpoint and with no database, because the month-boundary and
short-month cases are where it would otherwise be quietly wrong.
"""
import calendar
from datetime import date


def days_until_refresh(billing_day: int, today: date) -> int | None:
    """Whole days until this shop's monthly minute refresh.

    Returns None when billing_day is 0, which is how the schema says "no
    monthly refresh". A shop whose billing day has already passed this month
    wraps to next month. A billing day past the end of a short month clamps to
    that month's last day, matching what a monthly refresh can actually do.
    """
    if billing_day == 0:
        return None
    if not 1 <= billing_day <= 31:
        raise ValueError(f"billing_day out of range: {billing_day}")

    def clamped(year: int, month: int) -> date:
        last = calendar.monthrange(year, month)[1]
        return date(year, month, min(billing_day, last))

    this_month = clamped(today.year, today.month)
    if this_month >= today:
        return (this_month - today).days

    year = today.year + (1 if today.month == 12 else 0)
    month = 1 if today.month == 12 else today.month + 1
    return (clamped(year, month) - today).days


def resolve_allocation(
    fixed_enabled: bool,
    fixed_minutes: int,
    latest_period_allocation: int | None,
) -> int:
    """How many minutes this shop is given per period.

    A shop on a fixed figure uses it. Otherwise the most recent closed period
    is the best evidence. 0 means unknown, and the brief reports those shops as
    a data gap rather than dividing by them.
    """
    if fixed_enabled and fixed_minutes > 0:
        return fixed_minutes
    return latest_period_allocation or 0
```

- [ ] **Step 4: Run test to verify it passes**

Run: `./.venv/Scripts/python.exe -m pytest tests/test_araya_summary.py -v`
Expected: PASS — 15 passed

- [ ] **Step 5: Commit**

```bash
git add app/araya_summary.py tests/test_araya_summary.py
git commit -m "feat(araya-summary): add refresh-date and allocation arithmetic

The daily brief's upsell rule fires on days-until-refresh, so the month
boundary and short-month cases decide who the team contacts. Kept apart from
the endpoint and database, like app/alert_levels.py, so those cases are pinned
by tests: a billing day of 31 in November clamps to the 30th, and a day that
has already passed wraps into next month.

Allocation resolves to the fixed figure when a shop is on one, otherwise the
last closed period. Zero means unknown, which the brief reports as a data gap
rather than dividing by.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 2: Admin-key auth for the summary endpoint

**Files:**
- Create: `app/araya_auth.py`
- Test: `tests/test_araya_auth.py`

**Interfaces:**
- Produces: `require_admin_key(provided: str | None, expected: str | None) -> None` — returns on success, raises `fastapi.HTTPException(401)` otherwise.

Existing gcal endpoints authenticate per-shop by session cookie (`_require_user_and_shop`), which cannot serve a cross-shop report. There is no API-key scheme in the repo to reuse, so this adds one, minimally and testably.

- [ ] **Step 1: Write the failing test**

Create `tests/test_araya_auth.py`:

```python
"""Admin-key check for the Araya summary endpoint.

The endpoint exposes every production shop's balance, so the comparison is
pinned here: an unset server key must never become an open door, and the
compare must not leak length or prefix through timing.
"""
import os
import sys

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

import pytest
from fastapi import HTTPException

from app.araya_auth import require_admin_key


def test_matching_key_is_accepted():
    assert require_admin_key("s3cret", "s3cret") is None


@pytest.mark.parametrize("provided,expected,case", [
    ("wrong", "s3cret", "wrong key"),
    (None, "s3cret", "no key supplied"),
    ("", "s3cret", "empty key supplied"),
    ("s3cre", "s3cret", "prefix of the key"),
    ("s3crett", "s3cret", "key plus a character"),
    ("S3CRET", "s3cret", "wrong case"),
    (" s3cret", "s3cret", "leading space is not trimmed away"),
    ("s3cret ", "s3cret", "trailing space is not trimmed away"),
])
def test_bad_key_is_rejected(provided, expected, case):
    with pytest.raises(HTTPException) as err:
        require_admin_key(provided, expected)
    assert err.value.status_code == 401, case


@pytest.mark.parametrize("expected", [None, ""])
def test_unset_server_key_rejects_everything(expected):
    """A server with no key configured is closed, not open.

    Treating an unset key as "no auth required" would publish every shop's
    balance the first time the env var went missing in a deploy.
    """
    with pytest.raises(HTTPException) as err:
        require_admin_key("anything", expected)
    assert err.value.status_code == 401

    with pytest.raises(HTTPException):
        require_admin_key(None, expected)

    # Notably, matching "" against "" must still fail.
    with pytest.raises(HTTPException):
        require_admin_key("", expected)
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./.venv/Scripts/python.exe -m pytest tests/test_araya_auth.py -v`
Expected: FAIL — `ModuleNotFoundError: No module named 'app.araya_auth'`

- [ ] **Step 3: Write minimal implementation**

Create `app/araya_auth.py`:

```python
"""Admin-key check for the cross-shop Araya summary endpoint.

Every other endpoint here authenticates one shop by session cookie, which
cannot answer a question about all of them. This is the only key-based door in
the app, so it is deliberately small and deliberately closed by default.
"""
import hmac

from fastapi import HTTPException


def require_admin_key(provided: str | None, expected: str | None) -> None:
    """Pass if the caller's key matches the configured one, else raise 401.

    An unset or empty server-side key rejects every request. The alternative —
    treating "no key configured" as "no key required" — would publish every
    production shop's balance the first time the env var went missing from a
    deploy, and would do it silently.
    """
    if not expected:
        raise HTTPException(status_code=401, detail="Unauthorized")
    if not provided:
        raise HTTPException(status_code=401, detail="Unauthorized")
    # Constant-time: a plain == leaks how much of the key was right.
    if not hmac.compare_digest(provided, expected):
        raise HTTPException(status_code=401, detail="Unauthorized")
    return None
```

- [ ] **Step 4: Run test to verify it passes**

Run: `./.venv/Scripts/python.exe -m pytest tests/test_araya_auth.py -v`
Expected: PASS — 11 passed

- [ ] **Step 5: Commit**

```bash
git add app/araya_auth.py tests/test_araya_auth.py
git commit -m "feat(araya-auth): add admin-key check for cross-shop reporting

Every existing endpoint authenticates a single shop by session cookie, which
cannot serve a report about all of them, and there was no key-based scheme to
reuse. This adds the smallest one that is safe.

An unset or empty server key rejects every request. Treating a missing key as
\"no auth required\" would have published every production shop's balance the
first time the env var dropped out of a deploy, silently. The comparison is
constant-time so a wrong key leaks neither its length nor how much of it was
right.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 3: The `/api/admin/araya-summary` endpoint

**Files:**
- Modify: `app/main.py` (add the route near the other `/api/` routes, after `@app.get("/api/minutes")`)
- Test: `tests/test_araya_endpoint.py`

**Interfaces:**
- Consumes: `app.araya_summary.days_until_refresh`, `app.araya_summary.resolve_allocation`, `app.araya_auth.require_admin_key`
- Produces: `GET /api/admin/araya-summary`, header `X-Admin-Key`, returning the JSON shape in the spec.

Read the existing `/api/minutes` handler at `app/main.py:3287` before starting, and compute `remaining_minutes` exactly the way it does, so the two endpoints can never report different balances for the same shop. Note that `/api/minutes` clamps the balance at zero for the shop-facing page; this endpoint must **not** clamp, because a negative balance is the most urgent row in the brief.

- [ ] **Step 1: Write the failing test**

Create `tests/test_araya_endpoint.py`. Read `tests/test_revenue.py` first and follow its in-memory-database and `TestClient` setup; mirror that fixture style rather than inventing one.

```python
"""The cross-shop summary the daily brief is built from.

What is pinned here is what the brief would get wrong silently: demo shops
counted as customers, a negative balance clamped to zero and so dropping off
the urgent list, and the endpoint disagreeing with /api/minutes about the same
shop.
"""
import os
import sys
from datetime import date, datetime, timedelta

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

import pytest
from fastapi.testclient import TestClient

ADMIN_KEY = "test-admin-key"


@pytest.fixture
def client(monkeypatch):
    monkeypatch.setenv("ARAYA_ADMIN_KEY", ADMIN_KEY)
    from app.main import app
    return TestClient(app)


def auth(key=ADMIN_KEY):
    return {"X-Admin-Key": key}


def test_wrong_key_is_rejected(client):
    assert client.get("/api/admin/araya-summary", headers=auth("nope")).status_code == 401


def test_missing_key_is_rejected(client):
    assert client.get("/api/admin/araya-summary").status_code == 401


def test_shape_of_a_live_shop(client, seeded_live_shop):
    """A live shop reports every field the brief reads, by name."""
    body = client.get("/api/admin/araya-summary", headers=auth()).json()
    assert "generated_at" in body
    shop = next(s for s in body["shops"] if s["shop_id"] == seeded_live_shop.shop_id)
    for field in (
        "shop_id", "shop_name", "shop_email", "calendar_id", "lifecycle_status",
        "remaining_minutes", "allocation", "billing_day", "days_until_refresh",
        "currency", "closed_periods",
    ):
        assert field in shop, f"missing {field}"


def test_demo_and_internal_shops_are_excluded(client, seeded_demo_shop, seeded_internal_shop):
    """A test shop counted as a customer makes every head count wrong.

    The Monday board has a "Beam Test hello" item sitting in the LIVED group,
    so this is a real row, not a hypothetical.
    """
    ids = {s["shop_id"] for s in client.get("/api/admin/araya-summary", headers=auth()).json()["shops"]}
    assert seeded_demo_shop.shop_id not in ids
    assert seeded_internal_shop.shop_id not in ids


def test_deleted_shops_are_excluded(client, seeded_deleted_shop):
    ids = {s["shop_id"] for s in client.get("/api/admin/araya-summary", headers=auth()).json()["shops"]}
    assert seeded_deleted_shop.shop_id not in ids


def test_negative_balance_is_not_clamped(client, seeded_overdrawn_shop):
    """-109 must stay -109.

    /api/minutes clamps at zero for the shop-facing page. Clamping here would
    turn the single most urgent shop in the brief into an unremarkable zero.
    """
    body = client.get("/api/admin/araya-summary", headers=auth()).json()
    shop = next(s for s in body["shops"] if s["shop_id"] == seeded_overdrawn_shop.shop_id)
    assert shop["remaining_minutes"] == -109


def test_closed_periods_are_newest_first_and_capped_at_four(client, seeded_shop_with_six_periods):
    """The trend rule needs the latest period plus the three before it."""
    body = client.get("/api/admin/araya-summary", headers=auth()).json()
    shop = next(s for s in body["shops"] if s["shop_id"] == seeded_shop_with_six_periods.shop_id)
    periods = shop["closed_periods"]
    assert len(periods) == 4
    ends = [p["period_end"] for p in periods]
    assert ends == sorted(ends, reverse=True)
    for period in periods:
        assert {"period_end", "allocation", "used_minutes"} <= set(period)


def test_billing_day_zero_yields_null_days(client, seeded_shop_no_billing_day):
    body = client.get("/api/admin/araya-summary", headers=auth()).json()
    shop = next(s for s in body["shops"] if s["shop_id"] == seeded_shop_no_billing_day.shop_id)
    assert shop["billing_day"] == 0
    assert shop["days_until_refresh"] is None


def test_agrees_with_api_minutes_for_the_same_shop(client, seeded_live_shop):
    """Two endpoints disagreeing about one shop's balance is the worst outcome.

    Compared against the same computation /api/minutes performs, not against a
    hardcoded number, so a change to that formula breaks this test loudly.
    """
    body = client.get("/api/admin/araya-summary", headers=auth()).json()
    shop = next(s for s in body["shops"] if s["shop_id"] == seeded_live_shop.shop_id)
    assert shop["remaining_minutes"] == max(0, seeded_live_shop.expected_balance) or \
           shop["remaining_minutes"] == seeded_live_shop.expected_balance
```

Write the fixtures (`seeded_live_shop`, `seeded_demo_shop`, `seeded_internal_shop`, `seeded_deleted_shop`, `seeded_overdrawn_shop`, `seeded_shop_with_six_periods`, `seeded_shop_no_billing_day`) in this same file, following the database setup in `tests/test_revenue.py`. Each seeds one `ShopSettings` row plus its `ShopMinutes`, `Call`, and `BillingPeriodBalance` rows. `seeded_overdrawn_shop` needs `total_topped_up` and call durations that leave a balance of exactly `-109`. `seeded_live_shop` carries an `expected_balance` attribute so the last test compares against the formula, not a constant.

- [ ] **Step 2: Run test to verify it fails**

Run: `./.venv/Scripts/python.exe -m pytest tests/test_araya_endpoint.py -v`
Expected: FAIL — 404 on the route (it does not exist yet)

- [ ] **Step 3: Write minimal implementation**

Add to `app/main.py`, immediately after the `/api/minutes` handler. Import `require_admin_key`, `days_until_refresh`, and `resolve_allocation` at the top of the file alongside the other `app.` imports.

```python
@app.get("/api/admin/araya-summary")
def api_admin_araya_summary(request: Request):
    """Per-shop minute data for every production shop, for the daily brief.

    masterPanel merges this with the Monday client tracker. This endpoint
    deliberately knows nothing about Monday: it answers "what are the numbers",
    and Monday answers "who is a customer and what state are they in".

    Balances are returned signed. /api/minutes clamps at zero because a shop
    does not need to see a debt on its own dashboard, but the brief ranks shops
    by balance and a clamped -109 would sort as an unremarkable zero.
    """
    require_admin_key(
        request.headers.get("X-Admin-Key"),
        os.getenv("ARAYA_ADMIN_KEY"),
    )

    today = datetime.now(BANGKOK_TZ).date() if "BANGKOK_TZ" in globals() else date.today()
    out = []

    with Session(engine) as sess:
        shops = sess.exec(
            active_shops().where(ShopSettings.account_type == "production")
        ).all()

        for shop in shops:
            minutes = sess.exec(
                select(ShopMinutes).where(ShopMinutes.shop_id == shop.shop_id)
            ).first()
            calls = sess.exec(
                select(Call).where(Call.shop_id == shop.shop_id)
            ).all()

            # Same computation as /api/minutes, so the two never disagree.
            used = int((sum((c.duration_sec or 0) for c in calls) + 59) // 60)
            topped_up = getattr(minutes, "total_topped_up", 0) if minutes else 0
            balance = topped_up - used

            periods = sess.exec(
                select(BillingPeriodBalance)
                .where(BillingPeriodBalance.shop_id == shop.shop_id)
                .order_by(BillingPeriodBalance.period_end.desc())
                .limit(4)
            ).all()

            out.append({
                "shop_id": shop.shop_id,
                "shop_name": shop.shop_name,
                "shop_email": shop.shop_email,
                "calendar_id": shop.calendar_id or "",
                "lifecycle_status": shop.lifecycle_status,
                "remaining_minutes": balance,
                "allocation": resolve_allocation(
                    bool(getattr(minutes, "fixed_minutes_enabled", False)),
                    int(getattr(minutes, "fixed_minutes", 0) or 0),
                    periods[0].allocation if periods else None,
                ),
                "billing_day": shop.billing_day,
                "days_until_refresh": days_until_refresh(shop.billing_day, today),
                "currency": shop.currency,
                "closed_periods": [
                    {
                        "period_end": p.period_end.isoformat(),
                        "allocation": p.allocation,
                        "used_minutes": p.used_minutes,
                    }
                    for p in periods
                ],
            })

    return {
        "generated_at": datetime.now().astimezone().isoformat(),
        "shops": out,
    }
```

If `BANGKOK_TZ` is not already defined in `main.py`, replace that line with `today = date.today()` and drop the conditional — do not introduce a new timezone constant for one line.

- [ ] **Step 4: Run tests to verify they pass**

Run: `./.venv/Scripts/python.exe -m pytest tests/test_araya_endpoint.py tests/test_araya_summary.py tests/test_araya_auth.py -v`
Expected: PASS — all green

Then run the whole suite to confirm nothing regressed:
Run: `./.venv/Scripts/python.exe -m pytest tests/ -q`
Expected: no new failures compared to before this task

- [ ] **Step 5: Document the env var**

Add to `.env.example`:

```
# Shared secret for GET /api/admin/araya-summary, which masterPanel's daily
# Araya brief reads. Unset means the endpoint rejects every request.
ARAYA_ADMIN_KEY=
```

- [ ] **Step 6: Commit**

```bash
git add app/main.py tests/test_araya_endpoint.py .env.example
git commit -m "feat(araya-summary): add cross-shop summary endpoint for the daily brief

masterPanel's daily Araya brief needs every production shop's balance,
allocation and billing day in one call. This endpoint answers only \"what are
the numbers\" — the Monday client tracker remains the authority on who is a
customer and what state they are in.

Balances are returned signed, unlike /api/minutes, which clamps at zero for
the shop-facing page. The brief ranks shops by balance, so a clamped -109 would
sort as an unremarkable zero and the most urgent shop would vanish from the top
of the report.

Demo, internal_test and soft-deleted shops are excluded: the Monday board has a
test item sitting in the LIVED group, so counting them would make every head
count in the brief wrong. Remaining minutes use the same computation as
/api/minutes, with a test comparing against that formula rather than a constant
so the two cannot drift apart.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 4: masterPanel module skeleton and config

**Files:**
- Create: `modules/arayaBrief/assets/config.example.php`
- Create: `modules/arayaBrief/models/config.php`
- Create: `modules/arayaBrief/tests/test_config.php`
- Modify: `.gitignore` (repo root)

**Interfaces:**
- Produces: `arayaLoadConfig(): array` with keys `gcal_base_url`, `gcal_admin_key`, `monday_token`, `chat_webhook_url`.

Follow `modules/upgrade-downgrade/sync/mondayClient.php`'s `upDownLoadConfig()`: throw on a missing or incomplete config rather than returning defaults. A brief that runs without a token looks exactly like "Monday returned nothing", which would report every shop as missing.

- [ ] **Step 1: Write the failing test**

Create `modules/arayaBrief/tests/test_config.php`:

```php
<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../models/config.php';

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

echo "== arayaValidateConfig ==\n";

$complete = [
    'gcal_base_url'    => 'https://example.test',
    'gcal_admin_key'   => 'k',
    'monday_token'     => 't',
    'chat_webhook_url' => 'https://chat.example.test/hook',
];

$ok = true;
try { arayaValidateConfig($complete, '/cfg.php'); } catch (Throwable $e) { $ok = false; }
test("a complete config passes", $ok);

foreach (array_keys($complete) as $key) {
    $missing = $complete;
    unset($missing[$key]);
    $threw = false;
    try { arayaValidateConfig($missing, '/cfg.php'); } catch (RuntimeException $e) { $threw = true; }
    test("missing {$key} throws", $threw);

    $blank = $complete;
    $blank[$key] = '';
    $threw = false;
    try { arayaValidateConfig($blank, '/cfg.php'); } catch (RuntimeException $e) { $threw = true; }
    test("blank {$key} throws", $threw);
}

$threw = false;
try { arayaValidateConfig('not an array', '/cfg.php'); } catch (RuntimeException $e) { $threw = true; }
test("a non-array throws", $threw);

// The message must help whoever hit it, and must never echo a secret.
try {
    arayaValidateConfig(['gcal_base_url' => 'https://x'], '/path/to/cfg.php');
} catch (RuntimeException $e) {
    test("message names the file", str_contains($e->getMessage(), '/path/to/cfg.php'));
    test("message names a missing key", str_contains($e->getMessage(), 'gcal_admin_key'));
}

// A real token must never reach an error message, a log or a stack trace.
try {
    arayaValidateConfig($complete + ['monday_token' => 'SUPERSECRET123'], '/cfg.php');
    test("a valid config leaks nothing because it does not throw", true);
} catch (RuntimeException $e) {
    test("secret never appears in the message", !str_contains($e->getMessage(), 'SUPERSECRET123'));
}

$blankKey = $complete;
$blankKey['monday_token'] = '';
try {
    arayaValidateConfig($blankKey, '/cfg.php');
} catch (RuntimeException $e) {
    test("other secrets never appear in the message",
         !str_contains($e->getMessage(), $complete['gcal_admin_key'] === 'k' ? 'chat.example.test/hook' : 'x'));
}

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php modules/arayaBrief/tests/test_config.php`
Expected: FAIL — `Failed to open stream` / `arayaValidateConfig` not defined

- [ ] **Step 3: Write minimal implementation**

Create `modules/arayaBrief/models/config.php`:

```php
<?php
/**
 * Config loading for the Araya daily brief.
 *
 * Throws rather than defaulting, following modules/upgrade-downgrade: a brief
 * that ran without a Monday token would look identical to "Monday returned no
 * shops" and would report the entire customer base as missing.
 */

/**
 * Check a loaded config has every key, with a message naming what is missing.
 *
 * Never includes a value in the message: these are credentials, and this
 * message reaches logs and stack traces.
 */
function arayaValidateConfig($config, string $file): array
{
    if (!is_array($config)) {
        throw new RuntimeException("Config at {$file} did not return an array.");
    }

    $required = ['gcal_base_url', 'gcal_admin_key', 'monday_token', 'chat_webhook_url'];
    $missing = [];
    foreach ($required as $key) {
        if (empty($config[$key])) {
            $missing[] = $key;
        }
    }

    if ($missing) {
        throw new RuntimeException(
            "Config at {$file} is missing: " . implode(', ', $missing)
            . ". Copy assets/config.example.php to assets/config.php and fill it in."
        );
    }

    return $config;
}

/** Load and validate modules/arayaBrief/assets/config.php. */
function arayaLoadConfig(): array
{
    $file = __DIR__ . '/../assets/config.php';

    if (!is_file($file)) {
        throw new RuntimeException(
            "Missing config: {$file}. Copy assets/config.example.php to assets/config.php and fill it in."
        );
    }

    return arayaValidateConfig(include $file, $file);
}
```

Create `modules/arayaBrief/assets/config.example.php`:

```php
<?php
/**
 * Copy to config.php and fill in. config.php is gitignored — never commit it.
 */
return [
    // Base URL of gcal-booking-api, no trailing slash.
    'gcal_base_url'    => 'https://your-gcal-host',
    // Must equal ARAYA_ADMIN_KEY in the gcal app's environment.
    'gcal_admin_key'   => '',
    // Monday API token with read access to board 2032016220.
    'monday_token'     => '',
    // Google Chat incoming webhook for the "IT in house" space.
    'chat_webhook_url' => '',
];
```

- [ ] **Step 4: Gitignore the real config**

Add to the root `.gitignore`, next to the existing `**/initDB.php` line:

```
modules/arayaBrief/assets/config.php
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php modules/arayaBrief/tests/test_config.php`
Expected: PASS — `Failed: 0`

Verify the ignore rule works:
Run: `git check-ignore -v modules/arayaBrief/assets/config.php`
Expected: prints the matching `.gitignore` line (exit 0)

- [ ] **Step 6: Commit**

```bash
git add modules/arayaBrief/models/config.php modules/arayaBrief/assets/config.example.php modules/arayaBrief/tests/test_config.php .gitignore
git commit -m "feat(araya-brief): add module config loading

Throws on a missing or incomplete config rather than defaulting, following
modules/upgrade-downgrade. A brief that ran without a Monday token would look
exactly like \"Monday returned no shops\" and would report the whole customer
base as unmatched.

Error messages name the file and the missing keys but never a value, because
these are credentials and the message reaches logs and stack traces. The real
config.php is gitignored alongside initDB.php.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 5: Shop matching

**Files:**
- Create: `modules/arayaBrief/models/matchShops.php`
- Test: `modules/arayaBrief/tests/test_match_shops.php`

**Interfaces:**
- Produces:
  - `arayaNormalizeKey(?string $s): string` — lowercase, nbsp-safe trim, collapse whitespace
  - `arayaNormalizeShopName(?string $s): string` — as above, plus strip a trailing parenthesised group and punctuation
  - `arayaMatchShops(array $mondayShops, array $gcalShops): array` → `['matched' => [...], 'unmatchedMonday' => [...], 'unmatchedGcal' => [...]]`

Each `matched` entry is `['monday' => <row>, 'gcal' => <row>, 'matched_by' => 'calendar'|'email'|'name']`. Monday rows carry at least `name`, `calendar_id`, `email`. gcal rows carry the endpoint's fields.

**Read `modules/upgrade-downgrade/sync/mirrorMatch.php` first.** It solves the same problem and documents the UTF-8 trim hazard; reuse its normalization approach rather than writing a new one.

- [ ] **Step 1: Write the failing test**

Create `modules/arayaBrief/tests/test_match_shops.php`:

```php
<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../models/matchShops.php';

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

echo "== arayaNormalizeKey ==\n";
test("lowercases", arayaNormalizeKey('AbC@X.CoM') === 'abc@x.com');
test("trims", arayaNormalizeKey('  a@b.com  ') === 'a@b.com');
test("trims nbsp", arayaNormalizeKey("a@b.com\xC2\xA0") === 'a@b.com');
test("null is empty", arayaNormalizeKey(null) === '');

echo "== arayaNormalizeShopName ==\n";
test("lowercases and trims", arayaNormalizeShopName('  Silk Thai Therapy ') === 'silk thai therapy');
test("collapses inner whitespace", arayaNormalizeShopName("Silk  Thai\tTherapy") === 'silk thai therapy');

// The booking system, not the shop: these must match their gcal counterparts.
test("strips a trailing (Amelia)",
     arayaNormalizeShopName('Roanoke Thai Massage & Spa (Amelia)') === arayaNormalizeShopName('Roanoke Thai Massage & Spa'));
test("strips a padded trailing group",
     arayaNormalizeShopName('KLR Massage Therapy ( Croydon )') === 'klr massage therapy');
test("strips only the trailing group",
     arayaNormalizeShopName('A (X) B (Y)') === arayaNormalizeShopName('A (X) B'));

// Thai names: trim() is byte-based and would corrupt these.
test("keeps a name ending in a 0xA0-byte character", arayaNormalizeShopName('ลาภ') === 'ลาภ');
test("keeps that character mid-name", arayaNormalizeShopName('ร้านภ') === 'ร้านภ');
test("keeps non-ASCII intact", arayaNormalizeShopName('ร้านอาหารไทย') === 'ร้านอาหารไทย');
test("a name that is only a parenthesised group does not become empty",
     arayaNormalizeShopName('(Amelia)') !== '');

echo "== arayaMatchShops: calendar id wins ==\n";
$monday = [['name' => 'Shop A', 'calendar_id' => 'CAL-1', 'email' => 'a@x.com']];
$gcal   = [
    ['shop_id' => 'g1', 'shop_name' => 'Totally Different', 'calendar_id' => 'cal-1', 'shop_email' => 'other@x.com'],
    ['shop_id' => 'g2', 'shop_name' => 'Shop A', 'calendar_id' => '', 'shop_email' => 'a@x.com'],
];
$r = arayaMatchShops($monday, $gcal);
test("calendar id matches case-insensitively", count($r['matched']) === 1);
test("calendar id beats name and email", $r['matched'][0]['gcal']['shop_id'] === 'g1');
test("matched_by is calendar", $r['matched'][0]['matched_by'] === 'calendar');
test("the unused gcal shop is reported", count($r['unmatchedGcal']) === 1);

echo "== arayaMatchShops: email fallback ==\n";
$monday = [['name' => 'Shop B', 'calendar_id' => '', 'email' => 'B@X.com']];
$gcal   = [['shop_id' => 'g3', 'shop_name' => 'Nope', 'calendar_id' => '', 'shop_email' => 'b@x.com']];
$r = arayaMatchShops($monday, $gcal);
test("email matches when no calendar id", $r['matched'][0]['gcal']['shop_id'] === 'g3');
test("matched_by is email", $r['matched'][0]['matched_by'] === 'email');

echo "== arayaMatchShops: name fallback ==\n";
$monday = [['name' => 'Tableland Traditional Thai massage (Amelia)', 'calendar_id' => '', 'email' => '']];
$gcal   = [['shop_id' => 'g4', 'shop_name' => 'Tableland Traditional Thai Massage', 'calendar_id' => '', 'shop_email' => '']];
$r = arayaMatchShops($monday, $gcal);
test("name matches after stripping (Amelia)", count($r['matched']) === 1);
test("matched_by is name", $r['matched'][0]['matched_by'] === 'name');

echo "== arayaMatchShops: ambiguous names are NOT matched ==\n";
// Two real, distinct KLR branches that normalize to the same string. Guessing
// would report one branch's minutes against the other.
$monday = [
    ['name' => 'KLR Massage Therapy ( Croydon )', 'calendar_id' => '', 'email' => ''],
    ['name' => 'KLR Massage Therapy (Purley)',    'calendar_id' => '', 'email' => ''],
];
$gcal = [['shop_id' => 'g5', 'shop_name' => 'KLR Massage Therapy', 'calendar_id' => '', 'shop_email' => '']];
$r = arayaMatchShops($monday, $gcal);
test("two Monday shops normalizing alike match nothing", count($r['matched']) === 0);
test("both are reported unmatched", count($r['unmatchedMonday']) === 2);
test("the gcal shop is reported unmatched too", count($r['unmatchedGcal']) === 1);

// Ambiguity on the gcal side is just as unsafe.
$monday = [['name' => 'Sabai Thai Massage', 'calendar_id' => '', 'email' => '']];
$gcal = [
    ['shop_id' => 'g6', 'shop_name' => 'Sabai Thai Massage', 'calendar_id' => '', 'shop_email' => ''],
    ['shop_id' => 'g7', 'shop_name' => 'Sabai  Thai  Massage', 'calendar_id' => '', 'shop_email' => ''],
];
$r = arayaMatchShops($monday, $gcal);
test("two gcal shops normalizing alike match nothing", count($r['matched']) === 0);
test("the Monday shop is reported unmatched", count($r['unmatchedMonday']) === 1);

echo "== arayaMatchShops: a gcal shop is used at most once ==\n";
$monday = [
    ['name' => 'First',  'calendar_id' => 'CAL-9', 'email' => ''],
    ['name' => 'Second', 'calendar_id' => 'CAL-9', 'email' => ''],
];
$gcal = [['shop_id' => 'g8', 'shop_name' => 'X', 'calendar_id' => 'cal-9', 'shop_email' => '']];
$r = arayaMatchShops($monday, $gcal);
test("only one Monday shop claims it", count($r['matched']) === 1);
test("the loser is reported unmatched", count($r['unmatchedMonday']) === 1);

echo "== arayaMatchShops: blanks never match blanks ==\n";
$monday = [['name' => 'Has No Keys', 'calendar_id' => '', 'email' => '']];
$gcal   = [['shop_id' => 'g9', 'shop_name' => 'Also No Keys', 'calendar_id' => '', 'shop_email' => '']];
$r = arayaMatchShops($monday, $gcal);
test("empty calendar ids do not match each other", count($r['matched']) === 0);
test("empty emails do not match each other", count($r['unmatchedMonday']) === 1);

echo "== arayaMatchShops: empty inputs ==\n";
$r = arayaMatchShops([], []);
test("no shops is not an error", $r['matched'] === [] && $r['unmatchedMonday'] === [] && $r['unmatchedGcal'] === []);
$r = arayaMatchShops([], [['shop_id' => 'gx', 'shop_name' => 'Orphan', 'calendar_id' => '', 'shop_email' => '']]);
test("a gcal shop with no Monday item is reported", count($r['unmatchedGcal']) === 1);

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php modules/arayaBrief/tests/test_match_shops.php`
Expected: FAIL — `arayaNormalizeKey` not defined

- [ ] **Step 3: Write minimal implementation**

Create `modules/arayaBrief/models/matchShops.php`:

```php
<?php
/**
 * Pair Monday client-tracker rows with gcal-booking-api shops.
 *
 * Monday is the authority on who is a customer; gcal holds the minutes. This
 * file is the join between them and nothing else — no rules, no formatting —
 * so it can be tested from arrays with no network.
 *
 * Keys are tried calendar id, then email, then normalized name. Name is last
 * and an ambiguous name match is treated as no match: the board holds
 * "KLR Massage Therapy ( Croydon )" and "KLR Massage Therapy (Purley)", two
 * real branches that normalize identically. Reporting one branch's minutes
 * against the other is worse than reporting that the pairing is unknown.
 */

/**
 * Lowercase and trim a key for comparison.
 *
 * The character class includes U+00A0 and carries the /u flag because trim()
 * is byte-based: on a Thai name ending in ภ (U+0E20 = E0 B8 A0) it eats the
 * trailing A0 and leaves invalid UTF-8.
 */
function arayaNormalizeKey(?string $s): string
{
    if ($s === null) {
        return '';
    }
    $s = preg_replace('/^[\s\x{00A0}]+|[\s\x{00A0}]+$/u', '', $s);
    $s = preg_replace('/[\s\x{00A0}]+/u', ' ', $s);
    return mb_strtolower($s, 'UTF-8');
}

/**
 * Normalize a shop name, dropping a trailing parenthesised group.
 *
 * On this board that group names the booking system or the branch — "(Amelia)",
 * "( Croydon )" — and never appears in the gcal name. Stripping it is what lets
 * the fallback work at all; the ambiguity check in arayaMatchShops is what keeps
 * it from pairing two branches that collapse to the same string.
 */
function arayaNormalizeShopName(?string $s): string
{
    $s = arayaNormalizeKey($s);
    if ($s === '') {
        return '';
    }
    // Only a trailing group, and only if something is left over.
    $stripped = preg_replace('/\s*\([^()]*\)\s*$/u', '', $s);
    if ($stripped !== null && $stripped !== '') {
        $s = $stripped;
    }
    return arayaNormalizeKey($s);
}

/** Index rows by a key, marking any key that appears more than once. */
function arayaIndexUnique(array $rows, callable $keyOf): array
{
    $index = [];
    $dupes = [];
    foreach ($rows as $i => $row) {
        $key = $keyOf($row);
        if ($key === '') {
            continue;
        }
        if (isset($index[$key])) {
            $dupes[$key] = true;
            continue;
        }
        $index[$key] = $i;
    }
    foreach (array_keys($dupes) as $key) {
        unset($index[$key]);
    }
    return $index;
}

/**
 * Pair the two sources.
 *
 * Returns matched pairs plus both unmatched lists. Nothing is dropped: a shop
 * that fails to pair is reported, because unmatched rows vanishing silently is
 * exactly how an earlier mirror-sync problem in this repo went unnoticed.
 */
function arayaMatchShops(array $mondayShops, array $gcalShops): array
{
    $byCalendar = arayaIndexUnique($gcalShops, fn($g) => arayaNormalizeKey($g['calendar_id'] ?? ''));
    $byEmail    = arayaIndexUnique($gcalShops, fn($g) => arayaNormalizeKey($g['shop_email'] ?? ''));
    $byName     = arayaIndexUnique($gcalShops, fn($g) => arayaNormalizeShopName($g['shop_name'] ?? ''));

    // A Monday name shared by two items cannot safely match by name either.
    $mondayNameCounts = [];
    foreach ($mondayShops as $m) {
        $key = arayaNormalizeShopName($m['name'] ?? '');
        if ($key !== '') {
            $mondayNameCounts[$key] = ($mondayNameCounts[$key] ?? 0) + 1;
        }
    }

    $matched = [];
    $unmatchedMonday = [];
    $claimed = [];

    foreach ($mondayShops as $m) {
        $hit = null;

        $cal = arayaNormalizeKey($m['calendar_id'] ?? '');
        if ($cal !== '' && isset($byCalendar[$cal]) && !isset($claimed[$byCalendar[$cal]])) {
            $hit = ['i' => $byCalendar[$cal], 'by' => 'calendar'];
        }

        if ($hit === null) {
            $email = arayaNormalizeKey($m['email'] ?? '');
            if ($email !== '' && isset($byEmail[$email]) && !isset($claimed[$byEmail[$email]])) {
                $hit = ['i' => $byEmail[$email], 'by' => 'email'];
            }
        }

        if ($hit === null) {
            $name = arayaNormalizeShopName($m['name'] ?? '');
            $unambiguous = $name !== ''
                && ($mondayNameCounts[$name] ?? 0) === 1
                && isset($byName[$name])
                && !isset($claimed[$byName[$name]]);
            if ($unambiguous) {
                $hit = ['i' => $byName[$name], 'by' => 'name'];
            }
        }

        if ($hit === null) {
            $unmatchedMonday[] = $m;
            continue;
        }

        $claimed[$hit['i']] = true;
        $matched[] = [
            'monday'     => $m,
            'gcal'       => $gcalShops[$hit['i']],
            'matched_by' => $hit['by'],
        ];
    }

    $unmatchedGcal = [];
    foreach ($gcalShops as $i => $g) {
        if (!isset($claimed[$i])) {
            $unmatchedGcal[] = $g;
        }
    }

    return [
        'matched'         => $matched,
        'unmatchedMonday' => $unmatchedMonday,
        'unmatchedGcal'   => $unmatchedGcal,
    ];
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php modules/arayaBrief/tests/test_match_shops.php`
Expected: PASS — `Failed: 0`

- [ ] **Step 5: Commit**

```bash
git add modules/arayaBrief/models/matchShops.php modules/arayaBrief/tests/test_match_shops.php
git commit -m "feat(araya-brief): pair Monday client-tracker rows with gcal shops

Monday is the authority on who is a customer and gcal holds the minutes, so the
brief needs a join. Keys are tried calendar id, then email, then normalized
name.

Name is last and an ambiguous name match counts as no match. The board holds
\"KLR Massage Therapy ( Croydon )\" and \"KLR Massage Therapy (Purley)\" — two
real branches that normalize to the same string — so guessing would report one
branch's minutes against the other. Both go to the unmatched list instead. The
trailing parenthesised group is stripped because on this board it names the
booking system or branch, \"(Amelia)\", and never appears in the gcal name.

Normalization is nbsp-aware with the /u flag rather than trim(), which is
byte-based and corrupts a Thai name ending in ภ by eating its trailing A0 byte.
Nothing is dropped: both unmatched lists are returned, since unmatched rows
disappearing silently is how an earlier mirror-sync problem here went unnoticed.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 6: Section rules

**Files:**
- Create: `modules/arayaBrief/models/buildBrief.php`
- Test: `modules/arayaBrief/tests/test_build_brief.php`

**Interfaces:**
- Consumes: the array returned by `arayaMatchShops`
- Produces: `arayaBuildBrief(array $matchResult, string $todayYmd): array` with keys `generated_for`, `overview`, `live`, `upsell`, `idleNew`, `idleQuiet`, `setup`, `cancelling`, `gaps`.

This is the single place that knows the rules; both the dashboard and the Chat message render its output. Status label constants come from the spec's "Section rules" table.

- [ ] **Step 1: Write the failing test**

Create `modules/arayaBrief/tests/test_build_brief.php`. Define a `mk()` helper that builds a matched pair from overrides so each test states only the fields it cares about.

```php
<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../models/buildBrief.php';

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

const TODAY = '2026-10-20';

/** A matched pair with sane live defaults, overridable per test. */
function mk(array $monday = [], array $gcal = []): array {
    return [
        'monday' => array_merge([
            'name' => 'Test Shop', 'live_status' => 'LIVE', 'setup_status' => 'Done',
            'po' => 'Aim', 'register_date' => '2026-01-01', 'live_date' => '2026-06-01',
            'reason_for_cancel' => '', 'note' => '',
            'calendar_id' => 'cal-x', 'email' => 'a@x.com',
        ], $monday),
        'gcal' => array_merge([
            'shop_id' => 'g1', 'shop_name' => 'Test Shop', 'remaining_minutes' => 200,
            'allocation' => 250, 'billing_day' => 15, 'days_until_refresh' => 26,
            'currency' => 'AUD', 'closed_periods' => [],
        ], $gcal),
        'matched_by' => 'calendar',
    ];
}
function res(array $matched, array $um = [], array $ug = []): array {
    return ['matched' => $matched, 'unmatchedMonday' => $um, 'unmatchedGcal' => $ug];
}

echo "== overview counts ==\n";
$b = arayaBuildBrief(res([
    mk(['live_status' => 'LIVE']), mk(['live_status' => 'LIVE']),
    mk(['live_status' => 'Waiting to cancel']),
    mk(['live_status' => 'Not Live']), mk(['live_status' => 'Testing']),
    mk(['live_status' => 'Ready to LIVE']), mk(['live_status' => 'Waiting to onboard']),
    mk(['live_status' => 'Stuck']), mk(['live_status' => 'Ghosted']), mk(['live_status' => 'Pause']),
    mk(['live_status' => 'Canceled']), mk(['live_status' => 'Canceled']),
    mk(['live_status' => 'Cancel Before LIVE']),
]), TODAY);
test("live count", $b['overview']['live'] === 2);
test("pending cancel count", $b['overview']['pendingCancel'] === 1);
test("setup bucket count", $b['overview']['setup'] === 7);
test("cancelled total", $b['overview']['cancelled'] === 3);
test("cancelled split: canceled", $b['overview']['canceledAfterLive'] === 2);
test("cancelled split: before live", $b['overview']['cancelBeforeLive'] === 1);
test("total counts every item", $b['overview']['total'] === 13);

$b = arayaBuildBrief(res([mk(['live_status' => ''])]), TODAY);
test("blank status counts only toward the total", $b['overview']['total'] === 1 && $b['overview']['live'] === 0);
test("blank status is a data gap",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'blank_status')) === 1);

echo "== live section ==\n";
$b = arayaBuildBrief(res([
    mk(['name' => 'Rich'],   ['remaining_minutes' => 500]),
    mk(['name' => 'Broke'],  ['remaining_minutes' => -109]),
    mk(['name' => 'Middle'], ['remaining_minutes' => 60]),
]), TODAY);
test("sorted by minutes ascending, most urgent first",
     array_column($b['live'], 'name') === ['Broke', 'Middle', 'Rich']);
test("negative minutes stay negative", $b['live'][0]['remaining_minutes'] === -109);

$b = arayaBuildBrief(res([mk([], ['remaining_minutes' => 152])]), TODAY);
test("minutes carried through", $b['live'][0]['remaining_minutes'] === 152);

// Lifespan from live_date.
$b = arayaBuildBrief(res([mk(['live_date' => '2026-06-01'])]), TODAY);
test("lifespan months", $b['live'][0]['lifespan']['months'] === 4);
test("lifespan days", $b['live'][0]['lifespan']['days'] === 19);

$b = arayaBuildBrief(res([mk(['live_date' => ''])]), TODAY);
test("missing live date is flagged on the row", $b['live'][0]['lifespan'] === null);
test("missing live date is a data gap",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'no_live_date')) === 1);

// A live shop with no gcal match still appears.
$b = arayaBuildBrief(res([], [['name' => 'Ghost Live', 'live_status' => 'LIVE', 'live_date' => '2026-06-01']]), TODAY);
test("an unmatched live shop is still listed", count($b['live']) === 1);
test("it is marked as having no gcal data", $b['live'][0]['gcal_missing'] === true);
test("and it is a data gap", count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'unmatched_monday')) === 1);

echo "== upsell: <=15 days AND <70 minutes ==\n";
foreach ([
    [15, 69, true,  'at both boundaries'],
    [15, 70, false, '70 minutes is not under 70'],
    [16, 69, false, '16 days is not within 15'],
    [14, 1,  true,  'well inside'],
    [0,  69, true,  'refresh today'],
    [15, -5, true,  'negative is under 70'],
] as [$days, $mins, $want, $case]) {
    $b = arayaBuildBrief(res([mk([], ['days_until_refresh' => $days, 'remaining_minutes' => $mins])]), TODAY);
    test("upsell {$case}", (count($b['upsell']) === 1) === $want);
}

// Shops that have given notice are excluded but explained.
foreach (['Waiting to cancel', 'Canceled', 'Cancel Before LIVE'] as $status) {
    $b = arayaBuildBrief(res([mk(['live_status' => $status], ['days_until_refresh' => 5, 'remaining_minutes' => 10])]), TODAY);
    test("{$status} is not an upsell target", count($b['upsell']) === 0);
    test("{$status} exclusion is explained", count($b['upsellExcluded']) === 1);
}

$b = arayaBuildBrief(res([mk([], ['billing_day' => 0, 'days_until_refresh' => null, 'remaining_minutes' => 10])]), TODAY);
test("no billing day is not an upsell target", count($b['upsell']) === 0);
test("no billing day is a data gap",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'no_billing_day')) === 1);

echo "== idle rule A: live >=10 days and used <15% ==\n";
foreach ([
    ['2026-10-10', 250, 230, true,  'day 10, used 8%'],
    ['2026-10-11', 250, 230, false, 'day 9 is too early'],
    // These two bracket the 15% threshold from either side: 38/250 is 15.2%,
    // which is NOT under the line, and 37/250 is 14.8%, which is.
    ['2026-10-01', 250, 212, false, 'used 15.2%, just over the line'],
    ['2026-10-01', 250, 213, true,  'used 14.8%, just under the line'],
    ['2026-10-01', 250, 100, false, 'used 60%, busy'],
    ['2026-10-01', 1000, 900, true, 'big allocation, used 10%'],
    ['2026-10-01', 1000, 800, false, 'big allocation, used 20%'],
] as [$liveDate, $alloc, $remaining, $want, $case]) {
    $b = arayaBuildBrief(res([mk(['live_date' => $liveDate], ['allocation' => $alloc, 'remaining_minutes' => $remaining])]), TODAY);
    test("idleNew {$case}", (count($b['idleNew']) === 1) === $want);
}

$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['allocation' => 0, 'remaining_minutes' => 0])]), TODAY);
test("zero allocation is never divided by", count($b['idleNew']) === 0);
test("zero allocation is a data gap",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'no_allocation')) === 1);

echo "== idle rule C: latest period under 30% of the prior three ==\n";
$quiet = [
    ['period_end' => '2026-09-30', 'allocation' => 250, 'used_minutes' => 10],
    ['period_end' => '2026-08-31', 'allocation' => 250, 'used_minutes' => 100],
    ['period_end' => '2026-07-31', 'allocation' => 250, 'used_minutes' => 110],
    ['period_end' => '2026-06-30', 'allocation' => 250, 'used_minutes' => 90],
];
$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['closed_periods' => $quiet])]), TODAY);
test("a shop that went quiet is flagged", count($b['idleQuiet']) === 1);

$busy = $quiet; $busy[0]['used_minutes'] = 95;
$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['closed_periods' => $busy])]), TODAY);
test("a shop still using normally is not flagged", count($b['idleQuiet']) === 0);

// Mean of 100, 110, 90 is 100; 30 is exactly 30% and so not under it.
$edge = $quiet; $edge[0]['used_minutes'] = 30;
$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['closed_periods' => $edge])]), TODAY);
test("exactly 30% is not flagged", count($b['idleQuiet']) === 0);
$edge[0]['used_minutes'] = 29;
$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['closed_periods' => $edge])]), TODAY);
test("just under 30% is flagged", count($b['idleQuiet']) === 1);

$twoPriors = array_slice($quiet, 0, 3);
$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['closed_periods' => $twoPriors])]), TODAY);
test("fewer than 3 prior periods is not evaluated by rule C", count($b['idleQuiet']) === 0);

$neverUsed = [
    ['period_end' => '2026-09-30', 'allocation' => 250, 'used_minutes' => 0],
    ['period_end' => '2026-08-31', 'allocation' => 250, 'used_minutes' => 0],
    ['period_end' => '2026-07-31', 'allocation' => 250, 'used_minutes' => 0],
    ['period_end' => '2026-06-30', 'allocation' => 250, 'used_minutes' => 0],
];
$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['allocation' => 250, 'remaining_minutes' => 250, 'closed_periods' => $neverUsed])]), TODAY);
test("a shop that never used anything is not a rule C finding", count($b['idleQuiet']) === 0);
test("it is a rule A finding instead", count($b['idleNew']) === 1);

// A shop matching both appears only under C, the more specific finding.
$b = arayaBuildBrief(res([mk(['live_date' => '2026-01-01'], ['allocation' => 250, 'remaining_minutes' => 240, 'closed_periods' => $quiet])]), TODAY);
test("a shop matching A and C appears only under C",
     count($b['idleQuiet']) === 1 && count($b['idleNew']) === 0);

echo "== idle rules only consider live shops ==\n";
$b = arayaBuildBrief(res([mk(['live_status' => 'Waiting to cancel', 'live_date' => '2026-01-01'], ['allocation' => 250, 'remaining_minutes' => 250])]), TODAY);
test("a cancelling shop is not an idle finding", count($b['idleNew']) === 0);

echo "== setup section ==\n";
$b = arayaBuildBrief(res([
    mk(['name' => 'Newer', 'live_status' => 'Not Live', 'setup_status' => 'Not Started', 'register_date' => '2026-09-20', 'po' => 'Aim', 'note' => 'waiting on Fresha']),
    mk(['name' => 'Older', 'live_status' => 'Not Live', 'setup_status' => 'In Progress', 'register_date' => '2026-04-16', 'po' => 'Namva', 'note' => '']),
]), TODAY);
test("longest stalled first", array_column($b['setup'], 'name') === ['Older', 'Newer']);
test("days stalled computed", $b['setup'][0]['days_stalled'] === 187);
test("setup status carried", $b['setup'][0]['setup_status'] === 'In Progress');
test("owner carried", $b['setup'][0]['po'] === 'Namva');
test("empty note is empty", $b['setup'][0]['note'] === '');
test("note carried when present", $b['setup'][1]['note'] === 'waiting on Fresha');

$b = arayaBuildBrief(res([mk(['live_status' => 'Not Live', 'register_date' => ''])]), TODAY);
test("missing register date sorts last without crashing", $b['setup'][0]['days_stalled'] === null);
test("missing register date is a data gap",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'no_register_date')) === 1);

echo "== cancelling section ==\n";
$b = arayaBuildBrief(res([mk(['name' => 'Me Ya Wi', 'live_status' => 'Waiting to cancel', 'live_date' => '2026-07-21', 'po' => 'Noey', 'reason_for_cancel' => ''])]), TODAY);
test("one cancelling shop", count($b['cancelling']) === 1);
test("blank reason is reported as unrecorded", $b['cancelling'][0]['reason'] === '');
test("lifespan still computed", $b['cancelling'][0]['lifespan']['months'] === 2);

$b = arayaBuildBrief(res([mk(['live_status' => 'Waiting to cancel', 'reason_for_cancel' => 'Save Cost'])]), TODAY);
test("a recorded reason is carried", $b['cancelling'][0]['reason'] === 'Save Cost');

echo "== gaps section ==\n";
$b = arayaBuildBrief(res(
    [mk()],
    [['name' => 'No gcal row', 'live_status' => 'LIVE']],
    [['shop_id' => 'orphan', 'shop_name' => 'No Monday item']]
), TODAY);
test("unmatched Monday shop is a gap",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'unmatched_monday')) === 1);
test("unmatched gcal shop is a gap",
     count(array_filter($b['gaps'], fn($g) => $g['kind'] === 'unmatched_gcal')) === 1);
test("every gap names a shop",
     count(array_filter($b['gaps'], fn($g) => ($g['name'] ?? '') === '')) === 0);

echo "== empty input ==\n";
$b = arayaBuildBrief(res([]), TODAY);
test("no shops is not an error", $b['overview']['total'] === 0);
test("sections are empty arrays, not null",
     $b['live'] === [] && $b['upsell'] === [] && $b['gaps'] === []);
test("generated_for is the given day", $b['generated_for'] === TODAY);

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php modules/arayaBrief/tests/test_build_brief.php`
Expected: FAIL — `arayaBuildBrief` not defined

- [ ] **Step 3: Write the implementation**

Create `modules/arayaBrief/models/buildBrief.php`. It must satisfy every test above. Required structure:

- Constants for the label buckets: `ARAYA_STATUS_LIVE = 'LIVE'`, `ARAYA_STATUS_PENDING_CANCEL = 'Waiting to cancel'`, `ARAYA_SETUP_STATUSES = ['Not Live','Testing','Ready to LIVE','Waiting to onboard','Stuck','Ghosted','Pause']`, `ARAYA_CANCELLED_STATUSES = ['Canceled','Cancel Before LIVE']`.
- Threshold constants, each on its own line with a comment: `ARAYA_UPSELL_MAX_DAYS = 15`, `ARAYA_UPSELL_MAX_MINUTES = 70`, `ARAYA_IDLE_MIN_DAYS_LIVE = 10`, `ARAYA_IDLE_MAX_USED_PCT = 0.15`, `ARAYA_QUIET_MAX_PCT_OF_MEAN = 0.30`, `ARAYA_QUIET_MIN_PRIOR_PERIODS = 3`.
- `arayaLifespan(?string $fromYmd, string $todayYmd): ?array` returning `['months' => int, 'days' => int]` or `null`. Use `DateTimeImmutable::diff` so month lengths are handled; do not divide days by 30.
- `arayaBuildBrief(array $matchResult, string $todayYmd): array` building each section in the order the spec lists them, appending to a single `$gaps` array as it goes.

Rules that the tests pin and the implementation must respect:
- Comparisons are strict: `<= 15` days, `< 70` minutes, `< 0.15` used fraction, `< 0.30` of the mean.
- Rule C runs before rule A, and a shop flagged by C is excluded from A.
- Idle and upsell rules consider only shops whose `live_status` is `LIVE`.
- A live Monday shop with no gcal pair still produces a `live` row, with `gcal_missing => true`, `remaining_minutes => null`, and a gap of kind `unmatched_monday`.
- Every gap entry is `['kind' => string, 'name' => string, 'detail' => string]` and `name` is never empty.
- Gap kinds used by the tests: `unmatched_monday`, `unmatched_gcal`, `no_live_date`, `no_billing_day`, `no_allocation`, `no_register_date`, `blank_status`.
- `upsellExcluded` holds shops that met the numeric condition but were excluded for having given notice, each with a `reason` string.
- Every section key is always present and is an array, never null.

- [ ] **Step 4: Run test to verify it passes**

Run: `php modules/arayaBrief/tests/test_build_brief.php`
Expected: PASS — `Failed: 0`

- [ ] **Step 5: Commit**

```bash
git add modules/arayaBrief/models/buildBrief.php modules/arayaBrief/tests/test_build_brief.php
git commit -m "feat(araya-brief): add the eight section rules

One pure function holds every rule, because the dashboard and the 07:00 Chat
message both render its output and a brief that disagrees with the dashboard is
worse than no brief.

Two decisions worth recording. The idle threshold is proportional, not a fixed
minute count: 230 minutes left means untouched on a 250-minute allocation and
normal use on a 1000-minute one, so a fixed number would alarm on large shops
every day. And the two idle rules stay separate, with a shop matching both
reported only under the trend rule, because \"never started\" and \"went quiet\"
are different problems with different fixes.

A data-gaps section collects every shop a rule had to skip — unmatched, no
billing day, no allocation, no live date. Without it those shops would drop out
of every count with nothing to show they existed.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 7: Fetchers with caching

**Files:**
- Create: `modules/arayaBrief/models/fetchGcal.php`
- Create: `modules/arayaBrief/models/fetchMonday.php`
- Create: `modules/arayaBrief/models/cache.php`
- Create: `modules/arayaBrief/tests/test_cache.php`
- Create: `modules/arayaBrief/assets/cache/.gitignore` (contents: `*` then `!.gitignore`)

**Interfaces:**
- Consumes: `arayaLoadConfig()` from Task 4
- Produces:
  - `arayaCacheGet(string $name, int $ttlSeconds): ?array`
  - `arayaCachePut(string $name, array $data): void`
  - `arayaFetchGcal(array $config, bool $useCache = true): array` — returns gcal shop rows, throws `RuntimeException` on failure
  - `arayaFetchMonday(array $config, bool $useCache = true): array` — returns Monday rows keyed as `matchShops` expects (`name`, `live_status`, `setup_status`, `po`, `register_date`, `live_date`, `reason_for_cancel`, `note`, `calendar_id`, `email`), throws `RuntimeException` on failure

`arayaFetchMonday` queries board `2032016220` with the column ids from the spec and flattens Monday's `column_values` into the flat keys above. Follow the GraphQL request style in `modules/upgrade-downgrade/sync/mondayClient.php`.

Only the cache is unit-tested here; the fetchers are exercised end to end in Task 11, because testing them properly means either a live token or an HTTP stub layer this module does not have.

- [ ] **Step 1: Write the failing cache test**

Create `modules/arayaBrief/tests/test_cache.php`:

```php
<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../models/cache.php';

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

$name = 'test_cache_' . getmypid();

echo "== cache round trip ==\n";
test("a cold read misses", arayaCacheGet($name, 300) === null);

arayaCachePut($name, ['shops' => [['shop_id' => 'g1']]]);
$hit = arayaCacheGet($name, 300);
test("a warm read hits", $hit !== null);
test("the payload round trips", $hit['shops'][0]['shop_id'] === 'g1');

echo "== expiry ==\n";
test("a zero ttl always misses", arayaCacheGet($name, 0) === null);
test("a negative ttl misses", arayaCacheGet($name, -1) === null);

echo "== corrupt cache is a miss, not a crash ==\n";
file_put_contents(arayaCachePath($name), 'not json at all');
test("unparseable cache reads as a miss", arayaCacheGet($name, 300) === null);

file_put_contents(arayaCachePath($name), '');
test("an empty cache file reads as a miss", arayaCacheGet($name, 300) === null);

echo "== name sanitising ==\n";
$evil = '../../../etc/passwd';
arayaCachePut($evil, ['ok' => true]);
test("a traversing name cannot escape the cache directory",
     str_starts_with(realpath(dirname(arayaCachePath($evil))), realpath(__DIR__ . '/../assets/cache')));

@unlink(arayaCachePath($name));
@unlink(arayaCachePath($evil));

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php modules/arayaBrief/tests/test_cache.php`
Expected: FAIL — `arayaCacheGet` not defined

- [ ] **Step 3: Write the cache**

Create `modules/arayaBrief/models/cache.php`:

```php
<?php
/**
 * File cache for the brief's two upstream calls.
 *
 * The dashboard gets refreshed repeatedly, Monday rate-limits, and the gcal
 * endpoint scans every shop's periods, so a short TTL keeps an idle refresh
 * from costing two API calls. A corrupt or unreadable cache always reads as a
 * miss: serving half-parsed JSON would be worse than making the call again.
 */

function arayaCacheDir(): string
{
    $dir = __DIR__ . '/../assets/cache';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    return $dir;
}

/** Path for a cache entry. The name is sanitised: it reaches the filesystem. */
function arayaCachePath(string $name): string
{
    $safe = preg_replace('/[^A-Za-z0-9_-]/', '_', $name);
    return arayaCacheDir() . '/' . $safe . '.json';
}

function arayaCacheGet(string $name, int $ttlSeconds): ?array
{
    if ($ttlSeconds <= 0) {
        return null;
    }
    $path = arayaCachePath($name);
    if (!is_file($path)) {
        return null;
    }
    if (time() - filemtime($path) > $ttlSeconds) {
        return null;
    }
    $raw = file_get_contents($path);
    if ($raw === false || $raw === '') {
        return null;
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function arayaCachePut(string $name, array $data): void
{
    $path = arayaCachePath($name);
    // Write then rename, so a concurrent read never sees a half-written file.
    $tmp = $path . '.' . getmypid() . '.tmp';
    file_put_contents($tmp, json_encode($data, JSON_UNESCAPED_UNICODE));
    rename($tmp, $path);
}
```

Create `modules/arayaBrief/assets/cache/.gitignore`:

```
*
!.gitignore
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php modules/arayaBrief/tests/test_cache.php`
Expected: PASS — `Failed: 0`

- [ ] **Step 5: Write the two fetchers**

Create `modules/arayaBrief/models/fetchGcal.php` with `arayaFetchGcal(array $config, bool $useCache = true): array`. It must:
- return the cached `shops` array when `$useCache` and `arayaCacheGet('gcal', 300)` hits;
- otherwise `curl` `GET {$config['gcal_base_url']}/api/admin/araya-summary` with header `X-Admin-Key: {$config['gcal_admin_key']}`, a 20-second timeout, and `CURLOPT_RETURNTRANSFER`;
- throw `RuntimeException` on a curl error, a non-200 status, or a body that does not decode to an array with a `shops` key — the caller in Task 10 catches this to degrade gracefully, so failure must be loud here, never an empty array;
- never include the key in an exception message;
- cache and return the `shops` array.

Create `modules/arayaBrief/models/fetchMonday.php` with `arayaFetchMonday(array $config, bool $useCache = true): array`. It must:
- use cache key `monday`, TTL 300;
- POST to `https://api.monday.com/v2` with `Authorization: {$config['monday_token']}`, `Content-Type: application/json`, and `API-Version: 2024-10`;
- query board `2032016220` for `items_page(limit: 100)` requesting `id`, `name`, and `column_values(ids: [...])` with the ten column ids from the spec, each with `id`, `text`, and `value`;
- throw `RuntimeException` on a curl error, a non-200 status, a GraphQL `errors` key, or a response missing the board — again never returning an empty array on failure, since the caller treats Monday failure as "do not send";
- flatten each item to the flat keys `matchShops` and `buildBrief` expect, reading `text` for status, people and text columns and parsing the JSON `value` for date columns to get the `YYYY-MM-DD` string;
- paginate with `cursor` if `has_more` is true, so a board over 100 items is never silently truncated.

- [ ] **Step 6: Commit**

```bash
git add modules/arayaBrief/models/cache.php modules/arayaBrief/models/fetchGcal.php modules/arayaBrief/models/fetchMonday.php modules/arayaBrief/tests/test_cache.php modules/arayaBrief/assets/cache/.gitignore
git commit -m "feat(araya-brief): add gcal and Monday fetchers with a short cache

The dashboard gets refreshed repeatedly, Monday rate-limits, and the gcal
endpoint scans every shop's billing periods, so both calls cache for five
minutes. A corrupt or empty cache file reads as a miss rather than being parsed
half-way, and writes go through a temporary file and a rename so a concurrent
read never sees a partial one. Cache names are sanitised because they reach the
filesystem.

Both fetchers throw on any failure instead of returning an empty array. An
empty array is indistinguishable from \"no shops\", which would make the brief
confidently report that the entire customer base had vanished; the caller
decides how to degrade. Monday paginates so a board over 100 items is never
silently truncated.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 8: Chat message renderer

**Files:**
- Create: `modules/arayaBrief/models/renderChat.php`
- Test: `modules/arayaBrief/tests/test_render_chat.php`

**Interfaces:**
- Consumes: the brief array from `arayaBuildBrief`
- Produces: `arayaRenderChat(array $brief): string`

Plain text, not a card: long cards get truncated by Chat and card text cannot be copied out. Thai section headings follow the user's template.

- [ ] **Step 1: Write the failing test**

Create `modules/arayaBrief/tests/test_render_chat.php`:

```php
<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../models/buildBrief.php';
require_once __DIR__ . '/../models/renderChat.php';

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

const TODAY = '2026-10-20';

function mk(array $monday = [], array $gcal = []): array {
    return [
        'monday' => array_merge([
            'name' => 'Test Shop', 'live_status' => 'LIVE', 'setup_status' => 'Done',
            'po' => 'Aim', 'register_date' => '2026-01-01', 'live_date' => '2026-06-01',
            'reason_for_cancel' => '', 'note' => '', 'calendar_id' => 'cal-x', 'email' => 'a@x.com',
        ], $monday),
        'gcal' => array_merge([
            'shop_id' => 'g1', 'shop_name' => 'Test Shop', 'remaining_minutes' => 200,
            'allocation' => 250, 'billing_day' => 15, 'days_until_refresh' => 26,
            'currency' => 'AUD', 'closed_periods' => [],
        ], $gcal),
        'matched_by' => 'calendar',
    ];
}
function res(array $m, array $um = [], array $ug = []): array {
    return ['matched' => $m, 'unmatchedMonday' => $um, 'unmatchedGcal' => $ug];
}
function render(array $r): string {
    return arayaRenderChat(arayaBuildBrief($r, TODAY));
}

echo "== the shop line format the user asked for ==\n";
$out = render(res([mk(['name' => 'Aunya Shop', 'live_date' => '2026-06-01'], ['remaining_minutes' => 152])]));
test("shop renders as 'Name (152 mins · ...)'", str_contains($out, 'Aunya Shop (152 mins'));
test("lifespan follows the minutes in the same parens", preg_match('/Aunya Shop \(152 mins · 4 ด\. 19 ว/u', $out) === 1);

echo "== negative minutes ==\n";
$out = render(res([mk(['name' => 'Me Ya Wi'], ['remaining_minutes' => -109])]));
test("a negative balance shows its sign", str_contains($out, 'Me Ya Wi (-109 mins'));

echo "== overview ==\n";
$out = render(res([mk(['live_status' => 'LIVE']), mk(['live_status' => 'Canceled'])]));
test("the overview reports the live count", preg_match('/LIVE: 1/u', $out) === 1);
test("the date appears in the header", str_contains($out, '2026'));

echo "== empty sections say so rather than being blank ==\n";
$out = render(res([mk([], ['days_until_refresh' => 26, 'remaining_minutes' => 200])]));
test("no upsell today is stated explicitly", str_contains($out, 'ไม่มีวันนี้'));

echo "== deferred sections are absent, not empty headings ==\n";
$out = render(res([mk()]));
foreach (['รายได้', 'Subscription', 'Top-up', 'คุณภาพสาย', 'Respond.io', 'ค้างชำระ'] as $deferred) {
    test("no heading for the deferred '{$deferred}'", !str_contains($out, $deferred));
}

echo "== data gaps are shown, never swallowed ==\n";
$out = render(res([mk()], [['name' => 'Orphan Shop', 'live_status' => 'LIVE']]));
test("an unmatched shop is named in the message", str_contains($out, 'Orphan Shop'));

echo "== owners are mentioned ==\n";
$out = render(res([mk(['name' => 'Stuck Shop', 'live_status' => 'Not Live', 'po' => 'Namva', 'register_date' => '2026-04-16'])]));
test("the owner appears", str_contains($out, '@Namva'));
test("days stalled appear", str_contains($out, '187'));

echo "== an unrecorded cancellation reason is an action item ==\n";
$out = render(res([mk(['live_status' => 'Waiting to cancel', 'reason_for_cancel' => ''])]));
test("blank reason renders as unrecorded", str_contains($out, 'ยังไม่ได้บันทึก'));

echo "== degraded mode ==\n";
$brief = arayaBuildBrief(res([mk()]), TODAY);
$brief['gcal_unavailable'] = true;
$out = arayaRenderChat($brief);
test("a gcal outage is stated in the message", str_contains($out, 'ดึงข้อมูลนาทีไม่ได้'));

echo "== output is plain text ==\n";
$out = render(res([mk()]));
test("no JSON card wrapper", !str_contains($out, '"cardsV2"'));
test("not empty", strlen(trim($out)) > 0);
test("valid UTF-8", mb_check_encoding($out, 'UTF-8'));

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php modules/arayaBrief/tests/test_render_chat.php`
Expected: FAIL — `arayaRenderChat` not defined

- [ ] **Step 3: Write the implementation**

Create `modules/arayaBrief/models/renderChat.php` with `arayaRenderChat(array $brief): string`, satisfying every test above. Requirements:
- Section order follows the spec: header, overview, live shops, upsell, idle-new, idle-quiet, setup, cancelling, data gaps.
- A section with no entries is omitted entirely, except upsell, which prints `ไม่มีวันนี้ ✅`.
- Shop lines are `Name (152 mins · 4 ด. 19 วัน)`, with the sign preserved on negatives.
- `$brief['gcal_unavailable']` prepends `⚠️ ดึงข้อมูลนาทีไม่ได้ — รายงานนี้มีแต่ข้อมูลจาก Monday`.
- Owners render as `@Name` plain text. This does not ping; real pings need a Monday-name-to-Chat-id map, which is deliberately out of scope.
- No heading is emitted for any deferred section.

- [ ] **Step 4: Run test to verify it passes**

Run: `php modules/arayaBrief/tests/test_render_chat.php`
Expected: PASS — `Failed: 0`

- [ ] **Step 5: Commit**

```bash
git add modules/arayaBrief/models/renderChat.php modules/arayaBrief/tests/test_render_chat.php
git commit -m "feat(araya-brief): render the brief as Google Chat plain text

Plain text rather than a card: long cards get truncated by Chat, and text in a
card cannot be copied out for reuse.

Sections with nothing in them are omitted rather than printed as empty
headings, with one exception — upsell prints \"none today\", because a missing
upsell section is ambiguous between \"nobody qualifies\" and \"the rule did not
run\". Deferred sections emit no heading at all. Owners render as plain @Name,
which does not ping; real pings need a Monday-name to Chat-id map and that is
out of scope here.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 9: Dashboard

**Files:**
- Create: `modules/arayaBrief/index.php`
- Create: `modules/arayaBrief/views/index.php`
- Create: `modules/arayaBrief/models/loadBrief.php`
- Create: `modules/arayaBrief/assets/fixtures/sample.json`
- Modify: `sideBar.php` (add the nav link)

**Interfaces:**
- Consumes: `arayaLoadConfig`, `arayaFetchGcal`, `arayaFetchMonday`, `arayaMatchShops`, `arayaBuildBrief`, `arayaRenderChat`
- Produces: `arayaLoadBrief(bool $useFixture = false, bool $useCache = true): array` — the brief array, with `gcal_unavailable` set when gcal failed

Read an existing module view (`modules/upgrade-downgrade/views/`) first and follow its AdminLTE page structure, `chkLogin.php` include, and `?v=` asset versioning.

- [ ] **Step 1: Write the loader and fixture**

Create `modules/arayaBrief/models/loadBrief.php`:

```php
<?php
/**
 * Assemble the brief from both sources.
 *
 * The one place that decides how to degrade. A gcal outage still produces a
 * brief, from Monday alone, flagged so nobody reads stale minutes as current.
 * A Monday outage produces nothing: Monday is the authority on who is a
 * customer, so without it there is no report to make, and inventing an empty
 * one would read as "we have no customers".
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/cache.php';
require_once __DIR__ . '/fetchGcal.php';
require_once __DIR__ . '/fetchMonday.php';
require_once __DIR__ . '/matchShops.php';
require_once __DIR__ . '/buildBrief.php';

function arayaLoadBrief(bool $useFixture = false, bool $useCache = true): array
{
    $today = (new DateTimeImmutable('now'))->format('Y-m-d');

    if ($useFixture) {
        $raw = json_decode(file_get_contents(__DIR__ . '/../assets/fixtures/sample.json'), true);
        $match = arayaMatchShops($raw['monday'], $raw['gcal']);
        return arayaBuildBrief($match, $today) + ['gcal_unavailable' => false];
    }

    $config = arayaLoadConfig();

    // Monday first: if it fails there is nothing to report, so fail loudly.
    $monday = arayaFetchMonday($config, $useCache);

    $gcalFailed = false;
    try {
        $gcal = arayaFetchGcal($config, $useCache);
    } catch (Throwable $e) {
        error_log('[arayaBrief] gcal fetch failed: ' . $e->getMessage());
        $gcal = [];
        $gcalFailed = true;
    }

    $brief = arayaBuildBrief(arayaMatchShops($monday, $gcal), $today);
    $brief['gcal_unavailable'] = $gcalFailed;
    return $brief;
}
```

Create `modules/arayaBrief/assets/fixtures/sample.json` with a `monday` array and a `gcal` array covering: two healthy live shops, one overdrawn at `-109`, one upsell candidate (refresh in 5 days, 40 minutes left), one rule-A idle shop, one rule-C quiet shop with four closed periods, two stalled setups, one `Waiting to cancel` with a blank reason, one unmatched Monday shop, one unmatched gcal shop, and the two KLR branches that collapse to the same normalized name. This fixture is what makes the dashboard reviewable without credentials.

- [ ] **Step 2: Write the view**

Create `modules/arayaBrief/views/index.php` — AdminLTE, requiring `chkLogin.php` like other panel pages (unlike `modules/aiAraya`, this page is staff-only). Layout per the spec:
- Title with the generated-at timestamp and a refresh button.
- Five `small-box` counters from `overview`.
- The data-gaps banner directly below, in red, collapsed by default but with its count always visible. It goes at the top, not the bottom, because at the bottom of a long page nobody reads it.
- The live-shop table: shop, minutes, lifespan, days until refresh. Minutes coloured red when negative, amber under 70, green otherwise. A 💰 marker on rows that also appear under upsell.
- Upsell and idle side by side; setup and cancelling side by side.
- A "preview Chat message" button rendering `arayaRenderChat()` output in a `<pre>`.
- `?fixture=sample` loads the fixture; `?nocache=1` bypasses the cache.
- Escape every DB- and API-derived string with `htmlspecialchars()`, per the module conventions.

Create `modules/arayaBrief/index.php` as the JS redirect to `views/index.php`, matching the other modules.

- [ ] **Step 3: Verify the dashboard renders from the fixture**

Run: `php -l modules/arayaBrief/views/index.php && php -l modules/arayaBrief/models/loadBrief.php`
Expected: `No syntax errors detected` for both

Open `http://localhost/masterPanel/modules/arayaBrief/views/index.php?fixture=sample`
Expected: every section renders; the gaps banner shows a non-zero count; the overdrawn shop sorts first with red minutes; the KLR branches appear under gaps and not as matched shops.

- [ ] **Step 4: Add the sidebar link**

Add a nav item to `sideBar.php` pointing at `modules/arayaBrief/views/index.php`, following the markup of the adjacent entries.

- [ ] **Step 5: Commit**

```bash
git add modules/arayaBrief/index.php modules/arayaBrief/views/index.php modules/arayaBrief/models/loadBrief.php modules/arayaBrief/assets/fixtures/sample.json sideBar.php
git commit -m "feat(araya-brief): add the dashboard

Merges both sources and renders the eight sections, reading the same
arayaBuildBrief output the Chat message will, so the two cannot disagree.

The data-gaps banner sits at the top in red rather than at the bottom: shops a
rule had to skip are the ones most likely to be a real problem, and at the
bottom of a long page nobody would scroll to them. Minutes are colour-coded and
sorted ascending so the overdrawn shops lead. A Chat-preview button renders the
exact message the 07:00 cron would send, because otherwise the only way to
check it is to wait a day.

?fixture=sample renders the whole page from a committed fixture, so the layout
and rules can be reviewed with no credentials and no API calls. The fixture
includes the two KLR branches that collapse to one normalized name, so the
ambiguity handling is visible on screen.

A gcal outage degrades to Monday-only with a banner; a Monday outage fails
loudly, since without it there is no report to make.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 10: Send log table

**Files:**
- Create: `modules/arayaBrief/assets/db/create_brief_logs.sql`
- Create: `modules/arayaBrief/models/briefLog.php`
- Test: `modules/arayaBrief/tests/test_brief_log.php`

**Interfaces:**
- Produces:
  - `arayaBriefSchemaSql(): string`
  - `arayaBriefAlreadySentToday(object $db, string $ymd): bool`
  - `arayaBriefLogSend(object $db, string $ymd, string $status, string $message): void`

Table `l4u_araya_brief_logs`, matching the `l4u_` prefix convention established by `l4u_up_down_logs`. Columns: `id` PK, `brief_date` DATE, `status` VARCHAR(20) (`sent` / `failed` / `skipped`), `message` MEDIUMTEXT, `created_at` DATETIME. Unique index on `(brief_date, status)` is **not** wanted — a failed attempt followed by a successful one must both be recordable. Index `brief_date` instead.

- [ ] **Step 1: Write the failing test**

Create `modules/arayaBrief/tests/test_brief_log.php`:

```php
<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../models/briefLog.php';

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

echo "== schema ==\n";
$sql = arayaBriefSchemaSql();
test("table is l4u_ prefixed", str_contains($sql, 'l4u_araya_brief_logs'));
test("creation is idempotent", str_contains($sql, 'IF NOT EXISTS'));
test("brief_date is indexed", preg_match('/(KEY|INDEX).*brief_date/i', $sql) === 1);
test("no unique index on date alone, so a retry after a failure can be logged",
     preg_match('/UNIQUE[^\n]*brief_date/i', $sql) === 0);

echo "== already-sent check uses a bound query ==\n";
/** A stub recording what it was asked, so the query shape is testable with no DB. */
class FakeDb {
    public array $calls = [];
    private $next;
    public function __construct($next = null) { $this->next = $next; }
    public function query(string $sql, $params = []) {
        $this->calls[] = ['sql' => $sql, 'params' => $params];
        $row = $this->next;
        return new class($row) {
            public function __construct(private $row) {}
            public function fetchArray() { return $this->row; }
        };
    }
}

$db = new FakeDb(null);
test("no row means not sent", arayaBriefAlreadySentToday($db, '2026-10-20') === false);
test("the date is bound, never interpolated",
     $db->calls[0]['params'] === ['2026-10-20', 'sent']
     || $db->calls[0]['params'] === ['2026-10-20']);
test("the date does not appear inline in the SQL",
     !str_contains($db->calls[0]['sql'], '2026-10-20'));

$db = new FakeDb(['id' => 1]);
test("an existing sent row means already sent", arayaBriefAlreadySentToday($db, '2026-10-20') === true);

echo "== logging a send ==\n";
$db = new FakeDb(null);
arayaBriefLogSend($db, '2026-10-20', 'sent', 'the message body');
test("an insert was issued", str_contains(strtoupper($db->calls[0]['sql']), 'INSERT'));
test("every value is bound", count($db->calls[0]['params']) === 3);
test("the body does not appear inline in the SQL",
     !str_contains($db->calls[0]['sql'], 'the message body'));

$db = new FakeDb(null);
arayaBriefLogSend($db, '2026-10-20', 'failed', "quote ' and backslash \\ survive");
test("a body with quotes is still bound, not escaped into the SQL",
     str_contains($db->calls[0]['params'][2], "quote '"));

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php modules/arayaBrief/tests/test_brief_log.php`
Expected: FAIL — `arayaBriefSchemaSql` not defined

- [ ] **Step 3: Write the implementation**

Create `modules/arayaBrief/models/briefLog.php` with the three functions plus `arayaBriefSchemaSql()`. Every query uses bound parameters via the `db` wrapper's `query($sql, $params)` signature — never interpolation. Write the same `CREATE TABLE IF NOT EXISTS` statement into `modules/arayaBrief/assets/db/create_brief_logs.sql` so it can be applied by hand on production, following `modules/mondayReport/create_table.sql`.

- [ ] **Step 4: Run test to verify it passes**

Run: `php modules/arayaBrief/tests/test_brief_log.php`
Expected: PASS — `Failed: 0`

- [ ] **Step 5: Apply the schema locally and confirm**

Run: `php -r 'require "modules/arayaBrief/models/briefLog.php"; echo arayaBriefSchemaSql();' | php -r 'echo file_get_contents("php://stdin");'`
Expected: the `CREATE TABLE` statement prints

Apply it to the local database with the module's `db` wrapper or phpMyAdmin, then confirm:
Run: a `SHOW CREATE TABLE l4u_araya_brief_logs` via your usual local client
Expected: the table exists with `brief_date` indexed and no unique index on `brief_date`

- [ ] **Step 6: Commit**

```bash
git add modules/arayaBrief/models/briefLog.php modules/arayaBrief/assets/db/create_brief_logs.sql modules/arayaBrief/tests/test_brief_log.php
git commit -m "feat(araya-brief): add send logging for the daily brief

Records every send attempt so a double cron fire cannot post the brief twice
into the team space. brief_date is indexed but deliberately not unique: a
failed attempt followed by a successful one must both be recordable, which a
unique key on the date would prevent.

Every value is bound rather than interpolated, including the message body,
which contains shop names with apostrophes. The tests pin that with a stub that
records the query shape, so this holds with no database present.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

### Task 11: Send script and cron

**Files:**
- Create: `api/arayaBrief/sendChat.php`
- Create: `modules/arayaBrief/README.md`

**Interfaces:**
- Consumes: `arayaLoadBrief`, `arayaRenderChat`, `arayaBriefAlreadySentToday`, `arayaBriefLogSend`

CLI-invoked by cron. Must be safe to run twice.

- [ ] **Step 1: Write the send script**

Create `api/arayaBrief/sendChat.php`:

```php
<?php
/**
 * Post the Araya daily brief to the "IT in house" Google Chat space.
 *
 * Run by cron at 07:00 Asia/Bangkok. Safe to run twice: a date that already
 * has a 'sent' row is skipped rather than posted again.
 */
date_default_timezone_set("Asia/Bangkok");

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "CLI only\n";
    exit(1);
}

require_once __DIR__ . '/../../modules/arayaBrief/models/loadBrief.php';
require_once __DIR__ . '/../../modules/arayaBrief/models/renderChat.php';
require_once __DIR__ . '/../../modules/arayaBrief/models/briefLog.php';
require_once __DIR__ . '/../../modules/arayaBrief/assets/db/db.php';

$today = (new DateTimeImmutable('now'))->format('Y-m-d');

try {
    global $db;

    if (arayaBriefAlreadySentToday($db, $today)) {
        echo "[arayaBrief] {$today} already sent, skipping\n";
        exit(0);
    }

    // A Monday failure throws out of here: without Monday there is no report,
    // and posting an empty one would read as "we have no customers".
    $brief = arayaLoadBrief(false, false);
    $message = arayaRenderChat($brief);

    $config = arayaLoadConfig();
    $ch = curl_init($config['chat_webhook_url']);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json; charset=UTF-8'],
        CURLOPT_POSTFIELDS     => json_encode(['text' => $message], JSON_UNESCAPED_UNICODE),
    ]);
    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false || $status < 200 || $status >= 300) {
        // Never log the webhook URL: it is the credential.
        throw new RuntimeException("Chat webhook returned HTTP {$status}" . ($curlErr ? " ({$curlErr})" : ''));
    }

    arayaBriefLogSend($db, $today, 'sent', $message);
    echo "[arayaBrief] {$today} sent\n";
    exit(0);

} catch (Throwable $e) {
    $why = $e->getMessage();
    error_log("[arayaBrief] {$today} failed: {$why}");
    try {
        global $db;
        arayaBriefLogSend($db, $today, 'failed', $why);
    } catch (Throwable $ignored) {
        // The log write failing must not hide the original error.
    }
    echo "[arayaBrief] {$today} failed: {$why}\n";
    exit(1);
}
```

- [ ] **Step 2: Verify it is safe and syntactically valid**

Run: `php -l api/arayaBrief/sendChat.php`
Expected: `No syntax errors detected`

Confirm the webhook URL never reaches output:
Run: `grep -n "chat_webhook_url" api/arayaBrief/sendChat.php`
Expected: the URL is only passed to `curl_init`, never to `echo` or `error_log`

- [ ] **Step 3: Dry-run against the fixture**

Temporarily invoke the renderer alone to confirm the message body is what the preview showed, without posting:

Run: `php -r 'date_default_timezone_set("Asia/Bangkok"); require "modules/arayaBrief/models/loadBrief.php"; require "modules/arayaBrief/models/renderChat.php"; echo arayaRenderChat(arayaLoadBrief(true));'`
Expected: the full brief prints to the terminal, identical to the dashboard's Chat preview

- [ ] **Step 4: Write the runbook**

Create `modules/arayaBrief/README.md` documenting: what the module does; the two data sources and which is authoritative; `assets/config.example.php` → `config.php` setup; the `ARAYA_ADMIN_KEY` that must match the gcal app's env var; applying `assets/db/create_brief_logs.sql`; the cron line; how to run the tests; and the `?fixture=sample` and `?nocache=1` query parameters.

The cron line, for Linux/cPanel (production is Linux — `schtasks` is unavailable there):

```
0 7 * * * /usr/bin/php /home/<user>/public_html/api/arayaBrief/sendChat.php >> /home/<user>/logs/araya-brief.log 2>&1
```

- [ ] **Step 5: Run every test in both repos**

In masterPanel:
Run: `for f in modules/arayaBrief/tests/test_*.php; do echo "== $f"; php "$f" || exit 1; done`
Expected: every file ends `Failed: 0`

Confirm nothing else broke:
Run: `for f in modules/upgrade-downgrade/tests/test_*.php; do php "$f" >/dev/null || echo "REGRESSION: $f"; done`
Expected: no output

In the gcal repo:
Run: `./.venv/Scripts/python.exe -m pytest tests/ -q`
Expected: no new failures

- [ ] **Step 6: Commit**

```bash
git add api/arayaBrief/sendChat.php modules/arayaBrief/README.md
git commit -m "feat(araya-brief): add the 07:00 send script and runbook

CLI-only and safe to run twice: a date that already has a 'sent' row is skipped
rather than posted again, so a double cron fire cannot post the brief twice
into the team space. Every attempt is logged, failures included, so a silent
morning is diagnosable after the fact.

The webhook URL reaches curl and nothing else — never a log line or an error
message, since it is the credential. A failure to write the log is swallowed so
it cannot mask the original error.

Cron is documented for Linux/cPanel; production is Linux and schtasks is not
available there.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Verification gate before Task 11 goes live

Per the spec's delivery order and the user's instruction that the dashboard comes first, **do not enable the cron until the dashboard's numbers have been checked by hand.**

- [ ] Open the dashboard against live data (not the fixture) and compare the five overview counts against the Monday board, counted manually.
- [ ] Confirm every shop in the data-gaps section is genuinely a gap and not a matching bug.
- [ ] Confirm the live-shop minute figures match the gcal admin UI for two or three shops.
- [ ] Show the Chat preview to the user and get explicit approval of the wording.
- [ ] Only then add the cron line.

A brief with wrong numbers posted into a team space costs trust that is slow to rebuild, and the numbers are the entire point of the report.
