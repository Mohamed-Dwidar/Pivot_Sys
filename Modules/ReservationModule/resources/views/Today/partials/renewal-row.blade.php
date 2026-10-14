{{-- One renewal row ($reservation, $kind due | ended | next): member, place, when it ends, ✓ Renewed → end / Renew (+ Mark left) --}}
@php
    $end = ($reservation->realEnd() ?? $reservation->start_at)->copy()->startOfDay();
    $days = (int) $date->diffInDays($end, false);
    $renewal = $reservation->renewedBy;
@endphp
<tr>
    <td>
        <a href="{{ route($area . '.reservations.show', $reservation->id) }}" data-modal class="font-medium">{{ $reservation->member?->name ?? '-' }}</a>
        @if ($reservation->member?->phone)
            <div class="text-xs text-slate-500">{{ $reservation->member->phone }}</div>
        @endif
    </td>
    <td>
        {{ $reservation->unit?->name ?? '-' }}
        <div class="text-xs text-slate-500">{{ $reservation->space?->name }}</div>
    </td>
    <td class="whitespace-nowrap">
        @if ($kind === 'due')
            ends {{ $isToday ? 'today' : 'this day' }}
        @elseif ($kind === 'ended')
            ended {{ $end->format(\Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT) }} <span class="text-danger">({{ -$days }} {{ Str::plural('day', -$days) }})</span>
        @else
            ends {{ $end->format(\Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT) }} <span class="text-slate-500">({{ $days }} {{ Str::plural('day', $days) }})</span>
        @endif
    </td>
    <td class="today-renewals__actions">
        @if ($renewal)
            <a href="{{ route($area . '.reservations.show', $renewal->id) }}" data-modal class="text-success font-medium"><i data-lucide="check" class="inline h-4 w-4"></i> Renewed → {{ $renewal->realEnd()?->format(\Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT) ?? '-' }}</a>
        @else
            <a href="{{ route($area . '.reservations.create', ['renew' => $reservation->id]) }}" data-modal class="btn btn-primary btn-sm"><i data-lucide="refresh-cw"></i> Renew</a>
            @if ($kind === 'ended')
                @include('reservationmodule::Reservation.partials.left-form')
            @endif
        @endif
    </td>
</tr>
