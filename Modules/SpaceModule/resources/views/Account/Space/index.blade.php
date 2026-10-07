@extends('layoutmodule::account.main')

@section('title')
    Spaces
@endsection

@section('actions')
    <a href="{{ route('account.spaces.create') }}" data-modal class="btn btn-primary"><i data-lucide="plus"></i> Add Space</a>
@endsection

@section('content')
    <form id="spaces-filters" class="filter-bar">
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="Search by name" autocomplete="off">
        <select name="subscription_type_id" class="{{ config('layoutmodule.form.select') }}" aria-label="Subscription type">
            <option value="">All Subscription Types</option>
            @foreach ($subscriptionTypes as $id => $name)
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
        <table class="data-table" data-datatable data-url="{{ route('account.spaces.data') }}"
            data-filters="#spaces-filters" data-order='[[1, "asc"]]' data-empty="No spaces yet.">
            <thead>
                <tr>
                    <th data-data="image"></th>
                    <th data-data="name_html" data-name="name">Name</th>
                    <th data-data="subscription_types">Subscription Types</th>
                    <th data-data="units_count" data-name="units_count">Units</th>
                    <th data-data="images_count">Images</th>
                    <th data-data="status" data-name="is_active">Status</th>
                    <th data-data="actions"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
