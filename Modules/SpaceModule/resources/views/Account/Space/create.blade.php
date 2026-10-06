@extends('layoutmodule::account.main')

@section('title')
    Add Space
@endsection

@section('content')
    <form method="POST" action="{{ route('account.spaces.store') }}" enctype="multipart/form-data">
        @csrf

        @include('spacemodule::Account.Space.partials.form', ['space' => null])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
            <a href="{{ route('account.spaces.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
