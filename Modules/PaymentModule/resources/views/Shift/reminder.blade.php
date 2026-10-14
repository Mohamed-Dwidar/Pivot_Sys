@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::employee.main')

{{--
    The "close your shift" reminder (middleware ShiftReminder: the open period is older than the account's setting).
    Never a block: "I'm Still Working" needs a reason, is logged, and the popup comes back after the snooze hours.
--}}
@section('title')
    Your Shift Is Still Open
@endsection

@section('content')
    <div class="mb-4">
        You have {{ $period['summary']['count'] }} cash in {{ Str::plural('entry', $period['summary']['count']) }} not closed since {{ $period['since']?->format('d-m-Y h:i A') ?? '-' }}
        ({{ number_format($period['summary']['cash_total'], 2) }} collected). Close your shift when you finish.
    </div>

    <div class="form-actions">
        <a href="{{ route('employee.shift.confirm') }}" data-modal class="btn btn-primary"><i data-lucide="lock"></i> Close My Shift Now</a>
    </div>

    <div class="form-section mt-8">Still working?</div>
    <form method="POST" action="{{ route('employee.shift.still-working') }}" data-ajax>
        @csrf
        <input type="hidden" name="action" value="still_working">
        @include('layoutmodule::partials.field', ['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea', 'required' => true,
            'attrs' => 'maxlength="255"', 'hint' => 'E.g. covering a colleague\'s shift (the account sees it).'])
        <div class="form-actions">
            <button type="submit" class="btn btn-secondary"><i data-lucide="clock"></i> I'm Still Working (Covering a Shift)</button>
        </div>
    </form>
@endsection
