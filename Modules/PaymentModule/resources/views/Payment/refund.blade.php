@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    Refund Payment #{{ $payment->payment_no }}
@endsection

@section('modal-size', 'lg')

{{-- the owner only: a new refund record (type refund) for the same reservation / package / income; the server checks the limit again --}}
@section('content')
    <form method="POST" action="{{ route('account.payments.refund.store', $payment->id) }}" data-ajax
        data-confirm="A refund will be added: the money goes back to the member."
        data-confirm-title="Add this refund?"
        data-confirm-button="Refund"
        data-confirm-variant="danger">
        @csrf

        @include('paymentmodule::Payment.partials.payment-summary')

        <div class="form-section mt-6">Refund</div>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            @include('layoutmodule::partials.field', ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true,
                'value' => max(0, min($refundable, $payment->amount)) ?: null,
                'attrs' => 'min="0.01" step="0.01" max="' . max(0, $refundable) . '"',
                'hint' => 'Up to ' . number_format(max(0, $refundable), 2) . ($payment->payable_type ? ' (the money received for it, after its refunds).' : ' (this payment).')])
            @include('layoutmodule::partials.field', ['name' => 'paid_at', 'label' => 'Date', 'type' => 'date', 'required' => true, 'value' => today()->format('Y-m-d')])
            @include('layoutmodule::partials.field', ['name' => 'payment_method_id', 'label' => 'Paid Back By', 'type' => 'select', 'required' => true, 'searchable' => true,
                'options' => ['' => '- Select -'] + $methods, 'value' => $payment->payment_method_id ?: null])
        </div>
        <div class="grid grid-cols-1 gap-5 mt-5 md:grid-cols-3">
            @include('layoutmodule::partials.field', ['name' => 'reference', 'label' => 'Reference', 'attrs' => 'maxlength="255"',
                'hint' => 'Empty: "Refund of #' . $payment->payment_no . '".'])
        </div>
        <div class="mt-5">
            @include('layoutmodule::partials.field', ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea'])
        </div>

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.payments.show', $payment->id)])
    </form>
@endsection
