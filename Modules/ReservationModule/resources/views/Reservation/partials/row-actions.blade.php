@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'View', 'icon' => 'eye', 'url' => route($area . '.reservations.show', $reservation->id), 'modal' => true],
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route($area . '.reservations.edit', $reservation->id), 'modal' => true],
        // not inside a package (paid through the package)
        // not counted (cancelled, pending ...): back to the default counted status (popup, the slot is checked)
        // a contract period: the next period, pre-filled (the Subscriptions tab)
        ...($reservation->isContract() && $reservation->isCounted() ? [['label' => 'Renew', 'icon' => 'refresh-cw', 'url' => route($area . '.reservations.create', ['renew' => $reservation->id]), 'modal' => true]] : []),
        ...(!$reservation->isCounted() ? [['label' => 'Restore', 'icon' => 'rotate-ccw', 'url' => route($area . '.reservations.restore', $reservation->id), 'modal' => true]] : []),
        ...($reservation->canTakePayments() ? [['label' => 'Add Cash In', 'icon' => 'wallet', 'url' => route($area . '.payments.create', ['payable' => 'reservation', 'payable_id' => $reservation->id]), 'modal' => true]] : []),
    ],
    'actions' => [['view' => 'reservationmodule::Reservation.partials.delete-form', 'data' => ['reservation' => $reservation]]],
])
