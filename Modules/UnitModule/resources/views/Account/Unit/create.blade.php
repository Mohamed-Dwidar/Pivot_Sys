@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    {{ $space->name }} &rsaquo; Add Unit
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route('account.spaces.units.store', $space->id) }}" enctype="multipart/form-data" data-ajax
        data-confirm="The unit will be added to &quot;{{ $space->name }}&quot;."
        data-confirm-title="Add this unit?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('unitmodule::Account.Unit.partials.form', ['unit' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.spaces.show', $space->id)])
    </form>
@endsection
