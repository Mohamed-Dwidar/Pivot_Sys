# Project Progress

> Loaded into every session via CLAUDE.md — keep it short and current.
> Edit module sections in place. Session log: last 10 entries only (git has the rest).

---

## Current status
- **Settings › General + My Shift list (2026-10-13), uncommitted, tested (HTTP kernel; OFF via the in-memory setting), not in a browser:**
  - My Shift payments = the Payments page list (`Payment/partials/list-table`, `?shift=open`), no Created By; Add Payment / Daily Summary buttons removed from My Shift. My Closings: Payments column = the payments' dates + count, Withholding column hidden while withholding tax is off; the shift popup = totals + the payments in the Payments list cells (newest 200, `popupPayments()`), no History (`Shift/partials/change` deleted).
  - Settings › General (account only): Withholding tax (OFF hides new withholding everywhere, existing records stay visible) and VAT (stored; Phase 2 rule in `payments-plan.md` §3).
  - DB done by the user (2026-10-13): `accounts.withholding_enabled` / `vat_enabled` (default 1; create migration has them).
- **Payments list simplified + filter order on all three lists (2026-10-13), committed `fed9ef1`, tested (HTTP kernel, real data), not checked in a browser:** spec `docs/payments-plan.md` "Payments list".
  - Payments: tabs Today | This Month | All (account; employees: no tabs, see below); one filter row search | date | For (Reservations, Packages, "Other income" group: all + each category) | Method (+ Refunds, Withholding; Type filter removed) | Clear; summary line "Total — per method · Refunds · Withholding" (withholding not in the total); columns Member (no.) | For (link, unit color) | Date (time) | Method | Amount (refund red minus, withholding badge) | Created By (account only).
  - Reservations: search | date | status | space | type | payment | Clear. Packages: search | date (period overlaps) | status | payment | Clear.
  - ONE date field on the three lists (`layoutmodule::partials.date-range-filter`, flatpickr 4.6.13 range, MIT, Arabic locale; shared init in custom.js), synced with the tabs both ways (`partials.filter-tabs` + `Model::tabDates()`): Today / This Month / All = their days; Upcoming "From <tomorrow>", Current "From <today>", Past "Until <yesterday>"; other days = no tab highlighted (reservations: the Today overlap rule for the picked days; packages: period overlaps; payments: paid on them). Employees' payments list: NO tabs, one day only, today by default, × / Clear = today (server: no date / range → today / first day). Clear count: the date field = one filter. flatpickr served locally (`public/assets/vendor/flatpickr/`). Tested in headless Chrome (test page with the real custom.js code) + HTTP kernel.
- **Packages (2026-10-13), committed `29c020b`, built + tested (HTTP kernel / rendered PDFs), not checked in a browser:**
  - List in the reservations list style: tabs Current | Past | All; filters search / status / payment; columns Package | Period | Reservations `count` / `(h)` | Amount | Paid (black bold) | Remaining | Created By | Status; Activate / Deactivate in the row menu.
  - "Export PDF" package statement (view header, account + employee): A4, summary + reservations (all, zebra, cancelled red with 0 h / 0.00) + extras + payments, page X of Y.
  - Packages without a period: SQL given (from = created_at, end = OLD system updated_at; 8 packages) + `to_run` STEP 4b — WAITING for the user to run it. Offered: make `recalculate()` keep these dates (no answer yet).
- **Working on (2026-10-12/13): reservations list** — spec `docs/reservations-list-plan.md` (CURRENT toolbar section, 2026-10-13). Committed `6eff649` / `29c020b`.
  - Built + tested (HTTP kernel, real data): tabs **Today | Upcoming | All** (Today = overlap rule, count on Today only); one filter row (2026-10-13 order): search | date (one field) | status | space | **type** (`subscription_type_id`) | payment | Clear; contract periods hidden from Today / Upcoming unless the type filter is chosen; muted cancelled-like rows last; Restore + status menu check; Renew on contract rows; same for employees. DB done by the user: `subscription_types.is_contract` (1 = Subscriptions) + reservations indexes.
  - Question open: the 243 "continues" rows with an end date treated as ending at it?
  - Kept on purpose, unused (user 2026-10-13: "may use it later"): the Subscriptions page files (`Concerns/SubscriptionActions`, `Account|Employee/Subscription*ModuleController`, `views/Subscription/index`, no routes / menu), `partials/contracts-summary`, the model subscription scopes (`SUBSCRIPTION_TABS`, `scopeSubscriptionTab`, `withRenewed`, `renewedSql()`).
  - Columns (2026-10-13, built + tested on real rows, account + employee; NOT checked in a browser yet): Member (phone, package line = link to the package popup) | Space / Unit on two lines (color dot, no "›") | Plan | Period by type (full day = `31-12-2026 Mon`; auto renew / contract = From / Ends; else two lines: date + day name / times, or `date day | time →` / `date day | time`) | Amount (green bold) | Paid | Remaining (red > 0, blue "credit" < 0, "-" in a package) | Created By (creator) | Status (reservation + payment badges; "In package" badge = link to the package popup; cancelled = status only, muted); long badge dropped.
- **Closing simplified to ONE level, the shift (2026-10-13), uncommitted, built + tested (rolled back transactions + HTTP kernel, real data), not checked in a browser:** spec `docs/payments-plan.md` §2b (rewritten).
  - Every login closes its own shift (employees AND the account: My Shift); no day / month lock, no reopen; the account edits / deletes a closed-shift payment with a reason (totals recalculated, logged before / after, "Edited after closing" badge); refunds by employees and the account (reason, ≤ the payment less its refunds, into the refunder's open shift; a closed original only gets the log + badge); refunds = their own minus line in the close popup; Daily Summary (read only) replaces Day Closing; reminders 12 h / 4 h in config, Shift Reminders page removed.
  - DB done by the user (2026-10-13, backup first): dropped `day_closings`, `day_closing_logs`, `shift_closings.status / reopened_*`, `accounts.shift_reminder_hours / shift_snooze_hours`; added `shift_closings.edited_at / edit_count`, `shift_events.payment_id / changes` + new actions, `payments.refund_of / reason`. Create migrations match the DB; the two day migrations deleted. 52 imported refunds stay unlinked (user).
  - DB done by the user: `to_run` **STEP 3.15b** ran (the account's 1,848 imported payments = one closed "Imported" shift, 1,346,429.92, closed 04-10-2026 23:50:30; every imported shift closed at its last payment); check 6.11 = all 0 (verified 2026-10-13).
  - Before (2026-10-11, committed `f17f7f9`): two levels (shift + account day closing) — replaced; `to_run` 3.15 (14 employee closings) ran, 3.16 never ran.
- **Next up:** (0) user runs the packages period SQL (STEP 4b) + checks the payments / reservations / packages lists and the statement PDF in the browser; (1) user commits the closing / settings work; (2) user tests in the browser (employee: My Shift + its list, My Closings, close / logout / reminder popups, refund; account: My Shift, Daily Summary, close on behalf, edit / delete / refund in a closed shift + the badge, the shift popup; Settings › General both ways); update the "Closing Day Cycle — User Guide" doc if the screens change; (3) then the 6.9 decision and Phase 2 (InvoiceModule).
- **Before:** data transfer `dwidar_pivot_api` (old, live, READ ONLY) → `dwidar_res_portal` — **RAN 2026-10-11** (`to_run` steps 1–6; backup `storage/app/backups/dwidar_res_portal-20261011-1742.sql`, 6.1 MB). All checks match except 6.9 (17 packages differ from the old stored values — waiting for the user: accept the new values or not). Phase 1 code done (steps 5–10 + extras committed by the user in `ac1c79f`; `is_revenue` + docs uncommitted — the user commits, suggest messages only).
- **Transfer result:** members 2,932, packages 220, reservations 19,823 (17,756 Confirmed / 2,067 Cancelled), extras 179 (extra 123 skipped: its package was deleted), payments 13,054 (7,707,148.91) + incomes 736 (76,550.94), methods 5, categories 9 (6 VAT inactive + not revenue: 12 incomes 32,564.44; 101 Buffet, 102 Break Hours, 103 Extra Hours), employees 15 + logins (active 3, 7, 10, 12, 13, 14, 15, 16; others leave_date), account login 1 untouched; next payment id / No. 100001 (imported max 14,220).
- **`to_run` is re-runnable** for go-live day (after stopping the old system): 3.0 pre-check (74 checks on every source row, BLOCKING stops before any TRUNCATE, fallbacks listed); employees / logins upserted; ids from the source (income offset = max old payment id). User decisions: reservations of deleted members imported without a member (9), of deleted packages → no package + Cancelled (20), extras of deleted packages skipped (1), old 8330 (−43.40 withholding) → refund "withholding correction".
- **Check 6.9 (packages vs old):** 215 / 220 same net, 203 / 220 same remaining (−5,127.10 in total). 12 = old stored remaining was stale (ours = old formula net + extras − paid); 5 (2, 3, 98, 139, 151) = old net not recalculated after reservations were cancelled (+ package 3: payment 35 moved in). Recommended: accept the new values.
- **Waiting for the user:** (packages) run STEP 4b SQL; `recalculate()` fallback for packages without reservations?; (reservations) the "continues" question above; (0) check 6.9 — accept the 17 new package values (recommended) or not; Cairo font files (`public/assets/fonts/Cairo/Cairo-{Regular,Bold,Light}.ttf`, static TTF); (1) migrations rule vs practice (edit `create_*` + manual DB, see CLAUDE.md Conventions); (2) are Vite / Tailwind 4 in package.json used or removable; (3) accountant: rounding strategy + T4 on invoices (don't block Phase 1).
- **Known issues:**
  - DB columns added by hand must exist: `reservation_statuses.color`, `is_counted`, `is_default`, `sort_order`; `plans.is_time_based`; `subscription_types.can_repeat`, `auto_renew`; `reservations.paid_amount`, `remaining`; `packages.paid_amount`; money columns of reservations / packages = decimal(12,2) (all run 2026-10-11).
  - After any change of a status's `is_counted` or a bulk data change: run the package recalculation + `php artisan payments:refresh` (`to_run` steps 4–5).
  - App payments use ids / `payment_no` from 100001 (config `paymentmodule.number_start`, `PAYMENT_NUMBER_START`; `nextNumber()` floor; AUTO_INCREMENT set by `to_run` 3.14); imported old payments / incomes stay below (max 14,220 now).
  - 9 reservations (June 2024) have no member (deleted in the old system): views / lists / receipt show "-" (checked); a NEW payment for them can't be added (the form needs an existing member).
  - Old 4 payments of 0.00, 6,110 old reservations end before they start (same-day times), 106 repeat copies without their original — imported as they are (pre-check warnings).
  - Old users 12, 13, 15 had plain-text passwords (`000` / `0000`): imported as the same password hashed — weak, ask them to change it. Inactive users (2, 4, 5, 6, 8, 9, 11) get `leave_date` = last login → login refused.
  - Cairo fonts of `config/pdf.php` (`public/assets/fonts/Cairo/`) are missing: the receipt falls back to DejaVu Sans (Arabic OK).
  - Packages without counted reservations (8 imported): period set by `to_run` STEP 4b (from = created_at, end = the old system's updated_at, user 2026-10-13); `Package::recalculate()` empties it again (STEP 4 → always run 4b after it)
  - Changing a status's `is_counted` doesn't recalculate packages or refresh reservations (old gap) → run `payments:refresh` / package recalculation after such a change.
  - Table `creatable_backup` (old creator values of 220 packages + 2,887 members) — keep until the user confirms, then drop.
  - Lucide here has no `lock-open` icon (use `unlock`).
  - Testing note: rolled back test inserts still move AUTO_INCREMENT (shift_closings / shift_events / payments ids may have gaps — cosmetic).
  - Shift totals saved before 2026-10-13 have no `refunds` key (views fall back to the per-method refunds).
  - Legacy, unused: `AdminModuleController`, `Admin/UserModuleController`, AdminModule `admin/*` + UserModule `change_password` / `forgotPassword` views, nwidart `components/layouts/master` stubs.

---

## Modules

### UserModule / AccountModule / EmployeeModule (auth & user types)
- **Last updated:** 2026-10-11 (data transfer)
- **Files:** `UserModule/app/{Contracts/Userable, Models/User, Http/Middleware/EnsureUserIsActive}`, `AccountModule/app/Models/Account` (+ `settingsOf()`, `withholdingEnabled()`, `vatEnabled()`), `AccountModule/app/Http/{Controllers/Account/GeneralSettingAccountModuleController, Requests/GeneralSettingRequest}` + `views/Setting/general` (Settings › General, routes `account.settings.general.{edit, update}`), `EmployeeModule/app/Models/Employee`, `UserModule/resources/views/user/account.blade.php`
- **How it works:** one login (`/login`) for all `users`; the `userable` model decides access (`loginError`), home, layout. Accounts register → pending → admin approves. Employees belong to an account, log in, change password only. Middleware `user.active:{Model}`.
- **Depends on:** LayoutModule (layouts per type)
- **Decisions:**
  - Employees' email managed by the account (`canChangeEmail()`) — account owns the profile
  - Employee attachments on the private disk, downloaded via the account — personal documents
  - Deleting an employee removes their login — email reusable
  - Old system users = employees (user decision 2026-10-11): employee id = user id = old user id (2–16), same email + old bcrypt password; inactive ones have `leave_date` (login refused by `Employee::loginError()`); the 2 test employees were dropped; account login 1 untouched
- **Pending:** positions / shifts of the imported employees (only 3 = Accountant set); 12, 13, 15 should change their weak passwords

### LayoutModule (layouts, shared UI, JS/CSS)
- **Last updated:** 2026-10-13 (list toolbars: filter-tabs, one date field, local flatpickr)
- **Files:** `views/{main,modal,account/*,admin/*,employee/*}`, `views/partials/*`, `config/config.php`, `public/assets/{css/custom.css, css/pdf.css, js/custom.js, js/datatables.js}`
  - `pdf.css`: PDF views only (mpdf, CSS 2, linked by `public_path()`), e.g. the payment receipt
  - List toolbars (2026-10-13): `partials/filter-tabs` (hidden `tab` + tab buttons, `data-date-select` / `data-date-label` from `Model::tabDates()`, optional counts) + `partials/date-range-filter` (ONE date field, flatpickr 4.6.13 MIT, local copy `public/assets/vendor/flatpickr/` (+ LICENSE) + `l10n/ar`, pushed `@once`; hidden `date_from` / `date_to`; `single` = one day only; `default` = starts on / goes back to that day, never empty; custom.js `dateRange*` syncs it with the tabs) — reservations, packages, payments
  - Shell: `main` (page frame, `@yield('header'|'sidebar'|'title'|'actions'|'content')`, `@stack('styles'|'scripts')`), `header` (top bar + brand), `sidebar` (frame, `@include($menu)`), `partials/menu` (renders `$items`), `flash`, `footer`, `login` (guest shell for the login / register pages)
  - Per area: `{admin,account,employee}/main` (extends `main`, fills header vars + picks `{area}/sidebar`), `{area}/sidebar` (the `$items` array), `{area}/dashboard`
  - Account sidebar groups: Reservations, Payments (All Payments, Withholding Certificates, My Shift, Daily Summary — 2026-10-13, replaced Day Closing), Employees, Spaces, Settings (General — withholding tax / VAT switches, Payment Methods, Income Categories; Shift Reminders removed 2026-10-13); Withholding Certificates hidden while withholding tax is off — user approved; employee sidebar: Reservations, Packages, Payments, My Shift (was Day Closing)
  - Shift popups (2026-10-11): `header` optional `$logoutPrompt` (`data-logout-prompt` on the logout form); `employee/main` passes it when `$shiftOpen` and pushes `[data-shift-reminder]` when `$shiftReminderUrl` (both shared by PaymentModule `ShiftReminder` middleware)
  - Brand: `public/assets/images/{logo.png, favicon.png|jpg}`
- **How it works:** popup CRUD (`data-modal`), ajax forms (`data-ajax`, SweetAlert confirm, 422 errors under fields, `html_` errors rendered as HTML), DataTables row update/remove, row toggles, dependent selects (`data-options-url`, `data-parent`, `data-also`), `data-show-if` (checkbox name, or `field=value other` for a select; `field=` = empty) / `data-disable-if`, Tom Select (`searchable`), `Str::humanize()` option texts.
  - Page → `@extends('layoutmodule::{area}.main')` → `main`. The area layout passes `header` its variables (`$homeUrl`, `$userName`, `$userSubtitle`, `$menuLinks`, `$logoutUrl`) and gives `sidebar` its `$menu` view. Menu items: `divider` / link (`title, icon, url, active, badge, counter`) / `children` drop list.
  - Sidebar: open by default, the arrow next to the logo collapses it to icons and remembers the choice (`localStorage.compactMenu`). Collapsed = `favicon` icon, open = `logo.png` (`.brand-logo` / `.brand-icon` in custom.css). Below 1280px: ☰ opens it as an overlay.
  - `custom.js` loads BEFORE `themes/dagger.js`: it sets the default (open), blocks dagger's hover-to-expand, and replaces dagger's resize handler (it forced compact below 1600px).
  - custom.js shift handling: logout form `data-logout-prompt` → popup instead of logging out; `[data-shift-reminder]` → popup on page load; ajax answer `{logout: true}` → `logoutNow()`; response header `X-Shift-Reminder` → popup after the action
  - Script order: template vendors (`dom.js` = `window.$`, popper, dropdown, transition, simplebar, modal, tom-select, lucide) → `custom.js` → `dagger.js` → SweetAlert2 CDN → `@stack('scripts')`.
- **Depends on:** template (`Template_Source`), CDN DataTables / SweetAlert, local flatpickr (`public/assets/vendor/flatpickr/`); area routes (`admin.dashboard`, `admin.logout`, `account.dashboard`, `employee.dashboard`, …) used by the area layouts / sidebars
- **Decisions:**
  - Theme `theme-6`, no gradients on buttons/links — user request
  - Assets loaded with `?v=filemtime` — popup content reuses the page's JS/CSS, new rules need one page reload
  - Only template-compiled Tailwind classes work (e.g. no `justify-between`) — use custom.css classes
  - Template = Tailwise "dagger" (replaced the old Bootstrap / LightAble layout, its CSS / JS and commented blocks removed) — user choice
  - No inline CSS / `<style>` in any view; custom CSS only in `custom.css` — user rule
  - Content area full width (no `.container` max-width) — show the most data
  - Sidebar open by default + working collapse toggle, no hover-expand — user found hover-expand looked like "not collapsing"
  - Template overrides go in `custom.js`, template files (`themes/dagger.js`, `app.css`) are not edited — keep the template replaceable
  - jQuery not global: it would overwrite the template's `window.$` (`dom.js`) that dropdown / menu scripts use → only for DataTables with `noConflict`
  - No avatar images: header avatar = first letter of `$userName` (template `images/` demo files not copied, 122 MB)
- **Pending:** RTL / Arabic UI; favicon file differs (`main` + header icon use `favicon.jpg`, `login` uses `favicon.png`); root `views/dashboard.blade.php` looks unused (no references found)

### SpaceModule / UnitModule / PlanModule (places & prices)
- **Last updated:** 2026-10-13
- **Files:** `SpaceModule/app/{Models/Space,SubscriptionType}` (flags in `SubscriptionType::OPTIONS` drive the form checkboxes, request, service, list badges), `UnitModule/app/{Models/Unit,Color, Services/UnitService}`, `PlanModule/app/{Models/Plan, Services/PlanService}`
- **How it works:** spaces have subscription types and plans; units belong to a space with one subscription type, capacity, concurrent usage, color, plans (from the space). Plans: amount, lease period, `is_time_based`.
- **Depends on:** —
- **Decisions:**
  - Subscription type flags `can_repeat`, `auto_renew` drive the reservation form options
  - `is_contract` (2026-10-12, column added by hand — create migration edited): a contract = monthly subscription stored as one reservation per period; drives the reservations list display (not the name / id / auto_renew)
  - Time based plan amount = amount × periods (`PERIOD_HOURS`, exact, month = 30 d)
- **Pending:** —

### ReservationModule
- **Last updated:** 2026-10-13 (list tabs, Restore, contracts, one date field = Today rule — spec `docs/reservations-list-plan.md`)
- **Files:** `app/Http/Controllers/Concerns/ReservationActions.php`, `Account|Employee/Reservation*ModuleController`, `Account/ReservationStatusAccountModuleController`, `app/Services/{ReservationService,ReservationStatusService}`, `app/Models/{Reservation,ReservationStatus}`, `app/Http/Requests/ReservationRequest.php`, `views/Reservation/*` (+ `restore`, `partials/row-period`; `partials/contracts-summary` unused), `views/Status/*`, `views/Subscription/partials/days`; kept unused for later (user 2026-10-13, no routes / menu): `Concerns/SubscriptionActions`, `Account|Employee/Subscription*ModuleController`, `views/Subscription/index`
  - routes + `{area}.reservations.{restore, restore.store}`; `create?renew=ID`
- **How it works:** account + employees manage reservations. Form chain: subscription type → spaces → units (space + type) → plans (ajax). Package locks member. Status not in the form (new = default status; changed from the view header menu by ajax). Repeat on create only (frequency, every, until; copies with `repeat_id`). Availability check: overlapping counted reservations vs unit `concurrent_usage` (busiest moment), repeats included; error links to the clashing reservations (`periodsError()`, shared by `availabilityError()` / `restoreError()`).
  - List toolbar (2026-10-13): tabs Today (default, `scopeRunningBetween` overlap `start_at < to AND (end_at > from OR start_at >= from)`, starting today first) | Upcoming (`start_at >= tomorrow`, nearest first) | All (`Reservation::TABS`, hidden `tab`, `[data-filter-tab]`, `scopeTab` / `scopeTabOrder`); one filter row: search, date (one field synced with the tabs, `partials.date-range-filter`), status, space, type (`subscription_type_id`), payment, Clear (same order on every list) (`.filter-bar__tabs` puts the tabs on their own row). `data-order='[]'` → tab order until a column click; `countedFirst()` always first; Today count (counted) via json `counts`; picked days (`date_from` / `date_to`) = internal tab `days` (`ReservationActions::tab()`): the Today rule for those days (overlap, contracts hidden unless the type filter, starting in them first; `Reservation::pickedDays()`); ranges, no `whereDate`
  - Contracts (`is_contract` types, `contractTypeIds()`, `scopeContracts` / `notContracts`): hidden from Today / Upcoming unless a type is chosen; Renew = `renewalOf()` (next period, same length, form pre-filled, repeat allowed); unused leftovers of the 2026-10-12 versions (subscription scopes, `contracts-summary`, the Subscriptions page) are kept for later (user)
  - List columns (2026-10-13): partials `row-member` (package line → `{area}.packages.show` popup), `row-place` (`stacked` = two lines, list only; one line in the package view), `row-period` (by type: `full_day` / `auto_renew` / `is_contract` / else; day names `D`), `row-status` (two badges; "In package" → package popup; "Partial" short label here only), `row-remaining` (red / blue credit / "-" in a package); `by` = `creatable.userable` (eager loaded in `table()`); `long_badge` / `days_left` kept in the model, not shown; `realEnd()` (end before start = start); not counted rows `DT_RowClass row-muted`
  - Restore (`restoreForm` popup checks the slot first, `ReservationService::restore()` → default counted status via `changeStatus()`); `changeStatus()` refuses not counted → counted when the slot is taken (`html_status`, shown in a Swal popup)
- **Depends on:** Space/Unit/Plan, PackageModule (recalculate), MemberModule
- **Decisions:**
  - Status colors = fixed theme palette (`badge-status-{color}`) — no inline CSS
  - A reservation with payments can't be deleted: refund / delete them or move it into a package first (user, 2026-10-11)
  - `is_counted` status flag (off = cancelled-like) excluded from package totals, hours, availability
  - One default + active status per account; default can't be switched off / deleted
  - Server recalculates amounts, period, subscription day (`ReservationService::period()`)
  - `Reservation` implements `Payable` (payable only when `package_id = 0`; due = net, 0 with a not counted status — user 2026-10-11), uses `HasPayments` + `SafeMorphHelper`; columns `paid_amount`, `remaining`
  - `ReservationService` create (+ repeat copies) / update / changeStatus → `PaymentService::refresh()`; update into a package → `movePayments()`
  - Imported creators: all reservations → User 1 (fixed by hand, old class names); `SafeMorphHelper` makes unknown / empty morph types load as null
  - Contracts = one reservation per period, each with its own due + payments (user 2026-10-12); display only collapses them
  - "Continues" rows: the end date is used everywhere (tabs, subscriptions, availability) — 243 imported rows have both (question open)
- **Pending:** browser check of the list; the "continues" question in Current status; repeat for edits not supported by design

### PackageModule
- **Last updated:** 2026-10-13 (list in the reservations list style)
- **Files:** `app/Http/Controllers/Concerns/PackageActions.php`, `app/Models/Package.php`, `app/Services/PackageService.php`, `views/Package/*` (view includes `paymentmodule::Extra.partials.package-extras` + `Payment.partials.payable-payments`; list partials `row-{package, period, reservations, status, amount}`, `active-form`)
- **How it works:** a member's package; price (`amount`) = sum of its counted reservations' net, discount % → net (`after_discount`), `date_from/to` from the reservations, total hours shown in the view. Recalculated on every reservation change (`recalculate()`).
  - Statement PDF (2026-10-13): "Export PDF" in the package view (account + employee, new tab) → `{area}.packages.statement` (`PackageActions::statement()`, data `PackageService::statement()`), view `Package/statement-pdf` (mpdf A4, styles in `pdf.css`, DejaVu Sans fallback while the Cairo files are missing): "Package Statement / كشف حساب باقة" (NOT an invoice); header (logo, account, statement date, package), member (+ company), summary (period, reservations count + timed hours, total before discount, discount %, net, extras, balance carried over, due / paid / remaining or credit), reservations (ALL by date, even rows shaded; cancelled like ones on red with "· Cancelled", 0 hours / 0.00, not in the totals; summary "+ N cancelled"; hours of timed counted ones only; totals row = counted), extras (type extra; adjustments = one "Balance carried over" line; hidden if none), payments (receipt no., date, method; refunds minus; withholding = "Withholding tax certificate"; no collector); `<thead>` repeats on every page, footer "Page X of Y" + printed date; Arabic: `autoScriptToLang` / `autoLangToFont` + `dir="rtl"` on texts that START with Arabic (mixed names keep their order)
  - List (2026-10-13, spec `docs/reservations-list-plan.md` "Packages list"): tabs Current (default) | Past | All (`Package::TABS`, `scopeTab`); filters search / date (one field: period overlaps; Current "From today", Past "Until yesterday") / status / payment / Clear (no member select); columns Package (+ member) | Period From / Ends | Reservations `count` / `(h)` two lines (`scopeWithReservationStats`: counted, timed hours only) | Amount (net + `price · −x%`) | Paid (black bold `.amount-paid`) | Remaining (shared `remaining-amount`) | Created By | Status (Active / Inactive + payment badge); Activate / Deactivate in the row menu (confirmation, `toggleActive`)
- **Depends on:** ReservationModule, MemberModule
- **Decisions:**
  - A package with reservations or payments can't be deleted (user, 2026-10-11)
  - Discount typed as % or net, stored as % — price changes keep the %
  - `Package` implements `Payable`: due = `after_discount` + Σ extras (`HasExtras`, the old system's rule: remaining = net + extras − paid); uses `HasPayments` + `SafeMorphHelper`; column `paid_amount`
  - `recalculate()` ends with `PaymentService::refresh($this)` (paid / remaining from the payments; it no longer sets `remaining` itself) — Payments step 4
- **Pending:** 17 packages differ from the old stored values after the recalculation (check 6.9, stale old values) — user decision

### Payments / Invoices / ETA (PaymentModule — phase 1 in progress; InvoiceModule, ReportModule — not built)
- **Last updated:** 2026-10-13 (payments list simplified; ONE closing level: the shift)
- **Spec: `docs/payments-plan.md` — read before any payments/invoice work**
- **Status:** shift closing = the ONE closing level since 2026-10-13 (§2b; two levels 2026-10-11 → 10-13, `f17f7f9`), uncommitted; Phase 1 done (`9dd28e0`, `a7170e8`, `a02f1fa`, `a2b6075`, `d73c9e2`, `ac1c79f`; `is_revenue` uncommitted); old data transferred from `dwidar_pivot_api` 2026-10-11 (`to_run`, re-runnable for go-live)
- **Phases:** (1) payments + withholding certificates + shift closing + old data import · (2) tax rates + invoices / receipts + ETA entry view · (3) reports
- **Files (PaymentModule):**
  - `app/Contracts/Payable`, `app/Models/Concerns/HasPayments` (payments(), `paymentsTotal()`, `payment_status` unpaid / partial / paid / credit)
  - `app/Models/{Payment, PaymentMethod, IncomeCategory, WithholdingCertificate}`, `database/Migrations/2026_10_11_10000{0-3}_*` (100004 / 100005 day closing migrations deleted 2026-10-13)
  - `app/Services/{PaymentService, PaymentMethodService, IncomeCategoryService}`, `app/Repositories/{PaymentMethod, IncomeCategory}Repository`
  - `app/Http/Controllers/Account/{PaymentMethod, IncomeCategory}AccountModuleController`, `app/Http/Requests/{PaymentMethod, IncomeCategory}Request`, `views/{Method, Category}/*`
  - Extras: `app/Models/Extra`, `app/Models/Concerns/HasExtras`, `app/Services/ExtraService`, `app/Http/Requests/ExtraRequest`, `Account/ExtraAccountModuleController`, routes `account.extras.{create, store, edit, update, destroy}`, `views/Extra/*`, migration `2026_10_11_100006_create_extras_table`
  - `income_categories.is_revenue` (create migration, model, request, service, "Counts as income" checkbox)
  - Payments: `app/Http/Controllers/Concerns/PaymentActions` (+ refundForm / refund for both areas) + `Account|Employee/Payment*ModuleController` (account adds edit / update / deleteForm / destroy), `app/Http/Requests/Payment{,Update,Delete,Refund}Request`, `app/Repositories/PaymentRepository`, `views/Payment/{index, create, show, edit, delete, refund, receipt-pdf, partials/{form, row-source, row-actions, payable-payments, row-remaining, status-filter, payment-summary, closed-shift-note}}`
  - Withholding: `Account/WithholdingCertificateAccountModuleController`, `Services/WithholdingCertificateService`, `Repositories/WithholdingCertificateRepository`, `Requests/WithholdingCertificateRequest`, `views/Withholding/*`
  - Shift closing (ONE level): `app/Models/{ShiftClosing (edited_at / edit_count, isEdited()), ShiftEvent (payment_id, changes)}`, migrations `2026_10_11_100007_create_shift_closings_table`, `100008_create_shift_events_table`, `payments.shift_closing_id` / `refund_of` / `reason` (payments create migration); `Services/ShiftClosingService` (open shift of any login, close, `paymentChanged()`, reminders from config, `openPeriods` / `longOpenPeriods`); `Concerns/MyShiftActions` (index, data, confirm, close) + `Employee/ShiftClosingEmployeeModuleController` (+ show, reminder, logoutPrompt, stillWorking) + `Account/MyShiftAccountModuleController`; `Account/ShiftClosingAccountModuleController` (show any shift, confirm / close on behalf); `Requests/Shift{Close, StillWorking}Request`; `Http/Middleware/ShiftReminder` (employees only; appended to `web` in `PaymentModuleServiceProvider::boot()` via the HTTP kernel); `views/Shift/{index, confirm, show, reminder, logout, partials/{totals, payments, period, back, status, change}}`
  - Daily Summary: `Services/DailySummaryService` (summary per method / collector, refunds), `Account/DailySummaryAccountModuleController`, `views/DailySummary/{index, partials/totals}`
  - `config/config.php`: `number_start` (100001), `shift_reminder_hours` (12), `shift_snooze_hours` (4)
  - `app/Console/RefreshPaymentsCommand` (`payments:refresh {--account=}`, registered in `Providers/PaymentModuleServiceProvider::$commands`)
  - Payments list partials (2026-10-13): `views/Payment/partials/{list-table (Payments page + My Shift), row-member, row-amount, row-source, list-summary}`; `Http/Middleware/WithholdingEnabled` (certificates routes, Settings › General)
  - `routes/web.php`: `{account|employee}.payments.{index, data, options.payables, create, store, show, receipt, refund, refund.store}` + `account.payments.{edit, update, delete, destroy}`; `account.daily-summary.index`; `account.shift.{index, data, confirm, close}`; `employee.shift.{index, data, confirm, close, reminder, logout-prompt, still-working, show}`; `account.shifts.{confirm, close, show}`; `account.withholding-certificates.*` (+ `attachment`); `account.payment-methods.*`, `account.income-categories.*`
- **How it works:**
  - Methods / categories: popup CRUD like reservation statuses (list, add / edit, active toggle, sort order); delete refused while used by payments
  - `PaymentService::create($accountId, $data, $creator)`: payable `reservation | package` (+ `payable_id`) or other income (`income_category_id`); type payment | withholding (refund = step 6); refuses a reservation inside a package, amount ≤ 0, no method (withholding → method 0) — any date (no day lock); `payment_no` = MAX + 1 per account under `lockForUpdate`; then `refresh(payable)`
  - `refresh()`: paid = Σ `Payment::PAID_SQL` (payments + withholding − refunds), remaining = due − paid (due 0 inside a package); `refreshAll()` one grouped query per payable type, ~7 s for all rows
  - `isOwner($user)` = userable is Account (edit / delete rights)
  - Payments list (account all; employees ONLY their own: `PaymentActions::collectorId()` + `Payment::scopeCollectedBy`; one payment of a reservation / package opens for any employee, a colleague's other income = 404) — simplified 2026-10-13 (`payments-plan.md` "Payments list"): tabs Today (default) | This Month | All (`Payment::TABS` / `scopeTab` / `tabDates()`); filters search | date (one field synced with the tabs) | `for` (reservation / package / income / `category-{id}`) | `method` (id / refund / withholding); summary line (`PaymentService::listSummary()` → partial `list-summary`, json `html` → `[data-html]`); columns Member (no.) | For (`row-source`: unit color + Space › Unit + start / package / category) | Date (+ time added) | Method | Amount (`row-amount`) | Created By (account only); view + receipt in the row menu
  - Add form: For (reservation / package / other income) → member (searchable) → the member's reservations (not in a package) / packages by ajax (`options.payables`, owed first, "Remaining x" in the name) or income category; type payment / withholding (no method); `create?payable=reservation|package&payable_id=X` pre-fills it (amount = remaining) for step 5
  - Receipt: `receipt()` = mpdf A5 PDF (inline response from `output()`, not the package's `stream()` which echoes itself); Cairo font if its files exist, else DejaVu Sans
  - Ownership: `PaymentRequest` (exists + account + active) and `PaymentService::create()` (method / category / member of the account, active not checked: imports bring old ones)
  - Views: reservation / package views have the payments block (due / paid / remaining + status, payments table, "Add Payment"; a reservation in a package → "Paid through the package"); "Add Payment" in both row menus; lists: Paid + Remaining columns + payment status filter (`HasPayments::scopePaymentStatus`: unpaid / partial / paid / credit / nothing_due / elsewhere = in package)
  - Owner (account routes + `assertOwner()` in the service): edit (amount, date, method, category, reference, notes; payable / member / type kept; in a CLOSED shift `change_reason` required), delete (reason required, soft delete + `deleted_by`); both in a closed shift → `ShiftClosingService::paymentChanged()` (totals recalculated, `shift_events` payment_edit / payment_delete with before / after snapshots, `edited_at` / `edit_count`)
  - Refund (account AND employees, `PaymentActions::refundForm / refund`, an employee: a payment they can open): a new `refund` row (`refund_of` = the payment, `reason` required, reference "Refund of #N") created by the refunder → their open shift; max = the payment's amount − its refunds (`refundable()`; an unlinked imported refund being edited: the old payable rule); not for a refund / withholding; original in a closed shift → `payment_refund` log + badge, its totals unchanged
  - Withholding certificates (account only): number, issue date, payer (name, tax id, optional company / member), period, amount, attachment (private disk `withholding/{account_id}/`, `attachment` route), withholding payments attached by checkboxes (free ones + its own); difference = amount − payments, shown (+ list filter), not blocking; delete frees its payments
  - Shift closing (§2b, ONE level): open shift = a login's payments with `shift_closing_id` 0 (linked, not a time window; `paid_at` is a date); every login has one (employees + the account); `close()` sends the max payment id shown (newer → 422 "review again"), saves totals (methods, refunds, withholding, cash_total, count, first / last `created_at`), logs `shift_events`; on behalf = account (`close_on_behalf`, employees only); no reopen — changes after closing go through `paymentChanged()`; the shift popup shows the badge, totals and its payments in the Payments list cells (newest 200), no History (the changes stay in `shift_events`)
  - Reminders (employees only): `reminderDue()` = oldest unclosed `created_at` older than `config shift_reminder_hours` (12) and no `still_working` in the last `shift_snooze_hours` (4); middleware `ShiftReminder` (not on `employee.shift.*`): full pages share `$shiftOpen` / `$shiftReminderUrl`, successful ajax actions get `X-Shift-Reminder`; logout popup "Close My Shift and Log Out" (confirm `?logout=1` → `logout_closed`) / "Log Out, I'm Still Working" (`logout_still_working`); none for the account
  - Daily Summary (account only, read only, `?date=`): Long Open Shifts (employees, answers, Close on Behalf) | the shifts with payments of that date (open → Close on Behalf / the account's own: Close My Shift; closed → View + badge) | day totals per method + per collector (Total payments, Refunds line, Total collected, withholding apart) | the day's refunds (who, amount, method, original payment, member, reason); popups carry `?date=` back (`Shift/partials/back`)
  - A reservation / package with (not deleted) payments can't be deleted: `deleteOne()` returns the reason (422 toast "refund or delete its payments first / move it into a package")
  - `movePayments()`: a reservation moved into a package → all its payments (trashed too) move to the package, both refreshed (`ReservationService::update`)
  - JSON answer `modal` (custom.js): a popup form goes back to that page after saving (add payment from a view, edits)
- **Depends on:** ReservationModule, PackageModule (`Payable`, `recalculate()` → `PaymentService::refresh()`), MemberModule, PlanModule / CompanyModule (phase 2 fields), LayoutModule sidebars
- **Decisions:**
  - One `payments` table: morph `payable` → Reservation | Package | null (other income); `type` = payment | refund | withholding; amount `decimal(12,2)` always positive
  - A reservation inside a package is paid through the package; a paid reservation moved into a package → its payments move with it
  - Negative `remaining` = member credit, shown clearly; wallet later. Refunds = refund records only
  - Employees add and refund payments (reason, into their own shift; user 2026-10-13); edit / delete = account owner; delete = soft delete + reason
  - ONE closing level, the shift (user, 2026-10-13, §2b; replaced the 2026-10-11 two levels with an account day closing): every login (the account too) closes its own shift; no day / month lock; no reopen — the account edits / deletes with a reason (logged before / after, totals recalculated, badge); refunds go into the refunder's open shift; never a hard block for employees (popups only, "still working" needs a reason, logged); reminder hours fixed in config
  - Imported data: each old login's payments (employees + the account) = one closed "Imported from the old system" shift (`to_run` 3.15; this DB: 3.15b for the account); the 52 imported refunds keep `refund_of` 0 (user)
  - Employee isolation: no colleagues' collections as a whole (lists, totals, closings, reports); all payment rows of ONE reservation / package are shown with "Collected by"
  - Payment methods per account; expenses = separate table later (no `direction`)
  - Old data source = `dwidar_pivot_api` (live old system; `dwidar_pivot_res` was outdated): payments id = `payment_no` = old id, incomes after the highest old payment id; old method 5 "إشعار خصم" = withholding (not a method); a negative withholding with no positive one on its reservation → refund "withholding correction" (old 8330)
  - Service rule errors = `ValidationException` (422 under the fields), so the service is safe for imports / commands too
  - Ownership (method, category, member, payable of the account) checked in the Form Request AND in `PaymentService` — the service is also called by imports / invoices (user, 2026-10-11)
  - Methods 1–4, 6 and categories 1–6 keep old ids; 6 VAT = inactive + `is_revenue` 0 (VAT collected never counts as income); new categories 101 Buffet, 102 Break Hours, 103 Extra Hours (ids 101+ so new old types never clash)
  - Extras (user, 2026-10-11): type extra = a sale (income category, revenue, invoiceable in phase 2) / adjustment = a balance move (not revenue, can be negative); amount = qty × unit price; account manages them from the package view; old extras classified by item (rule in `to_run` 3.10)
  - `refresh()` writes only `paid_amount` / `remaining` (query update: no `updated_at`, no events)
  - Invoices ETA-ready, no API now: invoices + receipts in one table, created on demand, issued = read-only, corrections by credit / debit note; manual ETA entry view + paste UUID (pending / registered); one ETA service layer (`EtaGateway`, manual now, API later)
  - Taxes: `tax_rates` (T1 VAT, T2 / T3 table tax, …), several per plan / income category (`taxables` pivot), several per line (`invoice_item_taxes`); ETA order = VAT on (net + table tax + taxable fees); ONE `TaxCalculator`
  - Prices VAT-inclusive by default (`prices_include_tax`); the calculator reverse-calculates through all taxes
  - Rounding to the exact price isolated in `RoundingStrategy` (setting `rounding_strategy`); extra-discount option waiting for the accountant
  - T4 (withholding) on invoices: setting `withholding_on_invoices`, default off (accountant confirming)
- **Pending:** browser test of My Shift / Daily Summary / closed-shift edits and refunds; check 6.9 decision; go-live re-run of `to_run` (3.15 covers the account); Phase 2 (InvoiceModule: extras of type extra + income categories become invoice lines; `is_revenue` 0 excluded from income reports in phase 3)

### Member / Company / Job modules
- **Last updated:** 2026-10-07
- **Files:** `MemberModule/app/{Models/Member, Services/MemberService}`, CompanyModule, JobModule
- **How it works:** account's members (company, job); names humanized on save; options "name mobile" for searchable selects.
- **Depends on:** —
- **Pending:** —

---

## Session log

### 2026-10-13 — ONE closing level: the shift
- **Changed:** day closing removed (tables, model, service, controllers, requests, views, routes, sidebar item, the day lock in `PaymentService`); shift reopen / close again removed; the account's own shift (`MyShiftActions`, `account.shift.*`, sidebar My Shift); closed-shift edit / delete with a reason → `ShiftClosingService::paymentChanged()` (recalculated totals, `shift_events` payment_* before / after, `edited_at` / `edit_count` badge, History); refunds for employees + the account (`refund_of`, `reason`, cap per payment, refunder's shift; closed original → log + badge only); refunds line in the shift / day totals; Daily Summary (`DailySummaryService`, `account.daily-summary.index`); reminder hours → config, Shift Reminders page + account columns removed; create migrations match the live DB (day migrations deleted); `to_run` (3.1 tables, 3.15 incl. the account, 3.15b one time, 3.16 / 6.12 removed, 6.11); docs (`payments-plan` §2b rewritten, CLAUDE.md, the user guide doc)
- **Why:** user: one simple closing level (the shift), no day / month lock, refunds by whoever gives the money back
- **Affects:** every payment edit / delete / refund, employee + account My Shift, account sidebar, `to_run`, the "Closing Day Cycle — User Guide" doc
- **Then (same day):** imported shifts closed at their last payment (`to_run` 3.15 / 3.15b / 6.11); My Shift without Add Payment / Daily Summary buttons, its payments = the Payments list (`?shift=open`); Settings › General (withholding tax switch applied everywhere, VAT stored for Phase 2); My Closings Payments column = the payments' dates, Withholding column by setting; shift popup = the payments in the Payments list cells (newest 200), no History; the user ran 3.15b + the accounts SQL (6.11 all 0)
- **Found:** `PaymentService` can't inject `ShiftClosingService` (it injects PaymentService) → resolved with `app()` when used; the account had 1,848 imported payments outside any shift (3.15b); popups don't start DataTables (cells rendered server side); a 4,296-payment shift took 5.7 s to render → capped at 200 (0.45 s)
- **Commit:** see suggested commit (user commits)

### 2026-10-13 — payments list simplified, same filter order on every list
- **Changed:** payments list: tabs Today | This Month | All, one filter row (search, dates, For with the "Other income" group, Method + Refunds / Withholding; Type / Sources / Categories filters removed), summary line by method (withholding apart), columns Member (no.) | For (unit color + Space › Unit + start / package / category) | Date (time) | Method | Amount (refund red, withholding badge) | Created By (account only); `Payment::TABS` / `scopeTab` / `for` + `method` filter keys, `PaymentService::listSummary()`, partials `row-member`, `row-amount`, `list-summary`; datatables.js json `html` → `[data-html]`; filter order search | date | selects on reservations + packages; packages date filter (period overlaps); then ONE date field for the three lists (flatpickr range, MIT, synced with the tabs both ways; `partials/{filter-tabs, date-range-filter}`, `Model::tabDates()`, custom.js `dateRange*` replaced the date range → All rule)
- **Why:** user: simpler payments list, one filter order on the three lists
- **Affects:** payments list (account + employee), reservations / packages toolbars, shared datatables.js / custom.js / custom.css (`.badge-small`, `.list-summary`)
- **Then (same day):** picked reservation days = the Today overlap rule (`days` tab); employees' payments: no tabs, one day, today default (server enforced); flatpickr local copy; Clear count = date field as one
- **Found:** flatpickr fires its own `change` on the shown field (stopped there, else datatables.js reloads twice); payments `paid_at` is a DATE (no time): the Date column's time = when the payment was added (`created_at`)
- **Commit:** see suggested commit (user commits)

### 2026-10-13 — packages list, package statement PDF, column tweaks
- **Changed:** packages list rebuilt (tabs Current | Past | All, `Package::TABS` / `scopeTab`, `scopeWithReservationStats` counted + timed hours, filters without the member select, columns + Activate / Deactivate menu `active-form`); shared cells `Payment/partials/{remaining-amount, payment-badge}` (reservations list uses them too); "Created By" header + Status moved last in both lists; Paid black bold `.amount-paid`; package statement PDF (`PackageService::statement()`, `PackageActions::statement()`, route `{area}.packages.statement`, `Package/statement-pdf`, pdf.css statement styles; zebra rows, cancelled red 0 / 0.00, Arabic RTL only when the text starts with Arabic); `to_run` STEP 4b (packages without a period); reservations list: day names, Space / Unit two lines, package links
- **Why:** user requests (lists in one style, customer statement — not an invoice)
- **Affects:** packages list + view (account + employee), reservations list cells, pdf.css, `to_run`
- **Found:** our `packages.updated_at` = the transfer time (old system's dates used instead); mpdf repeats `<thead>` by default; text containing Arabic but starting in Latin must stay LTR (else reversed); `sips` renders only page 1 (mpdf `importPage` used to check other pages); Swift PDFKit render hung
- **Commit:** `29c020b`

### 2026-10-13 — reservations toolbar simplified + new list columns
- **Changed:** toolbar = tabs Today | Upcoming | All (count on Today) + one filter row (search, status, space, type, payment, dates, Clear); contracts hidden from Today / Upcoming unless the type filter selects them; banner, Subscriptions tab, datatables.js `slots` removed (their unused code kept, user); columns Member | Space / Unit (two lines) | Plan | Period by type with day names | Status + payment badges | Amount | Paid | Remaining (credit blue) | By; package name + "In package" badge open the package popup; plan doc "CURRENT" sections
- **Why:** user: simpler toolbar, then exact columns
- **Affects:** reservations list (account + employee); `row-place` shared with the package view (one line there); CLAUDE.md dependencies (counts / is_contract wording)
- **Found:** PHP 8.5 pipe operator not available on the local CLI (8.4) — test scripts only
- **Commit:** `6eff649` / `29c020b`

### 2026-10-12 — reservations list tabs, Restore, contracts
- **Changed:** plan `docs/reservations-list-plan.md`; `Reservation` scopes (tabs, overlap, counted first, contracts, subscriptions filter, renewed), range date filters; list tabs + counts + long badge + muted rows; Restore popup + check in `changeStatus()`; Subscriptions tab + contracts summary; Renew pre-fill; `subscription_types.is_contract` (OPTIONS, create migration); reservations create migration indexes; datatables.js json `counts` / `slots`; custom.js tab rules + html status errors; custom.css `.row-muted`, `.contracts-summary`; a separate Subscriptions page was built then replaced by the tab (routes / menu removed)
- **Why:** ~20k reservations, months ahead, monthly contracts one row per period (user spec + answers)
- **Affects:** reservations list (account + employee), status menu, reservation form (renew, repeat on unsaved), subscription types form, shared datatables.js / custom.js / custom.css
- **Found:** 6,110 rows end before start, 243 "continues" rows with an end date, 19,569 repeat copies; renewal = same member + space in 1,092 / 1,113; `--color-slate-200` doesn't exist in app.css
- **Commit:** `6eff649`

### 2026-10-11 — shift closing + account day closing (two levels)
- **Changed:** plan `payments-plan.md` §2b; tables `shift_closings`, `shift_events`, `payments.shift_closing_id`, `accounts.shift_reminder_hours` / `shift_snooze_hours`; `ShiftClosingService`, two-level lock in `PaymentService`, day close guard in `DayClosingService`; employee My Shift (+ close popup, logout / reminder popups, still working); account day screen (close on behalf, reopen / close again, confirm popup), long open / reopened shift flags, Shift Reminders settings; employee payments list = own only, "Collected by" in the payments block; `ShiftReminder` middleware; custom.js popups; header / employee layout / both sidebars; `to_run` 3.1, 3.2, 3.15, 3.16, checks 6.11, 6.12; CLAUDE.md dependencies
- **Why:** the place works 24 h: employees close per shift, the account closes days for reports (user spec + answers)
- **Affects:** every payment edit / delete (closed shift), day closing (account only, guarded), employee payments list, LayoutModule header / employee layout / sidebars / custom.js, web middleware group
- **Found:** a TEMPORARY table can't be read twice in one MySQL query (3.16 uses a normal helper table); pushing a middleware to the router group at boot is overwritten by the kernel (use the kernel's `appendMiddlewareToGroup`); mixing `@php(...)` and `@php ... @endphp` in one Blade file breaks it; no `lock-open` icon in this Lucide
- **Commit:** `f17f7f9`

### 2026-10-11 — data transfer from dwidar_pivot_api (ran)
- **Changed:** `extras` (table, model, `HasExtras`, service, request, controller, routes, views; package due = net + extras); `income_categories.is_revenue` (create migration + form); `to_run` rewritten (backup, pre-check, truncate target tables, upsert employees / logins, inserts, recalculation, refresh, checks) and RUN; old `to_run` kept in `storage/app/backups/to_run.before-2026-10-11`
- **Why:** user: move all old data (up to date source) into the new system, account 1, old users = employees
- **Affects:** every module's data; PackageModule due (extras); IncomeCategory form
- **Found:** first run stopped at 3.10 (an extra without a date) → pre-check added (74 checks, stops before TRUNCATE); old system's stored package values were stale for 17 packages (check 6.9, decision pending); 9 old users had plain-text passwords (3 active: 12, 13, 15 — hashed as is, weak); all member displays are null-safe (9 reservations without a member checked: views, lists, receipt)
- **Commit:** `ac1c79f` (extras + code) + see suggested commit (is_revenue, docs)

### 2026-10-11 — step 10 (sidebars), delete guard, day closing history
- **Changed:** account sidebar + Withholding Certificates, Day Closing; employee sidebar + Day Closing; reservation / package delete refused while they have payments (`deleteOne()` → reason, 422); `day_closing_logs` (create migration, `DayClosingLog`, logged on close / reopen, History in the day view) + SQL in `to_run`; checkpoint
- **Why:** user decisions (sidebar OK, refuse paid deletes, full reopen history); step 10 minus the import (on hold)
- **Affects:** LayoutModule sidebars; Reservation / Package delete; day closing needs the new table
- **Found:** tested the logs with a TEMPORARY table (no real DB change); migrations batch was 11 (SQL uses 12)
- **Commit:** see suggested commit (user commits)

### 2026-10-11 — Payments phase 1 steps 5–9, reserved number range
- **Changed:** number range 100001 (`paymentmodule.number_start`, `nextNumber()` floor, `to_run` SQL; import SQL adjusted + re-dry-run); step 5 payments block + Add Payment in reservation / package views and row menus, Paid / Remaining columns + payment status filter; step 6 owner edit / delete (reason) / refund; step 7 withholding certificates (CRUD, attach payments, difference, private attachment); step 8 day closing (summary, close, reopen); step 9 `movePayments()`; custom.js JSON `modal`; custom.css `.payments-block__head`
- **Why:** user: import on hold (source may be outdated), reserve ids; continue Phase 1 to the first step needing DB changes / approval
- **Affects:** Reservation / Package lists + views; LayoutModule custom.js / custom.css; every payment change (closed days)
- **Found:** `/` redirects → the Laravel example test fails (old); my rolled back tests moved the `payments` / `withholding_certificates` AUTO_INCREMENT (the range SQL sets payments)
- **Commit:** user commits (not committed by Claude)

### 2026-10-11 — cancelled due 0, cutoff analysis, overpaid list
- **Changed:** `Reservation::payableAmount()` 0 for a not counted status; `ReservationService` refreshes paid / remaining on create / update / status change; `refreshAll()` eager loads the status; `to_run` RUN ORDER (import now, recalculation + refresh on hold); `storage/app/payments-review/{overpaid-reservations.csv, monthly.md}`
- **Why:** user decisions (cancelled = nothing due, cutoff 2024-04-24 under review, overpaid not touched)
- **Affects:** reservation remaining / payment status everywhere; 4 paid cancelled reservations become credit (915.00)
- **Found:** "2022-04" = payment date / start date of 18 back-dated rows (all created 2024-09, all fully paid), nothing created before 2024-06-01; old system's manual entry ends 2025-02-14; 11,916 reservations here aren't in the old DB (repeat copies from 2025-03-16)
- **Commit:** `d73c9e2`
