@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Add Space
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route('account.spaces.store') }}" enctype="multipart/form-data" data-ajax
        data-confirm="The space will be added, then you can add its units."
        data-confirm-title="Add this space?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('spacemodule::Account.Space.partials.form', ['space' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.spaces.index')])
    </form>
@endsection
