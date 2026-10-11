@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(Auth::user()->userable->layout())

@section('title')
    Day Closing
@endsection

@section('actions')
    <a href="{{ route($area . '.payments.index') }}" class="btn btn-secondary"><i data-lucide="wallet"></i> Payments</a>
    <a href="{{ route($area . '.day-closings.create') }}" class="btn btn-primary"><i data-lucide="lock"></i> Close a Day</a>
@endsection

@section('content')
    <form id="day-closings-filters" class="filter-bar">
        <select name="status" class="{{ config('layoutmodule.form.select') }}" aria-label="Status">
            <option value="">All Statuses</option>
            <option value="closed">Closed</option>
            <option value="reopened">Reopened</option>
        </select>
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table" data-datatable data-url="{{ route($area . '.day-closings.data') }}"
            data-filters="#day-closings-filters" data-order='[[0, "desc"]]' data-empty="No closed days yet.">
            <thead>
                <tr>
                    <th data-data="date_html" data-name="date">Day</th>
                    <th data-data="status_html" data-name="status">Status</th>
                    <th data-data="cash_total" data-name="cash_total">Cash Total</th>
                    <th data-data="withholding_total" data-name="withholding_total">Withholding</th>
                    <th data-data="closed" data-name="closed_at">Closed By</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
