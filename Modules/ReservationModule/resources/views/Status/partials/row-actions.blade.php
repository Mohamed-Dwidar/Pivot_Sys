@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('account.reservation-statuses.edit', $status->id), 'modal' => true],
    ],
    'actions' => [['view' => 'reservationmodule::Status.partials.delete-form', 'data' => ['status' => $status]]],
])
