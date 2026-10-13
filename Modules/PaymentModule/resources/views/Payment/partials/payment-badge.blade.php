{{-- the payment status badge of a list row ("Partial" = the short label of partially paid); $payable: Payable + HasPayments --}}
<span class="badge {{ $payable->payment_status_badge_class }}">{{ $payable->payment_status === 'partial' ? 'Partial' : $payable->payment_status_label }}</span>
