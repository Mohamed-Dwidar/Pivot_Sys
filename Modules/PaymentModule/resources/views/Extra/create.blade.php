@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    Add Extra · {{ $package->name }}
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route($area . '.extras.store') }}" data-ajax
        data-confirm="It will be added to what the package owes."
        data-confirm-title="Add this to the package?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf
        <input type="hidden" name="package_id" value="{{ $package->id }}">

        @include('paymentmodule::Extra.partials.form')

        @include('layoutmodule::partials.form-actions', ['cancel' => route($area . '.packages.show', $package->id)])
    </form>
@endsection
