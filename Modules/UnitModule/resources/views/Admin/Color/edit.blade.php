@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::admin.main')

@section('title')
    Edit Color: {{ $color->name }}
@endsection

@section('actions')
    @include('unitmodule::Admin.Color.partials.delete-form')
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.colors.update', $color->id) }}" data-ajax data-row="{{ $color->id }}"
        data-confirm="The changes to &quot;{{ $color->name }}&quot; will be saved."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('unitmodule::Admin.Color.partials.form', ['color' => $color])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('admin.colors.index')])
    </form>
@endsection
