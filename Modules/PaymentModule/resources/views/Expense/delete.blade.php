@extends(request()->ajax() ? 'layoutmodule::modal' : Auth::user()->userable->layout())

@section('title')
    Delete Expense #{{ $expense->expense_no }}
@endsection

{{-- the owner only: soft delete, the reason + who deleted it are kept; in a closed shift: logged on it, its totals recalculated --}}
@section('content')
    <form method="POST" action="{{ route('account.expenses.destroy', $expense->id) }}" data-ajax data-row="{{ $expense->id }}"
        data-confirm="Expense #{{ $expense->expense_no }} will be deleted."
        data-confirm-title="Delete this expense?"
        data-confirm-button="Delete"
        data-confirm-variant="danger">
        @csrf
        @method('DELETE')
        @include('paymentmodule::Expense.partials.closed-shift-note')
        <dl class="detail-list">
            <div><dt>Item</dt><dd>{{ $expense->item }}</dd></div>
            <div><dt>Amount</dt><dd>{{ number_format($expense->amount, 2) }} · {{ $expense->method_name }}</dd></div>
        </dl>
        <div class="mt-6">
            @include('layoutmodule::partials.field', ['name' => 'delete_reason', 'label' => 'Reason', 'type' => 'textarea', 'required' => true,
                'attrs' => 'maxlength="255"', 'hint' => 'Why the expense is deleted (kept with it).'])
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-danger"><i data-lucide="trash-2"></i> Delete</button>
            @if (request()->ajax())
                <button type="button" data-tw-dismiss="modal" class="btn btn-secondary">Cancel</button>
            @else
                <a href="{{ route('account.expenses.show', $expense->id) }}" class="btn btn-secondary">Cancel</a>
            @endif
        </div>
    </form>
@endsection
