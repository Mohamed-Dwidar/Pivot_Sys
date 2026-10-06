@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Edit Subscription Type: {{ $subscriptionType->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route('account.subscription-types.update', $subscriptionType->id) }}" data-ajax data-row="{{ $subscriptionType->id }}"
        data-confirm="The changes to &quot;{{ $subscriptionType->name }}&quot; will be saved."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('spacemodule::Account.SubscriptionType.partials.form', ['subscriptionType' => $subscriptionType])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.subscription-types.show', $subscriptionType->id)])
    </form>
@endsection
