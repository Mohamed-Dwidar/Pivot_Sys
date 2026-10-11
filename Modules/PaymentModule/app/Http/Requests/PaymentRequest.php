<?php

namespace Modules\PaymentModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\PaymentModule\app\Services\PaymentService;

// add a payment: of a reservation / a package of a member, or other income (income category);
// PaymentService::create() checks the rules (and the ownership) again, it is also called by imports / invoices
class PaymentRequest extends FormRequest
{
    public function rules(): array
    {
        // the account itself, or the account of the logged in employee
        $accountId = $this->user()->userable->ownerAccountId();
        $ownedBy = fn ($table) => Rule::exists($table, 'id')->where('account_id', $accountId)->whereNull('deleted_at');
        $payable = $this->input('payable');
        $tables = ['reservation' => 'reservations', 'package' => 'packages'];

        return [
            // empty = other income
            'payable' => ['nullable', Rule::in(array_keys(PaymentService::PAYABLES))],
            'payable_id' => isset($tables[$payable])
                ? ['required', 'integer', $ownedBy($tables[$payable])->where('member_id', (int) $this->input('member_id'))]
                : ['exclude'],
            'income_category_id' => $payable ? ['exclude'] : ['required', 'integer', $ownedBy('income_categories')->where('is_active', true)],
            // required for a reservation / package (its options are the member's), optional for other income
            // (members are not soft deleted)
            'member_id' => [Rule::requiredIf((bool) $payable), 'nullable', 'integer', Rule::exists('members', 'id')->where('account_id', $accountId)],
            'type' => ['required', Rule::in(['payment', 'withholding'])],
            'amount' => 'required|numeric|min:0.01|max:99999999',
            // withholding: no money received (a certificate instead)
            'payment_method_id' => $this->input('type') === 'withholding'
                ? ['exclude']
                : ['required', 'integer', $ownedBy('payment_methods')->where('is_active', true)],
            'paid_at' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:5000',
            // the form was opened from a reservation / package view (the answer goes back to it)
            'back_to_payable' => 'boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'payable' => 'payment for',
            'payable_id' => $this->input('payable') === 'package' ? 'package' : 'reservation',
            'income_category_id' => 'income category',
            'member_id' => 'member',
            'payment_method_id' => 'payment method',
            'paid_at' => 'date',
        ];
    }

    public function messages(): array
    {
        return [
            'payable_id.exists' => 'Choose one of the member\'s ' . ($this->input('payable') === 'package' ? 'packages' : 'reservations') . '.',
            'income_category_id.exists' => 'This income category is not available, choose another one.',
            'payment_method_id.exists' => 'This payment method is not available, choose another one.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
