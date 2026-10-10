@extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')

@section('title')
    Add Income Category
@endsection

@section('content')
    <form method="POST" action="{{ route('account.income-categories.store') }}" data-ajax
        data-confirm="The income category will be added to your income categories list."
        data-confirm-title="Add this income category?"
        data-confirm-button="Add"
        data-confirm-variant="success">
        @csrf

        @include('paymentmodule::Category.partials.form', ['category' => null])

        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.income-categories.index')])
    </form>
@endsection
