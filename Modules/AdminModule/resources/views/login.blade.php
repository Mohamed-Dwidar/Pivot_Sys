@extends('layoutmodule::login')

@section('content')
    <div class="mt-10">
        @include('layoutmodule::partials.login-form', [
            'action' => route('admin.loginpost'),
            'subtitle' => 'Admin Panel Login',
            'remember' => 'rememberme',
        ])
    </div>
@endsection
