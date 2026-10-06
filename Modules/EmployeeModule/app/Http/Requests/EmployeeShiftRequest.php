<?php

namespace Modules\EmployeeModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeShiftRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            // the end can be before the start (night shift)
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|different:start_time',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'end_time.different' => 'The end time must be different from the start time.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
