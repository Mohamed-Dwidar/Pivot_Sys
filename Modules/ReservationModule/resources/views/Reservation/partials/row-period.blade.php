{{--
    The list Period column, by the subscription type (docs/reservations-list-plan.md, columns 2026-10-13):
    full day → one date + day "31-12-2026 Mon"; auto renew / contract → "From 04-10-2027" + "Ends 03-10-2028" (two lines);
    otherwise two lines: same day "31-12-2026 Mon" / "09:00 AM → 05:00 PM",
    other days "31-12-2026 Mon | 09:00 AM →" / "01-01-2027 Tue | 05:00 PM".
--}}
@php
    $date = \Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT;
    // + the day name (Mon, Tue ...)
    $day = $date . ' D';
    $time = \Modules\ReservationModule\app\Models\Reservation::TIME_FORMAT;
    $type = $reservation->subscriptionType;
    $start = $reservation->start_at;
    $end = $reservation->end_at;
@endphp
@if (!$start)
    -
@elseif ($type?->full_day)
    {{ $start->format($day) }}
@elseif ($type?->auto_renew || $type?->is_contract)
    <div>From {{ $start->format($date) }}</div>
    <div class="text-slate-500">Ends {{ $end?->format($date) ?? '-' }}</div>
@elseif ($end && $start->isSameDay($end))
    <div>{{ $start->format($day) }}</div>
    <div class="text-slate-500">{{ $start->format($time) }} → {{ $end->format($time) }}</div>
@else
    <div>{{ $start->format($day . ' | ' . $time) }} →</div>
    <div class="text-slate-500">{{ $end?->format($day . ' | ' . $time) ?? '-' }}</div>
@endif
{{-- (kept for later: the Subscriptions page selects "renewed") --}}
@if (array_key_exists('renewed', $reservation->getAttributes()))
    <div class="text-xs">
        @include('reservationmodule::Subscription.partials.days')
        <span class="badge {{ $reservation->renewed ? 'badge-status-success' : 'badge-status-warning' }}">{{ $reservation->renewed ? 'Renewed' : 'Not renewed' }}</span>
    </div>
@endif
