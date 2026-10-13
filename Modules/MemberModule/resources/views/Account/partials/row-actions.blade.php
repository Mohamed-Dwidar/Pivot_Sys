@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'View', 'icon' => 'eye', 'url' => route($area . '.members.show', $member->id), 'modal' => true],
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route($area . '.members.edit', $member->id), 'modal' => true],
    ],
    // delete: the account only (a member is deleted for good)
    'actions' => $area === 'account' ? [['view' => 'membermodule::Account.partials.delete-form', 'data' => ['member' => $member]]] : [],
])
