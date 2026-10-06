@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Add Member
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route('account.members.store') }}" data-ajax
        data-confirm="The member will be added to your members list."
        data-confirm-title="Add this member?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('membermodule::Account.partials.form', ['member' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.members.index')])
    </form>
@endsection
