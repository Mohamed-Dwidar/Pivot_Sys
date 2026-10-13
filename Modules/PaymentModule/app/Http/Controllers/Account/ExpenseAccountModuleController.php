<?php

namespace Modules\PaymentModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\PaymentModule\app\Http\Controllers\Concerns\ExpenseActions;
use Modules\PaymentModule\app\Http\Requests\ExpenseRequest;

// logged in account: all the account's expenses (ExpenseActions) + the owner's edit / delete (a closed shift: reason, logged on it)
class ExpenseAccountModuleController extends Controller
{
    use ExpenseActions;

    protected function area(): string
    {
        return 'account';
    }

    public function edit($id)
    {
        $expense = $this->expenseService->findOne($this->accountId(), $id);
        return view('paymentmodule::Expense.edit', [
            'expense' => $expense,
            'categories' => $this->categoryService->options($this->accountId(), $expense->expense_category_id),
            'methods' => $this->methodService->options($this->accountId(), $expense->payment_method_id),
            'closedShift' => $this->expenseService->closedShiftOf($expense),
            'hasAttachment' => (bool) $this->expenseService->attachmentPath($expense),
        ]);
    }

    public function update(ExpenseRequest $request, $id)
    {
        $expense = $this->expenseService->update($this->accountId(), $id, $request->validated(), Auth::user());

        return response()->json([
            'message' => 'Expense #' . $expense->expense_no . ' has been updated successfully.',
            'row' => $this->row($request, $id),
            'modal' => route('account.expenses.show', $id),
            'redirect' => route('account.expenses.show', $id),
        ]);
    }

    // the delete popup: the reason is required
    public function deleteForm($id)
    {
        $expense = $this->expenseService->findOne($this->accountId(), $id);
        return view('paymentmodule::Expense.delete', ['expense' => $expense, 'closedShift' => $this->expenseService->closedShiftOf($expense)]);
    }

    public function destroy(Request $request, $id)
    {
        $request->validate(['delete_reason' => 'required|string|max:255'], [], ['delete_reason' => 'reason']);
        $expense = $this->expenseService->delete($this->accountId(), $id, $request->input('delete_reason'), Auth::user());

        return response()->json([
            'message' => 'Expense #' . $expense->expense_no . ' has been deleted.',
            'row' => null,
            'reload' => true,
            'redirect' => route('account.expenses.index'),
        ]);
    }
}
