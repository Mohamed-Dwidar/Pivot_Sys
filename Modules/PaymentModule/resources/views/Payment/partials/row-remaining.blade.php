{{-- a reservation / package list cell: the remaining (a credit as +x) + its payment status badge ($payable: Payable + HasPayments) --}}
@if ($payable->canTakePayments())
    <div class="whitespace-nowrap">{{ $payable->remaining < 0 ? '+' . number_format(-$payable->remaining, 2) : number_format($payable->remaining, 2) }}</div>
@endif
<span class="badge {{ $payable->payment_status_badge_class }} mt-0.5">{{ $payable->payment_status_label }}</span>
