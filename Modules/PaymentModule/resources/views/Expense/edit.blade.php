@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    Edit Expense #{{ $expense->expense_no }}
@endsection

@section('modal-size', 'lg')

{{-- the owner only (account routes); in a closed shift a reason is required (logged on the shift, its totals recalculated) --}}
@section('content')
    <form method="POST" action="{{ route('account.expenses.update', $expense->id) }}" enctype="multipart/form-data" data-ajax data-row="{{ $expense->id }}"
        data-confirm="The expense will be changed{{ $closedShift ? ', and its closed shift calculated again' : '' }}."
        data-confirm-title="Save the changes?"
        data-confirm-button="Save"
        data-confirm-variant="success">
        @csrf
        @method('PUT')
        @include('paymentmodule::Expense.partials.closed-shift-note')
        @include('paymentmodule::Expense.partials.form')
        @include('layoutmodule::partials.form-actions', ['cancel' => route('account.expenses.show', $expense->id)])
    </form>
@endsection
