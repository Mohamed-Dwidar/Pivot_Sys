@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'View', 'icon' => 'eye', 'url' => route('account.spaces.units.show', [$space->id, $unit->id])],
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('account.spaces.units.edit', [$space->id, $unit->id])],
    ],
    'actions' => [['view' => 'unitmodule::Account.Unit.partials.delete-form', 'data' => ['unit' => $unit]]],
])
