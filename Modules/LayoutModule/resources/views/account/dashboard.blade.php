@extends('layoutmodule::account.main')

@section('title')
    Dashboard
@endsection

@section('content')
    <div class="flex items-center gap-4">
        @include('accountmodule::Account.partials.logo', ['account' => $account])
        <div>
            <div class="text-base font-medium">Welcome, {{ $account->name_ar }}</div>
            <div class="mt-1 text-slate-500">{{ config('app.name') }}</div>
        </div>
    </div>

    @php($completion = $account->profileCompletion())
    <div class="mt-6">
        <div class="flex items-center mb-2">
            <div class="font-medium">Profile completion</div>
            <div class="ml-auto text-slate-500">{{ $completion }}%</div>
        </div>
        <progress class="progress" value="{{ $completion }}" max="100">{{ $completion }}%</progress>
    </div>

    @if ($completion < 100)
        <div class="notice mt-6">
            <div class="font-medium">Complete your profile</div>
            <div class="mt-1 text-slate-600">Add your English name, address, description and logo so your profile is complete.</div>
            <a href="{{ route('account.profile.edit') }}" class="btn btn-primary btn-sm mt-3">Complete Profile</a>
        </div>
    @endif
@endsection
