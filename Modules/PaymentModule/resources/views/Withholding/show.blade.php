@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')
@php($dateFormat = \Modules\ReservationModule\app\Models\Reservation::DATE_FORMAT)

@section('title')
    Certificate {{ $certificate->certificate_no }}
    <span class="ml-2">@include('paymentmodule::Withholding.partials.difference')</span>
@endsection

@section('modal-size', 'lg')

@section('actions')
    @if ($hasFile)
        <a href="{{ route('account.withholding-certificates.attachment', $certificate->id) }}" target="_blank" class="btn btn-secondary"><i data-lucide="paperclip"></i> File</a>
    @endif
    <a href="{{ route('account.withholding-certificates.edit', $certificate->id) }}" data-modal class="btn btn-secondary"><i data-lucide="pencil"></i> Edit</a>
    @include('paymentmodule::Withholding.partials.delete-form')
@endsection

@section('content')
    <dl class="detail-list">
        <div><dt>Issue Date</dt><dd>{{ $certificate->issued_at?->format($dateFormat) }}</dd></div>
        <div><dt>Amount</dt><dd class="font-medium">{{ number_format($certificate->amount, 2) }}</dd></div>
        <div><dt>Period</dt><dd>{{ $certificate->period_from?->format($dateFormat) ?? '-' }} → {{ $certificate->period_to?->format($dateFormat) ?? '-' }}</dd></div>
        <div><dt>Payer</dt><dd>{{ $certificate->payer_name }}{{ $certificate->payer_tax_id ? ' · ' . $certificate->payer_tax_id : '' }}</dd></div>
        <div><dt>Company</dt><dd>{{ $certificate->company?->name ?? '-' }}</dd></div>
        <div><dt>Member</dt><dd>{{ $certificate->member?->name ?? '-' }}</dd></div>
    </dl>

    <div class="form-section mt-8">Withholding Payments ({{ $certificate->payments->count() }})</div>
    @if ($certificate->payments->isNotEmpty())
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>No.</th><th>Date</th><th>Member</th><th>Amount</th></tr></thead>
                <tbody>
                    @foreach ($certificate->payments as $payment)
                        <tr>
                            <td><a href="{{ route('account.payments.show', $payment->id) }}" data-modal class="font-medium">#{{ $payment->payment_no }}</a></td>
                            <td class="whitespace-nowrap">{{ $payment->paid_at?->format($dateFormat) }}</td>
                            <td>{{ $payment->member?->name ?? '-' }}</td>
                            <td>{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-slate-500">No payments attached yet.</div>
    @endif

    <div class="form-section">&nbsp;</div>
    <dl class="detail-list">
        <div class="wide"><dt>Notes</dt><dd class="whitespace-pre-line">{{ $certificate->notes ?: '-' }}</dd></div>
        <div class="wide"><dd>Added by <b>{{ $certificate->creatable?->userable?->displayName() ?? '-' }}</b> | At {{ $certificate->created_at->format('d-m-Y h:i A') }}</dd></div>
    </dl>

    @unless (request()->ajax())
        <div class="form-actions">
            <a href="{{ route('account.withholding-certificates.index') }}" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Back to Certificates</a>
        </div>
    @endunless
@endsection
