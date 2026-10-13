@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Edit Expense Category: {{ $category->name }}
@endsection

@section('actions')
    @include('paymentmodule::ExpenseCategory.partials.delete-form')
@endsection

@section('content')
    <form method="POST" action="{{ route('account.expense-categories.update', $category->id) }}" data-ajax data-row="{{ $category->id }}"
        data-confirm="The changes to &quot;{{ $category->name }}&quot; will be saved."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')

        @include('paymentmodule::ExpenseCategory.partials.form', ['category' => $category])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.expense-categories.index')])
    </form>
@endsection
