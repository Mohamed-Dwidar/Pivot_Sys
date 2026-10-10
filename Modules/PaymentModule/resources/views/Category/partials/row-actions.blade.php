@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('account.income-categories.edit', $category->id), 'modal' => true],
    ],
    'actions' => [['view' => 'paymentmodule::Category.partials.delete-form', 'data' => ['category' => $category]]],
])
