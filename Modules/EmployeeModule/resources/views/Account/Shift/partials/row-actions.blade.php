@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('account.employee-shifts.edit', $shift->id), 'modal' => true],
    ],
    'actions' => [['view' => 'employeemodule::Account.Shift.partials.delete-form', 'data' => ['shift' => $shift]]],
])
