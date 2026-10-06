@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'View', 'icon' => 'eye', 'url' => route('account.subscription-types.show', $subscriptionType->id), 'modal' => true],
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('account.subscription-types.edit', $subscriptionType->id), 'modal' => true],
    ],
    'actions' => [['view' => 'spacemodule::Account.SubscriptionType.partials.delete-form', 'data' => ['subscriptionType' => $subscriptionType]]],
])
