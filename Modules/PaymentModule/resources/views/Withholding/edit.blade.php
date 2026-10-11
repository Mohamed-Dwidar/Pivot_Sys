@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Edit Certificate {{ $certificate->certificate_no }}
@endsection

@section('modal-size', 'lg')

@section('content')
    <form method="POST" action="{{ route('account.withholding-certificates.update', $certificate->id) }}" enctype="multipart/form-data" data-ajax data-row="{{ $certificate->id }}"
        data-confirm="The certificate will be changed."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('paymentmodule::Withholding.partials.form')

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.withholding-certificates.index')])
    </form>
@endsection
