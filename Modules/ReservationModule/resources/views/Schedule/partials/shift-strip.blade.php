{{-- The logged in user's open shift (ShiftClosingService::openTotals(); hidden when nothing is open): cash in / out, Close --}}
@if ($shift)
    <div class="today-shift">
        <span class="font-medium">My shift</span>
        <span class="today-shift__sep">·</span>
        <span>open since {{ $shift['since']->isSameDay(now()) ? $shift['since']->format('h:i A') : $shift['since']->format('d-m-Y h:i A') }}</span>
        <span class="today-shift__sep">·</span>
        <span>Cash in <b class="text-success">{{ number_format($shift['cash_in'], 2) }}</b></span>
        <span class="today-shift__sep">·</span>
        <span>Cash out <b class="text-danger">{{ number_format($shift['cash_out'], 2) }}</b></span>
        <a href="{{ route($area . '.shift.confirm') }}" data-modal class="btn btn-secondary btn-sm today-shift__close"><i data-lucide="lock"></i> Close Shift</a>
    </div>
@endif
