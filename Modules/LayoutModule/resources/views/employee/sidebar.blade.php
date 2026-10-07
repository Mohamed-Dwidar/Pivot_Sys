@php
    $items = [
        ['divider' => 'Main'],
        [
            'title' => 'My Profile',
            'icon' => 'contact',
            'url' => route('employee.dashboard'),
            'active' => request()->routeIs('employee.dashboard'),
        ],

        ['divider' => 'Work'],
        [
            'title' => 'Reservations',
            'icon' => 'calendar-check',
            'url' => route('employee.reservations.index'),
            'active' => request()->routeIs('employee.reservations.*'),
        ],
        [
            'title' => 'Packages',
            'icon' => 'package',
            'url' => route('employee.packages.index'),
            'active' => request()->routeIs('employee.packages.*'),
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
