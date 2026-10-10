@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'View', 'icon' => 'eye', 'url' => route($area . '.payments.show', $payment->id), 'modal' => true],
        ['label' => 'Receipt', 'icon' => 'printer', 'url' => route($area . '.payments.receipt', $payment->id)],
    ],
])
