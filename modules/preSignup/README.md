# Pre Sign-up

Lets a sales rep open a lead **for** a customer, during a chat or a cold call,
and hand them a short link that opens the full signup form with everything the
rep already collected filled in.

```
lead arrives (Respond.io chat / cold call)
  -> rep fills in views/preSignupForm.php
  -> row in `presignup`, link prelead-0000001
  -> rep sends link to customer
  -> customer opens it -> models/resolve.php -> signup form, pre-filled
```

## Pages

| Page | Who uses it |
|---|---|
| `views/index.php` | Landing page, explains the flow |
| `views/preSignupForm.php` | The form a rep fills in |
| `views/entries.php` | Every lead opened, with copyable links and whether the customer has opened theirs |
| `views/thankyou.php` | Confirmation with the generated link (`?ref=prelead-0000001`) |

## Endpoints

| File | Does |
|---|---|
| `models/preSignupForm.php` | Validates and inserts the lead, returns `{ref, link}` |
| `models/resolve.php` | `?ref=prelead-0000001` → 302 to the signup form with query prefill |
| `models/entriesTable.php` | Feeds the DataTable on the entries page |
| `models/mondayOptions.php` | Dropdown labels, read live from Monday, cached on disk |

## Tests

```sh
node tests/test_prelead_prefill.js     # the signup form's prefill behaviour
php  tests/test_lookup_field_ids.php   # every prefilled id exists on that form
```

Neither needs a database or a web server.

## Setup

1. Create the table:

   ```sh
   mysql -u root db_localforyou < assets/db/schema.sql
   ```

2. Copy the config and fill in the Monday token:

   ```sh
   cp assets/config.example.php assets/config.php
   ```

   `assets/config.php` is gitignored — the token must not be committed.

3. Make sure `assets/cache/` is writable by the web server; Monday dropdown
   labels are cached there.

## The short link

`models/preSignupForm.php` returns a full URL built from `public_base_url`:

```
https://report.localforyou.com/prelead-0000001
```

For that to resolve, the web server needs to rewrite it to the module. This is
**not** configured yet — there is no root `.htaccess` in this repo, and adding
one affects every URL on the site, so it was left as a deliberate decision.
Add to the document root `.htaccess`:

```apache
RewriteEngine On
RewriteRule ^prelead-([0-9]+)/?$ /modules/preSignup/models/resolve.php?ref=prelead-$1 [L,QSA]
```

Until that rule exists, the link works in its long form:

```
/modules/preSignup/models/resolve.php?ref=prelead-0000001
```

The link is **reusable** — a customer who closes the tab half way through
signup can open it again. Each open is counted, so the entries page can show
"Not opened" against a lead the customer never followed up.

## One link format, whichever way the lead arrived

`resolve.php` redirects to the signup form carrying only the reference:

```
https://report.localforyou.com/modules/signup?prelead=0000001
```

The signup form then reads the values from `models/lookup.php`:

```
GET /modules/preSignup/models/lookup.php?ref=0000001

{
  "success": true,
  "ref": "prelead-0000001",
  "prefill": { "shopName": "…", "first_name": "…", "email": "…", … },
  "context": { "country": "AU", "shopType": "Massage", "leadSource": "…", … }
}
```

`prefill` is keyed by the signup form's own field names, so it can apply the
values without knowing anything about this module. Empty fields are left out
rather than sent as `""`, so the form keeps its own defaults. `context` is
what the sales team collected but the signup form does not fill in.

Keeping the values out of the link means the URL stays short, the customer's
details never appear in their address bar or browser history, and a field
added later changes neither the link nor its callers. A lead opened
automatically from a chat platform can use this same `?prelead=` shape.

Responses: `400 invalid_ref`, `404 not_found`, `410 cancelled`.

### How the signup form applies it

`modules/signup/assets/js/preleadPrefill.js` reads `?prelead=`, fetches
`lookup.php`, and fills the form in. It is loaded from `form_footer.php` after
`bootstrap-multi-step-form.js`, because that script is what reveals the first
step's fields and binds the handlers each prefilled value has to trigger.

It is deliberately defensive, since nothing about the signup form should depend
on it:

- No `?prelead=` — it makes no request and does nothing.
- Unknown, cancelled or failed lookup — the customer gets the ordinary empty
  form, with no error shown to them.
- A field the customer has already typed in is never overwritten, so a reload
  part-way through filling the form cannot clobber their work.
- A `<select>` is only set to a value it actually offers; a missing option
  would otherwise leave it silently blank.
- Each value is applied with `.trigger('change')` so the form's own handlers
  (currency, backup form, dependent sections) see it.

It adds a hidden `preleadRef` input so the submission can be tied back to this
row, and a short notice explaining why the form is already filled in — a
pre-filled form with no explanation looks like someone else's data.

**The prefill is keyed by element ids on `modules/signup/index.php`**, not by
this module's column names. An id that does not exist there fails silently, so
`tests/test_lookup_field_ids.php` checks every key against that file.

Three fields are left out on purpose:

| Field | Why |
|---|---|
| country | The signup form offers 6 countries; the Lead board knows 24 |
| state | Picking a state populates the city dropdown |
| city | `#city` is only a hidden mirror of `#citySelect` |

Writing state or city out of order desyncs that pair, so both are returned
under `context` and the notice names what the customer told us, letting them
re-pick it without having to remember.

## Matching a pre sign-up to its Lead item

A lead reaches the board two ways — automatically from a chat platform, and
manually through this module — so an item for the shop may already exist by the
time a rep submits. `models/mondayMatch.php` finds the likely items:

```
GET /modules/preSignup/models/mondayMatch.php?ref=0000001

{
  "success": true, "ref": "prelead-0000001", "linked": false,
  "candidates": [
    { "itemId": "2896765799", "name": "Horsham Thai massage",
      "opportunityId": "", "address": "22b Pynsent st, Horsham, VIC, 3400",
      "matchedOn": ["conversation_link", "phone", "email", "shop_name"],
      "url": "https://localforyou.monday.com/boards/…/pulses/2896765799" }
  ],
  "advice": "confirm_single_candidate"
}
```

It matches on the conversation link, phone, email and shop name, and sorts the
strongest evidence first. Phone numbers are compared digits-only from the end,
so `0401536261` and `+61401536261` count as the same number.

## Required fields

Required: shop name, country, shop type, customer type, lead source, contact
channel, **and at least one way to reach the shop** — a mobile number, a shop
number, an email, or a conversation link. Any one of those four will do.

Which one is deliberately not prescribed. A chat lead may arrive through
Respond.io, Facebook, TikTok or Line, and only some of those produce a link
worth storing; a cold call has a phone number and often nothing else. The
field is therefore `conversationLink`, not `respondIoLink`, it is always
visible rather than appearing only for Chat, and it is optional on its own.

Everything else — nickname, names, website, address — is optional, because a
rep taking details over a call cannot always get all of it.

**It never picks for you.** Shops with several branches share one phone
number, so a phone match can point at two real and different items; choosing
one would quietly file the lead against the wrong branch. Each candidate
carries its address, which is what tells the branches apart. `advice` is
`no_match_treat_as_new`, `confirm_single_candidate` or `ask_which_branch` — a
single candidate still wants a human nod.

Once an item is settled on, confirm it:

```
POST ref=0000001&itemId=2896765799

{ "success": true, "itemId": "2896765799",
  "opportunityId": "", "opportunityIdPending": true }
```

That stores `mondayItemId` and reads Monday's Opportunity ID from the text
mirror into `opportunityId`. Responses: `400 invalid_ref`,
`400 invalid_item_id`, `404 not_found`, `404 item_not_found`,
`422 item_on_other_board`, `502 monday_unavailable`.

Nothing calls this endpoint yet — it is not wired into the form or the entries
page, pending a decision on where the rep should see candidates.

### Duplicate items already exist on the board

Worth knowing before building on this: the live board already holds items that
are the same lead twice. `Horsham Thai massage` and `Contact 1793569283233`
share a conversation link, phone, email and address — the second is an
auto-created item that was never named. Any matching built on top of this
should expect to find more than one real item for one real shop.

## Country is not passed through

The Lead board offers 24 countries; the signup form offers 6. Rather than
guess at the mismatch, country is recorded here and shown on the entries page
but **not** sent to the signup form, so the customer selects it themselves.
Pending a decision with the team.

## Dropdown choices come from Monday

The form mirrors the label sets on **New – Lead Management V1** (board
`5031995646`) so a submitted value is always one Monday recognises:

| Form field | Monday column |
|---|---|
| Country | `numbers_mkn4adf8` |
| Shop Type | `status_mkn4hemw` |
| Customer Type | `color_mm7r5k6v` |
| Lead Source | `lead_source` |
| Contact Channel | `color_mm897hc7` |

Labels are cached for `options_cache_ttl` seconds (default 1 hour). If Monday is
unreachable the module serves a stale cache; with no cache at all the form falls
back to a minimal hardcoded set, so a rep can always finish a lead.

## Two things the spec asked for that are not here

**Opportunity ID.** The spec pairs the link with `Opportunity ID: 0000001`. On
the Lead board that column (`autonumber_mm89fqqv`) is an **auto_number**, and
Monday neither lets the API write it nor **read** it back: asking for it by id
returns an empty `column_values` list, and the column type's schema exposes
only display settings (scope, colour, font, order, prefix) with no value field.
Creating the item first does not help — there is no point at which the API can
see the number, so the link cannot be built from it.

The link reference is therefore our own, from the `presignup` table's id.

Monday's number reaches `presignup.opportunityId` through a **text column that
a Monday automation copies `Opportunity ID` into**, which the API *can* read.
That column now exists: **`text_mm8q2zp1` — "opportunityIDforPresignup"**, wired
up in `monday_item_columns`.

As of the last check it reads empty on every existing item, so the automation
has either not been set up yet or only fires for new items. The code handles
both: a confirmed match stores `mondayItemId` immediately and returns
`opportunityIdPending: true` when the mirror is still blank, so the link is
never lost waiting for a number. Backfilling old items is a Monday-side job.

**Dedupe against existing customers.** The spec asks that a matching
name/email/phone be linked to the existing customer. Not implemented: the rep
picks Customer Type on the form instead. Adding it needs a decision on which
table holds the customer record and how strict a match counts — matching all
four fields almost never fires, matching email alone fires too often.

## Monday writes

This module only **reads** from Monday. Submitting writes to the local
`presignup` table only; the board Lead column that links back to a pre-signup is
maintained separately.
