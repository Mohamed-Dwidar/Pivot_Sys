@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Add Employee
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route('account.employees.store') }}" enctype="multipart/form-data" data-ajax
        data-confirm="The employee will be added and can log in with the email and password."
        data-confirm-title="Add this employee?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('employeemodule::Account.Employee.partials.form', ['employee' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.employees.index')])
    </form>
@endsection
