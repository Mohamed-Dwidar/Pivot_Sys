@extends('layoutmodule::admin.main')

@section('title')
    My Account
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.profile.update') }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            @include('layoutmodule::partials.field', ['name' => 'name', 'label' => 'Name', 'required' => true, 'value' => $admin->name])
            @include('layoutmodule::partials.field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'value' => $admin->email])
        </div>

        <div class="form-section mt-8">Change Password</div>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            @include('layoutmodule::partials.field', ['name' => 'password', 'label' => 'New Password', 'type' => 'password', 'hint' => 'Leave empty to keep the current password.', 'attrs' => 'autocomplete=new-password'])
            @include('layoutmodule::partials.field', ['name' => 'password_confirmation', 'label' => 'Confirm New Password', 'type' => 'password', 'attrs' => 'autocomplete=new-password'])
        </div>

        <div class="form-section mt-8">Confirm</div>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            @include('layoutmodule::partials.field', ['name' => 'current_password', 'label' => 'Current Password', 'type' => 'password', 'required' => true, 'hint' => 'Required to save any change.', 'attrs' => 'autocomplete=current-password'])
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
        </div>
    </form>
@endsection
