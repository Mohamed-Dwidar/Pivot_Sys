@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(Auth::user()->userable->layout())

@section('title')
    Reservations
@endsection

@section('actions')
    <a href="{{ route($area . '.reservations.create') }}" data-modal class="btn btn-primary"><i data-lucide="plus"></i> Add Reservation</a>
@endsection

@section('content')
    {{--
        Toolbar (docs/reservations-list-plan.md, simplified 2026-10-13): tabs Today (default, value '', running today,
        count of the counted ones) | Upcoming | All; under them ONE row of filters, all visible:
        search | date (one field: Today / All synced, Upcoming = "From <tomorrow>"; picked days = running on them, the Today rule) | status | space | type | payment | Clear.
        Today / Upcoming hide the contract periods unless the type filter is chosen (a contract type shows its rows).
        Not counted reservations (cancelled ...) = muted, last.
    --}}
    <form id="reservations-filters" class="filter-bar">
        @include('layoutmodule::partials.filter-tabs', ['tabs' => \Modules\ReservationModule\app\Models\Reservation::TABS, 'default' => 'today', 'dates' => \Modules\ReservationModule\app\Models\Reservation::tabDates(), 'counts' => $counts, 'counter' => 'reservations-'])
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="Member name or phone" autocomplete="off">
        @include('layoutmodule::partials.date-range-filter')
        <select name="reservation_status_id" class="{{ config('layoutmodule.form.select') }}" aria-label="Status">
            <option value="">All Statuses</option>
            @include('layoutmodule::partials.filter-options', ['options' => $statuses])
        </select>
        <select name="space_id" class="{{ config('layoutmodule.form.select') }}" aria-label="Space">
            <option value="">All Spaces</option>
            @include('layoutmodule::partials.filter-options', ['options' => $spaces])
        </select>
        <select name="subscription_type_id" class="{{ config('layoutmodule.form.select') }}" aria-label="Type">
            <option value="">All Types</option>
            @foreach ($types as $id => $name)
                <option value="{{ $id }}">{{ Str::humanize($name) }}</option>
            @endforeach
        </select>
        @include('paymentmodule::Payment.partials.status-filter', ['statuses' => \Modules\ReservationModule\app\Models\Reservation::PAYMENT_STATUSES])
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table" data-datatable data-url="{{ route($area . '.reservations.data') }}"
            data-filters="#reservations-filters" data-order='[]' data-empty="No reservations here.">
            <thead>
                <tr>
                    <th data-data="member_html">Member</th>
                    <th data-data="place">Space &rsaquo; Unit</th>
                    <th data-data="plan">Plan</th>
                    <th data-data="period" data-name="start_at" data-class="whitespace-nowrap">Period</th>
                    <th data-data="net_amount" data-name="net_amount" data-class="whitespace-nowrap">Amount</th>
                    <th data-data="paid_amount" data-name="paid_amount" data-class="whitespace-nowrap">Paid</th>
                    <th data-data="remaining_html" data-name="remaining" data-class="whitespace-nowrap">Remaining</th>
                    <th data-data="by">Created By</th>
                    <th data-data="status">Status</th>
                    <th data-data="actions"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
