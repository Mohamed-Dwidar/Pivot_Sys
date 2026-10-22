<?php

namespace Modules\PaymentModule\app\Http\Controllers\Account;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\PaymentModule\app\Http\Controllers\Concerns\PaymentActions;
use Modules\PaymentModule\app\Http\Requests\PaymentDeleteRequest;
use Modules\PaymentModule\app\Http\Requests\PaymentUpdateRequest;

// logged in account: the account's payments (add / view / refund in PaymentActions) + the owner's edit / delete
// (a payment of a closed shift: reason required, logged on the shift; PaymentService checks the owner again: the routes are account only)
class PaymentAccountModuleController extends Controller
{
    use PaymentActions;

    protected function area(): string
    {
        return 'account';
    }

    // a reservation / package payment: back to its view after the change (popup or full page)
    private function payableUrl($payment): ?string
    {
        return $payment->payable_kind ? route('account.' . $payment->payable_kind . 's.show', $payment->payable_id) : null;
    }

    public function edit($id)
    {
        $payment = $this->paymentService->findOne($this->accountId(), $id);
        return view('paymentmodule::Payment.edit', [
            'payment' => $payment,
            'payableName' => $this->paymentService->payableName($payment->payable, false),
            'closedShift' => $this->paymentService->closedShiftOf($payment),
            'methods' => $this->methodService->options($this->accountId(), $payment->payment_method_id),
            'categories' => $this->categoryService->options($this->accountId(), $payment->income_category_id),
        ]);
    }

    public function update(PaymentUpdateRequest $request, $id)
    {
        $payment = $this->paymentService->update($this->accountId(), $id, $request->validated(), Auth::user());

        return response()->json([
            'message' => ($payment->type === 'refund' ? 'Refund #' : 'Cash In #') . $payment->payment_no . ' has been updated successfully.',
            'row' => $this->row($request, $id),
            // a list without this row id (Payments All / Cash Out: row-payment-{id}) reloads
            'reload' => true,
            'modal' => route('account.payments.show', $id),
            'redirect' => route('account.payments.show', $id),
        ]);
    }

    // the delete popup: the reason is required
    public function deleteForm($id)
    {
        $payment = $this->paymentService->findOne($this->accountId(), $id);
        return view('paymentmodule::Payment.delete', ['payment' => $payment, 'closedShift' => $this->paymentService->closedShiftOf($payment),
            'payableName' => $this->paymentService->payableName($payment->payable, false)]);
    }

    public function destroy(PaymentDeleteRequest $request, $id)
    {
        $payment = $this->paymentService->delete($this->accountId(), $id, $request->validated('delete_reason'), Auth::user());
        $payableUrl = $this->payableUrl($payment);

        return response()->json([
            'message' => ($payment->type === 'refund' ? 'Refund #' : 'Cash In #') . $payment->payment_no . ' has been deleted.',
            'row' => null,
            'reload' => true,
            'modal' => $payableUrl,
            'redirect' => $payableUrl ?? route('account.payments.index'),
        ]);
    }
}
