@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
{{-- edit / refund / delete: the account owner only (employees add payments) --}}
@include('layoutmodule::partials.row-menu', [
    'links' => array_merge([
        ['label' => 'View', 'icon' => 'eye', 'url' => route($area . '.payments.show', $payment->id), 'modal' => true],
        ['label' => 'Receipt', 'icon' => 'printer', 'url' => route($area . '.payments.receipt', $payment->id)],
    ], $area !== 'account' ? [] : array_merge(
        [['label' => 'Edit', 'icon' => 'pencil', 'url' => route('account.payments.edit', $payment->id), 'modal' => true]],
        $payment->type === 'payment' ? [['label' => 'Refund', 'icon' => 'undo-2', 'url' => route('account.payments.refund', $payment->id), 'modal' => true]] : [],
        [['label' => 'Delete', 'icon' => 'trash-2', 'url' => route('account.payments.delete', $payment->id), 'modal' => true]],
    )),
])
