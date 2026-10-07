@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Edit Status: {{ $status->name }}
@endsection

@section('actions')
    @include('reservationmodule::Status.partials.delete-form')
@endsection

@section('content')
    <form method="POST" action="{{ route('account.reservation-statuses.update', $status->id) }}" data-ajax data-row="{{ $status->id }}"
        data-confirm="The changes to &quot;{{ $status->name }}&quot; will be saved."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('reservationmodule::Status.partials.form', ['status' => $status])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.reservation-statuses.index')])
    </form>
@endsection
