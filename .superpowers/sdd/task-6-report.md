# Task 6 report: buildBrief fixes (current)

Status: DONE. Suite: 100 passed, 0 failed (was 69).

Fixes
1. LIVE shop with a matched gcal row and null/non-numeric remaining_minutes now raises gap `no_remaining_minutes` (live row unchanged).
2. Balance > allocation (negative used fraction) is excluded from idleNew and raises `no_allocation` naming both numbers. Not clamped. Strict `< 0.15` kept.
3. Tests added: Thai name ending in U+0E20, NBSP-padded Thai name, unknown_status, all 8 section keys, missing and null allocation, upsellExcluded reason, rule A at exactly 15% (200/170) and at 0%, top-up, no_remaining_minutes (null, non-numeric, non-LIVE), malformed closed period.
Cleanups: duplicate upsell comment removed; arayaQuietFinding returns null on any malformed closed period (commented, no new gap).

Mutation proofs
- Mut1 (no_remaining_minutes gap disabled): FAIL x2 ("null remaining minutes raises no_remaining_minutes", "non-numeric ..."), Passed 97 Failed 2, exit 1. Restored: green.
- Mut2 (negative-fraction guard disabled): FAIL x3 ("a topped-up shop is not reported idle", "... raises no_allocation", "the top-up gap mentions both numbers"), Passed 96 Failed 3, exit 1. Restored: green.
- Mut3a (arayaClean regex replaced by bare trim()): FAIL "an NBSP-padded Thai name is trimmed exactly", Passed 99 Failed 1, exit 1. Restored: green.
  NOTE: with only the plain Thai-name test, bare trim() did NOT fail: PHP's default trim() does not strip byte A0, so ร้านลาภ survives it. The NBSP-padded case was added so bare trim() is caught.
- Mut3b (byte-based trim with \xA0 in the charlist, the real corruption hazard): FAIL "a Thai name ending in ภ survives byte-for-byte" and "a padded Thai name is trimmed without corruption", Passed 97 Failed 2, exit 1. Restored: green.

Regression loop (arayaBrief + upgrade-downgrade tests): printed nothing.
