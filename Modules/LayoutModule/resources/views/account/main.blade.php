{{-- Account (company) layout --}}
@extends('layoutmodule::main')

@php($authUser = Auth::user())

@section('header')
    @include('layoutmodule::header', [
        'homeUrl' => route('account.dashboard'),
        'userName' => $authUser->userable->displayName(),
        'userSubtitle' => $authUser->email,
        'menuLinks' => [
            ['label' => 'My Profile', 'icon' => 'building-2', 'url' => route('account.profile.edit')],
            ['label' => 'Login Details', 'icon' => 'key-round', 'url' => route('user.account.edit')],
        ],
        'logoutUrl' => route('logout'),
    ])
@endsection

@section('sidebar')
    @include('layoutmodule::sidebar', ['menu' => 'layoutmodule::account.sidebar'])
@endsection
