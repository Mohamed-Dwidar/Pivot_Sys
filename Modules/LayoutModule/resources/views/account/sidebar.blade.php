@php
    // Settings › General: withholding tax off → no Withholding Certificates item
    $withholdingOn = Auth::user()->userable->withholdingEnabled();
    $items = [
        ['divider' => 'Main'],
        [
            'title' => 'Dashboard',
            'icon' => 'gauge-circle',
            'url' => route('account.dashboard'),
            'active' => request()->routeIs('account.dashboard'),
        ],

        ['divider' => 'Management'],
        [
            'title' => 'Reservations',
            'icon' => 'calendar-check',
            'children' => [
                [
                    'title' => 'All Reservations',
                    'icon' => 'list',
                    'url' => route('account.reservations.index'),
                    'active' => request()->routeIs('account.reservations.*'),
                ],
                [
                    'title' => 'Packages',
                    'icon' => 'package',
                    'url' => route('account.packages.index'),
                    'active' => request()->routeIs('account.packages.*'),
                ],
                [
                    'title' => 'Statuses',
                    'icon' => 'list-checks',
                    'url' => route('account.reservation-statuses.index'),
                    'active' => request()->routeIs('account.reservation-statuses.*'),
                ],
            ],
        ],

        [
            'title' => 'Payments',
            'icon' => 'wallet',
            'children' => [
                [
                    'title' => 'All Payments',
                    'icon' => 'list',
                    'url' => route('account.payments.index'),
                    'active' => request()->routeIs('account.payments.*'),
                ],
                ...($withholdingOn ? [[
                    'title' => 'Withholding Certificates',
                    'icon' => 'file-badge',
                    'url' => route('account.withholding-certificates.index'),
                    'active' => request()->routeIs('account.withholding-certificates.*'),
                ]] : []),
                [
                    'title' => 'My Shift',
                    'icon' => 'lock',
                    'url' => route('account.shift.index'),
                    'active' => request()->routeIs('account.shift.*'),
                ],
                [
                    'title' => 'Daily Summary',
                    'icon' => 'calendar-days',
                    'url' => route('account.daily-summary.index'),
                    'active' => request()->routeIs('account.daily-summary.*', 'account.shifts.*'),
                ],
            ],
        ],

        [
            'title' => 'Members',
            'icon' => 'users',
            'url' => route('account.members.index'),
            'active' => request()->routeIs('account.members.*') && !request()->routeIs('account.members.create'),
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
            'title' => 'Employees',
            'icon' => 'contact',
            'children' => [
                [
                    'title' => 'All Employees',
                    'icon' => 'list',
                    'url' => route('account.employees.index'),
                    'active' => request()->routeIs('account.employees.*'),
                ],
                [
                    'title' => 'Positions',
                    'icon' => 'award',
                    'url' => route('account.employee-positions.index'),
                    'active' => request()->routeIs('account.employee-positions.*'),
                ],
                [
                    'title' => 'Shifts',
                    'icon' => 'clock',
                    'url' => route('account.employee-shifts.index'),
                    'active' => request()->routeIs('account.employee-shifts.*'),
                ],
            ],
        ],
        [
            'title' => 'Spaces',
            'icon' => 'layout-grid',
            'children' => [
                [
                    'title' => 'All Spaces',
                    'icon' => 'list',
                    'url' => route('account.spaces.index'),
                    'active' => request()->routeIs('account.spaces.*'),
                ],
                [
                    'title' => 'Subscription Types',
                    'icon' => 'tags',
                    'url' => route('account.subscription-types.index'),
                    'active' => request()->routeIs('account.subscription-types.*'),
                ],
            ],
        ],
        [
            'title' => 'Plans',
            'icon' => 'receipt',
            'url' => route('account.plans.index'),
            'active' => request()->routeIs('account.plans.*'),
        ],
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
            ],
        ],
    ];
@endphp

@include('layoutmodule::partials.menu', ['items' => $items])
