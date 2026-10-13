@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    {{ !empty($reservation) ? 'Renew Subscription' : 'Add Reservation' }}
@endsection

@section('modal-size', 'xl')

@section('content')
    <form method="POST" action="{{ route($area . '.reservations.store') }}" data-ajax data-reservation-form
        data-confirm="The reservation will be added."
        data-confirm-title="Add this reservation?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        {{-- $reservation: a renewal (not saved, pre-filled from a subscription) or null --}}
        @include('reservationmodule::Reservation.partials.form', ['reservation' => $reservation ?? null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route($area . '.reservations.index')])
    </form>
@endsection
