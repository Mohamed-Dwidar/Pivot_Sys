@extends('layoutmodule::admin.main')

@section('title')
    Accounts
@endsection

@section('actions')
    <a href="{{ route('admin.accounts.create') }}" data-modal class="btn btn-primary"><i data-lucide="plus"></i> Add Account</a>
@endsection

@section('content')
    <form id="accounts-filters" class="flex flex-col gap-y-3 md:flex-row md:items-center">
        <input type="hidden" name="status" value="{{ $status }}">
        <div class="status-tabs">
            <button type="button" data-filter-tab data-name="status" data-value="" class="{{ $status === '' ? 'active' : '' }}">
                All (<span data-counter="accounts-all">{{ array_sum($counts) }}</span>)
            </button>
            @foreach (\Modules\AccountModule\app\Models\Account::STATUSES as $value => $label)
                <button type="button" data-filter-tab data-name="status" data-value="{{ $value }}" class="{{ $status === $value ? 'active' : '' }}">
                    {{ $label }} (<span data-counter="accounts-{{ $value }}">{{ $counts[$value] }}</span>)
                </button>
            @endforeach
            <button type="button" data-filter-tab data-name="status" data-value="deleted" class="{{ $status === 'deleted' ? 'active' : '' }}">
                Deleted (<span data-counter="accounts-deleted">{{ $deletedCount }}</span>)
            </button>
        </div>

        <div class="filter-bar md:ml-auto">
            <input type="search" name="search" class="{{ config('layoutmodule.form.input') }}" placeholder="Name, phone or email" autocomplete="off">
            @include('layoutmodule::partials.datatable-clear')
        </div>
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table" data-datatable data-url="{{ route('admin.accounts.data') }}"
            data-filters="#accounts-filters" data-order='[[4, "desc"]]' data-empty="No accounts found.">
            <thead>
                <tr>
                    <th data-data="name_html" data-name="name">Account</th>
                    <th data-data="email">Email</th>
                    <th data-data="phone" data-name="phone">Phone</th>
                    <th data-data="status_html" data-name="status">Status</th>
                    <th data-data="created_at" data-name="created_at" data-class="whitespace-nowrap">Registered</th>
                    <th data-data="actions"></th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@include('layoutmodule::partials.datatable-assets')
