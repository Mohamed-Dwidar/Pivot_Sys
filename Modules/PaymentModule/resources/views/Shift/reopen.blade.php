@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Reopen the Shift of {{ $closing->user?->userable?->displayName() ?? '-' }}
@endsection

{{-- the account only: the shift's payments can be changed again (they stay in it), the reason is logged --}}
@section('content')
    <form method="POST" action="{{ route('account.shifts.reopen.store', $closing->id) }}" data-ajax
        data-confirm="The payments of this shift can be changed and deleted again until you close it again. The days they are dated on can't be closed meanwhile."
        data-confirm-title="Reopen this shift?"
        data-confirm-button="Reopen"
        data-confirm-variant="danger">
        @csrf
        @include('paymentmodule::Shift.partials.back')
        @include('layoutmodule::partials.field', ['name' => 'reopen_reason', 'label' => 'Reason', 'type' => 'textarea', 'required' => true,
            'attrs' => 'maxlength="255"', 'hint' => 'Why the shift is opened again (kept in its history).'])
        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.shifts.show', $closing->id)])
    </form>
@endsection
