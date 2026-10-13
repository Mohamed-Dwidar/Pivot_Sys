<?php

namespace Modules\PaymentModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// closing a shift: the highest payment / expense id shown in the confirmation (ShiftClosingService::close() refuses a newer one)
class ShiftCloseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // the newest payment / expense shown (0 = none of that kind)
            'up_to_id' => 'required|integer|min:0',
            'up_to_expense_id' => 'nullable|integer|min:0',
            // the account closes an employee's shift on their behalf (users.id; the service checks it is their employee)
            'user_id' => 'nullable|integer',
            // the Daily Summary day it was opened from (the answer goes back to it)
            'date' => 'nullable|date',
            // logout: the closing popup of the logout (logged logout_closed), then the logout
            'logout' => 'boolean',
        ];
    }
}
