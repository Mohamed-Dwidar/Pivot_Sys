@extends('layoutmodule::login')

@section('content')
    <div class="mt-10">
        @include('layoutmodule::partials.login-form', [
            'action' => route('loginUser'),
            'subtitle' => 'Sign in to your account',
            'remember' => 'remember',
        ])

        <div class="auth-links">
            Don't have an account? <a href="{{ route('account.register') }}">Create one</a>
        </div>
    </div>
@endsection
