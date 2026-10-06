<?php

namespace Modules\SpaceModule\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\SpaceModule\app\Models\SubscriptionType;

class SubscriptionTypeRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = ['name' => 'required|string|max:255'];
        foreach (array_keys(SubscriptionType::OPTIONS) as $option) {
            $rules[$option] = 'boolean';
        }
        return $rules;
    }

    public function authorize(): bool
    {
        return true;
    }
}
