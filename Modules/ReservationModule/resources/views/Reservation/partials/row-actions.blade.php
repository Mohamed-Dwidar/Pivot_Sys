@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'View', 'icon' => 'eye', 'url' => route($area . '.reservations.show', $reservation->id), 'modal' => true],
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route($area . '.reservations.edit', $reservation->id), 'modal' => true],
        // not inside a package (paid through the package)
        ...($reservation->canTakePayments() ? [['label' => 'Add Payment', 'icon' => 'wallet', 'url' => route($area . '.payments.create', ['payable' => 'reservation', 'payable_id' => $reservation->id]), 'modal' => true]] : []),
    ],
    'actions' => [['view' => 'reservationmodule::Reservation.partials.delete-form', 'data' => ['reservation' => $reservation]]],
])
