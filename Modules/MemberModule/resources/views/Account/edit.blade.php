@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    Edit Member: {{ $member->name }}
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route($area . '.members.update', $member->id) }}" data-ajax data-row="{{ $member->id }}"
        data-confirm="The changes to &quot;{{ $member->name }}&quot; will be saved."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('membermodule::Account.partials.form', ['member' => $member])

        @include('layoutmodule::partials.form-actions', ['cancel' => route($area . '.members.show', $member->id)])
    </form>
@endsection
