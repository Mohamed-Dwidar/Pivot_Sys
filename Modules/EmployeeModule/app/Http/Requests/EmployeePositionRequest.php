<?php

namespace Modules\EmployeeModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeePositionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
