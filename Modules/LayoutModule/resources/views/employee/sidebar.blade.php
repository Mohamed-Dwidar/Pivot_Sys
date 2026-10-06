@php
    $items = [
        ['divider' => 'Main'],
        [
            'title' => 'My Profile',
            'icon' => 'contact',
            'url' => route('employee.dashboard'),
            'active' => request()->routeIs('employee.dashboard'),
        ],

        ['divider' => 'Settings'],
        [
            'title' => 'Change Password',
            'icon' => 'key-round',
            'url' => route('user.account.edit'),
            'active' => request()->routeIs('user.account.*'),
        ],
    ];
@endphp

@include('layoutmodule::partials.menu', ['items' => $items])
