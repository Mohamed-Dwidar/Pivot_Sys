@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::admin.main')

@section('title')
    Add Color
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.colors.store') }}" data-ajax
        data-confirm="The color will be available for the units of all accounts."
        data-confirm-title="Add this color?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('unitmodule::Admin.Color.partials.form', ['color' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('admin.colors.index')])
    </form>
@endsection
