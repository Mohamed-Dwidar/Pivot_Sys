@extends('layoutmodule::account.main')

@section('title')
    Subscription Types
@endsection

@section('actions')
    <a href="{{ route('account.subscription-types.create') }}" class="btn btn-primary"><i data-lucide="plus"></i> Add Subscription Type</a>
@endsection

@section('content')
    <form method="GET" action="{{ route('account.subscription-types.index') }}" class="filter-bar">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="{{ config('layoutmodule.form.input') }}" placeholder="Search by name">
        <button type="submit" class="btn btn-primary"><i data-lucide="search"></i> Search</button>
        @if (!empty($filters['search']))
            <a href="{{ route('account.subscription-types.index') }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>

    <div class="table-wrap mt-5">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Options</th>
                    <th>Spaces</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subscriptionTypes as $subscriptionType)
                    <tr>
                        <td><a href="{{ route('account.subscription-types.show', $subscriptionType->id) }}" class="font-medium">{{ $subscriptionType->name }}</a></td>
                        <td>@include('spacemodule::Account.SubscriptionType.partials.options')</td>
                        <td>{{ $subscriptionType->spaces_count }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('account.subscription-types.show', $subscriptionType->id) }}" class="btn btn-secondary btn-sm" title="View"><i data-lucide="eye"></i></a>
                                <a href="{{ route('account.subscription-types.edit', $subscriptionType->id) }}" class="btn btn-secondary btn-sm" title="Edit"><i data-lucide="pencil"></i></a>
                                @include('spacemodule::Account.SubscriptionType.partials.delete-form', ['size' => 'btn-sm'])
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">
                            No subscription types found.
                            <a href="{{ route('account.subscription-types.create') }}" class="text-primary">Add a subscription type</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $subscriptionTypes->links('layoutmodule::pagination') }}
@endsection
