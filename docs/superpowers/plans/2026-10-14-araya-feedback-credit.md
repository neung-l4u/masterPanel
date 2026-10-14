# Araya Feedback Credit Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Grant a shop 30 free minutes, once per shop for the lifetime of the account, when its owner completes the Araya cancellation feedback form.

**Architecture:** A single `POST /api/feedback-credit` endpoint in the gcal API owns the credit decision: it authenticates a shared token, writes one `TopUp` row whose `payment_reference` a partial unique index constrains to one per shop, raises `ShopMinutes.total_topped_up` by 30, and emails the customer the arithmetic. The masterPanel feedback form gains an Araya mode driven by query parameters and calls that endpoint **server-side**, so the token never reaches a browser. Both the negative-balance deduction and the "does not move the billing date" guarantee fall out of the existing data model rather than from new logic.

**Tech Stack:** gcal — Python 3, FastAPI, SQLModel/SQLAlchemy, pytest with `fastapi.testclient.TestClient`, PostgreSQL in production and SQLite in tests. masterPanel — PHP 8 (no framework, `mysqli` wrapper), jQuery 3.7.1, Bootstrap 5.3.3, no build step.

## Global Constraints

- **Two repositories.** gcal is `D:\Projects\2.LocalEats\github\gcal-booking-api-main`. masterPanel is the worktree `C:\xampp\htdocs\masterPanel-ud-lookup` on branch `mark/feature/ud-email-lookup-wt`. Each task names which one it touches. Commit in the repo the task touches; never stage across both.
- **The credit amount is 30 and it is a server-side constant.** It is never read from the request body. A caller holding the token must not be able to choose the amount.
- **Once per shop is enforced by the database**, with a partial unique index on `top_up(payment_reference) WHERE payment_reference LIKE 'feedback-credit:%'`. No application-level lock.
- **The `payment_reference` value is exactly** `feedback-credit:<shop_id>` — no other prefix, no timestamp, no suffix. The index and the lookup both depend on this string.
- **The TopUp row records:** `note` = `Feedback credit`, `status` = `manual`, `amount_cents` = `0`, `minutes_added` = `30`, `package` = `custom`.
- **`status` must be `manual`, never `paid`.** The revenue report reads this table, and `paid` means money arrived; a zero-value `paid` row would appear as a sale of nothing.
- **Never touch `cancel_effective_on`, `billing_day`, or any billing field.** These are goodwill minutes; the service-stop date does not move. Task 3 asserts this.
- **"Already claimed" is HTTP 200 with `granted: false`**, not 409. The customer did nothing wrong.
- **Token auth follows the existing `MONDAY_WEBHOOK_SECRET` pattern** at `app/main.py:1408-1410`: read from env, compare with `secrets.compare_digest`, return 401 on mismatch, and an empty configured secret rejects every request.
- **The feedback credit is a reward, never a gate.** No failure path may stop the feedback itself from being recorded.
- **Without a `shop_id`, the feedback form behaves exactly as it does today** (lead mode): no credit attempt, no error.
- End every git commit message with:
  `Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>`

---

## File Structure

gcal (`D:\Projects\2.LocalEats\github\gcal-booking-api-main`):

| Path | Responsibility |
| --- | --- |
| `app/db.py` | Gains the partial unique index in two migration lists (PG and the MySQL/SQLite-flavoured one). |
| `app/main.py` | Gains `POST /api/feedback-credit`, its token check, and the customer email. |
| `tests/test_feedback_credit.py` | New. Owns every assertion about the credit: grant, idempotency, negative balance, billing untouched, auth, email. |

masterPanel (`C:\xampp\htdocs\masterPanel-ud-lookup`):

| Path | Responsibility |
| --- | --- |
| `modules/feedback/assets/config.example.php` | New. The gcal base URL and the shared token, so `config.php` (gitignored) has something to copy. |
| `modules/feedback/views/feedbackForm.php` | Two modes. Reads `mode`, `shop_id`, `shopName`, `email` from the query string. |
| `modules/feedback/controllers/feedbackForm.js` | Submits the new fields; routes to thankyou with the credit outcome. |
| `modules/feedback/models/feedbackForm.php` | Writes the feedback row, then calls gcal server-side and records the outcome. |
| `modules/feedback/views/thankyou.php` | States what happened to the credit. |
| `modules/upgrade-downgrade/controllers/downgradeForm.js` | Adds `shop_id` to the redirect it already builds. |

**Task order and why:** gcal first (Tasks 1–3), because the masterPanel side cannot be tested against anything until the endpoint exists. Within gcal: the index before the endpoint, because the endpoint's idempotency depends on it. masterPanel afterwards (Tasks 4–7), ending with the one-line change to the downgrade form that turns the flow on.

---

## Task 1: The partial unique index

The index is what makes "once per shop" true. It lands first and on its own, because every later task's idempotency rests on it and because a reviewer can reject it independently.

**Repository:** gcal — `D:\Projects\2.LocalEats\github\gcal-booking-api-main`

**Files:**
- Modify: `app/db.py` — append to `BALANCE_HISTORY_PG` and `BALANCE_HISTORY_MYSQL`
- Test: `tests/test_feedback_credit.py` (created here, extended in Task 3)

**Interfaces:**
- Consumes: nothing.
- Produces: a unique index named `ux_top_up_feedback_credit` on `top_up(payment_reference)`, constrained to rows whose `payment_reference` starts with `feedback-credit:`. Task 2's `IntegrityError` branch depends on it existing.

- [ ] **Step 1: Read the two lists you are appending to**

Run:

```bash
grep -n "BALANCE_HISTORY_PG\|BALANCE_HISTORY_MYSQL" app/db.py
```

Expected: each name appears twice — once where the list is defined (around lines 620–640) and once where `init_db()` iterates it (the PG one around line 295, the MySQL/SQLite one around line 316). Read both definitions in full before editing. If the names do not exist, STOP and report: the migration structure has changed and this plan's assumption is wrong.

- [ ] **Step 2: Write the failing test**

Create `tests/test_feedback_credit.py`:

```python
"""The 30-minute goodwill credit for completing the Araya feedback form.

Granted once per shop for the lifetime of the account. The uniqueness is a
database constraint rather than an application check, so a double-click or two
concurrent submissions cannot grant twice; these tests exercise that directly
by inserting the row twice.

The two conditions that need no code are asserted here anyway, because they are
guarantees to the customer rather than implementation details: the credit
absorbs a negative balance first (which falls out of balance being computed
rather than stored), and it does not move the service-stop date.
"""
import os
import random
import sys

DB = os.path.join(os.path.dirname(os.path.abspath(__file__)), "test_feedback_credit.db")
if os.path.exists(DB):
    os.remove(DB)
os.environ["DATABASE_URL"] = f"sqlite:///{DB}"
os.environ.setdefault("SESSION_SECRET_KEY", "test-secret")
os.environ["FEEDBACK_CREDIT_TOKEN"] = "test-feedback-token"
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

import pytest
from datetime import datetime, timezone
from sqlalchemy.exc import IntegrityError
from sqlmodel import Session, select

import app.models  # noqa: F401
from app.db import init_db, engine
from app.models import User, ShopSettings, ShopMinutes, TopUp

# The engine is process-global and the first suite to import wins the DB, so
# ids here are unique per run to coexist with the other suites.
_base = random.randint(20000, 89999)

init_db()

from passlib.hash import bcrypt as _bcrypt


def _mk_shop(code: str, topped_up: int = 0) -> int:
    """A shop with a minutes row. Returns the ShopSettings primary key."""
    with Session(engine) as s:
        user = User(username=f"fc{code}@t.t", hashed_password=_bcrypt.hash("pw"))
        s.add(user)
        s.commit()
        s.refresh(user)
        shop = ShopSettings(shop_id=code, user_id=user.id)
        s.add(shop)
        s.commit()
        s.refresh(shop)
        s.add(ShopMinutes(shop_id=code, total_topped_up=topped_up))
        s.commit()
        return shop.id


def _insert_credit_row(code: str) -> None:
    """Write the credit row exactly as the endpoint will, in its own session."""
    with Session(engine) as s:
        s.add(TopUp(
            shop_id=code,
            purchased_at=datetime.now(timezone.utc),
            package="custom",
            minutes_added=30,
            amount_cents=0,
            currency="USD",
            payment_reference=f"feedback-credit:{code}",
            status="manual",
            note="Feedback credit",
        ))
        s.commit()


def test_index_admits_one_credit_row_per_shop():
    code = str(_base + 1)
    _mk_shop(code)

    _insert_credit_row(code)

    # The second insert must be refused by the database, not by application
    # code. This is what makes a double-click safe without a lock.
    with pytest.raises(IntegrityError):
        _insert_credit_row(code)

    with Session(engine) as s:
        rows = s.exec(
            select(TopUp).where(TopUp.payment_reference == f"feedback-credit:{code}")
        ).all()
    assert len(rows) == 1


def test_index_does_not_constrain_other_shops():
    a, b = str(_base + 2), str(_base + 3)
    _mk_shop(a)
    _mk_shop(b)

    _insert_credit_row(a)
    _insert_credit_row(b)

    with Session(engine) as s:
        rows = s.exec(
            select(TopUp).where(TopUp.payment_reference.like("feedback-credit:%"))
        ).all()
    assert {r.shop_id for r in rows} >= {a, b}


def test_index_leaves_ordinary_topups_alone():
    code = str(_base + 4)
    _mk_shop(code)

    # Two Stripe-style rows sharing a NULL reference, and two sharing the same
    # non-feedback reference. The partial index must not touch either: it
    # constrains only the feedback-credit keyspace.
    with Session(engine) as s:
        for _ in range(2):
            s.add(TopUp(
                shop_id=code,
                purchased_at=datetime.now(timezone.utc),
                package="100",
                minutes_added=100,
                amount_cents=5000,
                currency="USD",
                payment_reference=None,
                status="paid",
            ))
        s.commit()

    with Session(engine) as s:
        rows = s.exec(select(TopUp).where(TopUp.shop_id == code)).all()
    assert len(rows) == 2
```

- [ ] **Step 3: Run the test to verify it fails**

Run: `python -m pytest tests/test_feedback_credit.py -v`

Expected: `test_index_admits_one_credit_row_per_shop` FAILS with `DID NOT RAISE <class 'sqlalchemy.exc.IntegrityError'>`, because the index does not exist yet so the second insert succeeds. The other two tests pass already.

- [ ] **Step 4: Add the index to both migration lists**

In `app/db.py`, append one entry to `BALANCE_HISTORY_PG`:

```python
    # One feedback credit per shop, for the lifetime of the account. A partial
    # index so it constrains only this keyspace and leaves every Stripe and
    # hand-entered payment_reference alone -- including the many NULLs, which a
    # plain unique index would not tolerate.
    #
    # Enforced here rather than by a SELECT-then-INSERT in the endpoint,
    # because two concurrent submissions would both pass that check. Here one
    # insert wins and the other raises, which the endpoint reads back as
    # "already claimed".
    "CREATE UNIQUE INDEX IF NOT EXISTS ux_top_up_feedback_credit ON top_up (payment_reference) WHERE payment_reference LIKE 'feedback-credit:%'",
```

Then append the identical DDL to `BALANCE_HISTORY_MYSQL`:

```python
    # SQLite supports partial indexes with the same syntax as Postgres, so the
    # tests exercise the real constraint. MySQL does not: it will raise here
    # and the surrounding try/except in init_db() swallows it, leaving MySQL
    # deployments without the guard. That is acceptable because production is
    # Postgres -- but a MySQL deployment would need an application-level check,
    # so do not quietly rely on this index where the dialect is MySQL.
    "CREATE UNIQUE INDEX IF NOT EXISTS ux_top_up_feedback_credit ON top_up (payment_reference) WHERE payment_reference LIKE 'feedback-credit:%'",
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `python -m pytest tests/test_feedback_credit.py -v`

Expected: PASS, 3 passed.

If `test_index_admits_one_credit_row_per_shop` still fails, the index was not created. Check it exists:

```bash
python -c "
import os; os.environ['DATABASE_URL']='sqlite:///tests/test_feedback_credit.db'
from sqlmodel import Session, text
from app.db import engine
with Session(engine) as s:
    print([r[0] for r in s.exec(text(\"SELECT name FROM sqlite_master WHERE type='index' AND name LIKE 'ux_top_up%'\"))])
"
```

Expected: `['ux_top_up_feedback_credit']`

- [ ] **Step 6: Confirm the existing suites still pass**

The index is new DDL on a shared table, so the neighbouring suites are the regression check.

Run: `python -m pytest tests/test_revenue.py tests/test_monthly_allocation.py tests/test_negative_balance_alerts.py -q`

Expected: all pass, with no new errors mentioning `top_up` or `ux_top_up_feedback_credit`.

- [ ] **Step 7: Commit**

```bash
git add app/db.py tests/test_feedback_credit.py
git commit -m "feat(minutes): one feedback credit per shop, enforced by the database

The 30-minute goodwill credit for completing the Araya feedback form is granted
once per shop for the lifetime of the account, and the top-up log's
payment_reference column already nominates itself as an idempotency key.

A partial unique index on the feedback-credit keyspace is what makes that true.
It leaves every Stripe and hand-entered reference alone, including the many
NULLs a plain unique index would reject, and it means two concurrent
submissions cannot both be granted: one insert wins and the other raises, which
the endpoint will read back as already claimed. A SELECT-then-INSERT check in
the endpoint would let both through.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 2: The credit endpoint

**Repository:** gcal — `D:\Projects\2.LocalEats\github\gcal-booking-api-main`

**Files:**
- Modify: `app/main.py` — add the endpoint near the other `/api/` routes
- Test: `tests/test_feedback_credit.py` (extended in Task 3)

**Interfaces:**
- Consumes: the `ux_top_up_feedback_credit` index from Task 1; `send_email(...)` already in `app/main.py`; `ShopSettings`, `ShopMinutes`, `TopUp`, `Call` from `app/models.py`.
- Produces: `POST /api/feedback-credit`. Request header `X-Feedback-Token`. Request body `{"shop_id": str, "email": str (optional), "feedback_ref": str (optional)}`. Response bodies exactly as the table below. Task 5's PHP client consumes these.

| Outcome | HTTP | Body |
| --- | --- | --- |
| Granted | 200 | `{"granted": true, "minutes": 30, "previous_balance": int, "new_balance": int, "shop_name": str}` |
| Already claimed | 200 | `{"granted": false, "reason": "already_claimed", "claimed_at": "YYYY-MM-DD", "new_balance": int}` |
| Unknown shop | 200 | `{"granted": false, "reason": "shop_not_found"}` |
| Missing `shop_id` | 200 | `{"granted": false, "reason": "shop_not_found"}` |
| Bad/missing token | 401 | `{"ok": false, "error": "unauthorized"}` |

- [ ] **Step 1: Find the insertion point and confirm the helpers exist**

Run:

```bash
grep -n "^FEEDBACK_CREDIT_MINUTES\|^@app.get(\"/api/minutes\")\|^def send_email(\|^import secrets\|MONDAY_WEBHOOK_SECRET" app/main.py
```

Expected: `import secrets` near the top (around line 46), `MONDAY_WEBHOOK_SECRET` around line 1408, `@app.get("/api/minutes")` around line 3091, `def send_email(` around line 3856, and NO `FEEDBACK_CREDIT_MINUTES` (you are adding it). If `import secrets` or `send_email` is missing, STOP and report.

Insert the new endpoint immediately **before** the `@app.get("/api/minutes")` route, so the two minute-related API routes sit together.

- [ ] **Step 2: Write the endpoint**

Insert into `app/main.py`:

```python
# The goodwill credit for completing the Araya cancellation feedback form.
#
# A server-side constant on purpose: the amount is never read from the request
# body, so a caller holding the shared token cannot choose how many minutes to
# grant itself.
FEEDBACK_CREDIT_MINUTES = 30
FEEDBACK_CREDIT_PREFIX = "feedback-credit:"


def _feedback_credit_reference(shop_id: str) -> str:
    """The idempotency key the partial unique index constrains.

    One per shop, for the lifetime of the account. The exact string matters:
    ux_top_up_feedback_credit indexes `payment_reference LIKE 'feedback-credit:%'`.
    """
    return f"{FEEDBACK_CREDIT_PREFIX}{shop_id}"


def _shop_balance(sess: Session, shop_id: str, minutes: "ShopMinutes") -> int:
    """Minutes remaining, which may be negative.

    Balance is not stored. It is total_topped_up minus the minutes actually
    used, rounded up from call seconds the same way /api/minutes does it. This
    is why adding 30 to a shop sitting at -12 lands it at +18 without any
    deduction logic of our own.
    """
    calls = sess.exec(select(Call).where(Call.shop_id == shop_id)).all()
    used = int((sum((c.duration_sec or 0) for c in calls) + 59) // 60)
    return int(minutes.total_topped_up or 0) - used


@app.post("/api/feedback-credit")
async def api_feedback_credit(request: Request):
    """Grant 30 goodwill minutes for a completed Araya feedback form.

    Called server-side by the masterPanel feedback module, never by a browser:
    the shared token would otherwise be readable by anyone who opened the page.

    Authenticated with the same shape as the Monday webhook: a token from the
    environment compared with compare_digest, and an unset token rejects every
    request rather than waving them through.

    Returns 200 with granted=false for an already-claimed shop rather than a
    409, because the customer did nothing wrong -- they filled in the form and
    the system declined to credit a second time. The caller records the outcome
    either way.

    Deliberately does NOT touch cancel_effective_on, billing_day, or any
    billing field. These are goodwill minutes; the date the service stops does
    not move.
    """
    expected = os.getenv("FEEDBACK_CREDIT_TOKEN", "")
    supplied = request.headers.get("X-Feedback-Token", "")
    if not expected or not secrets.compare_digest(supplied, expected):
        return JSONResponse(status_code=401, content={"ok": False, "error": "unauthorized"})

    try:
        payload = await request.json()
    except Exception:
        payload = {}

    shop_id = str((payload or {}).get("shop_id") or "").strip()
    feedback_ref = str((payload or {}).get("feedback_ref") or "").strip()

    # An empty shop_id is reported as an unknown shop rather than a validation
    # error: the caller's next action is identical either way, and the customer
    # is told the same thing.
    if not shop_id:
        return JSONResponse(status_code=200, content={"granted": False, "reason": "shop_not_found"})

    with Session(engine) as sess:
        shop = sess.exec(select(ShopSettings).where(ShopSettings.shop_id == shop_id)).first()
        if not shop:
            return JSONResponse(status_code=200, content={"granted": False, "reason": "shop_not_found"})

        minutes = sess.exec(select(ShopMinutes).where(ShopMinutes.shop_id == shop_id)).first()
        if not minutes:
            # A live shop with no minutes row has never been allocated any.
            # Create it at zero so the credit has somewhere to land.
            minutes = ShopMinutes(shop_id=shop_id, total_topped_up=0)
            sess.add(minutes)
            sess.commit()
            sess.refresh(minutes)

        reference = _feedback_credit_reference(shop_id)
        previous_balance = _shop_balance(sess, shop_id, minutes)

        existing = sess.exec(
            select(TopUp).where(TopUp.payment_reference == reference)
        ).first()
        if existing:
            return JSONResponse(status_code=200, content={
                "granted": False,
                "reason": "already_claimed",
                "claimed_at": existing.purchased_at.date().isoformat() if existing.purchased_at else None,
                "new_balance": previous_balance,
            })

        # The read above is a courtesy, not the guard: two concurrent requests
        # would both pass it. The unique index is the guard, so the insert is
        # attempted and the loser is reported as already claimed.
        try:
            sess.add(TopUp(
                shop_id=shop_id,
                purchased_at=datetime.now(timezone.utc),
                package="custom",
                minutes_added=FEEDBACK_CREDIT_MINUTES,
                amount_cents=0,
                currency=(getattr(shop, "currency", None) or "USD"),
                payment_reference=reference,
                # 'manual', never 'paid': the revenue report reads this table and
                # 'paid' means money arrived. A zero-value 'paid' row would show
                # up there as a sale of nothing.
                status="manual",
                note="Feedback credit",
            ))
            minutes.total_topped_up = int(minutes.total_topped_up or 0) + FEEDBACK_CREDIT_MINUTES
            minutes.last_topup_at = datetime.now(timezone.utc)
            sess.add(minutes)
            sess.commit()
        except IntegrityError:
            sess.rollback()
            existing = sess.exec(
                select(TopUp).where(TopUp.payment_reference == reference)
            ).first()
            return JSONResponse(status_code=200, content={
                "granted": False,
                "reason": "already_claimed",
                "claimed_at": existing.purchased_at.date().isoformat() if (existing and existing.purchased_at) else None,
                "new_balance": _shop_balance(sess, shop_id, minutes) if minutes else previous_balance,
            })

        sess.refresh(minutes)
        new_balance = _shop_balance(sess, shop_id, minutes)
        shop_name = getattr(shop, "shop_name", None) or shop_id
        to_address = str((payload or {}).get("email") or "").strip() or getattr(shop, "email", None)

    # Outside the session: the credit is committed, and a mail failure must not
    # unwind it. send_email logs its own outcome to EmailLog.
    if to_address:
        try:
            _send_feedback_credit_email(
                to_address=to_address,
                shop=shop,
                shop_name=shop_name,
                previous_balance=previous_balance,
                new_balance=new_balance,
            )
        except Exception as exc:
            logging.error(
                "feedback credit email failed for shop %s (ref %s): %s",
                shop_id, feedback_ref or "-", exc,
            )

    return JSONResponse(status_code=200, content={
        "granted": True,
        "minutes": FEEDBACK_CREDIT_MINUTES,
        "previous_balance": previous_balance,
        "new_balance": new_balance,
        "shop_name": shop_name,
    })
```

- [ ] **Step 3: Write the email helper**

Insert immediately after the endpoint:

```python
def _send_feedback_credit_email(*, to_address: str, shop, shop_name: str,
                                previous_balance: int, new_balance: int) -> None:
    """Tell the customer the credit landed, and show the arithmetic.

    The previous balance is shown even when negative. A customer at -12 who is
    told "30 minutes added" and then sees 18 will believe they were
    short-changed; showing all three figures is what makes the deduction
    legible rather than suspicious.

    The goodwill sentence is here because it is the question this email
    otherwise invites: free minutes do not buy more time before the service
    stops.
    """
    subject = f"30 free minutes added to {shop_name}"
    body = (
        "Thank you for your feedback.\n\n"
        "We have added 30 free minutes to your account as a thank-you.\n\n"
        f"  Previous balance:  {previous_balance} minutes\n"
        f"  Feedback credit:   +{FEEDBACK_CREDIT_MINUTES} minutes\n"
        f"  New balance:       {new_balance} minutes\n\n"
        "These are goodwill minutes. They do not change your billing date or "
        "your cancellation date.\n"
    )
    send_email(
        subject,
        body,
        [to_address],
        settings=shop,
        shop_id=getattr(shop, "shop_id", None),
    )
```

- [ ] **Step 4: Confirm the imports the new code needs**

Run:

```bash
grep -n "^import logging\|^import os\|^import secrets\|^from datetime import\|IntegrityError\|^from fastapi.responses import\|^from sqlmodel import" app/main.py | head
```

Every one of `logging`, `os`, `secrets`, `datetime`/`timezone`, `JSONResponse`, `Session`/`select` must already be imported. `IntegrityError` probably is **not**. If it is missing, add it next to the other SQLAlchemy imports:

```python
from sqlalchemy.exc import IntegrityError
```

Report which imports you had to add.

- [ ] **Step 5: Verify the module still imports and the route is registered**

Run:

```bash
python -c "
import os
os.environ.setdefault('DATABASE_URL','sqlite:///./dev.db')
os.environ.setdefault('SESSION_SECRET_KEY','x')
import app.main as m
routes = [r.path for r in m.app.routes if getattr(r,'path','').startswith('/api/feedback')]
print('routes:', routes)
print('constant:', m.FEEDBACK_CREDIT_MINUTES)
print('reference:', m._feedback_credit_reference('12345'))
"
```

Expected exactly:

```
routes: ['/api/feedback-credit']
constant: 30
reference: feedback-credit:12345
```

- [ ] **Step 6: Commit**

```bash
git add app/main.py
git commit -m "feat(minutes): grant 30 goodwill minutes for Araya feedback

One endpoint owns the credit decision, so there is one place where once-per-shop
is enforced and one place to audit. Called server-side by the feedback module,
never by a browser, because the shared token would otherwise be readable by
anyone who opened the page.

The amount is a server-side constant and is not read from the request body: a
caller holding the token must not be able to choose how many minutes to grant
itself. The row is written as status 'manual' rather than 'paid', because the
revenue report reads this table and a zero-value 'paid' row would appear there
as a sale of nothing.

The pre-insert lookup is a courtesy, not the guard -- two concurrent requests
would both pass it. The unique index is the guard, so the insert is attempted
and the loser is reported as already claimed, with 200 rather than 409: the
customer filled the form in correctly and the system simply declined to credit
twice.

Nothing here reads or writes a billing field. These are goodwill minutes and
the date the service stops does not move.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 3: The endpoint's behaviour, under test

Task 2 shipped the code; this task proves every promise in the spec, including the two that are properties of the data model rather than of new logic.

**Repository:** gcal — `D:\Projects\2.LocalEats\github\gcal-booking-api-main`

**Files:**
- Modify: `tests/test_feedback_credit.py` — append to the file Task 1 created
- Test: the same file

**Interfaces:**
- Consumes: `POST /api/feedback-credit` from Task 2, with the response bodies in Task 2's table.
- Produces: nothing later tasks depend on.

- [ ] **Step 1: Append the endpoint tests**

Add to the end of `tests/test_feedback_credit.py`:

```python
import app.main as m
from fastapi.testclient import TestClient
from app.models import Call, EmailLog

client = TestClient(m.app)
TOKEN = "test-feedback-token"


@pytest.fixture
def mail(monkeypatch):
    """Capture send_email instead of talking to SMTP or SES."""
    sent = []

    def fake_send_email(subject, body, to_addresses, reply_to=None, *,
                        html_body=None, settings=None, shop_id=None):
        sent.append({
            "subject": subject,
            "body": body,
            "to": list(to_addresses),
            "shop_id": shop_id,
        })

    monkeypatch.setattr(m, "send_email", fake_send_email)
    return sent


def _use_minutes(code: str, minutes: int) -> None:
    """Burn `minutes` of call time, so the computed balance moves."""
    with Session(engine) as s:
        s.add(Call(shop_id=code, duration_sec=minutes * 60))
        s.commit()


def _post(shop_id: str, token: str = TOKEN, **extra):
    headers = {} if token is None else {"X-Feedback-Token": token}
    body = {"shop_id": shop_id}
    body.update(extra)
    return client.post("/api/feedback-credit", json=body, headers=headers)


def test_first_call_grants_and_logs_the_row(mail):
    code = str(_base + 10)
    _mk_shop(code, topped_up=100)

    r = _post(code, email="owner@shop.test")
    assert r.status_code == 200
    data = r.json()
    assert data["granted"] is True
    assert data["minutes"] == 30
    assert data["previous_balance"] == 100
    assert data["new_balance"] == 130

    with Session(engine) as s:
        rows = s.exec(
            select(TopUp).where(TopUp.payment_reference == f"feedback-credit:{code}")
        ).all()
    assert len(rows) == 1
    row = rows[0]
    assert row.note == "Feedback credit"
    # 'paid' would make this a zero-value sale in the revenue report.
    assert row.status == "manual"
    assert row.amount_cents == 0
    assert row.minutes_added == 30
    assert row.package == "custom"


def test_second_call_is_already_claimed_and_writes_no_second_row(mail):
    code = str(_base + 11)
    _mk_shop(code, topped_up=50)

    first = _post(code, email="owner@shop.test")
    assert first.json()["granted"] is True

    second = _post(code, email="owner@shop.test")
    assert second.status_code == 200
    data = second.json()
    assert data["granted"] is False
    assert data["reason"] == "already_claimed"
    assert data["claimed_at"]

    with Session(engine) as s:
        rows = s.exec(
            select(TopUp).where(TopUp.payment_reference == f"feedback-credit:{code}")
        ).all()
    assert len(rows) == 1


def test_negative_balance_is_absorbed_first(mail):
    """CS's condition: the credit pays down a deficit before adding value."""
    code = str(_base + 12)
    _mk_shop(code, topped_up=100)
    _use_minutes(code, 112)          # balance now -12

    r = _post(code, email="owner@shop.test")
    data = r.json()
    assert data["previous_balance"] == -12
    assert data["new_balance"] == 18


def test_zero_balance_gets_the_full_thirty(mail):
    code = str(_base + 13)
    _mk_shop(code, topped_up=0)

    data = _post(code, email="owner@shop.test").json()
    assert data["previous_balance"] == 0
    assert data["new_balance"] == 30


def test_billing_fields_are_untouched(mail):
    """CS's condition: goodwill minutes do not move the service-stop date."""
    code = str(_base + 14)
    pk = _mk_shop(code, topped_up=10)

    with Session(engine) as s:
        shop = s.get(ShopSettings, pk)
        before = {
            "cancel_effective_on": shop.cancel_effective_on,
            "cancel_requested_at": shop.cancel_requested_at,
            "lifecycle_status": shop.lifecycle_status,
            "billing_day": getattr(shop, "billing_day", None),
        }

    assert _post(code, email="owner@shop.test").json()["granted"] is True

    with Session(engine) as s:
        shop = s.get(ShopSettings, pk)
        after = {
            "cancel_effective_on": shop.cancel_effective_on,
            "cancel_requested_at": shop.cancel_requested_at,
            "lifecycle_status": shop.lifecycle_status,
            "billing_day": getattr(shop, "billing_day", None),
        }

    assert before == after


def test_unknown_shop_is_refused_and_writes_nothing(mail):
    missing = str(_base + 900)

    data = _post(missing, email="nobody@nowhere.test").json()
    assert data["granted"] is False
    assert data["reason"] == "shop_not_found"

    with Session(engine) as s:
        rows = s.exec(
            select(TopUp).where(TopUp.payment_reference == f"feedback-credit:{missing}")
        ).all()
    assert rows == []


def test_missing_shop_id_is_refused(mail):
    r = client.post("/api/feedback-credit", json={}, headers={"X-Feedback-Token": TOKEN})
    assert r.status_code == 200
    assert r.json()["reason"] == "shop_not_found"


@pytest.mark.parametrize("token", ["wrong-token", "", None])
def test_bad_token_is_401_and_writes_nothing(mail, token):
    code = str(_base + 20 + abs(hash(str(token))) % 50)
    _mk_shop(code, topped_up=10)

    r = _post(code, token=token)
    assert r.status_code == 401
    assert r.json()["error"] == "unauthorized"

    with Session(engine) as s:
        rows = s.exec(
            select(TopUp).where(TopUp.payment_reference == f"feedback-credit:{code}")
        ).all()
    assert rows == []


def test_unset_server_token_rejects_everything(mail, monkeypatch):
    """An unconfigured secret must reject, not wave requests through."""
    code = str(_base + 15)
    _mk_shop(code, topped_up=10)

    monkeypatch.delenv("FEEDBACK_CREDIT_TOKEN", raising=False)
    r = _post(code, token="anything")
    assert r.status_code == 401


def test_email_shows_the_arithmetic_on_a_grant(mail):
    code = str(_base + 16)
    _mk_shop(code, topped_up=100)
    _use_minutes(code, 112)          # balance -12

    assert _post(code, email="owner@shop.test").json()["granted"] is True

    assert len(mail) == 1
    sent = mail[0]
    assert sent["to"] == ["owner@shop.test"]
    assert "30" in sent["subject"]
    # All three figures, so a customer landing on 18 can see why.
    assert "-12 minutes" in sent["body"]
    assert "+30 minutes" in sent["body"]
    assert "18 minutes" in sent["body"]
    # The question the email otherwise invites.
    assert "do not change your billing date" in sent["body"]


def test_no_email_on_already_claimed(mail):
    code = str(_base + 17)
    _mk_shop(code, topped_up=10)

    _post(code, email="owner@shop.test")
    assert len(mail) == 1

    _post(code, email="owner@shop.test")
    assert len(mail) == 1          # unchanged: a refusal is not worth an email


def test_credit_survives_an_email_failure(monkeypatch):
    """Losing the notification must not unwind a granted credit."""
    code = str(_base + 18)
    _mk_shop(code, topped_up=10)

    def boom(*a, **kw):
        raise RuntimeError("SES down")

    monkeypatch.setattr(m, "send_email", boom)

    r = _post(code, email="owner@shop.test")
    assert r.status_code == 200
    assert r.json()["granted"] is True

    with Session(engine) as s:
        rows = s.exec(
            select(TopUp).where(TopUp.payment_reference == f"feedback-credit:{code}")
        ).all()
    assert len(rows) == 1


def test_two_concurrent_requests_grant_once(mail):
    """The index, not a lock, is what makes a double-click safe."""
    import threading

    code = str(_base + 19)
    _mk_shop(code, topped_up=10)
    results = []

    def go():
        results.append(_post(code, email="owner@shop.test").json())

    threads = [threading.Thread(target=go) for _ in range(2)]
    for t in threads:
        t.start()
    for t in threads:
        t.join()

    granted = [r for r in results if r.get("granted")]
    assert len(granted) == 1, results

    with Session(engine) as s:
        rows = s.exec(
            select(TopUp).where(TopUp.payment_reference == f"feedback-credit:{code}")
        ).all()
    assert len(rows) == 1
```

- [ ] **Step 2: Run the suite**

Run: `python -m pytest tests/test_feedback_credit.py -v`

Expected: all pass (3 from Task 1 plus the new ones; the parametrised token test counts as 3).

If `test_two_concurrent_requests_grant_once` is flaky under SQLite — SQLite serialises writers and can raise `OperationalError: database is locked` rather than `IntegrityError` — report it rather than deleting the test, and note which exception you saw. The fix is to catch `OperationalError` alongside `IntegrityError` in the endpoint's except clause, but confirm the real exception first instead of guessing.

- [ ] **Step 3: Confirm nothing else regressed**

Run: `python -m pytest tests/test_revenue.py tests/test_monthly_allocation.py tests/test_negative_balance_alerts.py tests/test_shop_lifecycle.py -q`

Expected: all pass.

- [ ] **Step 4: Commit**

```bash
git add tests/test_feedback_credit.py
git commit -m "test(minutes): cover the feedback credit end to end

Includes the two conditions that are properties of the data model rather than
of new logic, because they are promises to the customer and not implementation
details: a shop at -12 lands at +18, and every billing field is byte-identical
before and after a grant. Both would be easy for a later change to break
silently.

Also covers what must NOT happen: no row on an unknown shop, no row on a bad or
unset token, no second row on a second submission, no email on a refusal, and a
granted credit that survives the notification failing.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 4: The feedback form's two modes

**Repository:** masterPanel — `C:\xampp\htdocs\masterPanel-ud-lookup`

**Files:**
- Create: `modules/feedback/assets/config.example.php`
- Modify: `modules/feedback/views/feedbackForm.php`
- Modify: `modules/feedback/controllers/feedbackForm.js`

**Interfaces:**
- Consumes: nothing from the gcal tasks (the form does not call gcal; Task 5's model does).
- Produces: the form posts these additional fields, which Task 5 reads from the payload: `mode` (`araya` or absent), `shop_id`, and `package` forced to `AI Receptionist cancellation` in Araya mode. Config keys `gcal_base_url` and `feedback_credit_token` for Task 5.

- [ ] **Step 1: Read what you are changing**

Run:

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup/modules/feedback
sed -n '1,10p' views/feedbackForm.php
grep -n 'id="shopName"\|id="email"\|id="shopType"\|id="package"\|id="description"\|id="imageDiv"\|name="formVersion"' views/feedbackForm.php
file views/feedbackForm.php controllers/feedbackForm.js
```

Expected: the view opens with `<?php session_start(); require_once '../assets/db/db.php'; ...` and has no `$_GET` handling. The listed ids all exist. Note the line endings `file` reports and preserve them.

- [ ] **Step 2: Create the config example**

Create `modules/feedback/assets/config.example.php`:

```php
<?php
/**
 * Feedback module configuration.
 *
 * Copy this file to config.php and fill in the values. config.php is
 * gitignored: the feedback credit token must never be committed.
 */

return [
    // Base URL of the gcal booking API, no trailing slash.
    // e.g. 'https://api.example.com'
    'gcal_base_url' => '',

    // Shared secret for POST /api/feedback-credit. Must match
    // FEEDBACK_CREDIT_TOKEN in the gcal environment.
    //
    // Empty disables the credit call entirely: feedback is still recorded and
    // the customer is still thanked. That is the safe default, because a
    // half-configured deployment must not look like a working one.
    'feedback_credit_token' => '',
];
```

- [ ] **Step 3: Confirm config.php is gitignored**

Run:

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup
cat modules/feedback/.gitignore 2>/dev/null
git check-ignore -v modules/feedback/assets/config.php
```

If `git check-ignore` prints nothing, the file is NOT ignored. Add `modules/feedback/assets/config.php` to the repository's `.gitignore` (or create `modules/feedback/.gitignore` containing `assets/config.php`), then re-run and confirm it reports a match. Report which you did.

- [ ] **Step 4: Add the mode handling to the view's PHP header**

Replace the opening PHP block of `modules/feedback/views/feedbackForm.php` (currently lines 1–6) with:

```php
<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;

// Araya mode is entered by the downgrade form's post-submit redirect, which
// carries the shop identity it already resolved from the email lookup.
//
// A shop_id is required for the mode, not just mode=araya: the 30-minute
// credit is granted once per shop for the lifetime of the account, and a
// once-per-lifetime grant cannot key off a shop name the customer types. With
// no shop_id this form is exactly what it was before -- a lead enquiry form --
// so a truncated URL degrades to the old behaviour instead of breaking.
$fbMode   = ($_GET['mode'] ?? '') === 'araya' ? 'araya' : 'lead';
$fbShopId = trim($_GET['shop_id'] ?? '');
$isAraya  = ($fbMode === 'araya' && $fbShopId !== '');

// Prefilled from the lookup, shown rather than hidden so the customer can see
// the system recognised them. shop_id is what the credit keys on, so what they
// read here cannot redirect it.
$fbShopName = trim($_GET['shopName'] ?? '');
$fbEmail    = trim($_GET['email'] ?? '');
?>
```

- [ ] **Step 5: Make the shop and email fields prefill**

Replace the `shopName` field block:

```php
                    <div class="mb-4">
                        <label for="shopName" class="form-label"><i class="bi bi-shop-window"></i> Shop Name</label>
                        <input type="text" class="form-control" id="shopName" name="shopName" placeholder="e.g. The Calm Spa" value="<?php echo htmlspecialchars($fbShopName); ?>"<?php echo $isAraya ? ' readonly' : ''; ?> required>
                    </div>
```

and the `email` field block:

```php
                    <div class="mb-4">
                        <label for="email" class="form-label"><i class="bi bi-envelope"></i> Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="e.g. hello@example.com" value="<?php echo htmlspecialchars($fbEmail); ?>"<?php echo $isAraya ? ' readonly' : ''; ?> required>
                    </div>
```

- [ ] **Step 6: Gate the three lead-only fields and force the topic**

Wrap the `shopType` block (including its `shopTypeOtherWrapper` sibling) so it renders only in lead mode:

```php
                    <?php if (!$isAraya): ?>
```

immediately before `<div class="mb-4">` of the `shopType` field, and

```php
                    <?php endif; ?>
```

immediately after the closing `</div>` of `shopTypeOtherWrapper`.

Do the same for the `package` block and its `packageOtherWrapper` sibling, but in Araya mode emit a hidden field in their place, so the submitted payload still carries a topic:

```php
                    <?php if ($isAraya): ?>
                    <input type="hidden" name="package" value="AI Receptionist cancellation">
                    <?php else: ?>
```

immediately before the `package` field's `<div class="mb-4">`, and

```php
                    <?php endif; ?>
```

immediately after the closing `</div>` of `packageOtherWrapper`.

Then gate the upload block the same way — `<?php if (!$isAraya): ?>` before `<div class="mb-3" id="imageDiv">` and `<?php endif; ?>` after its closing `</div>`.

- [ ] **Step 7: Ask the Araya question, and carry the identity**

Replace the `description` label and textarea so the prompt matches the mode:

```php
                    <div class="mb-4">
                        <?php if ($isAraya): ?>
                        <label for="description" class="form-label"><i class="bi bi-chat-right-text"></i> Why did you cancel the AI Receptionist?</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Anything you tell us here helps us improve it..." required></textarea>
                        <?php else: ?>
                        <label for="description" class="form-label"><i class="bi bi-chat-right-text"></i> Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Tell us more about your business or needs..." required></textarea>
                        <?php endif; ?>
                    </div>
```

Then extend the hidden inputs at the end of the form, replacing:

```php
                    <input type="hidden" name="formVersion" value="1.0.0">
                    <input type="hidden" name="emailVersion" value="1.0.0">
```

with:

```php
                    <input type="hidden" name="formVersion" value="1.1.0">
                    <input type="hidden" name="emailVersion" value="1.0.0">
                    <input type="hidden" name="mode" value="<?php echo htmlspecialchars($fbMode); ?>">
                    <input type="hidden" name="shop_id" value="<?php echo htmlspecialchars($fbShopId); ?>">
```

- [ ] **Step 8: Tell the customer what they are about to get**

In Araya mode the form should say why it is worth filling in. Replace the intro paragraph:

```php
                <?php if ($isAraya): ?>
                <p class="text-muted mt-3">
                    Sorry to see the AI Receptionist go. Telling us why takes a minute,
                    and we will add <strong>30 free minutes</strong> to your account as a thank-you.
                </p>
                <?php else: ?>
                <p class="text-muted mt-3">
                    Our feedback system allows you to easily share your thoughts, suggestions, or issues. Every entry helps us improve our service and better meet your needs.
                </p>
                <?php endif; ?>
```

- [ ] **Step 9: Check the syntax and render both modes**

Run:

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup/modules/feedback
php -l views/feedbackForm.php
printf "  if: %s  endif: %s\n" "$(grep -c '<?php if' views/feedbackForm.php)" "$(grep -c '<?php endif' views/feedbackForm.php)"
```

Expected: no syntax errors, and the `if`/`endif` counts are equal.

Then render each mode (run php from inside `views/`, because the requires are relative):

```bash
cd views
php -r '
foreach ([
  "lead"  => [],
  "araya" => ["mode"=>"araya","shop_id"=>"12345","shopName"=>"Thai Spa","email"=>"owner@spa.test"],
] as $label => $q) {
  $_GET = $q; ob_start(); require "feedbackForm.php"; $h = ob_get_clean();
  echo "=== $label ===\n";
  foreach ([
    "id=\"shopType\""        => "shopType",
    "id=\"package\""          => "package select",
    "id=\"imageDiv\""         => "upload",
    "AI Receptionist cancellation" => "hidden topic",
    "name=\"shop_id\""        => "shop_id input",
    "30 free minutes"         => "credit promise",
    "Why did you cancel"      => "araya question",
    "readonly"                => "readonly fields",
  ] as $needle => $what) {
    printf("  %-16s %s\n", $what, strpos($h,$needle)!==false ? "present" : "absent");
  }
}' 2>&1 | grep -v Warning
```

Expected:

```
=== lead ===
  shopType         present
  package select   present
  upload           present
  hidden topic     absent
  shop_id input    present
  credit promise   absent
  araya question   absent
  readonly fields  absent
=== araya ===
  shopType         absent
  package select   absent
  upload           absent
  hidden topic     present
  shop_id input    present
  credit promise   present
  araya question   present
  readonly fields  present
```

Note `shop_id input` is present in BOTH — in lead mode it is simply empty, which is what Task 5 keys off.

- [ ] **Step 10: Confirm mode=araya WITHOUT a shop_id degrades to lead mode**

Run:

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup/modules/feedback/views
php -r '
$_GET = ["mode"=>"araya"];   // no shop_id
ob_start(); require "feedbackForm.php"; $h = ob_get_clean();
echo "  shopType present (should be yes): ", strpos($h,"id=\"shopType\"")!==false?"yes":"no", "\n";
echo "  credit promise  (should be no ): ", strpos($h,"30 free minutes")!==false?"yes":"no", "\n";
' 2>&1 | grep -v Warning
```

Expected: `yes` then `no`. A truncated URL must not promise a credit it cannot deliver.

- [ ] **Step 11: Pass the new fields through the controller**

`collectFormData` in `controllers/feedbackForm.js` serialises the form, so `mode` and `shop_id` ride along with no change. Confirm that:

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup/modules/feedback
grep -n 'serializeArray\|collectFormData\|function sendData\|location.replace' controllers/feedbackForm.js
node --check controllers/feedbackForm.js
```

Expected: a `serializeArray()`-based collector exists, `node --check` is silent. If the controller builds its payload field-by-field instead, add `mode` and `shop_id` to it and say so in your report.

- [ ] **Step 12: Commit**

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup
git add modules/feedback/views/feedbackForm.php \
        modules/feedback/controllers/feedbackForm.js \
        modules/feedback/assets/config.example.php \
        .gitignore
git commit -m "feat(feedback): add an Araya mode to the feedback form

The downgrade form redirects a customer who cancelled the AI Receptionist here,
but this form was built for leads: it asks which package they are interested
in, what type of shop they run, and invites an image upload. Asking someone who
has just cancelled what they would like to buy is the wrong question, so those
three fields are hidden and the topic is submitted as the cancellation itself.

The mode needs a shop_id, not just mode=araya. The credit behind it is granted
once per shop for the lifetime of the account, and that cannot key off a shop
name the customer types -- 'Thai Spa' and 'thai spa' would be two shops. With
no shop_id the form is exactly what it was before, so a truncated URL degrades
to a lead enquiry instead of promising a credit it cannot deliver.

Shop name and email are prefilled readonly rather than hidden, so the customer
can see they were recognised. What they read cannot redirect the credit: the
shop_id is what it keys on.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 5: Call the credit endpoint server-side

**Repository:** masterPanel — `C:\xampp\htdocs\masterPanel-ud-lookup`

**Files:**
- Modify: `modules/feedback/models/feedbackForm.php`

**Interfaces:**
- Consumes: `POST /api/feedback-credit` from Task 2; `mode` / `shop_id` from Task 4's form; `gcal_base_url` / `feedback_credit_token` from Task 4's config.
- Produces: the model's JSON response gains a `credit` object, which Task 6's controller reads:
  `{"result":"success","msg":"...","credit":{"attempted":bool,"granted":bool,"reason":string|null,"minutes":int|null,"new_balance":int|null,"claimed_at":string|null}}`
  `reason` is one of `already_claimed`, `shop_not_found`, `credit_service_unavailable`, `not_configured`, or null when granted.

- [ ] **Step 1: Read the current model**

Run:

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup/modules/feedback
cat models/feedbackForm.php
file models/feedbackForm.php
```

Expected: an 18-line file that inserts `$_POST['payload']` into `feedback(dataLogs)` and echoes `{"result":"success",...}`. Note the line endings.

- [ ] **Step 2: Replace the model**

Replace the whole of `modules/feedback/models/feedbackForm.php` with:

```php
<?php
global $db;
session_start();
header('Content-Type: application/json');
include '../assets/db/db.php';
include '../assets/db/initDB.php';

$result = ['result' => '', 'msg' => '', 'credit' => [
    'attempted'   => false,
    'granted'     => false,
    'reason'      => null,
    'minutes'     => null,
    'new_balance' => null,
    'claimed_at'  => null,
]];

$payload = !empty($_POST['payload']) ? $_POST['payload'] : 'no data';
$data    = json_decode($payload, true) ?: [];

// The feedback row is written first and unconditionally. The credit is a
// reward for submitting feedback, never a gate on recording it, so nothing
// below may cost us the submission.
$logsToDB = $db->query('INSERT INTO `feedback`(`dataLogs`) VALUES (?)', [$payload]);
$feedbackId = $db->lastInsertID();

$result['result'] = 'success';
$result['msg']    = 'Save to DB successfully!';

/**
 * Ask the gcal API to grant the 30-minute goodwill credit.
 *
 * Called from here, server-side, rather than from the browser: the shared
 * token would otherwise be readable by anyone who opened the feedback page,
 * and anyone holding it could credit any shop.
 *
 * Returns the credit block for the response. Every failure is a refusal with a
 * reason, never an exception: the caller has already been thanked for feedback
 * that is already saved.
 */
function udmRequestFeedbackCredit(string $shopId, string $email, string $feedbackRef): array
{
    $out = [
        'attempted'   => true,
        'granted'     => false,
        'reason'      => null,
        'minutes'     => null,
        'new_balance' => null,
        'claimed_at'  => null,
    ];

    $configPath = __DIR__ . '/../assets/config.php';
    if (!is_file($configPath)) {
        $out['reason'] = 'not_configured';
        return $out;
    }

    $config = require $configPath;
    $baseUrl = rtrim((string) ($config['gcal_base_url'] ?? ''), '/');
    $token   = (string) ($config['feedback_credit_token'] ?? '');

    // A half-configured deployment must not look like a working one, and must
    // not look like a refused credit either -- 'not_configured' is a
    // deployment problem, distinct from the shop having already claimed.
    if ($baseUrl === '' || $token === '') {
        $out['reason'] = 'not_configured';
        return $out;
    }

    $body = json_encode([
        'shop_id'      => $shopId,
        'email'        => $email,
        'feedback_ref' => $feedbackRef,
    ], JSON_UNESCAPED_UNICODE);

    $ch = curl_init($baseUrl . '/api/feedback-credit');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'X-Feedback-Token: ' . $token,
        ],
        // Short, and deliberately so: the customer is waiting on this request
        // before they see their thank-you page. A credit we failed to grant can
        // be granted by hand from the Minutes page; a page that hangs cannot be
        // recovered.
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response = curl_exec($ch);
    $status   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false || $status === 0) {
        error_log('feedback credit transport failed for shop ' . $shopId . ': ' . $curlErr);
        $out['reason'] = 'credit_service_unavailable';
        return $out;
    }

    if ($status === 401) {
        // A token problem is ours, not the customer's. They are told only that
        // their feedback arrived.
        error_log('feedback credit rejected the token for shop ' . $shopId);
        $out['reason'] = 'credit_service_unavailable';
        return $out;
    }

    $decoded = json_decode((string) $response, true);
    if (!is_array($decoded)) {
        error_log('feedback credit returned unparseable body for shop ' . $shopId . ': HTTP ' . $status);
        $out['reason'] = 'credit_service_unavailable';
        return $out;
    }

    $out['granted'] = !empty($decoded['granted']);
    if ($out['granted']) {
        $out['minutes']     = isset($decoded['minutes']) ? (int) $decoded['minutes'] : null;
        $out['new_balance'] = isset($decoded['new_balance']) ? (int) $decoded['new_balance'] : null;
    } else {
        $out['reason']      = isset($decoded['reason']) ? (string) $decoded['reason'] : 'credit_service_unavailable';
        $out['claimed_at']  = isset($decoded['claimed_at']) ? (string) $decoded['claimed_at'] : null;
        $out['new_balance'] = isset($decoded['new_balance']) ? (int) $decoded['new_balance'] : null;
    }

    return $out;
}

// Only Araya mode with a real shop_id asks for a credit. Lead submissions are
// untouched by any of this.
$mode   = (string) ($data['mode'] ?? 'lead');
$shopId = trim((string) ($data['shop_id'] ?? ''));

if ($mode === 'araya' && $shopId !== '') {
    try {
        $result['credit'] = udmRequestFeedbackCredit(
            $shopId,
            trim((string) ($data['email'] ?? '')),
            $feedbackId ? ('feedback-' . $feedbackId) : ''
        );
    } catch (Throwable $e) {
        // The feedback row is already committed. A credit we could not request
        // is recoverable by hand; losing the feedback is not.
        error_log('feedback credit call threw for shop ' . $shopId . ': ' . $e->getMessage());
        $result['credit']['attempted'] = true;
        $result['credit']['reason']    = 'credit_service_unavailable';
    }

    // Record what happened alongside the submission, so an admin chasing an
    // ungranted credit can see the outcome without reading web server logs.
    $db->query(
        'UPDATE `feedback` SET `dataLogs` = ? WHERE `id` = ?',
        [
            json_encode(
                array_merge($data, ['creditOutcome' => $result['credit']]),
                JSON_UNESCAPED_UNICODE
            ),
            $feedbackId,
        ]
    );
}

echo json_encode($result);
```

- [ ] **Step 3: Confirm the table has the columns this UPDATE needs**

The update assumes `feedback` has an `id` primary key and that `lastInsertID()` works on this wrapper.

Run:

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup/modules/feedback
php -r '
require "assets/db/db.php"; require "assets/db/initDB.php"; global $db;
$db->query("DESCRIBE `feedback`");
foreach ($db->fetchAll() as $r) { echo "  ", $r["Field"], "  ", $r["Type"], "  ", $r["Key"], "\n"; }
echo "lastInsertID exists: ", method_exists($db, "lastInsertID") ? "yes" : "NO", "\n";
' 2>&1
```

Expected: a `dataLogs` column, an `id` column marked `PRI`, and `lastInsertID exists: yes`.

If there is no `id` column, STOP and report: the UPDATE cannot target the row, and the plan needs the outcome stored differently (most likely by building the full payload before the single INSERT instead of updating after it).

- [ ] **Step 4: Check the syntax**

Run: `php -l modules/feedback/models/feedbackForm.php`

Expected: `No syntax errors detected`.

- [ ] **Step 5: Prove each refusal path without a running gcal**

Run from `modules/feedback/models/`:

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup/modules/feedback/models
php -r '
// Load only the function, not the request-handling script around it.
$src = file_get_contents("feedbackForm.php");
$start = strpos($src, "function udmRequestFeedbackCredit");
$end   = strpos($src, "// Only Araya mode");
eval("?>" . "<?php " . substr($src, $start, $end - $start));

echo "=== no config.php present or token empty ===\n";
$r = udmRequestFeedbackCredit("12345", "a@b.test", "feedback-1");
printf("  attempted=%s granted=%s reason=%s\n",
  var_export($r["attempted"], true), var_export($r["granted"], true), $r["reason"]);
' 2>&1
```

Expected: `attempted=true granted=false reason=not_configured` (because `config.php` does not exist yet, or has empty values).

Then point it at a URL that refuses connections, to prove the transport path:

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup/modules/feedback
cp assets/config.example.php assets/config.php
php -r '
$p = "assets/config.php";
$s = file_get_contents($p);
$s = str_replace("\x27gcal_base_url\x27 => \x27\x27", "\x27gcal_base_url\x27 => \x27http://127.0.0.1:9\x27", $s);
$s = str_replace("\x27feedback_credit_token\x27 => \x27\x27", "\x27feedback_credit_token\x27 => \x27t\x27", $s);
file_put_contents($p, $s);
echo "config pointed at a dead port\n";
'
cd models
php -r '
$src = file_get_contents("feedbackForm.php");
$start = strpos($src, "function udmRequestFeedbackCredit");
$end   = strpos($src, "// Only Araya mode");
eval("?>" . "<?php " . substr($src, $start, $end - $start));
$r = udmRequestFeedbackCredit("12345", "a@b.test", "feedback-1");
printf("  reason=%s (expect credit_service_unavailable)\n", $r["reason"]);
' 2>&1
cd ..
rm assets/config.php
echo "config.php removed again"
```

Expected: `reason=credit_service_unavailable`. Confirm you removed `assets/config.php` afterwards — it is gitignored, but leaving a dead-port config behind would make Task 7's checks confusing.

- [ ] **Step 6: Commit**

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup
git add modules/feedback/models/feedbackForm.php
git commit -m "feat(feedback): request the Araya credit server-side

The call lives here rather than in the browser because the shared token would
otherwise be readable by anyone who opened the feedback page, and anyone
holding it could credit any shop.

The feedback row is written first and unconditionally. Every failure below it
is a refusal carrying a reason rather than an exception: a transport failure, a
rejected token and an unparseable body all resolve to
credit_service_unavailable, and a deployment that has not been configured
resolves to not_configured so it cannot be mistaken for a shop that already
claimed. A credit we failed to grant can be granted by hand from the Minutes
page; feedback we failed to record is gone.

The outcome is written back onto the submission, so an admin chasing an
ungranted credit can see what happened without reading web server logs. The
timeout is short on purpose: the customer is waiting on this before their
thank-you page.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 6: Tell the customer what happened

**Repository:** masterPanel — `C:\xampp\htdocs\masterPanel-ud-lookup`

**Files:**
- Modify: `modules/feedback/controllers/feedbackForm.js`
- Modify: `modules/feedback/views/thankyou.php`

**Interfaces:**
- Consumes: the `credit` object from Task 5's model response.
- Produces: nothing later tasks depend on.

- [ ] **Step 1: Read the submit path and the thank-you page**

Run:

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup/modules/feedback
grep -n 'saveToDB\|success:\|location.replace\|thankyou' controllers/feedbackForm.js
sed -n '1,20p' views/thankyou.php
file controllers/feedbackForm.js views/thankyou.php
```

Note how `saveToDB` currently handles success, and whether `thankyou.php` reads any query parameters today (it does not).

- [ ] **Step 2: Carry the outcome to the thank-you page**

In `controllers/feedbackForm.js`, find the `saveToDB` success handler and pass the credit outcome through the redirect. Replace its `success` callback with:

```javascript
        success: (res) => {
            // The credit outcome rides in the query string so the thank-you
            // page can state plainly what happened. The page re-states what
            // the server decided; it never decides anything itself.
            let data = res;
            if (typeof data === 'string') {
                try { data = JSON.parse(data); } catch (e) { data = {}; }
            }
            const credit = (data && data.credit) || {};
            const params = [];
            if (credit.attempted) {
                params.push('credit=' + encodeURIComponent(credit.granted ? 'granted' : (credit.reason || 'unknown')));
                if (credit.granted && credit.minutes != null) {
                    params.push('minutes=' + encodeURIComponent(credit.minutes));
                }
                if (credit.new_balance != null) {
                    params.push('balance=' + encodeURIComponent(credit.new_balance));
                }
                if (credit.claimed_at) {
                    params.push('claimed=' + encodeURIComponent(credit.claimed_at));
                }
            }
            window.location.replace('thankyou.php' + (params.length ? '?' + params.join('&') : ''));
        },
```

If the existing handler also drives a countdown or a result message, keep that behaviour and only replace the redirect line — report what you kept.

- [ ] **Step 3: State the outcome on the thank-you page**

At the very top of `modules/feedback/views/thankyou.php`, before any output, insert:

```php
<?php
// Set by the feedback form's redirect. The page only re-states what the server
// already decided; it never grants anything or infers an outcome.
$creditState = $_GET['credit'] ?? '';
$creditMins  = isset($_GET['minutes']) ? (int) $_GET['minutes'] : 0;
$creditBal   = isset($_GET['balance']) && $_GET['balance'] !== '' ? (int) $_GET['balance'] : null;
$creditOn    = trim($_GET['claimed'] ?? '');
?>
```

Then inside the page body, immediately before the closing of the main card, insert:

```php
                <?php if ($creditState === 'granted'): ?>
                <div class="alert alert-success mt-4">
                    <strong><?php echo $creditMins ?: 30; ?> free minutes added.</strong>
                    <?php if ($creditBal !== null): ?>
                    Your balance is now <strong><?php echo $creditBal; ?> minutes</strong>.
                    <?php endif; ?>
                    We have emailed you the details. These are goodwill minutes and do not
                    change your billing or cancellation date.
                </div>
                <?php elseif ($creditState === 'already_claimed'): ?>
                <div class="alert alert-info mt-4">
                    Thank you for the feedback. This shop has already received its free
                    minutes<?php echo $creditOn !== '' ? ' on ' . htmlspecialchars($creditOn) : ''; ?>,
                    and the offer is one per shop.
                </div>
                <?php elseif ($creditState !== ''): ?>
                <div class="alert alert-info mt-4">
                    Thank you for the feedback. We could not confirm your free minutes
                    automatically, so our team will check and apply them for you.
                </div>
                <?php endif; ?>
```

The final branch deliberately covers `shop_not_found`, `credit_service_unavailable`, `not_configured` and anything unrecognised with the same sentence: each is our problem to chase, and none is something the customer can act on.

- [ ] **Step 4: Check the syntax and render every state**

Run:

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup/modules/feedback
node --check controllers/feedbackForm.js && echo "JS OK"
php -l views/thankyou.php
cd views
php -r '
foreach ([
  "no credit (lead)"   => [],
  "granted"            => ["credit"=>"granted","minutes"=>"30","balance"=>"18"],
  "already claimed"    => ["credit"=>"already_claimed","claimed"=>"2026-09-02"],
  "shop not found"     => ["credit"=>"shop_not_found"],
  "service down"       => ["credit"=>"credit_service_unavailable"],
  "not configured"     => ["credit"=>"not_configured"],
] as $label => $q) {
  $_GET = $q; ob_start(); require "thankyou.php"; $h = ob_get_clean();
  $box = strpos($h,"alert-success")!==false ? "SUCCESS"
       : (strpos($h,"alert-info")!==false ? "info" : "none");
  printf("  %-18s -> %s\n", $label, $box);
  if ($label === "granted")         { echo "      balance shown: ", strpos($h,"18 minutes")!==false?"yes":"no", "\n"; }
  if ($label === "already claimed") { echo "      date shown:    ", strpos($h,"2026-09-02")!==false?"yes":"no", "\n"; }
}' 2>&1 | grep -v Warning
```

Expected:

```
  no credit (lead)   -> none
  granted            -> SUCCESS
      balance shown: yes
  already claimed    -> info
      date shown:    yes
  shop not found     -> info
  service down       -> info
  not configured     -> info
```

- [ ] **Step 5: Confirm the page cannot be made to claim a credit**

The page reads the query string, so anyone can type `?credit=granted`. That is acceptable — it changes a sentence on a page, not a balance — but confirm it grants nothing:

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup/modules/feedback
grep -c 'curl_\|feedback-credit\|gcal_base_url\|INSERT\|UPDATE' views/thankyou.php
```

Expected: `0`. The thank-you page must contain no credit logic, no HTTP call and no write. Report the number you got.

- [ ] **Step 6: Commit**

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup
git add modules/feedback/controllers/feedbackForm.js modules/feedback/views/thankyou.php
git commit -m "feat(feedback): state the credit outcome on the thank-you page

A customer who was promised 30 free minutes needs to be told whether they got
them. Granted says so with the new balance; an already-claimed shop is told
plainly, with the date, that the offer is one per shop.

Every other outcome -- an unknown shop, the credit service being unreachable, a
deployment that is not configured -- gets the same sentence, because each is
ours to chase and none is something the customer can act on.

The page re-states what the server decided and contains no credit logic, no
HTTP call and no write, so a hand-typed ?credit=granted changes a sentence and
nothing else.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 7: Turn the flow on from the downgrade form

The last link: the downgrade form already redirects to a feedback URL with the shop name and email, but not the `shop_id` the credit keys on.

**Repository:** masterPanel — `C:\xampp\htdocs\masterPanel-ud-lookup`

**Files:**
- Modify: `modules/upgrade-downgrade/controllers/downgradeForm.js` — `udmPostSubmitUrl()`
- Modify: `modules/upgrade-downgrade/README.md` — the open-questions section

**Interfaces:**
- Consumes: Task 4's form, which needs `mode=araya` and `shop_id` in the query string.
- Produces: nothing.

- [ ] **Step 1: Read the function you are changing**

Run:

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup/modules/upgrade-downgrade
sed -n '/^function udmPostSubmitUrl/,/^}/p' controllers/downgradeForm.js
```

Expected: it reads `document.body.dataset.arayaFeedbackUrl`, returns `thankyou.php` when it is empty or when no checked product is tagged `araya`, and otherwise appends `shopName` and `email` via `$.param`.

- [ ] **Step 2: Add the shop id and the mode**

Replace the `$.param({...})` call at the end of `udmPostSubmitUrl` with:

```javascript
    return feedbackUrl + sep + $.param({
        mode:     'araya',
        // The feedback form needs this to enter Araya mode at all, and the
        // credit behind it is granted once per shop for the lifetime of the
        // account -- which cannot key off a shop name the customer types. It is
        // whatever the email lookup resolved, and empty when no lookup
        // succeeded, in which case the form correctly stays a lead enquiry.
        shop_id:  $('#mondayProjectId').val() || '',
        shopName: formData.shopName || '',
        email:    formData.emailAddress || '',
    });
```

- [ ] **Step 3: Verify every case still behaves**

Run:

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup/modules/upgrade-downgrade
node --check controllers/downgradeForm.js && echo "JS OK"
```

Then exercise the real function with a stub:

```bash
SP="C:/Users/comp734/AppData/Local/Temp/claude/c--xampp-htdocs-masterPanel/b106ee00-cfec-470b-a877-07cd25bc67b2/scratchpad"
mkdir -p "$SP"
sed -n '/^function udmPostSubmitUrl/,/^}/p' controllers/downgradeForm.js > "$SP/fn.js"
cat > "$SP/run.js" <<'EOF'
let FEEDBACK = '', PROJECT_ID = '';
global.document = { body: { dataset: { get arayaFeedbackUrl() { return FEEDBACK; } } } };
global.$ = function (sel) { return { val: () => (sel === '#mondayProjectId' ? PROJECT_ID : '') }; };
global.$.param = o => Object.keys(o).map(k => encodeURIComponent(k) + '=' + encodeURIComponent(o[k])).join('&');
let udmProductTags = {};
EOF
cat "$SP/fn.js" >> "$SP/run.js"
cat >> "$SP/run.js" <<'EOF'
const FD = { shopName: 'Thai Spa & Co', emailAddress: 'owner@spa.test' };
function t(label, url, pid, tags, cancelled) {
  FEEDBACK = url; PROJECT_ID = pid; udmProductTags = tags;
  console.log('  ' + label + '\n     ' + udmPostSubmitUrl(cancelled, FD));
}
t('url unset, araya',        '',                            '12345', {'AI Receptionist':['araya']}, ['AI Receptionist']);
t('url set, NOT araya',      'https://f.test/araya',        '12345', {'Local Starter Bundle':['plan_bundle']}, ['Local Starter Bundle']);
t('url set, araya, has id',  'https://f.test/araya',        '12345', {'AI Receptionist':['araya']}, ['AI Receptionist']);
t('url set, araya, NO id',   'https://f.test/araya',        '',      {'AI Receptionist':['araya']}, ['AI Receptionist']);
t('url already has ?',       'https://f.test/f?src=cancel', '12345', {'AI Receptionist':['araya']}, ['AI Receptionist']);
EOF
node "$SP/run.js"
rm -f "$SP/fn.js" "$SP/run.js"
```

Expected:

```
  url unset, araya
     thankyou.php
  url set, NOT araya
     thankyou.php
  url set, araya, has id
     https://f.test/araya?mode=araya&shop_id=12345&shopName=Thai%20Spa%20%26%20Co&email=owner%40spa.test
  url set, araya, NO id
     https://f.test/araya?mode=araya&shop_id=&shopName=Thai%20Spa%20%26%20Co&email=owner%40spa.test
  url already has ?
     https://f.test/f?src=cancel&mode=araya&shop_id=12345&shopName=Thai%20Spa%20%26%20Co&email=owner%40spa.test
```

The fourth case matters: an empty `shop_id` still redirects, and Task 4's form then renders as a lead enquiry rather than promising a credit it cannot deliver.

- [ ] **Step 4: Record the deployment steps**

In `modules/upgrade-downgrade/README.md`, under `## Open questions`, replace the "Deferred to their own specs" bullet about the Araya feedback form:

```markdown
- **The AI Araya feedback form** (item 7's redirect destination). The hook is
  built and gated on `araya_feedback_url`; the form itself does not exist yet.
```

with:

```markdown
- **The AI Araya feedback form** is built: `modules/feedback` has an Araya mode,
  and completing it grants the shop 30 goodwill minutes once per shop for the
  lifetime of the account. See
  `docs/superpowers/specs/2026-10-14-araya-feedback-credit-design.md`.

  Three settings have to line up before the flow does anything:

  1. `araya_feedback_url` in this module's `config.php` must point at
     `modules/feedback/views/feedbackForm.php`. The redirect appends
     `mode=araya`, `shop_id`, `shopName` and `email` itself.
  2. `gcal_base_url` and `feedback_credit_token` in
     `modules/feedback/assets/config.php` (copied from `config.example.php`).
  3. `FEEDBACK_CREDIT_TOKEN` in the gcal API's environment, matching the token
     above. Unset there means every credit request is rejected, which is the
     safe default.

  With any of them unset the customer still reaches a thank-you page and their
  feedback is still recorded; only the credit is skipped.
```

- [ ] **Step 5: Commit**

```bash
cd /c/xampp/htdocs/masterPanel-ud-lookup
git add modules/upgrade-downgrade/controllers/downgradeForm.js \
        modules/upgrade-downgrade/README.md
git commit -m "feat(upgrade-downgrade): pass the shop id to the feedback form

The redirect already carried the shop name and email so the customer would not
retype them. The credit behind the form needs the shop id as well: it is
granted once per shop for the lifetime of the account, and that cannot key off
a name the customer types, where 'Thai Spa' and 'thai spa' would be two shops.

The id is whatever the email lookup resolved, and empty when no lookup
succeeded. The redirect still happens in that case and the feedback form
renders as an ordinary lead enquiry, so a customer whose shop we could not
identify is never promised minutes we cannot grant.

The README now lists the three settings that have to line up -- the feedback
URL here, the gcal URL and token in the feedback module, and the matching token
in the gcal environment -- and states that with any of them unset the feedback
is still recorded and only the credit is skipped.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Deployment

In order, because each step is useless without the one before it:

1. **gcal:** deploy, which runs `init_db()` and creates `ux_top_up_feedback_credit`. Set `FEEDBACK_CREDIT_TOKEN` to a fresh random secret. Unset means every request is refused.
2. **masterPanel:** copy `modules/feedback/assets/config.example.php` to `config.php` (gitignored) and fill in `gcal_base_url` and the same `feedback_credit_token`.
3. **masterPanel:** set `araya_feedback_url` in `modules/upgrade-downgrade/assets/config.php` to the feedback form's URL. Until this is set the downgrade form redirects to its own thank-you page and nothing else in this plan runs.

Verify end to end on one real shop before announcing it: submit the Araya feedback form, confirm the `TopUp` row carries `note = "Feedback credit"` and `status = "manual"`, confirm the balance moved by exactly 30, confirm the email arrived with all three figures, and confirm the shop's `cancel_effective_on` is unchanged.

**MySQL caveat:** the partial unique index is Postgres and SQLite syntax. On MySQL the migration's `try/except` swallows the failure and the deployment has no database-level guard, leaving only the endpoint's pre-insert lookup — which two concurrent requests can both pass. Production is Postgres, so this is recorded rather than solved.

## Self-review notes

Checked against the spec section by section. Covered: the partial unique index (Task 1), the endpoint and its four outcomes (Task 2), every tested promise including the negative-balance and billing-untouched conditions (Task 3), the form's two modes and the no-`shop_id` fallback (Task 4), the server-side call and all four refusal reasons (Task 5), what the customer is told (Task 6), and the `shop_id` hand-off plus deployment documentation (Task 7).

Type and name consistency checked across tasks: `feedback-credit:<shop_id>` is written identically in Tasks 1, 2 and 3; `FEEDBACK_CREDIT_MINUTES` is defined in Task 2 and referenced in Task 2's email helper only; the `credit` response object's six keys are produced in Task 5 and consumed in Task 6 under the same names; `gcal_base_url` and `feedback_credit_token` are created in Task 4 and read in Task 5.

Two things this plan does **not** assume, and verifies instead of trusting: that `feedback` has an `id` column for Task 5's UPDATE (Task 5 Step 3 stops the task if it does not), and that Task 4's controller serialises the form rather than building its payload field-by-field (Task 4 Step 11 checks and adapts).

One risk carried deliberately: Task 3's concurrency test may be flaky on SQLite, which serialises writers and can raise `OperationalError` instead of `IntegrityError`. The step says to report the real exception rather than widening the `except` on a guess, because the production dialect is Postgres and the two behave differently here.
