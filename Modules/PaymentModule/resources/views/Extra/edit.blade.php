@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Edit {{ $extra->type_label }} · {{ $package?->name }}
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route('account.extras.update', $extra->id) }}" data-ajax
        data-confirm="What the package owes will be calculated again."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('paymentmodule::Extra.partials.form')

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.packages.show', $extra->extrable_id)])
    </form>
@endsection
