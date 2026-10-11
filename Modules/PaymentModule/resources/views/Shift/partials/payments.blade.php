{{-- the payments of a shift (open period / a closing), oldest first; $payments --}}
@php
    $area = request()->routeIs('employee.*') ? 'employee' : 'account';
    $dateFormat = \Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT;
@endphp

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr><th>No.</th><th>Added At</th><th>Date</th><th>For</th><th>Member</th><th>Type</th><th>Method</th><th>Amount</th></tr>
        </thead>
        <tbody>
            @foreach ($payments as $payment)
                <tr>
                    <td><a href="{{ route($area . '.payments.show', $payment->id) }}" data-modal class="font-medium">#{{ $payment->payment_no }}</a></td>
                    <td class="whitespace-nowrap">{{ $payment->created_at?->format('d-m-Y h:i A') }}</td>
                    <td class="whitespace-nowrap">{{ $payment->paid_at?->format($dateFormat) }}</td>
                    <td>@include('paymentmodule::Payment.partials.row-source')</td>
                    <td>{{ $payment->member?->name ?? '-' }}</td>
                    <td><span class="badge {{ $payment->type_badge_class }}">{{ $payment->type_label }}</span></td>
                    <td>{{ $payment->paymentMethod?->name ?? '-' }}</td>
                    <td class="{{ $payment->type === 'refund' ? 'text-danger' : '' }}">{{ number_format($payment->signed_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
