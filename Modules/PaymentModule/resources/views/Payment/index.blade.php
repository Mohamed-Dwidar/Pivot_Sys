@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(Auth::user()->userable->layout())

@section('title')
    Payments
@endsection

@section('actions')
    @if ($area === 'employee')
        <a href="{{ route('employee.shift.index') }}" class="btn btn-secondary"><i data-lucide="lock"></i> My Shift</a>
    @else
        <a href="{{ route('account.day-closings.index') }}" class="btn btn-secondary"><i data-lucide="lock"></i> Day Closing</a>
    @endif
    @if ($area === 'account')
        <a href="{{ route('account.withholding-certificates.index') }}" class="btn btn-secondary"><i data-lucide="file-badge"></i> Withholding Certificates</a>
    @endif
    <a href="{{ route($area . '.payments.create') }}" data-modal class="btn btn-primary"><i data-lucide="plus"></i> Add Payment</a>
@endsection

@section('content')
    {{--
        Toolbar (docs/payments-plan.md "Payments list", 2026-10-13): tabs Today (default, value '') | This Month | All;
        ONE row of filters: search | date (one field, synced with the tabs both ways: Today = today, This Month = the month, All = empty;
        other days = no tab highlighted) | For | Method | Clear. Employees: no tabs, the date only (one day, today by default; × / Clear = today, never all).
        For = Reservations, Packages, the "Other income" group (all, then each category); Method = each method, then Refunds / Withholding.
        The summary line (total by method, refunds, withholding) comes with the rows ([data-html]).
    --}}
    <form id="payments-filters" class="filter-bar">
        {{-- employees: no tabs, the date field only (one day, today by default, × / Clear = today) --}}
        @if ($area === 'account')
            @include('layoutmodule::partials.filter-tabs', ['tabs' => \Modules\PaymentModule\app\Models\Payment::TABS, 'default' => 'today', 'dates' => \Modules\PaymentModule\app\Models\Payment::tabDates()])
        @endif
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="No., reference, member name or phone" autocomplete="off">
        @include('layoutmodule::partials.date-range-filter', $area === 'employee' ? ['single' => true, 'default' => today()->toDateString()] : [])
        <select name="for" class="{{ config('layoutmodule.form.select') }}" aria-label="For">
            <option value="">All (For)</option>
            <option value="reservation">Reservations</option>
            <option value="package">Packages</option>
            <optgroup label="Other income">
                <option value="income">All other income</option>
                @foreach ($categories as $id => $name)
                    <option value="category-{{ $id }}">{{ Str::humanize($name) }}</option>
                @endforeach
            </optgroup>
        </select>
        <select name="method" class="{{ config('layoutmodule.form.select') }}" aria-label="Method">
            <option value="">All Methods</option>
            @foreach ($methods as $id => $name)
                <option value="{{ $id }}">{{ Str::humanize($name) }}</option>
            @endforeach
            <option value="refund">Refunds</option>
            <option value="withholding">Withholding</option>
        </select>
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="list-summary" data-html="payments-summary"></div>

    <div class="table-wrap mt-5">
        <table class="data-table" data-datatable data-url="{{ route($area . '.payments.data') }}"
            data-filters="#payments-filters" data-order='[[2, "desc"]]' data-empty="No payments here.">
            <thead>
                <tr>
                    <th data-data="member_html">Member</th>
                    <th data-data="source">For</th>
                    <th data-data="paid_at" data-name="paid_at" data-class="whitespace-nowrap">Date</th>
                    <th data-data="method">Method</th>
                    <th data-data="amount" data-name="amount" data-class="whitespace-nowrap">Amount</th>
                    {{-- employees see only their own payments --}}
                    @if ($area === 'account')
                        <th data-data="by">Created By</th>
                    @endif
                    <th data-data="actions"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
