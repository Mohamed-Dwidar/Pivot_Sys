@extends('layoutmodule::account.main')

@section('title')
    Spaces
@endsection

@section('actions')
    <a href="{{ route('account.spaces.create') }}" class="btn btn-primary"><i data-lucide="plus"></i> Add Space</a>
@endsection

@section('content')
    <form method="GET" action="{{ route('account.spaces.index') }}" class="filter-bar">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="{{ config('layoutmodule.form.input') }}" placeholder="Search by name">
        <select name="subscription_type_id" class="{{ config('layoutmodule.form.select') }}" aria-label="Subscription type">
            <option value="">All Subscription Types</option>
            @foreach ($subscriptionTypes as $id => $name)
                <option value="{{ $id }}" @selected(($filters['subscription_type_id'] ?? '') == $id)>{{ $name }}</option>
            @endforeach
        </select>
        <select name="is_active" class="{{ config('layoutmodule.form.select') }}" aria-label="Status">
            <option value="">All Statuses</option>
            <option value="1" @selected(($filters['is_active'] ?? '') === '1')>Active</option>
            <option value="0" @selected(($filters['is_active'] ?? '') === '0')>Inactive</option>
        </select>
        <button type="submit" class="btn btn-primary"><i data-lucide="search"></i> Search</button>
        @if (array_filter($filters, fn ($value) => $value !== null && $value !== ''))
            <a href="{{ route('account.spaces.index') }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th>Subscription Types</th>
                    <th>Units</th>
                    <th>Images</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($spaces as $space)
                    <tr>
                        <td>
                            @if ($space->images->isNotEmpty())
                                <img src="{{ $space->images->first()->url }}" alt="{{ $space->name }}" class="table-thumb" data-action="zoom">
                            @else
                                <div class="table-thumb table-thumb--empty"><i data-lucide="image"></i></div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('account.spaces.show', $space->id) }}" class="font-medium">{{ $space->name }}</a>
                        </td>
                        <td>{{ $space->subscriptionTypes->pluck('name')->join(', ') ?: '-' }}</td>
                        <td>{{ $space->units_count }}</td>
                        <td>{{ $space->images->count() }}</td>
                        <td>@include('spacemodule::Account.Space.partials.status-badge')</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('account.spaces.show', $space->id) }}" class="btn btn-secondary btn-sm" title="View"><i data-lucide="eye"></i></a>
                                <a href="{{ route('account.spaces.edit', $space->id) }}" class="btn btn-secondary btn-sm" title="Edit"><i data-lucide="pencil"></i></a>
                                @include('spacemodule::Account.Space.partials.delete-form', ['size' => 'btn-sm'])
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            No spaces found.
                            <a href="{{ route('account.spaces.create') }}" class="text-primary">Add a space</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $spaces->links('layoutmodule::pagination') }}
@endsection
