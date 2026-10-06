@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    {{ $space->name }} &rsaquo; Edit Unit: {{ $unit->name }}
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route('account.spaces.units.update', [$space->id, $unit->id]) }}" enctype="multipart/form-data" data-ajax data-row="{{ $unit->id }}"
        data-confirm="The changes to &quot;{{ $unit->name }}&quot; will be saved."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('unitmodule::Account.Unit.partials.form', ['unit' => $unit])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.spaces.units.show', [$space->id, $unit->id])])
    </form>
@endsection
