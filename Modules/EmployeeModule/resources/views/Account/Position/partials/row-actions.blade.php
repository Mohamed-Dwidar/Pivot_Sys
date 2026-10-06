@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('account.employee-positions.edit', $position->id), 'modal' => true],
    ],
    'actions' => [['view' => 'employeemodule::Account.Position.partials.delete-form', 'data' => ['position' => $position]]],
])
