{{-- Login details page, shared by every user type: $layout comes from the user's Userable model. --}}
@extends($layout)

@section('title')
    {{ $user->userable->canChangeEmail() ? 'Login Details' : 'Change Password' }}
@endsection

@section('content')
    <form method="POST" action="{{ route('user.account.update') }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            @if ($user->userable->canChangeEmail())
                @include('layoutmodule::partials.field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'value' => $user->email])
            @else
                @include('layoutmodule::partials.field', ['name' => 'email_display', 'label' => 'Email', 'type' => 'email', 'value' => $user->email,
                    'attrs' => 'disabled', 'hint' => 'Your company manages your email, you can change your password.'])
            @endif
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
