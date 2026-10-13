<?php

namespace Modules\PaymentModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// a refund of a payment (the account or an employee; into the refunder's open shift); PaymentService::refund() checks the
// limit (this payment's amount less its refunds), the reason, ownership
class PaymentRefundRequest extends FormRequest
{
    public function rules(): array
    {
        $accountId = $this->user()->userable->ownerAccountId();

        return [
            'amount' => 'required|numeric|min:0.01|max:99999999',
            // how the money goes back
            'payment_method_id' => ['required', 'integer', Rule::exists('payment_methods', 'id')->where('account_id', $accountId)->where('is_active', true)->whereNull('deleted_at')],
            'paid_at' => 'required|date',
            // why the money is given back (shown in the Daily Summary)
            'reason' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:5000',
        ];
    }

    public function attributes(): array
    {
        return ['payment_method_id' => 'payment method', 'paid_at' => 'date'];
    }

    public function messages(): array
    {
        return ['payment_method_id.exists' => 'This payment method is not available, choose another one.'];
    }

    public function authorize(): bool
    {
        return true;
    }
}
