@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'View', 'icon' => 'eye', 'url' => route('account.plans.show', $plan->id), 'modal' => true],
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('account.plans.edit', $plan->id), 'modal' => true],
    ],
    'actions' => [['view' => 'planmodule::Account.partials.delete-form', 'data' => ['plan' => $plan]]],
])
