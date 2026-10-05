<?php

namespace Modules\AccountModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\AccountModule\app\Models\Account;

// guest registration: basic data only
class RegisterAccountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'phone' => ['required', 'regex:' . Account::PHONE_REGEX],
            'email' => 'required|email:rfc,filter|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
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
        return ['name_ar' => 'name (Arabic)'];
    }

    public function authorize(): bool
    {
        return true;
    }
}
