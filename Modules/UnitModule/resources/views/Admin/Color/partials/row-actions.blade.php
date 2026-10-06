@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('admin.colors.edit', $color->id), 'modal' => true],
    ],
    'actions' => [['view' => 'unitmodule::Admin.Color.partials.delete-form', 'data' => ['color' => $color]]],
])
