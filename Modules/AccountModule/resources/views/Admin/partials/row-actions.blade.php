@if ($account->trashed())
    @include('layoutmodule::partials.row-menu', [
        'actions' => [['view' => 'accountmodule::Admin.partials.restore-form', 'data' => ['account' => $account]]],
    ])
@else
    @include('layoutmodule::partials.row-menu', [
        'links' => [
            ['label' => 'View', 'icon' => 'eye', 'url' => route('admin.accounts.show', $account->id)],
            ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('admin.accounts.edit', $account->id)],
        ],
        'actions' => [['view' => 'accountmodule::Admin.partials.status-actions', 'data' => ['account' => $account]]],
    ])
@endif
