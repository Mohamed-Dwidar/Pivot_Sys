{{--
    Payment receipt (PDF, mpdf: PaymentActions::receipt()). Not an ETA e-receipt (phase 2 = invoices / receipts).
    mpdf can't read custom.css (CSS variables), its styles are in public/assets/css/pdf.css (file path, no inline CSS).
--}}
@php($dateFormat = \Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT)
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $payment->payment_no }}</title>
    <link rel="stylesheet" href="{{ public_path('assets/css/pdf.css') }}">
</head>
<body>
    <table class="receipt-head">
        <tr>
            <td>
                @if ($account?->logo && is_file(public_path('uploads/accounts/' . $account->id . '/' . $account->logo)))
                    <img src="{{ public_path('uploads/accounts/' . $account->id . '/' . $account->logo) }}" class="receipt-logo">
                @endif
                <div class="receipt-account">{{ $account?->name }}</div>
                @if ($account?->phone)<div class="receipt-muted">{{ $account->phone }}</div>@endif
                @if ($account?->address)<div class="receipt-muted">{{ $account->address }}</div>@endif
            </td>
            <td class="receipt-title-cell">
                <div class="receipt-title">{{ $payment->type === 'withholding' ? 'Withholding Receipt' : 'Payment Receipt' }}</div>
                <div class="receipt-no">No. {{ $payment->payment_no }}</div>
                <div class="receipt-muted">{{ $payment->paid_at?->format($dateFormat) }}</div>
            </td>
        </tr>
    </table>

    <table class="receipt-table">
        <tr><th>Received from</th><td>{{ $payment->member?->name ?? '-' }} @if ($payment->member?->phone)<span class="receipt-muted">· {{ $payment->member->phone }}</span>@endif</td></tr>
        <tr><th>For</th><td>{{ $payment->payable ? $payableName : ($payment->incomeCategory?->name ?? 'Other income') }}</td></tr>
        <tr><th>Payment method</th><td>{{ $payment->paymentMethod?->name ?? ($payment->type === 'withholding' ? 'Withholding' : '-') }}</td></tr>
        @if ($payment->reference)
            <tr><th>Reference</th><td>{{ $payment->reference }}</td></tr>
        @endif
        @if ($payment->notes)
            <tr><th>Notes</th><td>{!! nl2br(e($payment->notes)) !!}</td></tr>
        @endif
    </table>

    <table class="receipt-amount">
        <tr>
            <td>Amount</td>
            <td class="receipt-amount-value">{{ number_format($payment->signed_amount, 2) }} EGP</td>
        </tr>
    </table>

    @if ($payment->payable)
        {{-- the payable after all its payments (now, not at the payment date) --}}
        <table class="receipt-table receipt-summary">
            <tr><th>Total due</th><td>{{ number_format($payment->payable->payableAmount(), 2) }}</td></tr>
            <tr><th>Paid so far</th><td>{{ number_format($payment->payable->paid_amount, 2) }}</td></tr>
            <tr><th>{{ $payment->payable->remaining < 0 ? 'Credit' : 'Remaining' }}</th><td>{{ number_format(abs($payment->payable->remaining), 2) }}</td></tr>
        </table>
    @endif

    <table class="receipt-foot">
        <tr>
            <td class="receipt-muted">Received by: {{ $payment->creatable?->userable?->displayName() ?? '-' }}<br>Printed {{ now()->format($dateFormat . ' h:i A') }}</td>
            <td class="receipt-sign">Signature</td>
        </tr>
    </table>
</body>
</html>
