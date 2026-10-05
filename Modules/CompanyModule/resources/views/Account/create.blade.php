@extends('layoutmodule::account.main')

@section('title')
    Add Company
@endsection

@section('content')
    <form method="POST" action="{{ route('account.companies.store') }}">
        @csrf

        @include('companymodule::Account.partials.form', ['company' => null])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
            <a href="{{ route('account.companies.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
