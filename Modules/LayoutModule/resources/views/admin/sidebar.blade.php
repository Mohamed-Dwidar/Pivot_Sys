@php
    $pendingList = request()->routeIs('admin.accounts.index') && request('status') == 'pending';

    $items = [
        ['divider' => 'Main'],
        [
            'title' => 'Dashboard', 'icon' => 'gauge-circle',
            'url' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard'),
        ],

        ['divider' => 'Management'],
        [
            'title' => 'Accounts', 'icon' => 'building-2',
            'children' => [
                [
                    'title' => 'All Accounts', 'icon' => 'list',
                    'url' => route('admin.accounts.index'),
                    'active' => request()->routeIs('admin.accounts.*') && !request()->routeIs('admin.accounts.create') && !$pendingList,
                ],
                [
                    'title' => 'Pending Requests', 'icon' => 'user-check',
                    'url' => route('admin.accounts.index', ['status' => 'pending']),
                    'active' => $pendingList, 'badge' => $pendingAccountsCount ?? 0, 'counter' => 'accounts-pending',
                ],
                [
                    'title' => 'Add Account', 'icon' => 'plus-circle',
                    'url' => route('admin.accounts.create'), 'active' => request()->routeIs('admin.accounts.create'),
                ],
            ],
        ],

        ['divider' => 'Settings'],
        [
            'title' => 'Colors', 'icon' => 'palette',
            'url' => route('admin.colors.index'), 'active' => request()->routeIs('admin.colors.*'),
        ],
        [
            'title' => 'Account Settings', 'icon' => 'settings',
            'children' => [
                [
                    'title' => 'My Account', 'icon' => 'user-cog',
                    'url' => route('admin.profile.edit'), 'active' => request()->routeIs('admin.profile.*'),
                ],
            ],
        ],
    ];
@endphp

@include('layoutmodule::partials.menu', ['items' => $items])
