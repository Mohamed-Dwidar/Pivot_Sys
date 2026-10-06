@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Add Plan
@endsection

@section('content')
    <form method="POST" action="{{ route('account.plans.store') }}" data-ajax
        data-confirm="The plan will be added, then you can assign it to your spaces and units."
        data-confirm-title="Add this plan?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('planmodule::Account.partials.form', ['plan' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.plans.index')])
    </form>
@endsection
