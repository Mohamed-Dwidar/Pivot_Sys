{{-- Admin layout --}}
@extends('layoutmodule::main')

@php($authAdmin = Auth::guard('admin')->user())

@section('header')
    @include('layoutmodule::header', [
        'homeUrl' => route('admin.dashboard'),
        'userName' => $authAdmin->name,
        'userSubtitle' => $authAdmin->email,
        'menuLinks' => [
            ['label' => 'My Account', 'icon' => 'user-cog', 'url' => route('admin.profile.edit')],
        ],
        'logoutUrl' => route('admin.logout'),
    ])
@endsection

@section('sidebar')
    @include('layoutmodule::sidebar', ['menu' => 'layoutmodule::admin.sidebar'])
@endsection
