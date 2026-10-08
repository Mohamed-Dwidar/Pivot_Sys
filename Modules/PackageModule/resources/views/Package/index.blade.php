@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(Auth::user()->userable->layout())

@section('title')
    Packages
@endsection

@section('actions')
    <a href="{{ route($area . '.packages.create') }}" data-modal class="btn btn-primary"><i data-lucide="plus"></i> Add Package</a>
@endsection

@section('content')
    <form id="packages-filters" class="filter-bar">
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="Package, member name or phone" autocomplete="off">
        <select name="member_id" class="{{ config('layoutmodule.form.select') }}" aria-label="Member">
            <option value="">All Members</option>
            @foreach ($members as $id => $name)
                <option value="{{ $id }}">{{ Str::humanize($name) }}</option>
            @endforeach
        </select>
        <select name="is_active" class="{{ config('layoutmodule.form.select') }}" aria-label="Status">
            <option value="">All Statuses</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table" data-datatable data-url="{{ route($area . '.packages.data') }}"
            data-filters="#packages-filters" data-order='[[0, "asc"]]' data-empty="No packages yet.">
            <thead>
                <tr>
                    <th data-data="name_html" data-name="name">Package</th>
                    <th data-data="member">Member</th>
                    <th data-data="period" data-name="date_from" data-class="whitespace-nowrap">Period</th>
                    <th data-data="amount" data-name="amount">Price</th>
                    <th data-data="discount" data-name="discount_percentage">Discount</th>
                    <th data-data="after_discount" data-name="after_discount">Net</th>
                    <th data-data="reservations_count" data-name="reservations_count">Reservations</th>
                    <th data-data="status" data-name="is_active">Status</th>
                    <th data-data="actions"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
