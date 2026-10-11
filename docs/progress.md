# Project Progress

> Loaded into every session via CLAUDE.md — keep it short and current.
> Edit module sections in place. Session log: last 10 entries only (git has the rest).

---

## Current status
- **Working on:** full data transfer `dwidar_pivot_api` (old, up to date, READ ONLY) → `dwidar_res_portal` — `to_run` rewritten (backup → truncate target tables → members, packages, reservations, extras, payments + incomes, payment methods, income categories 1–9, employees + logins from old users → recalculation → refresh → checks); WAITING for the user's go-ahead, nothing run yet. Phase 1 code done (steps 5–10 + extras NOT committed — the user commits, suggest messages only).
- **Source:** `dwidar_pivot_api` is the live old system (payments to 2026-10-11); `dwidar_pivot_res` was an outdated copy (ignore it). The new DB's reservations were a 2026-10-06 snapshot of `api`.
- **Next up:** after the transfer: check 6.9 (packages vs old: our net formula matched 208 / 212 before), then Phase 2 (InvoiceModule).
- **Waiting for the user:** (0) cutoff 2024-04-24 (recommended: config `paymentmodule.opening_date`, `refresh()` writes remaining 0 for a payable dated before it — not built; it only touches 18 reservations, all fully paid) and the 7,348 unpaid counted reservations outside packages (4,044,782.44): 6,796 of them (3.85M) are repeat copies created from 2025-03-16 that are NOT in `dwidar_pivot_res` (11,916 reservations of this DB aren't there), old payments end 2025-02-14; overpaid list `storage/app/payments-review/overpaid-reservations.csv` (163, don't touch); Cairo font files; (1) migrations rule vs practice (edit `create_*` + manual DB, see CLAUDE.md Conventions); (2) are Vite / Tailwind 4 in package.json used or removable; (3) accountant: rounding strategy + T4 on invoices (don't block Phase 1).
- **Known issues:**
  - DB columns added by hand must exist: `reservation_statuses.color`, `is_counted`, `is_default`, `sort_order`; `plans.is_time_based`; `subscription_types.can_repeat`, `auto_renew`; `reservations.paid_amount`, `remaining`; `packages.paid_amount`; money columns of reservations / packages = decimal(12,2) (all run 2026-10-11).
  - `reservations.paid_amount` / `remaining` are 0 until `php artisan payments:refresh` runs (lists show "Nothing due"); packages keep old values (remaining = net, "Unpaid") until recalculated (`to_run`).
  - App payments use ids / `payment_no` from 100001 (config `paymentmodule.number_start`, `PAYMENT_NUMBER_START`; `nextNumber()` floor) — run the `AUTO_INCREMENT = 100001` SQL; the import keeps old ids below it (its SQL checks / sets that).
  - Tables `day_closing_logs` and `extras` are created by `to_run` step 3.1 (CREATE IF NOT EXISTS + migrations rows) — until then day closing / package views with extras fail.
  - Old users 12, 13, 15 had plain-text passwords (`000` / `0000`): imported as the same password hashed — weak, ask them to change it. Inactive users (2, 4, 5, 6, 8, 9, 11) get `leave_date` = last login → login refused.
  - Cairo fonts of `config/pdf.php` (`public/assets/fonts/Cairo/`) are missing: the receipt falls back to DejaVu Sans (Arabic OK).
  - Changing a status's `is_counted` doesn't recalculate packages or refresh reservations (old gap) → run `payments:refresh` / package recalculation after such a change.
  - Table `creatable_backup` (old creator values of 220 packages + 2,887 members) — keep until the user confirms, then drop.
  - Legacy, unused: `AdminModuleController`, `Admin/UserModuleController`, AdminModule `admin/*` + UserModule `change_password` / `forgotPassword` views, nwidart `components/layouts/master` stubs.

---

## Modules

### UserModule / AccountModule / EmployeeModule (auth & user types)
- **Last updated:** 2026-10-06
- **Files:** `UserModule/app/{Contracts/Userable, Models/User, Http/Middleware/EnsureUserIsActive}`, `AccountModule/app/Models/Account`, `EmployeeModule/app/Models/Employee`, `UserModule/resources/views/user/account.blade.php`
- **How it works:** one login (`/login`) for all `users`; the `userable` model decides access (`loginError`), home, layout. Accounts register → pending → admin approves. Employees belong to an account, log in, change password only. Middleware `user.active:{Model}`.
- **Depends on:** LayoutModule (layouts per type)
- **Decisions:**
  - Employees' email managed by the account (`canChangeEmail()`) — account owns the profile
  - Employee attachments on the private disk, downloaded via the account — personal documents
  - Deleting an employee removes his login — email reusable
- **Pending:** —

### LayoutModule (layouts, shared UI, JS/CSS)
- **Last updated:** 2026-10-11
- **Files:** `views/{main,modal,account/*,admin/*,employee/*}`, `views/partials/*`, `config/config.php`, `public/assets/{css/custom.css, css/pdf.css, js/custom.js, js/datatables.js}`
  - `pdf.css`: PDF views only (mpdf, CSS 2, linked by `public_path()`), e.g. the payment receipt
  - Shell: `main` (page frame, `@yield('header'|'sidebar'|'title'|'actions'|'content')`, `@stack('styles'|'scripts')`), `header` (top bar + brand), `sidebar` (frame, `@include($menu)`), `partials/menu` (renders `$items`), `flash`, `footer`, `login` (guest shell for the login / register pages)
  - Per area: `{admin,account,employee}/main` (extends `main`, fills header vars + picks `{area}/sidebar`), `{area}/sidebar` (the `$items` array), `{area}/dashboard`
  - Account sidebar groups: Reservations, Payments, Withholding Certificates, Day Closing, Employees, Spaces, Settings (Payment Methods, Income Categories) — all user approved 2026-10-11; employee sidebar: Reservations, Packages, Payments, Day Closing
  - Brand: `public/assets/images/{logo.png, favicon.png|jpg}`
- **How it works:** popup CRUD (`data-modal`), ajax forms (`data-ajax`, SweetAlert confirm, 422 errors under fields, `html_` errors rendered as HTML), DataTables row update/remove, row toggles, dependent selects (`data-options-url`, `data-parent`, `data-also`), `data-show-if` (checkbox name, or `field=value other` for a select; `field=` = empty) / `data-disable-if`, Tom Select (`searchable`), `Str::humanize()` option texts.
  - Page → `@extends('layoutmodule::{area}.main')` → `main`. The area layout passes `header` its variables (`$homeUrl`, `$userName`, `$userSubtitle`, `$menuLinks`, `$logoutUrl`) and gives `sidebar` its `$menu` view. Menu items: `divider` / link (`title, icon, url, active, badge, counter`) / `children` drop list.
  - Sidebar: open by default, the arrow next to the logo collapses it to icons and remembers the choice (`localStorage.compactMenu`). Collapsed = `favicon` icon, open = `logo.png` (`.brand-logo` / `.brand-icon` in custom.css). Below 1280px: ☰ opens it as an overlay.
  - `custom.js` loads BEFORE `themes/dagger.js`: it sets the default (open), blocks dagger's hover-to-expand, and replaces dagger's resize handler (it forced compact below 1600px).
  - Script order: template vendors (`dom.js` = `window.$`, popper, dropdown, transition, simplebar, modal, tom-select, lucide) → `custom.js` → `dagger.js` → SweetAlert2 CDN → `@stack('scripts')`.
- **Depends on:** template (`Template_Source`), CDN DataTables / SweetAlert; area routes (`admin.dashboard`, `admin.logout`, `account.dashboard`, `employee.dashboard`, …) used by the area layouts / sidebars
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
- **Last updated:** 2026-10-09
- **Files:** `SpaceModule/app/{Models/Space,SubscriptionType}`, `UnitModule/app/{Models/Unit,Color, Services/UnitService}`, `PlanModule/app/{Models/Plan, Services/PlanService}`
- **How it works:** spaces have subscription types and plans; units belong to a space with one subscription type, capacity, concurrent usage, color, plans (from the space). Plans: amount, lease period, `is_time_based`.
- **Depends on:** —
- **Decisions:**
  - Subscription type flags `can_repeat`, `auto_renew` drive the reservation form options
  - Time based plan amount = amount × periods (`PERIOD_HOURS`, exact, month = 30 d)
- **Pending:** —

### ReservationModule
- **Last updated:** 2026-10-11
- **Files:** `app/Http/Controllers/Concerns/ReservationActions.php`, `Account|Employee/Reservation*ModuleController`, `Account/ReservationStatusAccountModuleController`, `app/Services/{ReservationService,ReservationStatusService}`, `app/Models/{Reservation,ReservationStatus}`, `app/Http/Requests/ReservationRequest.php`, `views/Reservation/*`, `views/Status/*`
- **How it works:** account + employees manage reservations. Form chain: subscription type → spaces → units (space + type) → plans (ajax). Package locks member. Status not in the form (new = default status; changed from the view header menu by ajax). Repeat on create only (frequency, every, until; copies with `repeat_id`). Availability check: overlapping counted reservations vs unit `concurrent_usage` (busiest moment), repeats included; error links to the clashing reservations.
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
- **Pending:** repeat for edits not supported by design

### PackageModule
- **Last updated:** 2026-10-11 (step 4)
- **Files:** `app/Http/Controllers/Concerns/PackageActions.php`, `app/Models/Package.php`, `app/Services/PackageService.php`, `views/Package/*`
- **How it works:** a member's package; price (`amount`) = sum of its counted reservations' net, discount % → net (`after_discount`), `date_from/to` from the reservations, total hours shown in the view. Recalculated on every reservation change (`recalculate()`).
- **Depends on:** ReservationModule, MemberModule
- **Decisions:**
  - A package with reservations or payments can't be deleted (user, 2026-10-11)
  - Discount typed as % or net, stored as % — price changes keep the %
  - `Package` implements `Payable` (due = `after_discount`), uses `HasPayments` + `SafeMorphHelper`; column `paid_amount`
  - `recalculate()` ends with `PaymentService::refresh($this)` (paid / remaining from the payments; it no longer sets `remaining` itself) — Payments step 4
- **Pending:** recalc existing packages (`to_run`, on hold)

### Payments / Invoices / ETA (PaymentModule — phase 1 in progress; InvoiceModule, ReportModule — not built)
- **Last updated:** 2026-10-11
- **Spec: `docs/payments-plan.md` — read before any payments/invoice work**
- **Status:** Phase 1 — steps 1–9 done (`9dd28e0`, `a7170e8`, `a02f1fa`, `a2b6075`, 5–9 uncommitted); DB migrated + ALTERs run; methods / categories imported; old payments import on hold
- **Phases:** (1) payments + withholding certificates + day closing + old data import · (2) tax rates + invoices / receipts + ETA entry view · (3) reports
- **Files (PaymentModule):**
  - `app/Contracts/Payable`, `app/Models/Concerns/HasPayments` (payments(), `paymentsTotal()`, `payment_status` unpaid / partial / paid / credit)
  - `app/Models/{Payment, PaymentMethod, IncomeCategory, WithholdingCertificate, DayClosing}`, `database/Migrations/2026_10_11_10000{0-4}_*`
  - `app/Services/{PaymentService, PaymentMethodService, IncomeCategoryService}`, `app/Repositories/{PaymentMethod, IncomeCategory}Repository`
  - `app/Http/Controllers/Account/{PaymentMethod, IncomeCategory}AccountModuleController`, `app/Http/Requests/{PaymentMethod, IncomeCategory}Request`, `views/{Method, Category}/*`
  - `app/Models/DayClosingLog`, `database/Migrations/2026_10_11_100005_create_day_closing_logs_table`
  - Payments: `app/Http/Controllers/Concerns/PaymentActions` + `Account|Employee/Payment*ModuleController` (account adds edit / update / deleteForm / destroy / refundForm / refund), `app/Http/Requests/Payment{,Update,Delete,Refund}Request`, `app/Repositories/PaymentRepository`, `views/Payment/{index, create, show, edit, delete, refund, receipt-pdf, partials/{form, row-source, row-actions, payable-payments, row-remaining, status-filter, payment-summary}}`
  - Withholding: `Account/WithholdingCertificateAccountModuleController`, `Services/WithholdingCertificateService`, `Repositories/WithholdingCertificateRepository`, `Requests/WithholdingCertificateRequest`, `views/Withholding/*`
  - Day closing: `Concerns/DayClosingActions` + `Account|Employee/DayClosing*ModuleController` (account adds reopen), `Services/DayClosingService`, `Requests/Day{Closing,Reopen}Request`, `views/DayClosing/*`
  - `config/config.php`: `number_start` (100001)
  - `app/Console/RefreshPaymentsCommand` (`payments:refresh {--account=}`, registered in `Providers/PaymentModuleServiceProvider::$commands`)
  - `routes/web.php`: `{account|employee}.payments.{index, data, options.payables, create, store, show, receipt}` + `account.payments.{edit, update, delete, destroy, refund, refund.store}`; `{account|employee}.day-closings.{index, data, create, store, show}` + `account.day-closings.{reopen, reopen.store}`; `account.withholding-certificates.*` (+ `attachment`); `account.payment-methods.*`, `account.income-categories.*`
- **How it works:**
  - Methods / categories: popup CRUD like reservation statuses (list, add / edit, active toggle, sort order); delete refused while used by payments
  - `PaymentService::create($accountId, $data, $creator)`: payable `reservation | package` (+ `payable_id`) or other income (`income_category_id`); type payment | withholding (refund = step 6); refuses a reservation inside a package, amount ≤ 0, no method (withholding → method 0), a closed day (`assertDayOpen`); `payment_no` = MAX + 1 per account under `lockForUpdate`; then `refresh(payable)`
  - `refresh()`: paid = Σ `Payment::PAID_SQL` (payments + withholding − refunds), remaining = due − paid (due 0 inside a package); `refreshAll()` one grouped query per payable type, ~7 s for all rows
  - `isOwner($user)` = userable is Account (edit / delete / refund rights, steps 6+)
  - Payments list (account + employees): search (number, reference, member), filters for (reservation / package / other income `source`), type, method, category, dates; view + receipt in the row menu
  - Add form: For (reservation / package / other income) → member (searchable) → the member's reservations (not in a package) / packages by ajax (`options.payables`, owed first, "Remaining x" in the name) or income category; type payment / withholding (no method); `create?payable=reservation|package&payable_id=X` pre-fills it (amount = remaining) for step 5
  - Receipt: `receipt()` = mpdf A5 PDF (inline response from `output()`, not the package's `stream()` which echoes itself); Cairo font if its files exist, else DejaVu Sans
  - Ownership: `PaymentRequest` (exists + account + active) and `PaymentService::create()` (method / category / member of the account, active not checked: imports bring old ones)
  - Views: reservation / package views have the payments block (due / paid / remaining + status, payments table, "Add Payment"; a reservation in a package → "Paid through the package"); "Add Payment" in both row menus; lists: Paid + Remaining columns + payment status filter (`HasPayments::scopePaymentStatus`: unpaid / partial / paid / credit / nothing_due / elsewhere = in package)
  - Owner (account routes + `assertOwner()` in the service): edit (amount, date, method, category, reference, notes; payable / member / type kept), delete (reason required, soft delete + `deleted_by`), refund (a new `refund` row for the same payable / income, max = money received: payments − refunds of the payable, other income = that payment; not for a refund / withholding; reference "Refund of #N")
  - Withholding certificates (account only): number, issue date, payer (name, tax id, optional company / member), period, amount, attachment (private disk `withholding/{account_id}/`, `attachment` route), withholding payments attached by checkboxes (free ones + its own); difference = amount − payments, shown (+ list filter), not blocking; delete frees its payments
  - Day closing (account + employees close, owner reopens with a reason): `summary()` by creator × method (payments − refunds), withholding apart; totals saved as json; closing again after a reopen saves new totals; `assertDayOpen` in create / update (old + new date) / delete / refund
  - Day closing history: every close / reopen → `day_closing_logs` (`DayClosingLog`: action, user, reason, cash / withholding totals + totals json snapshot; `DayClosing::logs()`), shown as "History" in the day view; `day_closings.reopened_*` = the last reopen only
  - A reservation / package with (not deleted) payments can't be deleted: `deleteOne()` returns the reason (422 toast "refund or delete its payments first / move it into a package")
  - `movePayments()`: a reservation moved into a package → all its payments (trashed too) move to the package, both refreshed (`ReservationService::update`)
  - JSON answer `modal` (custom.js): a popup form goes back to that page after saving (add payment from a view, edits)
- **Depends on:** ReservationModule, PackageModule (`Payable`, `recalculate()` → `PaymentService::refresh()`), MemberModule, PlanModule / CompanyModule (phase 2 fields), LayoutModule sidebars
- **Decisions:**
  - One `payments` table: morph `payable` → Reservation | Package | null (other income); `type` = payment | refund | withholding; amount `decimal(12,2)` always positive
  - A reservation inside a package is paid through the package; a paid reservation moved into a package → its payments move with it
  - Negative `remaining` = member credit, shown clearly; wallet later. Refunds = refund records only
  - Employees only add payments; edit / delete / refund = account owner; delete = soft delete + reason
  - Day closing: one per account per day, locks that date; totals by employee × payment method (cash handover), withholding excluded from cash; reopen = owner + reason
  - Payment methods per account; expenses = separate table later (no `direction`)
  - Old data: 5,042 payments + 227 incomes from `dwidar_pivot_res`, ids kept; old method 5 "إشعار خصم" = withholding (confirmed): NOT imported as a method, no `is_cash` column (re-confirmed 2026-10-11)
  - Service rule errors = `ValidationException` (422 under the fields), so the service is safe for imports / commands too
  - Ownership (method, category, member, payable of the account) checked in the Form Request AND in `PaymentService` — the service is also called by imports / invoices (user, 2026-10-11)
  - Old payments / incomes import moved before step 5 (user, 2026-10-11): payments id = `payment_no` = old id, incomes id = `payment_no` = 5189 + old id; old payment 35 (reservation 106 in package 3) → package 3; old 0.00 payment 42 kept; checks: counts, signed totals, per method / type / payable / category all equal the old DB
  - Old methods / categories imported with old ids: methods 1–4, 6 (Cash, Visa, InstaPay, Vodafone Cash, Bank Transfer - CIB; 5 skipped), categories 1–5; AUTO_INCREMENT 7 / 6
  - `refresh()` writes only `paid_amount` / `remaining` (query update: no `updated_at`, no events)
  - Invoices ETA-ready, no API now: invoices + receipts in one table, created on demand, issued = read-only, corrections by credit / debit note; manual ETA entry view + paste UUID (pending / registered); one ETA service layer (`EtaGateway`, manual now, API later)
  - Taxes: `tax_rates` (T1 VAT, T2 / T3 table tax, …), several per plan / income category (`taxables` pivot), several per line (`invoice_item_taxes`); ETA order = VAT on (net + table tax + taxable fees); ONE `TaxCalculator`
  - Prices VAT-inclusive by default (`prices_include_tax`); the calculator reverse-calculates through all taxes
  - Rounding to the exact price isolated in `RoundingStrategy` (setting `rounding_strategy`); extra-discount option waiting for the accountant
  - T4 (withholding) on invoices: setting `withholding_on_invoices`, default off (accountant confirming)
- **Pending:** old payments import (ON HOLD, `to_run` part 2, dry-run checked against `dwidar_pivot_res`); cutoff 2024-04-24 (recommended, not built); Phase 2

### Member / Company / Job modules
- **Last updated:** 2026-10-07
- **Files:** `MemberModule/app/{Models/Member, Services/MemberService}`, CompanyModule, JobModule
- **How it works:** account's members (company, job); names humanized on save; options "name mobile" for searchable selects.
- **Depends on:** —
- **Pending:** —

---

## Session log

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

### 2026-10-11 — old payments import SQL, sidebars, pdf.css rule
- **Changed:** `to_run` part 2 (old payments + incomes import, AUTO_INCREMENT, checks; dry-run in a rolled back transaction = all equal); Payments in account + employee sidebars; CLAUDE.md + memory: `pdf.css` = the one exception to the custom-CSS rule
- **Why:** user moved the import before any real payment (id / number clash); user approved the sidebar + pdf.css
- **Affects:** LayoutModule sidebars; every reservation / package paid / remaining after `payments:refresh`
- **Found:** 8,823 reservations outside packages have no payment (4.74M; 1,475 cancelled); old DB has no other paid marker; 22 partly paid (3,485 owed), 163 overpaid (19,030.50 credit); my rolled back tests moved `payments` AUTO_INCREMENT to 18 (the SQL sets it)
- **Commit:** `55a4dcd`

### 2026-10-11 — Payments phase 1 step 4
- **Changed:** payments list / add form / view / receipt PDF for account + employees (`PaymentActions`, 2 controllers, `PaymentRequest`, `PaymentRepository`, routes `{area}.payments.*`); `PaymentService` ownership checks + `listQuery` / `findOne` / `payableOptions`; `Payment` `source` filter + type badge, `HasPayments` status badge; `Package::recalculate()` → `PaymentService::refresh()`; custom.js `data-show-if="field=value"` + payment form reload; `public/assets/css/pdf.css`
- **Why:** Phase 1 build order step 4 + the two items the user moved into it
- **Affects:** PackageModule (remaining now follows payments); every `data-show-if` (checkbox use unchanged); LayoutModule sidebars still without Payments
- **Found:** `members` has no `deleted_at`; Cairo PDF fonts missing; the package's `stream()` echoes outside Laravel; app payments before the import would clash with the kept old ids / numbers
- **Commit:** `a2b6075`

### 2026-10-11 — reservation 500 fix + Payments phase 1 steps 1–3
- **Changed:** `App\Helpers\SafeMorphHelper` + `App\Models\Relations\SafeMorphTo` on Reservation, Package, Member, Payment, WithholdingCertificate; creator data fix (220 packages + 2,887 members → User 1, backup table `creatable_backup`, SQL + revert in `to_run`); PaymentModule skeleton aligned; step 1 (5 tables, 5 models, `Payable` / `HasPayments`, reservation / package money columns decimal + `paid_amount` / `remaining`); step 2 (methods / categories CRUD); account sidebar "Settings"; old methods / categories import SQL (user ran it); step 3 (`PaymentService`, `payments:refresh`)
- **Why:** plan decision 6 (fix before Phase 1); Phase 1 build order; user moved the sidebar + the methods / categories import earlier
- **Affects:** Reservation / Package / Member models + create migrations; LayoutModule account sidebar; every future payment caller (service rules)
- **Found:** reservations' creators were already fixed by hand; `to_run` is gitignored; old method 6 "Bank Transfer - CIB" exists (missing from the plan); old category 2 has an Arabic English name
- **Commit:** `a0be704`, `4395657`, `9dd28e0`, `a7170e8`, `77731d8`, `a02f1fa` (+ docs commits)

### 2026-10-10 — payments, invoices & ETA plan
- **Changed:** wrote `docs/payments-plan.md` (3 phases: files, DB columns, import SQL, build order, ETA tax formula + worked example); Payments section in this file
- **Why:** user wants payments for reservations, packages, other income + VAT invoices / e-receipts ready for ETA
- **Affects:** Reservation, Package, Member, Plan, Company modules; LayoutModule sidebars (when built)
- **Found:** old DB `dwidar_pivot_res` has payments / incomes / methods / income types (all link to existing rows); ETA rule: VAT base includes table tax; 5-decimal rounding can miss an inclusive price by ≤ 0.00001
- **Commit:** "docs: payments, invoices & ETA module plan"

### 2026-10-09 — layout notes added to memory (work done 2026-10-05)
- **Changed:** documented the dagger layout set up on 2026-10-05: Bootstrap layout → Tailwise dagger (main, header, sidebar, flash, footer, login + AdminModule login form), old CSS/JS and commented code removed, full-width content, sidebar open by default + collapse toggle (no hover-expand), logo / favicon, `custom.css` / `custom.js` created
- **Why:** user requests; the memory files were written in another session without the layout history
- **Affects:** every module's pages (LayoutModule shell)
- **Found:** favicon jpg/png mismatch between `main` and `login`; root `views/dashboard.blade.php` probably unused
- **Commit:** layout code in `ea7738c` ("New Layout") and later commits; this entry: see suggested commit

### 2026-10-09 — reservations, packages, statuses on imported data
- **Changed:** reservation form (chain filters, time based plans, amount × periods, repeat on create, availability + concurrent usage, clickable conflict links), status menu + colors + `is_counted` + default status toggle, package price/net/dates/hours from reservations, list/view display (unit colors, `d-m-Y | h:i A`), memory files (CLAUDE.md, progress.md)
- **Why:** user requests while finishing ReservationModule on old-system data
- **Affects:** Package totals, Unit/Plan/Space options, LayoutModule JS/CSS
- **Found:** reservation view 500 = imported `creatable_type` old class names (diagnosed, not fixed yet)
- **Commit:** code in `b98d51b` / `2344555`; memory files: "docs: add CLAUDE.md and docs/progress.md project memory"
