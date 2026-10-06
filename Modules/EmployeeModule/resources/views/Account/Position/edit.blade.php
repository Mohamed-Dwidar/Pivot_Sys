@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Edit Position: {{ $position->name }}
@endsection

@section('actions')
    @include('employeemodule::Account.Position.partials.delete-form')
@endsection

@section('content')
    <form method="POST" action="{{ route('account.employee-positions.update', $position->id) }}" data-ajax data-row="{{ $position->id }}"
        data-confirm="The changes to &quot;{{ $position->name }}&quot; will be saved."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('employeemodule::Account.Position.partials.form', ['position' => $position])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.employee-positions.index')])
    </form>
@endsection
