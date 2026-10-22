{{--
    The list "Renewal" column (the Renewal filter, 2026-10-23; Reservation::scopeRenewalStatus()): the period's renewal date and
    what happened, with the Schedule page texts / actions (Schedule/partials/renewal-row) — renewed = its renewal exists AND is paid:
    Left (+ Undo) | a renewal still owing → "Renewal created · Due X" + Pay | a paid renewal → ✓ Renewed → its end |
    no renewal → Not renewed + Renew, Mark left (+ Pay its own debt). $reservation (renewedBy eager loaded).
--}}
@php
    $area = request()->routeIs('employee.*') ? 'employee' : 'account';
    $dateFormat = \Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT;
    $renewal = $reservation->renewedBy;
    $owes = fn ($row) => $row && $row->canTakePayments() && $row->remaining > 0;
    $payUrl = fn ($row) => route($area . '.payments.create', ['payable' => 'reservation', 'payable_id' => $row->id]);
@endphp
<div class="whitespace-nowrap">{{ $reservation->renewalDate()?->format($dateFormat) ?? '-' }}</div>
<div class="renewal-cell">
    @if ($reservation->left_at)
        <span class="badge badge-status-slate">Left</span>
        @include('reservationmodule::Reservation.partials.left-form', ['undo' => true])
    @elseif ($renewal && $owes($renewal))
        <a href="{{ route($area . '.reservations.show', $renewal->id) }}" data-modal class="text-warning font-medium">Renewal created · Due {{ number_format($renewal->remaining, 2) }}</a>
        <a href="{{ $payUrl($renewal) }}" data-modal class="btn btn-primary btn-sm"><i data-lucide="wallet"></i> Pay</a>
    @elseif ($renewal)
        <a href="{{ route($area . '.reservations.show', $renewal->id) }}" data-modal class="text-success font-medium"><i data-lucide="check" class="inline h-4 w-4"></i> Renewed → {{ $renewal->realEnd()?->format($dateFormat) ?? '-' }}</a>
    @else
        <span class="text-danger font-medium">Not renewed</span>
        <a href="{{ route($area . '.reservations.create', ['renew' => $reservation->id]) }}" data-modal class="btn btn-primary btn-sm"><i data-lucide="refresh-cw"></i> Renew</a>
        @if ($owes($reservation))
            <a href="{{ $payUrl($reservation) }}" data-modal class="btn btn-secondary btn-sm"><i data-lucide="wallet"></i> Pay</a>
        @endif
        @include('reservationmodule::Reservation.partials.left-form')
    @endif
</div>
