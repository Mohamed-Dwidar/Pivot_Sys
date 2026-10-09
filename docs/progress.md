# Project Progress

> Loaded into every session via CLAUDE.md — keep it short and current.
> Edit module sections in place. Session log: last 10 entries only (git has the rest).

---

## Current status
- **Working on:** ReservationModule on the imported old-system data (`reservations from old db`).
- **Next up:** fix the reservation view 500 (old `creatable_type` values), then run the package recalculation (`to_run`).
- **Waiting for the user:** (1) 500 fix option — code guard / SQL update / both; (2) migrations rule vs practice (edit `create_*` + manual DB, see CLAUDE.md Conventions); (3) are Vite / Tailwind 4 in package.json used or removable.
- **Known issues:**
  - Reservation view → 500: ~17,800 imported reservations have `creatable_type` = `Modules\UserModule\Entities\User` / `Modules\AdminModule\Entities\Admin` (old classes, don't exist); only 3 of 13 old user ids exist in `users`. Options given: guard in code (recommended) / SQL update (in `to_run`) / both — waiting for the user's choice.
  - DB columns added by hand must exist: `reservation_statuses.color`, `is_counted`, `is_default`, `sort_order`; `plans.is_time_based`; `subscription_types.can_repeat`, `auto_renew`.
  - Packages keep old values until recalculated (`to_run` command).
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
- **Last updated:** 2026-10-09
- **Files:** `views/{main,modal,account/*,admin/*,employee/*}`, `views/partials/*`, `config/config.php`, `public/assets/{css/custom.css, js/custom.js, js/datatables.js}`
  - Shell: `main` (page frame, `@yield('header'|'sidebar'|'title'|'actions'|'content')`, `@stack('styles'|'scripts')`), `header` (top bar + brand), `sidebar` (frame, `@include($menu)`), `partials/menu` (renders `$items`), `flash`, `footer`, `login` (guest shell for the login / register pages)
  - Per area: `{admin,account,employee}/main` (extends `main`, fills header vars + picks `{area}/sidebar`), `{area}/sidebar` (the `$items` array), `{area}/dashboard`
  - Brand: `public/assets/images/{logo.png, favicon.png|jpg}`
- **How it works:** popup CRUD (`data-modal`), ajax forms (`data-ajax`, SweetAlert confirm, 422 errors under fields, `html_` errors rendered as HTML), DataTables row update/remove, row toggles, dependent selects (`data-options-url`, `data-parent`, `data-also`), `data-show-if` / `data-disable-if`, Tom Select (`searchable`), `Str::humanize()` option texts.
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
- **Last updated:** 2026-10-09
- **Files:** `app/Http/Controllers/Concerns/ReservationActions.php`, `Account|Employee/Reservation*ModuleController`, `Account/ReservationStatusAccountModuleController`, `app/Services/{ReservationService,ReservationStatusService}`, `app/Models/{Reservation,ReservationStatus}`, `app/Http/Requests/ReservationRequest.php`, `views/Reservation/*`, `views/Status/*`
- **How it works:** account + employees manage reservations. Form chain: subscription type → spaces → units (space + type) → plans (ajax). Package locks member. Status not in the form (new = default status; changed from the view header menu by ajax). Repeat on create only (frequency, every, until; copies with `repeat_id`). Availability check: overlapping counted reservations vs unit `concurrent_usage` (busiest moment), repeats included; error links to the clashing reservations.
- **Depends on:** Space/Unit/Plan, PackageModule (recalculate), MemberModule
- **Decisions:**
  - Status colors = fixed theme palette (`badge-status-{color}`) — no inline CSS
  - `is_counted` status flag (off = cancelled-like) excluded from package totals, hours, availability
  - One default + active status per account; default can't be switched off / deleted
  - Server recalculates amounts, period, subscription day (`ReservationService::period()`)
- **Pending:** reservation view 500 on imported data (see Known issues); repeat for edits not supported by design

### PackageModule
- **Last updated:** 2026-10-09
- **Files:** `app/Http/Controllers/Concerns/PackageActions.php`, `app/Models/Package.php`, `app/Services/PackageService.php`, `views/Package/*`
- **How it works:** a member's package; price (`amount`) = sum of its counted reservations' net, discount % → net (`after_discount`), `date_from/to` from the reservations, total hours shown in the view. Recalculated on every reservation change (`recalculate()`).
- **Depends on:** ReservationModule, MemberModule
- **Decisions:**
  - Discount typed as % or net, stored as % — price changes keep the %
  - `remaining` = net (no payments yet)
- **Pending:** payments; recalc existing packages (`to_run`)

### Member / Company / Job modules
- **Last updated:** 2026-10-07
- **Files:** `MemberModule/app/{Models/Member, Services/MemberService}`, CompanyModule, JobModule
- **How it works:** account's members (company, job); names humanized on save; options "name mobile" for searchable selects.
- **Depends on:** —
- **Pending:** —

---

## Session log

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
