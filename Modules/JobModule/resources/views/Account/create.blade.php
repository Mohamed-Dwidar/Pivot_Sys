@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Add Job
@endsection

@section('content')
    <form method="POST" action="{{ route('account.jobs.store') }}" data-ajax
        data-confirm="The job will be added to your jobs list."
        data-confirm-title="Add this job?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('jobmodule::Account.partials.form', ['job' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.jobs.index')])
    </form>
@endsection
