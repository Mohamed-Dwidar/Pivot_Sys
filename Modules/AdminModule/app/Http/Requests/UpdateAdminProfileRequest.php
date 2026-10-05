<?php

namespace Modules\AdminModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminProfileRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->user('admin')->id;

        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email:rfc,filter', 'max:255', Rule::unique('admins', 'email')->ignore($id)],
            'current_password' => 'required|current_password:admin',
            'password' => 'nullable|string|min:6|confirmed',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
