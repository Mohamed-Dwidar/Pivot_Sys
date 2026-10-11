<?php

namespace Modules\PaymentModule\app\Http\Controllers\Account;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\PaymentModule\app\Http\Controllers\Concerns\PaymentActions;
use Modules\PaymentModule\app\Http\Requests\PaymentDeleteRequest;
use Modules\PaymentModule\app\Http\Requests\PaymentRefundRequest;
use Modules\PaymentModule\app\Http\Requests\PaymentUpdateRequest;

// logged in account: the account's payments (add / view in PaymentActions) + the owner's edit / delete (reason) / refund
// (PaymentService checks the owner again: the routes are account only)
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
            'payableName' => $this->paymentService->payableName($payment->payable),
            'methods' => $this->methodService->options($this->accountId(), $payment->payment_method_id),
            'categories' => $this->categoryService->options($this->accountId(), $payment->income_category_id),
        ]);
    }

    public function update(PaymentUpdateRequest $request, $id)
    {
        $payment = $this->paymentService->update($this->accountId(), $id, $request->validated(), Auth::user());

        return response()->json([
            'message' => 'Payment #' . $payment->payment_no . ' has been updated successfully.',
            'row' => $this->row($request, $id),
            'modal' => route('account.payments.show', $id),
            'redirect' => route('account.payments.show', $id),
        ]);
    }

    // the delete popup: the reason is required
    public function deleteForm($id)
    {
        $payment = $this->paymentService->findOne($this->accountId(), $id);
        return view('paymentmodule::Payment.delete', compact('payment'));
    }

    public function destroy(PaymentDeleteRequest $request, $id)
    {
        $payment = $this->paymentService->delete($this->accountId(), $id, $request->validated('delete_reason'), Auth::user());
        $payableUrl = $this->payableUrl($payment);

        return response()->json([
            'message' => 'Payment #' . $payment->payment_no . ' has been deleted.',
            'row' => null,
            'reload' => true,
            'modal' => $payableUrl,
            'redirect' => $payableUrl ?? route('account.payments.index'),
        ]);
    }

    public function refundForm($id)
    {
        $payment = $this->paymentService->findOne($this->accountId(), $id);
        return view('paymentmodule::Payment.refund', [
            'payment' => $payment,
            'payableName' => $this->paymentService->payableName($payment->payable),
            'refundable' => $this->paymentService->refundable($payment),
            'methods' => $this->methodService->options($this->accountId()),
        ]);
    }

    public function refund(PaymentRefundRequest $request, $id)
    {
        $refund = $this->paymentService->refund($this->accountId(), $id, $request->validated(), Auth::user());

        return response()->json([
            'message' => 'Refund #' . $refund->payment_no . ' has been added successfully.',
            'reload' => true,
            'modal' => route('account.payments.show', $refund->id),
            'redirect' => route('account.payments.show', $refund->id),
        ]);
    }
}
