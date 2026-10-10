@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Edit Income Category: {{ $category->name }}
@endsection

@section('actions')
    @include('paymentmodule::Category.partials.delete-form')
@endsection

@section('content')
    <form method="POST" action="{{ route('account.income-categories.update', $category->id) }}" data-ajax data-row="{{ $category->id }}"
        data-confirm="The changes to &quot;{{ $category->name }}&quot; will be saved."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('paymentmodule::Category.partials.form', ['category' => $category])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.income-categories.index')])
    </form>
@endsection
