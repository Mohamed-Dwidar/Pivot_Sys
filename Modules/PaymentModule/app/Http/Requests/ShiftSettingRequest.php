<?php

namespace Modules\PaymentModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// the account's shift reminder settings (hours)
class ShiftSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shift_reminder_hours' => 'required|integer|min:1|max:168',
            'shift_snooze_hours' => 'required|integer|min:1|max:48',
        ];
    }

    public function attributes(): array
    {
        return ['shift_reminder_hours' => 'reminder after', 'shift_snooze_hours' => 'remind again after'];
    }
}
