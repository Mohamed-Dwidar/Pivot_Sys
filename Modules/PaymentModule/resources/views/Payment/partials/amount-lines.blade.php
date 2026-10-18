{{--
    The money of a reservation / package on two lines (user 2026-10-18): Amount - Discount (only when there is one) - Net Amount
    (bold), then Paid (green) - Remaining (red when owed; Credit in blue when negative). Used by the reservation view and the Add Cash In
    details (Payment/partials/payable-details).
    @include('paymentmodule::Payment.partials.amount-lines', ['payable' => $reservation, 'kind' => 'reservation', 'paidLine' => true])
    $paidLine: false = the first line only (a reservation inside a package / not counted is paid elsewhere).
--}}
@php
    $price = (float) $payable->amount;
    $net = $kind === 'reservation' ? (float) $payable->net_amount : (float) $payable->after_discount;
    $discount = round($price - $net, 2);
    $percentage = rtrim(rtrim(number_format((float) $payable->discount_percentage, 2), '0'), '.');
    $extras = method_exists($payable, 'extrasTotal') ? $payable->extrasTotal() : 0;
@endphp
<dl class="detail-list amount-lines">
    <div><dt>Amount</dt><dd>{{ number_format($price, 2) }}</dd></div>
    @if ($discount > 0)
        <div><dt>Discount</dt><dd>{{ number_format($discount, 2) }} ({{ $percentage }}%)</dd></div>
    @endif
    <div><dt>Net Amount</dt><dd class="font-bold">{{ number_format($net, 2) }}
        @if ($extras != 0)
            <span class="block text-xs font-normal text-slate-500">+ extras {{ number_format($extras, 2) }} = due {{ number_format($payable->payableAmount(), 2) }}</span>
        @endif
    </dd></div>
</dl>
@if ($paidLine ?? true)
    <dl class="detail-list amount-lines mt-4">
        <div><dt>Paid</dt><dd class="font-bold text-success">{{ number_format($payable->paid_amount, 2) }}</dd></div>
        <div><dt>{{ $payable->remaining < 0 ? 'Credit' : 'Remaining' }}</dt><dd class="font-bold {{ $payable->remaining > 0 ? 'text-danger' : ($payable->remaining < 0 ? 'text-info' : '') }}">{{ number_format(abs($payable->remaining), 2) }}</dd></div>
    </dl>
@endif
