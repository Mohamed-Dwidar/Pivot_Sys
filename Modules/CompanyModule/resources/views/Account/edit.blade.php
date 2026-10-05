@extends('layoutmodule::account.main')

@section('title')
    Edit Company: {{ $company->name_ar }}
@endsection

@section('content')
    <form method="POST" action="{{ route('account.companies.update', $company->id) }}">
        @csrf
        @method('PUT')

        @include('companymodule::Account.partials.form', ['company' => $company])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
            <a href="{{ route('account.companies.show', $company->id) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
