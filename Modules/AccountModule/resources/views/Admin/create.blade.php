@extends('layoutmodule::admin.main')

@section('title')
    Add Account
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.accounts.store') }}" enctype="multipart/form-data">
        @csrf

        @include('accountmodule::Admin.partials.form', ['account' => null])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
            <a href="{{ route('admin.accounts.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
