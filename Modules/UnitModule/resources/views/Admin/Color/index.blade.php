@extends('layoutmodule::admin.main')

@section('title')
    Colors
@endsection

@section('actions')
    <a href="{{ route('admin.colors.create') }}" data-modal class="btn btn-primary"><i data-lucide="plus"></i> Add Color</a>
@endsection

@section('content')
    <form id="colors-filters" class="filter-bar">
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="Search by name" autocomplete="off">
        @include('layoutmodule::partials.datatable-clear')
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table" data-datatable data-url="{{ route('admin.colors.data') }}"
            data-filters="#colors-filters" data-order='[[0, "asc"]]' data-empty="No colors found.">
            <thead>
                <tr>
                    <th data-data="name_html" data-name="name">Color</th>
                    <th data-data="value" data-name="value">Value</th>
                    <th data-data="units_count" data-name="units_count">Used by Units</th>
                    <th data-data="actions"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
