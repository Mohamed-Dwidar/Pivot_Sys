<?php

namespace Modules\UserModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserAccountRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->user()->id;

        return [
            // employees can not change their email (their account manages it)
            'email' => $this->user()->userable->canChangeEmail()
                ? ['required', 'email:rfc,filter', 'max:255', Rule::unique('users', 'email')->ignore($id)]
                : ['exclude'],
            'current_password' => 'required|current_password:web',
            'password' => 'nullable|string|min:6|confirmed',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
