<?php

namespace Modules\ReservationModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\ReservationModule\app\Models\ReservationStatus;

class ReservationStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            // the default status must be active
            'is_active' => 'boolean|accepted_if:is_default,1',
            // the current default stays default (another status is made the default instead)
            'is_default' => ['boolean', function ($attribute, $value, $fail) {
                $current = $this->route('id') ? ReservationStatus::find($this->route('id')) : null;
                if ($current?->is_default && !filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                    $fail('There must be a default status, make another status the default instead.');
                }
            }],
            'sort_order' => 'nullable|integer|min:0|max:65535',
        ];
    }

    public function messages(): array
    {
        return [
            'is_active.accepted_if' => 'The default status must be active.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name_ar' => 'name (Arabic)',
            'name_en' => 'name (English)',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
