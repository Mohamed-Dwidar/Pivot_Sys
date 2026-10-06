@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Edit Job: {{ $job->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route('account.jobs.update', $job->id) }}" data-ajax data-row="{{ $job->id }}"
        data-confirm="The changes to &quot;{{ $job->name }}&quot; will be saved."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('jobmodule::Account.partials.form', ['job' => $job])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.jobs.show', $job->id)])
    </form>
@endsection
