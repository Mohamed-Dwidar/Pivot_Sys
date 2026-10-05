@extends('layoutmodule::account.main')

@section('title')
    Company Details
@endsection

@section('actions')
    <a href="{{ route('account.companies.edit', $company->id) }}" class="btn btn-secondary"><i data-lucide="pencil"></i> Edit</a>
    @include('companymodule::Account.partials.delete-form')
@endsection

@section('content')
    <dl class="detail-list">
        <div><dt>Name (Arabic)</dt><dd dir="rtl">{{ $company->name_ar }}</dd></div>
        <div><dt>Name (English)</dt><dd>{{ $company->name_en ?: '-' }}</dd></div>
        <div><dt>Added At</dt><dd>{{ $company->created_at->format('Y-m-d H:i') }}</dd></div>
        <div><dt>Last Update</dt><dd>{{ $company->updated_at->format('Y-m-d H:i') }}</dd></div>
    </dl>

    <div class="form-actions">
        <a href="{{ route('account.companies.index') }}" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Back to Companies</a>
    </div>
@endsection
