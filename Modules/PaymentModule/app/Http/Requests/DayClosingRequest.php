<?php

namespace Modules\PaymentModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// close a day (today or before); DayClosingService::close() checks it again (already closed, future)
class DayClosingRequest extends FormRequest
{
    public function rules(): array
    {
        return ['date' => 'required|date|before_or_equal:today'];
    }

    public function messages(): array
    {
        return ['date.before_or_equal' => 'A day in the future can\'t be closed.'];
    }

    public function authorize(): bool
    {
        return true;
    }
}
