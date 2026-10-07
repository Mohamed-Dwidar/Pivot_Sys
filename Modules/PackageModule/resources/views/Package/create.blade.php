@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    Add Package
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route($area . '.packages.store') }}" data-ajax
        data-confirm="The package will be added for the selected member."
        data-confirm-title="Add this package?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('packagemodule::Package.partials.form', ['package' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route($area . '.packages.index')])
    </form>
@endsection
