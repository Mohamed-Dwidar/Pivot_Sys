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
            'email' => ['required', 'email:rfc,filter', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'current_password' => 'required|current_password:web',
            'password' => 'nullable|string|min:6|confirmed',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
