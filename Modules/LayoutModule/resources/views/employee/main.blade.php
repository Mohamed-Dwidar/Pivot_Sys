{{-- Employee layout (an employee of an account: he sees his profile and changes his password) --}}
@extends('layoutmodule::main')

@php($authUser = Auth::user())

@section('header')
    @include('layoutmodule::header', [
        'homeUrl' => route('employee.dashboard'),
        'userName' => $authUser->userable->displayName(),
        'userSubtitle' => $authUser->email,
        'menuLinks' => [
            ['label' => 'My Profile', 'icon' => 'user', 'url' => route('employee.dashboard')],
            ['label' => 'Change Password', 'icon' => 'key-round', 'url' => route('user.account.edit')],
        ],
        'logoutUrl' => route('logout'),
    ])
@endsection

@section('sidebar')
    @include('layoutmodule::sidebar', ['menu' => 'layoutmodule::employee.sidebar'])
@endsection
