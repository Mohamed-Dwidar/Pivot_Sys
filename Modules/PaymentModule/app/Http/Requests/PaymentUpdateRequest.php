<?php

namespace Modules\PaymentModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\PaymentModule\app\Models\Payment;

// the owner's edit of a payment / refund / withholding (the payable, member and type are kept);
// PaymentService::update() checks the rules again (owner, closed days, refund limit, ownership)
class PaymentUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $accountId = $this->user()->userable->ownerAccountId();
        $ownedBy = fn ($table) => Rule::exists($table, 'id')->where('account_id', $accountId)->whereNull('deleted_at');
        // the payment being edited (404 in the controller / service when it is not the account's)
        $payment = Payment::where('account_id', $accountId)->find($this->route('id'));

        return [
            'amount' => 'required|numeric|min:0.01|max:99999999',
            'paid_at' => 'required|date',
            // the current method stays valid even if it was deactivated since
            'payment_method_id' => $payment?->type === 'withholding'
                ? ['exclude']
                : ['required', 'integer', $ownedBy('payment_methods')->where(fn ($query) => $query->where('is_active', true)->orWhere('id', (int) $payment?->payment_method_id))],
            'income_category_id' => $payment?->payable_type
                ? ['exclude']
                : ['required', 'integer', $ownedBy('income_categories')->where(fn ($query) => $query->where('is_active', true)->orWhere('id', (int) $payment?->income_category_id))],
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:5000',
        ];
    }

    public function attributes(): array
    {
        return ['payment_method_id' => 'payment method', 'income_category_id' => 'income category', 'paid_at' => 'date'];
    }

    public function messages(): array
    {
        return [
            'income_category_id.exists' => 'This income category is not available, choose another one.',
            'payment_method_id.exists' => 'This payment method is not available, choose another one.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
