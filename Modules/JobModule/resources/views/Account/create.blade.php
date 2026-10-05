@extends('layoutmodule::account.main')

@section('title')
    Add Job
@endsection

@section('content')
    <form method="POST" action="{{ route('account.jobs.store') }}">
        @csrf

        @include('jobmodule::Account.partials.form', ['job' => null])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
            <a href="{{ route('account.jobs.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
