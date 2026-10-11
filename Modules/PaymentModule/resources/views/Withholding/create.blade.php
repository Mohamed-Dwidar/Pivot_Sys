@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Add Withholding Certificate
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route('account.withholding-certificates.store') }}" enctype="multipart/form-data" data-ajax
        data-confirm="The certificate will be added."
        data-confirm-title="Add this certificate?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('paymentmodule::Withholding.partials.form', ['certificate' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.withholding-certificates.index')])
    </form>
@endsection
