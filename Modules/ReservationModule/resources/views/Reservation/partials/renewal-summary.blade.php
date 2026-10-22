{{--
    The line under the reservations list filters with the "Renewal" filter (ReservationActions::renewalSummary()):
    the renewal dates, the count and what is owed — $totals: periods, own_due (the periods' own debt), renewal_due (renewals
    created, not paid), not_created (no renewal yet) + not_created_amount (their amount = the expected renewals).
--}}
@php($dateFormat = \Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT)
<div class="renewal-summary">
    <b>{{ \Modules\ReservationModule\app\Models\Reservation::RENEWAL_FILTERS[$status] }}</b>
    · renewal dates {{ $from->format($dateFormat) }} → {{ $to->format($dateFormat) }}
    · <b>{{ number_format($totals->periods) }}</b> {{ Str::plural('period', (int) $totals->periods) }}
    @if ($status === 'not_renewed')
        · Due <b class="text-danger">{{ number_format($totals->own_due + $totals->renewal_due, 2) }}</b>
        <span class="text-slate-500">(renewals created {{ number_format($totals->renewal_due, 2) }} + own {{ number_format($totals->own_due, 2) }})</span>
        @if ($totals->not_created > 0)
            · not renewed yet <b>{{ number_format($totals->not_created) }}</b> worth {{ number_format($totals->not_created_amount, 2) }}
        @endif
    @endif
</div>
