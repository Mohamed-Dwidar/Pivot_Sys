@extends('layoutmodule::account.main')

@section('title')
    Edit Job: {{ $job->name_ar }}
@endsection

@section('content')
    <form method="POST" action="{{ route('account.jobs.update', $job->id) }}">
        @csrf
        @method('PUT')

        @include('jobmodule::Account.partials.form', ['job' => $job])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
            <a href="{{ route('account.jobs.show', $job->id) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
