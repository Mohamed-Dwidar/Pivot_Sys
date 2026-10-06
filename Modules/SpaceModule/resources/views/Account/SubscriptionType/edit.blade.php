@extends('layoutmodule::account.main')

@section('title')
    Edit Subscription Type: {{ $subscriptionType->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route('account.subscription-types.update', $subscriptionType->id) }}">
        @csrf
        @method('PUT')

        @include('spacemodule::Account.SubscriptionType.partials.form', ['subscriptionType' => $subscriptionType])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
            <a href="{{ route('account.subscription-types.show', $subscriptionType->id) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
