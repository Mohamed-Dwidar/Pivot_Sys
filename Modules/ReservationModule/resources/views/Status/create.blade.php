@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Add Status
@endsection

@section('content')
    <form method="POST" action="{{ route('account.reservation-statuses.store') }}" data-ajax
        data-confirm="The status will be added to your reservation statuses list."
        data-confirm-title="Add this status?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('reservationmodule::Status.partials.form', ['status' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.reservation-statuses.index')])
    </form>
@endsection
