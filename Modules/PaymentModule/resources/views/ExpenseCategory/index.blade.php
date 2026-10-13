@extends('layoutmodule::account.main')

@section('title')
    Expense Categories
@endsection

@section('actions')
    <a href="{{ route('account.expense-categories.create') }}" data-modal class="btn btn-primary"><i data-lucide="plus"></i> Add Expense Category</a>
@endsection

@section('content')
    <form id="expense-categories-filters" class="filter-bar">
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="Search by name" autocomplete="off">
        <select name="is_active" class="{{ config('layoutmodule.form.select') }}" aria-label="Status">
            <option value="">All Statuses</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table" data-datatable data-url="{{ route('account.expense-categories.data') }}"
            data-filters="#expense-categories-filters" data-order='[[0, "asc"]]' data-empty="No expense categories yet.">
            <thead>
                <tr>
                    <th data-data="sort_order" data-name="sort_order" data-class="w-24">Order</th>
                    <th data-data="name_html" data-name="name">Name</th>
                    <th data-data="expenses_count" data-name="expenses_count">Expenses</th>
                    <th data-data="status" data-name="is_active">Status</th>
                    <th data-data="actions"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
