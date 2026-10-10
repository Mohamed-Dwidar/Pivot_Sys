@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Add Payment Method
@endsection

@section('content')
    <form method="POST" action="{{ route('account.payment-methods.store') }}" data-ajax
        data-confirm="The payment method will be added to your payment methods list."
        data-confirm-title="Add this payment method?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('paymentmodule::Method.partials.form', ['method' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.payment-methods.index')])
    </form>
@endsection
