@php
    /*
     * The employee sidebar (2026-10-13): their daily pages only, direct links (Members since 2026-10-14; order + Payments = Cash In & Cash Out, no Expenses item: 2026-10-14);
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
            'title' => 'Packages',
            'icon' => 'package',
            'url' => route('employee.packages.index'),
            'active' => request()->routeIs('employee.packages.*'),
        ],
        [
            'title' => 'Payments',
            'icon' => 'wallet',
            'url' => route('employee.payments.index'),
            // Cash In + Cash Out (2026-10-14): the expense pages too (/expenses → the Cash Out tab)
            'active' => request()->routeIs('employee.payments.*', 'employee.expenses.*'),
        ],
        [
            'title' => 'My Shift',
            'icon' => 'lock',
            'url' => route('employee.shift.index'),
            'active' => request()->routeIs('employee.shift.*'),
        ],
        [
            'title' => 'Members',
            'icon' => 'users',
            'url' => route('employee.members.index'),
            'active' => request()->routeIs('employee.members.*'),
        ],
    ];
@endphp

@include('layoutmodule::partials.menu', ['items' => $items])
