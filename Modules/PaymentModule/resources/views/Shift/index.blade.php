@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(Auth::user()->userable->layout())

{{--
    My Shift (docs/payments-plan.md §2b), the employee's and the account's own (MyShiftActions): ONLY their payments not closed
    yet (since their last closing, any time of day; refunds they made included), totals by method, "Close My Shift" opens the
    confirmation popup; their past closings below.
--}}
@section('title')
    My Shift
@endsection

@section('actions')
    @if ($period['payments']->isNotEmpty() || $period['expenses']->isNotEmpty())
        <a href="{{ route($area . '.shift.confirm') }}" data-modal class="btn btn-primary"><i data-lucide="lock"></i> Close My Shift</a>
    @endif
@endsection

@section('content')
    <dl class="detail-list">
        <div><dt>Open Since</dt><dd>{{ $period['since']?->format('d-m-Y h:i A') ?? '-' }}</dd></div>
        <div><dt>Last Closing</dt><dd>{{ $period['last_closing']?->closed_at?->format('d-m-Y h:i A') ?? 'Never' }}</dd></div>
        <div><dt>Payments</dt><dd>{{ $period['summary']['count'] }}</dd></div>
        <div><dt>Expenses</dt><dd>{{ $period['summary']['expenses']['count'] }}</dd></div>
    </dl>

    @if ($period['payments']->isEmpty() && $period['expenses']->isEmpty())
        <div class="mt-5 text-slate-500">No payments or expenses since your last closing.</div>
    @else
        <div class="form-section mt-8">Totals</div>
        @include('paymentmodule::Shift.partials.totals', ['summary' => $period['summary']])

        {{-- the same list as the Payments page (row menu included), for this open shift only (shift=open), no Created By --}}
        <div class="form-section mt-8">Payments</div>
        <form id="shift-payments-filters" hidden><input type="hidden" name="shift" value="open"></form>
        @include('paymentmodule::Payment.partials.list-table', ['filters' => '#shift-payments-filters', 'empty' => 'No payments since your last closing.'])

        {{-- the same list as the Expenses page, for this open shift only (shift=open), no Created By --}}
        <div class="form-section mt-8">Expenses</div>
        <form id="shift-expenses-filters" hidden><input type="hidden" name="shift" value="open"></form>
        @include('paymentmodule::Expense.partials.list-table', ['filters' => '#shift-expenses-filters', 'empty' => 'No expenses since your last closing.'])
    @endif

    <div class="form-section mt-8">My Closings</div>
    <div class="table-wrap">
        <table class="data-table" data-datatable data-url="{{ route($area . '.shift.data') }}" data-order='[[0, "desc"]]' data-empty="No closings yet.">
            <thead>
                <tr>
                    <th data-data="closed_html" data-name="closed_at">Closed</th>
                    <th data-data="period" data-name="first_payment_at">Payments From → To</th>
                    <th data-data="payments_html">Payments</th>
                    <th data-data="cash_total" data-name="cash_total">Collected</th>
                    <th data-data="expenses_total" data-name="expenses_total">Expenses</th>
                    <th data-data="net_total">Net</th>
                    {{-- Settings › General "Withholding tax" off: no Withholding column --}}
                    @if (\Modules\PaymentModule\app\Services\PaymentService::withholdingOn())
                        <th data-data="withholding_total" data-name="withholding_total">Withholding</th>
                    @endif
                    <th data-data="status_html" data-name="edit_count">Status</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
