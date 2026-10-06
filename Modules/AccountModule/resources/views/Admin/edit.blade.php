@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::admin.main')

@section('title')
    Edit Account: {{ $account->name }}
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route('admin.accounts.update', $account->id) }}" enctype="multipart/form-data" data-ajax data-row="{{ $account->id }}"
        data-confirm="The changes to &quot;{{ $account->name }}&quot; will be saved."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('accountmodule::Admin.partials.form', ['account' => $account])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('admin.accounts.show', $account->id)])
    </form>
@endsection
