# Downgrade form conditionals — manual test script

The form's conditional sections are driven by `recomputeConditionalState()` in
`controllers/downgradeForm.js`. There is no JavaScript test runner in this
repo, so they are verified by hand. Run this whole script after any change to
that function or to the markup it toggles.

`udmClassifyProduct()` itself is covered automatically:

    php tests/test_product_classify.php

Expected: `Passed: 41  Failed: 0`

## Setting up a state

Only two rows in the mirror classify as `araya`, so a real Araya shop is
unlikely to be available. Force the state from the browser console instead,
after a lookup has rendered at least one product checkbox:

```javascript
// Tag the first rendered product however the test needs
const name = $('#productCheckboxList input').first().val();
udmProductTags[name] = ['araya'];            // or ['plan_bundle'], ['addon'],
                                             // or ['araya','plan_bundle']
$('#productCheckboxList input').first().prop('checked', true);
recomputeConditionalState();
```

## 1. Email lookup (the gate on the Task 3 refactor)

Every failure path must leave the form submittable by hand, with nothing the
customer typed cleared.

| Step | Expected |
| --- | --- |
| Pick a country, type a known single-shop email, blur | Business Name, Contact Person and Phone fill; status reads "Using **<shop>**" |
| Type a known multi-branch email | Branch picker appears, nothing filled until a branch is chosen |
| Type an unknown email | Neutral "couldn't prefill" message; **nothing you typed by hand is cleared** |
| Type a known email, then change it to an unknown one | `#mondayProjectId` and `#boardId` are empty in devtools |
| Type a shop name by hand with no lookup ever succeeding, then clear the email | Your typed shop name is **still there** |
| Click "Not my shop?" | Fields clear, status empties, focus lands on the email field (not `<body>`) |
| Devtools offline, trigger a lookup, restore the network, blur the same email again | A **second** request goes out (the 503 retry) |
| Change the country after a successful lookup | Result discarded, fresh lookup runs |
| `?mode=staff`: type a known email and blur | The lookup fills the fields **and** the project search still works |
| `?mode=staff`: search shop X, then type shop Y's email | Y wins, and the status line names Y |
| `?mode=staff`: email fills Y, then pick X from the project search | X's id submits **and** the status line no longer claims Y |

Also re-verify the **upgrade** form at `views/upgradeForm.php?mode=customer`
against this same list: Task 3 extracted the lookup out of it, and "the upgrade
form behaves identically" is the gate on that refactor.

## 2. Product list (items 2, 2a)

| Step | Expected |
| --- | --- |
| Choose "Unsubscribe specific product" before any lookup | The "Enter your email address above" prompt, not an empty box |
| Look up a shop with products | One checkbox per product, real names, prompt gone |
| Look up a shop that was **found but has no active products** | A message saying so, pointing at "Unsubscribe all services" — **not** the email prompt |
| Choose "Unsubscribe all services" | Section closes, every checkbox unchecked |
| Back to "Unsubscribe specific product" | Checkboxes clear, nothing remembered |
| Look up shop A, tick a product, look up shop B | B's products replace A's, nothing ticked |
| A product name containing `<`, `&` or `"` | Renders as text, no broken markup, and the tick still registers |

## 3. Araya conditionals (items 2b, 3, 3a, 4, 5)

Tag a product `['araya']` and check it.

| Step | Expected |
| --- | --- |
| Araya checked | Five extra reasons appear |
| Uncheck it | They disappear, **and** any that were ticked are unchecked |
| Araya + "Stop the service now" | The forfeited-minutes warning shows |
| Araya + "End of current billing cycle" | No warning |
| No Araya + "Stop the service now" | No warning |
| Araya checked, open the Information modal | The "AI Receptionist — Cancellation Conditions" card, five bullets, at the same text size as its sibling cards |
| No Araya, open the modal | That card absent |
| Araya checked | "What could we improve..." and the 1–5 rating both hidden |
| Rate 4 stars, check Araya, uncheck it | Rating group returns with **no stars lit** and `#overallRating` empty |
| Araya checked, submit | **Submits.** A hidden required field would block it with nothing to fix |

## 4. Plan bundle keep-list (item 2c)

Tag a product `['plan_bundle']` and check it.

| Step | Expected |
| --- | --- |
| Business Type = Restaurant | Pro Plan, Website Hosting |
| Business Type = Massage | Booking System, Website Hosting |
| Business Type = Other | All three |
| Change Business Type with the section open | Options change; anything still offered stays ticked |
| Uncheck the product | Section hides and `#keepProductsList` is empty in devtools |
| A product tagged `['addon']` | Section does **not** appear |
| A product named "Social Media Marketing Bundle" | Section does **not** appear |
| A product tagged `['araya','plan_bundle']` | **Both** the Araya conditionals and this keep-list appear |
| Anywhere in the section | No prices |

## 5. Both modes

| Step | Expected |
| --- | --- |
| `?mode=customer` field order | Country, Business Type, Business Name, Trading Name, Contact Person, Email, Phone, Address |
| `?mode=customer` | No "Contact Information" heading |
| `?mode=customer` | Policy section with the acknowledgement; no Submitted By |
| `?mode=customer` submit without ticking the policy | Blocked, with "Please confirm you understand the policy" |
| `?mode=staff` | Required Submitted By; no policy section, no contact-preference question, no star rating |
| `?mode=staff` submit with Submitted By empty | Blocked |
| `?mode=staff` submit a complete form | **Submits** |

## 6. Payload

With the Network tab filtered to `hook.us1.make.com`:

| Step | Expected |
| --- | --- |
| Two products ticked | `cancelledProducts` is a 2-element array; `cancelledProductsText` is them comma-joined |
| Any submission | **No `downgradeTarget` key** |
| Any submission | `changeType` equals the request type |
| A keep-list answer | `keepProducts` and `keepProductsText` carry it |
| Any submission | `businessType` carries the selection |

## 7. Post-submit Araya flow (item 7)

| Step | Expected |
| --- | --- |
| Submit with an Araya product, `araya_feedback_url` unset | Lands on `thankyou.php`, **and** the admin email is attempted |
| Set `araya_feedback_url` in `config.php`, submit with an Araya product | Redirected there with `shopName` and `email` in the query string |
| Same, with a `?` already in the configured URL | Parameters appended with `&`, not a second `?` |
| Submit with `testMode=true` and an Araya product | No email sent |
| Break the mail config, then submit | The row is still written and the customer still reaches the next page |

## 8. Lookup/classification failure paths

| Step | Expected |
| --- | --- |
| Block `classifyProducts.php` in devtools (500) | Checkboxes still render; **no** conditional section fires; the form still submits |
| Classification slow for shop A, then look up shop B | B's tags win; A's late response is discarded |
