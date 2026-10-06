@extends('layoutmodule::account.main')

@section('title')
    Subscription Types
@endsection

@section('actions')
    <a href="{{ route('account.subscription-types.create') }}" data-modal class="btn btn-primary"><i data-lucide="plus"></i> Add Subscription Type</a>
@endsection

@section('content')
    <form id="subscription-types-filters" class="filter-bar">
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="Search by name" autocomplete="off">
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table" data-datatable data-url="{{ route('account.subscription-types.data') }}"
            data-filters="#subscription-types-filters" data-order='[[0, "asc"]]' data-empty="No subscription types yet.">
            <thead>
                <tr>
                    <th data-data="name_html" data-name="name">Name</th>
                    <th data-data="options">Options</th>
                    <th data-data="spaces_count" data-name="spaces_count">Spaces</th>
                    <th data-data="actions"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
