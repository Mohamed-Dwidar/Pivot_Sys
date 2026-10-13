{{--
    The daily tabs (today / tomorrow / this week): the contract periods running then, collapsed into one row above the list
    (docs/reservations-list-plan.md). Sent with the rows (json slots.contracts, datatables.js keeps it open after a reload).
    $count, $contracts (at most $limit, ending soonest first)
--}}
@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@if ($count)
    <details class="contracts-summary">
        <summary>
            <i data-lucide="refresh-cw" class="h-4 w-4"></i> Active subscriptions ({{ $count }})
            <span class="text-xs text-slate-500">monthly contracts, one period each — click to show</span>
        </summary>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Member</th><th>Space &rsaquo; Unit</th><th>Period</th><th>Days Left</th><th>Paid / Remaining</th></tr></thead>
                <tbody>
                    @foreach ($contracts as $reservation)
                        <tr>
                            <td><a href="{{ route($area . '.reservations.show', $reservation->id) }}" data-modal class="font-medium">{{ $reservation->member?->name ?? '-' }}</a></td>
                            <td>@include('reservationmodule::Reservation.partials.row-place')</td>
                            <td class="whitespace-nowrap">{{ $reservation->period }}</td>
                            <td class="whitespace-nowrap">@include('reservationmodule::Subscription.partials.days')</td>
                            <td class="whitespace-nowrap">{{ number_format($reservation->paid_amount, 2) }} / @include('paymentmodule::Payment.partials.row-remaining', ['payable' => $reservation])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($count > $contracts->count())
            <div class="mt-2 text-xs text-slate-500">The first {{ $limit }} ending soonest; all of them are on the Subscriptions tab.</div>
        @endif
    </details>
@endif
