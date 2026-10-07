<?php

namespace Modules\PackageModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            // a member of the same account (the account, or the account of the logged in employee)
            'member_id' => ['required', 'integer', Rule::exists('members', 'id')->where('account_id', $this->user()->userable->ownerAccountId())],
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'amount' => 'required|numeric|min:0|max:99999999',
            'discount_type' => 'nullable|in:percentage,after',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'after_discount' => 'nullable|numeric|min:0|lte:amount',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'after_discount.lte' => 'The after discount can not be more than the amount.',
        ];
    }

    public function attributes(): array
    {
        return [
            'member_id' => 'member',
            'date_from' => 'start date',
            'date_to' => 'end date',
            'after_discount' => 'after discount',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
