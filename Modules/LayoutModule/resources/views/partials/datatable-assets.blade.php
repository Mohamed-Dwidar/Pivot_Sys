{{--
    DataTables for a list page: @include('layoutmodule::partials.datatable-assets')
    Then mark the table with data-datatable (see public/assets/js/datatables.js).
    jQuery is only for DataTables: datatables.js calls jQuery.noConflict(true), "$" stays the template's (vendors/dom.js).
--}}
@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
    @endpush
    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
        <script src="{{ asset('assets/js/datatables.js') }}?v={{ filemtime(public_path('assets/js/datatables.js')) }}"></script>
    @endpush
@endonce
