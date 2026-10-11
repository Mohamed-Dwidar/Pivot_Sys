@extends('layoutmodule::employee.main')

{{--
    The employee's shift (docs/payments-plan.md §2b): ONLY their payments not closed yet (since their last closing, any time of day),
    totals by method, "Close My Shift" opens the confirmation popup; their past closings below.
--}}
@section('title')
    My Shift
@endsection

@section('actions')
    <a href="{{ route('employee.payments.create', ['back_to_shift' => 1]) }}" data-modal class="btn btn-secondary"><i data-lucide="plus"></i> Add Payment</a>
    @if ($period['payments']->isNotEmpty())
        <a href="{{ route('employee.shift.confirm') }}" data-modal class="btn btn-primary"><i data-lucide="lock"></i> Close My Shift</a>
    @endif
@endsection

@section('content')
    <dl class="detail-list">
        <div><dt>Open Since</dt><dd>{{ $period['since']?->format('d-m-Y h:i A') ?? '-' }}</dd></div>
        <div><dt>Last Closing</dt><dd>{{ $period['last_closing']?->closed_at?->format('d-m-Y h:i A') ?? 'Never' }}</dd></div>
        <div><dt>Payments</dt><dd>{{ $period['summary']['count'] }}</dd></div>
    </dl>

    @if ($period['payments']->isEmpty())
        <div class="mt-5 text-slate-500">No payments since your last closing.</div>
    @else
        <div class="form-section mt-8">Totals</div>
        @include('paymentmodule::Shift.partials.totals', ['summary' => $period['summary']])

        <div class="form-section mt-8">Payments</div>
        @include('paymentmodule::Shift.partials.payments', ['payments' => $period['payments']])
    @endif

    <div class="form-section mt-8">My Closings</div>
    <div class="table-wrap">
        <table class="data-table" data-datatable data-url="{{ route('employee.shift.data') }}" data-order='[[0, "desc"]]' data-empty="No closings yet.">
            <thead>
                <tr>
                    <th data-data="closed_html" data-name="closed_at">Closed</th>
                    <th data-data="period" data-name="first_payment_at">Payments From → To</th>
                    <th data-data="count" data-name="count">Payments</th>
                    <th data-data="cash_total" data-name="cash_total">Collected</th>
                    <th data-data="withholding_total" data-name="withholding_total">Withholding</th>
                    <th data-data="status_html" data-name="status">Status</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
