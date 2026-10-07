@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    Add Reservation
@endsection

@section('modal-size', 'xl')

@section('content')
    <form method="POST" action="{{ route($area . '.reservations.store') }}" data-ajax data-reservation-form
        data-confirm="The reservation will be added."
        data-confirm-title="Add this reservation?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('reservationmodule::Reservation.partials.form', ['reservation' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route($area . '.reservations.index')])
    </form>
@endsection
