@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    Payment #{{ $payment->payment_no }}
    <span class="badge {{ $payment->type_badge_class }} ml-2">{{ $payment->type_label }}</span>
@endsection

@section('modal-size', 'lg')

@section('actions')
    <a href="{{ route($area . '.payments.receipt', $payment->id) }}" target="_blank" class="btn btn-secondary"><i data-lucide="printer"></i> Receipt</a>
@endsection

@section('content')
    <dl class="detail-list">
        <div><dt>Date</dt><dd>{{ $payment->paid_at?->format(\Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT) }}</dd></div>
        <div><dt>Amount</dt><dd class="font-medium {{ $payment->type === 'refund' ? 'text-danger' : 'text-success' }}">{{ number_format($payment->signed_amount, 2) }}</dd></div>
        <div><dt>Payment Method</dt><dd>{{ $payment->paymentMethod?->name ?? '-' }}</dd></div>
        <div><dt>Reference</dt><dd>{{ $payment->reference ?: '-' }}</dd></div>
    </dl>

    <div class="form-section">&nbsp;</div>

    <dl class="detail-list">
        <div><dt>Member</dt><dd>{{ $payment->member?->name ?? '-' }}</dd></div>
        <div><dt>Member Phone</dt><dd>{{ $payment->member?->phone ?? '-' }}</dd></div>
        <div class="wide"><dt>For</dt><dd>
            @if ($payment->payable)
                {{ $payableName }}
                &middot; @include('paymentmodule::Payment.partials.row-source')
            @else
                @include('paymentmodule::Payment.partials.row-source')
            @endif
        </dd></div>
        @if ($payment->payable)
            {{-- the payable now (after all its payments) --}}
            <div><dt>Due</dt><dd>{{ number_format($payment->payable->payableAmount(), 2) }}</dd></div>
            <div><dt>Paid / Remaining</dt><dd>
                {{ number_format($payment->payable->paid_amount, 2) }} / {{ number_format($payment->payable->remaining, 2) }}
                <span class="badge {{ $payment->payable->payment_status_badge_class }} ml-1">{{ $payment->payable->payment_status_label }}</span>
            </dd></div>
        @endif
    </dl>

    <div class="form-section">&nbsp;</div>

    <dl class="detail-list">
        <div class="wide"><dt>Notes</dt><dd class="whitespace-pre-line">{{ $payment->notes ?: '-' }}</dd></div>
        <div class="wide">
            <dd class="whitespace-pre-line">
                Added by <b>{{ $payment->creatable?->userable?->displayName() ?? $payment->creatable?->email ?? '-' }}</b>
                |
                At {{ $payment->created_at->format('d-m-Y h:i A') }}
            </dd>
        </div>
    </dl>

    @unless (request()->ajax())
        <div class="form-actions">
            <a href="{{ route($area . '.payments.index') }}" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Back to Payments</a>
        </div>
    @endunless
@endsection
