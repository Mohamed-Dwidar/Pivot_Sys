@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    Add Member
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route($area . '.members.store') }}" data-ajax
        data-confirm="The member will be added to your members list."
        data-confirm-title="Add this member?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('membermodule::Account.partials.form', ['member' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route($area . '.members.index')])
    </form>
@endsection
