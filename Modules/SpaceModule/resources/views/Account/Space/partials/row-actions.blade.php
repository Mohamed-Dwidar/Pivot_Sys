@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'View', 'icon' => 'eye', 'url' => route('account.spaces.show', $space->id)],
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('account.spaces.edit', $space->id)],
        ['label' => 'Add Unit', 'icon' => 'plus-circle', 'url' => route('account.spaces.units.create', $space->id)],
    ],
    'actions' => [['view' => 'spacemodule::Account.Space.partials.delete-form', 'data' => ['space' => $space]]],
])
