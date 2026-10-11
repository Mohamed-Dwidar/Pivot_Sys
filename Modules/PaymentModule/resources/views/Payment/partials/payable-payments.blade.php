{{--
    The payments block of a reservation / package view.
    @include('paymentmodule::Payment.partials.payable-payments', ['payable' => $reservation, 'kind' => 'reservation'])
    $kind: reservation | package (PaymentService::PAYABLES). A reservation inside a package is paid through the package (link to it).
    "Add payment" opens the payment form for it in the popup; after saving, the popup shows this view again (json "modal").
--}}
@php
    $area = request()->routeIs('employee.*') ? 'employee' : 'account';
    $dateFormat = \Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT;
@endphp

<div class="form-section mt-8 payments-block__head">
    <span>Payments</span>
    @if ($payable->canTakePayments())
        <a href="{{ route($area . '.payments.create', ['payable' => $kind, 'payable_id' => $payable->getKey()]) }}" data-modal class="btn btn-primary btn-sm"><i data-lucide="plus"></i> Add Payment</a>
    @endif
</div>

@if (!$payable->canTakePayments())
    <div class="text-slate-500">
        Paid through the package
        @if ($payable->package)
            <a href="{{ route($area . '.packages.show', $payable->package_id) }}" data-modal class="font-medium"><i data-lucide="package" class="inline h-3 w-3"></i> {{ $payable->package->name }}</a>.
        @endif
    </div>
@else
    <dl class="detail-list">
        <div><dt>Due</dt><dd>{{ number_format($payable->payableAmount(), 2) }}
            @if (method_exists($payable, 'extrasTotal') && $payable->extrasTotal() != 0)
                <span class="text-xs text-slate-500">(net {{ number_format($payable->after_discount, 2) }} + extras {{ number_format($payable->extrasTotal(), 2) }})</span>
            @endif
        </dd></div>
        <div><dt>Paid</dt><dd>{{ number_format($payable->paid_amount, 2) }}</dd></div>
        <div><dt>{{ $payable->remaining < 0 ? 'Credit' : 'Remaining' }}</dt><dd class="font-medium {{ $payable->remaining > 0 ? 'text-danger' : 'text-success' }}">{{ number_format(abs($payable->remaining), 2) }}</dd></div>
        <div><dt>Status</dt><dd><span class="badge {{ $payable->payment_status_badge_class }}">{{ $payable->payment_status_label }}</span></dd></div>
    </dl>

    @php($payments = $payable->paymentsList())
    @if ($payments->isNotEmpty())
        <div class="table-wrap mt-4">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $payment)
                        <tr>
                            <td><a href="{{ route($area . '.payments.show', $payment->id) }}" data-modal class="font-medium">#{{ $payment->payment_no }}</a></td>
                            <td class="whitespace-nowrap">{{ $payment->paid_at?->format($dateFormat) }}</td>
                            <td><span class="badge {{ $payment->type_badge_class }}">{{ $payment->type_label }}</span></td>
                            <td>{{ $payment->paymentMethod?->name ?? '-' }}</td>
                            <td class="{{ $payment->type === 'refund' ? 'text-danger' : '' }}">{{ number_format($payment->signed_amount, 2) }}</td>
                            <td><a href="{{ route($area . '.payments.receipt', $payment->id) }}" target="_blank" title="Receipt"><i data-lucide="printer" class="h-4 w-4"></i></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="mt-3 text-slate-500">No payments yet.</div>
    @endif
@endif
