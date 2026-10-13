@php
    /*
     * The account sidebar, by how often things are used (2026-10-13): Daily (direct links) | Management | Settings (one group,
     * collapsed unless the current page is in it — partials/menu opens a group with an active child).
     * Settings › General: withholding tax off → no Withholding Certificates item.
     */
    $withholdingOn = Auth::user()->userable->withholdingEnabled();
    $items = [
        ['divider' => 'Daily'],
        [
            'title' => 'Dashboard',
            'icon' => 'gauge-circle',
            'url' => route('account.dashboard'),
            'active' => request()->routeIs('account.dashboard'),
        ],
        [
            'title' => 'Reservations',
            'icon' => 'calendar-check',
            'url' => route('account.reservations.index'),
            'active' => request()->routeIs('account.reservations.*'),
        ],
        [
            'title' => 'Payments',
            'icon' => 'wallet',
            'url' => route('account.payments.index'),
            'active' => request()->routeIs('account.payments.*'),
        ],
        [
            'title' => 'My Shift',
            'icon' => 'lock',
            'url' => route('account.shift.index'),
            'active' => request()->routeIs('account.shift.*'),
        ],
        [
            'title' => 'Members',
            'icon' => 'users',
            'url' => route('account.members.index'),
            'active' => request()->routeIs('account.members.*') && !request()->routeIs('account.members.create'),
        ],
        [
            'title' => 'Packages',
            'icon' => 'package',
            'url' => route('account.packages.index'),
            'active' => request()->routeIs('account.packages.*'),
        ],

        ['divider' => 'Management'],
        [
            'title' => 'Daily Summary',
            'icon' => 'calendar-days',
            'url' => route('account.daily-summary.index'),
            'active' => request()->routeIs('account.daily-summary.*', 'account.shifts.*'),
        ],
        ...($withholdingOn ? [[
            'title' => 'Withholding Certificates',
            'icon' => 'file-badge',
            'url' => route('account.withholding-certificates.index'),
            'active' => request()->routeIs('account.withholding-certificates.*'),
        ]] : []),
        [
            'title' => 'Employees',
            'icon' => 'contact',
            'url' => route('account.employees.index'),
            'active' => request()->routeIs('account.employees.*'),
        ],

        ['divider' => 'Settings'],
        [
            'title' => 'Settings',
            'icon' => 'settings',
            'children' => [
                [
                    'title' => 'General',
                    'icon' => 'sliders-horizontal',
                    'url' => route('account.settings.general.edit'),
                    'active' => request()->routeIs('account.settings.general.*'),
                ],
                [
                    'title' => 'Spaces & Units',
                    'icon' => 'layout-grid',
                    'url' => route('account.spaces.index'),
                    // the units pages are under a space (account.spaces.units.*)
                    'active' => request()->routeIs('account.spaces.*'),
                ],
                [
                    'title' => 'Plans',
                    'icon' => 'receipt',
                    'url' => route('account.plans.index'),
                    'active' => request()->routeIs('account.plans.*'),
                ],
                [
                    'title' => 'Subscription Types',
                    'icon' => 'tags',
                    'url' => route('account.subscription-types.index'),
                    'active' => request()->routeIs('account.subscription-types.*'),
                ],
                [
                    'title' => 'Reservation Statuses',
                    'icon' => 'list-checks',
                    'url' => route('account.reservation-statuses.index'),
                    'active' => request()->routeIs('account.reservation-statuses.*'),
                ],
                [
                    'title' => 'Payment Methods',
                    'icon' => 'credit-card',
                    'url' => route('account.payment-methods.index'),
                    'active' => request()->routeIs('account.payment-methods.*'),
                ],
                [
                    'title' => 'Income Categories',
                    'icon' => 'tags',
                    'url' => route('account.income-categories.index'),
                    'active' => request()->routeIs('account.income-categories.*'),
                ],
                [
                    'title' => 'Companies',
                    'icon' => 'briefcase',
                    'url' => route('account.companies.index'),
                    'active' => request()->routeIs('account.companies.*') && !request()->routeIs('account.companies.create'),
                ],
                [
                    'title' => 'Jobs',
                    'icon' => 'clipboard-list',
                    'url' => route('account.jobs.index'),
                    'active' => request()->routeIs('account.jobs.*') && !request()->routeIs('account.jobs.create'),
                ],
                [
                    'title' => 'Employee Positions',
                    'icon' => 'award',
                    'url' => route('account.employee-positions.index'),
                    'active' => request()->routeIs('account.employee-positions.*'),
                ],
                [
                    'title' => 'Employee Shifts',
                    'icon' => 'clock',
                    'url' => route('account.employee-shifts.index'),
                    'active' => request()->routeIs('account.employee-shifts.*'),
                ],
            ],
        ],
    ];
@endphp

@include('layoutmodule::partials.menu', ['items' => $items])
