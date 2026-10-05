@extends('layoutmodule::admin.main')

@section('title')
    Dashboard
@endsection

@section('content')
    <div class="text-base font-medium">
        Welcome, {{ Auth::guard('admin')->user()->name }}
    </div>
    <div class="mt-1 text-slate-500">{{ config('app.name') }}</div>

    <div class="stat-grid mt-6">
        @foreach (\Modules\AccountModule\app\Models\Account::STATUSES as $status => $label)
            <a href="{{ route('admin.accounts.index', ['status' => $status]) }}" class="stat-card">
                <span class="badge badge-{{ $status }}">{{ $label }} Accounts</span>
                <div class="stat-card__value">{{ $counts[$status] }}</div>
            </a>
        @endforeach
    </div>

    <div class="flex items-center mt-8 mb-4">
        <div class="text-base font-medium">Latest Pending Requests</div>
        <a href="{{ route('admin.accounts.index', ['status' => 'pending']) }}" class="ml-auto text-primary">View all</a>
    </div>

    @include('accountmodule::Admin.partials.table', ['accounts' => $pendingAccounts])
@endsection
