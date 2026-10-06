@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'View', 'icon' => 'eye', 'url' => route('account.members.show', $member->id), 'modal' => true],
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('account.members.edit', $member->id), 'modal' => true],
    ],
    'actions' => [['view' => 'membermodule::Account.partials.delete-form', 'data' => ['member' => $member]]],
])
