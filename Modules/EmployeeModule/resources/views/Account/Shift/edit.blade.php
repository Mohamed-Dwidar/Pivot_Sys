@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Edit Shift: {{ $shift->name }}
@endsection

@section('actions')
    @include('employeemodule::Account.Shift.partials.delete-form')
@endsection

@section('content')
    <form method="POST" action="{{ route('account.employee-shifts.update', $shift->id) }}" data-ajax data-row="{{ $shift->id }}"
        data-confirm="The changes to &quot;{{ $shift->name }}&quot; will be saved."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('employeemodule::Account.Shift.partials.form', ['shift' => $shift])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.employee-shifts.index')])
    </form>
@endsection
