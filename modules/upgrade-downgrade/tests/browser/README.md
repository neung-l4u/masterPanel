# Browser verification — downgrade form revisions

Covers the checks the implementation plan deferred to a human: the email-lookup
failure paths on **both** forms, the Araya and plan-bundle conditionals, the
staff/customer split, and the webhook payload.

Nothing is mocked except the two cases that cannot be provoked otherwise (a 503
from the lookup, and a product classification the mirror does not happen to
hold). The lookup itself runs against the real endpoint and the real mirror,
because every fix under test is about what happens on a real miss, a real
multi-branch match, or a real transport failure.

## Running it

Needs Apache serving this worktree from `htdocs`, and a populated mirror.

```bash
NODE_PATH="$(npm root -g)" node tests/browser/downgrade-form.spec.js
```

Playwright is installed globally (`playwright@1.62.1`), hence `NODE_PATH`.
Chromium is already in the shared browser cache. Expect **64 passed**, and a
non-zero exit code on any failure.

## Why the upgrade form is in here

Task 3 extracted the email lookup out of `controllers/upgradeForm.js` into the
shared `assets/js/emailLookup.js`. The gate on that refactor is "the upgrade
form behaves identically", which nothing automated covered. The `U*` checks are
that gate: a regression there means the extraction broke something, even if
every downgrade check still passes.

## Two things to know before trusting a failure

**The inputs are hidden by CSS.** `.radio-card-v input` and `.check-card input`
are `display: none` and the `<label>` is the visible control. Clicking the input
times out. Use the `tick()` helper, which clicks the label.

**`isVisible()` is the wrong assertion here.** This codebase hides things with
`d-none`, and Playwright's `isVisible()` also returns false when an *ancestor*
is collapsed — which made a correctly working form look broken in three places
while this suite was being written. Use the `shown()` helper, which reads the
class.

## What is deliberately not covered

- `modules/feedback`'s Araya mode and the 30-minute credit: not built yet
  (Tasks 4–7 of the feedback-credit plan).
- Email delivery, and the Make.com scenario's own behaviour. The payload shape
  is asserted; what Make does with it is not ours to run.
