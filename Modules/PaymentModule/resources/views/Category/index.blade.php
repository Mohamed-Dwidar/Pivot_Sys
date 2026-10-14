@extends('layoutmodule::account.main')

@section('title')
    Income Categories
@endsection

@section('actions')
    <a href="{{ route('account.income-categories.create') }}" data-modal class="btn btn-primary"><i data-lucide="plus"></i> Add Income Category</a>
@endsection

@section('content')
    <form id="income-categories-filters" class="filter-bar">
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="Search by name" autocomplete="off">
        <select name="is_active" class="{{ config('layoutmodule.form.select') }}" aria-label="Status">
            <option value="">All Statuses</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table" data-datatable data-url="{{ route('account.income-categories.data') }}"
            data-filters="#income-categories-filters" data-order='[[0, "asc"]]' data-empty="No income categories yet.">
            <thead>
                <tr>
                    <th data-data="name_html" data-name="name">Name</th>
                    <th data-data="payments_count" data-name="payments_count">Cash In</th>
                    <th data-data="status" data-name="is_active">Status</th>
                    <th data-data="actions"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
