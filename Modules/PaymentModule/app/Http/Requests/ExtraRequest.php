<?php

namespace Modules\PaymentModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\PaymentModule\app\Models\Extra;

// an extra / adjustment of a package; ExtraService checks the rules again (category ownership, amount not 0)
class ExtraRequest extends FormRequest
{
    public function rules(): array
    {
        $accountId = $this->user()->userable->ownerAccountId();

        return [
            'type' => ['required', Rule::in(array_keys(Extra::TYPES))],
            // a sale only (an adjustment is not revenue)
            'income_category_id' => $this->input('type') === 'extra'
                ? ['required', 'integer', Rule::exists('income_categories', 'id')->where('account_id', $accountId)->whereNull('deleted_at')]
                : ['exclude'],
            'item' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.01|max:99999',
            // an adjustment / a return can be negative
            'unit_price' => 'required|numeric|min:-99999999|max:99999999|not_in:0',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:5000',
        ];
    }

    public function attributes(): array
    {
        return ['income_category_id' => 'income category', 'unit_price' => 'unit price'];
    }

    public function messages(): array
    {
        return ['unit_price.not_in' => 'The unit price can\'t be 0.'];
    }

    public function authorize(): bool
    {
        return true;
    }
}
