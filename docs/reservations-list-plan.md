# Reservations list — tabs, filters, cancelled rows, restore

> Status: **CURRENT = the simplified toolbar of 2026-10-13 (next section)**. Earlier versions (2026-10-12: 7 tabs, contracts
> banner, Subscriptions tab / page) are kept below as history — superseded where they differ. Columns come later. Discussion: ~20k reservations, future ones months ahead,
> long subscriptions, repeat copies. Timeline per unit = later, a separate view (not in this plan).

## CURRENT toolbar (user, 2026-10-13) — supersedes the tabs / subscriptions sections below
1. **Tabs: Today | Upcoming | All** (Tomorrow, This week, Past, Subscriptions removed; the "Active subscriptions" banner removed).
   - Today = running today (overlap rule, decision 1 below), "starts today" first then the running ones; **count on Today only**
     (counted reservations, same filters).
   - Upcoming = `start_at >= tomorrow`, nearest first. All = everything, newest first (date range applies).
2. **Filters, all visible, one row under the tabs** (no "Filters" button):
   search (member name or phone) | status | space | **type** | payment | date from | date to | Clear.
   - **type** = subscription type (`subscription_type_id`: Day Use / Meeting / Subscriptions …). Choosing a contract type
     (`is_contract`) shows its rows on ANY tab, Today and Upcoming included.
   - Choosing a date range switches to All (as before); another tab clears the dates.
3. Kept: overlap rule for Today; contract periods hidden from Today / Upcoming unless the type filter selects them (any type
   chosen = that type only, so no hiding needed); cancelled-like rows visible but muted + last; Restore (+ the status menu check);
   Renew in the row menu of contract rows; long badge in the Period column.
4. Same for employees (shared `ReservationActions`).
- Code: `Reservation::TABS`, `scopeTab($tab, ['subscription_type_id'])`, `scopeTabOrder($tab)`; `ReservationActions::tabCounts()` (Today);
  view `Reservation/index` (`.filter-bar__tabs` = tabs on their own row); custom.js date range → All.
- Left from the earlier versions, unused now: `Reservation::SUBSCRIPTION_TABS`, `ENDED_DAYS`, `scopeSubscriptionTab`, `withRenewed`,
  `renewedSql()` (only used by the unreachable Subscriptions page files), `views/Reservation/partials/contracts-summary`,
  the "renewed" part of `partials/row-period` and the separate Subscriptions page files (no routes / menu) — KEPT for later
  (user, 2026-10-13: "leave it, we may use it later").

## CURRENT columns (user, 2026-10-13) — replace the old ones; same for employees; toolbar / tabs unchanged
| # | Column | Content |
|---|---|---|
| 1 | Member | name (opens the view), phone under it, the package name as a small line if any — a link to the package view (popup) (`partials/row-member`) |
| 2 | Space › Unit | two lines: "Meeting Room" / "P6" (no "›" in the rows), with the unit color dot (`partials/row-place`, `stacked`; one line elsewhere, e.g. the package view) |
| 3 | Plan | plan name |
| 4 | Period | by the subscription type (`partials/row-period`): **full_day** → one date + day `31-12-2026 Mon`; **auto_renew** or **is_contract** → two lines `From 04-10-2027` / `Ends 03-10-2028`; otherwise two lines: same day `31-12-2026 Mon` / `09:00 AM → 05:00 PM`, other days `31-12-2026 Mon \| 09:00 AM →` / `01-01-2027 Tue \| 05:00 PM` (day names added 2026-10-13; stored times shown as they are) |
| 5 | Status | two badges on two lines: the reservation status, then the payment status — Paid / Unpaid / Partial / In package / Nothing due / Credit ("Partial" is this list's short label of `partial`); a not counted row (Cancelled …): its status badge only, row muted; in a package the "In package" badge opens the package view (popup) (`partials/row-status`) |
| 6 | Amount | net amount, green + bold |
| 7 | Paid | paid amount |
| 8 | Remaining | > 0 red; < 0 (member credit) blue `77.00 credit`; 0 normal; inside a package `-` (`partials/row-remaining`) |
| 9 | Created By | the creator (employee / account name: `creatable.userable->displayName()`) |
| – | (menu) | row menu unchanged (View, Edit, Restore, Renew, Add Payment, Delete) |
- Column order (2026-10-13): Member | Space › Unit | Plan | Period | Amount | Paid | Remaining | Created By | Status (Status moved after Created By).
- Sortable: Period (start), Amount, Paid, Remaining; the tab order applies until a column is clicked (as before).
- Dropped with this change: the long badge ("running · day 12 of 90" / "ends …", decision 2 below) — the Period format replaces it;
  `long_badge` / `days_left` stay in the model (unused in the list).

## Packages list (user, 2026-10-13) — the same style; same for employees (shared `PackageActions`)
- **Tabs:** Current (default: `date_to` NULL — no end / no reservations yet — or `date_to >= today`) | Past (`date_to < today`) | All
  (`Package::TABS`, `scopeTab`). Default order: Period (start) newest first.
- **Filters, all visible:** search (package, member name or phone) | status (active) | payment | Clear. The "All Members" select was removed.
- **Columns (order updated 2026-10-13):** Package (name → view popup; member name small under it, always) | Period (`From 14-10-2024` / `Ends 03-12-2024`,
  no end = "Continues", none = "-") | Reservations, two lines: the counted ones (cancelled like excluded) / the booked hours of the TIMED ones in brackets
  `91` / `(124.5 h)`; full day / contract types count but add no hours; no hours = count only (`scopeWithReservationStats`:
  `counted_reservations`, `booked_minutes` subqueries, sortable) | Amount (net green bold; with a discount a small line `<price> · −<discount>%`;
  no Price / Discount columns) | Paid (black bold, `.amount-paid`) | Remaining (red > 0, blue "credit" < 0, normal 0, no badge) |
  Created By (creator) | Status (Active / Inactive, then the payment badge) — Status moved after Created By.
- **Row menu:** View, Edit, Add Payment, **Activate / Deactivate** (confirmation; replaces the switch in every row), Delete.
- Shared cells (PaymentModule): `Payment/partials/remaining-amount` (reservations + packages Remaining), `Payment/partials/payment-badge`
  (the payment badge, "Partial" short label) — the reservations list uses them too.

## Data facts (2026-10-12)
- 19,823 reservations; 19,569 are repeat copies (each its own row); 1,629 last 7 days or more; last start 2027-10-04.
- 6,110 old rows have `end_at` before `start_at` (same-day times, mostly plan "Full Day Use"); no `end_at` NULL.
- Statuses: 1 Pending + 3 Cancelled = not counted, 2 Confirmed = counted + default.
- Subscriptions (contracts) = subscription types with `is_contract = 1` (type 2 "Subscriptions", month / year plans): 1,431 confirmed.
  Renewals in the data: the next subscription of the same member is in the same space in 1,092 / 1,113 cases.
- `reservations` had no index on `start_at` / `end_at`; the date filters used `whereDate()` (no index use).

## Decisions (user, 2026-10-12)
1. Tabs **Today / Tomorrow / This week** use the OVERLAP rule (running in the period), not "starts in the period".
   `end_at` before `start_at` = ends at `start_at`. Rule for a period [from, to):
   `start_at < to AND (end_at > from OR start_at >= from)` (a zero-length / end-before-start row counts on its start day).
2. Long reservations (more than one day): a badge in the Period column — "running · day 12 of 90" while running,
   "ends 30-11" otherwise; Today sorts "starts today" first, then the running ones.
3. Repeat copies: as they are (one row each).
4. Indexes `(account_id, start_at)` and `(account_id, end_at)`; all date filters use range comparisons.
5. Choosing a date range (from / to) switches to the **All** tab (the range applies alone).
6. Counts only on the Today and Tomorrow tabs.
7. Not counted reservations (Cancelled, Pending, …) stay VISIBLE on every tab:
   - their status badge + a muted row (`DT_RowClass`), sorted AFTER the counted ones (always the first order key),
   - left out of the tab counts,
   - **Restore** action (row menu + view): back to the account's default counted status (Confirmed); first the same
     availability check as a new booking (`ReservationService::availabilityError()`, the reservation itself ignored) —
     refused with the reservations holding the slot (links); then the package recalculation + `PaymentService::refresh()`
     (amount due comes back) — same as `changeStatus()`.
   - The status menu has the same gap (not counted → counted without a check): `changeStatus()` runs the same check.
8. **This week** = the next 7 days (today + 6).
9. The list always opens on Today (the tab is not remembered).
10. Same tabs for employees (shared `ReservationActions`).

## Tabs (2026-10-12 — SUPERSEDED by the current toolbar above)
| Tab | Rows | Default order |
|---|---|---|
| Today (default) | overlap [today, tomorrow) | starts today first (start time), then running (start time) |
| Tomorrow | overlap [tomorrow, +2 d) | starts tomorrow first, then running (like Today; built 2026-10-12: running subscriptions buried the bookings) |
| This week | overlap [today, +7 d) | starts this week first, then running (same reason) |
| Upcoming | `start_at >= tomorrow` | nearest first |
| Past | ended: `GREATEST(end_at, start_at) < now` | newest first |
| All | everything (date range applies) | newest first (as before) |
- Before every order: counted first (not counted after). A column click replaces the tab order (not the counted-first key).
- Tabs = radio buttons inside the filters form (`datatables.js` reloads on change); `data-order='[]'` so the server
  applies the tab order until a column is clicked. Counts come with the DataTables JSON (`counts`) → `[data-counter]`.
- Existing filters (search, status, space, payment status) combine with the tab.

## Subscriptions (contracts) — 2026-10-12, SUPERSEDED 2026-10-13 except points 4–5 (the flag, one row per period)
Storage stays as is: a monthly contract = one reservation per renewal period (one row per month, own amount due + payments).
Only the display changes:
1. **Day Use / Meeting** (not contracts): individual rows on Today / Tomorrow / This week / Upcoming / Past (as planned).
2. **Contracts on the daily tabs** (Today / Tomorrow / This week): NOT listed row by row. One collapsed summary above the
   rows: "Active subscriptions (N)" (counted contract periods running in the tab's period, same search / space filters),
   expandable to the list: member, unit, period, days left, paid / remaining (link to the reservation view).
   Upcoming / Past: contract rows are left out too (their future / old periods are on the Subscriptions tab); **All** keeps every row.
3. **"Subscriptions" tab** in the same list (same columns), with a filter (shown on that tab only):
   Active now (default) · Ending within 7 days · Ending within 30 days · Ended and not renewed.
   - Not renewed = no next period exists: no later counted contract reservation of the same member in the same space.
   - Ending tabs = not renewed only; Active now = all, with a Renewed / Not renewed badge; ended = the last 60 days or the date range (on the end).
   - Period column: the long badge + days left + Renewed / Not renewed; row menu: Renew (form pre-filled, next period).
4. **Flag `subscription_types.is_contract`** (0/1) drives it — not the name / id / auto_renew. A checkbox on the subscription
   types form next to can_repeat / auto_renew / full_day (`SubscriptionType::OPTIONS`). SQL: add the column, 1 for "Subscriptions".
5. Each period row keeps its own amount due and payments (unchanged).
- The tab counts (Today / Tomorrow) count the listed rows only (counted, not contracts).

## Build order
1. `Reservation` scopes: `overlapping period` rule, tabs, counted-first; `scopeFilter` dates as ranges (no `whereDate`)
2. List tabs (radios, counts, per-tab order, date range → All), long badge, muted not-counted rows
3. Restore (route `{area}.reservations.restore`, service + availability check, row menu + view) + check in `changeStatus()`
4. ~~Subscriptions page~~ (built, then replaced 2026-10-12 by the tab: routes / sidebar items removed, its files to delete with the user's OK)
5. Indexes: reservations create migration + SQL for the user (DB change)
6. `is_contract` flag (migration, model OPTIONS, SQL) — DB change
7. Daily tabs without contract rows + "Active subscriptions (N)" summary (in the DataTables JSON → above the table);
   Upcoming / Past without contracts; Subscriptions tab + its filter; Renew in the row menu of contract rows
8. Docs (`progress.md`, CLAUDE.md if conventions change)
9. 2026-10-13: simplified toolbar (Today | Upcoming | All, one filter row + type filter); banner, Subscriptions tab, `slots` removed
