@php
    $items = [
        ['divider' => 'Main'],
        [
            'title' => 'Dashboard', 'icon' => 'gauge-circle',
            'url' => route('account.dashboard'), 'active' => request()->routeIs('account.dashboard'),
        ],

        ['divider' => 'Settings'],
        [
            'title' => 'My Account', 'icon' => 'building-2',
            'children' => [
                [
                    'title' => 'My Profile', 'icon' => 'contact',
                    'url' => route('account.profile.edit'), 'active' => request()->routeIs('account.profile.*'),
                ],
                [
                    'title' => 'Login Details', 'icon' => 'key-round',
                    'url' => route('user.account.edit'), 'active' => request()->routeIs('user.account.*'),
                ],
            ],
        ],
    ];
@endphp

@include('layoutmodule::partials.menu', ['items' => $items])
