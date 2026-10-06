@extends('layoutmodule::employee.main')

@section('title')
    My Profile
@endsection

@section('content')
    <div class="flex items-center gap-4">
        @include('employeemodule::Account.Employee.partials.photo')
        <div>
            <div class="text-base font-medium">Welcome, {{ $employee->name }}</div>
            <div class="mt-1 text-slate-500">{{ $employee->account?->name }}</div>
        </div>
    </div>

    @include('employeemodule::Account.Employee.partials.details')

    <div class="notice mt-8">
        <div class="font-medium">Your profile is managed by {{ $employee->account?->name ?? 'your company' }}</div>
        <div class="mt-1 text-slate-600">Please contact them to change your data. You can change your password from here:</div>
        <a href="{{ route('user.account.edit') }}" class="btn btn-primary btn-sm mt-3"><i data-lucide="key-round"></i> Change Password</a>
    </div>
@endsection
