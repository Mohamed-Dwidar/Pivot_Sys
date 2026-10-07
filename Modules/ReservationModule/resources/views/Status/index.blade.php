@extends('layoutmodule::account.main')

@section('title')
    Reservation Statuses
@endsection

@section('actions')
    <a href="{{ route('account.reservation-statuses.create') }}" data-modal class="btn btn-primary"><i data-lucide="plus"></i> Add Status</a>
@endsection

@section('content')
    <form id="statuses-filters" class="filter-bar">
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="Search by name" autocomplete="off">
        <select name="is_active" class="{{ config('layoutmodule.form.select') }}" aria-label="Status">
            <option value="">All Statuses</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table" data-datatable data-url="{{ route('account.reservation-statuses.data') }}"
            data-filters="#statuses-filters" data-order='[[0, "asc"]]' data-empty="No statuses yet.">
            <thead>
                <tr>
                    <th data-data="sort_order" data-name="sort_order" data-class="w-24">Order</th>
                    <th data-data="name_html" data-name="name">Name</th>
                    <th data-data="reservations_count" data-name="reservations_count">Reservations</th>
                    <th data-data="default" data-name="is_default">Default</th>
                    <th data-data="status" data-name="is_active">Status</th>
                    <th data-data="actions"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
