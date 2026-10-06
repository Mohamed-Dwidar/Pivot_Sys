@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Edit Space: {{ $space->name }}
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route('account.spaces.update', $space->id) }}" enctype="multipart/form-data" data-ajax data-row="{{ $space->id }}"
        data-confirm="The changes to &quot;{{ $space->name }}&quot; will be saved."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('spacemodule::Account.Space.partials.form', ['space' => $space])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.spaces.show', $space->id)])
    </form>
@endsection
