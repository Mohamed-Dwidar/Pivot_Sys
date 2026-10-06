@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Add Subscription Type
@endsection

@section('content')
    <form method="POST" action="{{ route('account.subscription-types.store') }}" data-ajax
        data-confirm="The subscription type will be added, then you can assign it to your spaces."
        data-confirm-title="Add this subscription type?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('spacemodule::Account.SubscriptionType.partials.form', ['subscriptionType' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.subscription-types.index')])
    </form>
@endsection
