@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::admin.main')

@section('title')
    Add Account
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route('admin.accounts.store') }}" enctype="multipart/form-data" data-ajax
        data-confirm="The account will be created with its login details."
        data-confirm-title="Create this account?"
        data-confirm-button="Create"
        data-confirm-variant="success">
        @csrf

        @include('accountmodule::Admin.partials.form', ['account' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('admin.accounts.index')])
    </form>
@endsection
