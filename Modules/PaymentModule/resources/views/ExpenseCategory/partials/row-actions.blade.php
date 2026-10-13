@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('account.expense-categories.edit', $category->id), 'modal' => true],
    ],
    'actions' => [['view' => 'paymentmodule::ExpenseCategory.partials.delete-form', 'data' => ['category' => $category]]],
])
