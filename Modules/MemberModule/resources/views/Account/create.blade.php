@extends('layoutmodule::account.main')

@section('title')
    Add Member
@endsection

@section('content')
    <form method="POST" action="{{ route('account.members.store') }}">
        @csrf

        @include('membermodule::Account.partials.form', ['member' => null])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
            <a href="{{ route('account.members.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
