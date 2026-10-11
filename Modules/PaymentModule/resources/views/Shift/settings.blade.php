@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

{{-- when employees are reminded to close their shift (never a block, docs/payments-plan.md §2b) --}}
@section('title')
    Shift Reminders
@endsection

@section('content')
    <form method="POST" action="{{ route('account.shift-settings.update') }}" data-ajax
        data-confirm="The new reminder times apply to all your employees."
        data-confirm-title="Save the settings?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            @include('layoutmodule::partials.field', ['name' => 'shift_reminder_hours', 'label' => 'Remind After (hours)', 'type' => 'number', 'required' => true,
                'value' => $account->shift_reminder_hours, 'attrs' => 'min="1" max="168" step="1"',
                'hint' => 'An employee whose oldest payment not closed is older than this sees the "close your shift" popup (on login / any action).'])
            @include('layoutmodule::partials.field', ['name' => 'shift_snooze_hours', 'label' => 'Remind Again After (hours)', 'type' => 'number', 'required' => true,
                'value' => $account->shift_snooze_hours, 'attrs' => 'min="1" max="48" step="1"',
                'hint' => 'After "I\'m still working" (a reason is required), the popup comes back after this.'])
        </div>
        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.day-closings.index')])
    </form>
@endsection
