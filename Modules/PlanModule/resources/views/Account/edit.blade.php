@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Edit Plan: {{ $plan->name }}
@endsection

@section('content')
    <form method="POST" action="{{ route('account.plans.update', $plan->id) }}" data-ajax data-row="{{ $plan->id }}"
        data-confirm="The changes to &quot;{{ $plan->name }}&quot; will be saved."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('planmodule::Account.partials.form', ['plan' => $plan])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.plans.show', $plan->id)])
    </form>
@endsection
