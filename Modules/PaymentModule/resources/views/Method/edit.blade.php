@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Edit Payment Method: {{ $method->name }}
@endsection

@section('actions')
    @include('paymentmodule::Method.partials.delete-form')
@endsection

@section('content')
    <form method="POST" action="{{ route('account.payment-methods.update', $method->id) }}" data-ajax data-row="{{ $method->id }}"
        data-confirm="The changes to &quot;{{ $method->name }}&quot; will be saved."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('paymentmodule::Method.partials.form', ['method' => $method])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.payment-methods.index')])
    </form>
@endsection
