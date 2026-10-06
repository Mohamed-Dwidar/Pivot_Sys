@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Edit Employee: {{ $employee->name }}
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route('account.employees.update', $employee->id) }}" enctype="multipart/form-data" data-ajax data-row="{{ $employee->id }}"
        data-confirm="The changes to &quot;{{ $employee->name }}&quot; will be saved."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('employeemodule::Account.Employee.partials.form', ['employee' => $employee])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.employees.show', $employee->id)])
    </form>
@endsection
