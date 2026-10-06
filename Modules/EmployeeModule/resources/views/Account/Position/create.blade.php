@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Add Position
@endsection

@section('content')
    <form method="POST" action="{{ route('account.employee-positions.store') }}" data-ajax
        data-confirm="The position will be added to your employee positions list."
        data-confirm-title="Add this position?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('employeemodule::Account.Position.partials.form', ['position' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.employee-positions.index')])
    </form>
@endsection
