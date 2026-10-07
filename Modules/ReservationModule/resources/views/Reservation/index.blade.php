@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(Auth::user()->userable->layout())

@section('title')
    Reservations
@endsection

@section('actions')
    <a href="{{ route($area . '.reservations.create') }}" data-modal class="btn btn-primary"><i data-lucide="plus"></i> Add Reservation</a>
@endsection

@section('content')
    <form id="reservations-filters" class="filter-bar">
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="Member name or phone" autocomplete="off">
        <select name="reservation_status_id" class="{{ config('layoutmodule.form.select') }}" aria-label="Status">
            <option value="">All Statuses</option>
            @foreach ($statuses as $id => $name)
                <option value="{{ $id }}">{{ Str::humanize($name) }}</option>
            @endforeach
        </select>
        <select name="space_id" class="{{ config('layoutmodule.form.select') }}" aria-label="Space">
            <option value="">All Spaces</option>
            @foreach ($spaces as $id => $name)
                <option value="{{ $id }}">{{ Str::humanize($name) }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" class="{{ config('layoutmodule.form.input') }} field-short" aria-label="Start from" title="Start from">
        <input type="date" name="date_to" class="{{ config('layoutmodule.form.input') }} field-short" aria-label="Start to" title="Start to">
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table" data-datatable data-url="{{ route($area . '.reservations.data') }}"
            data-filters="#reservations-filters" data-order='[[3, "desc"]]' data-empty="No reservations yet.">
            <thead>
                <tr>
                    <th data-data="member_html">Member</th>
                    <th data-data="place">Space &rsaquo; Unit</th>
                    <th data-data="plan">Plan</th>
                    <th data-data="period" data-name="start_at" data-class="whitespace-nowrap">Period</th>
                    <th data-data="status">Status</th>
                    <th data-data="net_amount" data-name="net_amount">Net Amount</th>
                    <th data-data="actions"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
