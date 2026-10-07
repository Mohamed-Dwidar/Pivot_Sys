@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    Edit Reservation #{{ $reservation->id }}
@endsection

@section('modal-size', 'xl')

@section('content')
    <form method="POST" action="{{ route($area . '.reservations.update', $reservation->id) }}" data-ajax data-reservation-form data-row="{{ $reservation->id }}"
        data-confirm="The changes to the reservation of &quot;{{ $reservation->member?->name }}&quot; will be saved."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('reservationmodule::Reservation.partials.form', ['reservation' => $reservation])

        @include('layoutmodule::partials.form-actions', ['cancel' => route($area . '.reservations.show', $reservation->id)])
    </form>
@endsection
