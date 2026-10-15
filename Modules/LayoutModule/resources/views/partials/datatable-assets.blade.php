{{--
    DataTables for a list page: @include('layoutmodule::partials.datatable-assets')
    Then mark the table with data-datatable (see public/assets/js/datatables.js).
    jQuery is only for DataTables: datatables.js calls jQuery.noConflict(true), "$" stays the template's (vendors/dom.js).
    Local copies, no CDN (2026-10-16): jQuery 3.7.1 (MIT), DataTables 2.1.8 (MIT) in public/assets/vendor/{jquery,datatables}.
--}}
@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/dataTables.dataTables.min.css') }}">
    @endpush
    @push('scripts')
        <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables/dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/js/datatables.js') }}?v={{ filemtime(public_path('assets/js/datatables.js')) }}"></script>
    @endpush
@endonce
