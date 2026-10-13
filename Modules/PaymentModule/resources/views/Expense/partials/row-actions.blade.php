@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
{{-- view + the receipt (anyone who can open it); edit / delete: the account owner only --}}
@include('layoutmodule::partials.row-menu', [
    'links' => array_merge(
        [['label' => 'View', 'icon' => 'eye', 'url' => route($area . '.expenses.show', $expense->id), 'modal' => true]],
        $expense->attachment ? [['label' => 'Receipt', 'icon' => 'paperclip', 'url' => route($area . '.expenses.attachment', $expense->id)]] : [],
        $area !== 'account' ? [] : [
            ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('account.expenses.edit', $expense->id), 'modal' => true],
            ['label' => 'Delete', 'icon' => 'trash-2', 'url' => route('account.expenses.delete', $expense->id), 'modal' => true],
        ],
    ),
])
