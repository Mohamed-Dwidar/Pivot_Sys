# Project: Res Portal (Reservations)

@docs/progress.md

## Before you start (every session)
1. `docs/progress.md` is loaded above — treat it as the latest state, decisions, and cross-module dependencies.
2. Run `git log --oneline -15` to see recent changes.
3. If the task touches a module listed under "Dependencies map", check the related files before editing.

## When a task or feature is done
When a task is finished, or when I say "update progress" or run /checkpoint, update `docs/progress.md`:
- Edit the relevant module section (files, how it works, dependencies, decisions, pending) — don't just append.
- Add one entry to the session log; keep only the last 10 entries.
- Update "Current status".
Then suggest a commit message.

## Stack
- Framework: Laravel 12, PHP ^8.2 (local CLI 8.4), modular with `nwidart/laravel-modules` (13 modules in `Modules/`)
- Database: MySQL `dwidar_res_portal` (timezone Africa/Cairo, locale en; `name_ar` / `name_en` fields ready for ar)
- Frontend: Blade + Tailwise "dagger" template (compiled `public/assets/css/app.css`, theme class `theme-6` on `<html>`), vanilla JS in `public/assets/js/custom.js`; jQuery only for DataTables (`jQuery.noConflict`). Vite/Tailwind 4 in package.json is NOT used by the pages.
- Auth: custom, 2 guards — `admin` (table `admins`, login `/admin`) and `web` (table `users`, morph `userable` → Account | Employee, login `/login`)
- Permissions: none (user type checked by middleware `user.active:{Model}`)
- Key packages: prettus/l5-repository (repositories), yajra/laravel-datatables-oracle 12 (server-side lists), intervention/image 2.7 (uploads), maatwebsite/excel, niklasravnsborg/laravel-pdf; CDN: DataTables 2.1.8, SweetAlert2, template Tom Select, Lucide icons

## Commands
- Dev server: `php artisan serve` (user runs http://127.0.0.1:8000; Apache also serves `/mdwidar_dev/Pivot/Pivot_Sys_New/public`)
- Assets: none to build — edit `public/assets/css/custom.css` / `public/assets/js/custom.js` directly (loaded with `?v=filemtime`)
- Migrate: `php artisan migrate` (user usually changes columns by hand, see Conventions)
- Tests: `php artisan test` (only Laravel example tests exist)
- Clear caches: `php artisan optimize:clear` (views: `php artisan view:clear`)

## Project structure
- `Modules/{Name}Module/` — one module per feature: `app/{Http/Controllers/{Account|Admin|Employee|Guest|Auth}, Http/Requests, Models, Repositories, Services}`, `database/Migrations`, `resources/views`, `routes/web.php`
- `Modules/LayoutModule/` — layouts (`main`, `admin/*`, `account/*`, `employee/*`, `modal`), sidebars, shared partials (`partials/field`, `checkbox`, `row-menu`, `row-toggle`, `form-actions`, `datatable-*`, `gallery`, `images-input`), `config/config.php` (template form / menu class strings)
- `app/Helpers/` — traits: `LocalizedHelper` (name_ar/name_en → `->name`, `orderByLocalized`), `UploaderHelper`, others legacy
- `app/Providers/AppServiceProvider.php` — `Str::humanize()` macro (drop-menu option texts)
- `public/assets/css/custom.css` — ALL custom CSS; `public/assets/js/custom.js` — modal, ajax forms, dynamic forms, toggles; `public/assets/js/datatables.js` — DataTables + `window.dataTables` row API
- `Template_Source/` — the purchased template HTML (reference for markup/classes)
- `to_run` — user's notes: manual SQL / commands to run on the DB

## Conventions
- Controllers: `{Module}{UserType}ModuleController` in `Controllers/{UserType}/`; a feature used by account AND employee = `Controllers/Concerns/{X}Actions` trait + 2 thin controllers (`area()` = account|employee)
- Route names: `{area}.{resource}.{action}` (`account.reservations.show`, `employee.packages.index`, `admin.accounts.status`); always named routes
- Data scope: `Auth::user()->userable->ownerAccountId()` (Account → id, Employee → account_id)
- Validation: Form Requests; business logic: Services (+ Prettus repositories `forAccount()`); controllers stay thin
- Lists: DataTables server side; controller `table($filters, $id)` builds the columns once (also used to re-render one row), `setRowId('row-'.id)`, no `#` column
- CRUD UI: view/add/edit open in the popup (`<a data-modal>`, views `@extends(request()->ajax() ? 'layoutmodule::modal' : layout)`); forms `data-ajax` + `data-confirm*`; JSON answers `{message, row|reload, redirect, counts}`; rows update in place
- Forms: `layoutmodule::partials.field` with template classes from `config('layoutmodule.form.*')`; `searchable`, `width`, `wrapperClass` options; no inline CSS
- Display formats: dates `d-m-Y`, times `h:i A` (`Reservation::DATE_FORMAT/TIME_FORMAT`); date/time INPUT values must stay `Y-m-d` / `H:i`
- DB columns: snake_case, `{x}_ar/{x}_en` for translated text, `account_id` on every account-owned table, `0` = none for non-nullable foreign ids, soft deletes on most tables
- Schema changes: in practice the user edits the module `create_*_table` migration and alters the DB by hand (new add/rename migrations were deleted) — conflicts with the Rules line below, confirm which to follow
- Language: UI English now; Arabic names stored, RTL not done yet
- Layout: pages `@extends` their area layout (`layoutmodule::admin.main` / `account.main` / `employee.main`), never `main` directly; fill `title`, `actions`, `content`; page JS/CSS via `@push('scripts')` / `@push('styles')`
- Layout: template markup + classes copied from `Template_Source/dagger-*.html`; custom styles = named classes in `custom.css` (no `style=""`, no `<style>`); template overrides in `custom.js`, never edit template files (`app.css`, `themes/dagger.js`, `vendors/*`)
- Layout: icons = Lucide (`data-lucide="name"`); images in `public/assets/images` (only logo / favicon — template demo images not copied)
- Layout: sidebar open by default, collapse toggle remembered (`localStorage.compactMenu`), no hover-expand; content full width (no `.container`)

## Dependencies map (what affects what)
- **UserModule** ↔ AccountModule, EmployeeModule: `users` morph `userable`; `Userable` contract (`loginError`, `homeRoute`, `layout`, `displayName`, `canChangeEmail`, `ownerAccountId`) — adding a user type = implement all
- **LayoutModule** ↔ every module: layouts, sidebars (account/admin/employee), shared partials, `custom.js` / `custom.css` / `datatables.js`
  - page → `layoutmodule::{area}.main` → `main`; area layout feeds `header` (`$homeUrl`, `$userName`, `$userSubtitle`, `$menuLinks`, `$logoutUrl`) and `sidebar` (`$menu` = `layoutmodule::{area}.sidebar` → `partials/menu`)
  - a new page needs a menu item in `{area}/sidebar.blade.php` (route name + `active` check, optional `counter` badge updated by ajax `counts`)
  - area layouts / sidebars use named routes from Admin/Account/Employee/User modules — renaming those routes breaks every page of that area
  - Login / register pages (AdminModule `login`, UserModule `user/login`, AccountModule `Guest/register`) → `layoutmodule::login`
  - `custom.js` must load before `themes/dagger.js` (sidebar default / overrides); DataTables' jQuery must stay `noConflict` (template `$` = `dom.js`)
- **SpaceModule** ↔ UnitModule, PlanModule, ReservationModule: spaces ↔ subscription types (`can_repeat`, `auto_renew`) ↔ plans (`plan_space`); unit form picks from its space's types / plans
- **UnitModule** ↔ ReservationModule: unit `subscription_type_id`, `capacity`, `concurrent_usage`, `color` used by the reservation form + availability check
- **PlanModule** ↔ ReservationModule: `is_time_based`, `lease_period`, `amount` → reservation times + amount (`Plan::amountBetween`, same formula in custom.js)
- **ReservationModule** ↔ PackageModule: saving / deleting / status change of a reservation calls `PackageService::recalculate()` (price, net, dates); `ReservationStatus::is_counted` excludes cancelled-like reservations from package totals, hours and availability
- **MemberModule** ↔ PackageModule, ReservationModule: member options "name mobile" (searchable), package locks the reservation member
- **EmployeeModule** ↔ ReservationModule, PackageModule: employees manage the account's reservations / packages (`employee.*` routes)

## Rules
- Before renaming anything shared (routes, components, config keys), search the whole project for references and update them all.
- Never edit migrations that already ran — create a new migration.
- Ask before deleting files or changing shared layouts.
- Keep changes minimal and scoped to the task.
 