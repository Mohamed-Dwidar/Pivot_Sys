{{-- My Closings "Payments": the dates of a closing's payments (paid_at: one date, or first → last) + how many (withMin / withMax) --}}
@php
    $format = \Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT;
    $from = $closing->payments_min_paid_at ? \Illuminate\Support\Carbon::parse($closing->payments_min_paid_at)->format($format) : null;
    $to = $closing->payments_max_paid_at ? \Illuminate\Support\Carbon::parse($closing->payments_max_paid_at)->format($format) : null;
@endphp
@if ($from)
    <span class="whitespace-nowrap">{{ $from }}</span>@if ($to !== $from) → <span class="whitespace-nowrap">{{ $to }}</span>@endif
@else
    -
@endif
<div class="text-xs text-slate-500">{{ $closing->count }} cash in</div>
