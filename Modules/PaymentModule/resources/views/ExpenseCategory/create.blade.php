@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Add Expense Category
@endsection

@section('content')
    <form method="POST" action="{{ route('account.expense-categories.store') }}" data-ajax
        data-confirm="The expense category will be added to your expense categories list."
        data-confirm-title="Add this expense category?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('paymentmodule::ExpenseCategory.partials.form', ['category' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.expense-categories.index')])
    </form>
@endsection
