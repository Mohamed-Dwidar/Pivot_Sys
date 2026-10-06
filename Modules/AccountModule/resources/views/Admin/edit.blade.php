@extends('layoutmodule::admin.main')

@section('title')
    Edit Account: {{ $account->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.accounts.update', $account->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('accountmodule::Admin.partials.form', ['account' => $account])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
            <a href="{{ route('admin.accounts.show', $account->id) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
