<?php

namespace Modules\AccountModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\AccountModule\app\Models\Account;

// account owner: complete / edit his profile
class UpdateAccountProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'phone' => ['required', 'regex:' . Account::PHONE_REGEX],
            'address' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string|max:5000',
            'description_en' => 'nullable|string|max:5000',
            'logo' => 'nullable|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'The phone must be a valid phone number: digits only (8-15), optionally starting with +.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name_ar' => 'name (Arabic)',
            'name_en' => 'name (English)',
            'description_ar' => 'description (Arabic)',
            'description_en' => 'description (English)',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
