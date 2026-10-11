<?php

namespace Modules\PaymentModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// the owner's delete of a payment: the reason is required (kept with the soft deleted payment)
class PaymentDeleteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'delete_reason' => 'required|string|min:3|max:255',
        ];
    }

    public function attributes(): array
    {
        return ['delete_reason' => 'reason'];
    }

    public function authorize(): bool
    {
        return true;
    }
}
