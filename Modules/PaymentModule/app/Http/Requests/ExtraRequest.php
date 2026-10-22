<?php

namespace Modules\PaymentModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\PaymentModule\app\Models\Extra;

// an extra / adjustment of a package; ExtraService checks the rules again (category ownership, amount not 0)
class ExtraRequest extends FormRequest
{
    // an adjustment (2026-10-21): the form has Item + Amount only → quantity 1, unit price = the amount
    protected function prepareForValidation(): void
    {
        if ($this->input('type') === 'adjustment') {
            $this->merge(['quantity' => 1, 'unit_price' => $this->input('adjustment_amount')]);
        }
    }

    public function rules(): array
    {
        $accountId = $this->user()->userable->ownerAccountId();

        // employees: an extra (a sale) with a positive price only; adjustments / returns = the account (2026-10-21)
        $employee = !($this->user()->userable instanceof \Modules\AccountModule\app\Models\Account);

        return [
            'type' => ['required', Rule::in($employee ? ['extra'] : array_keys(Extra::TYPES))],
            // a sale only (an adjustment is not revenue)
            'income_category_id' => $this->input('type') === 'extra'
                ? ['required', 'integer', Rule::exists('income_categories', 'id')->where('account_id', $accountId)->whereNull('deleted_at')]
                : ['exclude'],
            'item' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.01|max:99999',
            // an adjustment / a return can be negative
            'unit_price' => $employee ? 'required|numeric|min:0.01|max:99999999' : 'required|numeric|min:-99999999|max:99999999|not_in:0',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:5000',
            // an adjustment's Amount (its error shows under it; copied into unit_price above)
            'adjustment_amount' => $this->input('type') === 'adjustment' ? 'required|numeric|min:-99999999|max:99999999|not_in:0' : 'exclude',
        ];
    }

    public function attributes(): array
    {
        return ['income_category_id' => 'income category', 'unit_price' => 'unit price', 'adjustment_amount' => 'amount'];
    }

    public function messages(): array
    {
        return ['unit_price.not_in' => 'The unit price can\'t be 0.', 'adjustment_amount.not_in' => 'The amount can\'t be 0.'];
    }

    public function authorize(): bool
    {
        return true;
    }
}
