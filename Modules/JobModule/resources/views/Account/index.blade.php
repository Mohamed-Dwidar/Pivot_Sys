@extends('layoutmodule::account.main')

@section('title')
    Jobs
@endsection

@section('actions')
    <a href="{{ route('account.jobs.create') }}" class="btn btn-primary"><i data-lucide="plus"></i> Add Job</a>
@endsection

@section('content')
    <form id="jobs-filters" class="filter-bar">
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="Search by name" autocomplete="off">
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table" data-datatable data-url="{{ route('account.jobs.data') }}"
            data-filters="#jobs-filters" data-order='[[1, "asc"]]' data-empty="No jobs yet.">
            <thead>
                <tr>
                    <th data-data="DT_RowIndex">#</th>
                    <th data-data="name_html" data-name="name">Name</th>
                    <th data-data="created_at" data-name="created_at" data-class="whitespace-nowrap">Added</th>
                    <th data-data="actions"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
