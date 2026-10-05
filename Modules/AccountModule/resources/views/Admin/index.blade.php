@extends('layoutmodule::admin.main')

@section('title')
    {{ ['pending' => 'Pending Requests', 'deleted' => 'Deleted Accounts'][$filters['status'] ?? ''] ?? 'Accounts' }}
@endsection

@section('actions')
    <a href="{{ route('admin.accounts.create') }}" class="btn btn-primary"><i data-lucide="plus"></i> Add Account</a>
@endsection

@section('content')
    <div class="flex flex-col gap-y-3 md:flex-row md:items-center">
        <div class="status-tabs">
            <a href="{{ route('admin.accounts.index', ['search' => $filters['search'] ?? null]) }}" class="{{ empty($filters['status']) ? 'active' : '' }}">
                All ({{ array_sum($counts) }})
            </a>
            @foreach (\Modules\AccountModule\app\Models\Account::STATUSES as $status => $label)
                <a href="{{ route('admin.accounts.index', ['status' => $status, 'search' => $filters['search'] ?? null]) }}" class="{{ ($filters['status'] ?? '') == $status ? 'active' : '' }}">
                    {{ $label }} ({{ $counts[$status] }})
                </a>
            @endforeach
            <a href="{{ route('admin.accounts.index', ['status' => 'deleted', 'search' => $filters['search'] ?? null]) }}" class="{{ ($filters['status'] ?? '') == 'deleted' ? 'active' : '' }}">
                Deleted ({{ $deletedCount }})
            </a>
        </div>

        <form method="GET" action="{{ route('admin.accounts.index') }}" class="filter-bar md:ml-auto">
            @if (!empty($filters['status']))
                <input type="hidden" name="status" value="{{ $filters['status'] }}">
            @endif
            <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="{{ config('layoutmodule.form.input') }}" placeholder="Name, phone or email">
            <button type="submit" class="btn btn-secondary"><i data-lucide="search"></i> Search</button>
        </form>
    </div>

    <div class="mt-5">
        @include('accountmodule::Admin.partials.table', ['accounts' => $accounts])
    </div>

    {{ $accounts->links('layoutmodule::pagination') }}
@endsection
