@extends('layoutmodule::account.main')

@section('title')
    Subscription Type Details
@endsection

@section('actions')
    <a href="{{ route('account.subscription-types.edit', $subscriptionType->id) }}" class="btn btn-secondary"><i data-lucide="pencil"></i> Edit</a>
    @include('spacemodule::Account.SubscriptionType.partials.delete-form')
@endsection

@section('content')
    <div class="text-lg font-medium">{{ $subscriptionType->name }}</div>
    <div class="mt-2">@include('spacemodule::Account.SubscriptionType.partials.options')</div>

    <div class="form-section mt-8">Spaces ({{ $subscriptionType->spaces->count() }})</div>
    @forelse ($subscriptionType->spaces as $space)
        <a href="{{ route('account.spaces.show', $space->id) }}" class="badge badge-inactive mr-1">{{ $space->name }}</a>
    @empty
        <div class="text-slate-500">Not assigned to any space yet. Assign it from the space form.</div>
    @endforelse

    <div class="form-actions">
        <a href="{{ route('account.subscription-types.index') }}" class="btn btn-secondary"><i data-lucide="arrow-left"></i> Back to Subscription Types</a>
    </div>
@endsection
