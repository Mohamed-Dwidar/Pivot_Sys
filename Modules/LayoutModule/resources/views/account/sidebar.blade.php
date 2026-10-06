@php
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
    ];
@endphp

@include('layoutmodule::partials.menu', ['items' => $items])
