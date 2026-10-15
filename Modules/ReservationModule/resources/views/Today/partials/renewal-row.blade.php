{{--
    One renewal row ($reservation, $kind today | overdue | next): member, place, its renewal date (the day after its last day,
    Reservation::renewalDate()), the period's amount (net), ✓ Renewed → end / Renew (+ Mark left on the overdue ones)
--}}
@php
    $renewsOn = $reservation->renewalDate();
    $days = (int) $date->diffInDays($renewsOn, false);
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
        @if ($kind === 'today')
            renews {{ $isToday ? 'today' : 'this day' }}
        @elseif ($kind === 'overdue')
            due since {{ $renewsOn->format(\Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT) }} <span class="text-danger">({{ -$days }} {{ Str::plural('day', -$days) }})</span>
        @elseif ($days === 1 && $isToday)
            renews tomorrow
        @else
            renews {{ $renewsOn->format(\Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT) }} <span class="text-slate-500">(in {{ $days }} {{ Str::plural('day', $days) }})</span>
        @endif
    </td>
    {{-- the period's net amount (a renewal starts from the same amount); muted once renewed --}}
    <td class="today-renewals__amount {{ $renewal ? 'text-slate-400' : '' }}">{{ number_format($reservation->net_amount, 2) }}</td>
    <td class="today-renewals__actions">
        @if ($renewal)
            <a href="{{ route($area . '.reservations.show', $renewal->id) }}" data-modal class="text-success font-medium"><i data-lucide="check" class="inline h-4 w-4"></i> Renewed → {{ $renewal->realEnd()?->format(\Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT) ?? '-' }}</a>
        @else
            <a href="{{ route($area . '.reservations.create', ['renew' => $reservation->id]) }}" data-modal class="btn btn-primary btn-sm"><i data-lucide="refresh-cw"></i> Renew</a>
            @if ($kind === 'overdue')
                @include('reservationmodule::Reservation.partials.left-form')
            @endif
        @endif
    </td>
</tr>
