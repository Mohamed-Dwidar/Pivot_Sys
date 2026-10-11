<?php

namespace Modules\PaymentModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// the owner reopens a closed day / an employee's closed shift: the reason is required (kept on the closing + logged)
class DayReopenRequest extends FormRequest
{
    public function rules(): array
    {
        return ['reopen_reason' => 'required|string|min:3|max:255'];
    }

    public function attributes(): array
    {
        return ['reopen_reason' => 'reason'];
    }

    public function authorize(): bool
    {
        return true;
    }
}
