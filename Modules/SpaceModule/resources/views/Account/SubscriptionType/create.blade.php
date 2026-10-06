@extends('layoutmodule::account.main')

@section('title')
    Add Subscription Type
@endsection

@section('content')
    <form method="POST" action="{{ route('account.subscription-types.store') }}">
        @csrf

        @include('spacemodule::Account.SubscriptionType.partials.form', ['subscriptionType' => null])

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
            <a href="{{ route('account.subscription-types.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
