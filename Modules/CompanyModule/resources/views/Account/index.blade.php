@extends('layoutmodule::account.main')

@section('title')
    Companies
@endsection

@section('actions')
    <a href="{{ route('account.companies.create') }}" data-modal class="btn btn-primary"><i data-lucide="plus"></i> Add Company</a>
@endsection

@section('content')
    <form id="companies-filters" class="filter-bar">
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="Search by name" autocomplete="off">
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table" data-datatable data-url="{{ route('account.companies.data') }}"
            data-filters="#companies-filters" data-order='[[0, "asc"]]' data-empty="No companies yet.">
            <thead>
                <tr>
                    <th data-data="name_html" data-name="name">Name</th>
                    <th data-data="created_at" data-name="created_at" data-class="whitespace-nowrap">Added</th>
                    <th data-data="actions"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
