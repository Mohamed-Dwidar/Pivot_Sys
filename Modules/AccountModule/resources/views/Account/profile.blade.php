@extends('layoutmodule::account.main')

@section('title')
    My Profile
@endsection

@section('content')
    <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('accountmodule::Account.partials.profile-fields', ['account' => $account])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save"></i> Save Profile
            </button>
        </div>
    </form>
@endsection
