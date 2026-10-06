@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'View', 'icon' => 'eye', 'url' => route('account.subscription-types.show', $subscriptionType->id)],
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('account.subscription-types.edit', $subscriptionType->id)],
    ],
    'actions' => [['view' => 'spacemodule::Account.SubscriptionType.partials.delete-form', 'data' => ['subscriptionType' => $subscriptionType]]],
])
