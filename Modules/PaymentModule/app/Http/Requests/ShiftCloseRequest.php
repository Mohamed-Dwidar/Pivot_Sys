<?php

namespace Modules\PaymentModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// closing a shift: the highest payment id shown in the confirmation (ShiftClosingService::close() refuses a newer one)
class ShiftCloseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'up_to_id' => 'required|integer|min:1',
            // the account closes an employee's shift on their behalf (users.id; the service checks it is their employee)
            'user_id' => 'nullable|integer',
            // the account's day screen it was opened from (the answer goes back to it)
            'date' => 'nullable|date',
            'index' => 'boolean',
            // logout: the closing popup of the logout (logged logout_closed), then the logout
            'logout' => 'boolean',
        ];
    }
}
