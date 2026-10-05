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
            'title' => 'Members', 'icon' => 'users',
            'children' => [
                [
                    'title' => 'All Members', 'icon' => 'list',
                    'url' => route('account.members.index'),
                    'active' => request()->routeIs('account.members.*') && !request()->routeIs('account.members.create'),
                ],
                [
                    'title' => 'Add Member', 'icon' => 'user-plus',
                    'url' => route('account.members.create'), 'active' => request()->routeIs('account.members.create'),
                ],
            ],
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
    ];
@endphp

@include('layoutmodule::partials.menu', ['items' => $items])
