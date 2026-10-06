{{-- Units of $space, shown on the space page (DataTables). --}}
<div class="flex flex-col gap-3 mt-8 mb-4 md:flex-row md:items-center">
    <div class="text-base font-medium">Units ({{ $space->units_count }})</div>
    <form id="units-filters" class="filter-bar md:ml-auto">
        <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="Search units" autocomplete="off">
        @include('layoutmodule::partials.datatable-clear')
    </form>
    <a href="{{ route('account.spaces.units.create', $space->id) }}" class="btn btn-primary"><i data-lucide="plus"></i> Add Unit</a>
</div>

<div class="table-wrap">
    <table class="data-table" data-datatable data-url="{{ route('account.spaces.units.data', $space->id) }}"
        data-filters="#units-filters" data-order='[[1, "asc"]]' data-page-length="10" data-empty="No units in this space yet.">
        <thead>
            <tr>
                <th data-data="image"></th>
                <th data-data="name_html" data-name="name">Name</th>
                <th data-data="subscription_type">Subscription Type</th>
                <th data-data="capacity" data-name="capacity">Capacity</th>
                <th data-data="concurrent_usage" data-name="concurrent_usage">Concurrent</th>
                <th data-data="status" data-name="is_active">Status</th>
                <th data-data="actions"></th>
            </tr>
        </thead>
    </table>
</div>

@include('layoutmodule::partials.datatable-assets')
