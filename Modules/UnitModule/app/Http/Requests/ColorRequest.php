<?php

namespace Modules\UnitModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ColorRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('colors', 'name')->ignore($this->route('id'))],
            'value' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'value.regex' => 'The color must be a hex color like #1d4ed8.',
        ];
    }

    public function attributes(): array
    {
        return ['value' => 'color'];
    }

    public function authorize(): bool
    {
        return true;
    }
}
