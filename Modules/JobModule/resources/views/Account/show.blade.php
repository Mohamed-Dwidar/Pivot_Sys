@extends('layoutmodule::account.main')

@section('title')
    Job Details
@endsection

@section('actions')
    <a href="{{ route('account.jobs.edit', $job->id) }}" class="btn btn-secondary"><i data-lucide="pencil"></i> Edit</a>
    @include('jobmodule::Account.partials.delete-form')
@endsection

@section('content')
    <dl class="detail-list">
        <div><dt>Name (Arabic)</dt><dd dir="rtl">{{ $job->name_ar }}</dd></div>
        <div><dt>Name (English)</dt><dd>{{ $job->name_en ?: '-' }}</dd></div>
        <div><dt>Added At</dt><dd>{{ $job->created_at->format('Y-m-d H:i') }}</dd></div>
        <div><dt>Last Update</dt><dd>{{ $job->updated_at->format('Y-m-d H:i') }}</dd></div>
    </dl>

    <div class="form-actions">
        <a href="{{ route('account.jobs.index') }}" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Back to Jobs</a>
    </div>
@endsection
