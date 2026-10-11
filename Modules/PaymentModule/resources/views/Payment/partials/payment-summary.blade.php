{{-- the payment being edited / refunded / deleted (the top of those popups): number, type, date, amount, for --}}
<dl class="detail-list">
    <div><dt>Payment</dt><dd>#{{ $payment->payment_no }} <span class="badge {{ $payment->type_badge_class }} ml-1">{{ $payment->type_label }}</span></dd></div>
    <div><dt>Date / Amount</dt><dd>{{ $payment->paid_at?->format(\Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT) }} · {{ number_format($payment->signed_amount, 2) }}</dd></div>
    <div><dt>Member</dt><dd>{{ $payment->member?->name ?? '-' }}</dd></div>
    <div><dt>For</dt><dd>{{ $payment->payable ? ($payableName ?? '#' . $payment->payable_id) : ($payment->incomeCategory?->name ?? 'Other income') }}</dd></div>
</dl>
