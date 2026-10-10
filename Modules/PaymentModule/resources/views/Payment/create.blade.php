@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    Add Payment
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route($area . '.payments.store') }}" data-ajax data-payment-form
        data-confirm="The payment will be added. Only the account owner can change or delete it later."
        data-confirm-title="Add this payment?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('paymentmodule::Payment.partials.form')

        @include('layoutmodule::partials.form-actions', ['cancel' => route($area . '.payments.index')])
    </form>
@endsection
