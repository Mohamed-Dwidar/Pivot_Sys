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
    <form id="payments-filters" class="filter-bar">
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="Number, reference, member name or phone" autocomplete="off">
        <select name="source" class="{{ config('layoutmodule.form.select') }}" aria-label="For">
            <option value="">All Sources</option>
            <option value="reservation">Reservations</option>
            <option value="package">Packages</option>
            <option value="income">Other Income</option>
        </select>
        <select name="type" class="{{ config('layoutmodule.form.select') }}" aria-label="Type">
            <option value="">All Types</option>
            @foreach (\Modules\PaymentModule\app\Models\Payment::TYPES as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <select name="payment_method_id" class="{{ config('layoutmodule.form.select') }}" aria-label="Payment method">
            <option value="">All Methods</option>
            @foreach ($methods as $id => $name)
                <option value="{{ $id }}">{{ Str::humanize($name) }}</option>
            @endforeach
        </select>
        <select name="income_category_id" class="{{ config('layoutmodule.form.select') }}" aria-label="Income category">
            <option value="">All Categories</option>
            @foreach ($categories as $id => $name)
                <option value="{{ $id }}">{{ Str::humanize($name) }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" class="{{ config('layoutmodule.form.input') }} field-short" aria-label="Date from" title="Date from">
        <input type="date" name="date_to" class="{{ config('layoutmodule.form.input') }} field-short" aria-label="Date to" title="Date to">
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table" data-datatable data-url="{{ route($area . '.payments.data') }}"
            data-filters="#payments-filters" data-order='[[1, "desc"]]' data-empty="No payments yet.">
            <thead>
                <tr>
                    <th data-data="number_html" data-name="payment_no">No.</th>
                    <th data-data="paid_at" data-name="paid_at" data-class="whitespace-nowrap">Date</th>
                    <th data-data="member">Member</th>
                    <th data-data="source">For</th>
                    <th data-data="type_html" data-name="type">Type</th>
                    <th data-data="method">Method</th>
                    <th data-data="amount" data-name="amount">Amount</th>
                    <th data-data="actions"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
