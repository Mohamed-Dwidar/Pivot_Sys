@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Reopen {{ $closing->date->format(\Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT) }}
@endsection

{{-- the owner only: the day's payments can be changed again, the reason is kept --}}
@section('content')
    <form method="POST" action="{{ route('account.day-closings.reopen.store', $closing->id) }}" data-ajax data-row="{{ $closing->id }}"
        data-confirm="The payments of this day can be added, changed and deleted again until it is closed again."
        data-confirm-title="Reopen this day?"
        data-confirm-button="Reopen"
        data-confirm-variant="danger">
        @csrf
        @include('layoutmodule::partials.field', ['name' => 'reopen_reason', 'label' => 'Reason', 'type' => 'textarea', 'required' => true,
            'attrs' => 'maxlength="255"', 'hint' => 'Why the day is opened again (kept with the closing).'])
        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.day-closings.show', $closing->id)])
    </form>
@endsection
