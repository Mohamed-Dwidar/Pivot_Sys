@php
    /*
     * The employee sidebar (2026-10-13): their daily pages only, direct links (Members since 2026-10-14);
     * Change Password stays in the header menu (employee/main $menuLinks).
     */
    $items = [
        ['divider' => 'Daily'],
        [
            'title' => 'Dashboard',
            'icon' => 'gauge-circle',
            'url' => route('employee.dashboard'),
            'active' => request()->routeIs('employee.dashboard'),
        ],
        [
            'title' => 'Reservations',
            'icon' => 'calendar-check',
            'url' => route('employee.reservations.index'),
            'active' => request()->routeIs('employee.reservations.*'),
        ],
        [
            'title' => 'Payments',
            'icon' => 'wallet',
            'url' => route('employee.payments.index'),
            'active' => request()->routeIs('employee.payments.*'),
        ],
        [
            'title' => 'Expenses',
            'icon' => 'banknote',
            'url' => route('employee.expenses.index'),
            'active' => request()->routeIs('employee.expenses.*'),
        ],
        [
            'title' => 'Packages',
            'icon' => 'package',
            'url' => route('employee.packages.index'),
            'active' => request()->routeIs('employee.packages.*'),
        ],
        [
            'title' => 'Members',
            'icon' => 'users',
            'url' => route('employee.members.index'),
            'active' => request()->routeIs('employee.members.*'),
        ],
        [
            'title' => 'My Shift',
            'icon' => 'lock',
            'url' => route('employee.shift.index'),
            'active' => request()->routeIs('employee.shift.*'),
        ],
    ];
@endphp

@include('layoutmodule::partials.menu', ['items' => $items])
