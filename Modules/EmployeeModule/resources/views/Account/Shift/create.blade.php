@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Add Shift
@endsection

@section('content')
    <form method="POST" action="{{ route('account.employee-shifts.store') }}" data-ajax
        data-confirm="The shift will be added to your employee shifts list."
        data-confirm-title="Add this shift?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('employeemodule::Account.Shift.partials.form', ['shift' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.employee-shifts.index')])
    </form>
@endsection
