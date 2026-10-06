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
                <div class="stat-card__value" data-counter="accounts-{{ $status }}">{{ $counts[$status] }}</div>
            </a>
        @endforeach
    </div>

    <div class="flex items-center mt-8 mb-4">
        <div class="text-base font-medium">Latest Pending Requests</div>
        <a href="{{ route('admin.accounts.index', ['status' => 'pending']) }}" class="ml-auto text-primary">View all</a>
    </div>

    {{-- the accounts list with a fixed "pending" filter: approved / rejected requests leave the table --}}
    <form id="pending-filters" hidden>
        <input type="hidden" name="status" value="pending">
    </form>
    <div class="table-wrap">
        <table class="data-table" data-datatable data-url="{{ route('admin.accounts.data') }}"
            data-filters="#pending-filters" data-order='[[4, "desc"]]' data-page-length="5" data-empty="No pending requests.">
            <thead>
                <tr>
                    <th data-data="name_html" data-name="name">Account</th>
                    <th data-data="email">Email</th>
                    <th data-data="phone" data-name="phone">Phone</th>
                    <th data-data="status_html">Status</th>
                    <th data-data="created_at" data-name="created_at" data-class="whitespace-nowrap">Registered</th>
                    <th data-data="actions"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
