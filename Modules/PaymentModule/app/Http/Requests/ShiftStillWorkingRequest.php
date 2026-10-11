<?php

namespace Modules\PaymentModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// a reminder answer: "I'm still working" (reason required, the popup comes back after the snooze) / "Log out, I'm still working"
class ShiftStillWorkingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['still_working', 'logout_still_working'])],
            'reason' => 'required_if:action,still_working|nullable|string|min:3|max:255',
        ];
    }

    public function messages(): array
    {
        return ['reason.required_if' => 'Write why you are still working (e.g. covering a colleague\'s shift).'];
    }
}
