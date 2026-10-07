@php($area = request()->routeIs('employee.*') ? 'employee' : 'account')
@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    Edit Package: {{ $package->name }}
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route($area . '.packages.update', $package->id) }}" data-ajax data-row="{{ $package->id }}"
        data-confirm="The changes to &quot;{{ $package->name }}&quot; will be saved."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('packagemodule::Package.partials.form', ['package' => $package])

        @include('layoutmodule::partials.form-actions', ['cancel' => route($area . '.packages.show', $package->id)])
    </form>
@endsection
